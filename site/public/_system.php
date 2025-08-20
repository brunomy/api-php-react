<?php

use System\Core\Bootstrap;
use System\Libs\PHPMailer;

class _sys extends Bootstrap {

    public $upload_folder = "../uploads/";
    public $upload_path = "uploads/";
    public $pagination_tag = "pg";
    public $pagination_page = 1;
    public $seo_pages = array();
    public $seo_dynamic_pages = array();
    public $empresa = array();
    public $cotacao_dollar = 3.99;

    function __construct() {

        parent::__construct();

        // -----------------------------------------------------------------------
        // BUSCA TODAS AS PÁGINAS QUE NÃO SÃO DINAMICAS 
        // --------------------------------------------------------  -------------

        $query = $this->DB_fetch_array("SELECT *, id id_seo FROM tb_seo_paginas");
        $i = $query->num_rows - 1;

        while ($i >= 0) {

            if ($query->rows[$i]['seo_pagina_dinamica'] == 0) {
                $this->seo_pages[$query->rows[$i]['seo_pagina']] = new stdClass();
                $this->seo_pages[$query->rows[$i]['seo_pagina']] = $query->rows[$i];
            } else {
                $this->seo_dynamic_pages[$query->rows[$i]['seo_pagina']][$query->rows[$i]['id']] = $query->rows[$i];
            }

            $i--;
        }

        $this->empresa = $this->DB_fetch_array("SELECT * FROM tb_admin_empresa WHERE id=1");
        $this->empresa = $this->empresa->rows[0];



        // -----------------------------------------------------------------------
        // ATUALIZA COTAÇÃO DO DOLLAR
        // --------------------------------------------------------  -------------

        if (!isset($_SESSION["cotacaoDolarOff"])) {
            if(!isset($_SESSION['cotacao_dolar'])){

                //busca no banco dados a cotação de hoje
                $query = $this->DB_fetch_array("SELECT * FROM tb_utils_utils A WHERE A.chave = 'cotacao_dolar' AND DATE(A.created_at) = CURDATE() ORDER BY A.created_at DESC LIMIT 1");

                //se existir segue com essa cotação
                if($query->num_rows){
                    $_SESSION['cotacao_dolar'] = $query->rows[0]['valor'];
                }else{                
                    $this->getCotacao();
                }

            }

        }
        
        $_SESSION["cotacaoDolarOff"] = "";
        unset($_SESSION["cotacaoDolarOff"]);

        $this->cotacao_dollar = $_SESSION['cotacao_dolar'] * 0.99;
        
        $this->mail = new PHPMailer();
    }

    function limitarPalavrasPorCaracteres($texto,$limite){
        $len = strlen($texto);
        if($len > $limite){
            $pos = strpos($texto, ' ', $limite);
            $texto = substr($texto,0,$pos);
            $texto = $texto."...";
        }
        return $texto;
    }


    function getCotacao(){
        $data1 = date("m-d-Y", time() - 7 * 60 * 60 * 24);
        $data2 = date("m-d-Y");
        $query = 'https://olinda.bcb.gov.br/olinda/servico/PTAX/versao/v1/odata/CotacaoDolarPeriodo(dataInicial=@dataInicial,dataFinalCotacao=@dataFinalCotacao)?@dataInicial=%27'.$data1.'%27&@dataFinalCotacao=%27'.$data2.'%27&$top=100&$orderby=dataHoraCotacao%20desc&$format=json';

        /*
            api de dados publicos do banco central
            https://dadosabertos.bcb.gov.br/dataset/dolar-americano-usd-todos-os-boletins-diarios/resource/ae69aa94-4194-45a6-8bae-12904af7e176
        */

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$query);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close ($ch);

        $return = json_decode($server_output);

