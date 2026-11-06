<?php
/**
 * Client minimal pour l'API GraphQL de monday.com.
 */
class MondayApi
{
	const ENDPOINT = 'https://api.monday.com/v2';
	const MAX_ATTEMPTS = 3;

	/** @var string */
	public $error = '';

	/** @var bool Vrai si le dernier getBoard() n'a pas trouvé le board */
	public $notFound = false;

	/** @var string */
	private $token;

	/** @var string Version d'API (ex. 2025-10). Vide = version courante de monday. */
	private $apiVersion;

	public function __construct($token, $apiVersion = '')
	{
		$this->token = trim((string) $token);
		$this->apiVersion = trim((string) $apiVersion);
	}

	/**
	 * Exécute une requête GraphQL.
	 *
	 * @param string $query     Requête ou mutation
	 * @param array  $variables Variables GraphQL
	 * @return array|false      Contenu de "data", ou false (message dans $this->error)
	 */
	public function query($query, array $variables = array())
	{
		$this->error = '';
		$payload = json_encode(array('query' => $query, 'variables' => (object) $variables), JSON_UNESCAPED_UNICODE);

		$headers = array('Content-Type: application/json', 'Authorization: '.$this->token);
		if ($this->apiVersion !== '') {
			$headers[] = 'API-Version: '.$this->apiVersion;
		}

		for ($attempt = 1; $attempt <= self::MAX_ATTEMPTS; $attempt++) {
			$ch = curl_init(self::ENDPOINT);
			curl_setopt_array($ch, array(
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $payload,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_CONNECTTIMEOUT => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HTTPHEADER => $headers,
			));
			$body = curl_exec($ch);
			$http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$curlError = curl_error($ch);
			curl_close($ch);

			if ($body === false) {
				$this->error = 'monday : connexion impossible ('.$curlError.')';
				return false;
			}

			$json = json_decode($body, true);
			$rateLimited = ($http === 429) || (is_array($json) && !empty($json['errors']) && preg_match('/rate|complexity|limit/i', json_encode($json['errors'])));
			if ($rateLimited && $attempt < self::MAX_ATTEMPTS) {
				sleep(2 * $attempt);
				continue;
			}

			if (!is_array($json)) {
				$this->error = 'monday : réponse illisible (HTTP '.$http.')';
				return false;
			}
			if (!empty($json['errors'])) {
				$this->error = 'monday : '.$this->formatErrors($json['errors']);
				return false;
			}
			if (!empty($json['error_message'])) {
				$this->error = 'monday : '.$json['error_message'];
				return false;
			}
			if ($http >= 400) {
				$this->error = 'monday : erreur HTTP '.$http;
				return false;
			}

			return isset($json['data']) && is_array($json['data']) ? $json['data'] : array();
		}

		return false;
	}

	/**
	 * Infos d'un board : nom, groupes et colonnes (id, titre, type, réglages).
	 * Pour une colonne de statut, 'labels' contient la liste des libellés existants.
	 *
	 * @return array|false false si erreur ; $this->notFound vaut true si le board n'existe pas (ou n'est pas accessible)
	 */
	public function getBoard($boardId)
	{
		$this->notFound = false;
		$data = $this->query(
			'query ($b: [ID!]) { boards(ids: $b) { id name groups { id title } columns { id title type settings_str } } }',
			array('b' => array((string) $boardId))
		);
		if ($data === false) {
			return false;
		}
		if (empty($data['boards'][0])) {
			$this->notFound = true;
			$this->error = 'monday : board introuvable ou inaccessible avec ce jeton';
			return false;
		}

		$board = $data['boards'][0];
		foreach ($board['columns'] as $i => $column) {
			$labels = array();
			if ($column['type'] === 'status') {
				$settings = json_decode((string) $column['settings_str'], true);
				if (is_array($settings) && !empty($settings['labels']) && is_array($settings['labels'])) {
					$labels = array_values(array_filter($settings['labels'], 'is_string'));
				}
			}
			$board['columns'][$i]['labels'] = $labels;
			unset($board['columns'][$i]['settings_str']);
		}

		return $board;
	}

	/**
	 * Crée un board vide.
	 *
	 * @return string|false id du board
	 */
	public function createBoard($name, $description = '', $workspaceId = '')
	{
		$data = $this->query(
			'mutation ($n: String!, $d: String, $w: ID) { create_board(board_name: $n, description: $d, workspace_id: $w, board_kind: public, empty: true) { id } }',
			array('n' => $name, 'd' => $description, 'w' => $workspaceId !== '' ? (string) $workspaceId : null)
		);

		return ($data !== false && isset($data['create_board']['id'])) ? (string) $data['create_board']['id'] : false;
	}

