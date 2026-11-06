<?php
/**
 * Page de configuration du module Monday Kardinal.
 */

$res = 0;
if (!$res && file_exists('../main.inc.php')) {
	$res = @include '../main.inc.php';
}
if (!$res && file_exists('../../main.inc.php')) {
	$res = @include '../../main.inc.php';
}
if (!$res && file_exists('../../../main.inc.php')) {
	$res = @include '../../../main.inc.php';
}
if (!$res) {
	die('Include of main fails');
}

require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
dol_include_once('/mondaykardinal/class/mondayboards.class.php');

$langs->loadLangs(array('admin', 'mondaykardinal@mondaykardinal'));

if (!$user->admin) {
	accessforbidden();
}

$action = GETPOST('action', 'aZ09');

// Réglages éditables : section => clé => type de champ
$sections = array(
	'MondayConnection' => array(
		'TOKEN' => 'password',
		'WORKSPACE_ID' => 'id',
		'CONTACTS_BOARD_ID' => 'id',
		'DEALS_BOARD_ID' => 'id',
	),
	'MondayWhatToSync' => array(
		'SYNC_PROPAL' => 'yesno',
		'SYNC_INVOICE' => 'yesno',
		'SYNC_DRAFTS' => 'yesno',
		'SYNC_ORDERS' => 'yesno',
	),
	'MondayBoardQuotes' => array(
		'BOARD_ID' => 'id',
		'GROUP_ID' => 'id',
		'COL_DOLI_ID' => 'id',
		'COL_REF' => 'id',
		'COL_TYPE' => 'id',
		'COL_STATUS' => 'id',
		'COL_HT' => 'id',
		'COL_TVA' => 'id',
		'COL_TTC' => 'id',
		'COL_DATE' => 'id',
		'COL_DUE' => 'id',
		'COL_EMAIL' => 'id',
		'COL_DETAIL' => 'id',
	),
	'MondayLabels' => array(
		'LBL_TYPE_PROPAL' => 'text',
		'LBL_TYPE_INVOICE' => 'text',
		'LBL_P_DRAFT' => 'text',
		'LBL_P_OPEN' => 'text',
		'LBL_P_SIGNED' => 'text',
		'LBL_P_REFUSED' => 'text',
		'LBL_P_BILLED' => 'text',
		'LBL_I_DRAFT' => 'text',
		'LBL_I_UNPAID' => 'text',
		'LBL_I_LATE' => 'text',
		'LBL_I_PAID' => 'text',
		'LBL_I_ABANDONED' => 'text',
	),
	'MondayBoardProd' => array(
		'PROD_BOARD_ID' => 'id',
		'PROD_GROUP_ID' => 'id',
		'PROD_COL_DOLI_ID' => 'id',
		'PROD_COL_REF' => 'id',
		'PROD_COL_STEP' => 'id',
		'PROD_COL_AMOUNT' => 'id',
		'PROD_COL_DELIVERY' => 'id',
		'PROD_COL_LINK' => 'id',
		'PROD_STEP_INITIAL' => 'text',
	),
);

$report = null;

/*
 * Actions
 */
if ($action == 'save') {
	$error = 0;
	foreach ($sections as $fields) {
		foreach ($fields as $key => $type) {
			if ($type == 'password') {
				if (GETPOST('CLEAR_'.$key, 'int')) {
					$value = '';
				} else {
					$value = GETPOST($key, 'alphanohtml');
					if ($value === '') {
						continue; // champ secret laissé vide : on garde la valeur actuelle
					}
				}
			} elseif ($type == 'yesno') {
				$value = GETPOST($key, 'int') ? '1' : '0';
			} elseif ($type == 'id') {
				$value = trim(GETPOST($key, 'aZ09'));
			} else {
				$value = trim(GETPOST($key, 'alphanohtml'));
			}
			if (dolibarr_set_const($db, MondaySync::PREFIX.$key, $value, 'chaine', 0, '', $conf->entity) < 0) {
				$error++;
			}
		}
	}
	setEventMessages($error ? $langs->trans('Error') : $langs->trans('SetupSaved'), null, $error ? 'errors' : 'mesgs');
	header('Location: '.$_SERVER['PHP_SELF']);
	exit;
}

