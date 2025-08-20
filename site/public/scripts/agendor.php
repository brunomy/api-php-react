<?php 
ob_start();
require_once '../sistema/System/Core/Loader.php';

use System\Core\Bootstrap;
use classes\Product;
use classes\Agendor;

require_once "../_system.php";

$sistema = new Product();
$agendor = new Agendor();


if(isset($_GET['buscar'])){
	if($_GET['buscar'] == "tarefas") $chamada = "tasks";
	if($_GET['buscar'] == "pessoas") $chamada = "people";
	if($_GET['buscar'] == "empresas") $chamada = "organizations";
	if($_GET['buscar'] == "negocios") $chamada = "deals";

    echo date( "Y-m-d", strtotime("+1 week"));

    $ch = curl_init();

    //curl_setopt($ch, CURLOPT_URL, "https://api.agendor.com.br/v1/people?q=35722204838");
    curl_setopt($ch, CURLOPT_URL, "https://api.agendor.com.br/v1/$chamada");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Authorization: Token 4735cdc4-27a7-40db-bbd7-415a15c7c809",
        "Accept: application/json"
    ));

    $deals = curl_exec($ch);
    curl_close($ch);
    $deals = json_decode($deals);

    echo "<pre>";print_r($deals);echo "</pre>";
}else{

    if(isset($_GET['cpf']) || isset($_GET['email']) || isset($_GET['fone'])){
        
        if(isset($_GET['email'])) $get_email = $_GET['email']; else $get_email = '';
        if(isset($_GET['cpf'])) $get_cpf = $_GET['cpf']; else $get_cpf = '';
        if(isset($_GET['fone'])) $get_fone = $_GET['fone']; else $get_fone = '';

        echo 'Email: '. $get_email .'<br>';
        echo 'CPF: '. $get_cpf .'<br>';
        echo 'Fone: '. $get_fone .'<br><br><hr><br><br>';
        ob_flush();

        echo 'Vendedor: '.buscaVendedorPorPedido($get_cpf,$get_email,$get_fone);

        echo '<br><hr><br>Classe do sistema retornou: ' .$agendor->buscaVendedorPorPedido($get_email,$get_email,$get_fone);

    }
}

function call($chamada) {

    $url = "https://api.agendor.com.br/v1/$chamada";

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Authorization: Token 4735cdc4-27a7-40db-bbd7-415a15c7c809",
        "Accept: application/json"
    ));

    $resonse = curl_exec($ch);
    curl_close($ch);
    return json_decode($resonse);

}

