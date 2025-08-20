<?php

use System\Core\Bootstrap;

Production::setAction();

class Production extends Bootstrap {
    public $module = "";
    public $permissao_ref = "expedicoes";
    public $table = "dp_remessas";
    public $table_ordens = "dp_ordens";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
            exit;
        }
        $this->getAllPermissions();

        $this->module_icon = "icomoon-icon-office";
        $this->module_link = "production";
        $this->module_title = "Pedidos em produção";
        $this->retorno = "production";
    }

    private function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        // Buscar remessas com pedidos em uma única consulta
        $this->list = $this->DB_fetch_array(
            "SELECT 
                A.*,
                DATE_FORMAT(A.created_at, '%d/%m/%Y') as created_at_formatted,
                DATE_FORMAT(A.saida, '%d/%m/%Y') as saida_formatted,
                DATE_FORMAT(A.nova_saida, '%d/%m/%Y') as nova_saida_formatted,
                DATE_FORMAT(A.entrega, '%d/%m/%Y') as entrega_formatted,
                DATE_FORMAT(A.nova_entrega, '%d/%m/%Y') as nova_entrega_formatted,
                GROUP_CONCAT(DISTINCT B.id_pedido ORDER BY B.id_pedido ASC SEPARATOR ', ') as pedidos_concat,
                GROUP_CONCAT(DISTINCT C.nome ORDER BY C.nome ASC SEPARATOR ', ') as departamentos,
                D.cidade,
                E.uf
            FROM {$this->table} A
                LEFT JOIN {$this->table_ordens} B ON A.id = B.id_remessa
                LEFT JOIN dp_ordem_departamento BC ON B.id = BC.id_ordem
                LEFT JOIN dp_departamentos C ON BC.id_departamento = C.id
                LEFT JOIN tb_utils_cidades D ON A.id_cidade = D.id
                LEFT JOIN tb_utils_estados E ON A.id_estado = E.id
            GROUP BY A.id
            ORDER BY A.saida ASC;
        ");

        if ($this->list->num_rows) {
            $this->categorizeItems();
        }

        $this->renderView($this->getModule(), "index");
    }

    private function categorizeItems() {
        $hoje = date('Y-m-d');
        $seteDias = date('Y-m-d', strtotime('+7 days'));

        // Inicializar arrays
        $this->nova_saida_atraso = [];
        $this->saida_atraso = [];
        $this->nova_saida_seteDias = [];
        $this->saida_seteDias = [];
        $this->nova_saida_restantes = [];
        $remaining = [];

        // Única iteração para categorizar todos os itens
        foreach ($this->list->rows as $item) {
            $hasNovaSaida = !empty($item['nova_saida']);
            $hasSaida = !empty($item['saida']);
            $novaSaidaDate = $hasNovaSaida ? $item['nova_saida'] : null;
            $saidaDate = $hasSaida ? $item['saida'] : null;

            // Categorizar baseado nas regras
            if ($hasNovaSaida && $novaSaidaDate <= $hoje) {
                // Nova saída em atraso
                $this->nova_saida_atraso[] = $item;
            } elseif ($hasSaida && !$hasNovaSaida && $saidaDate <= $hoje) {
                // Saída em atraso (sem nova saída)
                $this->saida_atraso[] = $item;
            } elseif ($hasNovaSaida && $novaSaidaDate <= $seteDias) {
                // Nova saída nos próximos 7 dias
                $this->nova_saida_seteDias[] = $item;
            } elseif ($hasSaida && !$hasNovaSaida && $saidaDate <= $seteDias) {
                // Saída nos próximos 7 dias (sem nova saída)
                $this->saida_seteDias[] = $item;
            } elseif ($hasNovaSaida) {
                // Nova saída restante (futuro distante)
                $this->nova_saida_restantes[] = $item;
            } else {
                // Itens sem nova saída que não se encaixam em outras categorias
                $remaining[] = $item;
            }
        }

        // Atualizar lista principal com itens restantes
        $this->list->rows = $remaining;
        $this->list->rows = [
            [
                "category" => "atrasado",
                "items" => $this->nova_saida_atraso,
            ],
            [
                "category" => "atrasado",
                "items" => $this->saida_atraso,
            ],
            [
                "category" => "urgente",
                "items" => $this->nova_saida_seteDias,
            ],
            [
                "category" => "urgente",
                "items" => $this->saida_seteDias,
            ],
            [
                "category" => "normal",
                "items" => $this->nova_saida_restantes,
            ],
            [
                "category" => "normal",
                "items" => $remaining,
            ],
        ];
        $this->list->num_rows = count($remaining);
    }

    private function editAction() {
        $this->id = $this->getParameter("id");

        $this->estados = $this->DB_fetch_array("SELECT * FROM tb_utils_estados ORDER BY estado");

        //edit
        if (!$this->permissions[$this->permissao_ref]['editar']) {
            $this->noPermission();
        }

        $query = $this->DB_fetch_array(
            "SELECT 
                A.*,
                DATE_FORMAT(A.created_at, '%d/%m/%Y') as created_at_formatted,
                DATE_FORMAT(A.saida, '%d/%m/%Y') as saida_formatted,
                DATE_FORMAT(A.nova_saida, '%d/%m/%Y') as nova_saida_formatted,
                DATE_FORMAT(A.entrega, '%d/%m/%Y') as entrega_formatted,
                DATE_FORMAT(A.nova_entrega, '%d/%m/%Y') as nova_entrega_formatted
            FROM {$this->table} A
            WHERE id = {$this->id}"
        );
        $this->registro = ($query->num_rows) ? $query->rows[0] : [];

        $this->renderView($this->getModule(), "edit");
    }

    private function saveAction() {
        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormulario($formulario);

        if (!$validacao->return) {
            echo json_encode($validacao);
            return;
        }

        $entrega = DateTime::createFromFormat('d/m/Y', $formulario->update_entrega)->format('Y-m-d');;
        $saida = DateTime::createFromFormat('d/m/Y', $formulario->update_saida)->format('Y-m-d');;

        $resposta = new \stdClass();
        $data = $this->formularioObjeto($_POST, $this->table);

        if($entrega != $formulario->entrega_){
            $data->nova_entrega = $entrega;
        }
        if($saida != $formulario->saida_){
            $data->nova_saida = $saida;
        }
        
        if (!$this->permissions[$this->permissao_ref]['editar']) {
            exit();
        }

        foreach ($data as $key => $value) {
            if ($value == "NULL") $fields_values[] = "{$key}={$value}";
            else $fields_values[] = "{$key}='{$value}'";
        }

        $query = $this->DB_update($this->table, implode(',', $fields_values) . " WHERE id={$data->id}");
        if ($query) {
            $resposta->type = "success";
            $resposta->message = "Registro alterado com sucesso!";
            $this->inserirRelatorio("Alterou a remessa: [{$data->nome}]");
        } else {
            $resposta->type = "error";
            $resposta->message = "Aconteceu um erro no sistema, favor tentar novamente mais tarde!";
        }

        echo json_encode($resposta);
    }

    private function validaFormulario($form) {
        $resposta = new \stdClass();
        $resposta->return = true;

        if ($form->nome == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "nome";
            $resposta->return = false;
        } else if ($form->update_entrega == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "update_entrega";
            $resposta->return = false;
        } else if ($form->update_saida == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "update_saida";
            $resposta->return = false;
        } else if ($form->telefone == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "telefone";
            $resposta->return = false;
        } else if ($form->cpf_cnpj == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "cpf_cnpj";
            $resposta->return = false;
        } else if ($form->cep == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "cep";
            $resposta->return = false;
        } else if ($form->endereco == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "endereco";
            $resposta->return = false;
        } else if ($form->bairro == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "bairro";
            $resposta->return = false;
        }
        return $resposta;
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

    public static function setAction() {
        $sistema = new Bootstrap();

        #encontrar nome da classe pelo nome do arquivo e instanciá-la
        $class = explode(DIRECTORY_SEPARATOR, __FILE__);
        $class = str_replace(".php", "", end($class));
        $instance = new $class();

        #acionar o método da classe de acordo com o parâmetro da url
        $action = $sistema->getParameter(strtolower($class));
        $action = explode("?", $action);
        $newAction = $action[0] . "Action";

        #antes de acioná-lo, verifica se ele existe
        if (method_exists($instance, $newAction)) {
            $instance->setModule($class);
            $instance->$newAction();
        } else if ($newAction == "Action") {
            $instance->setModule($class);
            if (method_exists($instance, 'indexAction'))
                $instance->indexAction();
            else
                $sistema->renderView($instance->getModule(), "404");
        } else {
            $sistema->renderView($instance->getModule(), "404");
        }
    }
}
