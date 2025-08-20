<?php

use System\Core\Bootstrap;

ProductionServices::setAction();

class ProductionServices extends Bootstrap {

    public $module = "";
    public $permissao_ref = "producao-servicos";
    public $table = "tb_producao_servicos";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
            exit;
        }
        $this->getAllPermissions();

        $this->module_icon = "typ-icon-tag ";
        $this->module_link = "productionservices";
        $this->module_title = "Cadastro de Serviços";
        $this->retorno = "productionservices";
    }

    private function indexAction() {

        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->list = $this->DB_fetch_array("SELECT * FROM $this->table ORDER BY nome");

        $this->renderView($this->getModule(), "index");
    }

    private function duplicateAction() {
        $this->id = $this->getParameter("id");
        $this->categorias_ativas = array();
        if ($this->id == "") {
            //new            
            if (!$this->permissions[$this->permissao_ref]['gravar'])
                $this->noPermission();

            $campos = $this->DB_columns($this->table);
            foreach ($campos as $campo) {
                $this->registro[$campo] = "";
            }
        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar'])
                $this->noPermission();

            $query = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $this->id");
            $select = $this->DB_fetch_array("SELECT a.id_servico, a.id_atributo, d.id_categoria FROM tb_producao_servicos_has_atributos a JOIN tb_produtos_atributos_has_conjuntos_atributos b ON a.id_atributo = b.id_atributo JOIN tb_produtos_conjuntos_atributos c ON c.id = b.id_conjunto JOIN tb_produtos_produtos_has_tb_produtos_categorias d ON d.id_produto=c.id_produto WHERE a.id_servico = $this->id ORDER BY d.id_categoria");
            $flag = 0;
            $this->atributos = array();
            foreach ($select->rows as $atributo) {
                if($atributo['id_categoria'] != $flag){
                    $this->categorias_ativas[] = $atributo['id_categoria'];
                    $flag = $atributo['id_categoria'];
                }
                $this->atributos[] = $atributo['id_atributo'];
            }

            $this->registro = $query->rows[0];
        }
        
        $this->categorias = $this->DB_fetch_array("SELECT * FROM tb_produtos_categorias WHERE stats = 1 ORDER BY nome");

        $this->renderView($this->getModule(), "edit");
    }

    private function editAction() {
        $this->id = $this->getParameter("id");
        $this->duplicate = $this->getParameter("duplicate");
        $this->categorias_ativas = array();
        $this->atributos = array();
        if ($this->id == "" || $this->duplicate != "") {
            //new            
            if (!$this->permissions[$this->permissao_ref]['gravar'])
                $this->noPermission();

            $campos = $this->DB_columns($this->table);
            foreach ($campos as $campo) {
                $this->registro[$campo] = "";
            }

            $this->atributos = $this->getAtributos($this->duplicate);

        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar'])
                $this->noPermission();

            $query = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $this->id");
            $this->registro = $query->rows[0];
            $this->atributos = $this->getAtributos($this->id);
        }
        
        $this->categorias = $this->DB_fetch_array("SELECT * FROM tb_produtos_categorias WHERE stats = 1 ORDER BY nome");

        $this->renderView($this->getModule(), "edit");
    }

    private function saveAction() {

        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormulario($formulario);
        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {
            $resposta = new \stdClass();
            $data = $this->formularioObjeto($_POST, $this->table);

            if ($formulario->id == "") {
                //criar
                if (!$this->permissions[$this->permissao_ref]['gravar'])
                    exit();

                /*
                foreach ($data as $key => $value) {
                    $fields[] = $key;
                    if ($value == "NULL")
                        $values[] = "$value";
                    else
                        $values[] = "'$value'";
                }

                $query = $this->DB_insert($this->table, implode(',', $fields), implode(',', $values));
                */

                $query = $this->DB_insert($this->table, 'nome,tempo', '"'.$data->nome.'","'.$data->tempo.'"');
                $insert_id = $query->insert_id;

                if ($query->query) {
                    $resposta->type = "success";
                    $resposta->message = "Registro cadastrado com sucesso!";
                    foreach ($_POST['atributos'] as $atributo) {
                        $this->DB_insert('tb_producao_servicos_has_atributos', 'id_servico,id_atributo', $insert_id.','.$atributo);
                    }

                    $this->inserirRelatorio("Cadastrou serviço: [" . $data->nome . "]");
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            } else {
                //alterar
                if (!$this->permissions[$this->permissao_ref]['editar'])
                    exit();

                foreach ($data as $key => $value) {
                    $fields_values[] = "$key='$value'";
                }

                $query = $this->DB_update($this->table, "nome='".$data->nome."',tempo=".$data->tempo . " WHERE id=" . $data->id);
                if ($query) {
                    $resposta->type = "success";
                    $resposta->message = "Registro alterado com sucesso!";

                    $this->DB_delete('tb_producao_servicos_has_atributos', "id_servico=".$data->id);
                    foreach ($_POST['atributos'] as $atributo) {
                        $this->DB_insert('tb_producao_servicos_has_atributos', 'id_servico,id_atributo', $data->id.','.$atributo);
                    }

                    $this->inserirRelatorio("Alterou serviço: [" . $data->nome . "]");
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            }

            echo json_encode($resposta);
        }
    }

    private function getProdutosAction() {

        if(!isset($_POST['categorias'])) exit();

        //$categorias = implode(',', $_POST['categorias']);
        $subselect_categorias = "SELECT p.id FROM tb_produtos_produtos_has_tb_produtos_categorias c JOIN tb_produtos_produtos p ON p.id = c.id_produto WHERE c.id_categoria IN (".implode(',', $_POST['categorias']).")";

        $select_columns = "c.id_produto, p.nome produto, a.id_conjunto_atributo id_conjunto, c.nome conjunto, a.id id_atributo, a.nome atributo";
        $select_from_joins = "tb_produtos_atributos a JOIN tb_produtos_conjuntos_atributos c ON a.id_conjunto_atributo = c.id JOIN tb_produtos_produtos p ON c.id_produto = p.id";
        $select_where = "c.id_produto IN (".$subselect_categorias.") AND p.stats = 1";
        $select_order = "c.id_produto, a.id_conjunto_atributo, a.ordem";

        $query_select = "SELECT ".$select_columns." FROM ".$select_from_joins." WHERE ".$select_where." ORDER BY ".$select_order;
        $query = $this->DB_fetch_array($query_select);
        
        //echo '<pre>';print_r($query);echo '</pre>';
        //exit();

        if($query->num_rows){

            $id_produto = 0;
            $id_conjunto = 0;
            $count = 0; 

            $html = '';

            foreach ($query->rows as $atributo) {
                if($id_produto != $atributo['id_produto']){
                    if($id_produto==0) $html .= '<div class="produto">';
                    else $html .= '</div></div></div><div class="produto">';

                    $html .= '<div class="prod-nome">'.$atributo['produto'].'</div>';
                    $id_produto = $atributo['id_produto'];
                    $id_conjunto = 0;
                    $count ++; 
                }
                if($id_conjunto != $atributo['id_conjunto']){
                    if($id_conjunto==0) $html .= '<div class="prod-conjunto">';
                    else $html .= '</div></div><div class="prod-conjunto">';

                    $html .= '<div class="conj-nome">'.$atributo['conjunto'].'</div>';
                    $html .= '<div class="conj-atributos">';

                    $id_conjunto = $atributo['id_conjunto'];
                }

                $html .= ' <label class="checkbox"><input type="checkbox" name="atributos[]" class="checkbox-atributo" value="'.$atributo['id_atributo'].'">'.$atributo['atributo'].'</label>';
            }

            $html = '<div id="produtos" style="width:'.(280*$count).'px;"><div id="drag"></div>'. $html .'<div class="clear"></div></div>';

            echo $html;

        }else{
            echo "Nenhum produto encontrado!";
        }

    }

    private function getAtributos($id){
        $select = $this->DB_fetch_array("SELECT a.id_servico, a.id_atributo, d.id_categoria FROM tb_producao_servicos_has_atributos a JOIN tb_produtos_atributos c ON c.id = a.id_atributo JOIN tb_produtos_conjuntos_atributos b ON b.id = c.id_conjunto_atributo JOIN tb_produtos_produtos_has_tb_produtos_categorias d ON d.id_produto=b.id_produto WHERE a.id_servico = $id ORDER BY d.id_categoria");
        $flag = 0;
        $atributos = array();
        foreach ($select->rows as $atributo) {
            if($atributo['id_categoria'] != $flag){
                $this->categorias_ativas[] = $atributo['id_categoria'];
                $flag = $atributo['id_categoria'];
            }
            $atributos[] = $atributo['id_atributo'];
        }

        return $atributos;
    }

    public function validaFormulario($form) {

        $resposta = new \stdClass();
        $resposta->return = true;

        if ($form->nome == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "nome";
            $resposta->return = false;
        } else if ($form->tempo == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "tempo";
            $resposta->return = false;
        } else if(!isset($form->atributos)){
            $resposta->type = "attention";
            $resposta->message = "Nenhum atributo selecionado!";
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
