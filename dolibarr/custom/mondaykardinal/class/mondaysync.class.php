<?php
/**
 * Synchronisation Dolibarr -> monday.com (sens unique, polling) :
 *  - devis et factures clients -> board « Devis & Factures »
 *  - commandes clients validées -> board « Suivi de production »
 *
 * Clé de rapprochement : colonne « ID Dolibarr » du board, valeur PROPAL-<id>, FACTURE-<id> ou COMMANDE-<id>.
 * L'id Dolibarr ne change jamais (contrairement à la réf. provisoire (PROV12) d'un brouillon).
 *
 * Sur le board de production, la synchro ne touche qu'aux colonnes venant de Dolibarr (réf., montant, date, lien).
 * L'étape de production, le responsable, le planning et les notes restent à l'atelier : l'étape n'est
 * renseignée qu'à la création de l'élément.
 */

require_once __DIR__.'/mondayapi.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';

class MondaySync
{
	const PREFIX = 'MONDAYKARDINAL_';
	const MAX_WRITES_PER_RUN = 200;
	const LOCK_TTL = 900;

	/** À incrémenter pour forcer le renvoi de tous les documents au prochain passage */
	const HASH_VERSION = 2;

	/** @var DoliDB */
	public $db;

	/** @var string */
	public $error = '';

	/** @var string Résumé de la dernière exécution (affiché dans la liste des tâches planifiées) */
	public $output = '';

	/** @var MondayApi|null Injectable pour les tests */
	public $api;

	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * Valeurs par défaut des réglages (workspace, boards et colonnes actuels de Kardinal).
	 */
	public static function defaults()
	{
		return array(
			'TOKEN' => '',
			'API_VERSION' => '',
			'WORKSPACE_ID' => '8117079',
			'CONTACTS_BOARD_ID' => '5107069596',
			'DEALS_BOARD_ID' => '5107069598',
			'SYNC_PROPAL' => '1',
			'SYNC_INVOICE' => '1',
			'SYNC_DRAFTS' => '0',
			'SYNC_ORDERS' => '1',

			// Board « Devis & Factures »
			'BOARD_ID' => '5107224931',
			'GROUP_ID' => '',
			'COL_DOLI_ID' => 'text_mm8t2p30',
			'COL_REF' => 'text_mm8tpn8n',
			'COL_TYPE' => 'color_mm8sdj1s',
			'COL_STATUS' => 'color_mm8sf3pg',
			'COL_HT' => 'numeric_mm8se9ca',
			'COL_TVA' => 'numeric_mm8sq775',
			'COL_TTC' => 'numeric_mm8s98s2',
			'COL_DATE' => 'date_mm8srmth',
			'COL_DUE' => 'date_mm8sxgas',
			'COL_EMAIL' => 'email_mm8syxgm',
			'COL_DETAIL' => 'long_text_mm8szd38',
			'LBL_TYPE_PROPAL' => 'Devis',
			'LBL_TYPE_INVOICE' => 'Facture',
			'LBL_P_DRAFT' => 'En cours',
			'LBL_P_OPEN' => 'Envoyé',
			'LBL_P_SIGNED' => 'Accepté',
			'LBL_P_REFUSED' => 'Refusé',
			'LBL_P_BILLED' => 'Fait',
			'LBL_I_DRAFT' => 'En cours',
			'LBL_I_UNPAID' => 'En attente de paiement',
			'LBL_I_LATE' => 'En retard',
			'LBL_I_PAID' => 'Payée',
			'LBL_I_ABANDONED' => 'Bloqué',

			// Board « Suivi de production »
			'PROD_BOARD_ID' => '5107252543',
			'PROD_GROUP_ID' => '',
			'PROD_COL_DOLI_ID' => 'text_mm8tezwa',
			'PROD_COL_REF' => 'text_mm8t5x1v',
			'PROD_COL_STEP' => 'color_mm8tje02',
			'PROD_COL_AMOUNT' => 'numeric_mm8t1vzf',
			'PROD_COL_DELIVERY' => 'date_mm8twm4j',
			'PROD_COL_LINK' => 'link_mm8tp2m3',
			'PROD_STEP_INITIAL' => 'BAT en cours',
		);
	}

