<?php
/* Copyright (C) 2026 Signarama
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * Gère une liste de comptes comptables supplémentaires (table llx_signaramaplancomptable_line)
 * et la synchronise avec le plan comptable (table llx_accounting_account).
 *
 * Synchronisation : ligne absente du plan => créée ; présente => mise à jour ;
 * ligne supprimée du module => supprimée du plan (uniquement si le compte a été créé par le module,
 * sans sous-compte ni écriture comptable).
 */
class SignaramaPlanComptable
{
    /** Marque (accounting_account.import_key, 14 car. max) des comptes créés par ce module */
    const IMPORT_KEY = 'SIGNARAMAPC';

    // Résultats de synchronisation
    const SYNC_CREATED = 'created';
    const SYNC_UPDATED = 'updated';
    const SYNC_UNCHANGED = 'unchanged';
    const SYNC_DELETED = 'deleted';
    const SYNC_KEPT = 'kept';
    const SYNC_NOPARENT = 'noparent';
    const SYNC_ERROR = 'error';

    // Etats d'une ligne par rapport au plan
    const STATE_MISSING = 'missing';
    const STATE_UPTODATE = 'uptodate';
    const STATE_DIFFERENT = 'different';
    const STATE_NOPARENT = 'noparent';
    const STATE_TODELETE = 'todelete';

    /** @var string[] Colonnes du fichier CSV d'import / export */
    public static $csvColumns = array('pcg', 'number', 'label', 'labelshort', 'parent', 'group');

    /** @var DoliDB */
    protected $db;

    /** @var string */
    public $error = '';

    /**
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    // ------------------------------------------------------------------ référentiels

    /**
     * @return array pcg_version => libellé des plans comptables disponibles
     */
    public function getCharts()
    {
        $charts = array();
        $resql = $this->db->query("SELECT pcg_version, label FROM ".MAIN_DB_PREFIX."accounting_system WHERE active = 1 ORDER BY pcg_version");
        if ($resql) {
            while ($obj = $this->db->fetch_object($resql)) {
                $charts[$obj->pcg_version] = $obj->pcg_version.' - '.$obj->label;
            }
        }

        return $charts;
    }

    /**
     * @return string pcg_version du plan actif, '' si aucun
     */
    public function getActivePcg()
    {
        $resql = $this->db->query("SELECT pcg_version FROM ".MAIN_DB_PREFIX."accounting_system WHERE rowid = ".((int) getDolGlobalInt('CHARTOFACCOUNTS')));
        if ($resql && ($obj = $this->db->fetch_object($resql))) {
            return $obj->pcg_version;
        }

        return '';
    }

    /**
     * @return string[] Groupes de comptes (pcg_type) connus
     */
    public function getGroups()
    {
        global $conf;

        $groups = array('ASSET', 'CAPIT', 'EXPENSE', 'INCOME', 'LIABILITY');
        $resql = $this->db->query("SELECT DISTINCT pcg_type FROM ".MAIN_DB_PREFIX."accounting_account WHERE entity = ".((int) $conf->entity));
        if ($resql) {
            while ($obj = $this->db->fetch_object($resql)) {
                $groups[] = $obj->pcg_type;
            }
        }
        $groups = array_unique($groups);
        sort($groups);

        return $groups;
    }

    // ------------------------------------------------------------------ lignes du module

    /**
     * @return array Liste d'objets (id, pcg, number, label, labelshort, parent, group, to_delete)
     */
    public function fetchAll()
    {
        global $conf;

        $lines = array();
        $sql = "SELECT rowid, fk_pcg_version, account_number, label, labelshort, parent_number, pcg_type, to_delete";
        $sql .= " FROM ".MAIN_DB_PREFIX."signaramaplancomptable_line";
        $sql .= " WHERE entity = ".((int) $conf->entity);
        $sql .= " ORDER BY fk_pcg_version, account_number";
        $resql = $this->db->query($sql);
        if (!$resql) {
            $this->error = $this->db->lasterror();
            return $lines;
        }
        while ($obj = $this->db->fetch_object($resql)) {
            $lines[] = $this->hydrate($obj);
        }

        return $lines;
    }

