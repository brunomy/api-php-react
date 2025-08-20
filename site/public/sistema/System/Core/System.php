<?php

namespace System\Core;

date_default_timezone_set('America/Sao_Paulo');

use System\Libs\SimpleImage;
use System\Libs\MailChimp;
use System\Libs\PHPMailer;
use System\Libs\MobileDetect;
use System\Core\Config;
use Exception;

/**
 * Classe Loader
 * @author	<contato@hibrida.biz>
 * @copyright	Copyright (c) 2015 Híbrida
 * @link	http://www.hibrida.biz
 */
class System extends Config {

    public $mysqli;
    public $db_connected = false;
    //debug
    public $permissions = array();
    public $is_mobile = 0;
    private $_dominios = array();
    //constantes genericas para controllers e etc.
    public $_request = "";
    public $_get = "";
    public $_post = "";
    public $_empresa;
    public $_sem_dados = "";
    public $_seo_table = "";

    function __construct() {

        parent::__construct();

        // -----------------------------------------------------------------------
        // DETECTA SE O DISPOSITIVO É MOBILE
        // --------------------------------------------------------  -------------


        $this->detect = new MobileDetect();


        if ($this->detect->isMobile()) {
            $this->is_mobile = 1;
        }

        // -----------------------------------------------------------------------

        /*
         * CHAMA FUNÇÃO QUE TRATA ENTRADA DE DADOS COM ANTI INJECTION
         */

        if ($_POST) {
            $_POST = $this->segurancaForms($_POST);
        }

        if ($_GET) {
            $_GET = $this->segurancaForms($_GET);
        }

        if ($_REQUEST) {
            $_REQUEST = $this->segurancaForms($_REQUEST);
        }


        $this->_request = $_REQUEST;
        $this->_get = $_GET;
        $this->_post = $_POST;

        // -----------------------------------------------------------------------

        $query = $this->DB_fetch_array("SELECT * FROM tb_admin_empresa WHERE id=1");
        $this->_empresa = $query->rows[0];

        $this->_sem_dados = "Sem dados!";

        $this->_seo_table = "tb_seo_paginas";
    }

    /*
     * TRATA ENTRADA DE DADOS COM ANTI INJECTION
     */
    /*
      public function segurancaForms($inp) {
      if (is_array($inp))
      return array_map(__METHOD__, $inp);

      if (!empty($inp) && is_string($inp)) {
      return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
      }

      return $inp;
      }
     *
     */

