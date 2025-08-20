<?php

//https://github.com/DeveloperCielo/API-3.0-PHP

//http://developercielo.github.io/Webservice-3.0/#post-de-notificação
//https://developercielo.github.io/Checkout-Cielo/#url-de-mudança-de-status

namespace payments\cielo_transparente;

class CieloCheckoutTransparent {

    var $_MerchantId                    = '';
    var $_MerchantKey                   = '';
    var $_sandbox_transaction_url       = 'https://apisandbox.cieloecommerce.cielo.com.br';
    var $_sandbox_query_url             = 'https://apiquerysandbox.cieloecommerce.cielo.com.br';
    var $_production_transaction_url    = 'https://api.cieloecommerce.cielo.com.br';
    var $_production_query_url          = 'https://apiquery.cieloecommerce.cielo.com.br';
    var $_sandbox_enviroment            = true;

    public function setMerchant($id, $key, $sandbox) {
        $this->_MerchantId = $id;
        $this->_MerchantKey = $key;
        $this->_sandbox_enviroment = $sandbox;
    }

    public function simpleTransaction($data) {

        if($this->_sandbox_enviroment){
            $transation_url = $this->_sandbox_transaction_url;
        }else{
            $transation_url = $this->_production_transaction_url;
        }

        $data = json_encode($data);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $transation_url.'/1/sales/');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, true);
        //curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type: application/json',
            'MerchantId: '.$this->_MerchantId,
            'MerchantKey: '.$this->_MerchantKey,
            'RequestId: ' . uniqid()
        ));
        

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = '';

        if (curl_errno($ch)) {
            $error = curl_error($ch);
        }

        //Tratamento do retorno
            $response = trim($response, '"');
            $res = json_decode($response);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // Tenta decodificar novamente, tratando o JSON inválido
                $response = stripslashes($response); // Remove barras invertidas se houver
                $res = json_decode($response);
            }
        
        $return = new \stdClass();
        $return->response = $res;
        $return->raw_response = $response;
        $return->statusCode = $statusCode;
        $return->transation_url = $transation_url;
        $return->error = $error;
        


        //$return = json_decode('{"response":'.$response.',"statusCode":"'.$statusCode.'","error": "'.$error.'"}');

        curl_close($ch);
        return $return;

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
?>