    /**
     * @param  int         $id rowid
     * @return object|null
     */
    public function fetch($id)
    {
        global $conf;

        $sql = "SELECT rowid, fk_pcg_version, account_number, label, labelshort, parent_number, pcg_type, to_delete";
        $sql .= " FROM ".MAIN_DB_PREFIX."signaramaplancomptable_line";
        $sql .= " WHERE entity = ".((int) $conf->entity)." AND rowid = ".((int) $id);
        $resql = $this->db->query($sql);
        $obj = $resql ? $this->db->fetch_object($resql) : null;

        return $obj ? $this->hydrate($obj) : null;
    }

    /**
     * @param  object $obj Ligne SQL
     * @return object
     */
    protected function hydrate($obj)
    {
        return (object) array(
            'id' => (int) $obj->rowid,
            'pcg' => $obj->fk_pcg_version,
            'number' => $obj->account_number,
            'label' => $obj->label,
            'labelshort' => (string) $obj->labelshort,
            'parent' => (string) $obj->parent_number,
            'group' => $obj->pcg_type,
            'to_delete' => (int) $obj->to_delete,
        );
    }

    /**
     * Normalise et valide une ligne.
     *
     * @param  array $data pcg, number, label, labelshort, parent, group
     * @return string      Message d'erreur, '' si valide
     */
    public function validate(&$data)
    {
        global $langs;

        foreach (self::$csvColumns as $key) {
            $data[$key] = isset($data[$key]) ? trim((string) $data[$key]) : '';
        }

        if (!array_key_exists($data['pcg'], $this->getCharts())) {
            return $langs->trans('SignaramaPCErrorPcg', $data['pcg']);
        }
        if (!preg_match('/^[0-9A-Za-z]{1,32}$/', $data['number'])) {
            return $langs->trans('SignaramaPCErrorNumber', $data['number']);
        }
        if ($data['label'] === '' || dol_strlen($data['label']) > 255 || dol_strlen($data['labelshort']) > 255) {
            return $langs->trans('SignaramaPCErrorLabel', $data['number']);
        }
        if ($data['parent'] !== '' && (!preg_match('/^[0-9A-Za-z]{1,32}$/', $data['parent']) || $data['parent'] === $data['number'])) {
            return $langs->trans('SignaramaPCErrorParent', $data['number']);
        }
        if (!preg_match('/^[A-Za-z_]{1,60}$/', $data['group'])) {
            return $langs->trans('SignaramaPCErrorGroup', $data['number']);
        }

        return '';
    }

    /**
     * Crée ou met à jour une ligne (une ligne marquée à supprimer est réactivée).
     *
     * @param  array $data Données de la ligne
     * @param  User  $user Utilisateur
     * @param  int   $id   rowid de la ligne à modifier, 0 pour une création
     * @return int         rowid si OK, <0 si KO (voir $this->error)
     */
    public function save($data, $user, $id = 0)
    {
        global $conf, $langs;

        $this->error = $this->validate($data);
        if ($this->error) {
            return -1;
        }

        // Unicité (plan + numéro)
        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."signaramaplancomptable_line";
        $sql .= " WHERE entity = ".((int) $conf->entity);
        $sql .= " AND fk_pcg_version = '".$this->db->escape($data['pcg'])."'";
        $sql .= " AND account_number = '".$this->db->escape($data['number'])."'";
        $sql .= " AND rowid <> ".((int) $id);
        $resql = $this->db->query($sql);
        if ($resql && $this->db->num_rows($resql) > 0) {
            $this->error = $langs->trans('SignaramaPCErrorDuplicate', $data['number']);
            return -1;
        }

        $values = "fk_pcg_version = '".$this->db->escape($data['pcg'])."',";
        $values .= " account_number = '".$this->db->escape($data['number'])."',";
        $values .= " label = '".$this->db->escape($data['label'])."',";
        $values .= " labelshort = ".($data['labelshort'] === '' ? "NULL" : "'".$this->db->escape($data['labelshort'])."'").",";
        $values .= " parent_number = ".($data['parent'] === '' ? "NULL" : "'".$this->db->escape($data['parent'])."'").",";
        $values .= " pcg_type = '".$this->db->escape($data['group'])."',";
        $values .= " to_delete = 0";

        if ($id > 0) {
            $sql = "UPDATE ".MAIN_DB_PREFIX."signaramaplancomptable_line SET ".$values;
            $sql .= " WHERE entity = ".((int) $conf->entity)." AND rowid = ".((int) $id);
            if (!$this->db->query($sql)) {
                $this->error = $this->db->lasterror();
                return -1;
            }

            return $id;
        }

        $sql = "INSERT INTO ".MAIN_DB_PREFIX."signaramaplancomptable_line SET entity = ".((int) $conf->entity).", ".$values;
        $sql .= ", datec = '".$this->db->idate(dol_now())."', fk_user_author = ".((int) $user->id);
        if (!$this->db->query($sql)) {
            $this->error = $this->db->lasterror();
            return -1;
        }

        return (int) $this->db->last_insert_id(MAIN_DB_PREFIX."signaramaplancomptable_line");
    }

