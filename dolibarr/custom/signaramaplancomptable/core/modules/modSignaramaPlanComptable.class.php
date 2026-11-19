<?php
/* Copyright (C) 2026 Signarama
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

/**
 * Description and activation class for module SignaramaPlanComptable
 */
class modSignaramaPlanComptable extends DolibarrModules
{
    /**
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;

        $this->numero = 490420;
        $this->rights_class = 'signaramaplancomptable';
        $this->family = 'financial';
        $this->name = preg_replace('/^mod/i', '', get_class($this));
        $this->description = 'Ajoute au plan comptable des comptes supplémentaires propres à Signarama';
        $this->version = '1.0.0';
        $this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
        $this->special = 0;
        $this->picto = 'generic';

        $this->module_parts = array();
        $this->dirs = array();
        $this->config_page_url = array('setup.php@signaramaplancomptable');

        $this->hidden = false;
        $this->depends = array('modAccounting');
        $this->requiredby = array();
        $this->conflictwith = array();
        $this->phpmin = array(7, 4);
        $this->need_dolibarr_version = array(19, 0);
        $this->langfiles = array('signaramaplancomptable@signaramaplancomptable');

        $this->const = array();
        $this->tabs = array();
        $this->dictionaries = array();
        $this->boxes = array();
        $this->rights = array();
        $this->menu = array();
    }

    /**
     * Function called when module is enabled: creates the table and seeds the example account.
     *
     * @param  string $options Options when enabling module ('', 'noboxes')
     * @return int             1 if OK, 0 if KO
     */
    public function init($options = '')
    {
        global $conf, $user;

        $this->_load_tables('/signaramaplancomptable/sql/');

        // Compte d'exemple (une seule fois) : le plan n'est modifié qu'au clic sur "Synchroniser"
        $sql = array();
        if (!getDolGlobalInt('SIGNARAMAPC_SEEDED')) {
            $sql[] = "INSERT INTO ".MAIN_DB_PREFIX."signaramaplancomptable_line"
                ." (entity, fk_pcg_version, account_number, label, labelshort, parent_number, pcg_type, datec, fk_user_author)"
                ." VALUES (".((int) $conf->entity).", 'PCG25-DEV', '701100', 'DEEE', 'Taxe sur les Déchets Elec', '701', 'INCOME',"
                ." '".$this->db->idate(dol_now())."', ".((int) $user->id).")";
            $this->const = array(array('SIGNARAMAPC_SEEDED', 'chaine', '1', '', 0, 'current', 0));
        }

        return $this->_init($sql, $options);
    }

    /**
     * Function called when module is disabled. Accounts are kept on purpose:
     * they may already be used by bookkeeping entries.
     *
     * @param  string $options Options when disabling module ('', 'noboxes')
     * @return int             1 if OK, 0 if KO
     */
    public function remove($options = '')
    {
        return $this->_remove(array(), $options);
    }
}