	/**
	 * Valeur d'un réglage (constante Dolibarr, ou défaut si jamais enregistrée).
	 */
	public static function param($key)
	{
		$defaults = self::defaults();

		return trim(getDolGlobalString(self::PREFIX.$key, isset($defaults[$key]) ? $defaults[$key] : ''));
	}

	/**
	 * Point d'entrée de la tâche planifiée.
	 *
	 * @return int 0 si OK, 1 en cas d'erreur
	 */
	public function runCron()
	{
		$result = $this->run();

		return $result < 0 ? 1 : 0;
	}

	/**
	 * Lance une synchronisation.
	 *
	 * @return int >= 0 si OK, < 0 en cas d'erreur (message dans $this->error)
	 */
	public function run()
	{
		global $conf;

		$this->error = '';
		$this->output = '';

		if (self::param('TOKEN') === '') {
			$this->output = 'Module non configuré (jeton monday manquant).';
			return 0;
		}
		if (!$this->acquireLock()) {
			$this->output = 'Synchronisation déjà en cours, exécution ignorée.';
			return 0;
		}

		try {
			$result = $this->doRun();
		} finally {
			$this->releaseLock();
		}

		$summary = dol_print_date(dol_now(), 'dayhour').' - '.$this->output;
		dolibarr_set_const($this->db, self::PREFIX.'LAST_RUN', $summary, 'chaine', 0, '', $conf->entity);

		return $result;
	}

	private function doRun()
	{
		$api = $this->api ? $this->api : new MondayApi(self::param('TOKEN'), self::param('API_VERSION'));

		// Un « job » = un board cible et les documents Dolibarr à y envoyer
		$jobs = array();

		$quotes = array();
		if (self::param('SYNC_PROPAL') === '1') {
			$quotes = array_merge($quotes, $this->loadPropals());
		}
		if (self::param('SYNC_INVOICE') === '1') {
			$quotes = array_merge($quotes, $this->loadInvoices());
		}
		if ((self::param('SYNC_PROPAL') === '1' || self::param('SYNC_INVOICE') === '1')) {
			$jobs[] = array('label' => 'Devis & Factures', 'board' => self::param('BOARD_ID'), 'group' => self::param('GROUP_ID'), 'doli_col' => self::param('COL_DOLI_ID'), 'docs' => $quotes);
		}
		if (self::param('SYNC_ORDERS') === '1') {
			$jobs[] = array('label' => 'Suivi de production', 'board' => self::param('PROD_BOARD_ID'), 'group' => self::param('PROD_GROUP_ID'), 'doli_col' => self::param('PROD_COL_DOLI_ID'), 'docs' => $this->loadOrders());
		}

		$maps = $this->loadMaps();
		$stats = array('created' => 0, 'updated' => 0, 'unchanged' => 0, 'errors' => 0, 'pending' => 0);
		$errors = array();
		$writes = 0;

		foreach ($jobs as $job) {
			if ($job['board'] === '' || $job['doli_col'] === '') {
				$errors[] = $job['label'].' : board ou colonne « ID Dolibarr » non configuré (voir « Créer / réparer les boards »).';
				$stats['errors']++;
				continue;
			}

			foreach ($job['docs'] as $doc) {
				$item = $this->buildItem($doc);
				// Le board fait partie de l'empreinte : si un board est recréé, tous ses documents sont renvoyés
				$hash = sha1(self::HASH_VERSION.'|'.$job['board'].'|'.json_encode($item['update'], JSON_UNESCAPED_UNICODE));
				$map = isset($maps[$doc['type']][$doc['id']]) ? $maps[$doc['type']][$doc['id']] : null;

				if ($map && $map['hash'] === $hash) {
					$stats['unchanged']++;
					continue;
				}
				if ($writes >= self::MAX_WRITES_PER_RUN) {
					$stats['pending']++;
					continue;
				}
				$writes++;

				$pushed = $this->push($api, $job, $doc, $item, $map ? $map['item'] : '');
				if ($pushed === false) {
					$stats['errors']++;
					$errors[] = $doc['ref'].' : '.$api->error;
					continue;
				}

				$this->saveMap($doc['type'], $doc['id'], $pushed['item'], $hash);
				$stats[$pushed['action']]++;
			}
		}

		$this->output = $stats['created'].' créé(s), '.$stats['updated'].' mis à jour, '.$stats['unchanged'].' inchangé(s)';
		if ($stats['pending'] > 0) {
			$this->output .= ', '.$stats['pending'].' en attente (prochain passage)';
		}
		if ($stats['errors'] > 0) {
			$this->error = implode(' | ', array_slice($errors, 0, 5));
			$this->output .= ', '.$stats['errors'].' erreur(s) : '.$this->error;
			return -1;
		}

		return $stats['created'] + $stats['updated'];
	}

