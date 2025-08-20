<?php

use System\Core\Bootstrap;
use classes\Product;

Order::setAction();

class Order extends Bootstrap {

    public $module = "";
    public $permissao_ref = "pedidos-pedidos";
    public $table = "tb_pedidos_pedidos";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
        }
        $this->getAllPermissions();

        $this->arquivo = "";

        $this->module_icon = "icomoon-icon-cart-4";
        $this->module_link = "order";
        $this->module_title = "Pedidos";
        $this->retorno = "order";

        $this->product = new Product();

        $this->crop_sizes = array();
        array_push($this->crop_sizes, array("width" => 400, "height" => 1000, "best_fit" => true));
    }

    public function paymentType($string) {
        switch ($string) {
            case 'deposito':
                return 'Depósito';
                break;

            case 'boleto':
                return 'Boleto';
                break;

            case 'cielo':
                return 'Cielo';
                break;

            case 'pagseguro':
                return 'Pagseguro';
                break;

            case 'pokerstars':
                return 'Pokerstars';
                break;

            default:
                break;
        }
    }

    public function cieloBrands($int) {
        switch ($int) {
            case 1:
                return 'Visa';
                break;

            case 2:
                return 'Mastercard';
                break;

            case 3:
                return 'AmericanExpress';
                break;

            case 4:
                return 'Diners';
                break;

            case 5:
                return 'Elo';
                break;

            case 6:
                return 'Aura';
                break;

            case 7:
                return 'JCB';
                break;

            default:
                break;
        }
    }

    private function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }
        if($_SESSION['admin_grupo'] == 2 || $_SESSION['admin_grupo'] == 3){
            $this->list = $this->DB_fetch_array("SELECT A.*, A.data registro, DATE_FORMAT(A.data, '%d/%m/%Y às %H:%i') data_compra, DATE_FORMAT(A.prazo_entrega, '%d/%m/%Y') prazo_entrega, B.nome status, B.cor, IF(C.pessoa = 1, C.nome, C.razao_social) cliente, C.cpf, C.telefone, C.email, E.cidade, F.estado, G.nome vendedor, H.payment_method_brand, I.nome editando FROM $this->table A INNER JOIN tb_pedidos_status B ON B.id = A.id_status INNER JOIN tb_clientes_clientes C ON C.id = A.id_cliente INNER JOIN tb_pedidos_enderecos D ON D.id_pedido = A.id INNER JOIN tb_utils_cidades E ON E.id = D.id_cidade INNER JOIN tb_utils_estados F ON F.id = E.id_estado LEFT JOIN tb_admin_users G ON A.id_vendedor = G.id LEFT JOIN tb_pedidos_transacoes_cielo H ON H.checkout_cielo_order_number = A.metodo_pagamento_id LEFT JOIN tb_admin_users I ON A.usuario_editando_pedido = I.id  ORDER BY A.data DESC");            
        }else{
            //SOMENTE OS PEDIDOS VINCULADOS AO VENDEDOR
            $this->list = $this->DB_fetch_array("SELECT A.*, A.data registro, DATE_FORMAT(A.data, '%d/%m/%Y às %H:%i') data_compra, DATE_FORMAT(A.prazo_entrega, '%d/%m/%Y') prazo_entrega, B.nome status, B.cor, IF(C.pessoa = 1, C.nome, C.razao_social) cliente, C.cpf, C.telefone, C.email, E.cidade, F.estado, G.nome vendedor, H.payment_method_brand, I.nome editando FROM $this->table A INNER JOIN tb_pedidos_status B ON B.id = A.id_status INNER JOIN tb_clientes_clientes C ON C.id = A.id_cliente INNER JOIN tb_pedidos_enderecos D ON D.id_pedido = A.id INNER JOIN tb_utils_cidades E ON E.id = D.id_cidade INNER JOIN tb_utils_estados F ON F.id = E.id_estado LEFT JOIN tb_admin_users G ON A.id_vendedor = G.id LEFT JOIN tb_pedidos_transacoes_cielo H ON H.checkout_cielo_order_number = A.metodo_pagamento_id LEFT JOIN tb_admin_users I ON A.usuario_editando_pedido = I.id WHERE A.id_vendedor = {$_SESSION['admin_id']} ORDER BY A.data DESC");
        }

        $this->renderView($this->getModule(), "index");
        
        //$this->updateCampanhasConversoes(4099);
    }

    public function getDeducoesByProduto($idPedido = null, $id = null) {
        if ($idPedido != null && $id != null) {
            $query = $this->DB_fetch_array("SELECT * FROM tb_pedidos_deducoes WHERE id_pedido = $idPedido AND id_produto_carrinho = $id ORDER BY descricao");
            return $query;
        }
    }

    private function saveDeducoesAction() {
        if (!$this->permissions[$this->permissao_ref]['editar'])
            exit();

        $query = new \stdClass;
        $query->query = true;

        $resposta = new \stdClass;

        $formulario = $this->formularioObjeto($_POST);
        $this->DB_delete("tb_pedidos_deducoes", "id_produto_carrinho = $formulario->id");

        if (isset($_POST['valores'])) {
            for ($i = 0; $i < count($_POST['valores']); $i++) {
                if ($_POST['valores'][$i] != "") {
                    $query = $this->DB_insert("tb_pedidos_deducoes", "id_pedido,id_produto_carrinho,descricao,valor", "'$formulario->id_pedido','$formulario->id','{$_POST['descricoes'][$i]}','{$this->formataMoedaBd($_POST['valores'][$i])}'");
                }
            }
        }

        if ($query->query) {
            $resposta->type = "success";
            $resposta->message = "Registros salvos com sucesso!";
            $this->inserirRelatorio("Alterou adição/dedução para cálculo de fábrica, id produto do carrinho: [" . $formulario->id . "] nº pedido: [$formulario->id_pedido]");
        } else {
            $resposta->type = "error";
            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
        }

        echo json_encode($resposta);
    }

    private function getDeducaoTemplateAction() {
        if (!$this->permissions[$this->permissao_ref]['editar'])
            exit();
        $this->renderView($this->getModule(), "deducao");
    }

    private function editAction() {
        $this->id = $this->getParameter("id");
        if ($this->id == "") {
            //new            
            $this->noPermission();
        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar'])
                $this->noPermission();

            $query = $this->DB_fetch_array("SELECT A.*, A.data registro, DATE_FORMAT(A.data, '%d/%m/%Y às %H:%i') data_compra, DATE_FORMAT(A.prazo_entrega, '%d/%m/%Y') prazo_entrega, B.nome status, C.nome cliente, H.session  FROM $this->table A INNER JOIN tb_pedidos_status B ON B.id = A.id_status INNER JOIN tb_clientes_clientes C ON C.id = A.id_cliente INNER JOIN tb_carrinho_produtos_historico H ON H.id_pedido = A.id  WHERE A.id = $this->id", "form");
            $this->registro = $query->rows[0];

            $this->rastreios = $this->DB_fetch_array("SELECT * FROM tb_pedidos_rastreios A INNER JOIN tb_pedidos_has_tb_rastreios B ON B.id_rastreio = A.id WHERE B.id_pedido = {$this->registro['id']} ORDER BY A.data DESC");

            $this->historicos = $this->DB_fetch_array("SELECT *, DATE_FORMAT(data, '%d/%m/%Y às %H:%i') registro FROM tb_pedidos_historicos WHERE id_pedido = {$this->registro['id']} ORDER BY data DESC");

            $this->historico_emails = $this->DB_fetch_array("SELECT *, DATE_FORMAT(data, '%d/%m/%Y %H:%i') registro, data data FROM tb_pedidos_emails_historicos WHERE id_pedido = '{$this->registro['id']}' ORDER BY data DESC");
            
            $this->historicosnav = $this->DB_fetch_array("SELECT DATE_FORMAT(B.date, '%d/%m/%Y às %H:%i:%s') registro, B.date, C.seo_title titulo, B.origem, CONCAT(B.cidade, ', ', B.estado, ' - ', B.pais) localizacao, B.pais, B.estado, B.cidade, B.dispositivo, B.ip, B.session FROM tb_pedidos_pedidos A LEFT JOIN tb_carrinho_produtos_historico H ON H.id_pedido = A.id LEFT JOIN tb_seo_acessos_historicos B ON B.session = H.session INNER JOIN tb_seo_paginas C ON C.id = B.id_seo WHERE H.session = '{$this->registro['session']}' ORDER BY B.date");

            $this->usuarios = $this->DB_fetch_array("SELECT A.* FROM tb_admin_users A INNER JOIN tb_admin_grupos B ON A.id_grupo = B.id WHERE A.stats = 1 AND B.stats = 1");

            $this->avaliacoes = $this->DB_fetch_array("SELECT * FROM tb_produtos_avaliacoes WHERE id_pedido = ".$this->id);

            $this->extrato_financeiro = $this->DB_fetch_array("SELECT categoria, SUM(total_frete_embutido) total_frete, SUM(total_sem_frete) total_sem_frete FROM
                (SELECT *, (total_pago - total_frete_embutido) total_sem_frete FROM
                (SELECT *, ((valor_produto*quantidade) - (desconto*quantidade)) total_pago, (frete_embutido*quantidade) total_frete_embutido FROM 
                (SELECT a.id_produto, c.id id_categoria, c.nome categoria, a.custo, a.valor_produto, a.quantidade, COALESCE(a.desconto, 0) desconto, d.frete_embutido 
                FROM tb_carrinho_produtos_historico a 
                INNER JOIN tb_produtos_produtos_has_tb_produtos_categorias b ON a.id_produto = b.id_produto 
                INNER JOIN tb_produtos_categorias c ON b.id_categoria = c.id
                INNER JOIN tb_produtos_produtos d ON a.id_produto = d.id  
                WHERE id_pedido = {$this->id}) select1) select2) select3
                GROUP BY id_categoria");

            // SE NÃO EXISTE NINGUEM EDITANDO NO MOMENTO CONCEDER EDIÇÃO PARA ESTE USUÁRIO
                if($this->registro['usuario_editando_pedido'] == "" || $this->registro['usuario_editando_pedido'] == 0){
                    $this->concederEdicao($this->registro['id']);
                    $this->editor_permission = 1;
                    $this->editor_name = $_SESSION['admin_nome'];
                }else{

                    $this->editor_name = $this->DB_fetch_array("SELECT * FROM tb_admin_users WHERE id = ".$this->registro['usuario_editando_pedido']);
                    $this->editor_name = $this->editor_name->rows[0]['nome'];

                    //VERIFICA SE O ÚLTIMO PING É MAIOR QUE 1 MINUTO
                        $last_ping = strtotime($this->registro['usuario_editando_ultimo_ping']);
                        $now = strtotime(date("Y-m-d H:i:s"));
                        $interval = abs($now - $last_ping);
                        $difenca = round($interval / 60);

                        if($difenca > 1){
                            //SE FOR MAIOR QUE 1 MINUTO, CONCEDER NOVA EDIÇÃO PARA ESTE USUÁRIO
                                $this->concederEdicao($this->registro['id']);
                                $this->editor_permission = 1;
                                $this->editor_name = $_SESSION['admin_nome'];
                        }else if($_SESSION['admin_id'] == $this->registro['usuario_editando_pedido']){
                            //SE O USUÁRIO FOR O MESMO, CONCEDER NOVA EDIÇÃO PARA ELE
                                $this->concederEdicao($this->registro['id']);
                                $this->editor_permission = 1;
                        }else{
                            $this->editor_permission = 0;
                        }
                }

        }

        $this->status = $this->DB_fetch_array("SELECT * FROM tb_pedidos_status ORDER BY ordem");

        $this->estados = $this->DB_fetch_array("SELECT * FROM tb_utils_estados ORDER BY estado");



        $this->renderView($this->getModule(), "edit");
    }

    private function cidadesAction() {
        if (!$this->permissions[$this->permissao_ref]['ler'])
            $this->noPermission();

        $query = $this->DB_fetch_array("SELECT * FROM tb_utils_cidades WHERE id_estado = {$_GET['id']}");
        if ($query->num_rows) {
            foreach ($query->rows as $row) {
                if (isset($_GET['cidade']) && $_GET['cidade'] == $row['id'])
                    echo '<option selected data-cidade="' . $row['cidade'] . '" value="' . $row['id'] . '">' . $row['cidade'] . '</option>';
                else
                    echo '<option data-cidade="' . $row['cidade'] . '" value="' . $row['id'] . '">' . $row['cidade'] . '</option>';
            }
        }
    }

    private function orderAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();
        $this->ordenarRegistros($_POST["array"], $this->table);
    }

    private function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();

        $id = $this->getParameter("id");

        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");

        $fotos = $this->DB_fetch_array("SELECT C.id_carrinho_produto_historico, C.id_atributo, C.arquivo FROM tb_pedidos_pedidos A INNER JOIN tb_carrinho_produtos_historico B ON B.id_pedido = A.id INNER JOIN tb_carrinho_atributos_historico C ON C.id_carrinho_produto_historico = B.id WHERE A.id = $id");
        if ($fotos->num_rows) {
            foreach ($fotos->rows as $foto) {
                if ($foto['arquivo'])
                    $this->deleteFile('tb_carrinho_atributos_historico', "arquivo", "id_carrinho_produto_historico = {$foto['id_carrinho_produto_historico']} AND id_atributo = {$foto['id_atributo']}");
            }
        }


        $this->inserirRelatorio("Apagou pedido id: [$id] cliente id [{$dados->rows[0]['id_cliente']}");
        $this->DB_delete('tb_carrinho_produtos_historico', "id_pedido = $id");
        $this->DB_delete($this->table, "id=$id");

        echo $this->getModule();
    }

    private function saveAction() {
        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormulario($formulario);
        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {
            $resposta = new \stdClass();

            //$data = $this->formularioObjeto($_POST, $this->table);

            $data = new \stdClass();

            if ($formulario->id == "") {
                //criar
                $this->noPermission(true);
            } else {
                //alterar
                if (!$this->permissions[$this->permissao_ref]['editar'])
                    exit();

                $data->prazo_entrega = $this->formataDataDeMascara($_POST['prazo_entrega']);
                $data->id_status = $_POST['id_status'];

                $data->entrega_transportadora = $_POST['entrega_transportadora'];
                $data->entrega_cotacao = $_POST['entrega_cotacao'];
                $data->entrega_coleta = $_POST['entrega_coleta'];
                $data->entrega_valor = $_POST['entrega_valor'];
                $data->entrega_dia_coleta = $_POST['entrega_dia_coleta'];
                $data->observacoes_gerais = $_POST['observacoes_gerais'];
                $data->usuario_editando_pedido = 0;

                /*
                  $data->descontos = $this->formataMoedaBd($data->descontos);
                  $data->subtotal = $this->formataMoedaBd($data->subtotal);
                  $data->valor_final = $this->formataMoedaBd($data->valor_final);
                 * 
                 */
                //VERIFICA SE TROCOU O VENDEDOR
                if(isset($_POST['id_vendedor']) && $_POST['id_vendedor'] != $_POST['id_vendedor_atual']){
                    if($_POST['agendor'] == 1 || $_POST['agendor'] == 2){
                        $data->agendor = 2; // AGENDOR SETOU VENDEDOR
                    }else{
                        $data->agendor = 0;
                    }
                    $data->id_vendedor = $_POST['id_vendedor'];
                }
 
                foreach ($data as $key => $value) {
                    $fields_values[] = "$key='$value'";
                }


                $query = $this->DB_update($this->table, implode(',', $fields_values) . " WHERE id=" . $_POST['id']);
                if ($query) {
                    unset($fields_values);

                    $end = $this->formularioObjeto($_POST, "tb_pedidos_enderecos");

                    foreach ($end as $key => $value) {
                        $fields_values[] = "$key='$value'";
                    }

                    $query = $this->DB_update('tb_pedidos_enderecos', implode(',', $fields_values) . " WHERE id_pedido=" . $_POST['id']);

                    $this->DB_connect();
                    $this->mysqli->query("DELETE s.* FROM tb_pedidos_rastreios s
                        INNER JOIN tb_pedidos_has_tb_rastreios n ON s.id = n.id_rastreio
                        WHERE (n.id_pedido = {$_POST['id']})"
                    );
                    if (isset($_POST['link'])) {
                        for ($i = 0; $i < count($_POST['link']); $i++) {
                            if ($_POST['link'][$i] != "") {
                                $insert = $this->DB_insert("tb_pedidos_rastreios", "descricao,link,stats", "'{$_POST['descricao'][$i]}', '{$_POST['link'][$i]}', '{$_POST['rastreio_stats'][$i]}'");
                                if ($insert->query) {
                                    $this->DB_insert("tb_pedidos_has_tb_rastreios", "id_pedido,id_rastreio", "{$_POST['id']}, $insert->insert_id");
                                }
                            }
                        }
                    }

                    $status = $this->DB_fetch_array("SELECT * FROM tb_pedidos_status WHERE id = {$_POST['id_status']}");
                    if ($_POST['id_status'] != $_POST['id_status_original']) {
                        $this->DB_insert("tb_pedidos_historicos", "id_pedido, status, usuario", "{$_POST['id']},'{$status->rows[0]['id']} - {$status->rows[0]['nome']}','{$_SESSION['admin_id']} - {$_SESSION['admin_nome']}'");

                        if($_POST['id_status'] == 12){ //se status entregue, atualiza data da entrega
                            $this->DB_update($this->table,'entregue = NOW() WHERE id='.$_POST['id'].' AND entregue IS NULL');
                        }

                        $clientes = $this->DB_fetch_array("SELECT * FROM tb_clientes_clientes WHERE id = $formulario->id_cliente");
                        $notificar = $this->DB_fetch_array("SELECT * FROM tb_pedidos_status WHERE id = $data->id_status AND enviar_email = 1");
                        if ($notificar->num_rows) {
                            $notifica = $notificar->rows[0];
                            $destinos = $this->DB_fetch_array("SELECT IFNULL(B.nome, B.usuario) nome, email FROM tb_pedidos_status_has_users_notification A INNER JOIN tb_admin_users B ON B.id = A.id_usuario WHERE A.id_pedido_status = {$notifica['id']} AND B.stats = 1");
                            if ($destinos->num_rows) {

                                $cliente = $clientes->rows[0];

                                foreach ($destinos->rows as $destino) {
                                    $to[] = array("email" => $destino['email'], "nome" => utf8_decode($destino['nome']));
                                }

                                if($_POST['id_vendedor'] != '' && $_POST['id_vendedor'] != 0){
                                    $vendedor = $this->DB_fetch_array("SELECT * FROM tb_admin_users WHERE id = ".$_POST['id_vendedor']." AND stats = 1");
                                    if($vendedor->num_rows){
                                        $setFrom = array(array('email'=>$vendedor->rows[0]['email'], 'nome'=>'Real Poker'));
                                        if($notifica['notificar_vendedor']==1){
                                            $to[] = array("email" => $vendedor->rows[0]['email'], "nome" => utf8_decode($vendedor->rows[0]['nome']));
                                        }
                                    }
                                }

                                $to[] = array("email" => $cliente['email'], "nome" => utf8_decode($cliente['nome']));

                                $assunto = $notifica['assunto'];
                                $mensagem = $notifica['mensagem'];
                                
                                $mensagem = $this->trataTextoNotificar($mensagem);

                                $assunto = str_replace("[({NOME})]", $cliente['nome'], $assunto);
                                $assunto = str_replace("[({ID})]", $formulario->id, $assunto);
                                $mensagem = str_replace("[({NOME})]", $cliente['nome'], $mensagem);
                                $mensagem = str_replace("[({ID})]", $formulario->id, $mensagem);

                                $this->enviarEmail($to, $setFrom, utf8_decode($assunto), utf8_decode($mensagem),'','X-MC-Tags: Status Pedido '.$_POST['id_status']);
                                
                            }
                        }
                    }

                    $resposta->type = "success";
                    $resposta->message = "Registro alterado com sucesso!";
                    $this->inserirRelatorio("Alterou pedido: [" . $formulario->cliente . "] nº pedido: [{$_POST['id']}]");
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            }

            echo json_encode($resposta);
        }
    }

    private function sendNotificationsAction() {
        if (!$this->permissions[$this->permissao_ref]['gravar'])
            exit();


        $to = array();
        $send = false;

        parse_str($_POST['data'], $data);

        if (!isset($data['alvo_notificacao'])) {
            echo json_encode(Array('status' => $send, 'message' => "Selecione Financeiro ou Fábrica!"));
            exit();
        }

        if (isset($data['email_notificacao'])) {

            if(isset($data['id_vendedor']) && $data['id_vendedor'] != '' && $data['id_vendedor'] != 0){
                $vendedor = $this->DB_fetch_array('SELECT * FROM tb_admin_users WHERE id = '.$data['id_vendedor']);
                if($vendedor->num_rows){
                    $setFrom = array(array('email'=>$vendedor->rows[0]['email'], 'nome'=>'Real Poker'));
                }
            }else{
                $setFrom = '';
            }

            for ($i = 0; $i < count($data['email_notificacao']); $i++) {

                if ($data['email_notificacao'][$i] != "" && isset($data['alvo_notificacao'][$i][0]) && $data['alvo_notificacao'][$i][0] == "financeiro") {
                    $assunto_financeiro = "Pedido n.: {$data['id']} - Cliente: {$data['cliente']}" . " - Financeiro - " . $this->_empresa['nome'];


                    $pdts = "";
                    if (isset($data['produtos'][$i])) {
                        $separador = "";
                        for ($f = 0; $f < count($data['produtos'][$i]); $f++) {
                            $pdts .= $separador . $data['produtos'][$i][$f];
                            $separador = ",";
                        }
                    }

                    $link = $this->root_path . "script/pedido/pdf/?pedido=" . $this->embaralhar($data['codigo'] . "|" . $pdts);


                    $mensagem_financeiro = "Olá, [{NOME}].<br><br>Acesse o pedido clicando <a target='_blank' href='$link'>aqui</a>!<br><br>Financeiro";

                    $this->DB_insert('tb_pedidos_emails_historicos', 'id_pedido,alvo,nome,email,link,usuario', "
                        '{$data['id']}',
                        'financeiro',
                        '{$data['nome_notificacao'][$i]}',
                        '{$data['email_notificacao'][$i]}',
                        '$link',
                        '{$_SESSION['admin_id']} - {$_SESSION['admin_nome']}'
                        ");



                    $to[] = array("email" => $data['email_notificacao'][$i], "nome" => utf8_decode($data['nome_notificacao'][$i]));

                    $assunto = $assunto_financeiro;

                    $body = $mensagem_financeiro;


                    $body = str_replace("[{NOME}]", $data['nome_notificacao'][$i], $body);

                    $send = $this->enviarEmail($to, $setFrom, utf8_decode($assunto), utf8_decode($body));

                    unset($to);
                } 
                
                if ($data['email_notificacao'][$i] != "" && isset($data['alvo_notificacao'][$i][0]) && $data['alvo_notificacao'][$i][0] == "fabrica") {
                    $assunto_fabrica = "Pedido n.: {$data['id']} - Cliente: {$data['cliente']}" . " - Fábrica - " . $this->_empresa['nome'];

                    $pdts = "";
                    if (isset($data['produtos'][$i])) {
                        $separador = "";
                        for ($f = 0; $f < count($data['produtos'][$i]); $f++) {
                            $pdts .= $separador . $data['produtos'][$i][$f];
                            $separador = ",";
                        }
                    }

                    $link = $this->root_path . "script/pedido/pdf/?pedido=" . $this->embaralhar($data['codigo'] . "|" . $pdts . "|2");

                    $mensagem_fabrica = "Olá, [{NOME}].<br><br>Acesse o pedido clicando <a target='_blank' href='$link'>aqui</a>!<br><br>Fábrica";

                    $this->DB_insert('tb_pedidos_emails_historicos', 'id_pedido,alvo,nome,email,link,usuario', "
                        '{$data['id']}',
                        'fábrica',
                        '{$data['nome_notificacao'][$i]}',
                        '{$data['email_notificacao'][$i]}',
                        '$link',
                        '{$_SESSION['admin_id']} - {$_SESSION['admin_nome']}'
                        ");


                    $to[] = array("email" => $data['email_notificacao'][$i], "nome" => utf8_decode($data['nome_notificacao'][$i]));

                    $assunto = $assunto_fabrica;

                    $body = $mensagem_fabrica;


                    $body = str_replace("[{NOME}]", $data['nome_notificacao'][$i], $body);

                    $send = $this->enviarEmail($to, $setFrom, utf8_decode($assunto), utf8_decode($body));

                    unset($to);
                } 
                
                if ($data['email_notificacao'][$i] != "" && isset($data['alvo_notificacao'][$i][1]) && $data['alvo_notificacao'][$i][1] == "financeiro") {
                    $assunto_financeiro = "Pedido n.: {$data['id']} - Cliente: {$data['cliente']}" . " - Financeiro - " . $this->_empresa['nome'];


                    $pdts = "";
                    if (isset($data['produtos'][$i])) {
                        $separador = "";
                        for ($f = 0; $f < count($data['produtos'][$i]); $f++) {
                            $pdts .= $separador . $data['produtos'][$i][$f];
                            $separador = ",";
                        }
                    }

                    $link = $this->root_path . "script/pedido/pdf/?pedido=" . $this->embaralhar($data['codigo'] . "|" . $pdts);


                    $mensagem_financeiro = "Olá, [{NOME}].<br><br>Acesse o pedido clicando <a target='_blank' href='$link'>aqui</a>!<br><br>Financeiro";

                    $this->DB_insert('tb_pedidos_emails_historicos', 'id_pedido,alvo,nome,email,link,usuario', "
                        '{$data['id']}',
                        'financeiro',
                        '{$data['nome_notificacao'][$i]}',
                        '{$data['email_notificacao'][$i]}',
                        '$link',
                        '{$_SESSION['admin_id']} - {$_SESSION['admin_nome']}'
                        ");


                    $to[] = array("email" => $data['email_notificacao'][$i], "nome" => utf8_decode($data['nome_notificacao'][$i]));

                    $assunto = $assunto_financeiro;

                    $body = $mensagem_financeiro;


                    $body = str_replace("[{NOME}]", $data['nome_notificacao'][$i], $body);

                    $send = $this->enviarEmail($to, $setFrom, utf8_decode($assunto), utf8_decode($body));

                    unset($to);
                } 
                
                if ($data['email_notificacao'][$i] != "" && isset($data['alvo_notificacao'][$i][1]) && $data['alvo_notificacao'][$i][1] == "fabrica") {
                    $assunto_fabrica = "Pedido n.: {$data['id']} - Cliente: {$data['cliente']}" . " - Fábrica - " . $this->_empresa['nome'];

                    $pdts = "";
                    if (isset($data['produtos'][$i])) {
                        $separador = "";
                        for ($f = 0; $f < count($data['produtos'][$i]); $f++) {
                            $pdts .= $separador . $data['produtos'][$i][$f];
                            $separador = ",";
                        }
                    }

                    $link = $this->root_path . "script/pedido/pdf/?pedido=" . $this->embaralhar($data['codigo'] . "|" . $pdts . "|2");

                    $mensagem_fabrica = "Olá, [{NOME}].<br><br>Acesse o pedido clicando <a target='_blank' href='$link'>aqui</a>!<br><br>Fábrica";

                    $this->DB_insert('tb_pedidos_emails_historicos', 'id_pedido,alvo,nome,email,link,usuario', "
                        '{$data['id']}',
                        'fábrica',
                        '{$data['nome_notificacao'][$i]}',
                        '{$data['email_notificacao'][$i]}',
                        '$link',
                        '{$_SESSION['admin_id']} - {$_SESSION['admin_nome']}'
                        ");

                    $to[] = array("email" => $data['email_notificacao'][$i], "nome" => utf8_decode($data['nome_notificacao'][$i]));

                    $assunto = $assunto_fabrica;

                    $body = $mensagem_fabrica;


                    $body = str_replace("[{NOME}]", $data['nome_notificacao'][$i], $body);

                    $send = $this->enviarEmail($to, $setFrom, utf8_decode($assunto), utf8_decode($body));

                    unset($to);
                }
            }
        }



        if ($send) {
            $mensagem = "E-mails enviados com sucesso!";
        } else {
            $mensagem = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
        }

        echo json_encode(Array('status' => $send, 'message' => $mensagem));
    }

    private function mountXmlAction() {
        $resposta = new \stdClass();

        $url = 'https://bling.com.br/Api/v2/notafiscal/json/';
        $xml = '<?xml version="1.0" encoding="UTF-8"?><pedido>';

        $query = $this->DB_fetch_object("SELECT A.* FROM tb_pedidos_pedidos A WHERE A.id = {$_POST['id']}");
        $pedido = $query->rows[0];

        $query = $this->DB_fetch_object("SELECT B.* FROM tb_pedidos_pedidos A INNER JOIN tb_clientes_clientes B ON B.id = A.id_cliente WHERE A.id = {$_POST['id']}");
        $cliente = $query->rows[0];

        $query = $this->DB_fetch_object("SELECT A.*, C.cidade, E.estado, E.uf FROM tb_pedidos_enderecos A INNER JOIN tb_utils_cidades C ON C.id = A.id_cidade INNER JOIN tb_utils_estados E ON E.id = C.id_estado WHERE A.id_pedido = {$_POST['id']}");
        $endereco = $query->rows[0];

        if ($cliente->pessoa == 1) {
            $pessoa = "F";
            $cpf = $this->numeros($cliente->cpf);
        } else if ($cliente->pessoa == 2) {
            $pessoa = "J";
            $cpf = $this->numeros($cliente->cnpj);
        }

        $xml .= '
        <cliente>
            <nome>' . (($cliente->pessoa == 1) ? $cliente->nome : $cliente->razao_social) . '</nome>
            <tipoPessoa>' . $pessoa . '</tipoPessoa>
            <cpf_cnpj>' . $cpf . '</cpf_cnpj>
            <ie_rg>' . $this->numeros($cliente->inscricao_estadual) . '</ie_rg>
            <endereco>' . $endereco->endereco . '</endereco>
            <numero>' . $endereco->numero . '</numero>
            <complemento>' . $endereco->complemento . '</complemento>
            <bairro>' . $endereco->bairro . '</bairro>
            <cep>' . $this->formataCep($endereco->cep) . '</cep>
            <cidade>' . $endereco->cidade . '</cidade>
            <uf>' . $endereco->uf . '</uf>
            <fone>' . $cliente->telefone . '</fone>
            <email>' . $cliente->email . '</email>
        </cliente>    
        ';

        $xml .= '<itens>';

        $produtos = $this->product->getCartProductsByPedido($_POST['id']);
        $peso = 0;
        foreach ($produtos->rows as $produto) {
            $peso = $peso + $produto['peso'];
            $xml .= '
            <item>
                <codigo>' . $produto['id'] . '</codigo>
                <descricao>' . $produto['nome_produto'] . '</descricao>
                <un>un</un>
                <qtde>' . $produto['quantidade'] . '</qtde>
                <vlr_unit>' . ($produto['valor_produto'] - $produto['desconto']) . '</vlr_unit>
                <tipo>P</tipo>
                <peso_bruto>' . $produto['peso'] . '</peso_bruto>
                <peso_liq>' . $produto['peso'] . '</peso_liq>
                <class_fiscal>' . $produto['ncm'] . '</class_fiscal>
                <origem>0</origem>                
            </item>
            ';
        }


        $xml .= '</itens>';


        $xml .= '<transporte>';
        $xml .= '<peso_bruto>' . $peso . '</peso_bruto>';
        $xml .= '<peso_liquido>' . $peso . '</peso_liquido>';
        $xml .= '</transporte>';

        $valor_total = ($pedido->subtotal + $pedido->valor_frete) - $pedido->descontos;
        $cupom = $pedido->valor_cupom;
        $avista = 0;

        if($pedido->tipo_cupom == 1){
            $cupom = (($valor_total*$pedido->valor_cupom)/100);
        }

        if ($pedido->avista == 1) {
            $avista = ($valor_total - $cupom) * 5 / 100;
        }


        $xml .= '<vlr_frete>' . $pedido->valor_frete . '</vlr_frete>';
        $xml .= '<vlr_desconto>' . ($cupom+$avista) . '</vlr_desconto>';

        $xml .= '<obs>Forma de Pagamento: ' . $this->paymentType($pedido->metodo_pagamento) . '</obs>';

        $xml .= '</pedido>';


        $posts = array(
            "apikey" => "b7db6bf857787d283e543c378c84719ea63daf64",
            "xml" => rawurlencode($xml)
        );
        $retorno = $this->executeSendFiscalDocument($url, $posts);
        $json = json_decode($retorno);

        if (isset($json->retorno->notasfiscais{0}->notaFiscal->numero)) {

            $numero = $json->retorno->notasfiscais{0}->notaFiscal->numero;
            $codigo_rastreamento = $json->retorno->notasfiscais{0}->notaFiscal->codigos_rastreamento->codigo_rastreamento;
            $idNotaFiscal = $json->retorno->notasfiscais{0}->notaFiscal->idNotaFiscal;

            $fields_values = "";
            if ($codigo_rastreamento != "")
                $fields_values = ",codigo_rastreamento = '$codigo_rastreamento'";

            $query = $this->DB_update("tb_pedidos_pedidos", "n_nota = '$numero', idNotaFiscal = '$idNotaFiscal' $fields_values WHERE id = {$_POST['id']}");

            if ($query) {
                $resposta->numero = $numero;
                $resposta->idNotaFiscal = $idNotaFiscal;
                $resposta->type = "success";
                $resposta->message = "Nota faturada com sucesso!";
                $this->inserirRelatorio("Faturou nota id: [" . $_POST['id'] . "]");
                $this->updateCampanhasConversoes($_POST['id']);
            } else {
                $resposta->type = "error";
                $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
            }
        } else {
            $resposta->type = "error";
            $resposta->message = $json->retorno->erros{0}->erro->msg;
        }

        echo json_encode($resposta);
    }

    private function sendNotaAction() {

        $resposta = new \stdClass();

        $query = $this->DB_fetch_object("SELECT * FROM tb_clientes_clientes WHERE id = {$_POST['id_cliente']}");
        $cliente = $query->rows[0];

        $url = 'https://bling.com.br/Api/v2/notafiscal/json/';
        $posts = array(
            "apikey" => "b7db6bf857787d283e543c378c84719ea63daf64",
            "number" => $_POST['nota'],
            "serie" => 1,
            "sendEmail" => "$cliente->email"
        );
        $retorno = $this->executeSendFiscalDocument($url, $posts);
        $json = json_decode($retorno);

        if (isset($json->retorno->erros[0]->notafiscal->erro) && $json->retorno->erros[0]->notafiscal->erro != "") {
            $resposta->type = "error";
            $resposta->message = $json->retorno->erros[0]->notafiscal->erro;
        } else {
            $resposta->type = "success";
            $resposta->message = "Nota enviada com sucesso!";
            $this->inserirRelatorio("Enviou nota: [" . $_POST['nota'] . "]");
        }

        echo json_encode($resposta);
    }

    private function executeSendFiscalDocument($url, $data) {
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_POST, count($data));
        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, TRUE);
        $response = curl_exec($curl_handle);
        curl_close($curl_handle);
        return $response;
    }

    private function getRastreioTemplateAction() {
        if (!$this->permissions[$this->permissao_ref]['editar'])
            exit();
        $this->renderView($this->getModule(), "rastreio");
    }

    private function getNotificacaoTemplateAction() {
        if (!$this->permissions[$this->permissao_ref]['editar'])
            exit();
        $this->n = $_POST['id'];
        $this->nmore = $_POST['idmore'];
        $this->produtos = $this->product->getCartProductsByPedido($_POST['id_pedido']);
        $this->renderView($this->getModule(), "notificacao");
    }

    private function validaFormulario($form) {

        $resposta = new \stdClass();
        $resposta->return = true;

        if ($form->prazo_entrega == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "prazo_entrega";
            $resposta->return = false;
            return $resposta;
        }
        /*
          else if ($form->subtotal == "") {
          $resposta->type = "validation";
          $resposta->message = "Preencha este campo";
          $resposta->field = "subtotal";
          $resposta->return = false;
          return $resposta;
          } else if ($form->descontos == "") {
          $resposta->type = "validation";
          $resposta->message = "Preencha este campo";
          $resposta->field = "descontos";
          $resposta->return = false;
          return $resposta;
          } else if ($form->valor_final == "") {
          $resposta->type = "validation";
          $resposta->message = "Preencha este campo";
          $resposta->field = "valor_final";
          $resposta->return = false;
          return $resposta;
          } */ else if ($form->id_status == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "id_status";
            $resposta->return = false;
            return $resposta;
        } else {
            return $resposta;
        }
    }

    private function sendObservacaoAction() {
        if (!$this->permissions[$this->permissao_ref]['editar'])
            exit();

        $resposta = new \stdClass;

        $data = $this->formularioObjeto($_POST, 'tb_carrinho_produtos_historico');

        foreach ($data as $key => $value) {
            $fields_values[] = "$key='$value'";
        }

        $query = $this->DB_update('tb_carrinho_produtos_historico', implode(',', $fields_values) . " WHERE id = $data->id");
        if ($query) {
            $resposta->type = "success";
            $resposta->message = "Registro alterado com sucesso!";
            $this->inserirRelatorio("Alterou observação no produo do carrinho: [" . $data->id . "] nº pedido: [{$_POST['id_pedido']}]");
        } else {
            $resposta->type = "error";
            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
        }


        echo json_encode($resposta);
    }

    private function uploadFileAction() {
        $resposta = new \stdClass();
        $resposta->return = true;

        $formulario = $this->formularioObjeto($_POST, "tb_carrinho_produtos_anexos");

        if (is_uploaded_file($_FILES["arquivo"]["tmp_name"])) {

            $upload = $this->uploadFile("arquivo", array("jpg", "jpeg", "gif", "png", "rar", "zip", "pdf", "tar"), '');
            if ($upload->return) {
                $this->arquivo = $upload->file_uploaded;
            }
        }

        if (!isset($upload)) {
            $resposta->type = "attention";
            $resposta->message = "Arquivo não selecionado.";
            $resposta->return = false;
        } else if (isset($upload) && !$upload->return) {
            $resposta->type = "attention";
            $resposta->message = $upload->message;
            $resposta->return = false;
        } else {

            if ($formulario->id_produto_historico != "") {
                if (!$this->permissions[$this->permissao_ref]['editar'])
                    exit();

                $dados = $this->DB_fetch_array("SELECT * FROM tb_carrinho_produtos_anexos WHERE id_produto_historico = $formulario->id_produto_historico");

                if ($dados->num_rows AND $dados->rows[0]['arquivo'] != "" AND $this->arquivo != "") {
                    $this->deleteFile("tb_carrinho_produtos_anexos", "arquivo", "id_produto_historico = $formulario->id_produto_historico", "");
                    $this->DB_delete("tb_carrinho_produtos_anexos", "id_produto_historico = $formulario->id_produto_historico");
                }

                $query = $this->DB_insert("tb_carrinho_produtos_anexos", "id_produto_historico,arquivo", "$formulario->id_produto_historico,'$this->arquivo'");
                if ($query) {
                    $resposta->type = "success";
                    $resposta->message = "Arquivo enviado com sucesso!";
                    $resposta->arquivo = $this->root_path . "uploads/" . $this->arquivo;
                    $resposta->return = false;

                    $this->inserirRelatorio("Alterou anexo de produto do carrinho id [" . $formulario->id_produto_historico . "]");
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            }
        }


        echo json_encode($resposta);
    }

    private function uploadFotoAction() {
        $resposta = new \stdClass();
        $resposta->return = true;

        $formulario = $this->formularioObjeto($_POST, "tb_carrinho_produtos_historico");

        if (is_uploaded_file($_FILES["foto_final"]["tmp_name"])) {

            $upload = $this->uploadFile("foto_final", array("jpg", "jpeg", "png"), $this->crop_sizes);
            if ($upload->return) {
                $this->arquivo = $upload->file_uploaded;
            }
        }

        if (!isset($upload)) {
            $resposta->type = "attention";
            $resposta->message = "Arquivo não selecionado.";
            $resposta->return = false;
        } else if (isset($upload) && !$upload->return) {
            $resposta->type = "attention";
            $resposta->message = $upload->message;
            $resposta->return = false;
        } else {

            if ($formulario->id != "") {
                if (!$this->permissions[$this->permissao_ref]['editar'])
                    exit();

                $dados = $this->DB_fetch_array("SELECT * FROM tb_carrinho_produtos_historico WHERE id = $formulario->id");

                if ($dados->num_rows AND $dados->rows[0]['foto_final'] != "" AND $this->arquivo != "") {
                    $this->deleteFile("tb_carrinho_produtos_historico", "foto_final", "id = $formulario->id", $this->crop_sizes);
                }

                $query = $this->DB_update("tb_carrinho_produtos_historico", "foto_final='".$this->arquivo."' WHERE id=".$formulario->id);
                if ($query) {
                    $resposta->type = "success";
                    $resposta->message = "Arquivo enviado com sucesso!";
                    $resposta->arquivo = $this->root_path . "uploads/" . $this->getImageFileSized($this->arquivo,400,1000);
                    $resposta->return = false;

                    $this->inserirRelatorio("Alterou foto final de produto do carrinho id [" . $formulario->id . "]");
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            }
        }


        echo json_encode($resposta);
    }

    private function delArquivoAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            $this->noPermission();

        $resposta = new \stdClass();

        $id = $_POST['id_produto_historico'];

        $dados = $this->DB_fetch_array("SELECT * FROM tb_carrinho_produtos_anexos WHERE id_produto_historico = $id");

        if ($dados->num_rows AND $dados->rows[0]['arquivo'] != "") {
            $this->deleteFile("tb_carrinho_produtos_anexos", "arquivo", "id_produto_historico=$id", '');
        }

        $query = $this->DB_delete("tb_carrinho_produtos_anexos", "id_produto_historico = $id");
        if ($query) {
            $resposta->type = "success";
            $resposta->message = "Arquivo removido com sucesso!";
            $this->inserirRelatorio("Removeu arquivo de produto do carrinho id: [$id]");
        } else {
            $resposta->type = "error";
            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
        }

        echo json_encode($resposta);
    }
    
    public function updateCampanhasConversoes($idPedido) {
        if (!$this->permissions[$this->permissao_ref]['editar'])
            exit();

        $query = $this->DB_fetch_array("SELECT A.id, C.id id_acesso, C.faturado, C.faturamento, A.valor_final, B.session, DATE(A.data) utm_data FROM tb_pedidos_pedidos A INNER JOIN tb_carrinho_produtos_historico B ON B.id_pedido = A.id INNER JOIN tb_seo_acessos_historicos C ON C.session = B.session WHERE A.id = $idPedido AND C.compra IS NOT NULL AND C.faturado IS NULL GROUP BY A.id");
        if ($query->num_rows) {
            $new_faturado = 1 + $query->rows[0]['faturado'];
            $new_faturamento = $query->rows[0]['valor_final'] + $query->rows[0]['faturamento'];
            $updateSeoHistorico = $this->DB_update('tb_seo_acessos_historicos', "faturado = $new_faturado, faturamento = $new_faturamento WHERE id = {$query->rows[0]['id_acesso']}");
            if ($updateSeoHistorico) {
                $verifica = $this->DB_fetch_array("SELECT * FROM tb_seo_acessos WHERE id = {$query->rows[0]['id_acesso']}");
                if ($verifica->num_rows) {
                    $updateSeo = $this->DB_update('tb_seo_acessos', "faturado = $new_faturado, faturamento = $new_faturamento WHERE id = {$query->rows[0]['id_acesso']}");
                } else {
                    //cria histórico utm
                    $utm = $this->DB_fetch_array("SELECT date,utm,utm_source,utm_medium,utm_term,utm_content,utm_campaign FROM (
                    SELECT DATE(A.date) date, CONCAT('utm_source=',IFNULL(A.utm_source, ''),'&utm_medium=',IFNULL(A.utm_medium, ''),'&utm_term=',IFNULL(A.utm_term, ''),'&utm_content=',IFNULL(A.utm_content, ''),'&utm_campaign=',IFNULL(A.utm_campaign, '')) utm,
                    A.utm_source, A.utm_medium, A.utm_term, A.utm_content, A.utm_campaign, A.session
                    FROM tb_seo_acessos_historicos A WHERE A.session = '{$query->rows[0]['session']}' AND DATE(A.date) = '{$query->rows[0]['utm_data']}'
                    GROUP BY A.session
                    ) tab GROUP BY date, utm");

                    if ($utm->num_rows) {
                        $this->DB_insert('tb_dashboards_utms', 'date,utm_source,utm_medium,utm_term,utm_content,utm_campaign,cadastros,contatos,compras,faturados,faturamentos', "'{$utm->rows[0]['date']}','{$utm->rows[0]['utm_source']}','{$utm->rows[0]['utm_medium']}','{$utm->rows[0]['utm_term']}','{$utm->rows[0]['utm_content']}','{$utm->rows[0]['utm_campaign']}',0,0,0,1,'{$query->rows[0]['valor_final']}'");
                    }
                }
            }
        }
    }

    private function concederEdicao($idPedido){
        $this->DB_update("tb_pedidos_pedidos","usuario_editando_pedido={$_SESSION['admin_id']}, usuario_editando_ultimo_ping=NOW() WHERE id = ".$idPedido);
    }

    private function pingAction(){
        $resposta = new StdClass();
        if(isset($_POST['id'])){
            $this->concederEdicao($_POST['id']);
        }
    }

    private function pongAction(){
        $resposta = new StdClass();
        if(isset($_POST['id'])){
            $registro = $this->DB_fetch_array('SELECT * FROM tb_pedidos_pedidos WHERE id='.$_POST['id']);
            $registro = $registro->rows[0];

            $last_ping = strtotime($registro['usuario_editando_ultimo_ping']);
            $now = strtotime(date("Y-m-d H:i:s"));
            $interval = abs($now - $last_ping);
            if($interval > 60){
                echo 1;
            }else{
                echo 0;
            }
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

    public static function setAction() {
        $sistema = new Bootstrap();

        #encontrar nome da classe pelo nome do arquivo e instanciá-la
        $class = explode(DIRECTORY_SEPARATOR, __FILE__);
        $class = str_replace(".php", "", end($class));
        $instance = new $class();

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
                $sistema->renderView($instance->getModule(), "404");
        } else {
            $sistema->renderView($instance->getModule(), "404");
        }
    }

}