    /**
     * Marque une ligne à supprimer : elle disparaît du plan comptable à la prochaine synchronisation.
     *
     * @param  int  $id      rowid
     * @param  bool $restore true pour annuler la suppression
     * @return int           >0 si OK
     */
    public function markDeleted($id, $restore = false)
    {
        global $conf;

        $sql = "UPDATE ".MAIN_DB_PREFIX."signaramaplancomptable_line SET to_delete = ".($restore ? 0 : 1);
        $sql .= " WHERE entity = ".((int) $conf->entity)." AND rowid = ".((int) $id);
        if (!$this->db->query($sql)) {
            $this->error = $this->db->lasterror();
            return -1;
        }

        return 1;
    }

    // ------------------------------------------------------------------ export / import CSV

    /**
     * @return string Contenu CSV (séparateur ;) des lignes actives
     */
    public function exportCsv()
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, self::$csvColumns, ';', '"', '');
        foreach ($this->fetchAll() as $line) {
            if ($line->to_delete) {
                continue;
            }
            $row = array();
            foreach (array($line->pcg, $line->number, $line->label, $line->labelshort, $line->parent, $line->group) as $cell) {
                // Protection contre l'injection de formule à l'ouverture dans un tableur
                $row[] = preg_match('/^[=+\-@]/', $cell) ? "'".$cell : $cell;
            }
            fputcsv($handle, $row, ';', '"', '');
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }

    /**
     * Importe des lignes depuis un CSV. Rien n'est enregistré si une ligne est invalide.
     *
     * @param  string $content CSV
     * @param  User   $user    Utilisateur
     * @param  bool   $replace true : les lignes absentes du fichier sont marquées à supprimer
     * @return array           created, updated, marked, errors (liste de messages)
     */
    public function importCsv($content, $user, $replace = false)
    {
        global $conf, $langs;

        $result = array('created' => 0, 'updated' => 0, 'marked' => 0, 'errors' => array());

        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $firstline = strtok($content, "\r\n");
        $delimiter = substr_count($firstline, ';') >= substr_count($firstline, ',') ? ';' : ',';

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $content);
        rewind($handle);

        $header = fgetcsv($handle, 0, $delimiter, '"', '');
        $header = is_array($header) ? array_map('strtolower', array_map('trim', $header)) : array();
        foreach (array('pcg', 'number', 'label', 'group') as $required) {
            if (!in_array($required, $header, true)) {
                fclose($handle);
                $result['errors'][] = $langs->trans('SignaramaPCErrorImportHeader', implode(';', self::$csvColumns));
                return $result;
            }
        }

        $rows = array();
        $n = 1;
        while (($cells = fgetcsv($handle, 0, $delimiter, '"', '')) !== false) {
            $n++;
            if ($cells === array(null)) {
                continue; // ligne vide
            }
            $data = array();
            foreach ($header as $i => $name) {
                $value = isset($cells[$i]) ? $cells[$i] : '';
                $data[$name] = preg_match('/^\'[=+\-@]/', $value) ? substr($value, 1) : $value;
            }
            $error = $this->validate($data);
            if ($error) {
                $result['errors'][] = $langs->trans('SignaramaPCImportLine', $n).' : '.$error;
            }
            $rows[] = $data;
        }
        fclose($handle);

        if ($result['errors']) {
            return $result;
        }

        $this->db->begin();
        $seen = array();
        foreach ($rows as $data) {
            $key = $data['pcg'].'|'.$data['number'];
            $existing = $this->findByKey($data['pcg'], $data['number']);
            $id = $this->save($data, $user, $existing ? $existing->id : 0);
            if ($id < 0) {
                $this->db->rollback();
                $result['errors'][] = $this->error;
                return $result;
            }
            $seen[$key] = true;
            $result[$existing ? 'updated' : 'created']++;
        }
        if ($replace) {
            foreach ($this->fetchAll() as $line) {
                if (!$line->to_delete && empty($seen[$line->pcg.'|'.$line->number])) {
                    $this->markDeleted($line->id);
                    $result['marked']++;
                }
            }
        }
        $this->db->commit();

        return $result;
    }

    /**
     * @param  string      $pcg    Plan
     * @param  string      $number Numéro de compte
     * @return object|null Ligne du module
     */
    protected function findByKey($pcg, $number)
    {
        foreach ($this->fetchAll() as $line) {
            if ($line->pcg === $pcg && $line->number === $number) {
                return $line;
            }
        }

        return null;
    }

    // ------------------------------------------------------------------ plan comptable

    /**
     * @param  string      $pcg    Plan
     * @param  string      $number Numéro de compte
     * @return object|null Compte du plan (rowid, label, labelshort, pcg_type, account_parent, import_key)
     */
    protected function fetchPlanAccount($pcg, $number)
    {
        global $conf;

        $sql = "SELECT rowid, label, labelshort, pcg_type, account_parent, import_key FROM ".MAIN_DB_PREFIX."accounting_account";
        $sql .= " WHERE entity = ".((int) $conf->entity);
        $sql .= " AND fk_pcg_version = '".$this->db->escape($pcg)."'";
        $sql .= " AND account_number = '".$this->db->escape($number)."'";
        $resql = $this->db->query($sql);
        $obj = $resql ? $this->db->fetch_object($resql) : null;

        return $obj ? $obj : null;
    }

    /**
     * @param  object $line    Ligne du module
     * @param  object $account Compte du plan
     * @param  int    $parentId rowid attendu du père
     * @return bool            true si le compte du plan est identique à la ligne
     */
    protected function isSame($line, $account, $parentId)
    {
        return $account->label === $line->label
            && (string) $account->labelshort === $line->labelshort
            && $account->pcg_type === $line->group
            && (int) $account->account_parent === (int) $parentId;
    }

    /**
     * Etat de chaque ligne par rapport au plan, sans rien modifier.
     *
     * @return array Liste de array('line' => object, 'state' => STATE_*)
     */
    public function getStates()
    {
        $states = array();
        foreach ($this->fetchAll() as $line) {
            if ($line->to_delete) {
                $state = self::STATE_TODELETE;
            } else {
                $parentId = 0;
                $state = null;
                if ($line->parent !== '') {
                    $parent = $this->fetchPlanAccount($line->pcg, $line->parent);
                    if ($parent) {
                        $parentId = (int) $parent->rowid;
                    } else {
                        $state = self::STATE_NOPARENT;
                    }
                }
                if ($state === null) {
                    $account = $this->fetchPlanAccount($line->pcg, $line->number);
                    if (!$account) {
                        $state = self::STATE_MISSING;
                    } else {
                        $state = $this->isSame($line, $account, $parentId) ? self::STATE_UPTODATE : self::STATE_DIFFERENT;
                    }
                }
            }
            $states[] = array('line' => $line, 'state' => $state);
        }

        return $states;
    }

    /**
     * Synchronise le plan comptable avec la liste du module.
     *
     * @param  User $user Utilisateur
     * @return array      Liste de array('line' => object, 'status' => SYNC_*, 'info' => string)
     */
    public function sync($user)
    {
        global $conf, $langs;

        $lines = $this->fetchAll();
        $toDelete = array();
        $toUpsert = array();
        foreach ($lines as $line) {
            if ($line->to_delete) {
                $toDelete[] = $line;
            } else {
                $toUpsert[] = $line;
            }
        }
        // Suppressions : sous-comptes d'abord. Créations / mises à jour : comptes pères d'abord.
        usort($toDelete, function ($a, $b) {
            return strlen($b->number) - strlen($a->number);
        });
        usort($toUpsert, function ($a, $b) {
            return strlen($a->number) - strlen($b->number) ?: strcmp($a->number, $b->number);
        });

        $results = array();
        $this->db->begin();

        foreach ($toDelete as $line) {
            $results[] = $this->syncDelete($line);
        }
        foreach ($toUpsert as $line) {
            $results[] = $this->syncUpsert($line, $user);
        }

        $this->db->commit();

        return $results;
    }

    /**
     * @param  object $line Ligne marquée à supprimer
     * @return array
     */
    protected function syncDelete($line)
    {
        global $conf, $langs;

        $account = $this->fetchPlanAccount($line->pcg, $line->number);

        if ($account && $account->import_key !== self::IMPORT_KEY) {
            // Compte du plan d'origine : on ne le supprime jamais, on retire seulement la ligne du module
            $this->removeLine($line->id);
            return array('line' => $line, 'status' => self::SYNC_KEPT, 'info' => $langs->trans('SignaramaPCKeptNotOwned'));
        }

        if ($account) {
            $sql = "SELECT COUNT(*) as nb FROM ".MAIN_DB_PREFIX."accounting_account";
            $sql .= " WHERE entity = ".((int) $conf->entity)." AND account_parent = ".((int) $account->rowid);
            $obj = ($resql = $this->db->query($sql)) ? $this->db->fetch_object($resql) : null;
            if (!$obj || $obj->nb > 0) {
                return array('line' => $line, 'status' => self::SYNC_KEPT, 'info' => $langs->trans('SignaramaPCKeptChildren'));
            }

            $sql = "SELECT COUNT(*) as nb FROM ".MAIN_DB_PREFIX."accounting_bookkeeping";
            $sql .= " WHERE entity = ".((int) $conf->entity)." AND numero_compte = '".$this->db->escape($line->number)."'";
            $obj = ($resql = $this->db->query($sql)) ? $this->db->fetch_object($resql) : null;
            if (!$obj || $obj->nb > 0) {
                return array('line' => $line, 'status' => self::SYNC_KEPT, 'info' => $langs->trans('SignaramaPCKeptBookkeeping'));
            }

            if (!$this->db->query("DELETE FROM ".MAIN_DB_PREFIX."accounting_account WHERE rowid = ".((int) $account->rowid))) {
                $this->error = $this->db->lasterror();
                return array('line' => $line, 'status' => self::SYNC_ERROR, 'info' => $this->error);
            }
        }

        $this->removeLine($line->id);

        return array('line' => $line, 'status' => self::SYNC_DELETED, 'info' => '');
    }

    /**
     * @param  object $line Ligne active
     * @param  User   $user Utilisateur
     * @return array
     */
    protected function syncUpsert($line, $user)
    {
        global $conf;

        $parentId = 0;
        if ($line->parent !== '') {
            $parent = $this->fetchPlanAccount($line->pcg, $line->parent);
            if (!$parent) {
                return array('line' => $line, 'status' => self::SYNC_NOPARENT, 'info' => $line->parent);
            }
            $parentId = (int) $parent->rowid;
        }

        $account = $this->fetchPlanAccount($line->pcg, $line->number);
        $labelshort = $line->labelshort === '' ? "NULL" : "'".$this->db->escape($line->labelshort)."'";

        if ($account) {
            if ($this->isSame($line, $account, $parentId)) {
                return array('line' => $line, 'status' => self::SYNC_UNCHANGED, 'info' => '');
            }
            $sql = "UPDATE ".MAIN_DB_PREFIX."accounting_account SET";
            $sql .= " label = '".$this->db->escape($line->label)."', labelshort = ".$labelshort.",";
            $sql .= " pcg_type = '".$this->db->escape($line->group)."', account_parent = ".$parentId.",";
            $sql .= " fk_user_modif = ".((int) $user->id);
            $sql .= " WHERE rowid = ".((int) $account->rowid);
            $status = self::SYNC_UPDATED;
        } else {
            $sql = "INSERT INTO ".MAIN_DB_PREFIX."accounting_account";
            $sql .= " (entity, datec, fk_pcg_version, pcg_type, account_number, account_parent, label, labelshort, fk_user_author, active, import_key)";
            $sql .= " VALUES (".((int) $conf->entity).", '".$this->db->idate(dol_now())."',";
            $sql .= " '".$this->db->escape($line->pcg)."', '".$this->db->escape($line->group)."',";
            $sql .= " '".$this->db->escape($line->number)."', ".$parentId.",";
            $sql .= " '".$this->db->escape($line->label)."', ".$labelshort.", ".((int) $user->id).", 1, '".self::IMPORT_KEY."')";
            $status = self::SYNC_CREATED;
        }

        if (!$this->db->query($sql)) {
            $this->error = $this->db->lasterror();
            return array('line' => $line, 'status' => self::SYNC_ERROR, 'info' => $this->error);
        }

        return array('line' => $line, 'status' => $status, 'info' => '');
    }

    /**
     * @param int $id rowid de la ligne du module
     */
    protected function removeLine($id)
    {
        $this->db->query("DELETE FROM ".MAIN_DB_PREFIX."signaramaplancomptable_line WHERE rowid = ".((int) $id));
    }
}
