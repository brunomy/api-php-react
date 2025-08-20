<?php 

namespace System\Libs;

class ContaAzul
{
    private $sistema;
    private $access_token;
    private $api_endpoint = 'https://api.contaazul.com';
    private $verify_ssl   = false;

    function __construct($sistema)
    {
        $this->sistema = $sistema;
        $this->access_token = $this->getToken();
    }    

    private function getToken() {
        
        $query = $this->sistema->DB_fetch_array("SELECT contaazul_refresh_token, contaazul_access_token, contaazul_access_token_expires_at FROM tb_admin_empresa WHERE id = 1");

        if($query->rows[0]['contaazul_refresh_token'] == "") exit("Conta Azul não está conectado");

        //verifica se token ainda não expirou
        if ($query->rows[0]['contaazul_access_token_expires_at'] > date('Y-m-d H:i:s')) 
            return $query->rows[0]['contaazul_access_token'];

        return $this->getNewToken($query->rows[0]['contaazul_refresh_token']);
    }

    public function getNewToken($refresh_token) {

        $url = 'https://api.contaazul.com/oauth2/token?grant_type=refresh_token&refresh_token='.$refresh_token;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic '.base64_encode($_ENV['CONTAAZUL_CLIENT_ID'].":".$_ENV['CONTAAZUL_CLIENT_SECRET'])));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10000);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $result = curl_exec($ch);

        if ($result === false) {
            echo curl_error($ch);
            exit();
        }
        curl_close($ch);
        $result = json_decode($result);

        $expires_in = date('Y-m-d H:i:s', (time() + $result->expires_in));

        $this->sistema->DB_update('tb_admin_empresa','contaazul_refresh_token="'.$result->refresh_token.'", contaazul_access_token="'.$result->access_token.'", contaazul_access_token_expires_at="'.$expires_in.'" WHERE id=1');

        return $result->access_token;
    }

    public function getCategoriesList(){

        $ch = curl_init();
        //curl_setopt($ch, CURLOPT_URL, 'https://api.contaazul.com/v1/products?page=7');
        curl_setopt($ch, CURLOPT_URL, 'https://api.contaazul.com/v1/product-categories');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization: Bearer '.$this->access_token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10000);
        //curl_setopt($ch, CURLOPT_POST, true);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        //curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sale));
        $result = curl_exec($ch);

        if ($result === false) {
            echo curl_error($ch);
            exit();
        }
        curl_close($ch);

        return json_decode($result);

    }

    public function getProductsByCategory($category_id){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.contaazul.com/v1/product-categories/'.$category_id);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization: Bearer '.$this->access_token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10000);
        $result = curl_exec($ch);

        if ($result === false) {
            echo curl_error($ch);
            exit();
        }
        curl_close($ch);

        return json_decode($result);

    }

    public function newProduct($id){

        $query = $this->sistema->DB_fetch_array('SELECT * FROM tb_produtos_produtos a WHERE a.id = '.$id);

        if(!$query->num_rows) return false;

        $product = new \stdClass();

        $product->name = '[LOCK] '.$query->rows[0]['nome'];
        $product->value = 0;
        $product->cost = 0;
        $product->category_id = '33d0266e-d358-49a2-84da-b0b1e4890167';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.contaazul.com/v1/products');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization: Bearer '.$this->access_token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10000);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($product));
        $request = curl_exec($ch);

        if ($request === false) {
            echo curl_error($ch);
            exit();
        }
        curl_close($ch);
        $response = json_decode($request);

        $this->sistema->DB_update('tb_produtos_produtos','contaazul_id="'.$response->id.'" WHERE id='.$id);

        return $response;
    }

    public function getOrCreateCustomer($name, $company = null){

        $search = $company ?? $name;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.contaazul.com/v1/customers?search='.urlencode($search));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization: Bearer '.$this->access_token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10000);
        $request = curl_exec($ch);

        if ($request === false) {
            echo curl_error($ch);
            exit();
        }
        curl_close($ch);
        $response = json_decode($request);

        if($response == '' || !count($response) || count($response) > 1){

            $customer = new \stdClass();
            $customer->name = $search != $name ? $name.' ('.$company.')' : $name;
            $customer->person_type = $company === null ? 'NATURAL' : 'LEGAL';

            $ch2 = curl_init();
            curl_setopt($ch2, CURLOPT_URL, 'https://api.contaazul.com/v1/customers');
            curl_setopt($ch2, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization: Bearer '.$this->access_token));
            curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch2, CURLOPT_TIMEOUT, 10000);
            curl_setopt($ch2, CURLOPT_POST, true);
            curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode($customer));
            $request2 = curl_exec($ch2);

            if ($request2 === false) {
                echo curl_error($ch2);
                exit();
            }
            curl_close($ch2);
            $response2 = json_decode($request2);

            return $response2->id;

        }else{
            return $response[0]->id;
        }

    }



    public function delProduct($id){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.contaazul.com/v1/products/'.$id);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization: Bearer '.$this->access_token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10000);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        $request = curl_exec($ch);

        if ($request === false) {
            echo curl_error($ch);
            exit();
        }
        curl_close($ch);

        return json_decode($request);
    }

    public function listSellers(){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.contaazul.com/v1/sales/sellers');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization: Bearer '.$this->access_token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10000);
        $request = curl_exec($ch);

        if ($request === false) {
            echo curl_error($ch);
            exit();
        }
        curl_close($ch);
        
        return json_decode($request);

    }

    public function newSale($sale){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.contaazul.com/v1/sales');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization: Bearer '.$this->access_token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10000);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sale));
        $result = curl_exec($ch);

        if ($result === false) {
            echo curl_error($ch);
            exit();
        }
        curl_close($ch);

        return json_decode($result);

    }
    
    public function delSale($id){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.contaazul.com/v1/sale/'.$id);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization: Bearer '.$this->access_token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10000);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        $request = curl_exec($ch);

        if ($request === false) {
            echo curl_error($ch);
            exit();
        }
        curl_close($ch);

        return json_decode($request);
    }


}

?>