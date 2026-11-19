<?php
/* Copyright (C) 2026 Signarama
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

$res = 0;
if (!$res && file_exists('../../../main.inc.php')) {
    $res = @include '../../../main.inc.php';
}
if (!$res && file_exists('../../main.inc.php')) {
    $res = @include '../../main.inc.php';
}
if (!$res) {
    die('Include of main fails');
}

require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
dol_include_once('/signaramaplancomptable/class/signaramaplancomptable.class.php');

$langs->loadLangs(array('admin', 'signaramaplancomptable@signaramaplancomptable'));

if (!$user->admin) {
    accessforbidden();
}

$action = GETPOST('action', 'aZ09');
$id = GETPOSTINT('id');
$plan = new SignaramaPlanComptable($db);
$form = new Form($db);

/*
 * Actions
 */

if ($action == 'export') {
    $csv = $plan->exportCsv();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="signarama_plan_comptable_'.dol_print_date(dol_now(), 'dayrfc').'.csv"');
    print "\xEF\xBB\xBF".$csv;
    exit;
}

if ($action == 'save' && !GETPOST('cancel')) {
    $data = array(
        'pcg' => GETPOST('pcg', 'alphanohtml'),
        'number' => GETPOST('number', 'alphanohtml'),
        'label' => GETPOST('label', 'alphanohtml'),
        'labelshort' => GETPOST('labelshort', 'alphanohtml'),
        'parent' => GETPOST('parent', 'alphanohtml'),
        'group' => GETPOST('group', 'alphanohtml'),
    );
    if ($plan->save($data, $user, $id) > 0) {
        setEventMessages($langs->trans('RecordSaved'), null, 'mesgs');
        header('Location: '.$_SERVER['PHP_SELF']);
        exit;
    }
    setEventMessages($plan->error, null, 'errors');
    $action = $id > 0 ? 'edit' : 'add';
} elseif ($action == 'save') {
    $action = '';
}

if ($action == 'confirm_delete' && GETPOST('confirm') == 'yes') {
    if ($plan->markDeleted($id) > 0) {
        setEventMessages($langs->trans('SignaramaPCMarkedDeleted'), null, 'mesgs');
    } else {
        setEventMessages($plan->error, null, 'errors');
    }
    header('Location: '.$_SERVER['PHP_SELF']);
    exit;
}

if ($action == 'restore') {
    $plan->markDeleted($id, true);
    header('Location: '.$_SERVER['PHP_SELF']);
    exit;
}

if ($action == 'import' && !GETPOST('cancel')) {
    $file = isset($_FILES['importfile']) ? $_FILES['importfile'] : null;
    if (!$file || $file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        setEventMessages($langs->trans('ErrorFailedToReadFile', ''), null, 'errors');
    } elseif (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'csv' || $file['size'] > 2 * 1024 * 1024) {
        setEventMessages($langs->trans('SignaramaPCErrorImportFile'), null, 'errors');
    } else {
        $result = $plan->importCsv((string) file_get_contents($file['tmp_name']), $user, (bool) GETPOST('replace'));
        if ($result['errors']) {
            setEventMessages($langs->trans('SignaramaPCImportAborted'), $result['errors'], 'errors');
        } else {
            setEventMessages($langs->trans('SignaramaPCImportDone', $result['created'], $result['updated'], $result['marked']), null, 'mesgs');
        }
    }
    header('Location: '.$_SERVER['PHP_SELF']);
    exit;
}

if ($action == 'sync') {
    $count = array();
    $warnings = array();
    foreach ($plan->sync($user) as $r) {
        $count[$r['status']] = (isset($count[$r['status']]) ? $count[$r['status']] : 0) + 1;
        $n = $r['line']->number;
        if ($r['status'] == SignaramaPlanComptable::SYNC_NOPARENT) {
            $warnings[] = $langs->trans('SignaramaPCNoParent', $n, $r['info']);
        } elseif ($r['status'] == SignaramaPlanComptable::SYNC_KEPT) {
            $warnings[] = $langs->trans('SignaramaPCKept', $n, $r['info']);
        } elseif ($r['status'] == SignaramaPlanComptable::SYNC_ERROR) {
            $warnings[] = $n.' : '.$r['info'];
        }
    }
    $get = function ($k) use ($count) {
        return isset($count[$k]) ? $count[$k] : 0;
    };
    setEventMessages($langs->trans('SignaramaPCSyncDone', $get('created'), $get('updated'), $get('deleted'), $get('unchanged')), null, 'mesgs');
    if ($warnings) {
        setEventMessages('', $warnings, 'warnings');
    }
    header('Location: '.$_SERVER['PHP_SELF']);
    exit;
}

