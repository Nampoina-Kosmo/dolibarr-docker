<?php
/**
 * Définition des boards monday attendus par le module, contrôle de leur configuration
 * et création / réparation à la demande.
 */

require_once __DIR__.'/mondaysync.class.php';

class MondayBoards
{
	const OK = 'ok';
	const WARN = 'warn';
	const ERR = 'error';

	/**
	 * Boards attendus. 'param' : réglage où l'identifiant de colonne est enregistré (null = colonne
	 * laissée à l'atelier, jamais écrite par la synchro : on s'assure seulement qu'elle existe).
	 *
	 * @return array
	 */
	public static function specs()
	{
		$p = array('MondaySync', 'param');

		$devis = array(
			'title' => 'Devis & Factures',
			'description' => 'Suivi des devis et factures clients, alimenté depuis Dolibarr.',
			'board_param' => 'BOARD_ID',
			'enabled' => $p('SYNC_PROPAL') === '1' || $p('SYNC_INVOICE') === '1',
			'columns' => array(
				array('param' => null, 'title' => 'Client', 'type' => 'board_relation', 'link_param' => 'CONTACTS_BOARD_ID'),
				array('param' => null, 'title' => 'Deal', 'type' => 'board_relation', 'link_param' => 'DEALS_BOARD_ID'),
				array('param' => 'COL_DOLI_ID', 'title' => 'ID Dolibarr', 'type' => 'text', 'required' => true),
				array('param' => 'COL_REF', 'title' => 'Réf. Dolibarr', 'type' => 'text'),
				array('param' => 'COL_TYPE', 'title' => 'Type de document', 'type' => 'status', 'labels' => array($p('LBL_TYPE_PROPAL'), $p('LBL_TYPE_INVOICE'))),
				array('param' => 'COL_STATUS', 'title' => 'Statut', 'type' => 'status', 'labels' => array(
					$p('LBL_P_DRAFT'), $p('LBL_P_OPEN'), $p('LBL_P_SIGNED'), $p('LBL_P_REFUSED'), $p('LBL_P_BILLED'),
					$p('LBL_I_DRAFT'), $p('LBL_I_UNPAID'), $p('LBL_I_LATE'), $p('LBL_I_PAID'), $p('LBL_I_ABANDONED'),
				)),
				array('param' => 'COL_HT', 'title' => 'Montant HT (€)', 'type' => 'numbers'),
				array('param' => 'COL_TVA', 'title' => 'TVA (€)', 'type' => 'numbers'),
				array('param' => 'COL_TTC', 'title' => 'Montant TTC (€)', 'type' => 'numbers'),
				array('param' => 'COL_DATE', 'title' => "Date d'émission", 'type' => 'date'),
				array('param' => 'COL_DUE', 'title' => 'Échéance / Validité', 'type' => 'date'),
				array('param' => 'COL_EMAIL', 'title' => 'Email client', 'type' => 'email'),
				array('param' => 'COL_DETAIL', 'title' => 'Détail des prestations', 'type' => 'long_text'),
			),
		);

		$prod = array(
			'title' => 'Suivi de production',
			'description' => 'Suivi de la production (BAT, fabrication, pose) pour les commandes issues de Dolibarr.',
			'board_param' => 'PROD_BOARD_ID',
			'enabled' => $p('SYNC_ORDERS') === '1',
			'columns' => array(
				array('param' => null, 'title' => 'Client', 'type' => 'board_relation', 'link_param' => 'CONTACTS_BOARD_ID'),
				array('param' => null, 'title' => 'Deal', 'type' => 'board_relation', 'link_param' => 'DEALS_BOARD_ID'),
				array('param' => 'PROD_COL_DOLI_ID', 'title' => 'ID Dolibarr', 'type' => 'text', 'required' => true),
				array('param' => 'PROD_COL_REF', 'title' => 'Réf. commande Dolibarr', 'type' => 'text'),
				array('param' => 'PROD_COL_STEP', 'title' => 'Étape de production', 'type' => 'status', 'labels' => array_values(array_unique(array(
					$p('PROD_STEP_INITIAL'), 'BAT en cours', 'En fabrication', 'Pose planifiée', 'Terminé', 'Bloqué',
				)))),
				array('param' => 'PROD_COL_AMOUNT', 'title' => 'Montant HT (€)', 'type' => 'numbers'),
				array('param' => 'PROD_COL_DELIVERY', 'title' => 'Date de pose / livraison', 'type' => 'date'),
				array('param' => 'PROD_COL_LINK', 'title' => 'Lien Dolibarr', 'type' => 'link'),
				// Colonnes gérées par l'atelier dans monday (jamais écrasées par la synchro)
				array('param' => null, 'title' => 'Type de prestation', 'type' => 'status', 'labels' => array('Covering véhicule', 'Enseigne', 'Signalétique', 'PLV')),
				array('param' => null, 'title' => 'Responsable', 'type' => 'people'),
				array('param' => null, 'title' => 'Planning', 'type' => 'timeline'),
				array('param' => null, 'title' => 'Notes atelier', 'type' => 'long_text'),
			),
		);

		return array('devis' => $devis, 'prod' => $prod);
	}

	/**
	 * Contrôle (et répare si $apply) les boards activés.
	 *
	 * @param MondayApi $api
	 * @param DoliDB    $db
	 * @param bool      $apply Créer le board et les colonnes manquants, relier les colonnes existantes
	 * @return array    Liste de lignes array('level' => ok|warn|error, 'text' => string)
	 */
	public static function inspect(MondayApi $api, $db, $apply)
	{
		$rows = array();
		if (MondaySync::param('TOKEN') === '') {
			return array(self::row(self::ERR, 'Jeton API monday non renseigné.'));
		}

		foreach (self::specs() as $spec) {
			if (!$spec['enabled']) {
				continue;
			}
			$rows[] = self::row(self::OK, '— '.$spec['title'].' —', true);
			$rows = array_merge($rows, self::inspectBoard($api, $db, $spec, $apply));
		}

		return $rows;
	}

