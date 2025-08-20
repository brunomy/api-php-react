<?php

session_start();

require_once '../sistema/System/Core/Loader.php';

use System\Core\Bootstrap;

require_once "../_system.php";

$sistema = new _sys();

$sistema->DB_connect();


if (!isset($_SESSION["seo_session"])) {
    $_SESSION["seo_session"] = uniqid();
}

$post = array();

foreach ($_POST as $key => $value) {
    if ($_POST[$key] != "") {
        $post[$key] = $value;
    }

    if (isset($_FILES["arquivo-" . $key]) && $_FILES["arquivo-" . $key] != "") {
        $post["arquivo-" . $key] = $_FILES["arquivo-" . $key]['name'];
    }
}

$_POST = $post;

if (isset($_POST['id_produto']) && $_POST['id_produto'] != "") {

    $conjuntos = $sistema->DB_fetch_array("SELECT A.* FROM tb_produtos_conjuntos_atributos A INNER JOIN tb_produtos_atributos B ON B.id_conjunto_atributo = A.id WHERE A.id_produto = {$_POST['id_produto']} GROUP BY A.id ORDER BY A.ordem");

    function getAtributo($id) {
        global $sistema;
        $query = $sistema->DB_fetch_array("SELECT * FROM tb_produtos_atributos A WHERE A.id = $id");
        return $query->rows[0];
    }

    $resposta = new \stdClass();
    if (isset($_POST['produto']) && $_POST['produto'] != "") {
        //atualizar
        $quantidade = "";

        if (isset($_POST['quantidade']) && $_POST['quantidade'] != "")
            $quantidade = $sistema->DB_anti_injection($_POST['quantidade']);

        $query = $sistema->DB_update("tb_carrinho_produtos_historico", "quantidade = $quantidade WHERE id = {$_POST['produto']} AND session = '{$_SESSION["seo_session"]}'");

        if ($query) {

            $sistema->DB_delete("tb_carrinho_atributos_historico", "id_carrinho_produto_historico = {$_POST['produto']}");
            foreach ($conjuntos->rows as $conjunto) {
                $atributo = getAtributo($_POST[$conjunto['id']]);
                $arquivo = "";
                $valor = "";
                $cor = "";
                $texto = "";
                if (isset($_POST["arquivo-" . $conjunto['id']]) && $_POST["arquivo-" . $conjunto['id']] != "") {
                    $valor = $_POST["arquivo-" . $conjunto['id']];
                    $upload = $sistema->uploadFile("arquivo-{$conjunto['id']}", array("jpg", "jpeg", "gif", "png"), '');
                    if ($upload->return) {
                        $arquivo = $upload->file_uploaded;
                    }
                    @unlink($sistema->upload_folder . $_POST["arquivo-novo-" . $conjunto['id']]);
                } else {
                    if (isset($_POST["arquivo-nome-" . $conjunto['id']]) && $_POST["arquivo-nome-" . $conjunto['id']] != "") {
                        $valor = $_POST["arquivo-nome-" . $conjunto['id']];
                    }

                    if (isset($_POST["arquivo-novo-" . $conjunto['id']]) && $_POST["arquivo-novo-" . $conjunto['id']] != "") {
                        $arquivo = $_POST["arquivo-novo-" . $conjunto['id']];
                    }
                }
                if (isset($_POST["cor-" . $conjunto['id']]) && $_POST["cor-" . $conjunto['id']] != "") {
                    $cor = $_POST["cor-" . $conjunto['id']];
                }
                if (isset($_POST["texto-" . $conjunto['id']]) && $_POST["texto-" . $conjunto['id']] != "") {
                    $texto = $_POST["texto-" . $conjunto['id']];
                }
                $sistema->DB_insert("tb_carrinho_atributos_historico", "id_carrinho_produto_historico,id_conjunto_atributo,id_atributo,selecionado,nome_atributo,custo,arquivo,valor,cor, texto,nome_conjunto", "   
                            {$_POST['produto']},
                            {$conjunto['id']},
                            {$_POST[$conjunto['id']]},
                            1,
                            '{$atributo['nome']}',
                            '{$atributo['custo']}',
                            '$arquivo',
                            '$valor',
                            '$cor',
                            '$texto',
                            '{$conjunto['nome']}'
                        ");
            }

            $resposta->time = 2000;
            $resposta->type = "success";
            $resposta->message = "Produto alterado com sucesso!";
            $sistema->inserirRelatorio("Alterou produto no carrinho");
        } else {
            $resposta->type = "error";
            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
        }
    } else {
        //adicionar
        $idProduto = "NULL";
        $idProdutoPersonalizado = "NULL";
        $idCliente = "NULL";
        $session = $_SESSION["seo_session"];
        $custo = "";
        $quantidade = "";
        $nome_produto = "";
        $idSeo = "";

        if (isset($_POST['id_produto']) && $_POST['id_produto'] != "")
            $idProduto = $sistema->DB_anti_injection($_POST['id_produto']);

        if (isset($_POST['id_personalizado']) && $_POST['id_personalizado'] != "")
            $idProdutoPersonalizado = $sistema->DB_anti_injection($_POST['id_personalizado']);

        if (isset($_POST['id_cliente']) && $_POST['id_cliente'] != "")
            $idCliente = $sistema->DB_anti_injection($_POST['id_cliente']);

        if (isset($_POST['nome_produto']) && $_POST['nome_produto'] != "")
            $nome_produto = $sistema->DB_anti_injection($_POST['nome_produto']);

        if (isset($_POST['id_seo']) && $_POST['id_seo'] != "")
            $idSeo = $sistema->DB_anti_injection($_POST['id_seo']);

        if (isset($_POST['custo']) && $_POST['custo'] != "")
            $custo = $sistema->DB_anti_injection($_POST['custo']);

        $confere_custo = $sistema->DB_fetch_array("SELECT A.custo FROM tb_produtos_produtos A WHERE A.id_seo = $idSeo AND A.id = $idProduto");
        if (!$confere_custo->num_rows)
            $confere_custo = $sistema->DB_fetch_array("SELECT B.custo FROM tb_produtos_personalizados A INNER JOIN tb_produtos_produtos B ON B.id = A.id_produto WHERE A.id_seo = $idSeo AND A.id_produto = $idProduto");
        if ($confere_custo->rows[0]['custo'] != $custo) {
            $resposta->type = "error";
            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
            echo json_encode($resposta);
            exit();
        }

        if (isset($_POST['quantidade']) && $_POST['quantidade'] != "")
            $quantidade = $sistema->DB_anti_injection($_POST['quantidade']);

        $query = $sistema->DB_insert("tb_carrinho_produtos_historico", "id_seo,id_personalizado,id_produto,id_cliente,session,custo,nome_produto,quantidade", "
                    $idSeo,
                    $idProdutoPersonalizado,
                    $idProduto,
                    $idCliente,
                    '$session',
                    $custo,
                    '$nome_produto',
                    $quantidade
                ");

        if ($query->query) {
            foreach ($conjuntos->rows as $conjunto) {
                $atributo = getAtributo($_POST[$conjunto['id']]);
                $arquivo = "";
                $valor = "";
                $cor = "";
                $texto = "";
                if (isset($_POST["arquivo-" . $conjunto['id']]) && $_POST["arquivo-" . $conjunto['id']] != "") {
                    $valor = $_POST["arquivo-" . $conjunto['id']];
                    $upload = $sistema->uploadFile("arquivo-{$conjunto['id']}", array("jpg", "jpeg", "gif", "png"), '');
                    if ($upload->return) {
                        $arquivo = $upload->file_uploaded;
                    }
                }
                if (isset($_POST["cor-" . $conjunto['id']]) && $_POST["cor-" . $conjunto['id']] != "") {
                    $cor = $_POST["cor-" . $conjunto['id']];
                }
                if (isset($_POST["texto-" . $conjunto['id']]) && $_POST["texto-" . $conjunto['id']] != "") {
                    $texto = $_POST["texto-" . $conjunto['id']];
                }
                $sistema->DB_insert("tb_carrinho_atributos_historico", "id_carrinho_produto_historico,id_conjunto_atributo,id_atributo,selecionado,nome_atributo,custo,arquivo,valor,cor, texto,nome_conjunto", "   
                            $query->insert_id,{$conjunto['id']},
                            {$_POST[$conjunto['id']]},
                            1,
                            '{$atributo['nome']}',
                            '{$atributo['custo']}',
                            '$arquivo',
                            '$valor',
                            '$cor',
                            '$texto',
                            '{$conjunto['nome']}'
                        ");
            }

            $resposta->time = 2000;
            $resposta->type = "success";
            $resposta->message = "Produto adicionado com sucesso!";
            $sistema->inserirRelatorio("Colocou produto no carrinho");
        } else {
            $resposta->type = "error";
            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
        }
    }
    echo json_encode($resposta);
}
