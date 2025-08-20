<?php

use System\Core\Bootstrap;

Marketing::setAction();

class Marketing extends Bootstrap {

    public $module = "";
    public $permissao_ref = "pedidos-pedidos";
    public $table = "tb_marketing_financeiro";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
            exit;
        }
        $this->getAllPermissions();

        $this->module_icon = "icomoon-icon-megaphone";
        $this->module_link = "marketing";
        $this->module_title = "Marketing";
        $this->retorno = "marketing";
    }

    private function indexAction() {

        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->list = $this->DB_fetch_array("SELECT *, DATE_FORMAT(data, '%d/%m/%Y') FROM $this->table");

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

        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar'])
                $this->noPermission();

            $query = $this->DB_fetch_array("SELECT *, DATE_FORMAT(data, '%d/%m/%Y') data  FROM $this->table WHERE id = $this->id");

            $this->registro = $query->rows[0];
        }

        $this->renderView($this->getModule(), "edit");
    }

    private function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();

        $id = $this->getParameter("id");

        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");

        $this->inserirRelatorio("Apagou Marketing Financeiro: [" . $dados->rows[0]['descricao'] . "] id: [$id]");
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
            $data = $this->formularioObjeto($_POST, $this->table);
            $data->data = $this->formataDataDeMascara($_POST['data']);
            $data->valor = $this->formataMoedaBd($_POST['valor']);

            if ($formulario->id == "") {
                //criar
                if (!$this->permissions[$this->permissao_ref]['gravar'])
                    exit();

                unset($data->id);

                if($formulario->parcelas > 1){
                    $data->valor = $data->valor/$formulario->parcelas;
                    $desc = $data->descricao;
                    $data_p = new DateTime($data->data);
                    for ($i=1; $i <= $formulario->parcelas; $i++) { 
                        $data->descricao = $desc.' ('.$i.'/'.$formulario->parcelas.')';
                        if($i>1) $data->data = $this->getSameDayNextMonth($data_p, ($i-1))->format('Y-m-d');
                        $fields = array();
                        $values = array();
                        foreach ($data as $key => $value) {
                            $fields[] = $key;
                            if ($value == "NULL")
                                $values[] = "$value";
                            else
                                $values[] = "'$value'";
                        }
                        $query = $this->DB_insert($this->table, implode(',', $fields), implode(',', $values));
                    }
                }else{
                    foreach ($data as $key => $value) {
                        $fields[] = $key;
                        if ($value == "NULL")
                            $values[] = "$value";
                        else
                            $values[] = "'$value'";
                    }
                    $query = $this->DB_insert($this->table, implode(',', $fields), implode(',', $values));
                }
                
                if ($query->query) {
                    $resposta->type = "success";
                    $resposta->message = "Registro cadastrado com sucesso!";
                    $this->inserirRelatorio("Cadastrou Marketing Financeiro: [" . $data->descricao . "]");
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            } else {
                //alterar

                if (!$this->permissions[$this->permissao_ref]['editar'])
                    exit();

                if($formulario->parcelas > 1){
                    $id = $data->id;
                    unset($data->id);
                    $data->valor = $data->valor/$formulario->parcelas;
                    $desc = $data->descricao;
                    $data_p = new DateTime($data->data);
                    for ($i=1; $i <= $formulario->parcelas; $i++) { 
                        $data->descricao = $desc.' ('.$i.'/'.$formulario->parcelas.')';
                        if($i>1) $data->data = $this->getSameDayNextMonth($data_p, ($i-1))->format('Y-m-d');
                        $fields = array();
                        $values = array();
                        foreach ($data as $key => $value) {
                            $fields[] = $key;
                            if ($value == "NULL")
                                $values[] = "$value";
                            else
                                $values[] = "'$value'";
                        }
                        $query = $this->DB_insert($this->table, implode(',', $fields), implode(',', $values));
                    }
                    $this->DB_delete($this->table, "id=$id");
                }else{
                    foreach ($data as $key => $value) {
                        if ($value == "NULL")
                            $fields_values[] = "$key=$value";
                        else
                            $fields_values[] = "$key='$value'";
                    }
                    $fields_values[] = "updated_at=NOW()";
                    $query = $this->DB_update($this->table, implode(',', $fields_values) . " WHERE id=" . $data->id);
                }

                if ($query) {
                    $resposta->type = "success";
                    $resposta->message = "Registro alterado com sucesso!";
                    $this->inserirRelatorio("Alterou Marketing Financeiro: [" . $data->descricao . "]");
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

        if ($form->categoria == "") {
            $resposta->type = "validation";
            $resposta->message = "Selecione uma categoria";
            $resposta->field = "categoria";
            $resposta->return = false;
            return $resposta;
        } else if ($form->descricao == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha esse campo corretamente";
            $resposta->field = "descricao";
            $resposta->return = false;
            return $resposta;
        } else if ($form->data == "") {
            $resposta->type = "validation";
            $resposta->message = "Informe a data do lançamento";
            $resposta->field = "data";
            $resposta->return = false;
            return $resposta;
        } else if ($form->valor == "") {
            $resposta->type = "validation";
            $resposta->message = "Informe o valor do lançamento";
            $resposta->field = "valor";
            $resposta->return = false;
            return $resposta;
        } else {
            return $resposta;
        }
    }
    function getSameDayNextMonth(DateTime $startDate, $numberOfMonthsToAdd = 1) {
        $startDateDay = (int) $startDate->format('j');
        $startDateMonth = (int) $startDate->format('n');
        $startDateYear = (int) $startDate->format('Y');

        $numberOfYearsToAdd = floor(($startDateMonth + $numberOfMonthsToAdd) / 12);
        if ((($startDateMonth + $numberOfMonthsToAdd) % 12) === 0) {
          $numberOfYearsToAdd--;
        }
        $year = $startDateYear + $numberOfYearsToAdd;

        $month = ($startDateMonth + $numberOfMonthsToAdd) % 12;
        if ($month === 0) {
          $month = 12;
        }
        $month = sprintf('%02s', $month);

        $numberOfDaysInMonth = (new DateTime("$year-$month-01"))->format('t');
        $day = $startDateDay;
        if ($startDateDay > $numberOfDaysInMonth) {
          $day = $numberOfDaysInMonth;
        }
        $day = sprintf('%02s', $day);

        return new DateTime("$year-$month-$day");
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
