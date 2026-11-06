<?php
/**
 * Module descriptor : Monday Kardinal
 * Synchronise les devis et factures Dolibarr vers un board monday.com (polling via tâche planifiée).
 */

include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

class modMondayKardinal extends DolibarrModules
{
	public function __construct($db)
	{
		$this->db = $db;

		$this->numero = 490410;
		$this->rights_class = 'mondaykardinal';
		$this->family = 'interface';
		$this->module_position = '90';
		$this->name = preg_replace('/^mod/i', '', get_class($this));
		$this->description = 'Synchronise les devis, factures et commandes vers des boards monday.com';
		$this->descriptionlong = 'Envoie les devis et factures Dolibarr vers un board monday.com, via une tâche planifiée (polling). Configuration dans la page de réglages du module.';
		$this->version = '1.0.0';
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		$this->picto = 'generic';

		$this->module_parts = array();
		$this->dirs = array();
		$this->config_page_url = array('setup.php@mondaykardinal');

		$this->hidden = false;
		$this->depends = array('modPropale', 'modFacture');
		$this->requiredby = array();
		$this->conflictwith = array();
		$this->phpmin = array(8, 1);
		$this->need_dolibarr_version = array(19, 0);
		$this->langfiles = array('mondaykardinal@mondaykardinal');

		$this->const = array();
		$this->rights = array();
		$this->menu = array();

		// Tâche planifiée : toutes les 5 minutes (nécessite DOLI_CRON=1 dans le conteneur, voir README)
		$this->cronjobs = array(
			0 => array(
				'label' => 'MondayKardinalSyncJob',
				'jobtype' => 'method',
				'class' => '/mondaykardinal/class/mondaysync.class.php',
				'objectname' => 'MondaySync',
				'method' => 'runCron',
				'parameters' => '',
				'comment' => 'Synchronise les devis, factures et commandes vers monday.com',
				'frequency' => 5,
				'unitfrequency' => 60,
				'priority' => 60,
				'status' => 1,
				'test' => 'isModEnabled("mondaykardinal")',
			),
		);
	}

	public function init($options = '')
	{
		$result = $this->_load_tables('/mondaykardinal/sql/');
		if ($result < 0) {
			return -1;
		}

		return $this->_init(array(), $options);
	}

	public function remove($options = '')
	{
		return $this->_remove(array(), $options);
	}
}