    public function segurancaForms($inp) {
        if (is_array($inp))
            return array_map(__METHOD__, $inp);

        if (!empty($inp) && is_string($inp)) {
            return str_replace(array("\0", "\n", "\r", "'", '"', "\x1a"), array('\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
        }

        return $inp;
    }

    //desativado (muito lento)
    private function segurancaForm($array) {

        foreach ($array as $k => $v) {
            if (is_array($v)) {

                foreach ($v as $key => $value) {
                    $resultado[$k] = $this->segurancaForm($array[$k]);
                }
            } else {
                $resultado[$k] = $this->DB_anti_injection($v);
            }
        }

        return $resultado;
    }

    function DB_connect() {

        $this->mysqli = new \mysqli($this->db_host, $this->db_user, $this->db_pwd, $this->db_database);
        mysqli_set_charset($this->mysqli, "utf8");
        if ($this->mysqli->connect_errno) {
            printf("Erro ao conectar ao banco: %s\n", $mysqli->connect_error);
            exit();
        } else {
            $this->db_connected = true;
        }
    }

    function DB_disconnect() {
        $this->mysqli->close();
        $this->db_connected = false;
    }

    function DB_fetch_object($queryin, $origem = "echo") {

        //VERIFICA SE JÁ ESTÁ CONECTADO OU NÃO
        $justonpened = false;
        if (!$this->db_connected)
            $justonpened = true;
        $this->DB_connect();


        $return = new \stdClass();
        if ($query = $this->mysqli->query($queryin)) {
            $return->query = true;
            $return->num_rows = $query->num_rows;
            $i = 0;
            while ($result = $query->fetch_object()) {
                $return->rows[$i] = new \stdClass();
                $return->rows[$i] = $this->trataArray($result, $origem);
                $i++;
            }
            $query->close();
        } else {
            throw new Exception($this->mysqli->error);
            $return->query = false;
        }


        //VERIFICA SE A CONEXÃO FOI ABERTA AGORA PARA DESCONECTAR
        if ($justonpened)
            $this->DB_disconnect();


        return $return;
    }

    function DB_fetch_array($queryin, $origem = "echo") {

        //VERIFICA SE JÁ ESTÁ CONECTADO OU NÃO
        $justonpened = false;
        if (!$this->db_connected)
            $justonpened = true;
        $this->DB_connect();


        $return = new \stdClass();

        if ($query = $this->mysqli->query($queryin)) {
            $return->query = true;
            $return->num_rows = $query->num_rows;
            $i = 0;
            while ($result = $query->fetch_assoc()) {
                $return->rows[$i] = $this->trataArray($result, $origem);
                $i++;
            }
            $query->close();
        } else {
            throw new Exception($this->mysqli->error);
            $return->query = false;
        }


        //VERIFICA SE A CONEXÃO FOI ABERTA AGORA PARA DESCONECTAR
        if ($justonpened)
            $this->DB_disconnect();



        return $return;
    }

    //APLICA STRIPCSLASHES  DE RESULTADOS VINDO DO BANCO
    /*
      public function trataArray($array, $origem = "echo") {
      if ($origem == "echo") {
      if (is_array($array)) {
      $new_array = array();
      foreach ($array as $key => $value) {
      $new_array[$key] = stripcslashes(stripcslashes(stripcslashes(stripcslashes($value))));
      }
      } else if (is_object($array)) {
      $new_array = new \stdClass();

      foreach ($array as $key => $value) {
      $new_array->$key = stripcslashes(stripcslashes(stripcslashes(stripcslashes($value))));
      }
      } else {
      $new_array = $array;
      }
      } else if ($origem == "form") {
      if (is_array($array)) {
      $new_array = array();
      foreach ($array as $key => $value) {
      $new_array[$key] = htmlspecialchars(stripcslashes(stripcslashes($value)));
      }
      } else if (is_object($array)) {
      $new_array = new \stdClass();

      foreach ($array as $key => $value) {
      $new_array->$key = htmlspecialchars(stripcslashes(stripcslashes($value)));
      }
      } else {
      $new_array = $array;
      }
      } else {
      $this->trataArray($array, "echo");
      }

      return $new_array;
      }
     *
     */

    //APLICA STRIPCSLASHES  DE RESULTADOS VINDO DO BANCO
    public function trataArray($array, $origem = "echo") {
        if ($origem == "echo") {
            if (is_array($array)) {
                $new_array = array();
                foreach ($array as $key => $value) {
                    $new_array[$key] = stripslashes($value);
                }
            } else if (is_object($array)) {
                $new_array = new \stdClass();

                foreach ($array as $key => $value) {
                    $new_array->$key = stripslashes($value);
                }
            } else {
                $new_array = $array;
            }
        } else if ($origem == "form") {
            if (is_array($array)) {
                $new_array = array();
                foreach ($array as $key => $value) {
                    $new_array[$key] = htmlspecialchars(stripslashes($value));
                }
            } else if (is_object($array)) {
                $new_array = new \stdClass();

                foreach ($array as $key => $value) {
                    $new_array->$key = htmlspecialchars(stripslashes($value));
                }
            } else {
                $new_array = $array;
            }
        } else {
            $this->trataArray($array, "echo");
        }

        return $new_array;
    }

    function DB_num_rows($query) {

        //VERIFICA SE JÁ ESTÁ CONECTADO OU NÃO
        $justonpened = false;
        if (!$this->db_connected)
            $justonpened = true;
        $this->DB_connect();


        $query = $this->mysqli->query($query);


        //VERIFICA SE A CONEXÃO FOI ABERTA AGORA PARA DESCONECTAR
        if ($justonpened)
            $this->DB_disconnect();


        return $query->num_rows;
    }

    function DB_last_inserted_id() {

        //VERIFICA SE JÁ ESTÁ CONECTADO OU NÃO
        $justonpened = false;
        if (!$this->db_connected)
            $justonpened = true;
        $this->DB_connect();


        $lastid = $this->mysqli->query("SELECT LAST_INSERT_ID()");
        $lastid = $lastid->fetch_row();


        //VERIFICA SE A CONEXÃO FOI ABERTA AGORA PARA DESCONECTAR
        if ($justonpened)
            $this->DB_disconnect();



        return $lastid[0];
    }

    function DB_insert($table, $fields, $values) {

        //VERIFICA SE JÁ ESTÁ CONECTADO OU NÃO
        $justonpened = false;
        if (!$this->db_connected)
            $justonpened = true;
        $this->DB_connect();

        $response = new \stdClass();
        $queryin = "INSERT INTO " . $table . " (" . $fields . ") VALUES(" . $values . ")";

        $response->query = $this->mysqli->query($queryin);

        if (!$response->query) {
            throw new Exception($this->mysqli->error);
        } else {
            $response->insert_id = $this->mysqli->insert_id;
        }


        //VERIFICA SE A CONEXÃO FOI ABERTA AGORA PARA DESCONECTAR
        if ($justonpened)
            $this->DB_disconnect();


        return $response;
    }

    function DB_update($table, $fields_values) {

        //VERIFICA SE JÁ ESTÁ CONECTADO OU NÃO
        $justonpened = false;
        if (!$this->db_connected)
            $justonpened = true;
        $this->DB_connect();


        $query = $this->mysqli->query("UPDATE " . $table . " SET " . $fields_values);


        //VERIFICA SE A CONEXÃO FOI ABERTA AGORA PARA DESCONECTAR
        if ($justonpened)
            $this->DB_disconnect();


        return $query;
    }

    function DB_delete($table, $fields_values = "") {

        //VERIFICA SE JÁ ESTÁ CONECTADO OU NÃO
        $justonpened = false;
        if (!$this->db_connected)
            $justonpened = true;
        $this->DB_connect();


        if ($fields_values == "")
            $query = $this->mysqli->query("DELETE FROM " . $table);
        else
            $query = $this->mysqli->query("DELETE FROM " . $table . " WHERE " . $fields_values);


        //VERIFICA SE A CONEXÃO FOI ABERTA AGORA PARA DESCONECTAR
        if ($justonpened)
            $this->DB_disconnect();



        return $query;
    }

    function DB_anti_injection($value) {

        //VERIFICA SE JÁ ESTÁ CONECTADO OU NÃO
        $justonpened = false;
        if (!$this->db_connected)
            $justonpened = true;
        $this->DB_connect();


        $query = $this->mysqli->real_escape_string($value);

        $query = str_replace(array('\\n', '\\r'), '', $query);


        //VERIFICA SE A CONEXÃO FOI ABERTA AGORA PARA DESCONECTAR
        if ($justonpened)
            $this->DB_disconnect();

        return $query;
    }

    function topPermissionRequest() { //DEPRECATING
        if ($_SESSION['admin_grupo'] == 1) {
            return array('ler' => 1, 'gravar' => 1, 'editar' => 1, 'excluir' => 1);
        } else {
            return array('ler' => 0, 'gravar' => 0, 'editar' => 0, 'excluir' => 0);
        }
    }

    function permissionRequest($ref) { //DEPRECATING
        $query = $this->DB_fetch_array("SELECT IFNULL(C.ler,B.ler) ler, IFNULL(C.gravar,B.gravar) gravar, IFNULL(C.editar,B.editar) editar, IFNULL(C.excluir,B.excluir) excluir FROM tb_admin_funcoes A INNER JOIN tb_admin_permissoes B ON A.id=B.id_funcao LEFT JOIN tb_user_permissoes C ON C.id_funcao = A.id AND C.id_usuario = {$_SESSION['admin_id']} WHERE A.identificador = '$ref' AND B.id_grupo = {$_SESSION['admin_grupo']}");
        if ($query->query) {
            return $query->rows[0];
        } else {
            return "error";
        }
    }

    function getAllPermissions() {
        $query = $this->DB_fetch_array("SELECT A.id, A.nome, A.identificador, IFNULL(C.ler,B.ler) ler, IFNULL(C.gravar,B.gravar) gravar, IFNULL(C.editar,B.editar) editar, IFNULL(C.excluir,B.excluir) excluir FROM tb_admin_funcoes A LEFT JOIN tb_admin_permissoes B ON A.id=B.id_funcao AND B.id_grupo = '{$_SESSION['admin_grupo']}' LEFT JOIN tb_user_permissoes C ON C.id_funcao = A.id AND C.id_usuario = '{$_SESSION['admin_id']}' ORDER BY A.nome");

        if ($query->num_rows) {
            foreach ($query->rows as $permission) {
                $this->permissions[$permission['identificador']] = $permission;
            }
        }
    }

    function topPermission() {
        if ($_SESSION['admin_grupo'] == 1) {
            return 1;
        } else {
            return 0;
        }
    }

    function getActualUploadFolder() {
        $folder = date("Y") . "_" . date("m") . "/";
        if (!file_exists($this->upload_folder . $folder)) {
            mkdir($this->upload_folder . $folder, 0777, true);
        }
        return $folder;
    }

    function getActualFileFormatFolder($format) {

        $format = strtolower($format);

        if ($format == "jpg" || $format == "jpeg" || $format == "gif" || $format == "png") {

            $folder = $this->getActualUploadFolder();
            $folder = $folder . "images/";

            if (!file_exists($this->upload_folder . $folder)) {
                mkdir($this->upload_folder . $folder, 0777, true);
                mkdir($this->upload_folder . $folder . "original/", 0777, true);
                mkdir($this->upload_folder . $folder . "resized/", 0777, true);
            }

            return $folder . "original/";
        } else if ($format == "doc" || $format == "docx" || $format == "pdf") {

            $folder = $this->getActualUploadFolder();
            $folder = $folder . "docs/";

            if (!file_exists($this->upload_folder . $folder)) {
                mkdir($this->upload_folder . $folder, 0777, true);
            }

            return $folder;
        } else {

            $folder = $this->getActualUploadFolder();
            $folder = $folder . "medias/";

            if (!file_exists($this->upload_folder . $folder)) {
                mkdir($this->upload_folder . $folder, 0777, true);
            }

            return $folder;
        }
    }

    //DUPLICAR IMAGENS DO SERVIDOR
    function duplicateFile($field, $accepted_formats, $sizes = "") {
        $return = new \stdClass();

        $file = explode("/", $field);
        $file_original = end($file);
        $file_array = explode(".", end($file));
        $format = end($file_array);
        array_pop($file_array);
        $filename = implode(".", $file_array);

        $folder = $this->getActualFileFormatFolder($format);
        $file_uploaded = $folder . $file_original;

        //VERIFICA SE EXISTE UM ARQUIVO COM O MESMO NOME
        $copy = 0;
        while (file_exists($this->upload_folder . $file_uploaded)) {
            $copy++;
            $file_uploaded = $folder . $filename . "($copy)." . $format;
        }

        if (!copy($this->upload_folder . $field, $this->upload_folder . $file_uploaded)) {
            $return->return = false;
            $return->message = "Não foi possivel copiar o arquivo enviado. Tente outro arquivo!";
        } else {

            $return->return = true;
            $return->filename = $filename;
            $return->folder = $folder;
            $return->file_uploaded = $file_uploaded;

            if ($sizes != "") {
                foreach ($sizes as $size) {

                    $img = new SimpleImage($this->upload_folder . $file_uploaded);
                    $folder_resized = str_replace("original/", "resized/", $folder);

                    if ($copy > 0) {
                        $saveto = $this->upload_folder . $folder_resized . $filename . "($copy)[" . $size['width'] . "x" . $size['height'] . "]." . $format;
                    } else {
                        $saveto = $this->upload_folder . $folder_resized . $filename . "[" . $size['width'] . "x" . $size['height'] . "]." . $format;
                    }

                    if (isset($size["overlaped"])) {

                        $img2 = clone $img;
                        $img2->best_fit($size['width'], $size['height']);

                        $w = $img->get_width();
                        $h = $img->get_height();
                        $img->resize($w*2, $h*2)->blur('gaussian',30);

                        $new_img = new SimpleImage();
                        $new_img->create($size['width'], $size['height']);
                        $new_img->overlay($img,'center');
                        $new_img->overlay($img2,'center')->save($saveto);

                    } else if (isset($size["best_fit"])) {
                        $img->best_fit($size['width'], $size['height'])->save($saveto);
                    } else {

                        $orientation = $img->get_orientation();
                        if ($orientation == "portrait") {
                            $img->fit_to_width($size['width']);
                            // Crop a portion of the image from x1, y1 to x2, y2
                            //echo "0, {$size['height']}, {$size['width']}, {$size['height']}";
                            $img->crop(0, 0, $size['width'], $size['height']);
                            $img->save($saveto);
                        } else {
                            $img->adaptive_resize($size['width'], $size['height'])->save($saveto);
                        }
                    }
                }
            }
        }


        return $return;
    }

    function uploadFile($field, $accepted_formats, $sizes = "") {
        $return = new \stdClass();
        if ($_FILES[$field]["name"] != "") {

            $file = $this->removeAcentos($_FILES[$field]["name"]);
            $file = strtolower($file);
            $file = str_replace(" ", "-", $file);
            $file_array = explode(".", $file);
            $format = end($file_array);
            array_pop($file_array);
            $filename = implode(".", $file_array);


            $flag = false;
            foreach ($accepted_formats as $val) {
                if ($format == $val) {
                    $flag = true;
                    break;
                }
            }

            if ($flag) {

                $folder = $this->getActualFileFormatFolder($format);
                $file_uploaded = $folder . $file;
                $flag = false;

                //VERIFICA SE EXISTE UM ARQUIVO COM O MESMO NOME
                $copy = 0;
                while (file_exists($this->upload_folder . $file_uploaded)) {
                    $copy++;
                    $file_uploaded = $folder . $filename . "($copy)." . $format;
                }


                if (!move_uploaded_file($_FILES[$field]["tmp_name"], $this->upload_folder . $file_uploaded)) {
                    $return->return = false;
                    $return->message = "Não foi possivel copiar o arquivo enviado. Tente outro arquivo!";
                } else {

                    $return->return = true;
                    $return->filename = $filename;
                    $return->folder = $folder;
                    $return->file_uploaded = $file_uploaded;

                    if ($sizes != "") {
                        foreach ($sizes as $size) {

                            $img = new SimpleImage($this->upload_folder . $file_uploaded);
                            $folder_resized = str_replace("original/", "resized/", $folder);

                            if ($copy > 0) {
                                $saveto = $this->upload_folder . $folder_resized . $filename . "($copy)[" . $size['width'] . "x" . $size['height'] . "]." . $format;
                            } else {
                                $saveto = $this->upload_folder . $folder_resized . $filename . "[" . $size['width'] . "x" . $size['height'] . "]." . $format;
                            }

                            if (isset($size["overlaped"])) {

                                $img2 = clone $img;
                                $img2->best_fit($size['width'], $size['height']);

                                $w = $img->get_width();
                                $h = $img->get_height();
                                $img->resize($w*2, $h*2)->blur('gaussian',30);

                                $new_img = new SimpleImage();
                                $new_img->create($size['width'], $size['height']);
                                $new_img->overlay($img,'center');
                                $new_img->overlay($img2,'center')->save($saveto);

                            } else if (isset($size["best_fit"])) {
                                $img->best_fit($size['width'], $size['height'])->save($saveto);
                            } else {

                                $orientation = $img->get_orientation();
                                if ($orientation == "portrait") {
                                    $img->fit_to_width($size['width']);
                                    // Crop a portion of the image from x1, y1 to x2, y2
                                    //echo "0, {$size['height']}, {$size['width']}, {$size['height']}";
                                    $img->crop(0, 0, $size['width'], $size['height']);
                                    $img->save($saveto);
                                } else {
                                    $img->adaptive_resize($size['width'], $size['height'])->save($saveto);
                                }
                            }
                        }
                    }
                }
            } else {
                $return->return = false;
                $return->message = "Formato inesperado";
            }
        } else {
            $return->return = false;
            $return->message = "Não foi possivel reconhecer o arquivo enviado. Tente outro arquivo!";
        }

        return $return;
    }

    function uploadFiles($field, $accepted_formats, $sizes, $i) {
        $return = new \stdClass();

        if ($_FILES[$field]["name"] != "") {

            $file = $this->removeAcentos($_FILES[$field]["name"][$i]);
            $file = strtolower($file);
            $file = str_replace(" ", "-", $file);
            $file_array = explode(".", $file);
            $format = end($file_array);
            array_pop($file_array);
            $filename = implode(".", $file_array);


            $flag = false;
            foreach ($accepted_formats as $val) {
                if ($format == $val) {
                    $flag = true;
                    break;
                }
            }

            if ($flag) {

                $folder = $this->getActualFileFormatFolder($format);
                $file_uploaded = $folder . $file;
                $flag = false;

                //VERIFICA SE EXISTE UM ARQUIVO COM O MESMO NOME
                $copy = 0;
                while (file_exists($this->upload_folder . $file_uploaded)) {
                    $copy++;
                    $file_uploaded = $folder . $filename . "($copy)." . $format;
                }


                if (!move_uploaded_file($_FILES[$field]["tmp_name"][$i], $this->upload_folder . $file_uploaded)) {
                    $return->return = false;
                    $return->message = "Não foi possivel copiar o arquivo enviado. Tente outro arquivo!";
                } else {

                    chmod($this->upload_folder . $file_uploaded, 0644);

                    $return->return = true;
                    $return->filename = $filename;
                    $return->folder = $folder;
                    $return->file_uploaded = $file_uploaded;

                    if ($sizes != "") {
                        foreach ($sizes as $size) {

                            $img = new SimpleImage($this->upload_folder . $file_uploaded);
                            $folder_resized = str_replace("original/", "resized/", $folder);

                            if ($copy > 0) {
                                $saveto = $this->upload_folder . $folder_resized . $filename . "($copy)[" . $size['width'] . "x" . $size['height'] . "]." . $format;
                            } else {
                                $saveto = $this->upload_folder . $folder_resized . $filename . "[" . $size['width'] . "x" . $size['height'] . "]." . $format;
                            }

                            if (isset($size["best_fit"])) {
                                $img->best_fit($size['width'], $size['height'])->save($saveto);
                            } else {

                                $orientation = $img->get_orientation();
                                if ($orientation == "portrait") {
                                    $img->fit_to_width($size['width']);
                                    // Crop a portion of the image from x1, y1 to x2, y2
                                    //echo "0, {$size['height']}, {$size['width']}, {$size['height']}";
                                    $img->crop(0, 0, $size['width'], $size['height']);
                                    $img->save($saveto);
                                } else {
                                    $img->adaptive_resize($size['width'], $size['height'])->save($saveto);
                                }
                            }
                        }
                    }
                }
            } else {
                $return->return = false;
                $return->message = "Formato inesperado";
            }
        } else {
            $return->return = false;
            $return->message = "Não foi possivel reconhecer o arquivo enviado. Tente outro arquivo!";
        }

        return $return;
    }

    function deleteFile($table, $field, $condition, $sizes = "") {
        $query = $this->DB_fetch_array("SELECT $field FROM $table WHERE $condition");
        $file = $query->rows[0][$field];
        if ($file != "") {

            $split = explode(".", $file);
            $format = end($split);
            array_pop($split);
            $pathfile = implode(".", $split);

            if (($format == "jpg" || $format == "jpeg" || $format == "gif" || $format == "png") && $sizes != "") {
                foreach ($sizes as $size) {
                    $file_resized = str_replace("original/", "resized/", $pathfile);
                    if (file_exists($this->upload_folder . $file_resized . "[" . $size['width'] . "x" . $size['height'] . "]." . $format)) {
                        unlink($this->upload_folder . $file_resized . "[" . $size['width'] . "x" . $size['height'] . "]." . $format);
                    }
                }
            }

            if (file_exists($this->upload_folder . $file)) {
                unlink($this->upload_folder . $file);
            }
        }
    }

    function deleteFileResized($table, $field, $condition, $sizes = "") {
        $query = $this->DB_fetch_array("SELECT $field FROM $table WHERE $condition");
        $file = $query->rows[0][$field];
        if ($file != "") {

            $split = explode(".", $file);
            $format = end($split);
            array_pop($split);
            $pathfile = implode(".", $split);

            if (($format == "jpg" || $format == "jpeg" || $format == "gif" || $format == "png") && $sizes != "") {
                foreach ($sizes as $size) {
                    $file_resized = str_replace("original/", "resized/", $pathfile);
                    if (file_exists($this->upload_folder . $file_resized . "[" . $size['width'] . "x" . $size['height'] . "]." . $format)) {
                        unlink($this->upload_folder . $file_resized . "[" . $size['width'] . "x" . $size['height'] . "]." . $format);
                    }
                }
            }
        }
    }

    function getImageFileSized($filepath, $w, $h) {
        $file = str_replace("original/", "resized/", $filepath);
        $file = explode(".", $file);
        $format = end($file);
        $add = "[" . $w . "x" . $h . "]." . $format;
        array_pop($file);
        $file = implode(".", $file);
        return $this->upload_path . $file . $add;
    }

    function replaceRelativePath($content) {
        return str_replace("../files/", $this->root_path . "files/", $content);
    }

    function enviarEmail($to = "", $from = "", $assunto = "", $mensagem = "", $bcc = "", $customHeeader = "") {

        $mail = new PHPMailer();

        $mail->isSMTP();
        $mail->Host = $this->_empresa['email_host'];
        $mail->Port = $this->_empresa['email_port'];
        $mail->SMTPAuth = true;
        $mail->Username = $this->_empresa['email_user'];
        $mail->Password = $this->_empresa['email_password'];

        if ($customHeeader != "") {
            if (is_array($customHeeader)) {
                foreach ($customHeeader as $val) {
                    $mail->addCustomHeader($val);
                    //$this->inserirRelatorio('Mail Custom Header (from array): ' . $val);
                }
            } else {
                $mail->addCustomHeader($customHeeader);
                //$this->inserirRelatorio('Mail Custom Header: ' . $customHeeader);
            }
        }

        if ($from == "") {
            //$this->inserirRelatorio('From is empty!');
            $mail->addReplyTo($this->_empresa['email_padrao'], utf8_decode(utf8_decode("Real Poker")));
            $fromEmail = $this->_empresa['email_padrao'];
        } else {

            if (is_array($from)) {

                //$this->inserirRelatorio('From is array! nome:'.$from[0]['nome'].' ('.$from[0]['email'].')');
                $mail->addReplyTo($from[0]['email'], $from[0]['nome']);
                $fromEmail = $from[0]['email'];
            }else{
                //$this->inserirRelatorio('From is string! ('.$from.')');
                $mail->addReplyTo($from);
                $fromEmail = $from;
            }

        }

        $mail->setFrom($this->_empresa['email_padrao'], utf8_decode("Real Poker"));

        if ($bcc != "") {
            if (is_array($bcc)) {

                foreach ($bcc as $email) {
                    $email['nome'] = utf8_decode($email['nome']);
                    $mail->addBCC($email['email'], utf8_decode("Real Poker"));
                }
            }
        }

        if (is_array($to)) {

            foreach ($to as $email) {
                //hack para replyTo funcionar quando o remetente for igual ao destinatário
                if ($email['email'] == $this->_empresa['email_padrao']) {
                    if ($from != "")
                        if (is_array($from))
                            $mail->setFrom($from[0]['email'],$from[0]['nome']);
                        else
                            $mail->setFrom($from);

                }
                //------------------------------------------------------------------------
                $email['nome'] = utf8_decode($email['nome']);

                $this->inserirRelatorio('Sistema disparou email de: ['.$fromEmail.'] para ['.$email['email'].'] Assunto: '.utf8_encode($assunto));
                $mail->AddAddress($email['email'], utf8_decode("Real Poker"));
            }

            $dev = "";
            if($this->app_env=="development") $dev = " (AMBIENTE TESTE) ";

            $mail->Subject = $dev.$assunto;

            //Read an HTML message body from an external file, convert referenced images to embedded,
            //convert HTML into a basic plain-text alternative body
            $mail->msgHTML($mensagem, dirname(__FILE__));

            return $mail->send();
        }else {

            return 0;
        }
    }

    function inserirRelatorio($atividade) {

        //VERIFICA SE JÁ ESTÁ CONECTADO OU NÃO
        $justonpened = false;
        if (!$this->db_connected)
            $justonpened = true;
        $this->DB_connect();

        if (isset($_SESSION["admin_nome"])) {
            $nome = $_SESSION["admin_nome"];
        } else {
            $nome = "anônimo";
        }

        if (isset($_SESSION['admin_id'])) {
            $idUsuario = $_SESSION['admin_id'];
        } else {
            $idUsuario = "NULL";
        }

        $this->mysqli->query("INSERT INTO tb_admin_logs (usuario, atividade, id_usuario, date) VALUES ('$nome','$atividade', $idUsuario,NOW())");

        //VERIFICA SE A CONEXÃO FOI ABERTA AGORA PARA DESCONECTAR
        if ($justonpened)
            $this->DB_disconnect();
    }

    function alertaCritico($title,$msg){
        $to[] = array("email" => "jfelipesilva@gmail.com", "nome" => "João Felipe");
        $to[] = array("email" => "gabriel@realpoker.com.br", "nome" => "Gabriel Castro");

        $assunto = "[ALERTA CRÍTICO REAL POKER] ".$title;
        $this->enviarEmail($to,"", utf8_decode($assunto), utf8_decode($msg));
        //$this->inserirRelatorio($assunto);

    }

    function ordenarRegistros($array, $table) {

        //VERIFICA SE JÁ ESTÁ CONECTADO OU NÃO
        $justonpened = false;
        if (!$this->db_connected)
            $justonpened = true;
        $this->DB_connect();


        $indice = 0;

        for ($i = 0; $i < count($array); $i++) {
            $indice = ($i + 1);
            echo $table . " ordem = $indice WHERE id = " . $array[$i];
            $this->DB_update($table, "ordem = $indice WHERE id = " . $array[$i]);
        }

        $this->inserirRelatorio("Reordenou tabela: [" . $table . "]");


        //VERIFICA SE A CONEXÃO FOI ABERTA AGORA PARA DESCONECTAR
        if ($justonpened)
            $this->DB_disconnect();
    }

    function inserirMailing($email, $nome, $origem = "") {


        if (!$this->DB_num_rows("SELECT * FROM tb_mailings_mailings WHERE email = '$email'")) {
            $this->DB_insert("tb_mailings_mailings", "data, nome, email, origem", "NOW(), '$nome', '$email', '$origem'");
        }


        $MailChimp = new MailChimp('3fb97268eb8ed082cafc765b94aa101e-us8');
        $result = $MailChimp->call('lists/subscribe', array(
            'id' => '16514f64c7',
            'email' => array('email' => $email),
            'merge_vars' => array('FNAME' => $nome, 'LNAME' => ''),
            'double_optin' => false,
            'update_existing' => true,
            'replace_interests' => false,
            'send_welcome' => false,
        ));
        return $result;
    }

    function validaUrlAmiga($url, $id = 0) {

        //VERIFICA SE JÁ ESTÁ CONECTADO OU NÃO
        $justonpened = false;
        if (!$this->db_connected)
            $justonpened = true;
        $this->DB_connect();


        if ($id == 0) {
            $query = $this->DB_fetch_array("SELECT * FROM tb_seo_paginas WHERE seo_url = '$url'");
            if ($query->num_rows) {
                return false;
            } else {
                return true;
            }
        } else {
            $query = $this->DB_fetch_array("SELECT * FROM tb_seo_paginas WHERE seo_url = '$url' AND id<>" . $id);
            if ($query->num_rows) {
                return false;
            } else {
                return true;
            }
        }


        //VERIFICA SE A CONEXÃO FOI ABERTA AGORA PARA DESCONECTAR
        if ($justonpened)
            $this->DB_disconnect();
    }

    function formataUrlAmiga($url) {

        //limpa espaços no inicio e fim da string
        $url = trim($url);

        //substitui acentos da string
        $url = $this->removeAcentos($url);

        $url = strtolower($url);

        //substitui espacos,barras,etc por ifen
        $url = str_replace(array(" ", "/"), "-", $url);

        //retira virgulas e pontos da string
        $url = str_replace(array(",", ".", ":", "'", '"', "?", "!", "@", "#", "$", "%", "&", "*", "=", "+", "´", "`", ";", "/", "“", "”"), "", $url);

        $url = stripslashes($url);


        return $url;
    }

    function formataBreadcrumbs($url) {

        //limpa espaços no inicio e fim da string
        $url = trim($url);

        //substitui acentos da string
        $url = $this->removeAcentos($url);

        $url = strtolower($url);

        //substitui espacos,barras,etc por ifen
        $url = str_replace(array(" "), "-", $url);

        //retira virgulas e pontos da string
        $url = str_replace(array(",", ".", ":", "'", '"', "?", "!", "@", "#", "$", "%", "&", "*", "=", "+", "´", "`", ";", "“", "”"), "", $url);

        $url = stripslashes($url);

        if (substr($url, -1) != "/")
            $url = $url . "/";


        return $url;
    }

    function validaEmail($email) {

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 0;
        }else{
            
            if($this->verifyDomainOfEmail($email)){
                return 1;
            }else{
                return 0;
            }

        }

    }

    function cleanText($text){
        if (!preg_match("/^[a-zA-Z-' ]*$/",$text)) {
            return false;
        }else{
            return true;
        }
    }

    function checkdate($data) {
        // VERIFICA DE DATA É VÁLIDA (formato de entrada de data brasileiro)

        $data = explode("/", $data);

        return checkdate($data[1], $data[0], $data[2]);
    }

    function checaData($date) {

        $vencimento = explode("-", $date);
        $ano = $vencimento[0];
        $mes = $vencimento[1];
        $dia = $vencimento[2];

        if (!$this->checkdate("$dia/$mes/$ano")) {
            $dia = $dia - 1;
            $vencimento = "$ano-$mes-$dia";
            return $this->checaData($vencimento);
        } else {
            return "$ano-$mes-$dia";
        }
    }

    function checktime($time) {
        // VERIFICA DE HORA É VÁLIDA
        if (strpos($time, ":")) {
            $time = explode(":", $time);
            if ($time[0] < 24 && $time[0] >= 0) {
                if ($time[1] < 60 && $time[1] >= 0) {
                    if (isset($time[2])) {
                        if ($time[2] < 60 && $time[2] >= 0) {
                            return true;
                        } else {
                            return false;
                        }
                    } else {
                        return true;
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            if ($time < 24 && $time > -1) {
                return true;
            } else {
                return false;
            }
        }
    }

    function formataDataDeMascara($data) {

        $data = explode('/', $data);
        $data = $data[2] . '-' . $data[1] . '-' . $data[0];

        return $data;
    }

    function formataDataDeBanco($data) {
        $data = explode('-', $data);
        $data = $data[2] . '/' . $data[1] . '/' . $data[0];

        return $data;
    }

    function removeAcentos($value) {
        $from = "áàãâéêíóôõúüçÁÀÃÂÉÊÍÓÔÕÚÜÇ";
        $to = "aaaaeeiooouucAAAAEEIOOOUUC";

        $keys = array();
        $values = array();
        preg_match_all('/./u', $from, $keys);
        preg_match_all('/./u', $to, $values);
        $mapping = array_combine($keys[0], $values[0]);
        $value = strtr($value, $mapping);

        return $value;
    }

    function validaCPF($cpf) {
        // determina um valor inicial para o digito $d1 e $d2
        // pra manter o respeito ;)
        $d1 = 0;
        $d2 = 0;
        // remove tudo que não seja número
        $cpf = preg_replace("/[^0-9]/", "", $cpf);
        // lista de cpf inválidos que serão ignorados
        $ignore_list = array(
            '00000000000',
            '01234567890',
            '11111111111',
            '22222222222',
            '33333333333',
            '44444444444',
            '55555555555',
            '66666666666',
            '77777777777',
            '88888888888',
            '99999999999'
        );
        // se o tamanho da string for dirente de 11 ou estiver
        // na lista de cpf ignorados já retorna false
        if (strlen($cpf) != 11 || in_array($cpf, $ignore_list)) {
            return false;
        } else {
            // inicia o processo para achar o primeiro
            // número verificador usando os primeiros 9 dígitos
            for ($i = 0; $i < 9; $i++) {
                // inicialmente $d1 vale zero e é somando.
                // O loop passa por todos os 9 dígitos iniciais
                $d1 += $cpf[$i] * (10 - $i);
            }
            // acha o resto da divisão da soma acima por 11
            $r1 = $d1 % 11;
            // se $r1 maior que 1 retorna 11 menos $r1 se não
            // retona o valor zero para $d1
            $d1 = ($r1 > 1) ? (11 - $r1) : 0;
            // inicia o processo para achar o segundo
            // número verificador usando os primeiros 9 dígitos
            for ($i = 0; $i < 9; $i++) {
                // inicialmente $d2 vale zero e é somando.
                // O loop passa por todos os 9 dígitos iniciais
                $d2 += $cpf[$i] * (11 - $i);
            }
            // $r2 será o resto da soma do cpf mais $d1 vezes 2
            // dividido por 11
            $r2 = ($d2 + ($d1 * 2)) % 11;
            // se $r2 mair que 1 retorna 11 menos $r2 se não
            // retorna o valor zeroa para $d2
            $d2 = ($r2 > 1) ? (11 - $r2) : 0;
            // retona true se os dois últimos dígitos do cpf
            // forem igual a concatenação de $d1 e $d2 e se não
            // deve retornar false.
            return (substr($cpf, -2) == $d1 . $d2) ? true : false;
        }
    }

    function validaCNPJ($cnpj) {
        $cnpj = str_pad(str_replace(array('.', '-', '/'), '', $cnpj), 14, '0', STR_PAD_LEFT);
        if (strlen($cnpj) != 14) {
            return false;
        } else if ($cnpj == "00000000000000") {
            return false;
        } else {
            for ($t = 12; $t < 14; $t++) {
                for ($d = 0, $p = $t - 7, $c = 0; $c < $t; $c++) {
                    $d += $cnpj[$c] * $p;
                    $p = ($p < 3) ? 9 : --$p;
                }
                $d = ((10 * $d) % 11) % 10;
                if ($cnpj[$c] != $d) {
                    return false;
                }
            }
            return true;
        }
    }

    function removeImageFromString($string) {
        return preg_replace("/<img[^>]+\>/i", "", $string);
    }

    function getCidades($id) {

        $dados = $this->DB_fetch_array("SELECT * FROM tb_utils_cidades WHERE id_estado='" . $id . "' ORDER BY cidade");

        return $dados->rows;
    }

    function formataMoedaBd($valor = null) {
        if ($valor != null) {
            $valor = str_replace(".", "", $valor);
            $valor = str_replace(",", ".", $valor);
        }
        return $valor;
    }

    function formataMoeda($valor = null) {
        if ($valor != null) {
            $valor = number_format($valor, 2, ',', '');
        }
        return $valor;
    }

    function formataMoedaShow($valor = null) {
        if ($valor != null) {
            $valor = number_format($valor, 2, ',', '.');
        }
        return $valor;
    }

    /*
     * FUNÇÃO PROCURA TAG DENTRO DA STRING, SE ENCONTRAR INSERE UM ARQUIVO
     */

    function insertFileInString($string = null, $array = null) {

        if ($string != null && $array != null) {

            foreach ($array as $key => $value) {
                if (strpos($string, "$key") == true) {
                    ob_start();
                    include("$value");
                    $obj = ob_get_clean();
                    $string = str_replace("$key", $obj, $string);
                }
            }
            ob_end_flush();
        }

        return $this->replaceRelativePath($string);
    }

    /*
     * FUNÇÃO RETORNA SENHA CRIPTOGRAFADA
     */

    public function criptPass($senha = null) {
        $passCript = $this->criptBase;
        if ($senha != null) {
            $senha = sha1($senha . $passCript);
        }
        return $senha;
    }

    /*
     * FUNÇÃO REMOVE VALORRES NULOS DE UM ARRAY E RETORNA O ARRAY INDEXADO
     */

    public function recompoeArray($array = null) {

        if ($array != null) {
            $array = array_filter($array);
            $array = array_values($array);
        }
        return $array;
    }

    /*
     * CRIA PARAMETROS DA URL DANDO EXPLODE EM "/"
     */

    public function getParameter($parametro = null, $objeto = false) {

        //$this->inserirRelatorio('[debug][getParameter('.$parametro.','.$objeto.')][ip:'.$_SERVER['REMOTE_ADDR'].']');

        //trata caracteres especiais da url
        $uri = urldecode($_SERVER["REQUEST_URI"]);
        //$uri = strtolower($uri);
        //cria array com palavras entre barras
        $uri = explode("/", $uri);

        $array = array();
        $count = 1;

        for ($i = 0; $i < count($uri); $i++) {
            $id = $i + 1;
            $count++;

            if (isset($uri[$id]) && $uri[$i]) {
                //$uri[$id] = str_replace(" ", "+", $uri[$id]);
                $array[$uri[$i]] = $this->segurancaForms($uri[$id]);
            }

            //declara url amigável, sendo ela sempre o último do array
            if (isset($uri[$id]) && $count === count($uri))
                $array["url_amiga"] = $this->segurancaForms($uri[$id]);
        }

        //caso seja solicitado um objeto
        if ($objeto == true) {

            $objeto = (object) $array;

            //caso seja solicitado um parametro
            if ($parametro != null) {
                if (isset($objeto->$parametro))
                    return $objeto->$parametro;
                else
                    return null;
            }
            //caso não seja solicitado um parametro
            else
                return $objeto;
        }
        //caso seja solicitado um array
        else {

            //caso seja solicitado um parametro
            if ($parametro != null) {
                if (isset($array[$parametro]))
                    return $array[$parametro];
                else
                    return null;
            }
            //caso não seja solicitado um parametro
            else
                return $array;
        }
    }

    /*
     * TRATA STRING DO GET PARAMETER PARA FORMATAR URL, EXEMPLO: "Aparecida de Goiânia" FICARÁ: "aparecida+de+goiânia"
     */

    public function trataParameter($parametro = null) {

        if ($parametro != null) {
            $parametro = strtr($parametro, "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÜÚÞß", "àáâãäåæçèéêëìíîïðñòóôõö÷øùüúþÿ");
            if (function_exists('mb_convert_case'))
                $parametro = mb_convert_case($parametro, MB_CASE_LOWER, "UTF-8");
            else
                $parametro = strtolower($parametro);
            $parametro = str_replace(" ", "+", $parametro);
        }

        return $parametro;
    }

    /*
     * PASSA TUDO PARA MAIÚSCULO, INCLUSIVE CARACTERES ACENTUADOS
     */

    public function tudoMaiusculo($string = null) {
        if ($string != null) {
            $string = strtoupper(strtr($string, "áéíóúâêôãõàèìòùç", "ÁÉÍÓÚÂÊÔÃÕÀÈÌÒÙÇ"));
        }
        return $string;
    }

    /*
     * CRIA RESUMO COM LIMITES DA CARACTERES
     */

    public function intro($texto, $limite) {
        $red = '';
        $texto = trim($texto);

        if (strlen($texto) > $limite) {
            $red = '...';
        }
        $texto = strip_tags($texto, '<(.*?)>');
        $texto = trim($texto);
        $texto = substr($texto, 0, $limite);
        $texto = trim($texto) . $red;

        return $texto;
    }

    /*
     * RETORNA CAMPOS DA TABELA INFORMADA
     */

    function DB_columns($table = null) {
        $array = array();
        if ($table != null) {
            $query = $this->DB_fetch_array("DESCRIBE $table");
            if ($query->num_rows) {
                $campos = $query->rows;
                foreach ($campos as $campos) {
                    $array[] = $campos["Field"];
                }
            }
        }
        return $array;
    }

    /*
     * CRIA ARRAY PARA ALTERAÇÃO NO BANCO DE DADOS
     */

    function comparaCampos($table = null, $data = null) {
        $array = array();

        if ($table != null && $data != null) {
            $colunas = $this->DB_columns($table);

            foreach ($data as $key => $value) {
                if (in_array($key, $colunas)) {
                    $array[$key] = $value;
                }
            }
        }
        return $array;
    }

    /*
     * CRIA OBJETO COM DADOS VINDO DO FORMULÁRIO (GET OU POST)
     * Obs: caso a tabela seja informada, retorna apenas parametros que coincidem com ela
     */

    function formularioObjeto($array = null, $table = null) {
        $formulario = new \stdClass();
        if ($array != null) {
            foreach ($array as $key => $value) {
                if ($table != null) {
                    if($value != ""){
                        $colunas = $this->DB_columns($table);
                        if (in_array($key, $colunas)) {
                            $formulario->$key = $value;
                        }
                    }
                } else {
                    $formulario->$key = $value;
                }
            }
        }

        return $formulario;
    }

    /*
     * RESTA IDS PARA COMEÇAR A USAR O SISTEMA
     */

    function DB_tablesIdsReset($table = null) {
        if ($table != null) {
            $query = $this->DB_fetch_array("SELECT t.table_name FROM INFORMATION_SCHEMA.TABLES t WHERE t.table_schema = '$table'");

            $reset_auto_increment_tables = "";
            if ($query->num_rows) {
                foreach ($query->rows as $bd) {
                    $reset_auto_increment_tables = " ALTER TABLE {$bd['table_name']} AUTO_INCREMENT = 1 ";
                    $this->DB_connect();
                    $query = $this->mysqli->query($reset_auto_increment_tables);
                    $this->DB_disconnect();
                }
            }
        }
    }

    /*
     * CONVERTE DATETIME USA PARA BRASIL EX: 22/08/2014 17:35:03
     */

    public function datetime($converter) {
        $array_data = explode(" ", $converter);
        $array_dma = explode("-", $array_data[0]);
        $converter = $array_dma[2] . "/" . $array_dma[1] . "/" . $array_dma[0] . " " . $array_data[1];
        return $converter;
    }

    /*
     * GUARDA TODAS ATIVIDADES DO WEBSERVICE
     */

    function relatorioWs($formulario, $cliente, $dados) {
        $justonpened = false;
        if (!$this->db_connected)
            $justonpened = true;
        $this->DB_connect();

        $dados = json_encode($dados);

        $form = json_encode($formulario);

        $this->mysqli->query("INSERT INTO tb_admin_webservice_logs (id_cliente, ip, autenticacao, parametro, requisicao, retorno) VALUES ('" . $cliente->id . "','" . $formulario->ip . "','" . $formulario->autenticacao . "','" . $formulario->parametro . "', '" . $form . "' ,'" . $dados . "')");

        //$log = "Autenticação: $formulario->autenticacao, IP $formulario->ip, Parâmetro: $formulario->parametro";

        if ($justonpened)
            $this->DB_disconnect();

        //return $log;
    }

    /*
     * ATUALIZA Nº DE VISITAS, INFORMAR TABELA E ID_SEO
     */

    public function upViews($table = null, $id = null) {
        if ($table != null && $id != null) {
            $query = $this->DB_fetch_array("SELECT visitas FROM $table WHERE id_seo = $id");
            if ($query->num_rows) {
                $visitas = $query->rows[0]['visitas'];
                $views = $visitas + 1;
                $this->DB_update($table, "visitas = $views WHERE id_seo = $id");
            }
            unset($query);
        }
    }

    public function upViewsBackEnd($tabela = null, $idTarefa = null, $idUser = null) {
        if ($tabela != null && $idTarefa != null && $idUser != null) {
            $query = $this->DB_fetch_array("SELECT visitas FROM $tabela WHERE id_tarefa = $idTarefa AND id_usuario = $idUser");
            if ($query->num_rows) {
                $visitas = $query->rows[0]['visitas'];
                $views = $visitas + 1;
                $this->DB_update($tabela, "visitas = $views WHERE id_tarefa = $idTarefa");
            } else {
                $queryin = "INSERT INTO $tabela (id_tarefa, id_usuario, visitas) VALUES($idTarefa,$idUser,1)";
                $this->DB_connect();
                $this->mysqli->query($queryin);
                $this->DB_disconnect();
            }
            unset($query);
        }
    }

    //RETORNA NOME DO MÊS
    public function getNomeMes($int = null, $abr = null) {
        switch ($int) {
            case 1:
                if ($abr)
                    return "Jan";
                else
                    return "Janeiro";
                break;
            case 2:
                if ($abr)
                    return "Fev";
                else
                    return "Fevereiro";
                break;
            case 3:
                if ($abr)
                    return "Mar";
                else
                    return "Março";
                break;
            case 4:
                if ($abr)
                    return "Abr";
                else
                    return "Abril";
                break;
            case 5:
                if ($abr)
                    return "Mai";
                else
                    return "Maio";
                break;
            case 6:
                if ($abr)
                    return "Jun";
                else
                    return "Junho";
                break;
            case 7:
                if ($abr)
                    return "Jul";
                else
                    return "Julho";
                break;
            case 8:
                if ($abr)
                    return "Ago";
                else
                    return "Agosto";
                break;
            case 9:
                if ($abr)
                    return "Set";
                else
                    return "Setembro";
                break;
            case 10:
                if ($abr)
                    return "Out";
                else
                    return "Outubro";
                break;
            case 11:
                if ($abr)
                    return "Nov";
                else
                    return "Novembro";
                break;
            case 12:
                if ($abr)
                    return "Dez";
                else
                    return "Dezembro";
                break;
            default:
                return "Mês inválido";
                break;
        }
    }

    //CRIPTOGRAFIA PARA CONTEÚDOS IMPORTANTES

    public function embaralhar($plain_text = null) {
        $plain_text .= "\x13";
        $n = strlen($plain_text);
        if ($n % 16)
            $plain_text .= str_repeat("\0", 16 - ($n % 16));
        $i = 0;
        $iv_len = 16;
        $enc_text = '';
        while ($iv_len-- > 0) {
            $enc_text .= chr(mt_rand() & 0xff);
        }
        $iv = substr($this->criptBase ^ $enc_text, 0, 512);
        while ($i < $n) {
            $block = substr($plain_text, $i, 16) ^ pack('H*', md5($iv));
            $enc_text .= $block;
            $iv = substr($block . $iv, 0, 512) ^ $this->criptBase;
            $i += 16;
        }
        return base64_encode($enc_text);
    }

    public function desembaralhar($enc_text = null) {
        $enc_text = base64_decode($enc_text);
        $n = strlen($enc_text);
        $i = 16;
        $plain_text = '';
        $iv = substr($this->criptBase ^ substr($enc_text, 0, 16), 0, 512);
        while ($i < $n) {
            $block = substr($enc_text, $i, 16);
            $plain_text .= $block ^ pack('H*', md5($iv));
            $iv = substr($block . $iv, 0, 512) ^ $this->criptBase;
            $i += 16;
        }
        return preg_replace('/\x13\x00*$/', '', $plain_text);
    }

    function isValidMd5($md5 = ''){
        return preg_match('/^[a-f0-9]{32}$/', $md5);
    }
    

    /*
     * ATUALIZA TABELA DE SEO ACESSOS GENERAL
     */

    public function seoAcessosAtualiza() {


        $diaAnterior = date("Y-m-d", strtotime("-1 day"));

        $dataVerificada = "2000-01-01";
        $verificar = $this->DB_fetch_array("SELECT DATE_FORMAT(data, '%Y-%m-%d') dia FROM tb_dashboards_configuracoes WHERE origem = 'general'");
        if ($verificar->num_rows) {
            if ($verificar->rows[0]['dia'] != "")
                $dataVerificada = $verificar->rows[0]['dia'];
        }

        if ($dataVerificada < $diaAnterior) {
            $paginas = $this->DB_fetch_object("SELECT A.id_seo id_seo, COUNT(A.id_seo) qtd, date(date) date FROM tb_seo_acessos A WHERE DATE(A.date) > '$dataVerificada' AND DATE(A.date) <= '$diaAnterior' GROUP BY DATE(A.date), A.id_seo");
            if ($paginas->num_rows) {
                foreach ($paginas->rows as $row) {
                    $insertData[] = "($row->id_seo, $row->qtd, '$row->date')";
                }

                if (isset($insertData)) {
                    $this->DB_connect();
                    $query = $this->mysqli->query("INSERT INTO tb_dashboards_paginas (id_seo,qtd,date) VALUES " . implode(',', $insertData));
                    $this->DB_disconnect();
                }
                unset($insertData);
            }
            $visitas = $this->DB_fetch_object("SELECT date, COUNT(session) visitas, COUNT(desktop) desktop, COUNT(tablet) tablet, COUNT(mobile) mobile FROM (SELECT session, DATE_FORMAT(date, '%Y-%m-%d') date, CASE dispositivo WHEN 1 THEN COUNT(dispositivo) END desktop, CASE dispositivo WHEN 2 THEN COUNT(dispositivo) END tablet, CASE dispositivo WHEN 3 THEN COUNT(dispositivo) END mobile FROM tb_seo_acessos WHERE DATE(date) > '$dataVerificada' AND DATE(date) <= '$diaAnterior' GROUP BY session) sub GROUP BY date ORDER BY date");
            if ($visitas->num_rows) {
                foreach ($visitas->rows as $row) {
                    $insertData[] = "('$row->date', $row->visitas, $row->desktop, $row->tablet, $row->mobile)";
                }

                if (isset($insertData)) {
                    $this->DB_connect();
                    $query = $this->mysqli->query("INSERT INTO tb_dashboards_visitas (date,visitas,desktop,tablet,mobile) VALUES " . implode(',', $insertData));
                    $this->DB_disconnect();
                }
                unset($insertData);
            }


            $utms = $this->DB_fetch_object("SELECT date,utm,utm_source,utm_medium,utm_term,utm_content,utm_campaign,COUNT(session) visitas, COUNT(cadastro) cadastros, COUNT(contato) contatos, IFNULL(SUM(compra),0) compras, IFNULL(SUM(faturado),0) faturados, IFNULL(SUM(faturamento),0) faturamentos FROM (SELECT DATE(date) date, 
                CONCAT('utm_source=',IFNULL(A.utm_source, ''),'&utm_medium=',IFNULL(A.utm_medium, ''),'&utm_term=',IFNULL(A.utm_term, ''),'&utm_content=',IFNULL(A.utm_content, ''),'&utm_campaign=',IFNULL(A.utm_campaign, '')) utm,
                A.utm_source, A.utm_medium, A.utm_term, A.utm_content, A.utm_campaign,
                A.session, CASE WHEN SUM(A.cadastro) > 0 THEN 1 END cadastro, CASE WHEN SUM(A.contato) > 0 THEN 1 END contato, SUM(A.compra) compra, SUM(A.faturado) faturado, SUM(A.faturamento) faturamento
                FROM tb_seo_acessos A 
                WHERE DATE(A.date) > '$dataVerificada' AND DATE(A.date) <= '$diaAnterior'
                GROUP BY A.session) B GROUP BY date, utm
                ");

            if ($utms->num_rows) {
                foreach ($utms->rows as $row) {
                    $insertData[] = "('$row->date','$row->utm_source','$row->utm_medium','$row->utm_term','$row->utm_content','$row->utm_campaign', $row->visitas, $row->cadastros, $row->contatos, $row->compras, $row->faturados, $row->faturamentos)";
                }
                if (isset($insertData)) {
                    $this->DB_connect();
                    $query = $this->mysqli->query("INSERT INTO tb_dashboards_utms (date,utm_source,utm_medium,utm_term,utm_content,utm_campaign,visitas,cadastros,contatos,compras,faturados,faturamentos) VALUES " . implode(',', $insertData));
                    $this->DB_disconnect();
                }
                unset($insertData);
            }




            if ($paginas->num_rows || $visitas->num_rows || $utms->num_rows) {
                $this->DB_update("tb_dashboards_configuracoes", " data = '$diaAnterior 23:59:59' WHERE origem = 'general'");
                $this->DB_delete("tb_seo_acessos", " DATE_FORMAT(date, '%Y-%m-%d') <= '$diaAnterior' ");
            }
        }
    }

    /*
     * RETORNA ALFANUMERICO
     */

    public function uniqueAlfa($length = 16) {
        $salt = "abcdefghijklmnopqrstuvwxyz0123456789";
        $len = strlen($salt);
        $pass = '';
        mt_srand(10000000 * (double) microtime());
        for ($i = 0; $i < $length; $i++) {
            $pass .= $salt[mt_rand(0, $len - 1)];
        }
        return $pass;
    }

    /*
     * RETORNA NUMERICO
     */

    public function uniqueNumber($length = 16, $tabela = null, $code = null) {
        $salt = "0123456789";
        $len = strlen($salt);
        $pass = '';
        mt_srand(10000000 * (double) microtime());
        for ($i = 0; $i < $length; $i++) {
            $pass .= $salt[mt_rand(0, $len - 1)];
        }

        if ($tabela != "") {
            $existe = $this->DB_fetch_array("SELECT * FROM $tabela WHERE $code = '$pass'");
            if ($existe->num_rows)
                return $this->uniqueNumber($length, $tabela, $code);
        }

        return $pass;
    }

    /*
     * RETORNA APENAS NUMEROS
     */

    public function numeros($strCampo) {
        $strCampo = str_replace(".", "", $strCampo);
        $strCampo = str_replace("-", "", $strCampo);
        $strCampo = str_replace("/", "", $strCampo);
        $strCampo = str_replace("(", "", $strCampo);
        $strCampo = str_replace(")", "", $strCampo);
        $strCampo = str_replace("[", "", $strCampo);
        $strCampo = str_replace("]", "", $strCampo);
        $strCampo = str_replace("{", "", $strCampo);
        $strCampo = str_replace("}", "", $strCampo);
        $strCampo = str_replace(" ", "", $strCampo);
        return $strCampo;
    }

    public function formataCep($cep) {
        $cep = $this->numeros($cep);

        $antes = substr($cep, 0, 2);
        $depois = substr($cep, 2);
        $string = $antes . "." . $depois;
        $antes = substr($string, 0, 6);
        $depois = substr($string, 6);
        $string = $antes . "-" . $depois;

        return $string;
    }

    /**
     * ATUALIZA STATUS E-MAILS E-MAIL MARKETING
     */
    public function updateEmailsOfEmailMarketing() {
        $query = $this->DB_fetch_array("SELECT a.id_email, a.state, b.id_cliente FROM tb_disparos_disparos_has_tb_emails_emails a INNER JOIN tb_disparos_disparos b ON b.id = a.id_disparo WHERE a.state IS NOT NULL");
        if ($query->num_rows) {
            foreach ($query->rows as $email) {
                $this->DB_update("tb_emails_emails_has_tb_cadastros_clientes", "state = '{$email['state']}' WHERE id_email = {$email['id_email']} AND id_cliente = {$email['id_cliente']}");
            }
        }

        $query = $this->DB_fetch_array("SELECT a.id_email, a.state FROM tb_disparos_disparos_has_tb_emails_emails a WHERE state IS NOT NULL AND state != 'sent' AND state != 'soft-bounced'");
        if ($query->num_rows) {
            foreach ($query->rows as $email) {
                $this->DB_update("tb_emails_emails", "state = 'locked-system' WHERE id = {$email['id_email']}");
            }
        }

        $query = $this->DB_fetch_array("SELECT a.id_email, a.state FROM tb_emails_emails_has_tb_cadastros_clientes a WHERE state IS NOT NULL AND state != 'sent' AND state != 'soft-bounced'");
        if ($query->num_rows) {
            foreach ($query->rows as $email) {
                $this->DB_update("tb_emails_emails", "state = 'locked-system' WHERE id = {$email['id_email']}");
            }
        }
    }

    /**
     * VERIFICA SE DOMÍNIO DO E-MAIL EXISTE
     */
    public function verifyDomainOfEmail($EMail) {
        list($User, $Domain) = explode("@", $EMail);

        if (in_array($Domain, $this->_dominios)) {
            return true;
        } else if (@checkdnsrr($Domain, 'MX')) {
            $this->_dominios[] = $Domain;
            return true;
        } else {
            return false;
        }
    }


    /**
     *
     * GERA BACKUP DO BANCO DE DADOS
     */
    public function DB_backup() {

        $dir = "backups"; //diretorio dos backups
        $dir_backups = dirname(dirname(dirname(__FILE__))) . "/" . $dir;


        //APAGA BACKUPS COM MAIS DE 7 DIAS
        $antigos = $this->DB_fetch_array("SELECT *, DATE_FORMAT(data, '%d/%m/%Y') data FROM tb_admin_backups WHERE DATE(data) < DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
        if ($antigos->num_rows) {
            foreach ($antigos->rows as $antigo) {
                unlink(dirname(dirname(dirname(__FILE__))) . "/" . $antigo['arquivo']);
                unlink(dirname(dirname(dirname(__FILE__))) . "/" . str_replace('.php', '.sql', $antigo['arquivo']));
                unlink(dirname(dirname(dirname(__FILE__))) . "/" . str_replace('.php', '.zip', $antigo['arquivo']));
                $this->DB_delete("tb_admin_backups", "id={$antigo['id']}");
                $this->inserirRelatorio("Apagou backup database antigo: [{$antigo['data']}], id: [{$antigo['id']}]");
            }
        }

        //APAGA BACKUPS SQL COM MAIS DE 1 DIA
        $antigos = $this->DB_fetch_array("SELECT *, DATE_FORMAT(data, '%d/%m/%Y') data FROM tb_admin_backups WHERE DATE(data) < DATE(CURDATE())");
        if ($antigos->num_rows) {
            foreach ($antigos->rows as $antigo) {
                $antigo['arquivo'] = str_replace(".php", ".sql", $antigo['arquivo']);
                if (file_exists(dirname(dirname(dirname(__FILE__))) . "/" . $antigo['arquivo'])) {
                    unlink(dirname(dirname(dirname(__FILE__))) . "/" . $antigo['arquivo']);
                    unlink(dirname(dirname(dirname(__FILE__))) . "/" . str_replace('.php', '.sql', $antigo['arquivo']));
                    unlink(dirname(dirname(dirname(__FILE__))) . "/" . str_replace('.php', '.zip', $antigo['arquivo']));
                    $this->inserirRelatorio("Apagou arquivo sql de backups: [{$antigo['arquivo']}]");
                }
            }
        }

        //CRIA DIRETÓRIO SE NÃO EXISTIR
        if (!file_exists("$dir_backups")) {
            mkdir($dir_backups, 0755);
            $abre = fopen("$dir_backups/.htaccess", "w");
            fwrite($abre, "Options -Indexes");
        }

        $arquivo = "db_" . uniqid() . ".php"; //define nome do arquivo

        $abre = fopen("$dir_backups/" . $arquivo, "w"); // nome do arquivo que será salvo o backup

        $sql1 = $this->DB_fetch_array("SHOW TABLES");
        if ($sql1->num_rows) {
            fwrite($abre, "<?php\n/*\n");
            foreach ($sql1->rows as $ver) {
                $tabela = $ver["Tables_in_$this->db_database"];
                $sql2 = $this->DB_fetch_array("SHOW CREATE TABLE $tabela");
                if ($sql2->num_rows) {
                    foreach ($sql2->rows as $ver2) {
                        fwrite($abre, "-- Criando tabela: $tabela\n");
                        $pp = fwrite($abre, "{$ver2['Create Table']}\n\n-- Salva os dados\n");
                        $sql3 = $this->DB_fetch_array("SELECT * FROM $tabela");
                        if ($sql3->num_rows) {
                            foreach ($sql3->rows as $ver3) {
                                $grava = "INSERT INTO $tabela VALUES ('";
                                $grava .= implode("', '", $ver3);
                                $grava .= "');\n";
                                fwrite($abre, $grava);
                            }
                        }
                        fwrite($abre, "\n\n");
                    }
                }
            }
            fwrite($abre, "\n*/\n");
        }
        $finaliza = fclose($abre);

        //SE GERAR BACKUP GRAVA NO BANCO DE DADOS
        if ($finaliza) {
            $this->DB_insert("tb_admin_backups", "arquivo", "'$dir/$arquivo'");
            $this->inserirRelatorio("Gerou backup database: [" . date("d-m-Y") . "], arquivo: [$arquivo]");
        }

        return $finaliza;
    }

    /*
     * IMPRIME VALORES DAS VARIÁVEIS
     */

    public static function printVars($_data, $_type = false) {
        try {
            if ($_type === false) {
                echo "<pre>";
                print_r($_data);
                echo "</pre>";
            }
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    /*
     * RENDERIZA TELA
     */

    public function renderView($module, $view) {
        if (file_exists(__sys_path__."Modules/" . strtolower($module) . "/view/$view.phtml")) {
            require_once __sys_path__."Modules/" . strtolower($module) . "/view/$view.phtml";
        } else if (file_exists(__sys_path__."Modules/em/" . strtolower($module) . "/view/$view.phtml")) {
            require_once __sys_path__."Modules/em/" . strtolower($module) . "/view/$view.phtml";
        } else {
            require_once "layouts/404.phtml";
        }
    }

    /*
     * RENDERIZA AJAX
     */

    public function renderAjax($module, $ajax) {
        if (file_exists(__sys_path__."Modules/" . strtolower($module) . "/ajax/$ajax.phtml")) {
            require_once __sys_path__."Modules/" . strtolower($module) . "/ajax/$ajax.phtml";
        } else if (file_exists(__sys_path__."Modules/em/" . strtolower($module) . "/ajax/$ajax.phtml")) {
            require_once __sys_path__."Modules/em/" . strtolower($module) . "/ajax/$ajax.phtml";
        } else {
            require_once "layouts/404.phtml";
        }
    }

    /*
     * RENDERIZA EXPORTAÇÃO
     */

    public function renderExport($module, $export) {
        if (file_exists(__sys_path__."Modules/" . strtolower($module) . "/export/$export.phtml")) {
            require_once __sys_path__."Modules/" . strtolower($module) . "/export/$export.phtml";
        } else if (file_exists(__sys_path__."Modules/em/" . strtolower($module) . "/export/$export.phtml")) {
            require_once __sys_path__."Modules/em/" . strtolower($module) . "/export/$export.phtml";
        } else {
            require_once "layouts/404.phtml";
        }
    }

    /*
     * MOSTRA MENSAGEM DE PERMISSÃO NEGADA
     */

    public function noPermission($render = true) {
        if ($render === true)
            require_once "layouts/no-permission.phtml";
        else
            require_once "layouts/no-permission-no-render.phtml";
    }

    /*
     * INTERNACIONALIZAÇÃO
     */

    public function translate($variable, $value = null, $lang = "pt-br") {
        $language = Array(
            "pt-br" => Array(
                "metodo_nao_existe" => "Lamento, o método <b>$value</b> não existe!",
                "voltar" => "voltar"
            ),
            "en" => Array(
                "metodo_nao_existe" => "Sorry, the <b>$value</b> method does not exist!",
                "voltar" => "go back"
            ),
        );

        if (isset($language[$lang][$variable]))
            return $language[$lang][$variable];
        else
            return $variable;
    }

    public function file_exists_ci($file) {
        if (file_exists($file))
            return $file;
        $lowerfile = strtolower($file);
        foreach (glob(dirname($file) . '/*') as $file)
            if (strtolower($file) == $lowerfile)
                return $file;
        return false;
    }

    /*
     * FUNÇÃO PARA SER USADO NO FRONT-END, CORRIGE PATH DAS IMAGENS E RETIRA CARACTERES INDEVIDOS DO TEXTO
     */

    public function trataTexto($string = null) {
        if ($string != null) {
            $string = str_replace('src="../', 'src="', $this->stripslashes($string));
        }

        return $string;
    }

    public function trataTextoNotificar($string = null) {
        if ($string != null) {
            $string = str_replace('href="../', 'href="' . $this->root_path, $this->stripslashes($string));
            $string = str_replace('src="../', 'src="' . $this->root_path, $this->stripslashes($string));
        }

        return $string;
    }

    public function stripslashes($string = null) {
        if ($string != null) {
            $string = stripslashes(stripslashes($string));
        }

        return $string;
    }

    /* A PRIORE PARA MOSTRAR SALDOS DO PONTO */
    public function minToHours($minutos){
        $horas = floor($minutos/60);
        /* TEM Q INCREMENTAR QUANDO DER NEGATIVO POIS A FUNCAO FLOOR ARREDONDA PARA BAIXO, ENTAO -2.5 VAI PARA -3, NÃO -2 COMO DESEJADO */
        if($horas < 0)
            $horas++;
        if($horas != 0)
            return $horas." hora".( ($horas == 1) ? "" : "s" )." e ". abs($minutos%60)." minutos";
        else
            return $minutos." minutos";
    }

    public function alterarStatusPedido($idPedido, $status){

        $pedido = $this->DB_fetch_array('SELECT a.*, a.id pedido_id, b.id cliente_id, b.nome cliente_nome, b.email cliente_email, b.telefone cliente_telefone, b.pessoa tipo_pessoa, b.cpf cliente_cpf, b.cnpj cliente_cnpj, b.endereco cliente_endereco, b.numero cliente_numero, b.bairro cliente_bairro, b.complemento cliente_complemento, b.cep cliente_cep FROM tb_pedidos_pedidos a INNER JOIN tb_clientes_clientes b ON a.id_cliente = b.id LEFT JOIN tb_utils_cidades c ON c.id = b.id_cidade LEFT JOIN tb_utils_estados d ON c.id_estado = d.id WHERE a.id = '.$idPedido);

        $pedido = $pedido->rows[0];

        $status_atual = $this->DB_fetch_array("SELECT id_status FROM tb_pedidos_pedidos WHERE id = ".$pedido['pedido_id']);
        $status_atual = $status_atual->rows[0]['id_status'];


        if($status_atual != $status){

            $nome_status = $this->DB_fetch_array('SELECT nome FROM tb_pedidos_status WHERE id = '.$status);
            $nome_status = $nome_status->rows[0]['nome'];

            if(isset($_SESSION['admin_id'])){
                $usuario = $_SESSION['admin_id'].' - '.$_SESSION['admin_nome'];
            }else{
                $usuario = 'SISTEMA';
            }

            if($status == 12){ //se status entregue, atualiza data da entrega
                $this->DB_update('tb_pedidos_pedidos','entregue = NOW() WHERE id='.$idPedido.' AND entregue IS NULL');
            }

            $this->DB_insert("tb_pedidos_historicos", "id_pedido, status, usuario", "{$idPedido},'{$status} - {$nome_status}','{$usuario}'");
            $this->DB_update("tb_pedidos_pedidos", "id_status = $status WHERE id = ".$idPedido);

            $notificar = $this->DB_fetch_array("SELECT * FROM tb_pedidos_status WHERE id = $status AND enviar_email = 1");

            if ($notificar->num_rows) {
                $notifica = $notificar->rows[0];
                $destinos = $this->DB_fetch_array("SELECT IFNULL(B.nome, B.usuario) nome, email FROM tb_pedidos_status_has_users_notification A INNER JOIN tb_admin_users B ON B.id = A.id_usuario WHERE A.id_pedido_status = $status AND B.stats = 1");
                if ($destinos->num_rows) {

                    foreach ($destinos->rows as $destino) {
                        $to[] = array("email" => $destino['email'], "nome" => utf8_decode($destino['nome']));
                    }

                    $setFrom = "";

                    if($pedido['id_vendedor'] != '' && $pedido['id_vendedor'] != 0){
                        $vendedor = $this->DB_fetch_array("SELECT * FROM tb_admin_users WHERE id = ".$pedido['id_vendedor']." AND stats = 1");
                        if($vendedor->num_rows){
                            $setFrom = array(array('email'=>$vendedor->rows[0]['email'], 'nome'=>'Real Poker'));
                            if($notifica['notificar_vendedor']==1){
                                $to[] = array("email" => $vendedor->rows[0]['email'], "nome" => utf8_decode($vendedor->rows[0]['nome']));
                            }
                        }
                    }

                    $to[] = array("email" => $pedido['cliente_email'], "nome" => utf8_decode($pedido['cliente_nome']));

                    $assunto = $notifica['assunto'];
                    $mensagem = $notifica['mensagem'];
                    
                    $mensagem = $this->trataTextoNotificar($mensagem);

                    $assunto = str_replace("[({NOME})]", $pedido['cliente_nome'], $assunto);
                    $assunto = str_replace("[({ID})]", $pedido['pedido_id'], $assunto);
                    $mensagem = str_replace("[({NOME})]", $pedido['cliente_nome'], $mensagem);
                    $mensagem = str_replace("[({ID})]", $pedido['pedido_id'], $mensagem);

                    $this->enviarEmail($to, $setFrom, utf8_decode($assunto), utf8_decode($mensagem),'','X-MC-Tags: Status Pedido '.$status);
                    
                }
            }

        }
    }

}

?>