	/**
	 * Crée ou met à jour l'élément monday d'un document.
	 *
	 * @return array|false array('action' => 'created'|'updated', 'item' => id) ou false
	 */
	private function push(MondayApi $api, array $job, array $doc, array $item, $itemId)
	{
		if ($itemId !== '') {
			if ($api->updateItem($job['board'], $itemId, $item['update'])) {
				return array('action' => 'updated', 'item' => $itemId);
			}
			// Échec de la mise à jour (élément supprimé, archivé, ou board recréé) : on le cherche par son
			// ID Dolibarr, puis on le recrée. Une vraie panne (jeton, réseau) fera aussi échouer la recherche.
		}

		// Évite les doublons si l'élément existe déjà dans monday (ID Dolibarr saisi à la main, table de correspondance vidée...)
		$existing = $api->findItemByColumn($job['board'], $job['doli_col'], $doc['key']);
		if ($existing === false) {
			return false;
		}
		if ($existing !== null) {
			return $api->updateItem($job['board'], $existing, $item['update']) ? array('action' => 'updated', 'item' => $existing) : false;
		}

		$created = $api->createItem($job['board'], $job['group'], $item['name'], $item['create']);

		return $created === false ? false : array('action' => 'created', 'item' => $created);
	}

	/**
	 * Construit le nom de l'élément monday et les valeurs de colonnes d'un document.
	 *
	 * - 'create' : valeurs envoyées à la création ;
	 * - 'update' : valeurs envoyées aux mises à jour (et base de l'empreinte de changement).
	 *
	 * @return array array('name' => string, 'create' => array, 'update' => array)
	 */
	public function buildItem(array $doc)
	{
		$name = $doc['ref'].($doc['client'] !== '' ? ' - '.$doc['client'] : '');

		if ($doc['type'] === 'commande') {
			return $this->buildOrderItem($doc, $name);
		}

		$values = array();
		$this->setValue($values, 'COL_DOLI_ID', $doc['key']);
		$this->setValue($values, 'COL_REF', $doc['ref']);
		$this->setValue($values, 'COL_TYPE', array('label' => self::param($doc['type'] === 'propal' ? 'LBL_TYPE_PROPAL' : 'LBL_TYPE_INVOICE')));
		$this->setValue($values, 'COL_STATUS', array('label' => $this->statusLabel($doc)));
		$this->setValue($values, 'COL_HT', number_format((float) $doc['total_ht'], 2, '.', ''));
		$this->setValue($values, 'COL_TVA', number_format((float) $doc['total_tva'], 2, '.', ''));
		$this->setValue($values, 'COL_TTC', number_format((float) $doc['total_ttc'], 2, '.', ''));
		if ($doc['date'] !== '') {
			$this->setValue($values, 'COL_DATE', array('date' => $doc['date']));
		}
		if ($doc['due'] !== '') {
			$this->setValue($values, 'COL_DUE', array('date' => $doc['due']));
		}
		if ($doc['email'] !== '') {
			$this->setValue($values, 'COL_EMAIL', array('email' => $doc['email'], 'text' => $doc['email']));
		}
		if ($doc['detail'] !== '') {
			$this->setValue($values, 'COL_DETAIL', array('text' => $doc['detail']));
		}

		return array('name' => $name, 'create' => $values, 'update' => $values + array('name' => $name));
	}

