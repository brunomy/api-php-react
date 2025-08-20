<?php

namespace classes;

use System\Core\Bootstrap;

$e = new Expedicao();

if ($e->getParameter('expedicao') !== null) {
    $e->setAction();
}

class Expedicao extends Bootstrap {
    public $module = "";
    // public $permissao_ref = "expedicoes";
    public $table = "tb_expedicoes_expedicoes";

    public const BARCODE = '/^R([0-9]+)P([0-9]+)$/';
    public const ALPHANUM = '/^[A-Za-z0-9]+$/';
    public const CSV = '/^([^\,\s]+,?)+$/';

    public static $rules = [
        'id' => [
            'filter' => FILTER_VALIDATE_INT,
            'options' => ['default' => 0, 'min_range' => 0],
        ],
        'id_agendor' => [
            'filter' => FILTER_VALIDATE_REGEXP,
            'options' => ['default' => '', 'regexp' => self::ALPHANUM],
        ],
        'volumes_lidos' => [
            'filter' => FILTER_VALIDATE_REGEXP,
            'options' => ['default' => '', 'regexp' => self::CSV],
        ],
        'departamentos' => [
            'filter' => FILTER_VALIDATE_REGEXP,
            'options' => ['default' => '', 'regexp' => self::CSV],
        ],
    ];

    public static function validate($type) {
        return filter_var_array(
            filter_input_array(
                $type, self::$rules, true
            ) ?? array_keys(self::$rules),
            self::$rules,
            true
        );
    }

    function __construct() {
        parent::__construct();
        // if (!isset($_SESSION['admin_logado'])) {
        //     header("Location: " . $this->system_path . "login");
        //     exit;
        // }
        // $this->getAllPermissions();

        // $this->module_icon = "icomoon-icon-office";
        // $this->module_link = "carrier";
        // $this->module_title = "Transportadoras";
    }

    private function indexAction() {
        // Valida os parâmetros comuns recebidos por POST
        $this->params = self::validate(INPUT_POST);
        // Procura o parâmetro 'departamentos' no GET caso não exista no POST
        if (!isset($_POST['departamentos'])) {
            $this->params['departamentos'] = self::validate(INPUT_GET)['departamentos'];
        }

        // Tenta carregar do banco a expedição e o funcionário logado
        $this->registro = $this->getRegistro(
            $this->params['id'],
            $this->params['departamentos']
        );
        $this->user = $this->getUsuario($this->params['id_agendor']);

        // Tenta validar o código recebido
        $code = $this->parseCode(
            filter_input(INPUT_POST, 'input', FILTER_SANITIZE_STRING),
            $this->registro['volumes'] ?? PHP_INT_MAX,
            $this->user['id_agendor'] ?? ''
        );

        // Página 'edit' (Leitura de códigos de barra de uma expedição)
        if ($this->user && $this->registro) {
            $this->volumes = array_filter(
                explode(',', $this->params['volumes_lidos']),
                'strlen'
            );

            // Processa o código recebido (se houver)
            switch ($code['type'] ?? '') {
                case 'barcode': {
                    if (
                        $code['value'] === $this->registro['id'] &&
                        !in_array($code['volume_id'], $this->volumes)
                    ) array_push($this->volumes, $code['volume_id']);
                    else $this->info(
                        'error',
                        'Etiqueta repetida ou de NF diferente!'
                    );
                    break;
                }
                case 'logout': {
                    $this->info('info', 'Carregando listagem');
                    // Força a remoção do registro no refresh automático
                    $this->params['id'] = null;
                    break;
                }
                default: {
                    if ($code) $this->info('error', 'Código inválido!');
                }
            }
        } else {
            // Página 'list' (Listagem das expedições pendentes)
            $csv = "( '" . implode("', '", explode(',', $this->params['departamentos'])) . "' )";
            $whereDep = ($this->params['departamentos']) ? "A.departamento IN {$csv} AND" : '';
            $query = $this->DB_fetch_array("SELECT A.id, A.id_pedido, A.data_coleta, A.volumes, A.n_nota, B.nome AS transportadora FROM {$this->table} A INNER JOIN tb_expedicoes_transportadoras B ON B.id = A.id_transportadora WHERE {$whereDep} A.coleta_confirmada IS NULL ORDER BY A.data_coleta ASC, A.id ASC");
            $this->list = ($query->num_rows) ? $query->rows : [];

            // Agrupa a lista por transportadoras
            $grouped = [];
            foreach ($this->list as $el) {
                $grouped[$el['transportadora']][] = $el;
            }
            $this->list = $grouped;

            // Processa o código recebido (se houver)
            switch ($code['type'] ?? '') {
                case 'barcode': {
                    if ($this->user) {
                        $registro = $this->getRegistro(
                            $code['value'],
                            $this->params['departamentos']
                        );
                        if (
                            $registro &&
                            $code['volume_id'] <= $registro['volumes']
                        ) {
                            $this->list = [];
                            $this->registro = $registro;
                            $this->volumes = [$code['volume_id']];
                        } else $this->info(
                            'error',
                            'Código de barras inválido!'
                        );
                    } else $this->info(
                        'error',
                        'Nenhum funcionário logado!'
                    );
                    break;
                }
                case 'rfid': {
                    if ($user = $this->getUsuario($code['value'])) {
                        $this->user = $user;
                    } else $this->info(
                        'error',
                        'Código RFID inválido!'
                    );
                    break;
                }
                case 'logout': {
                    $this->user = [];
                    break;
                }
                default: {
                    if ($code) $this->info('error', 'Código inválido!');
                }
            }
        }

        // Todos os códigos de barras da expedição foram lidos
        if (
            $this->registro &&
            count($this->volumes) >= $this->registro['volumes']
        ) {
            if ($this->DB_update($this->table, "id_user = {$this->user['id']}, coleta_confirmada = NOW() WHERE id = {$this->registro['id']}")) {
                $this->info(
                    'success',
                    "NF {$this->registro['n_nota']} concluída!"
                );
                // Força a remoção do registro no refresh automático
                $this->params['id'] = null;
            } else {
                $this->info(
                    'error',
                    'Erro ao salvar dados! Expedição NÃO concluída'
                );
            }
        }

        // Apresenta a mensagem na tela e dá um refresh automático
        if (isset($this->information)) {
            $this->return = true;
        }

        $this->renderSiteView('expedicao');
    }