/*
 * View
 */

llxHeader('', $langs->trans('SignaramaPCSetup'));

$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1">'.$langs->trans('BackToModuleList').'</a>';
print load_fiche_titre($langs->trans('SignaramaPCSetup'), $linkback, 'title_setup');

if ($action == 'delete') {
    $line = $plan->fetch($id);
    if ($line) {
        print $form->formconfirm($_SERVER['PHP_SELF'].'?id='.$id, $langs->trans('SignaramaPCDelete'), $langs->trans('SignaramaPCConfirmDelete', $line->number), 'confirm_delete', '', 'no', 1);
    }
}

print '<div class="opacitymedium">'.$langs->trans('SignaramaPCDesc').'</div><br>';

// Formulaire d'ajout / modification
if ($action == 'add' || $action == 'edit') {
    $line = $action == 'edit' ? $plan->fetch($id) : null;
    $val = function ($key, $default = '') use ($line) {
        if (GETPOSTISSET($key)) {
            return GETPOST($key, 'alphanohtml');
        }
        return $line ? $line->$key : $default;
    };

    print load_fiche_titre($langs->trans($action == 'edit' ? 'SignaramaPCEdit' : 'SignaramaPCAdd'), '', '');
    print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
    print '<input type="hidden" name="token" value="'.newToken().'">';
    print '<input type="hidden" name="action" value="save">';
    print '<input type="hidden" name="id" value="'.((int) $id).'">';
    print '<table class="border centpercent tableforfield">';

    print '<tr><td class="titlefieldcreate fieldrequired">'.$langs->trans('SignaramaPCChart').'</td><td>';
    $pcg = $val('pcg', $plan->getActivePcg());
    print '<select name="pcg" class="flat minwidth300">';
    foreach ($plan->getCharts() as $code => $label) {
        print '<option value="'.dol_escape_htmltag($code).'"'.($code === $pcg ? ' selected' : '').'>'.dol_escape_htmltag($label).'</option>';
    }
    print '</select></td></tr>';

    print '<tr><td class="fieldrequired">'.$langs->trans('SignaramaPCNumber').'</td><td><input type="text" name="number" class="flat minwidth200" maxlength="32" value="'.dol_escape_htmltag($val('number')).'"></td></tr>';
    print '<tr><td class="fieldrequired">'.$langs->trans('SignaramaPCLabel').'</td><td><input type="text" name="label" class="flat minwidth400" maxlength="255" value="'.dol_escape_htmltag($val('label')).'"></td></tr>';
    print '<tr><td>'.$langs->trans('SignaramaPCLabelShort').'</td><td><input type="text" name="labelshort" class="flat minwidth300" maxlength="255" value="'.dol_escape_htmltag($val('labelshort')).'"></td></tr>';
    print '<tr><td>'.$langs->trans('SignaramaPCParent').'</td><td><input type="text" name="parent" class="flat minwidth200" maxlength="32" value="'.dol_escape_htmltag($val('parent')).'"></td></tr>';

    print '<tr><td class="fieldrequired">'.$langs->trans('SignaramaPCGroup').'</td><td>';
    $group = $val('group', 'INCOME');
    print '<select name="group" class="flat minwidth200">';
    foreach ($plan->getGroups() as $g) {
        print '<option value="'.dol_escape_htmltag($g).'"'.($g === $group ? ' selected' : '').'>'.dol_escape_htmltag($g).'</option>';
    }
    print '</select></td></tr>';

    print '</table>';
    print '<div class="center"><br>';
    print '<input type="submit" class="button button-save" value="'.dol_escape_htmltag($langs->trans('Save')).'"> ';
    print '<input type="submit" class="button button-cancel" name="cancel" value="'.dol_escape_htmltag($langs->trans('Cancel')).'">';
    print '</div></form><br>';
}

