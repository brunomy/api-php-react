<?php 

namespace System\Libs;

class Notificacoes
{
    private $sistema;
    private $identificador;
    private $assunto;
    private $body;
    private $vars;
    private $to;

    public function __construct(object $sistema, object $vars, string $identificador)
    {
        $this->sistema = $sistema;
        $this->identificador = $identificador;
        $this->vars = $vars;
        $this->to = $this->sendTo($identificador);
        $this->body = $this->message($identificador);
    }

    public function disparaNotificao(){
        $this->sistema->enviarEmail($this->to, '', utf8_decode($this->assunto), utf8_decode($this->body));
    }

    private function sendTo(){
        $to = [];
        $emails = $this->sistema->DB_fetch_array("SELECT C.email, C.nome FROM tb_admin_forms A INNER JOIN tb_admin_email_notification B ON A.id = B.id_form INNER JOIN tb_admin_users C ON B.id_user = C.id WHERE A.identificador = 'todos' OR A.identificador = '{$this->identificador}' GROUP BY C.email, C.nome");
        if ($emails->num_rows) {
            if ($emails->num_rows) {
                foreach ($emails->rows as $mail) {
                    $to[] = array("email" => $mail['email'], "nome" => $mail['nome']);
                }
            }
        }

        return $to;
    }    

    private function message(){

        switch ($this->identificador) {
            case 'cotacao-frete':
                
                $this->assunto = "Pedido de Cotação de Frete (Pedido #{$this->vars->PEDIDO})";
                $template = __root_path__."public/mailing_templates/solicita_cotacao_frete.html";

                break;

        }

        //pega template
        $body = file_get_contents($template);

        //preenche variáveis
        foreach ($this->vars as $key => $value) {
            $body = str_replace("{{$key}}", $value, $body);
        }

        return $body;

    }


}

?>