    private function etiquetasAction() {
        // Desembaralha o parâmetro id
        $id = $this->desembaralhar(
            str_replace(' ', '+',
                filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING)
            )
        );
        // Garante que o valor desembaralhado é um inteiro
        $id = filter_var($id, FILTER_VALIDATE_INT, [
            'options' => ['default' => 0, 'min_range' => 0],
        ]);

        // Tenta buscar o registro solicitado, ou redireciona para a home
        if (($this->registro = $this->getRegistro($id, null, true))) {
            $this->renderSiteView('etiquetas');
        } else {
            header("Location: {$this->root_path}");
        }
    }

    private function getRegistro($id, $departamentos = '', $confirmed = false) {
        if (!$id || $id <= 0) return [];

        $csv = "( '" . implode("', '", explode(',', $departamentos)) . "' )";
        $whereDep = ($departamentos) ? "AND A.departamento IN {$csv}" : '';
        $notConfirmed = ($confirmed) ? '' : 'AND A.coleta_confirmada IS NULL';

        try {
            $query = $this->DB_fetch_array("
            SELECT 
                A.*, 
                B.cidade, 
                C.uf, 
                D.nome AS transportadora, 
                E.faturado_por,
                F.razao_social, F.nome_fantasia, F.cnpj
                FROM {$this->table} A 
                INNER JOIN tb_utils_cidades B ON B.id = A.id_cidade 
                INNER JOIN tb_utils_estados C ON C.id = B.id_estado 
                INNER JOIN tb_expedicoes_transportadoras D ON D.id = A.id_transportadora 
                INNER JOIN tb_pedidos_pedidos E ON E.id = A.id_pedido 
                LEFT JOIN tb_admin_empresas F ON F.id = E.id_empresa_faturado
            WHERE A.id = {$id} {$whereDep} {$notConfirmed} LIMIT 1");
        }
        catch (\Exception $e){
            $query = $this->DB_fetch_array("SELECT A.*, B.cidade, C.uf, D.nome AS transportadora, E.faturado_por FROM {$this->table} A INNER JOIN tb_utils_cidades B ON B.id = A.id_cidade INNER JOIN tb_utils_estados C ON C.id = B.id_estado INNER JOIN tb_expedicoes_transportadoras D ON D.id = A.id_transportadora INNER JOIN tb_pedidos_pedidos E ON E.id = A.id_pedido WHERE A.id = {$id} {$whereDep} {$notConfirmed} LIMIT 1");
        }

        return ($query->num_rows) ? $query->rows[0] : [];
    }

    private function getUsuario($rfid) {
        if (!$rfid) return [];

        $query = $this->DB_fetch_array("SELECT id, id_agendor, nome FROM tb_admin_users WHERE id_agendor = '{$rfid}' LIMIT 1");

        return ($query->num_rows) ? $query->rows[0] : [];
    }

    private function parseCode($code, $maxVolume = PHP_MAX_INT, $login = '') {
        if (!$code) return [];

        // TODO: DECIDE - Desembaralhar ID no código de barras?
        if (
            preg_match(self::BARCODE, strtoupper($code), $matches) &&
            $matches[2] &&
            $matches[2] <= $maxVolume
        ) {
            // Códigos de barra code128 no formato R{ id_expedição }P{ volume }
            return [
                'type' => 'barcode',
                'value' => $matches[1],
                'volume_id' => $matches[2],
            ];
        } else if (preg_match(self::ALPHANUM, $code)) {
            // Códigos RFID são alfanuméricos
            return [
                'type' => ($code === $login) ? 'logout' : 'rfid',
                'value' => $code,
            ];
        } else {
            return [
                'type' => 'invalid',
                'value' => null,
            ];
        }
    }

    public function info($type, $message) {
        $this->information = [
            'type' => $type,
            'message' => $message,
        ];
    }

    public function renderSiteView($view) {
        if (file_exists(__DIR__ . "/view/expedicao/$view.phtml")) {
            require_once __DIR__ . "/view/expedicao/$view.phtml";
        } else {
            require_once "layouts/404.phtml";
        }
    }

    private function errorAction() {
        require_once '404.php';
    }

    /*
     * Métodos padrões da classe
     */

    public function setModule($module) {
        $this->module = $module;
    }

    public function getModule() {
        return strtolower($this->module);
    }

    public function setAction() {

        #encontrar nome da classe pelo nome do arquivo e instanciá-la
        $class = explode(DIRECTORY_SEPARATOR, __FILE__);
        $class = str_replace(".php", "", end($class));

        #acionar o método da classe de acordo com o parâmetro da url
        $action = $this->getParameter(strtolower($class));
        $action = explode("?", $action);
        $newAction = $action[0] . "Action";

        #antes de acioná-lo, verifica se ele existe
        if (method_exists($this, $newAction)) {
            $this->setModule($class);
            $this->$newAction();
        } else if ($newAction == "Action") {
            $this->setModule($class);
            if (method_exists($this, 'indexAction'))
                $this->indexAction();
            else
                $this->errorAction();
        } else {
            $this->errorAction();
        }
    }
}
