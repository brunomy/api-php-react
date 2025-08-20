<?php

use System\Core\Bootstrap;
use classes\Product;

ProductionOrder::setAction();

class ProductionOrder extends Bootstrap {

    public $module = "";
    public $permissao_ref = "producao-servicos";
    public $table = "tb_producao_ordens";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
            exit;
        }
        $this->getAllPermissions();
        $this->product = new Product();

        $this->module_icon = "icomoon-icon-profile";
        $this->module_link = "productionorder";
        $this->module_title = "Ordem de Produção por Equipe";
        $this->retorno = "productionorder";
    }

    private function indexAction() {

        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->equipes = $this->DB_fetch_array("SELECT * FROM tb_producao_equipes A");

        $this->renderView($this->getModule(), "edit");
    }

    private function printAction() {

        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $date = $this->getParameter("date");

        $query = $this->DB_fetch_array("SELECT A.*, B.id_equipe, C.nome equipe, D.nome funcionario, E.id_pedido FROM tb_producao_ordens A JOIN tb_producao_equipes_has_users_has_servicos B ON A.id_user = B.id_user JOIN tb_producao_equipes C ON B.id_equipe=C.id JOIN tb_admin_users D ON A.id_user=D.id JOIN tb_carrinho_produtos_historico E ON A.id_carrinho_produto=E.id WHERE A.date = '$date' GROUP BY A.id ORDER BY B.id_equipe, B.id_user, A.ordem");
        
        $date = explode("-", $date);
        $this->date = $date[2]."/".$date[1]."/".$date[0];
        $this->registro = array();
        if($query->num_rows){
            $registros = $query->rows;

            $equipes = array();
            $equipe = 0;
            $equipe_index = -1;
            $user = 0;
            $user_index = -1;
            foreach ($registros as $i => $registro) {
                if($equipe!=$registro['id_equipe']){
                    $equipe = $registros[$i]['id_equipe'];
                    $equipe_index++;
                    $equipes[$equipe_index] = new stdClass();
                    $equipes[$equipe_index]->nome=$registro['equipe'];
                    $equipes[$equipe_index]->funcionarios = array();
                    foreach ($registros as $j => $users) {
                        if($user!=$users['id_user']){
                            $user = $users['id_user'];
                            $user_index++;
                            $equipes[$equipe_index]->funcionarios[$user_index] = new stdClass();
                            $equipes[$equipe_index]->funcionarios[$user_index]->nome = $users['funcionario'];
                            $equipes[$equipe_index]->funcionarios[$user_index]->servicos = array();
                        }
                        $temp = new stdClass();
                        $temp->servico = $users['servico'];
                        $temp->tempo = $users['tempo'];
                        $temp->pedido = $users['id_pedido'];
                        $equipes[$equipe_index]->funcionarios[$user_index]->servicos[] = $temp;
                    }
                }
            }

            $this->registro = $equipes;
        }

        $this->renderView($this->getModule(), "imprimir");
    }

    private function editAction() {
        $this->id = $this->getParameter("id");
        if ($this->id == "") {
            //new            
            if (!$this->permissions[$this->permissao_ref]['gravar'])
                $this->noPermission();

            $campos = $this->DB_columns($this->table);
            foreach ($campos as $campo) {
                $this->registro[$campo] = "";
            }

            $id = 0;

        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar'])
                $this->noPermission();

            $query = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $this->id");
            $this->registro = $query->rows[0];

            $id = $this->id;

        }

        $this->users = $this->DB_fetch_array("SELECT A.id, A.stats, A.nome, B.id_equipe FROM tb_admin_users A LEFT JOIN (SELECT * FROM tb_producao_equipes_has_users_has_servicos WHERE id_equipe = $id) B ON A.id=B.id_user WHERE A.id_grupo!=7 GROUP BY A.id");

        $this->renderView($this->getModule(), "edit");
    }

    public function getEquipeAction(){
        $id = $this->getParameter("id");
        $equipe = $this->DB_fetch_array("SELECT A.id_equipe, A.id_user, B.nome FROM tb_producao_equipes_has_users_has_servicos A JOIN tb_admin_users B ON A.id_user=B.id WHERE A.id_equipe = $id AND A.id_servico IS NULL GROUP BY A.id_user");
        echo json_encode($equipe->rows);
    }

    public function setConcluidoAction(){
        $id = $this->getParameter("id");
        $concluido = $this->getParameter("concluido");
        $query = $this->DB_update($this->table, "concluido=".$concluido." WHERE id=" . $id);
    }

    public function getPedidoActionOld(){
        $id = $this->getParameter("id");
        $equipe = $this->getParameter("equipe");
        $user = $this->getParameter("user");
        if($user=="") $user=0;

        $query = $this->DB_fetch_array("SELECT B.nome_conjunto, B.nome_atributo, D.id id_servico, D.nome servico, D.tempo, 
            A.id_pedido, A.id id_produto_pedido, B.id_conjunto_atributo, B.id_atributo, E.id_user servico_atribuido_a_um_usuario, 
            F.concluido nao_concluido, H.concluido, G.id_user usuario_apto
            FROM tb_carrinho_produtos_historico A 
            JOIN tb_carrinho_atributos_historico B ON A.id=B.id_carrinho_produto_historico
            JOIN tb_producao_servicos_has_atributos C ON B.id_atributo=C.id_atributo
            JOIN tb_producao_servicos D ON C.id_servico=D.id
            LEFT JOIN tb_producao_ordens E ON B.id_atributo=E.id_atributo AND A.id=E.id_carrinho_produto AND D.id=E.id_servico
            LEFT JOIN tb_producao_ordens F ON B.id_atributo=F.id_atributo AND A.id=F.id_carrinho_produto AND D.id=F.id_servico AND F.concluido=0
            LEFT JOIN tb_producao_ordens H ON B.id_atributo=H.id_atributo AND A.id=H.id_carrinho_produto AND D.id=H.id_servico AND H.concluido!=0
            LEFT JOIN tb_producao_equipes_has_users_has_servicos G ON C.id_servico=G.id_servico AND G.id_user = $user
            WHERE A.id_pedido=$id 
            GROUP BY id_pedido, id_atributo, nao_concluido, concluido, servico
            ORDER BY id_produto_pedido");

        /*
        MOTIVO DOS MULTIPLOS LEFT JOINS:
        1º LEFT JOIN: saber se existe um usuário vinculado aquele serviço
        2º LEFT JOIN: buscar apenas por serviço que foi marcado como não concluído
        3º LEFT JOIN: buscar apenas por serviço concluído ou nulo.

        Ex1. Se o serviço estiver atribuido a um usuário este campo estará preenchido com o id do usuário.  Dessa forma se já tiver um usuário atribuido ao serviço, o serviço não deve ser disponibilzado novamente, a não ser que:

        Ex2.  Se existir um serviço que tenha sido marcado como não concluído, e não existir mais outros (1 ou 2), isso quer dizer que só existe um marcado como não concluído, dessa forma precisa disponibilizar o serviço novamente para ser executado.
        
        Ex3. Se existir um serviço marcado como não concluído, e também existir qualque outro (1 ou 2), isso quer dizer que apesar de alguém não ter concluído, aquele serviço foi disponibilizado e atribuido para que seja executado novamente, dessa forma não pode ser disponibilizado novamente.
        */

        $count_produtos = 0;
        $id_produto = 0;
        $servicos = array();
        if($query->num_rows){
            foreach ($query->rows as $key => $servico) {
                if($servico['id_produto_pedido'] != $id_produto){
                    $id_produto = $servico['id_produto_pedido'];
                    $count_produtos++;
                }
                if($servico['servico_atribuido_a_um_usuario']=="" || ($servico['nao_concluido']==0 && $servico['concluido']=="")){
                    //preciso fazer isso para contabilizar o # (count_produtos) mesmo quando estiver atribuido a outro usuário.
                    $servico['servico'] = $servico['id_pedido']." - #".$count_produtos." - ".$servico['servico']." (".$servico['tempo']." min.)";
                    $servicos[] = $servico;
                }
            }
        }

        echo json_encode($servicos);
    }
    public function getPedidoAction(){
        $id = $this->getParameter("id");
        $equipe = $this->getParameter("equipe");
        $user = $this->getParameter("user");
        if($user=="") $user=0;

        //BUSCA TODOS OS SERVIÇOS RELACIONADOS COM O PEDIDO E SE TEM ALGUM USUÁRIO ATRIBUIDO A ALGUM SERVIÇO
        $query = $this->DB_fetch_array("SELECT B.nome_conjunto, B.nome_atributo, D.id id_servico, D.nome servico, D.tempo, A.id_pedido, A.id id_carrinho_produto, B.id_conjunto_atributo, B.id_atributo, F.id_user usuario_apto, A.quantidade, G.unidade_calculada
            FROM tb_carrinho_produtos_historico A 
            JOIN tb_carrinho_atributos_historico B ON A.id=B.id_carrinho_produto_historico
            JOIN tb_producao_servicos_has_atributos C ON B.id_atributo=C.id_atributo
            JOIN tb_producao_servicos D ON C.id_servico=D.id
            JOIN tb_producao_equipes_has_users_has_servicos E ON C.id_servico=E.id_servico
            LEFT JOIN tb_producao_equipes_has_users_has_servicos F ON C.id_servico=F.id_servico AND F.id_user = $user
            JOIN tb_produtos_produtos G ON A.id_produto=G.id
            WHERE A.id_pedido=$id AND E.id_equipe=$equipe
            GROUP BY id_carrinho_produto, id_servico, id_atributo
            ORDER BY A.id");

        if(!$query->num_rows) die("Este pedido $id não possui nenhum serviço vinculado");

        $servicos = $query->rows;

        //BUSCA TODAS AS ORDENS DE SERVIÇO DO PEDIDO SELECIONADO
        $query = $this->DB_fetch_array("SELECT A.* FROM tb_producao_ordens A JOIN tb_carrinho_produtos_historico B ON A.id_carrinho_produto=B.id WHERE B.id_pedido=$id");

        $ordens_servico = array();

        if($query->num_rows) $ordens_servico = $query->rows;

        $count_produtos = 0;
        $id_produto = 0;
        $jobs = array();
        $alpha = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');

        foreach ($servicos as $servico) {
            $servico_temp = $servico['servico'];
            ($servico['unidade_calculada']==1) ? $quantidade = $servico['quantidade'] : $quantidade = 1;
            for($i=1;$i<=$quantidade;$i++){

                $disponibilizar=true;

                //VERIFICA SE ESTÁ ATRIBUÍDO À ALGUEM E SE FOI CONCLUÍDO?
                foreach ($ordens_servico as $ordem) {
                    if($i==$ordem['unidade'] && $servico['id_servico']==$ordem['id_servico'] && $servico['id_carrinho_produto']==$ordem['id_carrinho_produto'] && $servico['id_atributo']==$ordem['id_atributo'] && $ordem['concluido']!=0){
                        $disponibilizar=false;
                    }
                }

                //preciso fazer isso para contabilizar o # (count_produtos) mesmo quando estiver atribuido a outro usuário.
                if($servico['id_carrinho_produto'] != $id_produto){
                    $id_produto = $servico['id_carrinho_produto'];
                    $count_produtos++;
                }

                if($disponibilizar){
                    $servico['unidade'] = $i;
                    $servico['servico'] = $servico['id_pedido']." - #".$count_produtos." - (".$alpha[$i-1].") ".$servico_temp." (".$servico['tempo']." min.)";
                    $jobs[] = $servico;
                }
            }
        }

        echo json_encode($jobs);
    }

    public function getFuncionarioAction(){
        $user = $this->getParameter("user");
        $date = $this->getParameter("date");
        $query = $this->DB_fetch_array("SELECT * FROM  tb_producao_ordens p WHERE p.id_user = ".$user." AND p.date = '".$date."' ORDER BY ordem");
        $employee = [];
        if($query->num_rows){
            foreach ($query->rows as $key => $servico) {
                $employee[] = $servico;
            }
        }
        echo json_encode($employee);
    }

    public function saveFuncionarioAction(){
        $user = $this->getParameter("user");
        $date = $this->getParameter("date");

        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id_user = $user AND date='$date'");
        if($dados->num_rows) $dados = $dados->rows;

        if (isset($_POST['funcionarios'])) {
            foreach ($_POST['funcionarios'] as $servico) {
                $status = 2;
                //verifica status de conclusão para manter o mesmo após apagar o antigo
                foreach ($dados as $data) {
                    if(
                        $data['id_carrinho_produto']==$servico['id_carrinho_produto'] && 
                        $data['id_atributo']==$servico['id_atributo'] && 
                        $data['date']==$date
                    ){
                        $status=$data['concluido'];
                    }
                }

                if($servico['id_carrinho_produto']=="") $servico['id_carrinho_produto']="NULL";
                if($servico['id_atributo']=="") $servico['id_atributo']="NULL";
                if($servico['id_servico']=="") $servico['id_servico']="NULL";

                $insertData[] = "(" . $servico['id_servico'].",".$servico['id_carrinho_produto'] . "," . $servico['id_atributo'] . "," . $user .",'" . $servico['servico']."'," . $servico['unidade']."," . $servico['tempo']."," . $servico['ordem'].",'" .$date."',".$status.")";
            }
        }

        $this->DB_connect();
        $this->mysqli->query("DELETE FROM tb_producao_ordens WHERE id_user=".$user." AND date='".$date."'");
        if (isset($insertData))
            echo "INSERT INTO tb_producao_ordens (id_servico,id_carrinho_produto,id_atributo,id_user,servico,unidade,tempo,ordem,date,concluido) VALUES " . implode(',', $insertData);
            $this->mysqli->query("INSERT INTO tb_producao_ordens (id_servico,id_carrinho_produto,id_atributo,id_user,servico,unidade,tempo,ordem,date,concluido) VALUES " . implode(',', $insertData));
        $this->DB_disconnect();
        

    }


    private function saveAction() {

        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormulario($formulario);
        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {
            $resposta = new \stdClass();
            $data = $this->formularioObjeto($_POST, $this->table);

            if (!isset($data->id) || $data->id == "") {
                //criar
                if (!$this->permissions[$this->permissao_ref]['gravar'])
                    exit();

                try {
                    $query = $this->DB_insert($this->table, 'nome', '"'.$data->nome.'"');
                    $insert_id = $query->insert_id;
                    $this->inserirEquipe($_POST['usuarios'],$insert_id);
                    $this->inserirRelatorio("Cadastrou serviço: [" . $data->nome . "]");
                    $resposta->type = "success";
                    $resposta->message = "Registro cadastrado com sucesso!";
                } catch (Exception $e) {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                    $resposta->exception = $e->getMessage();
                }
                
            } else {
                //alterar
                if (!$this->permissions[$this->permissao_ref]['editar'])
                    exit();

                $this->DB_delete("tb_producao_equipes_has_users_has_servicos", "id_servico IS NULL AND id_equipe=" . $data->id);
                $this->inserirEquipe($_POST['usuarios'],$data->id);

                $resposta = new stdClass();
                $resposta->type = "success";
                $resposta->message = "Registro alterado com sucesso!";
                $this->inserirRelatorio("Alterou equipes: id formulário: [" . $data->id . "]");
            }

            echo json_encode($resposta);
        }
    }


    private function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();


        $id = $this->getParameter("id");

        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");

        $this->inserirRelatorio("Apagou Equipe: [" . $dados->rows[0]['nome'] . "] id: [$id]");
        $this->DB_delete("tb_producao_equipes_has_users_has_servicos", "id_equipe=" . $id);
        $this->DB_delete($this->table, "id=$id");

        echo $this->getModule();
    }

    private function inserirEquipe($users,$id) {
        if (isset($users)) {
            foreach ($users as $user) {
                $insertData[] = "(" . $id . "," . $user . ")";
            }
        }
        $this->DB_connect();
        if (isset($insertData))
            $this->mysqli->query("INSERT INTO tb_producao_equipes_has_users_has_servicos (id_equipe,id_user) VALUES " . implode(',', $insertData));
        $this->DB_disconnect();
    }

    public function validaFormulario($form) {

        $resposta = new \stdClass();
        $resposta->return = true;

        if ($form->nome == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "nome";
            $resposta->return = false;
        }

        return $resposta;
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
