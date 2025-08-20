<?php

use System\Core\Bootstrap;

SetFreight::setAction();

class SetFreight extends Bootstrap {

    public $module = "";
    public $permissao_ref = "configuracoes";
    public $table = "tb_config_conjuntos_fretes";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
            exit;
        }
        $this->getAllPermissions();

        $this->module_icon = "brocco-icon-location-2";
        $this->module_link = "setfreight";
        $this->module_title = "Conjuntos de Frete";
        $this->retorno = "setfreight";
    }

    private function indexAction() {

        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->list = $this->DB_fetch_array("SELECT A.*, B.nome tipo FROM $this->table A INNER JOIN tb_config_conjuntos_fretes_tipos B ON B.id = A.id_tipo ORDER BY A.nome");

        $this->renderView($this->getModule(), "index");
    }

    private function editAction() {
        $this->id = $this->getParameter("id");

        if ($this->id == "") {
            //new            
            if (!$this->permissions[$this->permissao_ref]['gravar'])
                $this->noPermission();

            $campos = $this->DB_columns($this->table);
            foreach ($campos as $campo) {
                $this->registro[$campo] = "";
            }

            $this->customizados = new \stdClass();
            $this->customizados->num_rows = false;

            $this->negados = new \stdClass();
            $this->negados->num_rows = false;
        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar'])
                $this->noPermission();

            $query = $this->DB_fetch_array("SELECT A.*, DATE_FORMAT(A.data, '%d/%m/%Y %H:%i') data FROM $this->table A WHERE A.id = $this->id");

            $this->registro = $query->rows[0];

            $this->customizados = $this->DB_fetch_array("SELECT A.*, B.nome estado, C.nome cidade FROM tb_config_conjuntos_fretes_customizados A LEFT JOIN tb_config_estados B ON B.id = A.id_estado LEFT JOIN tb_config_cidades C ON C.id = A.id_cidade WHERE A.id_conjunto = $this->id ORDER BY B.nome,  CASE WHEN C.nome IS NULL  THEN 0 WHEN C.capital = 1  THEN 0 ELSE 1 END, cidade");

            $this->negados = $this->DB_fetch_array("SELECT A.*, B.nome estado, C.nome cidade FROM tb_config_conjuntos_fretes_negar A LEFT JOIN tb_config_estados B ON B.id = A.id_estado LEFT JOIN tb_config_cidades C ON C.id = A.id_cidade WHERE A.id_conjunto = $this->id ORDER BY B.nome, C.nome");
        }

        $this->estados = $this->DB_fetch_array("SELECT * FROM tb_config_estados ORDER BY nome");

        $this->tipos = $this->DB_fetch_array("SELECT * FROM tb_config_conjuntos_fretes_tipos ORDER BY nome");

        $this->renderView($this->getModule(), "edit");
    }

    private function getCustomAction() {

        $this->estados = $this->DB_fetch_array("SELECT * FROM tb_config_estados ORDER BY nome");

        $options = "";
        foreach ($this->estados->rows as $estado) {
            $options .= "<option value='{$estado['id']}'>{$estado['nome']}</option>";
        }

        $content = '
            <div class="form-group form-group-vertical node">
                <div class="col-lg-2"></div>
                <div class="col-lg-1">
                    <select class="form-control input-sm estado" name="estado[]">
                        <option value="">Estado..</option>
                        ' . $options . '
                    </select>
                </div>
                <div class="col-lg-2">
                    <select class="form-control input-sm cidade" name="cidade[]">
                        <option value="">Selecione o Estado..</option>
                    </select>
                </div>
                <div class="col-lg-1">
                    <input placeholder="Prazo" class="form-control prazo" name="prazo[]" type="text" value="" />
                </div>
                <div class="col-lg-1">
                    <input placeholder="Preço" onKeyUp="moeda(this);" onKeyPress="return checkIt(event)" class="form-control preco" name="preco[]" type="text" value="" />
                </div>
                <div class="col-lg-2">
                    <input class="form-control cotacao_manual" name="cotacao_manual[]" type="checkbox" value="1" /> Habilitar Cotação Manual
                </div>

                <div class="col-lg-1">
                    <button type="button" class="btn btn-primary btn-plus">+</button>
                    <button type="button" class="btn btn-danger btn-minus">x</button>
                </div>
            </div>

        ';

        echo $content;
    }

    private function getNegarAction() {

        $this->estados = $this->DB_fetch_array("SELECT * FROM tb_config_estados ORDER BY nome");

        $options = "";
        foreach ($this->estados->rows as $estado) {
            $options .= "<option value='{$estado['id']}'>{$estado['nome']}</option>";
        }

        $content = '
            <div class="form-group form-group-vertical node_negar">
                <label class="col-lg-2 control-label" for=""></label>
                <div class="col-lg-2">
                    <select class="form-control input-sm estado" name="estado_negar[]">
                        <option value="">Estado..</option>
                        ' . $options . '
                    </select>
                </div>
                <div class="col-lg-2">
                    <select class="form-control input-sm cidade" name="cidade_negar[]">
                        <option value="">Selecione o Estado..</option>
                    </select>
                </div>

                <div class="col-lg-2">
                    <button type="button" class="btn btn-primary btn-plus-negar">+</button>
                    <button type="button" class="btn btn-danger btn-minus-negar">x</button>
                </div>
            </div>

        ';

        echo $content;
    }

    private function getCidadesAction() {
        if (!$this->permissions[$this->permissao_ref]['ler'])
            $this->noPermission();

        $content = "";

        if (isset($_POST['id']) && $_POST['id'] != "") {
            $id = $_POST['id'];

            $query = $this->DB_fetch_array("SELECT * FROM tb_config_cidades WHERE id_estado = $id ORDER BY nome");
            if ($query->num_rows) {
                $content .= "<option value=''>Cidade..</option>";
                foreach ($query->rows as $row) {
                    $content .= "<option value='{$row['id']}'>{$row['nome']}</option>";
                }
            }
        } else {
            $content .= "<option value=''>Selecione o Estado..</option>";
        }

        echo $content;
    }

    private function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();

        $id = $this->getParameter("id");

        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");


        $this->inserirRelatorio("Apagou conjunto de frete: [" . $dados->rows[0]['nome'] . "] id: [$id]");
        $this->DB_delete($this->table, "id=$id");

        echo $this->getModule();
    }

    private function saveAction() {
        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormulario($formulario);
        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {

            $resposta = new \stdClass();


            if ($_POST['preco_capital_padrao'] != "")
                $_POST['preco_capital_padrao'] = $this->formataMoedaBd($_POST['preco_capital_padrao']);
            else
                $_POST['preco_capital_padrao'] = "NULL";

            if ($_POST['preco_padrao'] != "")
                $_POST['preco_padrao'] = $this->formataMoedaBd($_POST['preco_padrao']);
            else
                $_POST['preco_padrao'] = "NULL";

            if ($_POST['prazo_padrao'] == "")
                $_POST['prazo_padrao'] = "NULL";

            if ($_POST['prazo_capital_padrao'] == "")
                $_POST['prazo_capital_padrao'] = "NULL";
            
            $data = $this->formularioObjeto($_POST, $this->table);

            if ($formulario->id == "") {
                //criar
                if (!$this->permissions[$this->permissao_ref]['gravar'])
                    exit();

                $verifica = $this->DB_fetch_array("SELECT * FROM tb_config_conjuntos_fretes WHERE nome = '$data->nome'");
                if ($verifica->num_rows) {
                    $resposta->type = "error";
                    $resposta->message = "Este nome já está em uso, por favor, escolha outro nome!";
                    echo json_encode($resposta);
                    exit;
                }

                foreach ($data as $key => $value) {
                    $fields[] = $key;
                    if ($value == "NULL")
                        $values[] = "$value";
                    else
                        $values[] = "'$value'";
                }

                $query = $this->DB_insert($this->table, implode(',', $fields), implode(',', $values));
                $idConjunto = $query->insert_id;
                if ($query->query) {

                    //prazos e preços customizados
                    if (isset($_POST['estado'])) {
                        for ($i = 0; $i < count($_POST['estado']); $i++) {

                            if ($_POST['estado'][$i] != "") {

                                if (isset($_POST['preco'][$i]) && $_POST['preco'][$i] != "")
                                    $_POST['preco'][$i] = $this->formataMoedaBd($_POST['preco'][$i]);
                                else {

                                    if (isset($_POST['cidade'][$i]) && $_POST['cidade'][$i] != "") {
                                        $verificaIsCapital = $this->DB_fetch_array("SELECT * FROM tb_config_cidades WHERE id = {$_POST['cidade'][$i]}");
                                        if ($verificaIsCapital->rows[0]['capital'] == 1)
                                            $_POST['preco'][$i] = $_POST['preco_capital_padrao'];
                                        else
                                            $_POST['preco'][$i] = $_POST['preco_padrao'];
                                    } else {
                                        $_POST['preco'][$i] = $_POST['preco_padrao'];
                                    }
                                }

                                if (!isset($_POST['prazo'][$i]) || $_POST['prazo'][$i] == "") {
                                    if (isset($_POST['cidade'][$i]) && $_POST['cidade'][$i] != "") {
                                        $verificaIsCapital = $this->DB_fetch_array("SELECT * FROM tb_config_cidades WHERE id = {$_POST['cidade'][$i]}");
                                        if ($verificaIsCapital->rows[0]['capital'] == 1)
                                            $_POST['prazo'][$i] = $_POST['prazo_capital_padrao'];
                                        else
                                            $_POST['prazo'][$i] = $_POST['prazo_padrao'];
                                    } else {
                                        $_POST['prazo'][$i] = $_POST['prazo_padrao'];
                                    }
                                }

                                if (!isset($_POST['cidade'][$i]) || $_POST['cidade'][$i] == "")
                                    $_POST['cidade'][$i] = "NULL";


                                $this->DB_insert("tb_config_conjuntos_fretes_customizados", "id_conjunto, id_estado, id_cidade, prazo, preco, cotacao_manual", "$idConjunto, {$_POST['estado'][$i]}, {$_POST['cidade'][$i]}, {$_POST['prazo'][$i]}, {$_POST['preco'][$i]}, {$_POST['cotacao_manual'][$i]} ");
                            }
                        }
                    }

                    //regiões não entregamos
                    if (isset($_POST['estado_negar'])) {
                        for ($i = 0; $i < count($_POST['estado_negar']); $i++) {
                            if ($_POST['estado_negar'][$i] != "") {
                                if (!isset($_POST['cidade_negar'][$i]) || $_POST['cidade_negar'][$i] == "")
                                    $_POST['cidade_negar'][$i] = "NULL";
                                $this->DB_insert("tb_config_conjuntos_fretes_negar", "id_conjunto, id_estado, id_cidade", "$idConjunto, {$_POST['estado_negar'][$i]}, {$_POST['cidade_negar'][$i]}");
                            }
                        }
                    }

                    $resposta->type = "success";
                    $resposta->message = "Registro cadastrado com sucesso!";
                    $this->inserirRelatorio("Cadastrou conjunto de frete: [" . $data->nome . "]");
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            } else {
                //alterar

                if (!$this->permissions[$this->permissao_ref]['editar'])
                    exit();

                $verifica = $this->DB_fetch_array("SELECT * FROM tb_config_conjuntos_fretes WHERE nome = '$data->nome' AND id != $data->id");
                if ($verifica->num_rows) {
                    $resposta->type = "error";
                    $resposta->message = "Este nome já está em uso, por favor, escolha outro nome!";
                    echo json_encode($resposta);
                    exit;
                }

                foreach ($data as $key => $value) {
                    if ($value == "NULL")
                        $fields_values[] = "$key=$value";
                    else
                        $fields_values[] = "$key='$value'";
                }

                $query = $this->DB_update($this->table, implode(',', $fields_values) . " WHERE id=" . $data->id);
                if ($query) {

                    //prazos e preços customizados
                    $this->DB_delete("tb_config_conjuntos_fretes_customizados", "id_conjunto = $data->id");
                    if (isset($_POST['estado'])) {
                        for ($i = 0; $i < count($_POST['estado']); $i++) {

                            if ($_POST['estado'][$i] != "") {

                                if (isset($_POST['preco'][$i]) && $_POST['preco'][$i] != "")
                                    $_POST['preco'][$i] = $this->formataMoedaBd($_POST['preco'][$i]);
                                else {

                                    if (isset($_POST['cidade'][$i]) && $_POST['cidade'][$i] != "") {
                                        $verificaIsCapital = $this->DB_fetch_array("SELECT * FROM tb_config_cidades WHERE id = {$_POST['cidade'][$i]}");
                                        if ($verificaIsCapital->rows[0]['capital'] == 1)
                                            $_POST['preco'][$i] = $_POST['preco_capital_padrao'];
                                        else
                                            $_POST['preco'][$i] = $_POST['preco_padrao'];
                                    } else {
                                        $_POST['preco'][$i] = $_POST['preco_padrao'];
                                    }
                                }

                                if (!isset($_POST['prazo'][$i]) || $_POST['prazo'][$i] == "") {
                                    if (isset($_POST['cidade'][$i]) && $_POST['cidade'][$i] != "") {
                                        $verificaIsCapital = $this->DB_fetch_array("SELECT * FROM tb_config_cidades WHERE id = {$_POST['cidade'][$i]}");
                                        if ($verificaIsCapital->rows[0]['capital'] == 1)
                                            $_POST['prazo'][$i] = $_POST['prazo_capital_padrao'];
                                        else
                                            $_POST['prazo'][$i] = $_POST['prazo_padrao'];
                                    } else {
                                        $_POST['prazo'][$i] = $_POST['prazo_padrao'];
                                    }
                                }


                                if (!isset($_POST['cidade'][$i]) || $_POST['cidade'][$i] == "")
                                    $_POST['cidade'][$i] = "NULL";



                                $this->DB_insert("tb_config_conjuntos_fretes_customizados", "id_conjunto, id_estado, id_cidade, prazo, preco, cotacao_manual", "$data->id, {$_POST['estado'][$i]}, {$_POST['cidade'][$i]}, {$_POST['prazo'][$i]}, {$_POST['preco'][$i]}, {$_POST['cotacao_manual'][$i]} ");
                            }
                        }
                    }

                    //regiões não entregamos
                    $this->DB_delete("tb_config_conjuntos_fretes_negar", "id_conjunto = $data->id");
                    if (isset($_POST['estado_negar'])) {
                        for ($i = 0; $i < count($_POST['estado_negar']); $i++) {
                            if ($_POST['estado_negar'][$i] != "") {
                                if (!isset($_POST['cidade_negar'][$i]) || $_POST['cidade_negar'][$i] == "")
                                    $_POST['cidade_negar'][$i] = "NULL";
                                $this->DB_insert("tb_config_conjuntos_fretes_negar", "id_conjunto, id_estado, id_cidade", "$data->id, {$_POST['estado_negar'][$i]}, {$_POST['cidade_negar'][$i]}");
                            }
                        }
                    }

                    $resposta->type = "success";
                    $resposta->message = "Registro alterado com sucesso!";
                    $this->inserirRelatorio("Alterou conjunto de frete: [" . $data->nome . "]");
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            }

            echo json_encode($resposta);
        }
    }

    private function validaFormulario($form) {

        $resposta = new \stdClass();
        $resposta->return = true;

        if ($form->nome == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "nome";
            $resposta->return = false;
            return $resposta;
        } else if ($form->id_tipo == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "id_tipo";
            $resposta->return = false;
            return $resposta;
        } else if ($form->prazo_capital_padrao == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "prazo_capital_padrao";
            $resposta->return = false;
            return $resposta;
        } else if ($form->preco_capital_padrao == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "preco_capital_padrao";
            $resposta->return = false;
            return $resposta;
        } else if ($form->prazo_padrao == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "prazo_padrao";
            $resposta->return = false;
            return $resposta;
        } else if ($form->preco_padrao == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "preco_padrao";
            $resposta->return = false;
            return $resposta;
        } else {
            return $resposta;
        }
    }

    private function duplicateAction() {
        $this->id = $this->getParameter("id");

        //edit
        if (!$this->permissions[$this->permissao_ref]['gravar'])
            $this->noPermission();

        $query = $this->DB_fetch_array("SELECT A.*, DATE_FORMAT(A.data, '%d/%m/%Y %H:%i') data FROM $this->table A WHERE A.id = $this->id");

        $this->registro = $query->rows[0];

        $this->customizados = $this->DB_fetch_array("SELECT A.*, B.nome estado, C.nome cidade FROM tb_config_conjuntos_fretes_customizados A LEFT JOIN tb_config_estados B ON B.id = A.id_estado LEFT JOIN tb_config_cidades C ON C.id = A.id_cidade WHERE A.id_conjunto = $this->id ORDER BY B.nome,  CASE WHEN C.nome IS NULL  THEN 0 WHEN C.capital = 1  THEN 0 ELSE 1 END, cidade");

        $this->negados = $this->DB_fetch_array("SELECT A.*, B.nome estado, C.nome cidade FROM tb_config_conjuntos_fretes_negar A LEFT JOIN tb_config_estados B ON B.id = A.id_estado LEFT JOIN tb_config_cidades C ON C.id = A.id_cidade WHERE A.id_conjunto = $this->id ORDER BY B.nome, C.nome");

        $this->estados = $this->DB_fetch_array("SELECT * FROM tb_config_estados ORDER BY nome");

        $this->tipos = $this->DB_fetch_array("SELECT * FROM tb_config_conjuntos_fretes_tipos ORDER BY nome");

        $this->renderView($this->getModule(), "duplicate");
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