	/**
	 * Crée une colonne (hors statut).
	 *
	 * @param array|null $defaults Réglages propres au type (ex. boardIds pour une relation)
	 * @return string|false id de la colonne
	 */
	public function createColumn($boardId, $title, $type, $description = '', array $defaults = null)
	{
		$data = $this->query(
			'mutation ($b: ID!, $t: String!, $ct: ColumnType!, $d: String, $df: JSON) { create_column(board_id: $b, title: $t, column_type: $ct, description: $d, defaults: $df) { id } }',
			array('b' => (string) $boardId, 't' => $title, 'ct' => $type, 'd' => $description, 'df' => $defaults !== null ? json_encode($defaults) : null)
		);

		return ($data !== false && isset($data['create_column']['id'])) ? (string) $data['create_column']['id'] : false;
	}

	/**
	 * Crée une colonne de statut avec ses libellés.
	 *
	 * @param string[] $labels
	 * @return string|false id de la colonne
	 */
	public function createStatusColumn($boardId, $title, array $labels)
	{
		$colors = array('working_orange', 'done_green', 'stuck_red', 'dark_blue', 'purple', 'sky', 'grass_green', 'egg_yolk', 'berry', 'navy', 'teal', 'lavender', 'peach', 'brown', 'steel');
		$defs = array();
		foreach (array_values($labels) as $i => $label) {
			$defs[] = array('label' => $label, 'color' => $colors[$i % count($colors)], 'index' => $i);
		}
		$data = $this->query(
			'mutation ($b: ID!, $t: String!, $df: CreateStatusColumnSettingsInput) { create_status_column(board_id: $b, title: $t, defaults: $df) { id } }',
			array('b' => (string) $boardId, 't' => $title, 'df' => array('labels' => $defs))
		);

		return ($data !== false && isset($data['create_status_column']['id'])) ? (string) $data['create_status_column']['id'] : false;
	}

	/**
	 * Cherche un élément d'après la valeur d'une colonne.
	 *
	 * @return string|null|false id de l'élément, null si absent, false en cas d'erreur
	 */
	public function findItemByColumn($boardId, $columnId, $value)
	{
		$data = $this->query(
			'query ($b: ID!, $c: [ItemsPageByColumnValuesQuery!]) { items_page_by_column_values(board_id: $b, columns: $c, limit: 1) { items { id } } }',
			array('b' => (string) $boardId, 'c' => array(array('column_id' => $columnId, 'column_values' => array((string) $value))))
		);
		if ($data === false) {
			return false;
		}

		return isset($data['items_page_by_column_values']['items'][0]['id']) ? (string) $data['items_page_by_column_values']['items'][0]['id'] : null;
	}

	/**
	 * @return string|false id du nouvel élément
	 */
	public function createItem($boardId, $groupId, $name, array $columnValues)
	{
		$vars = array('b' => (string) $boardId, 'n' => $name, 'cv' => json_encode($columnValues, JSON_UNESCAPED_UNICODE));
		if ($groupId !== '') {
			$query = 'mutation ($b: ID!, $g: String, $n: String!, $cv: JSON) { create_item(board_id: $b, group_id: $g, item_name: $n, column_values: $cv, create_labels_if_missing: true) { id } }';
			$vars['g'] = $groupId;
		} else {
			$query = 'mutation ($b: ID!, $n: String!, $cv: JSON) { create_item(board_id: $b, item_name: $n, column_values: $cv, create_labels_if_missing: true) { id } }';
		}
		$data = $this->query($query, $vars);
		if ($data === false) {
			return false;
		}

		return isset($data['create_item']['id']) ? (string) $data['create_item']['id'] : false;
	}

	/**
	 * Met à jour un élément. Réussit seulement si l'élément est actif : monday accepte les mises à jour
	 * d'un élément supprimé ou archivé (état « deleted » / « archived ») sans signaler d'erreur.
	 *
	 * @return bool
	 */
	public function updateItem($boardId, $itemId, array $columnValues)
	{
		$data = $this->query(
			'mutation ($b: ID!, $i: ID!, $cv: JSON!) { change_multiple_column_values(board_id: $b, item_id: $i, column_values: $cv, create_labels_if_missing: true) { id state } }',
			array('b' => (string) $boardId, 'i' => (string) $itemId, 'cv' => json_encode($columnValues, JSON_UNESCAPED_UNICODE))
		);
		if ($data === false) {
			return false;
		}
		$item = isset($data['change_multiple_column_values']) ? $data['change_multiple_column_values'] : null;
		if (empty($item['id']) || (isset($item['state']) && $item['state'] !== 'active')) {
			$this->error = 'monday : élément '.$itemId.' introuvable, supprimé ou archivé (not found)';
			return false;
		}

		return true;
	}

	private function formatErrors(array $errors)
	{
		$messages = array();
		foreach ($errors as $e) {
			$messages[] = is_array($e) && isset($e['message']) ? $e['message'] : json_encode($e);
		}

		return implode(' ; ', $messages);
	}
}
