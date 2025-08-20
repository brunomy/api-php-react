<?php

namespace classes;

if (!isset($_SESSION["seo_session"])) {
    $_SESSION["seo_session"] = uniqid();
}

use System\Core\Bootstrap;
use classes\Frete;

$p = new Product();

if ($p->getParameter('product')) {
    $p->setAction();
}

class Product extends Bootstrap {

    private $descontos = array();

    function __construct() {

        parent::__construct();

        $this->upload_folder = "uploads/";
        $this->upload_path = "uploads/";

        $this->module = "";
    }

    //pega dados do produto por seu id
    public function getProdutoById($idProduto = null) {
        if ($idProduto != null) {
            $query = $this->DB_fetch_array("SELECT * FROM tb_produtos_produtos A WHERE A.id = $idProduto");
            return $query;
        }
    }

    //pega dados do produto adicionado no carrinho por seu id
    public function getCarrinhoProdutoHistoricoById($idCarrinhoProduto = null) {
        if ($idCarrinhoProduto != null) {
            $query = $this->DB_fetch_array("SELECT * FROM tb_carrinho_produtos_historico A WHERE A.id = $idCarrinhoProduto");
            return $query;
        }
    }

    //pega todos dados dos produtos do carrinho pela sessão do usuário
    public function getCarrinhoProdutosHistoricoBySession($session = null) {
        if ($session != null) {
            $query = $this->DB_fetch_array("SELECT * FROM tb_carrinho_produtos_historico A WHERE A.session = '$session' AND A.id_pedido IS NULL");
            return $query;
        }
    }

    //pega todos atributos do produto adicionado no carrinho pelo id do produto
    public function getAtributosByIdCarrinhoProdutoHistorico($idCarrinhoProdutoHistorico = null) {
        if ($idCarrinhoProdutoHistorico != null) {
            $query = $this->DB_fetch_array("SELECT * FROM tb_carrinho_atributos_historico A WHERE A.id_carrinho_produto_historico = $idCarrinhoProdutoHistorico");
            if ($query->num_rows)
                return $query;
        }
    }

    //pega todos descontos pelo id do produto
    function getDescontosByProduto($idProduto = null) {
        if ($idProduto != null) {
            $query = $this->DB_fetch_array("SELECT * FROM tb_produtos_has_tb_descontos A INNER JOIN tb_produtos_descontos B ON B.id = A.id_desconto WHERE A.id_produto = $idProduto ORDER BY B.quantidade");
            return $query;
        }
    }

    //pega informações valor total e quantidade total dos itens do carrinho
    public function getCarrinhoInfoTotal() {
        $valor = 0;
        $quantidade = 0;
        $produtos = $this->DB_fetch_array("SELECT A.id, A.custo, A.quantidade FROM tb_carrinho_produtos_historico A INNER JOIN tb_produtos_produtos B ON B.id = A.id_produto INNER JOIN tb_seo_paginas C ON C.id = A.id_seo WHERE A.session = '{$_SESSION["seo_session"]}' AND A.id_pedido IS NULL ORDER BY A.id DESC");
        if ($produtos->num_rows) {
            foreach ($produtos->rows as $produto) {
                $valor = $valor + ($produto['custo'] * $produto['quantidade']);
                $quantidade = $quantidade + $produto['quantidade'];
            }
            $atributos = $this->DB_fetch_array("SELECT custo FROM tb_carrinho_atributos_historico WHERE id_carrinho_produto_historico = {$produto['id']}");
            if ($atributos->num_rows) {
                foreach ($atributos->rows as $atributo) {
                    $valor = $valor + ($atributo['custo'] * $quantidade);
                }
            }
        }
        $resposta['valor'] = $valor;
        $resposta['quantidade'] = $quantidade;
        return $resposta;
    }

    //pega valor do produto que está no carrinho pelo seu id
    public function getCarrinhoInfoByProduto($idCarrinhoProduto = null) {
        $valor = 0;
        $produtos = $this->DB_fetch_array("SELECT A.id, A.custo, A.quantidade FROM tb_carrinho_produtos_historico A INNER JOIN tb_produtos_produtos B ON B.id = A.id_produto INNER JOIN tb_seo_paginas C ON C.id = A.id_seo WHERE A.session = '{$_SESSION["seo_session"]}' AND A.id = $idCarrinhoProduto AND A.id_pedido IS NULL ");
        if ($produtos->num_rows) {
            foreach ($produtos->rows as $produto) {
                $valor = $valor + $produto['custo'];
            }
            $atributos = $this->DB_fetch_array("SELECT custo FROM tb_carrinho_atributos_historico WHERE id_carrinho_produto_historico = {$produto['id']}");
            if ($atributos->num_rows) {
                foreach ($atributos->rows as $atributo) {
                    $valor = $valor + $atributo['custo'];
                }
            }
        }
        $resposta['valor'] = $valor;
        return $resposta;
    }

