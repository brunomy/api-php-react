<?php

namespace classes;

use System\Core\Bootstrap;
use classes\Product;

if (!isset($_SESSION["seo_session"])) {
    $_SESSION["seo_session"] = uniqid();
}

$f = new Frete();

if ($f->getParameter('frete')) {
    $f->setAction();
}

class Frete extends Bootstrap {

    function __construct() {

        parent::__construct();

        $this->upload_folder = "uploads/";
        $this->upload_path = "uploads/";

        $this->module = "";
    }


    private function cepAction() {
        $resposta = new \stdClass();

        if (isset($_REQUEST['cep']) && $_REQUEST['cep'] != "") {
            $cep = $this->DB_anti_injection($_REQUEST['cep']);

            $cep = preg_replace('/\D/', '', $cep);
            $produto = $_REQUEST['produto'] ?? "";
            $quantidade = $_REQUEST['quantidade'] ?? 1;

            $_SESSION['cep'] = $cep;
            $_SESSION['opt-tipo-frete'] = '';
            $this->calcFreteAction($produto,$quantidade);

            echo $_SESSION['opt-fretes'];
        } else {
            echo "Não foi possível identificar o CEP informado.";
        }
    }

    public function calcFreteAction($produto = "", $quantidade = 1) {
        if (isset($_SESSION['cep']) && $_SESSION['cep'] != "") {

            $_SESSION['opt-fretes'] = "";

            $cep = $_SESSION['cep'];

            $cep = preg_replace('/\D/', '', $cep);

            $cotacao_manual = 0;

            $locais = $this->DB_fetch_array("    
                SELECT id_estado, id_cidade FROM
                (
                (SELECT A.id id_estado, NULL id_cidade FROM tb_config_estados A WHERE A.cep_init <= '$cep' AND A.cep_end >= '$cep')
                UNION
                (SELECT A.id_estado, A.id id_cidade FROM tb_config_cidades A WHERE A.cep_init <= '$cep' AND A.cep_end >= '$cep')
                ) TAB  ORDER BY id_cidade DESC
            ");

            if (!$locais->num_rows) {
                echo '<script>alert("Não localizamos o seu CEP, por favor confirme se está correto ou entre em contato conosco nos meios que estão no rodapé dessa página");</script>';
                $this->inserirRelatorio("Erro: não localizou frete para o CEP ".$cep." seo_session=".$_SESSION['seo_session']);
            } else {

                $cidades = array();
                $estados = array();

                $result['negar_aereo'] = false;
                $result['negar_terrestre'] = false;

                $negar_aereo = $this->negarAereo($produto);
                $negar_terrestre = $this->negarTerrestre($produto);

                foreach ($locais->rows as $local) {

                    if (in_array($local['id_estado'] . "-" . $local['id_cidade'], $negar_aereo)) {
                        $result['negar_aereo'] = true;
                    }
                    if (in_array($local['id_estado'] . "-" . $local['id_cidade'], $negar_terrestre)) {
                        $result['negar_terrestre'] = true;
                    }

                    $cidades[] = $local['id_cidade'];
                    $estados[] = $local['id_estado'];
                }

                $nao_negar_aereo = $this->naoNegarAereo($cidades, $estados, $produto);
                if ($nao_negar_aereo)
                    $result['negar_aereo'] = false;

                $nao_negar_terrestre = $this->naoNegarTerrestre($cidades, $estados, $produto);
                if ($nao_negar_terrestre)
                    $result['negar_terrestre'] = false;


                //DEFINE PRAZO PADRÃO--------------

                    $pPadrao = 0;
                    $pBase = 0;
                    if($produto == ""){
                        $prazo_padrao = $this->DB_fetch_array("
                            SELECT B.id, B.prazo_producao FROM tb_carrinho_produtos_historico A 
                            INNER JOIN tb_produtos_produtos B ON B.id = A.id_produto 
                            WHERE A.session = '{$_SESSION["seo_session"]}' AND A.id_pedido IS NULL 
                            ORDER BY B.prazo_producao DESC
                            LIMIT 1
                        ");
                    }else{
                        $prazo_padrao = $this->DB_fetch_array("
                            SELECT B.id, B.prazo_producao FROM tb_produtos_produtos B 
                            WHERE B.id = {$produto} 
                            ORDER BY B.prazo_producao DESC
                            LIMIT 1
                        ");                        
                    }
                    if ($prazo_padrao->num_rows) {
                        $pPadrao = $prazo_padrao->rows[0]['prazo_producao'];
                        $pBase = $prazo_padrao->rows[0]['id'];
                    }


                //DEFINE PRAZO ADICIONAL--------------

                    if($produto == ''){
                        $prazo_adicionais = $this->DB_fetch_array("
                            SELECT B.id, IFNULL(B.prazo_producao_adic, 0) prazo_producao_adic, A.quantidade  FROM tb_carrinho_produtos_historico A 
                            INNER JOIN tb_produtos_produtos B ON B.id = A.id_produto         
                            WHERE A.session = '{$_SESSION["seo_session"]}' AND A.id_pedido IS NULL
                        ");   
                    }else{
                        $prazo_adicionais = $this->DB_fetch_array("
                            SELECT B.id, IFNULL(B.prazo_producao_adic, 0) prazo_producao_adic, '{$quantidade}' quantidade  FROM tb_produtos_produtos B 
                            WHERE B.id = {$produto}
                        ");
                    }

                    $pPadraoNew = $pPadrao;
                    if ($prazo_adicionais->num_rows) {
                        foreach ($prazo_adicionais->rows as $pa) {
                            if ($pBase == $pa['id'])
                                $pa['quantidade'] = $pa['quantidade'] - 1;
                            $pPadraoNew = $pPadraoNew + ($pa['prazo_producao_adic'] * $pa['quantidade']);
                        }
                    }

                    $pPadraoNewCep = $pPadraoNew;


                //DEFINE PRAZO AÉREO--------------

                    $preco_aereo = 0;
                    $prazo_aereo = 0;
                    $produtoAereo = true;
                    if($produto == ''){
                        $aereos = $this->DB_fetch_array("SELECT B.id_frete_aereo, B.frete_embutido, A.quantidade FROM tb_carrinho_produtos_historico A INNER JOIN tb_produtos_produtos B ON B.id = A.id_produto WHERE A.session = '{$_SESSION["seo_session"]}' AND A.id_pedido IS NULL");
                    }else{
                        $aereos = $this->DB_fetch_array("SELECT B.id_frete_aereo, B.frete_embutido, '{$quantidade}' quantidade FROM tb_produtos_produtos B WHERE B.id = {$produto}");
                    }
                    if ($aereos->num_rows) {
                        $a = array();
                        foreach ($aereos->rows as $aereo) {
                            if ($aereo['id_frete_aereo'] != "") {
                                $a[] = $this->getInfoFrete($aereo['id_frete_aereo'], $estados, $cidades, $aereo['frete_embutido'], $aereo['quantidade']);
                                $produtoAereo = $this->entregaAereo($aereo['id_frete_aereo'], $estados, $cidades);
                            } else {
                                $produtoAereo = false;
                            }
                        }
                        foreach ($a as $a) {
                            if($a['cotacao_manual']) $cotacao_manual = 1;
                            if ($a['prazo'] > $prazo_aereo) {
                                $prazo_aereo = $a['prazo'];
                            }

                            $preco_aereo = $preco_aereo + $a['preco'];
                        }
                    }

                    $pPrazoAereo = $pPadraoNewCep + $prazo_aereo;
                    $pPrecoAereo = $preco_aereo;


                //DEFINE PRAZO TERRESTRE--------------

                    $preco_terrestre = 0;
                    $prazo_terrestre = 0;

                    if($produto == ''){
                        $terrestres = $this->DB_fetch_array("SELECT B.id_frete_terrestre, B.frete_embutido, A.quantidade FROM tb_carrinho_produtos_historico A INNER JOIN tb_produtos_produtos B ON B.id = A.id_produto WHERE A.session = '{$_SESSION["seo_session"]}' AND A.id_pedido IS NULL");    
                    }else{
                        $terrestres = $this->DB_fetch_array("SELECT B.id_frete_terrestre, B.frete_embutido, '{$quantidade}' quantidade FROM tb_produtos_produtos B WHERE B.id = {$produto}");
                    }
                    
                    if ($terrestres->num_rows) {
                        $a = array();
                        foreach ($terrestres->rows as $terrestre) {
                            if ($terrestre['id_frete_terrestre'] != "") {
                                $a[] = $this->getInfoFrete($terrestre['id_frete_terrestre'], $estados, $cidades, $terrestre['frete_embutido'], $terrestre['quantidade']);
                            }
                        }

                        foreach ($a as $a) {
                            $preco_terrestre = $preco_terrestre + $a['preco'];
                            if($a['cotacao_manual']) $cotacao_manual = 1;
                            if ($a['prazo'] > $prazo_terrestre) {
                                $prazo_terrestre = $a['prazo'];
                            }
                        }
                    }

                    $pPrazoTerrestre = $pPadraoNewCep + $prazo_terrestre;
                    $pPrecoTerrestre = $preco_terrestre;

                if($pPrazoTerrestre == 0) $this->inserirRelatorio("Erro: Frete zerado! CEP=".$cep." seo_session=".$_SESSION['seo_session']);

                $product = new Product();
                $produtos_no_carrinho = $product->getCarrinhoProdutosHistoricoBySession($_SESSION["seo_session"]);

                if($cotacao_manual){

                    $_SESSION['opt-fretes'] .= "
                    <span style='font-size:12px;'>Identificamos que o valor do frete ficou acima do normal. Para esses casos disponibilizamos abaixo uma opção para que você possa cotar o frete junto com nossos consultores pelo whatsapp.  Dessa forma você pode concluir a compra agora com frete à consultar e após cotação você pagará o frete separadamente com as mesmas condições da sua compra.</span>
                    <div class='option-frete'>
                        <label for='frete-terrestre'>
                            <input data-prazo='0' data-nome='A Consultar' type='radio' name='frete' id='frete-consultar' value='0'> 
                            <span><img src='img/icon_frete_terrestre.png'> Á Consultar</span>
                        </label>
                        <div>Frete à consultar, pagamento realizado separadamente</div>
                    </div>";

                } else {

                    if ($produtos_no_carrinho->num_rows || $produto != '') {

                        if (!$result['negar_terrestre']) {
                            if ($pPrecoTerrestre > 0)
                                $valorDescrito = "R$ " . $this->formataMoedaShow($pPrecoTerrestre);
                            else
                                $valorDescrito = 'Frete Grátis';

                            $_SESSION['opt-fretes'] .= "<div class='option-frete'><label for='frete-terrestre'><input data-prazo='$pPrazoTerrestre' data-nome='Terrestre' type='radio' name='frete' id='frete-terrestre' value='$pPrecoTerrestre'> <span><img src='img/icon_frete_terrestre.png'> Frete Terrestre</span></label> <div class='valor'>Valor do frete: <span>{$valorDescrito}</span></div> <div class='prazo'>Prazo para entregar: <span>$pPrazoTerrestre dias úteis</span></div></div>";
                        } else {
                            if ($produtos_no_carrinho->num_rows)
                                $_SESSION['opt-fretes'] .= "<div class='option-frete'><label><span><img src='img/icon_frete_terrestre.png'> Frete Terrestre </span><div>Não entregamos no CEP informado!</div></label></div>";
                        }

                        if (!$result['negar_aereo'] && $produtoAereo) {
                            if ($pPrecoAereo > 0)
                                $valorDescrito = "R$ " . $this->formataMoedaShow($pPrecoAereo);
                            else
                                $valorDescrito = 'Frete Grátis';

                            $_SESSION['opt-fretes'] .= "<div class='option-frete'><label for='frete-aereo'><input data-prazo='$pPrazoAereo' data-nome='Aéreo' type='radio' name='frete' id='frete-aereo' value='$pPrecoAereo'> <span><img src='img/icon_frete_aereo.png'> Frete Aéreo</span></label> <div class='valor'>Valor do frete: <span>$valorDescrito</span></div> <div class='prazo'>Prazo para entregar: <span>$pPrazoAereo dias úteis</span></div></div>";
                        } else {
                            $_SESSION['opt-fretes'] .= "<div class='option-frete'><label><span><img src='img/icon_frete_aereo.png'> Frete Aéreo </span><div>Não entregamos no CEP informado!</div></label></div>";
                        }
                    }
                }
                $this->updateOptFretes();
            }
        }
    }
    
    private function setTipoFreteAction() {
        $_SESSION['opt-tipo-frete'] = "";
        if (isset($_POST['tipo']) && $_POST['tipo'] != "") {
            $_SESSION['opt-tipo-frete'] = $_POST['tipo'];
            $this->updateOptFretes();
        }
    }

    private function negarAereo($produto) {

        if($produto==''){
            $negar = $this->DB_fetch_array("        
                SELECT C.*  FROM tb_carrinho_produtos_historico A 
                INNER JOIN tb_produtos_produtos B ON B.id = A.id_produto 
                INNER JOIN tb_config_conjuntos_fretes_negar C ON C.id_conjunto = B.id_frete_aereo 
                WHERE A.session = '{$_SESSION["seo_session"]}' AND A.id_pedido IS NULL
            ");
        }else{
            $negar = $this->DB_fetch_array("        
                SELECT C.*  FROM tb_produtos_produtos B  
                INNER JOIN tb_config_conjuntos_fretes_negar C ON C.id_conjunto = B.id_frete_aereo 
                WHERE B.id = {$produto}
            ");
        }

        $resposta['negar'] = array();

        if ($negar->num_rows) {
            foreach ($negar->rows as $nega) {
                $resposta['negar'][] = $nega['id_estado'] . "-" . $nega['id_cidade'];
            }
        }

        return $resposta['negar'];
    }

    private function negarTerrestre($produto) {

        if($produto==''){
            $negar = $this->DB_fetch_array("        
                SELECT C.*  FROM tb_carrinho_produtos_historico A 
                INNER JOIN tb_produtos_produtos B ON B.id = A.id_produto 
                INNER JOIN tb_config_conjuntos_fretes_negar C ON C.id_conjunto = B.id_frete_terrestre 
                WHERE A.session = '{$_SESSION["seo_session"]}' AND A.id_pedido IS NULL
            ");
        }else{
            $negar = $this->DB_fetch_array("        
                SELECT C.*  FROM tb_produtos_produtos B 
                INNER JOIN tb_config_conjuntos_fretes_negar C ON C.id_conjunto = B.id_frete_terrestre 
                WHERE B.id = {$produto}
            ");
        }

        $resposta['negar'] = array();

        if ($negar->num_rows) {
            foreach ($negar->rows as $nega) {
                $resposta['negar'][] = $nega['id_estado'] . "-" . $nega['id_cidade'];
            }
        }

        return $resposta['negar'];
    }    

    private function naoNegarAereo($cidades, $estados = null, $produto) {

        if($produto==''){
            $permitir = $this->DB_fetch_array("        
                SELECT C.*  FROM tb_carrinho_produtos_historico A 
                INNER JOIN tb_produtos_produtos B ON B.id = A.id_produto 
                INNER JOIN tb_config_conjuntos_fretes_customizados C ON C.id_conjunto = B.id_frete_aereo
                WHERE A.session = '{$_SESSION["seo_session"]}' AND A.id_pedido IS NULL
            ");   
        }else{
            $permitir = $this->DB_fetch_array("        
                SELECT C.*  FROM tb_produtos_produtos B 
                INNER JOIN tb_config_conjuntos_fretes_customizados C ON C.id_conjunto = B.id_frete_aereo
                WHERE B.id = {$produto}
            ");
        }

        $array = array();

        if ($estados != null) {
            for ($i = 0; $i < count($estados); $i++) {
                if ($cidades[$i] == "") {
                    $array[] = $estados[$i];
                }
            }
        }

        if ($permitir->num_rows) {
            foreach ($permitir->rows as $permite) {
                if (in_array($permite['id_estado'], $array) && $permite['id_cidade'] == "") {
                    $result = true;
                }
            }
        }

        $cidades = array_filter($cidades);

        $result = false;

        if ($permitir->num_rows) {
            foreach ($permitir->rows as $permite) {
                if (in_array($permite['id_cidade'], $cidades) && $permite['id_cidade'] != "") {
                    $result = true;
                }
            }
        }

        return $result;
    }

    private function naoNegarTerrestre($cidades, $estados = null, $produto) {

        if($produto == ''){
            $permitir = $this->DB_fetch_array("        
                SELECT C.*  FROM tb_carrinho_produtos_historico A 
                INNER JOIN tb_produtos_produtos B ON B.id = A.id_produto 
                INNER JOIN tb_config_conjuntos_fretes_customizados C ON C.id_conjunto = B.id_frete_terrestre
                WHERE A.session = '{$_SESSION["seo_session"]}' AND A.id_pedido IS NULL
            ");
        }else{
            $permitir = $this->DB_fetch_array("        
                SELECT C.*  FROM tb_produtos_produtos B 
                INNER JOIN tb_config_conjuntos_fretes_customizados C ON C.id_conjunto = B.id_frete_terrestre
                WHERE B.id = {$produto}
            ");
        }

        $array = array();

        if ($estados != null) {
            for ($i = 0; $i < count($estados); $i++) {
                if ($cidades[$i] == "") {
                    $array[] = $estados[$i];
                }
            }
        }

        if ($permitir->num_rows) {
            foreach ($permitir->rows as $permite) {
                if (in_array($permite['id_estado'], $array) && $permite['id_cidade'] == "") {
                    $result = true;
                }
            }
        }

        $cidades = array_filter($cidades);

        $result = false;

        if ($permitir->num_rows) {
            foreach ($permitir->rows as $permite) {
                if (in_array($permite['id_cidade'], $cidades) && $permite['id_cidade'] != "") {
                    $result = true;
                }
            }
        }

        return $result;
    }

    public function getInfoFrete($id, $estados, $cidades, $frete_embutido = 0, $quantidade = 1) {
        $preco = 0;
        $prazo = 0;
        $cotacao_manual = 0;
        $cidades = array_filter($cidades);
        $query = $this->DB_fetch_array("SELECT * FROM tb_config_conjuntos_fretes_customizados A WHERE A.id_conjunto = $id");
        if ($query->num_rows) {
            //busca por cidade personalizada, pega maior preço

            foreach ($query->rows as $row) {

                if (in_array($row['id_cidade'], $cidades)) {
                    if($row['cotacao_manual']) $cotacao_manual = 1;
                    if ($row['prazo'] > $prazo) {
                        $prazo = $row['prazo'];
                    }
                    if ($row['preco'] > $preco) {
                        $preco = $row['preco'];
                    }
                }
            }

            if ($preco > 0)
                $preco = ($preco * $quantidade) - ($frete_embutido * $quantidade);

            //não encontrou cidade personalizada, busca por estado personalizado, pega maior preço
            if ($prazo == 0 && $preco == 0) {

                foreach ($query->rows as $row) {
                    if (in_array($row['id_estado'], $estados) && $prazo == 0) {
                        if($row['cotacao_manual']) $cotacao_manual = 1;
                        if ($row['prazo'] > $prazo) {
                            $prazo = $row['prazo'];
                        }
                        if ($row['preco'] > $preco) {
                            $preco = $row['preco'];
                        }
                    }
                }
                if ($preco > 0)
                    $preco = ($preco * $quantidade) - ($frete_embutido * $quantidade);
            }
        } else {
            //não encontrou cidade nem estado personalizado, busca por informações padrões, pega maior preço
            if ($prazo == 0 && $preco == 0) {
                $query = $this->DB_fetch_array("SELECT * FROM tb_config_conjuntos_fretes A WHERE A.id = $id");
                if ($query->num_rows) {
                    $flag = false;
                    foreach ($cidades as $cidade) {
                        if ($cidade != "") {
                            $capital = $this->DB_fetch_array("SELECT * FROM tb_config_cidades A WHERE A.id = $cidade AND capital = 1");
                            if ($capital->num_rows) {
                                foreach ($query->rows as $row) {
                                    if ($row['prazo_capital_padrao'] > $prazo) {
                                        $prazo = $row['prazo_capital_padrao'];
                                    }
                                    if ($row['preco_capital_padrao'] > $preco) {
                                        $preco = $row['preco_capital_padrao'];
                                    }
                                }
                                if ($preco > 0)
                                    $preco = ($preco * $quantidade) - ($frete_embutido * $quantidade);
                            }
                            $flag = true;
                        } else if (!$flag) {
                            foreach ($query->rows as $row) {
                                if ($row['prazo_padrao'] > $prazo) {
                                    $prazo = $row['prazo_padrao'];
                                }
                                if ($row['preco_padrao'] > $preco) {
                                    $preco = $row['preco_padrao'];
                                }
                            }
                            if ($preco > 0)
                                $preco = ($preco * $quantidade) - ($frete_embutido * $quantidade);
                        }
                    }
                }
            }
        }

        if ($preco < 1)
            $preco = 0;

        $resultado['preco'] = $preco;
        $resultado['prazo'] = $prazo;
        $resultado['cotacao_manual'] = $cotacao_manual;

        return $resultado;
    }

    public function entregaAereo($id, $estados, $cidades) {
        $cidades = array_filter($cidades);
        $resultado = true;
        foreach ($cidades as $cidade) {
            $query = $this->DB_fetch_array("SELECT * FROM tb_config_conjuntos_fretes_customizados A WHERE A.id_conjunto = $id AND id_cidade = $cidade");
            if (!$query->num_rows) {
                $resultado = false;
            }
        }

        foreach ($estados as $estado) {
            $query = $this->DB_fetch_array("SELECT * FROM tb_config_conjuntos_fretes_customizados A WHERE A.id_conjunto = $id AND id_estado = $estado");
            if (!$query->num_rows) {
                $resultado = false;
            }
        }

        return $resultado;
    }

    private function updateOptFretes() {
        if (isset($_SESSION['opt-tipo-frete']) && $_SESSION['opt-tipo-frete'] != "") {
            $_SESSION['opt-fretes'] = str_replace('checked', "", $_SESSION['opt-fretes']);
            $_SESSION['opt-fretes'] = str_replace("id='{$_SESSION['opt-tipo-frete']}'", "id='{$_SESSION['opt-tipo-frete']}' checked", $_SESSION['opt-fretes']);
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
