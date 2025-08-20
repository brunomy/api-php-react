<?php

session_start();

require_once '../sistema/System/Core/Loader.php';

use System\Core\Bootstrap;

require_once "../_system.php";

$sistema = new _sys();

$sistema->DB_connect();

$formulario = $sistema->formularioObjeto($_POST);

$validacao = validaFormulario($formulario);


if (!isset($_SESSION["seo_session"])) {
    $_SESSION["seo_session"] = uniqid();
}

if (!$validacao->return) {
    echo json_encode($validacao);
} else {

    $resposta = new stdClass();
    $resposta->time = 4000;

    $query = $sistema->DB_insert('tb_pedidos_documentos', 'id_pedido, cartao_frente, cartao_verso, documento_frente, documento_verso, selfie', $formulario->id.',"'.$validacao->cartao_front.'"'.',"'.$validacao->cartao_back.'"'.',"'.$validacao->cnh_front.'"'.',"'.$validacao->cnh_back.'"'.',"'.$validacao->selfie.'"');

    $inserted_id = $query->insert_id;

    if($query->query){
        $resposta->type = "success";
        $resposta->time = 4000;
        $resposta->message = "Documentos enviados com sucesso!";
        $sistema->alterarStatusPedido($formulario->id, 24);
        $sistema->inserirRelatorio("Documentos: [Pedido " . $formulario->id . "] Id: [" . $inserted_id . "]");
    }else{
        $resposta->type = "error";
        $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
    }

    echo json_encode($resposta);
}

$sistema->DB_disconnect();

function validaFormulario($form) {

    $resposta = new stdClass();
    $resposta->return = true;

    global $sistema, $crop_sizes;

    $crop = array(array("width" => 200, "height" => 200, "best_fit" => true));

    $resposta->cartao_front = "";
    $resposta->cartao_back = "";
    $resposta->cnh_front = "";
    $resposta->cnh_back = "";
    $resposta->selfie = "";

    if (is_uploaded_file($_FILES["upload-cartao-front"]["tmp_name"])) {
        $recorte = $crop;
        $arquivo = explode(".", $_FILES["upload-cartao-front"]["name"]);
        if(end($arquivo)=="pdf")$recorte = "";
        $up_cartao_front = $sistema->uploadFile("upload-cartao-front", array("jpg", "jpeg", "png", "pdf"), $recorte);
        if ($up_cartao_front->return) {
            $resposta->cartao_front = $up_cartao_front->file_uploaded;
        }
    }

    if (is_uploaded_file($_FILES["upload-cartao-back"]["tmp_name"])) {
        $recorte = $crop;
        $arquivo = explode(".", $_FILES["upload-cartao-back"]["name"]);
        if(end($arquivo)=="pdf")$recorte = "";
        $up_cartao_back = $sistema->uploadFile("upload-cartao-back", array("jpg", "jpeg", "png", "pdf"), $recorte);
        if ($up_cartao_back->return) {
            $resposta->cartao_back = $up_cartao_back->file_uploaded;
        }
    }

    if (is_uploaded_file($_FILES["upload-cnh-front"]["tmp_name"])) {
        $recorte = $crop;
        $arquivo = explode(".", $_FILES["upload-cnh-front"]["name"]);
        if(end($arquivo)=="pdf")$recorte = "";
        $cnh_front = $sistema->uploadFile("upload-cnh-front", array("jpg", "jpeg", "png", "pdf"), $recorte);
        if ($cnh_front->return) {
            $resposta->cnh_front = $cnh_front->file_uploaded;
        }
    }

    if (is_uploaded_file($_FILES["upload-cnh-back"]["tmp_name"])) {
        $recorte = $crop;
        $arquivo = explode(".", $_FILES["upload-cnh-back"]["name"]);
        if(end($arquivo)=="pdf")$recorte = "";
        $cnh_back = $sistema->uploadFile("upload-cnh-back", array("jpg", "jpeg", "png", "pdf"), $recorte);
        if ($cnh_back->return) {
            $resposta->cnh_back = $cnh_back->file_uploaded;
        }
    }

    if (is_uploaded_file($_FILES["upload-selfie"]["tmp_name"])) {
        $recorte = $crop;
        $arquivo = explode(".", $_FILES["upload-selfie"]["name"]);
        if(end($arquivo)=="pdf")$recorte = "";
        $selfie = $sistema->uploadFile("upload-selfie", array("jpg", "jpeg", "png", "pdf"), $recorte);
        if ($selfie->return) {
            $resposta->selfie = $selfie->file_uploaded;
        }
    }

    if (!isset($up_cartao_front)) {
        $resposta->type = "attention";
        $resposta->message = "Anexar a foto da frente do cartão de crédito";
        $resposta->return = false;
    } else if (isset($up_cartao_front) && !$up_cartao_front->return) {
        $resposta->type = "error";
        $resposta->message = "Cartão de Crédito: ".$up_cartao_front->message;
        $resposta->return = false;
    } else if (!isset($cnh_front)) {
        $resposta->type = "attention";
        $resposta->message = "Anexar a foto de um documento";
        $resposta->return = false;
    } else if (isset($cnh_front) && !$cnh_front->return) {
        $resposta->type = "error";
        $resposta->message = "Documento CNH ou RG e CPF: ". $cnh_front->message;
        $resposta->return = false;
    } else if (!isset($selfie)) {
        $resposta->type = "attention";
        $resposta->message = "Anexar a foto do titular com documento ao lado do rosto";
        $resposta->return = false;
    } else if (isset($selfie) && !$selfie->return) {
        $resposta->type = "error";
        $resposta->message = "Foto do Títular: ".$selfie->message;
        $resposta->return = false;
    }

    return $resposta;
}


?>