function buscaVendedorPorPedido($cpf, $email, $telefone) {

    $id_pessoa = 0;
    $id_vendedor = 0;
    $data_limite = strtotime("-1 week");
    $array_de_negocios = array();

    //PROCURA CLIENTE POR CPF
    if($cpf!=''){
        $cpf = preg_replace('/\D/', '', $cpf);
        if(strlen($cpf)==11){
            $pessoa = call("people?q=$cpf");
            if(count($pessoa)){
                $id_pessoa = (int)$pessoa[0]->personId;
                echo 'achou pessoa pelo cpf<br>';
            }else{
                $empresa = call("organizations?q=$cpf");
                if(count($empresa)){
                    $id_pessoa = (int)$empresa[0]->organizationId;
                    echo 'achou empresa pelo cnpj<br>';
                }
            }
        }
    }

    //PROCURA CLIENTE POR EMAIL
    if($email!=''){
        if(!$id_pessoa){
            $pessoa = call("people?q=$email");
            if(count($pessoa)){
                $id_pessoa = (int)$pessoa[0]->personId;
                echo 'achou pessoa pelo email<br>';
            }else{
                $empresa = call("organizations?q=$email");
                if(count($empresa)){
                    $id_pessoa = (int)$empresa[0]->organizationId;
                    echo 'achou empresa pelo email<br>';
                }
            }
        }
    }

    if($telefone!=''){

        //PROCURA CLIENTE POR TELEFONE
        if(!$id_pessoa){
            $telefone = preg_replace('/\D/', '', $telefone);
            $pessoa = call("people?q=$telefone");
            if(count($pessoa)){
                $id_pessoa = (int)$pessoa[0]->personId;
                echo 'achou pessoa pelo telefone<br>';
            }else{
                $empresa = call("organizations?q=$telefone");
                if(count($empresa)){
                    $id_pessoa = (int)$empresa[0]->organizationId;
                    echo 'achou empresa pelo telefone<br>';
                }
            }
        }

        //PROCURA POR CLIENTE COMPARANDO TELEFONES
        if(!$id_pessoa){
            $pessoas = call("people");
            $flag_fone = true;
            foreach($pessoas as $pessoa){
                if($flag_fone){
                    foreach($pessoa->phones as $fone){
                        if($flag_fone && strlen($fone->number) && comparaTelefones($telefone, $fone->number)){
                            $id_pessoa = $pessoa->personId;
                            echo 'achou pessoa comparando telefone<br>';
                            $flag_fone = false;
                            break;
                        }
                    }
                }else{
                    break;
                }
            }
        }

        //PROCURA POR EMPRESAS COMPARANDO TELEFONES
        if(!$id_pessoa){
            $empresas = call("organizations");
            $flag_fone = true;
            foreach($empresas as $empresa){
                if($flag_fone){
                    foreach($empresa->phones as $fone){
                        if($flag_fone && strlen($fone->number) && comparaTelefones($telefone, $fone->number)){
                            $id_pessoa = $empresa->organizationId;
                            echo 'achou empresa comparando telefone<br>';
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

    //PROCURA POR ALGUM NEGÓCIO 
    if($id_pessoa){
        $negocios = call("deals");
        if(count($negocios)){
            foreach($negocios as $negocio){
                if($negocio->person->personId == $id_pessoa){
                    $array_de_negocios[] = $negocio->dealId;
                    $data = explode('T',$negocio->createTime);
                    $data = strtotime($data[0]);
                    if($data >= $data_limite){
                        $id_vendedor = $negocio->userOwner->userId;
                        echo 'encontrou vendedor na lista de negocios de pessoas<br>';
                    }
                }else if($negocio->organization->organizationId == $id_pessoa){
                    $array_de_negocios[] = $negocio->dealId;
                    $data = explode('T',$negocio->createTime);
                    $data = strtotime($data[0]);
                    if($data >= $data_limite){
                        $id_vendedor = $negocio->userOwner->userId;
                        echo 'encontrou vendedor na lista de negocios de empresas<br>';
                    }
                }
            }
        }
    }

    //PROCURA POR ALGUMA TAREFAS RELACIONADA À NEGÓCIOS
    if(count($array_de_negocios)){
        $tarefas = call("tasks");
        if(count($tarefas)){
            foreach($tarefas as $tarefa){
                if(in_array($tarefa->deal->dealId, $array_de_negocios)){
                    $data = explode('T',$tarefa->createTime);
                    $data = strtotime($data[0]);
                    if($data >= $data_limite){
                        $id_vendedor = $tarefa->user->userId;
                        echo 'encontrou vendedor na lista de tarefas relacionado aos negocios da pessoa<br>';
                    }
                }
            }
        }
    }

    //PROCURA POR ALGUMA TAREFAS RELACIONADA DIRETAMENTE À PESSOA
    if($id_pessoa && !$id_vendedor){
        if(!count($tarefas)) $tarefas = call("tasks");
        if(count($tarefas)){
            foreach($tarefas as $tarefa){
                if($tarefa->person->personId == $id_pessoa){
                    $data = explode('T',$tarefa->createTime);
                    $data = strtotime($data[0]);
                    if($data >= $data_limite){
                        $id_vendedor = $tarefa->user->userId;
                        echo 'encontrou vendedor na lista de tarefas<br>';
                    }
                }
            }
        }
    }

    return $id_vendedor;
}

function comparaTelefones($fone1, $fone2){
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

        if($phone1 == $phone2){
            return 1;
        }else{
            return 0;
        }

    }
}


?>