	private function buildOrderItem(array $doc, $name)
	{
		$values = array();
		$this->setValue($values, 'PROD_COL_DOLI_ID', $doc['key']);
		$this->setValue($values, 'PROD_COL_REF', $doc['ref']);
		$this->setValue($values, 'PROD_COL_AMOUNT', number_format((float) $doc['total_ht'], 2, '.', ''));
		if ($doc['due'] !== '') {
			$this->setValue($values, 'PROD_COL_DELIVERY', array('date' => $doc['due']));
		}
		if ($doc['url'] !== '') {
			$this->setValue($values, 'PROD_COL_LINK', array('url' => $doc['url'], 'text' => $doc['ref']));
		}

		// L'étape de production appartient à l'atelier : renseignée seulement à la création
		$create = $values;
		$this->setValue($create, 'PROD_COL_STEP', array('label' => self::param('PROD_STEP_INITIAL')));

		return array('name' => $name, 'create' => $create, 'update' => $values);
	}

	private function setValue(array &$values, $paramKey, $value)
	{
		$columnId = self::param($paramKey);
		if ($columnId !== '') {
			$values[$columnId] = $value;
		}
	}

	/**
	 * Libellé de statut monday d'un devis ou d'une facture (réglable dans la page de configuration).
	 */
	public function statusLabel(array $doc)
	{
		$status = (int) $doc['status'];

		if ($doc['type'] === 'propal') {
			$keys = array(0 => 'LBL_P_DRAFT', 1 => 'LBL_P_OPEN', 2 => 'LBL_P_SIGNED', 3 => 'LBL_P_REFUSED', 4 => 'LBL_P_BILLED');

			return self::param(isset($keys[$status]) ? $keys[$status] : 'LBL_P_OPEN');
		}

		if ($status === 3) {
			return self::param('LBL_I_ABANDONED');
		}
		if ($status === 2 || !empty($doc['paid'])) {
			return self::param('LBL_I_PAID');
		}
		if ($status === 1) {
			$late = $doc['due'] !== '' && $doc['due'] < date('Y-m-d');

			return self::param($late ? 'LBL_I_LATE' : 'LBL_I_UNPAID');
		}

		return self::param('LBL_I_DRAFT');
	}

	/**
	 * Devis Dolibarr à synchroniser.
	 */
	public function loadPropals()
	{
		$sql = "SELECT p.rowid, p.ref, p.fk_statut, p.total_ht, p.total_tva, p.total_ttc, p.datep, p.fin_validite, s.nom, s.email";
		$sql .= " FROM ".MAIN_DB_PREFIX."propal as p LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON s.rowid = p.fk_soc";
		$sql .= " WHERE p.entity IN (".getEntity('propal').")";
		if (self::param('SYNC_DRAFTS') !== '1') {
			$sql .= " AND p.fk_statut > 0";
		}
		$sql .= " ORDER BY p.rowid ASC";

		$lines = $this->loadLines('propaldet', 'fk_propal');
		$docs = array();
		$resql = $this->db->query($sql);
		if (!$resql) {
			dol_syslog(__METHOD__.' '.$this->db->lasterror(), LOG_ERR);
			return $docs;
		}
		while ($obj = $this->db->fetch_object($resql)) {
			$docs[] = array(
				'type' => 'propal',
				'id' => (int) $obj->rowid,
				'key' => 'PROPAL-'.(int) $obj->rowid,
				'ref' => (string) $obj->ref,
				'status' => (int) $obj->fk_statut,
				'paid' => false,
				'client' => (string) $obj->nom,
				'email' => (string) $obj->email,
				'total_ht' => $obj->total_ht,
				'total_tva' => $obj->total_tva,
				'total_ttc' => $obj->total_ttc,
				'date' => $this->day($obj->datep),
				'due' => $this->day($obj->fin_validite),
				'detail' => isset($lines[$obj->rowid]) ? $lines[$obj->rowid] : '',
			);
		}
		$this->db->free($resql);

		return $docs;
	}

