<?php

namespace classes;

if (!isset($_SESSION["seo_session"])) {
    $_SESSION["seo_session"] = uniqid();
}

use System\Core\Bootstrap;

$p = new Bootstrap();

if ($p->getParameter('agendor')) {
    Agendor::setAction();
}

class Agendor {

    private $token = "4735cdc4-27a7-40db-bbd7-415a15c7c809";

    function __construct() {

        $this->module = "";
    }

    private function call($chamada) {

        $url = "https://api.agendor.com.br/v1/$chamada";

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Token ".$this->token,
            "Accept: application/json"
        ));

        $resonse = curl_exec($ch);
        curl_close($ch);
        return json_decode($resonse);

    }

    public function buscaVendedorPorPedido($cpf, $email, $telefone) {
        $sistema = new Bootstrap();

        $id_pessoa = 0;
        $id_vendedor = 0;
        $data_limite = strtotime("-1 week");
        $array_de_negocios = array();

        //PROCURA CLIENTE POR CPF
        if($cpf!=''){
            $cpf = preg_replace('/\D/', '', $cpf);
            if(strlen($cpf)==11){
                $pessoa = $this->call("people?q=$cpf");
                //$sistema->inserirRelatorio('AGENDOR: procurou cpf:'.$cpf);
                if(count($pessoa)){
                    $id_pessoa = (int)$pessoa[0]->personId;
                    //$sistema->inserirRelatorio('AGENDOR: encontrou cpf:'.$cpf.' em PESSOAS (ID:'.$id_pessoa.')');
                }else{
                    $empresa = $this->call("organizations?q=$cpf");
                    if(count($empresa)){
                        $id_pessoa = (int)$empresa[0]->organizationId;
                        //$sistema->inserirRelatorio('AGENDOR: encontrou cpf:'.$cpf.' em ORGANIZAÇÕES (ID:'.$id_pessoa.')');
                    }
                }
            }
        }

        //PROCURA CLIENTE POR EMAIL
        if($email!=''){
            if(!$id_pessoa){
                $pessoa = $this->call("people?q=$email");
                //$sistema->inserirRelatorio('AGENDOR: procurou email:'.$email);
                if(count($pessoa)){
                    $id_pessoa = (int)$pessoa[0]->personId;
                    //$sistema->inserirRelatorio('AGENDOR: encontrou email:'.$email.' em PESSOAS (ID:'.$id_pessoa.')');
                }else{
                    $empresa = $this->call("organizations?q=$email");
                    if(count($empresa)){
                        $id_pessoa = (int)$empresa[0]->organizationId;
                        //$sistema->inserirRelatorio('AGENDOR: encontrou cpf:'.$email.' em ORGANIZAÇÕES (ID:'.$id_pessoa.')');
                    }
                }
            }
        }

        //PROCURA CLIENTE POR TELEFONE
        if($telefone!=''){
            if(!$id_pessoa){
                $telefone = preg_replace('/\D/', '', $telefone);
                $pessoa = $this->call("people?q=$telefone");
                //$sistema->inserirRelatorio('AGENDOR: procurou telefone:'.$telefone);
                if(count($pessoa)){
                    $id_pessoa = (int)$pessoa[0]->personId;
                    //$sistema->inserirRelatorio('AGENDOR: encontrou telefone:'.$telefone.' em PESSOAS (ID:'.$id_pessoa.')');
                }else{
                    $empresa = $this->call("organizations?q=$telefone");
                    if(count($empresa)){
                        $id_pessoa = (int)$empresa[0]->organizationId;
                        //$sistema->inserirRelatorio('AGENDOR: encontrou cpf:'.$telefone.' em ORGANIZAÇÕES (ID:'.$id_pessoa.')');
                    }
                }
            }


            //PROCURA POR CLIENTE COMPARANDO TELEFONES
            if(!$id_pessoa){
                $pessoas = $this->call("people");
                $flag_fone = true;
                //$sistema->inserirRelatorio('AGENDOR: comparou telefone:'.$telefone);
                if($pessoas){
                    foreach($pessoas as $pessoa){
                        if($flag_fone){
                            foreach($pessoa->phones as $fone){
                                if($flag_fone && strlen($fone->number) && $this->comparaTelefones($telefone, $fone->number)){
                                    $id_pessoa = $pessoa->personId;
                                    //$sistema->inserirRelatorio('AGENDOR: encontrou telefone:'.$telefone.' em PESSOAS (ID:'.$id_pessoa.')');
                                    $flag_fone = false;
                                    break;
                                }
                            }
                        }else{
                            break;
                        }
                    }
                }
            }

            //PROCURA POR ORGANIZAÇÃO COMPARANDO TELEFONES
            if(!$id_pessoa){
                $empresas = $this->call("organizations");
                $flag_fone = true;
                //$sistema->inserirRelatorio('AGENDOR: comparou telefone:'.$telefone);
                if($empresas){
                    foreach($empresas as $empresa){
                        if($flag_fone){
                            foreach($empresa->phones as $fone){
                                if($flag_fone && strlen($fone->number) && $this->comparaTelefones($telefone, $fone->number)){
                                    $id_pessoa = $empresa->personId;
                                    //$sistema->inserirRelatorio('AGENDOR: encontrou telefone:'.$telefone.' em ORGANIZAÇÕES (ID:'.$id_pessoa.')');
                                    $flag_fone = false;
                                    break;
                                }
                            }
                        }else{
                            break;
                        }
                    }
                }
            }
        }

        //PROCURA POR ALGUM NEGÓCIO 
        if($id_pessoa){
            $negocios = $this->call("deals");
            //$sistema->inserirRelatorio('AGENDOR: procurou negocios da pessoa '.$id_pessoa);
            if(count($negocios)){
                foreach($negocios as $negocio){
                    if($negocio->person->personId == $id_pessoa){
                        $array_de_negocios[] = $negocio->dealId;
                        $data = explode('T',$negocio->createTime);
                        $data = strtotime($data[0]);
                        if($data >= $data_limite){
                            $id_vendedor = $negocio->userOwner->userId;
                            //$sistema->inserirRelatorio('AGENDOR: encontrou negocios da pessoa '.$id_pessoa.' com vendedor '.$id_vendedor);
                        }
                    }else if($negocio->organization->organizationId == $id_pessoa){
                        $array_de_negocios[] = $negocio->dealId;
                        $data = explode('T',$negocio->createTime);
                        $data = strtotime($data[0]);
                        if($data >= $data_limite){
                            $id_vendedor = $negocio->userOwner->userId;
                        }
                    }
                }
            }
        }

        //PROCURA POR ALGUMA TAREFA RELACIONADA À NEGÓCIOS
        if(count($array_de_negocios)){
            $tarefas = $this->call("tasks");
            if(count($tarefas)){
                foreach($tarefas as $tarefa){
                    if(in_array($tarefa->deal->dealId, $array_de_negocios)){
                        $data = explode('T',$tarefa->createTime);
                        $data = strtotime($data[0]);
                        if($data >= $data_limite){
                            $id_vendedor = $tarefa->user->userId;
                        }
                    }
                }
            }
        }

        //PROCURA POR ALGUMA TAREFA RELACIONADA DIRETAMENTE À PESSOA
        if($id_pessoa && !$id_vendedor){
            if(!count($tarefas)) $tarefas = $this->call("tasks");
            //$sistema->inserirRelatorio('AGENDOR: procurou tarefas da pessoa '.$id_pessoa);
            if(count($tarefas)){
                foreach($tarefas as $tarefa){
                    if($tarefa->person->personId == $id_pessoa){
                        $data = explode('T',$tarefa->createTime);
                        $data = strtotime($data[0]);
                        if($data >= $data_limite){
                            $id_vendedor = $tarefa->user->userId;
                            //$sistema->inserirRelatorio('AGENDOR: encontrou tarefa da pessoa '.$id_pessoa.' com vendedor '.$id_vendedor);
                        }
                    }
                }
            }
        }

        return $id_vendedor;

    }

    private function comparaTelefones($fone1, $fone2){
        $fone1 = preg_replace('/\D/', '', $fone1); // somente numeros
        $fone2 = preg_replace('/\D/', '', $fone2); // somente numeros

        if(strlen($fone1) == strlen($fone2)){
            if($fone1 == $fone2){
                return 1;
            }else{
                return 0;
            }
        }else{

            $phone1 = substr($fone1, 0, 2).substr($fone1, -8);
            $phone2 = substr($fone2, 0, 2).substr($fone2, -8);

            //compara os dois primeiros e os ultimos oito digitos
            if($phone1 == $phone2){
                return 1;
            }else{
                return 0;
            }

        }
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
        $instance = new Agendor();

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
                $instance->errorAction();
        } else {
            $instance->errorAction();
        }
    }

}