    //pega soma de valores dos atributos do carrinho pelo id produto
    public function getCarrinhoAtributosInfoByProduto($idProduto = null) {
        $valor = 0;
        $atributos = $this->DB_fetch_array("SELECT * FROM tb_carrinho_atributos_historico A WHERE A.id_carrinho_produto_historico = $idProduto");
        if ($atributos->num_rows) {
            foreach ($atributos->rows as $atributo) {
                $valor = $valor + $atributo['custo'];
            }
        }
        $resposta['valor'] = $valor;
        return $resposta;
    }

    //pega dados dos atributos pelo id produto
    public function getAtributosInfoByProduto($idProduto) {
        $query = $this->DB_fetch_array("SELECT A.*, CONCAT(D.seo_url_breadcrumbs,D.seo_url) seo_url, IF(E.id IS NULL, 10000, E.ordem) ordem FROM tb_carrinho_atributos_historico A INNER JOIN tb_carrinho_produtos_historico B ON B.id = A.id_carrinho_produto_historico INNER JOIN tb_produtos_produtos C ON C.id = B.id_produto INNER JOIN tb_seo_paginas D ON D.id = C.id_seo LEFT JOIN tb_produtos_conjuntos_atributos E ON A.id_conjunto_atributo = E.id WHERE A.id_carrinho_produto_historico = $idProduto ORDER BY ordem");
        if ($query->num_rows)
            return $query;
    }

    //pega deduções dos produtos para cáculo valor de fábrica
    public function getDeducoesByProduto($idPedido = null, $id = null) {
        if ($idPedido != null && $id != null) {
            $query = $this->DB_fetch_array("SELECT * FROM tb_pedidos_deducoes WHERE id_pedido = $idPedido AND id_produto_carrinho = $id ORDER BY descricao");
            return $query;
        }
    }

    public function cadastrarTransacaoCielo($form) {

        $rs = new \stdClass();

        $this->DB_connect();


        foreach ($form as $key => $value) {
            $fields[] = $key;
            $values[] = "'" . mysqli_real_escape_string($this->mysqli, $value) . "'";
        }

        //inseri a curso no banco de dados
        $result = $this->DB_insert("tb_pedidos_transacoes_cielo", implode(',', $fields), implode(',', $values));

        $rs->result = $result;
        $rs->id = $result->insert_id;

        $this->DB_disconnect();

        return $rs;
    }

    public function cadastrarStatusTransacaoCielo($form) {
        $rs = new \stdClass();

        $form = $this->formularioObjeto($form, 'tb_pedidos_transacoes_cielo');

        foreach ($form as $key => $value) {
            $fields[] = $key;
            $values[] = "'" . $value . "'";
        }

        $result = $this->DB_insert("tb_pedidos_transacoes_cielo", implode(',', $fields), implode(',', $values));

        $rs->result = $result;
        $rs->id = $result->insert_id;

        //retorna o resultado da query para a câmada de controle
        return $rs;
    }

    public function encodeObj(&$obj) {
        foreach ($obj as &$attr) {
            if (is_object($attr)) {
                $this->encodeObj($attr);
            } elseif (is_array($attr)) {
                $this->encodeArray($attr);
            } else {
                $attr = utf8_encode($attr);
            }
        }
        return $obj;
    }

    public function encodeArray(&$array) {

        foreach ($array as &$elem) {
            if (is_array($elem)) {
                $this->encodeArray($elem);
            } elseif (is_object($elem)) {
                $this->encodeObj($elem);
            } else {
                $elem = utf8_encode($elem);
            }
        }
        return $array;
    }

    public function encode(&$var) {
        if (is_array($var)) {
            $this->encodeArray($var);
        } else if (is_object($var)) {
            $this->encodeObj($var);
        } else {
            $var = utf8_encode($var);
        }
        return $var;
    }

    public function decodeObj(&$obj) {
        foreach ($obj as &$attr) {
            if (is_object($attr)) {
                $this->decodeObj($attr);
            } elseif (is_array($attr)) {
                $this->decodeArray($attr);
            } else {
                $attr = utf8_decode($attr);
            }
        }
        return $obj;
    }

    public function decodeArray(&$array) {

        foreach ($array as &$elem) {
            if (is_array($elem)) {
                $this->decodeArray($elem);
            } elseif (is_object($elem)) {
                $this->decodeObj($elem);
            } else {
                $elem = utf8_decode($elem);
            }
        }
        return $array;
    }

    public function decode(&$var) {
        if (is_array($var)) {
            $this->decodeArray($var);
        } else if (is_object($var)) {
            $this->decodeObj($var);
        } else {
            $var = utf8_decode($var);
        }
        return $var;
    }