	/**
	 * Factures clients Dolibarr à synchroniser.
	 */
	public function loadInvoices()
	{
		$sql = "SELECT f.rowid, f.ref, f.fk_statut, f.paye, f.total_ht, f.total_tva, f.total_ttc, f.datef, f.date_lim_reglement, s.nom, s.email";
		$sql .= " FROM ".MAIN_DB_PREFIX."facture as f LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON s.rowid = f.fk_soc";
		$sql .= " WHERE f.entity IN (".getEntity('invoice').")";
		if (self::param('SYNC_DRAFTS') !== '1') {
			$sql .= " AND f.fk_statut > 0";
		}
		$sql .= " ORDER BY f.rowid ASC";

		$lines = $this->loadLines('facturedet', 'fk_facture');
		$docs = array();
		$resql = $this->db->query($sql);
		if (!$resql) {
			dol_syslog(__METHOD__.' '.$this->db->lasterror(), LOG_ERR);
			return $docs;
		}
		while ($obj = $this->db->fetch_object($resql)) {
			$docs[] = array(
				'type' => 'facture',
				'id' => (int) $obj->rowid,
				'key' => 'FACTURE-'.(int) $obj->rowid,
				'ref' => (string) $obj->ref,
				'status' => (int) $obj->fk_statut,
				'paid' => !empty($obj->paye),
				'client' => (string) $obj->nom,
				'email' => (string) $obj->email,
				'total_ht' => $obj->total_ht,
				'total_tva' => $obj->total_tva,
				'total_ttc' => $obj->total_ttc,
				'date' => $this->day($obj->datef),
				'due' => $this->day($obj->date_lim_reglement),
				'detail' => isset($lines[$obj->rowid]) ? $lines[$obj->rowid] : '',
			);
		}
		$this->db->free($resql);

		return $docs;
	}

	/**
	 * Commandes clients validées (ni brouillons ni annulées) à envoyer au suivi de production.
	 */
	public function loadOrders()
	{
		$sql = "SELECT c.rowid, c.ref, c.fk_statut, c.total_ht, c.date_livraison, s.nom";
		$sql .= " FROM ".MAIN_DB_PREFIX."commande as c LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON s.rowid = c.fk_soc";
		$sql .= " WHERE c.entity IN (".getEntity('commande').") AND c.fk_statut > 0";
		$sql .= " ORDER BY c.rowid ASC";

		$root = defined('DOL_MAIN_URL_ROOT') ? rtrim(DOL_MAIN_URL_ROOT, '/') : '';
		$docs = array();
		$resql = $this->db->query($sql);
		if (!$resql) {
			dol_syslog(__METHOD__.' '.$this->db->lasterror(), LOG_ERR);
			return $docs;
		}
		while ($obj = $this->db->fetch_object($resql)) {
			$docs[] = array(
				'type' => 'commande',
				'id' => (int) $obj->rowid,
				'key' => 'COMMANDE-'.(int) $obj->rowid,
				'ref' => (string) $obj->ref,
				'status' => (int) $obj->fk_statut,
				'client' => (string) $obj->nom,
				'total_ht' => $obj->total_ht,
				'due' => $this->day($obj->date_livraison),
				'url' => $root !== '' ? $root.'/commande/card.php?id='.(int) $obj->rowid : '',
			);
		}
		$this->db->free($resql);

		return $docs;
	}