if ($action == 'test' || $action == 'provision') {
	$api = new MondayApi(MondaySync::param('TOKEN'), MondaySync::param('API_VERSION'));
	$report = MondayBoards::inspect($api, $db, $action == 'provision');
	if ($action == 'provision') {
		// Contrôle final après réparation, pour confirmer l'état obtenu
		$report[] = array('level' => MondayBoards::OK, 'text' => $langs->trans('MondayRecheck'), 'title' => true);
		$report = array_merge($report, MondayBoards::inspect($api, $db, false));
	}
}

if ($action == 'sync') {
	$sync = new MondaySync($db);
	$result = $sync->run();
	setEventMessages($sync->output, null, $result < 0 ? 'errors' : 'mesgs');
	header('Location: '.$_SERVER['PHP_SELF']);
	exit;
}

/*
 * View
 */
$form = new Form($db);

llxHeader('', $langs->trans('MondayKardinalSetup'));

$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1">'.$langs->trans('BackToModuleList').'</a>';
print load_fiche_titre($langs->trans('MondayKardinalSetup'), $linkback, 'title_setup');

print '<div class="info">'.$langs->trans('MondayKardinalIntro').'</div><br>';

$lastRun = getDolGlobalString(MondaySync::PREFIX.'LAST_RUN');
print '<div class="opacitymedium">'.$langs->trans('MondayLastRun').' : '.dol_escape_htmltag($lastRun !== '' ? $lastRun : $langs->trans('MondayNever'));
print ' &nbsp; <a href="'.DOL_URL_ROOT.'/cron/list.php">'.$langs->trans('MondayCronList').'</a></div><br>';

// Boutons d'action (placés en haut : on configure puis on vérifie)
print '<div class="inline-block">';
foreach (array('test' => 'MondayTestConnection', 'provision' => 'MondayProvision', 'sync' => 'MondaySyncNow') as $act => $label) {
	print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'" class="inline-block">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="'.$act.'">';
	print '<input type="submit" class="button" value="'.dol_escape_htmltag($langs->trans($label)).'"> ';
	print '</form>';
}
print '</div>';
print '<div class="opacitymedium small">'.$langs->trans('MondayProvisionHelp').'</div><br>';

// Résultat du contrôle / de la réparation
if ($report !== null) {
	$icons = array(MondayBoards::OK => 'OK', MondayBoards::WARN => $langs->trans('Warning'), MondayBoards::ERR => $langs->trans('Error'));
	$classes = array(MondayBoards::OK => 'ok', MondayBoards::WARN => 'warning', MondayBoards::ERR => 'error');
	print '<table class="noborder centpercent">';
	foreach ($report as $row) {
		if (!empty($row['title'])) {
			print '<tr class="liste_titre"><td colspan="2">'.dol_escape_htmltag($row['text']).'</td></tr>';
			continue;
		}
		print '<tr class="oddeven"><td class="nowraponall" style="width:110px"><span class="'.$classes[$row['level']].'">'.dol_escape_htmltag($icons[$row['level']]).'</span></td><td>'.dol_escape_htmltag($row['text']).'</td></tr>';
	}
	print '</table><br>';
}

print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="save">';

foreach ($sections as $sectionKey => $fields) {
	print '<table class="noborder centpercent">';
	print '<tr class="liste_titre"><td class="titlefieldmiddle">'.$langs->trans($sectionKey).'</td><td>'.$langs->trans('Value').'</td></tr>';

	foreach ($fields as $key => $type) {
		$current = MondaySync::param($key);
		print '<tr class="oddeven"><td>'.$langs->trans('Monday_'.$key).'</td><td>';

		if ($type == 'password') {
			print '<input type="password" class="minwidth400" name="'.$key.'" value="" autocomplete="new-password" placeholder="'.dol_escape_htmltag($current !== '' ? $langs->trans('MondayTokenSet') : $langs->trans('MondayTokenEmpty')).'">';
			if ($current !== '') {
				print ' <label><input type="checkbox" name="CLEAR_'.$key.'" value="1"> '.$langs->trans('MondayTokenClear').'</label>';
			}
		} elseif ($type == 'yesno') {
			print $form->selectyesno($key, $current === '1' ? 1 : 0, 1);
		} else {
			print '<input type="text" class="minwidth300" name="'.$key.'" value="'.dol_escape_htmltag($current).'">';
		}

		print '</td></tr>';
	}
	print '</table><br>';
}

print '<div class="center"><input type="submit" class="button button-save" value="'.$langs->trans('Save').'"></div>';
print '</form>';

llxFooter();
$db->close();