        if(isset($return->value) && count($return->value) > 0){
            $_SESSION['cotacao_dolar'] = number_format($return->value[0]->cotacaoVenda,2,'.','');
            $this->DB_insert("tb_utils_utils", "chave,valor", "'cotacao_dolar','".$_SESSION['cotacao_dolar']."'");
        }else{
            $this->getCotacaoRedundancia();
        }

    }

    function getCotacaoRedundancia(){

        /*
            https://docs.awesomeapi.com.br/api-de-moedas
        */

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,'https://economia.awesomeapi.com.br/json/daily/USD-BRL/1');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close ($ch);

        $return = json_decode($server_output);

        if(isset($return[0]->code) && $return[0]->code = 'USD'){
            $_SESSION['cotacao_dolar'] = number_format($return[0]->bid,2,'.','');
            $this->DB_insert("tb_utils_utils", "chave,valor", "'cotacao_dolar','".$_SESSION['cotacao_dolar']."'");
        }else{


            //busca no banco dados a cotação mais recente
            $query = $this->DB_fetch_array("SELECT * FROM tb_utils_utils A WHERE A.chave = 'cotacao_dolar' ORDER BY A.created_at DESC LIMIT 1");

            //se existir segue com essa cotação
            if($query->num_rows){
                $_SESSION['cotacao_dolar'] = $query->rows[0]['valor'];
            }

            $query = $this->DB_fetch_array("SELECT * FROM tb_utils_utils A WHERE A.chave = 'alerta_critico' AND A.valor = 'cotacao_dolar' AND A.created_at BETWEEN (DATE_SUB(NOW(),INTERVAL 15 MINUTE)) AND NOW()");

            //enviar alerta crítico a cada 15 min
            if(!$query->num_rows){

                $this->DB_insert("tb_utils_utils", "chave,valor", "'alerta_critico','cotacao_dolar'");

                $this->alertaCritico("API Cotação do dólar", "Não foi possível fazer a leitura da cotação do dólar via API do Banco Central. Essa funcionalidade está descoberta e precisa de manutenção. Cotação atual: ".$_SESSION['cotacao_dolar']);
            }


        }

    }

    //detecta página (URI) corrente e devolve o número da página corrente para paginação substituindo o $pagination_page (correção 06/08/214)
    function paginaCorrente() {
        $pag = explode("/", $_SERVER['REQUEST_URI']);

        $pagina = 1;
        if (is_numeric($pag[3])) {
            $pagina = (int) $pag[3];
        } else if (is_numeric($pag[4])) {
            $pagina = (int) $pag[4];
        } else {

            for ($i = 0; $i < count($pag); $i++) {

                if (is_numeric($pag[$i])) {
                    $pagina = (int) $pag[$i];
                }
            }
        }

        return $pagina;
    }

    function pagination($itens = 5, $total = 0, $range = 5, $current = 0) {

        $pagination = new stdClass();

        $pagination->itens_per_page = $itens; // quantidade de registros por página
        $pagination->itens_total = $total; // quantidade total de registros
        $pagination->range_of_numbers = $range; // quantidade de numeros visiveis (raio) na paginação 
        $pagination->page_number_from_browser = $current; // numero da pagina atual

        $pagination->bd_search_starts_at = $pagination->itens_per_page * $pagination->page_number_from_browser;
        $pagination->range_centered_number = ceil($pagination->range_of_numbers / 2);
        $pagination->pages_total = ceil($pagination->itens_total / $pagination->itens_per_page);
        $pagination->page_current = $pagination->page_number_from_browser + 1;
        $pagination->page_prev = $pagination->page_current - 1;
        $pagination->page_next = $pagination->page_current + 1;

        $pagination->range_initial_limit = $pagination->pages_total + $pagination->range_of_numbers;

        // DEFININDO O INICIO DO RAIO
        if ($pagination->page_current <= $pagination->range_centered_number) {
            $pagination->range_initial_number = 1;
        } else {
            $pagination->range_initial_number = $pagination->page_current - $pagination->range_centered_number + 1;
        }

        // VERIFICA SE O INICIO DO RAIO ESTÁ SEU LIMITE NO FINAL
        if ($pagination->range_initial_number > $pagination->range_initial_limit) {

            // DEFINE O INICIO E FIM DO RAIO
            $pagination->range_initial_number = $pagination->range_initial_limit + 1;
            $pagination->range_end_number = $pagination->pages_total;
        } else {

            //DEFINE O FIM DO RAIO
            $pagination->range_end_number = ($pagination->range_initial_number + $pagination->range_of_numbers) - 1;
        }

        return $pagination;
    }

}

class pagination {

    public $itens_per_page, $itens_total, $bd_search_starts_at, $pages_total, $page_current, $page_prev, $page_next, $range_initial_number, $range_end_number;
    private $range_of_numbers, $page_number_from_browser;

    public function __construct($itens = 5, $total = 0, $range = 5, $current = 1) {

        $this->itens_per_page = $itens; // quantidade de registros por página
        $this->itens_total = $total; // quantidade total de registros
        $this->range_of_numbers = $range; // quantidade de numeros visiveis (raio) na paginação 
        $this->page_current = $current; // numero da pagina atual

        $this->bd_search_starts_at = $this->itens_per_page * ($this->page_current - 1);
        $this->range_centered_number = ceil($this->range_of_numbers / 2);
        $this->pages_total = ceil($this->itens_total / $this->itens_per_page);
        $this->page_prev = $this->page_current - 1;
        $this->page_next = $this->page_current + 1;

        if($this->pages_total <= $this->itens_per_page){
            $this->range_initial_limit = 1;
            $this->range_initial_number = 1;
            $this->range_end_number = $this->pages_total;
        }else{

            $this->range_initial_limit = $this->pages_total - $this->range_of_numbers;

            // DEFININDO O INICIO DO RAIO
            if ($this->page_current <= $this->range_centered_number) {
                $this->range_initial_number = 1;
            } else {
                $this->range_initial_number = $this->page_current - $this->range_centered_number + 1;
            }

            // VERIFICA SE O INICIO DO RAIO ESTÁ SEU LIMITE NO FINAL
            if ($this->range_initial_number > $this->range_initial_limit) {

                // DEFINE O INICIO E FIM DO RAIO
                $this->range_initial_number = $this->range_initial_limit + 1;
                $this->range_end_number = $this->pages_total;
            } else {

                //DEFINE O FIM DO RAIO
                $this->range_end_number = ($this->range_initial_number + $this->range_of_numbers) - 1;
            }

        }
    }

}

?>