	/**
	 * Lignes d'un type de document, regroupées par document : "qté x libellé", une ligne par prestation.
	 *
	 * @return array id document => texte
	 */
	private function loadLines($table, $fk)
	{
		$sql = "SELECT d.".$fk." as docid, d.qty, d.label, d.description, pr.label as product_label";
		$sql .= " FROM ".MAIN_DB_PREFIX.$table." as d LEFT JOIN ".MAIN_DB_PREFIX."product as pr ON pr.rowid = d.fk_product";
		$sql .= " ORDER BY d.".$fk." ASC, d.rang ASC, d.rowid ASC";

		$result = array();
		$resql = $this->db->query($sql);
		if (!$resql) {
			dol_syslog(__METHOD__.' '.$this->db->lasterror(), LOG_ERR);
			return $result;
		}
		while ($obj = $this->db->fetch_object($resql)) {
			$label = $obj->product_label !== null && $obj->product_label !== '' ? $obj->product_label : ($obj->label ? $obj->label : $this->firstLine($obj->description));
			$label = trim($this->plain($label));
			if ($label === '') {
				continue;
			}
			$qty = rtrim(rtrim(number_format((float) $obj->qty, 3, '.', ''), '0'), '.');
			$result[$obj->docid][] = $qty.' x '.$label;
		}
		$this->db->free($resql);

		foreach ($result as $docid => $parts) {
			$result[$docid] = mb_substr(implode("\n", $parts), 0, 2000);
		}

		return $result;
	}

	private function firstLine($html)
	{
		$text = trim($this->plain($html));
		$parts = preg_split('/\R/', $text);

		return isset($parts[0]) ? $parts[0] : '';
	}

	private function plain($html)
	{
		$text = str_ireplace(array('<br>', '<br/>', '<br />', '</p>'), "\n", (string) $html);

		return html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
	}

	private function day($value)
	{
		return $value ? substr((string) $value, 0, 10) : '';
	}

	/**
	 * @return array [doc_type][doc_id] => array('item' => ..., 'hash' => ...)
	 */
	private function loadMaps()
	{
		global $conf;

		$maps = array();
		$sql = "SELECT doc_type, doc_id, monday_item_id, hash FROM ".MAIN_DB_PREFIX."mondaykardinal_map WHERE entity = ".((int) $conf->entity);
		$resql = $this->db->query($sql);
		if ($resql) {
			while ($obj = $this->db->fetch_object($resql)) {
				$maps[$obj->doc_type][(int) $obj->doc_id] = array('item' => (string) $obj->monday_item_id, 'hash' => (string) $obj->hash);
			}
			$this->db->free($resql);
		}

		return $maps;
	}

	private function saveMap($type, $id, $itemId, $hash)
	{
		global $conf;

		$where = " WHERE entity = ".((int) $conf->entity)." AND doc_type = '".$this->db->escape($type)."' AND doc_id = ".((int) $id);
		$this->db->query("DELETE FROM ".MAIN_DB_PREFIX."mondaykardinal_map".$where);
		$this->db->query("INSERT INTO ".MAIN_DB_PREFIX."mondaykardinal_map (entity, doc_type, doc_id, monday_item_id, hash, date_sync) VALUES ("
			.((int) $conf->entity).", '".$this->db->escape($type)."', ".((int) $id).", '".$this->db->escape($itemId)."', '".$this->db->escape($hash)."', '".$this->db->idate(dol_now())."')");
	}

	private function acquireLock()
	{
		global $conf;

		$lock = (int) getDolGlobalString(self::PREFIX.'LOCK', '0');
		if ($lock > 0 && (dol_now() - $lock) < self::LOCK_TTL) {
			return false;
		}
		dolibarr_set_const($this->db, self::PREFIX.'LOCK', (string) dol_now(), 'chaine', 0, '', $conf->entity);

		return true;
	}

	private function releaseLock()
	{
		global $conf;

		dolibarr_set_const($this->db, self::PREFIX.'LOCK', '0', 'chaine', 0, '', $conf->entity);
	}
}
