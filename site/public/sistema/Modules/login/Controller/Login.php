<?php

use System\Core\Bootstrap;
use System\Libs\IpTables;

Login::setAction();

class Login extends Bootstrap {

    public $module = "";
    public $table = "tb_admin_users";

    function __construct() {
        parent::__construct();
    }

    private function indexAction() {

        if (isset($_SESSION['admin_logado']) && $_SESSION['admin_logado'] === true) {
            header("Location: $this->system_path");
            exit();
        } else if (isset($_COOKIE['c_admin_logado']) && isset($_COOKIE['c_admin_id']) && $_COOKIE['c_admin_id'] != "") {

            $verificar = $this->DB_fetch_array("SELECT * FROM tb_admin_users WHERE id = {$_COOKIE['c_admin_id']} AND id_grupo = {$_COOKIE['c_admin_grupo']} AND session = '{$_COOKIE['c_admin_login_session']}' AND session != ''");

            if ($verificar->num_rows) {
                $_SESSION['admin_logado'] = true;
                $_SESSION['admin_nome'] = $_COOKIE['c_admin_nome'];
                $_SESSION['admin_avatar'] = $_COOKIE['c_admin_avatar'];
                $_SESSION['admin_id'] = $_COOKIE['c_admin_id'];
                $_SESSION['admin_grupo'] = $_COOKIE['c_admin_grupo'];
                $this->inserirRelatorio("[Login]");

                if (!isset($_SESSION["login_session"])) {
                    $_SESSION["login_session"] = uniqid();
                }

                $this->DB_update($this->table, "session = '{$_SESSION["login_session"]}' WHERE id=" . $_COOKIE['c_admin_id']);

                $http = "https";
                if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
                    $http = "https";

                header("Location: $http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
                exit();
            } else {
                $this->inserirRelatorio("Tentativa frustrada de logar no sistema, ip: " . $_SERVER['REMOTE_ADDR']);
                $this->renderView($this->getModule(), "index");
                exit();
            }
            
            exit();
        } else {
            $this->renderView($this->getModule(), "index");
        }
    }

    private function entrarAction() {
        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormulario($formulario);

        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {
            $resposta = new \stdClass();

            if ($formulario->recuperar_senha == 1) {
                $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE usuario='" . $formulario->username . "'");
                if ($dados->num_rows) {
                    $code = $this->criptPass($dados->rows[0]['email'] . time());
                    $url = $this->system_path . "login?code=$code";
                    $body = "Seu nome de usuário é: <b>{$dados->rows[0]['usuario']}</b>.<p>Para gerar uma nova senha, clique <a href='$url'>aqui</a>!<br><br>" . $this->_empresa['nome'];
                    if ($this->enviarEmail(array(array("nome" => $dados->rows[0]['nome'], "email" => $dados->rows[0]['email'])), "", utf8_decode("Recuperação de Senha - " . $this->_empresa['nome'] . ""), utf8_decode($body))) {
                        $resposta->time = 5000;
                        $resposta->action = "";
                        $resposta->type = "success";
                        $resposta->message = "{$dados->rows[0]['nome']}, lhe enviamos um e-mail com instruções para gerar uma nova senha!";

                        $query = $this->DB_update($this->table, "code = '$code' WHERE id=" . $dados->rows[0]['id']);
                    } else {
                        $resposta->type = "error";
                        $resposta->message = "Ocorreu um erro, tente novamente mais tarde!";
                    }
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Usuário inexistente!";
                }
                echo json_encode($resposta);
                exit;
            }

            if ($formulario->recuperar_senha == 2) {
                $verifica = $this->DB_fetch_array("SELECT * FROM $this->table WHERE code = '{$_POST['code']}'");
                if ($verifica->num_rows) {
                    $query = $this->DB_update($this->table, "senha = '" . $this->embaralhar($formulario->password) . "', code = '' WHERE code = '{$_POST['code']}'");
                    $resposta->type = "success";
                    $resposta->action = "login";
                    $resposta->message = "Senha alterada com sucesso!";
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Código inválido!";
                }

                echo json_encode($resposta);
                exit;
            }


            $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE usuario='" . $formulario->username . "'");
            if ($dados->num_rows) {
                if ($this->desembaralhar($dados->rows[0]['senha']) == $formulario->password) {
                    if ($dados->rows[0]["stats"] == 1) {
                        $_SESSION['admin_logado'] = true;
                        $_SESSION['admin_nome'] = $dados->rows[0]["nome"];
                        $_SESSION['admin_avatar'] = $this->getImageFileSized($dados->rows[0]["avatar"], 38, 33);
                        $_SESSION['admin_id'] = $dados->rows[0]["id"];
                        $_SESSION['admin_grupo'] = $dados->rows[0]["id_grupo"];


                        $meia_noite = strtotime('today 23:59');
                        $crossdomain = "realpoker.com.br";

                        setcookie("adm_logado", 1, $meia_noite, "/", $crossdomain);
                        setcookie("adm_nome", $dados->rows[0]["nome"], $meia_noite, "/", $crossdomain);
                        setcookie("adm_id", $dados->rows[0]["id"], $meia_noite, "/", $crossdomain);
                        setcookie("adm_grupo", $dados->rows[0]["id_grupo"], $meia_noite, "/", $crossdomain);

                        $resposta->type = "success";
                        $resposta->message = "Seja bem vindo " . $dados->rows[0]["nome"];
                        $resposta->action = $_SESSION['firsturl'];
                        if($_SERVER['HTTP_HOST'] == "realgaming.com.br" || $_SERVER['HTTP_HOST'] == "www.realgaming.com.br" || $_SERVER['HTTP_HOST'] == "local.cassino"){
                            $resposta->action = "cassinodashboard";
                        }
                        $this->inserirRelatorio("[Login]");

                        if (!isset($_SESSION["login_session"])) {
                            $_SESSION["login_session"] = uniqid();
                        }


                        if (isset($formulario->keepLoged)) {
                            setcookie("c_admin_logado", true, time() + (86400 * 30), "/");
                            setcookie("c_admin_nome", $dados->rows[0]["nome"], time() + (86400 * 30), "/");
                            setcookie("c_admin_avatar", $this->getImageFileSized($dados->rows[0]["avatar"], 38, 33), time() + (86400 * 30), "/");
                            setcookie("c_admin_id", $dados->rows[0]["id"], time() + (86400 * 30), "/");
                            setcookie("c_admin_grupo", $dados->rows[0]["id_grupo"], time() + (86400 * 30), "/");
                            setcookie("c_admin_login_session", $_SESSION["login_session"], time() + (86400 * 30), "/");
                        }


                        $this->DB_update($this->table, "session = '{$_SESSION["login_session"]}' WHERE id=" . $dados->rows[0]['id']);
                        $ip = new IpTables($this);
                        $ip->whitelist($_SERVER['REMOTE_ADDR']);
                    } else {
                        $_SESSION['admin_logado'] = false;
                        $resposta->type = "attention";
                        $resposta->message = "Este usuário encontra-se bloqueado";
                        $this->inserirRelatorio("Usuário bloqueado [" . $formulario->username . "] tentou logar no sistema");
                    }
                } else {
                    unset($_SESSION['admin_logado']);
                    $resposta->type = "error";
                    $resposta->message = "Usuário e senha incorretos!";
                    $this->inserirRelatorio("Tentou logar no sistema [Usuário: " . $formulario->username . " senha:" . $formulario->password . "]");
                }
            } else {
                unset($_SESSION['admin_logado']);
                $resposta->type = "error";
                $resposta->message = "Usuário não encontrado!";
                $this->inserirRelatorio("Tentou logar no sistema [Usuário: " . $formulario->username . " senha:" . $formulario->password . "]");
            }

            echo json_encode($resposta);
        }
    }

    private function sairAction() {

        $this->DB_update($this->table, "session = null WHERE id=" . $_SESSION['admin_id']);

        setcookie("c_admin_logado", null, time() + (86400 * 30), "/");
        setcookie("c_admin_nome", null, time() + (86400 * 30), "/");
        setcookie("c_admin_avatar", null, time() + (86400 * 30), "/");
        setcookie("c_admin_id", null, time() + (86400 * 30), "/");
        setcookie("c_admin_grupo", null, time() + (86400 * 30), "/");
        setcookie("c_admin_login_session", null, time() + (86400 * 30), "/");

        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_nome']);
        unset($_SESSION['admin_avatar']);
        unset($_SESSION['admin_logado']);
        unset($_SESSION['admin_grupo']);
        unset($_SESSION['firsturl']);
        unset($_SESSION['login_session']);

        //session_destroy();

        header("Location: $this->system_path");
    }

    private function validaFormulario($form) {

        $resposta = new \stdClass();
        $resposta->return = true;

        if (isset($form->username) && $form->username == "" && $form->recuperar_senha != 2) {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "username";
            $resposta->return = false;
            return $resposta;
        } else if (($form->password == "" && $form->recuperar_senha == 2) || ($form->password == "" && !$form->recuperar_senha)) {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "password";
            $resposta->return = false;
            return $resposta;
        } else {
            return $resposta;
        }
    }

    /*
     * Métodos padrões da classe
     */

    private function setModule($module) {
        $this->module = $module;
    }
    
    public function getModule() {
        return $this->module;
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
