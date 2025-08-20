<?php

namespace classes;

use System\Core\Bootstrap;

class Cliente extends Bootstrap {

    //pega dados do produto por seu id
    public function adicionarEmailBase($crm_id)
    {
        $query = $this->DB_fetch_array("SELECT 
                                                    C.nome as vendedor_nome,
                                                    C.telefone as vendedor_telefone,
                                                    C.email as vendedor_email,
                                                 tb_crm_crm.email as email_cliente,
                                                 tb_crm_crm.nome as cliente_nome
                                                FROM
                                                    tb_crm_crm
                                                     INNER JOIN 
                                                     tb_admin_users C ON C.id = tb_crm_crm.id_user
                                                WHERE
                                                  tb_crm_crm.id = $crm_id
                                                ");

        if(!$query->num_rows)
            return;
        $this->crm = $query->rows[0];
        $cliente_email = $this->crm['email_cliente'];
        $cliente_nome = $this->crm['cliente_nome'];

        $vendedor_nome = $this->crm['vendedor_nome'];
        $vendedor_telefone = $this->crm['vendedor_telefone'];
        $vendedor_email = $this->crm['vendedor_email'];

        $verifica = $this->DB_num_rows("SELECT * FROM tb_emails_emails A INNER JOIN tb_listas_listas_has_tb_emails_emails B ON B.id_email = A.id AND A.email = ' $cliente_email' AND B.id_lista = 3");
        if (!$verifica) {
            $verifica = $this->DB_fetch_array("SELECT * FROM tb_emails_emails WHERE email = '$cliente_email'");
            if (!$verifica->num_rows) {
                $addEmail = $this->DB_insert('tb_emails_emails', "nome,email", "'$cliente_nome','$cliente_email'");
                $idEmail = $addEmail->insert_id;

                $to[] = array("email" => $cliente_email, "nome" => utf8_decode($cliente_nome));

                $assunto = "Formulário Cadastro de Cliente [$cliente_nome]";  // Assunto da mensagem de contato.

                $body = file_get_contents("../mailing_templates/email_cadastro_crm.html");
                $body = str_replace("{NOME_CLIENTE}", $cliente_nome, $body);
                $body = str_replace("{VENDEDOR}", $vendedor_nome, $body);
                $body = str_replace("{TELEFONE_VENDEDOR}", $vendedor_telefone, $body);
                $body = str_replace("{EMAIL_VENDEDOR}", $vendedor_email, $body);

                $this->enviarEmail($to, '', utf8_decode($assunto), utf8_decode($body));

            } else {
                $idEmail = $verifica->rows[0]['id'];
            }
            $addListaHasEmail = $this->DB_insert('tb_listas_listas_has_tb_emails_emails', "id_lista,id_email", "3,$idEmail");
        }
    }


}