// Formulaire d'import
if ($action == 'importform') {
    print load_fiche_titre($langs->trans('SignaramaPCImport'), '', '');
    print '<form method="POST" enctype="multipart/form-data" action="'.$_SERVER['PHP_SELF'].'">';
    print '<input type="hidden" name="token" value="'.newToken().'">';
    print '<input type="hidden" name="action" value="import">';
    print '<div class="opacitymedium">'.$langs->trans('SignaramaPCImportHelp', implode(';', SignaramaPlanComptable::$csvColumns)).'</div><br>';
    print '<input type="file" name="importfile" accept=".csv" required> ';
    print '<label><input type="checkbox" name="replace" value="1"> '.$langs->trans('SignaramaPCImportReplace').'</label><br><br>';
    print '<input type="submit" class="button" value="'.dol_escape_htmltag($langs->trans('SignaramaPCImport')).'"> ';
    print '<input type="submit" class="button button-cancel" name="cancel" value="'.dol_escape_htmltag($langs->trans('Cancel')).'">';
    print '</form><br>';
}

// Liste
$stateLabel = array(
    SignaramaPlanComptable::STATE_UPTODATE => array('SignaramaPCStateUptodate', 'status4'),
    SignaramaPlanComptable::STATE_MISSING => array('SignaramaPCStateMissing', 'status1'),
    SignaramaPlanComptable::STATE_DIFFERENT => array('SignaramaPCStateDifferent', 'status3'),
    SignaramaPlanComptable::STATE_NOPARENT => array('SignaramaPCStateNoParent', 'status8'),
    SignaramaPlanComptable::STATE_TODELETE => array('SignaramaPCStateToDelete', 'status8'),
);

print '<div class="div-table-responsive-no-min"><table class="noborder centpercent">';
print '<tr class="liste_titre">';
foreach (array('SignaramaPCChart', 'SignaramaPCNumber', 'SignaramaPCLabel', 'SignaramaPCLabelShort', 'SignaramaPCParent', 'SignaramaPCGroup') as $col) {
    print '<td>'.$langs->trans($col).'</td>';
}
print '<td class="center">'.$langs->trans('SignaramaPCState').'</td><td></td></tr>';

$states = $plan->getStates();
if (!$states) {
    print '<tr><td colspan="8"><span class="opacitymedium">'.$langs->trans('SignaramaPCNone').'</span></td></tr>';
}
foreach ($states as $s) {
    $l = $s['line'];
    print '<tr class="oddeven"'.($l->to_delete ? ' style="opacity:0.6"' : '').'>';
    print '<td>'.dol_escape_htmltag($l->pcg).'</td>';
    print '<td>'.dol_escape_htmltag($l->number).'</td>';
    print '<td>'.dol_escape_htmltag($l->label).'</td>';
    print '<td>'.dol_escape_htmltag($l->labelshort).'</td>';
    print '<td>'.dol_escape_htmltag($l->parent).'</td>';
    print '<td>'.dol_escape_htmltag($l->group).'</td>';
    print '<td class="center">'.dolGetStatus($langs->trans($stateLabel[$s['state']][0]), '', '', $stateLabel[$s['state']][1], 3).'</td>';
    print '<td class="right nowraponall">';
    if ($l->to_delete) {
        print '<a class="paddingright" href="'.$_SERVER['PHP_SELF'].'?action=restore&id='.$l->id.'&token='.newToken().'">'.$langs->trans('SignaramaPCRestore').'</a>';
    } else {
        print '<a class="editfielda paddingright" href="'.$_SERVER['PHP_SELF'].'?action=edit&id='.$l->id.'&token='.newToken().'">'.img_edit().'</a>';
        print '<a href="'.$_SERVER['PHP_SELF'].'?action=delete&id='.$l->id.'&token='.newToken().'">'.img_delete().'</a>';
    }
    print '</td></tr>';
}
print '</table></div>';

print '<div class="tabsAction">';
print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?action=add&token='.newToken().'">'.$langs->trans('SignaramaPCAdd').'</a>';
print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?action=importform&token='.newToken().'">'.$langs->trans('SignaramaPCImport').'</a>';
print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?action=export&token='.newToken().'">'.$langs->trans('SignaramaPCExport').'</a>';
print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'" class="inline-block">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="sync">';
print '<input type="submit" class="butAction" value="'.dol_escape_htmltag($langs->trans('SignaramaPCSync')).'">';
print '</form>';
print '</div>';

llxFooter();
$db->close();