	private static function inspectBoard(MondayApi $api, $db, array $spec, $apply)
	{
		$rows = array();
		$boardId = MondaySync::param($spec['board_param']);
		$board = $boardId !== '' ? $api->getBoard($boardId) : false;

		if ($board === false && $boardId !== '' && !$api->notFound) {
			return array(self::row(self::ERR, $api->error));
		}

		if ($board === false) {
			if (!$apply) {
				return array(self::row(self::ERR, $boardId === '' ? 'Aucun board configuré.' : 'Board '.$boardId.' introuvable (supprimé ou inaccessible). Utilisez « Créer / réparer les boards ».'));
			}
			$newId = $api->createBoard($spec['title'], $spec['description'], MondaySync::param('WORKSPACE_ID'));
			if ($newId === false) {
				return array(self::row(self::ERR, 'Création du board impossible : '.$api->error));
			}
			self::save($db, $spec['board_param'], $newId);
			$rows[] = self::row(self::OK, 'Board « '.$spec['title'].' » créé (identifiant '.$newId.').');
			$board = $api->getBoard($newId);
			if ($board === false) {
				$rows[] = self::row(self::ERR, $api->error);
				return $rows;
			}
		} else {
			$rows[] = self::row(self::OK, 'Board « '.$board['name'].' » accessible.');
		}

		$byId = array();
		foreach ($board['columns'] as $column) {
			$byId[$column['id']] = $column;
		}
		$used = array();

		foreach ($spec['columns'] as $col) {
			$title = $col['title'];
			$current = $col['param'] ? MondaySync::param($col['param']) : '';

			// 1. Colonne déjà configurée
			if ($current !== '' && isset($byId[$current])) {
				$used[$current] = true;
				if ($byId[$current]['type'] !== $col['type']) {
					$rows[] = self::row(self::ERR, $title.' : la colonne '.$current.' est de type « '.$byId[$current]['type'].' » (attendu : '.$col['type'].').');
				} else {
					$rows = array_merge($rows, self::checkLabels($title, $col, $byId[$current]));
				}
				continue;
			}

			// 2. Colonne existante de même titre et même type
			$found = self::findByTitle($board['columns'], $used, $col);
			if ($found !== null) {
				$used[$found['id']] = true;
				if (!$col['param']) {
					// Colonne de l'atelier : rien à enregistrer, seule sa présence compte
					$rows = array_merge($rows, self::checkLabels($title, $col, $found));
				} elseif ($apply) {
					if ($col['param']) {
						self::save($db, $col['param'], $found['id']);
					}
					$rows[] = self::row(self::OK, $title.' : reliée à la colonne existante '.$found['id'].'.');
					$rows = array_merge($rows, self::checkLabels($title, $col, $found));
				} else {
					$rows[] = self::row(!empty($col['required']) ? self::ERR : self::WARN, $title.' : '.($col['param'] ? 'identifiant non configuré' : 'colonne présente').' (colonne '.$found['id'].' trouvée, « Créer / réparer » la reliera).');
				}
				continue;
			}

			// 3. Colonne absente
			if (!$apply) {
				$rows[] = self::row(!empty($col['required']) ? self::ERR : self::WARN, $title.' : colonne absente du board.');
				continue;
			}
			if ($col['type'] === 'board_relation') {
				$linked = MondaySync::param($col['link_param']);
				if ($linked === '') {
					$rows[] = self::row(self::WARN, $title.' : board lié non configuré, colonne ignorée.');
					continue;
				}
				$newId = $api->createColumn($board['id'], $title, 'board_relation', '', array('boardIds' => array((int) $linked), 'allowMultipleItems' => false, 'allowCreateReflectionColumn' => false));
			} elseif ($col['type'] === 'status') {
				$newId = $api->createStatusColumn($board['id'], $title, $col['labels']);
			} else {
				$newId = $api->createColumn($board['id'], $title, $col['type']);
			}
			if ($newId === false) {
				$rows[] = self::row(self::ERR, $title.' : création impossible : '.$api->error);
				continue;
			}
			$used[$newId] = true;
			if ($col['param']) {
				self::save($db, $col['param'], $newId);
			}
			$rows[] = self::row(self::OK, $title.' : colonne créée ('.$newId.').');
		}

		return $rows;
	}

	private static function findByTitle(array $columns, array $used, array $col)
	{
		foreach ($columns as $column) {
			if (!isset($used[$column['id']]) && $column['type'] === $col['type'] && mb_strtolower($column['title']) === mb_strtolower($col['title'])) {
				return $column;
			}
		}

		return null;
	}

	/**
	 * Libellés de statut attendus mais absents : ils seront créés automatiquement à la première écriture.
	 */
	private static function checkLabels($title, array $col, array $column)
	{
		if ($col['type'] !== 'status' || empty($col['labels'])) {
			return array();
		}
		$missing = array();
		foreach ($col['labels'] as $label) {
			if ($label !== '' && !in_array($label, $column['labels'], true)) {
				$missing[] = $label;
			}
		}

		return $missing ? array(self::row(self::WARN, $title.' : libellé(s) absent(s) du board, créé(s) à la première synchro : '.implode(', ', $missing).'.')) : array();
	}

	private static function save($db, $param, $value)
	{
		global $conf;

		dolibarr_set_const($db, MondaySync::PREFIX.$param, $value, 'chaine', 0, '', $conf->entity);
	}

	private static function row($level, $text, $title = false)
	{
		return array('level' => $level, 'text' => $text, 'title' => $title);
	}
}