    function alterarStatusPagamento($form) {

        foreach ($form as $key => $value) {
            $fields_values[] = "$key='$value'";
        }


        $result = $this->DB_update('tb_pedidos_pedidos', implode(',', $fields_values) . " WHERE id=" . $form->id);

        $rs = new \stdClass();
        $rs->result = $result;

        //retorna o resultado da query para a câmada de controle
        return $rs;
    }

    public function zerarStatusTransacaoCielo($form) {

        //altera o curso no banco de dados
        $result = $this->DB_update("tb_pedidos_transacoes_cielo_status", "atual = 0 WHERE checkout_cielo_order_number='{$form['checkout_cielo_order_number']}'");

        //retorna o resultado da query para a câmada de controle
        return $result;
    }

    public function usuarioById($id = 0) {
        $query = $this->DB_fetch_object("SELECT * FROM tb_clientes_clientes WHERE id = $id");
        return $query->rows[0];
    }

    public function pedidoByOrderNumberCielo($orderNumberCielo, $amount) {

        $this->DB_connect();

        $result = $this->mysqli->query("
            
        SELECT pe.* FROM tb_pedidos_pedidos pe WHERE 1

        AND

        LPAD(LPAD(pe.id, 10, '0'), 32, MD5(pe.id+pe.id_cliente))='$orderNumberCielo'

        AND

        valor_final = '$amount'

        ");

        $this->DB_disconnect();


        //monta o objeto com os valores do banco de dados
        $dados = $result->fetch_object();

        $curso = new \stdClass();

        if (!empty($dados)) {
            foreach ($dados as $campo => $valor) {
                $curso->{$campo} = $valor;
            }
        }

        $this->DB_disconnect();

        return $dados ? $curso : null;
    }

    public function str2num($str) {
        if (strpos($str, '.') < strpos($str, ',')) {
            $str = str_replace('.', '', $str);
            $str = strtr($str, ',', '.');
        } else {
            $str = str_replace(',', '', $str);
        }
        return (float) $str;
    }

    public function confirmadoPagseguro($form) {
        //altera o conselho no banco de dados
        $result = $this->DB_update("tb_pedidos_pedidos", "id_status='$form->id_status',metodo_pagamento_id='$form->codigo_pagseguro',tipo_pagamento='$form->tipo_pagamento',status_pagamento='$form->status_pagamento' WHERE id = $form->id_pedido");

        //retorna o resultado da query para a câmada de controle
        return $result;
    }

    public function atualizarPedidoVoltaPagseguro($form) {

        //altera o conselho no banco de dados
        $result = $this->DB_update("tb_pedidos_pedidos", "id_status='$form->id_status',metodo_pagamento_id='$form->codigo_pagseguro',tipo_pagamento='$form->tipo_pagamento' WHERE id = $form->id_pedido");

        //retorna o resultado da query para a câmada de controle
        return $result;
    }

    public function getAtributo($idAtributo) {
        $query = $this->DB_fetch_array("SELECT * FROM tb_produtos_atributos A WHERE A.id = $idAtributo");
        return $query->rows[0];
    }

    public function getPedidosByCliente($id = null) {


        if ($id != null) {
            return $this->DB_fetch_array("SELECT 
                                                        A.*,
                                                        DATE_FORMAT(A.data, '%d/%m/%Y') registro,
                                                        DATE_FORMAT(A.prazo_entrega, '%d/%m/%Y') prazo_entrega,
                                                        IFNULL(B.label, B.nome) status,
                                                        B.mostrar_botao_pagar
                                                    FROM
                                                        tb_pedidos_pedidos A
                                                            LEFT JOIN
                                                        tb_pedidos_status B ON B.id = A.id_status
                                                    WHERE
                                                        A.id_cliente = $id 
                                                            AND  (A.orc_status = 'ganho'
                                                            or  A.orc_status is null)
                                                    ORDER BY A.data DESC ");
        }
    }

    private function getValorProdutoCarrinhoById($id) {
        $query = $this->DB_fetch_array("SELECT valor_produto FROM tb_carrinho_produtos_historico WHERE id = $id");
        if ($query->num_rows)
            return $query->rows[0]['valor_produto'];
        else
            return 0;
    }

    private function getQtdByProduto($id) {
        $qtd = 0;
        $query = $this->DB_fetch_array("SELECT SUM(A.quantidade) quantidade FROM tb_carrinho_produtos_historico A WHERE A.session = '{$_SESSION['seo_session']}' AND A.id_pedido IS NULL AND A.id_produto = $id");
        if ($query->num_rows) {
            $qtd = $query->rows[0]['quantidade'];
        }
        return $qtd;
    }

    public function calcTotalCarrinhoBySession() {
        $info['valor'] = 0;
        $info['desconto'] = 0;
        $produtos = $this->getCarrinhoProdutosHistoricoBySession($_SESSION['seo_session']);


        if ($produtos->num_rows) {
            foreach ($produtos->rows as $produto) {
                if($produto['valor_produto']!="" && $produto['quantidade'])
                    $info['valor'] = $info['valor'] + ($produto['valor_produto'] * $produto['quantidade']);

                if($produto['desconto']!="" && $produto['quantidade'])
                    $info['desconto'] = $info['desconto'] + ($produto['desconto'] * $produto['quantidade']);
            }
        }

        return $info;
    }

    public function getCartProductsByPedido($idPedido = null, $produtos = null) {
        if ($idPedido != "") {

            $where = "";
            if ($produtos != "") {
                $produtos = explode(",", $produtos);
                $where .= " AND (";
                $separador = "";
                foreach ($produtos as $produto) {
                    $produto = (int) $produto;
                    $where .= $separador . " A.id = $produto";
                    $separador = " OR ";
                }
                $where .= " ) ";
            }

            $query = $this->DB_fetch_array("SELECT 
                A.*, B.ncm, B.sku, B.frete_embutido, B.porcentagem_fabrica, B.porcentagem_ipi, B.resumo, B.qtd_minima, F.arquivo anexo, C.id_categoria,
                cat.nome as nome_categoria, B.unidade_calculada as agrupavel, B.prazo_producao, B.prazo_producao_adic
                FROM tb_carrinho_produtos_historico A 
                INNER JOIN tb_produtos_produtos B ON B.id = A.id_produto 
                LEFT JOIN tb_carrinho_produtos_anexos F ON F.id_produto_historico = A.id 
                LEFT JOIN tb_produtos_produtos_has_tb_produtos_categorias C ON C.id_produto = A.id_produto
                LEFT JOIN tb_produtos_categorias cat ON cat.id = C.id_categoria
                WHERE A.id_pedido = $idPedido $where");


            if ($query->num_rows){
                return $query;
            }

        }
    }

    public function getRastreiosByPedido($idPedido) {
        $query = $this->DB_fetch_array("SELECT * FROM tb_pedidos_rastreios A INNER JOIN tb_pedidos_has_tb_rastreios B ON B.id_rastreio = A.id WHERE B.id_pedido = $idPedido ORDER BY A.data DESC");
        return $query;
    }

    public function getEnderecoByPedido($idPedido) {
        $query = $this->DB_fetch_array("SELECT A.*, B.*, C.estado, C.uf FROM tb_pedidos_enderecos A INNER JOIN tb_utils_cidades B ON B.id = A.id_cidade INNER JOIN tb_utils_estados C ON C.id = B.id_estado WHERE A.id_pedido = $idPedido");
        return $query;
    }

    public function numeros($strCampo) {
        $strCampo = str_replace(".", "", $strCampo);
        $strCampo = str_replace("-", "", $strCampo);
        $strCampo = str_replace("/", "", $strCampo);
        $strCampo = str_replace("(", "", $strCampo);
        $strCampo = str_replace(")", "", $strCampo);
        $strCampo = str_replace("[", "", $strCampo);
        $strCampo = str_replace("]", "", $strCampo);
        $strCampo = str_replace("{", "", $strCampo);
        $strCampo = str_replace("}", "", $strCampo);
        $strCampo = str_replace(" ", "", $strCampo);
        return $strCampo;
    }

    ####
    #######
    ######          ACTIONS SCRIPT
    #######
    ####
    //não encontrou o método carrega a página de erro

    private function errorAction() {
        require_once '404.php';
    }

    private function setQtdAction() {
        if(is_numeric($_POST['id']) && is_numeric($_POST['qtd']) ){
            $id = $this->DB_anti_injection($_POST['id']);
            $qtd = $this->DB_anti_injection($_POST['qtd']);
            if ($qtd < 1)
                $qtd = 1;

            //verificar quantidade mínima
            $verificar = $this->DB_fetch_array("SELECT id_produto FROM tb_carrinho_produtos_historico WHERE id = $id");
            if ($verificar->num_rows) {
                $pesquisa = $this->DB_fetch_array("SELECT * FROM tb_produtos_produtos WHERE id = {$verificar->rows[0]['id_produto']}");
                if ($pesquisa->num_rows) {
                    if ($qtd < $pesquisa->rows[0]['qtd_minima'])
                        $qtd = $pesquisa->rows[0]['qtd_minima'];
                }
            }

            $this->DB_update("tb_carrinho_produtos_historico", "quantidade = $qtd WHERE id = $id");

            $this->calcAction();
        }else{
            http_response_code(500);
            echo "error";
        }
    }

    private function setCarrinhoAction() {
        $post = array();

        $postM = $_POST;

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

            $conjuntos = $this->DB_fetch_array("SELECT A.* FROM tb_produtos_conjuntos_atributos A INNER JOIN tb_produtos_atributos B ON B.id_conjunto_atributo = A.id WHERE A.id_produto = {$_POST['id_produto']} GROUP BY A.id ORDER BY A.ordem");

            $resposta = new \stdClass();
            if (isset($_POST['produto']) && $_POST['produto'] != "") {
                //atualizar
                $quantidade = "";

                if (isset($_POST['quantidade']) && $_POST['quantidade'] != "")
                    $quantidade = $this->DB_anti_injection($_POST['quantidade']);

                //verificar quantidade mínima
                $pesquisa = $this->DB_fetch_array("SELECT * FROM tb_produtos_produtos WHERE id = {$_POST['id_produto']}");
                if ($pesquisa->num_rows) {
                    if ($quantidade < $pesquisa->rows[0]['qtd_minima'])
                        $quantidade = $pesquisa->rows[0]['qtd_minima'];
                }

                $query = $this->DB_update("tb_carrinho_produtos_historico", "quantidade = $quantidade WHERE id = {$_POST['produto']} AND session = '{$_SESSION["seo_session"]}'");

                if ($query) {

                    $valor_produto = $this->DB_anti_injection($_POST['custo']);

                    $this->DB_delete("tb_carrinho_atributos_historico", "id_carrinho_produto_historico = {$_POST['produto']}");
                    if ($conjuntos->num_rows) {
                        foreach ($conjuntos->rows as $conjunto) {
                            $atributo = $this->getAtributo($_POST[$conjunto['id']]);
                            $arquivo = "";
                            $valor = "";
                            $cor = "";
                            $texto = "";
                            if (isset($_POST["arquivo-" . $conjunto['id']]) && $_POST["arquivo-" . $conjunto['id']] != "") {
                                $valor = $_POST["arquivo-" . $conjunto['id']];
                                $upload = $this->uploadFile("arquivo-{$conjunto['id']}", array("jpg", "jpeg", "gif", "png", "cdr", "pdf", "ai", "psd", "eps"), '');
                                if ($upload->return) {
                                    $arquivo = $upload->file_uploaded;
                                }
                                @unlink($this->upload_folder . $_POST["arquivo-novo-" . $conjunto['id']]);
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


                            $custoAtr = (float)$atributo['custo'];
                            $valor_produto = $valor_produto + $custoAtr;

                            $this->DB_insert("tb_carrinho_atributos_historico", "id_carrinho_produto_historico,id_conjunto_atributo,id_atributo,selecionado,nome_atributo,custo,arquivo,valor,cor, texto,nome_conjunto", "   
                            {$_POST['produto']},
                            {$conjunto['id']},
                            {$_POST[$conjunto['id']]},
                            1,
                            '{$atributo['nome']}',
                            '{$custoAtr}',
                            '$arquivo',
                            '$valor',
                            '$cor',
                            '$texto',
                            '{$conjunto['nome']}'
                        ");
                        }
                    }

                    $this->DB_update("tb_carrinho_produtos_historico", "valor_produto = $valor_produto WHERE id = {$_POST['produto']}");

                    $resposta->time = 2000;
                    $resposta->response = $_POST['retorno'];
                    $resposta->type = "success";
                    $resposta->message = "Produto alterado com sucesso!";
                    $this->inserirRelatorio("Alterou produto no carrinho");
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            } else {
                //adicionar
                $idProduto = "NULL";
                $idProdutoPersonalizado = "NULL";
                $session = $_SESSION["seo_session"];
                $custo = "";
                $valor_produto = "";
                $quantidade = "";
                $nome_produto = "";
                $idSeo = "";

                if (isset($_POST['id_produto']) && $_POST['id_produto'] != "")
                    $idProduto = $this->DB_anti_injection($_POST['id_produto']);

                if (isset($_POST['id_personalizado']) && $_POST['id_personalizado'] != "")
                    $idProdutoPersonalizado = $this->DB_anti_injection($_POST['id_personalizado']);

                if (isset($_POST['nome_produto']) && $_POST['nome_produto'] != "")
                    $nome_produto = $this->DB_anti_injection($_POST['nome_produto']);

                if (isset($_POST['id_seo']) && $_POST['id_seo'] != "")
                    $idSeo = $this->DB_anti_injection($_POST['id_seo']);

                if (isset($_POST['custo']) && $_POST['custo'] != "")
                    $custo = $this->DB_anti_injection($_POST['custo']);


                $confere_custo = $this->DB_fetch_array("SELECT A.custo, A.frete_embutido, A.peso FROM tb_produtos_produtos A WHERE A.id_seo = $idSeo AND A.id = $idProduto");
                if (!$confere_custo->num_rows)
                    $confere_custo = $this->DB_fetch_array("SELECT B.custo, B.frete_embutido, B.peso FROM tb_produtos_personalizados A INNER JOIN tb_produtos_produtos B ON B.id = A.id_produto WHERE A.id_seo = $idSeo AND A.id_produto = $idProduto");
                if (number_format(($confere_custo->rows[0]['custo'] + $confere_custo->rows[0]['frete_embutido']),2,",","") != number_format($custo,2,",","")) {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                    echo json_encode($resposta);
                    exit();
                }

                $peso = $confere_custo->rows[0]['peso'];
                if ($peso == '')
                    $peso = "NULL";

                if (isset($_POST['quantidade']) && $_POST['quantidade'] != "")
                    $quantidade = $this->DB_anti_injection($_POST['quantidade']);

                //verificar quantidade mínima
                $pesquisa = $this->DB_fetch_array("SELECT * FROM tb_produtos_produtos WHERE id = $idProduto");
                if ($pesquisa->num_rows) {
                    if ($quantidade < $pesquisa->rows[0]['qtd_minima'])
                        $quantidade = $pesquisa->rows[0]['qtd_minima'];
                }

                //WSOP
                if (isset($postM['mesa']) AND $postM['mesa'] != '') {

                    if ($postM['nome'] == "") {
                        $resposta->type = "validation";
                        $resposta->message = "Preencha este campo";
                        $resposta->field = "nome";
                        $resposta->return = false;
                        echo json_encode($resposta);
                        exit;
                    } else if ($postM['email'] == "") {
                        $resposta->type = "validation";
                        $resposta->message = "Preencha este campo";
                        $resposta->field = "email";
                        $resposta->return = false;
                        echo json_encode($resposta);
                        exit;
                    } else if ($this->validaEmail($postM['email']) != 1) {
                        $resposta->type = "validation";
                        $resposta->message = "E-mail inválido";
                        $resposta->field = "email";
                        $resposta->return = false;
                        echo json_encode($resposta);
                        exit;
                    } else if ($postM['telefone'] == "") {
                        $resposta->type = "validation";
                        $resposta->message = "Preencha este campo";
                        $resposta->field = "telefone";
                        $resposta->return = false;
                        echo json_encode($resposta);
                        exit;
                    }


                    require 'mailchimp/Mailchimp.php';

                    define('MAILCHIMP_API_KEY', '24604decb77b9ab290cea6eec4f40d66-us7'); // Sua chave da API
                    define('MAILCHIMP_LIST_ID', '34245fc6d0'); // O ID da sua lista



                    try {

                        //$postM['telefone'] = str_replace(Array('(',')','-',' '), '', $postM['telefone']);

                        $merge = array(
                            'MERGE1' => $postM['nome'],
                            'MERGE3' => $postM['telefone']
                        );
                        $mailchimp = new \Mailchimp(MAILCHIMP_API_KEY);
                        $lists = new \Mailchimp_Lists($mailchimp);
                        $email = array(
                            'email' => $postM['email']
                        );
                        $lists->subscribe(
                                MAILCHIMP_LIST_ID, // List ID
                                $email, // Subscriber ID, his/her email
                                $merge, // Custom fields
                                'html', // E-mail type
                                false, // Confirm subscription by email (double opt-in)?
                                true
                        );
                    } catch (\Mailchimp_List_AlreadySubscribed $e) {
                        $resposta->type = "success";
                        $resposta->message = "Já está registrado em nossa base.";
                    } catch (\Mailchimp_Email_AlreadySubscribed $e) {
                        $resposta->type = "success";
                        $resposta->message = "Este e-mail já está registrado em nossa base.";
                    } catch (\Mailchimp_Email_NotExists $e) {
                        $resposta->type = "error";
                        $resposta->message = "Este e-mail não existe.";
                        echo json_encode($resposta);
                        exit;
                    } catch (\Mailchimp_Invalid_Email $e) {
                        $resposta->type = "error";
                        $resposta->message = "Este e-mail é inválido.";
                        echo json_encode($resposta);
                        exit;
                    } catch (\Mailchimp_List_InvalidImport $e) {
                        $resposta->type = "error";
                        $resposta->message = "Este e-mail provavelmente é um e-mail inválido.";
                        echo json_encode($resposta);
                        exit;
                    } catch (\Exception $e) {
                        $resposta->type = "error";
                        $resposta->message = $e->getMessage(); // Do not show it to the user echo json_encode($resposta);
                        exit;
                    }


                    $query = $this->DB_insert("tb_carrinho_produtos_historico", "id_seo,id_produto,session,custo,nome_produto,quantidade,peso,mesa", "
                    '{$postM['id_seo']}',
                    '{$postM['id_produto']}',
                    '$session',
                    '$custo',
                    '{$postM['nome_produto']}',
                    '{$postM['quantidade']}',
                    '{$postM['peso']}',
                    '{$postM['mesa']}'
                ");
                } else {
                    $query = $this->DB_insert("tb_carrinho_produtos_historico", "id_seo,id_personalizado,id_produto,session,custo,nome_produto,quantidade,peso", "
                    $idSeo,
                    $idProdutoPersonalizado,
                    $idProduto,
                    '$session',
                    '$custo',
                    '$nome_produto',
                    $quantidade,
                    $peso
                ");
                }




                $valor_produto = $custo;

                if ($query->query) {

                    if ($conjuntos->num_rows) {
                        foreach ($conjuntos->rows as $conjunto) {
                            $atributo = $this->getAtributo($_POST[$conjunto['id']]);
                            $arquivo = "";
                            $valor = "";
                            $cor = "";
                            $texto = "";
                            if (isset($_POST["arquivo-" . $conjunto['id']]) && $_POST["arquivo-" . $conjunto['id']] != "") {
                                $valor = $_POST["arquivo-" . $conjunto['id']];
                                $upload = $this->uploadFile("arquivo-{$conjunto['id']}", array("jpg", "jpeg", "gif", "png", "cdr", "pdf", "ai", "psd", "eps"), '');
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
                            $custoAtr = (float)$atributo['custo'];
                            $this->DB_insert("tb_carrinho_atributos_historico", "id_carrinho_produto_historico,id_conjunto_atributo,id_atributo,selecionado,nome_atributo,custo,arquivo,valor,cor, texto,nome_conjunto", "   
                            $query->insert_id,{$conjunto['id']},
                            {$_POST[$conjunto['id']]},
                            1,
                            '{$atributo['nome']}',
                            '{$custoAtr}',
                            '$arquivo',
                            '$valor',
                            '$cor',
                            '$texto',
                            '{$conjunto['nome']}'
                        ");

                            if($atributo['custo']!="") $valor_produto = $valor_produto + $atributo['custo'];
                        }
                    }

                    $this->DB_update("tb_carrinho_produtos_historico", "valor_produto = $valor_produto WHERE id = $query->insert_id");

                    $resposta->time = 2000;
                    $resposta->response = $_POST['retorno'];
                    $resposta->type = "success";
                    $resposta->message = "Produto adicionado com sucesso!";
                    $this->inserirRelatorio("Colocou produto no carrinho id: [$query->insert_id]");
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            }
            echo json_encode($resposta);

            $frete = new Frete();
            $frete->calcFreteAction();
        }

        $this->calcAction();
    }

    //exclui produto do carrinho
    private function carrinhoExcluirAction() {
        $resposta = new \stdClass();

        $id = "";
        if (isset($_GET['id']))
            $id = $_GET['id'];

        if ($id != "") {

            $query = $this->DB_delete("tb_carrinho_produtos_historico", "id = $id AND session = '{$_SESSION["seo_session"]}' AND id_pedido IS NULL ");

            if ($query) {
                $resposta->return = 1;
            } else {
                $resposta->return = false;
                $resposta->error = "Não foi possível prosseguir, tente novamente mais tarde!";
            }

            $carrinho_geral = $this->getCarrinhoInfoTotal();
            $resposta->carrinho_valor = $carrinho_geral['valor'];
            $resposta->carrinho_quantidade = $carrinho_geral['quantidade'];

            echo json_encode($resposta);
        }

        $this->calcAction();
    }

    public function calcAction() {
        $itens = array();
        $produtos = $this->getCarrinhoProdutosHistoricoBySession($_SESSION['seo_session']);
        /*
          if ($produtos->num_rows) {

          foreach ($produtos->rows as $produto) {

          $descontos = $this->getDescontosByProduto($produto['id_produto']);
          if ($descontos->num_rows) {
          foreach ($descontos->rows as $desconto) {
          $this->descontos[] = Array(
          'id_item' => $produto['id'],
          'id_produto' => $produto['id_produto'],
          'id' => $desconto['id'],
          'porcentagem' => $desconto['porcentagem'],
          'quantidade' => $desconto['quantidade'],
          'valor' => $desconto['valor'],
          'descricao' => $desconto['descricao']
          );
          }
          }
          }
          }

          $qtd = 0;
          foreach ($this->descontos as $des) {
          $qtd = $this->getQtdByProduto($des['id_produto']);
          if ($qtd >= $des['quantidade']) {
          if ($des['porcentagem'])
          $itens[$des['id_item']] = ($this->getValorProdutoCarrinhoById($des['id_item']) * $des['valor']) / 100 . "|" . $des['descricao'];
          else
          $itens[$des['id_item']] = $des['valor'] . "|" . $des['descricao'];
          }
          }

          if (count($itens) > 0) {
          foreach ($itens as $key => $value) {
          $value = explode("|", $value);
          $this->DB_update("tb_carrinho_produtos_historico", "desconto = '" . $value[0] . "', descricao_desconto = '" . $value[1] . "' WHERE id = $key");
          }
          }else{
          if ($produtos->num_rows) {
          foreach ($produtos->rows as $produto) {
          $this->DB_update("tb_carrinho_produtos_historico", "desconto = NULL, descricao_desconto = NULL WHERE id = ".$produto['id']);
          }
          }
          }
         */

        $tempVar = 0;
        $tempProduto = array();
        if ($produtos->num_rows) {
            $i = 0;
            foreach ($produtos->rows as $produto) {
                $this->DB_update("tb_carrinho_produtos_historico", "desconto = NULL, descricao_desconto = NULL, desconto_fabrica = NULL  WHERE id = " . $produto['id']);
                $descontos = $this->getDescontosByProduto($produto['id_produto']);
                $j = 0;
                if ($descontos->num_rows) {
                    foreach ($descontos->rows as $desconto) {
                        $tempVar = 0;
                        $i2 = 0;
                        foreach ($produtos->rows as $produto2) {
                            $descontos2 = $this->getDescontosByProduto($produto2['id_produto']);
                            $j2 = 0;
                            if ($descontos2->num_rows) {
                                foreach ($descontos2->rows as $desconto2) {
                                    if ($desconto['id'] == $desconto2['id']) {
                                        $tempVar = intval($tempVar + $produto2['quantidade']);
                                    }
                                    $j2++;
                                }
                            }
                            $i2++;
                        }
                        $j++;

                        if ($tempVar >= $desconto['quantidade']) {
                            if ($desconto['porcentagem'] == 1) {
                                $desc = (($produto['valor_produto'] * $desconto['valor']) / 100);
                            } else {
                                $desc = $desconto['valor'];
                            }

                            if ($desconto['porcentagem_fabrica'] > 0) {
                                $fabr = $produto['valor_produto'] * ($desconto['porcentagem_fabrica'] / 100);
                            } else {
                                $fabr = "NULL";
                            }

                            $this->DB_update("tb_carrinho_produtos_historico", "desconto = '" . $desc . "', descricao_desconto = '" . $desconto['descricao'] . "', desconto_fabrica = " . $fabr . " WHERE id = " . $produto['id']);
                        }
                    }
                }
                $i++;
            }
        }
    }

    //pega dados do cupom pelo cupom
    public function clearCupomAction() {

        //echo "<pre>";print_r($_SESSION);echo "</pre>";

        $_SESSION['cupom'] = [];
        $_SESSION['cupom']['cupom'] = "";
        $_SESSION['cupom']['valor'] = "";
        $_SESSION['cupom']['tipo'] = "";
        $_SESSION['cupom']['tipo_int'] = "";
        $_SESSION['cupom']['mensagem'] = "";
    }

    //pega dados do cupom pelo cupom
    private function cupomAction() {

        $_SESSION['cupom'] = [];
        $_SESSION['cupom']['cupom'] = "";
        $_SESSION['cupom']['valor'] = "";
        $_SESSION['cupom']['tipo'] = "";
        $_SESSION['cupom']['tipo_int'] = "";
        $_SESSION['cupom']['mensagem'] = "";

        $resposta = new \stdClass();

        $resposta->cupom = new \stdClass();
        $resposta->cupom->cupom = $_SESSION['cupom'];

        if (isset($_POST['cupom']) && $_POST['cupom'] != "") {

            $codigo = $this->DB_anti_injection($_POST['cupom']);

            $query = $this->DB_fetch_array("SELECT * FROM tb_cupons_cupons WHERE codigo = '$codigo' AND stats = 1");
            if ($query->num_rows) {

                $resposta->return = 1;
                $resposta->type = 'success';
                $resposta->time = 4000;
                $resposta->message = $query->rows[0]['mensagem'];
                $resposta->cupom->cupom = $_SESSION['cupom'];
                if ($query->rows[0]['porcentagem'] == 1)
                    $resposta->cupom->tipo = 'porcentagem';
                else
                    $resposta->cupom->tipo = 'fixo';

                $_SESSION['cupom']['cupom'] = $codigo;
                $_SESSION['cupom']['valor'] = $query->rows[0]['valor'];
                $_SESSION['cupom']['tipo'] = $resposta->cupom->tipo;
                $_SESSION['cupom']['tipo_int'] = $query->rows[0]['porcentagem'];
                $_SESSION['cupom']['mensagem'] = $query->rows[0]['mensagem'];
                $resposta->cupom->valor = $query->rows[0]['valor'];
            } else {
                $resposta->return = false;
                $resposta->type = 'attention';
                $resposta->time = 4000;
                $resposta->message = 'Cupom não localizado !!!';
            }
        } else {
            $resposta->return = false;
            $resposta->type = 'attention';
            $resposta->time = 4000;
            $resposta->message = 'Por favor, preencha o cupom !!!';
        }

        echo json_encode($resposta);
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
