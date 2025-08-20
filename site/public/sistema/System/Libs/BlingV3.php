<?php 

namespace System\Libs;

class BlingV3
{
    private $sistema;
    private $company_id;
    private $client_id;
    private $client_secret;
    private $token;

    private $apiUrl = 'https://bling.com.br/Api/v3/';

    function __construct($sistema)
    {
        $this->sistema = $sistema;

        //Incluir esse parametro no objeto do sistema no momento que for instanciar a classe do Bling 
        if(!isset($sistema->bling_company_id)) throw new \InvalidArgumentException("O parâmetro bling_company_id não foi encontrado.");
        
        $query = $sistema->DB_fetch_array("SELECT * FROM tb_admin_empresas WHERE id = {$sistema->bling_company_id}");
        if(!$query->num_rows) throw new \RuntimeException("Instancia do bling não encontrado");
        
        $this->company_id       = $sistema->bling_company_id;
        $this->client_id        = $query->rows[0]['invoice_bling_client_id'];
        $this->client_secret    = $query->rows[0]['invoice_bling_client_secret'];
        $this->token            = $query->rows[0]['invoice_bling_autorization_code'];

        if($this->token == '') 
            return;

        $now = date('Y-m-d H:i:s');
        $expires = $query->rows[0]['invoice_bling_token_expires_at'];

        if ($now > $expires) {
            $this->token = $this->requestToken(['grant_type' => 'refresh_token', 'refresh_token' => $query->rows[0]['invoice_bling_refresh_token']]);
        }

    }

    public function getAuthorizetionUrl($state)
    {
        if($this->client_id == "") exit("Bling não está conectado");
        return $this->apiUrl . "oauth/authorize?response_type=code&client_id={$this->client_id}&client_secret={$this->client_secret}&state={$state}";
    }

    public function authenticate($authorizationCode)
    {

        return $this->requestToken(['grant_type' => 'authorization_code', 'code' => $authorizationCode]);

    }

    protected function oaut_request($params = []){
        $url = $this->apiUrl . 'oauth/token';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Basic '.base64_encode($this->client_id.":".$this->client_secret), 
            'Accept: 1.0',
            'Content-Type: application/x-www-form-urlencoded',
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        $result = curl_exec($ch);

        if ($result === false) {
            $error = curl_error($ch);
            $errorCode = curl_errno($ch);
            curl_close($ch);
            throw new \RuntimeException("Erro na requisição cURL: $error", $errorCode);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {

            $decoded = json_decode($result);
            
            if(isset($decoded->error) && isset($decoded->error->type) && isset($decoded->error->description)){
                throw new \RuntimeException("Erro {$httpCode} - {$decoded->error->type} - {$decoded->error->message}: {$decoded->error->description}");
            }

            throw new \RuntimeException("Erro HTTP ao acessar $url: Código $httpCode.");

        }

        return $result;
    }

    private function requestToken(Array $params){

        $result = json_decode($this->oaut_request($params));

        $expires_in = date('Y-m-d H:i:s', (time() + ($result->expires_in - 300)));

        $query = $this->sistema->DB_update('tb_admin_empresas','invoice_bling_refresh_token="'.$result->refresh_token.'", invoice_bling_autorization_code="'.$result->access_token.'", invoice_bling_token_expires_at="'.$expires_in.'" WHERE id='.$this->company_id);

        return $result->access_token;

    }

    protected function request($endpoint, $method = 'get', $params = []){
        $url = $this->apiUrl . $endpoint;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $this->token, 
            'Content-Type: application/json',
            'Accept: application/json',
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        if($method == 'post'){
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        }
        $result = curl_exec($ch);

        if ($result === false) {
            $error = curl_error($ch);
            $errorCode = curl_errno($ch);
            curl_close($ch);
            throw new \RuntimeException("Erro na requisição cURL: $error", $errorCode);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {

            $decoded = json_decode($result);

            $this->sistema->inserirRelatorio("Bling Errors: " . $result);
            
            if(isset($decoded->error) && isset($decoded->error->type) && isset($decoded->error->description)){
                throw new \RuntimeException("Erro {$httpCode} - {$decoded->error->type} - {$decoded->error->message} - {$decoded->error->description}");
            }

            throw new \RuntimeException("Erro HTTP ao acessar $url: Código $httpCode.");

        }

        return $result;
    }

    public function createNF(Array $data){
        return $this->request('nfe', 'post', $data);
    }

}

?>