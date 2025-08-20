<?php

namespace System\Libs;

use System\Core\System;

class EmailMarketingCliente extends System {

    function __construct() {
        parent::__construct();

        $this->vs = "2.0";
    }

    public function getEmailMarketing($parametro = null, $post = null) {
        #$url = 'http://192.168.25.135/hibrida/site/sys/scripts/webservice.php'; #COLOQUE A URL DO WEBSERVICE DA J2B DIGITAL
        $url = 'http://adm.hibrida.biz/scripts/webservice.php';

        $data['email'] = $this->_empresa['email_mkt']; #COLOQUE O E-MAIL REGISTRADO NA J2B DIGITAL
        $data['token'] = $this->_empresa['token_mkt']; #COLOQUE SEU TOKEN, O TOKEN É FORNECIDO PELA J2B DIGITAL

        $data['parametro'] = "$parametro";

        $data['root_path'] = "$this->root_path";

        $data['vs'] = $this->vs;

        $data['post'] = $post;



        $data = http_build_query($data);

        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_TIMEOUT, 800);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        $result = curl_exec($curl);

        curl_close($curl);

        $resposta = json_decode($result);

        //$this->DB_insert('tb_admin_webservice_logs', "autenticacao, resposta", "'$resposta->autenticacao', '$result'");

        return $resposta;
    }

    function sendFileEmailMarketing($parametro = null, $post = null) {

        #$url = 'http://192.168.25.135/hibrida/site/sys/scripts/webservice.php'; #COLOQUE A URL DO WEBSERVICE DA J2B DIGITAL
        $url = 'http://adm.hibrida.biz/scripts/webservice.php';

        $data['email'] = $this->_empresa['email_mkt']; #COLOQUE O E-MAIL REGISTRADO NA J2B DIGITAL
        $data['token'] = $this->_empresa['token_mkt']; #COLOQUE SEU TOKEN, O TOKEN É FORNECIDO PELA J2B DIGITAL

        $data['parametro'] = "$parametro";

        $data['root_path'] = "$this->root_path";

        $data['id_lista'] = $post->id_lista;

        $data['nome'] = $post->nome;

        $data['lista'] = $post->lista;
        
        $data['vs'] = $this->vs;
                
        $data['segmentacoes'] = $post->segmentacoes;

        if (file_exists(__sys_path__ . "tmp/arquivo.csv")) {
            $data['csv'] = "@tmp/arquivo.csv";
        }
    
        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        @curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        $result = curl_exec($curl);

        curl_close($curl);

        $resposta = json_decode($result);

        //$this->DB_insert('tb_admin_webservice_logs', "autenticacao, resposta", "'$resposta->autenticacao', '$result'");

        return $resposta;
    }

}
