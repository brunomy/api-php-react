<?php

use System\Core\Bootstrap;
use System\Libs\ContaAzul;

Product::setAction();

class Product extends Bootstrap {

    public $module = "";
    public $permissao_ref = "produtos-produtos";
    public $table = "tb_produtos_produtos";
    public $table2 = "tb_produtos_fotos";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
        }
        $this->getAllPermissions();

        $this->module_icon = "minia-icon-unchecked";
        $this->module_link = "product";
        $this->module_title = "Produtos";
        $this->retorno = "product";

        $this->crop_sizes = array();

        array_push($this->crop_sizes, array("width" => 75, "height" => 75));
        array_push($this->crop_sizes, array("width" => 150, "height" => 150));
        array_push($this->crop_sizes, array("width" => 1200, "height" => 800, "best_fit" => true));

        $this->imagem_capa = "";

        $this->crop_capa_sizes = array();
        array_push($this->crop_capa_sizes, array("width" => 700, "height" => 385));
        //array_push($this->crop_capa_sizes, array("width" => 730, "height" => 540));
        array_push($this->crop_capa_sizes, array("width" => 790, "height" => 435));
        array_push($this->crop_capa_sizes, array("width" => 455, "height" => 335));

        $this->imagem_atributo = "";

        $this->crop_atributo_sizes = array();
        array_push($this->crop_atributo_sizes, array("width" => 210, "height" => 142));
        array_push($this->crop_atributo_sizes, array("width" => 705, "height" => 530));
        array_push($this->crop_atributo_sizes, array("width" => 1200, "height" => 800, "best_fit" => true));

        $this->imagem_icone = "";

        $this->crop_icone_sizes = array();
        array_push($this->crop_icone_sizes, array("width" => 160, "height" => 160));
    }

    public function getCategoriaNome($id = null) {
        $cat = "";
        if ($id != null) {
            $query = $this->DB_fetch_array("SELECT id_pai, nome FROM tb_produtos_categorias WHERE id = $id");
            if ($query->num_rows) {
                $categorias = $query->rows;

                foreach ($categorias as $categoria) {
                    $cat .= $categoria['nome'] . ' , ';
                    if ($categoria['id_pai']) {
                        $cat .= $this->getCategoriaNome($categoria['id_pai']);
                    }
                }
            }

            $array = explode(",", $cat);
            $array = array_reverse($array);

            $cat = "";

            foreach ($array as $value) {
                $cat .= $value;
            }

            unset($query);

            echo $cat . " / ";
        } else {
            echo $cat;
        }
    }

    private function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->list = $this->DB_fetch_array("SELECT A.* FROM $this->table A WHERE apagado != 1 ORDER BY A.ordem");

        $this->renderView($this->getModule(), "index");
    }

    public function getCategoriasByIdProduto($idProduto) {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $result = "";
        $separador = "";

        $query = $this->DB_fetch_array("SELECT A.* FROM tb_produtos_categorias A INNER JOIN tb_produtos_produtos_has_tb_produtos_categorias B ON B.id_categoria = A.id WHERE B.id_produto = $idProduto ORDER BY A.ordem");
        if ($query->num_rows) {
            foreach ($query->rows as $row) {
                $result .= $separador . $row['nome'];
                $separador = ", ";
            }
        }

        return $result;
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

            $this->seo['seo_title'] = "";
            $this->seo['seo_description'] = "";
            $this->seo['seo_keywords'] = "";
            $this->seo['seo_url'] = "";
            $this->seo['seo_scripts'] = "";
            $this->seo['seo_url_breadcrumbs'] = "";

            $this->conjuntos = new \stdClass();
            $this->conjuntos->num_rows = false;

            $this->categorias = $this->DB_fetch_array("SELECT A.*, B.id_categoria FROM tb_produtos_categorias A LEFT JOIN tb_produtos_produtos_has_tb_produtos_categorias B ON B.id_categoria = A.id AND B.id_produto = 0 ORDER BY A.ordem");
        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar'])
                $this->noPermission();

            $query = $this->DB_fetch_array("SELECT A.* FROM $this->table A  WHERE id = $this->id", "form");
            $this->registro = $query->rows[0];

            $this->fotos = $this->DB_fetch_array("SELECT * FROM $this->table2 WHERE id_produto = $this->id", "form");

            $query = $this->DB_fetch_array("SELECT * FROM tb_seo_paginas WHERE id = " . $this->registro['id_seo'], "form");
            $this->seo = $query->rows[0];

            $this->conjuntos = $this->DB_fetch_array("SELECT * FROM tb_produtos_conjuntos_atributos A WHERE A.id_produto = $this->id ORDER BY A.ordem");

            $this->categorias = $this->DB_fetch_array("SELECT A.*, B.id_categoria FROM tb_produtos_categorias A LEFT JOIN tb_produtos_produtos_has_tb_produtos_categorias B ON B.id_categoria = A.id AND B.id_produto = {$this->registro['id']} ORDER BY A.ordem");
        }

        $this->tipos = $this->DB_fetch_array("SELECT * FROM tb_produtos_atributos_tipos A ORDER BY nome");
        $this->frete_terrestre = $this->DB_fetch_array("SELECT * FROM tb_config_conjuntos_fretes A WHERE A.id_tipo = 1 ORDER BY A.nome");
        $this->frete_aereo = $this->DB_fetch_array("SELECT * FROM tb_config_conjuntos_fretes A WHERE A.id_tipo = 2 ORDER BY A.nome");


        $this->renderView($this->getModule(), "edit");
    }

    private function fotosAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->id = $this->getParameter("id");

        $this->fotos = $this->DB_fetch_array("SELECT * FROM $this->table2 WHERE id_produto = $this->id order by ordem");

        $this->renderAjax($this->getModule(), "fotos");
    }

    private function fotoAction() {
        if (!$this->permissions[$this->permissao_ref]['ler'])
            $this->noPermission(false);

        $this->id = $this->getParameter("id");
        $this->id_produto = $this->getParameter("id_produto");

        $foto = $this->DB_fetch_array("SELECT * FROM $this->table2 WHERE id = $this->id AND id_produto = $this->id_produto");

        $this->foto = $foto->rows[0];

        $this->renderAjax($this->getModule(), "foto");
    }

    private function saveGroupAttributeAction() {
        if (!$this->permissions[$this->permissao_ref]['gravar'])
            $this->noPermission();

        $nome = "";
        $idProduto = "";
        $descricao = "";
        $descricao_aviso_desabilitado = "";

        $resposta = new \stdClass();

        if (isset($_POST['nome_conjunto_atributos']) && $_POST['nome_conjunto_atributos'] != "")
            $nome = $_POST['nome_conjunto_atributos'];

        if (isset($_POST['id_produto']) && $_POST['id_produto'] != "")
            $idProduto = $_POST['id_produto'];

        if (isset($_POST['descricao_aviso_desabilitado']) && $_POST['descricao_aviso_desabilitado'] != "")
            $descricao_aviso_desabilitado = $_POST['descricao_aviso_desabilitado'];

        if (isset($_POST['descricao']) && $_POST['descricao'] != "")
            $descricao = $_POST['descricao'];

        if ($nome != "") {
            $ordem = $this->DB_fetch_array("SELECT MAX(id) ordem FROM tb_produtos_conjuntos_atributos");
            if ($ordem->num_rows) {
                $order = $ordem->rows[0]['ordem'] + 1;
            } else {
                $order = 0;
            }

            $query = $this->DB_insert("tb_produtos_conjuntos_atributos", "id_produto,  nome, descricao,descricao_aviso_desabilitado, ordem", "$idProduto, '$nome', '$descricao','$descricao_aviso_desabilitado', $order");
            if ($query->query) {
                $resposta->type = "success";
                $resposta->message = "Registro cadastrado com sucesso!";
                $resposta->id = $query->insert_id;
                $this->inserirRelatorio("Cadastrou conjunto de atributo: [$nome] produto id: [$idProduto]");
            } else {
                $resposta->type = "error";
                $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
            }
        }

        echo json_encode($resposta);
    }


    private function salvarVideoAction(){
        if (!$this->permissions[$this->permissao_ref]['gravar'])
            $this->noPermission();

            $resposta = new \stdClass();

            $idProduto = $_POST['id_produto'];
            $videoDesktop = $_POST['video_desktop'];
            $videoMobile =  empty($_POST['video_mobile']) ? $videoDesktop : $_POST['video_mobile'];

             $query = $this->DB_update("tb_produtos_produtos", "video_desktop= '" .$videoDesktop . "', video_mobile= '" .$videoMobile . "' WHERE id = $idProduto");
            if ($query) {
                $resposta->type = "success";
                $resposta->message = "Registro atualiado com sucesso!";

                $this->inserirRelatorio("Atualizou o video do produto id: [$idProduto]");
            } else {
                $resposta->type = "error";
                $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
            }

        echo json_encode($resposta);
    }

    private function getTiposConjunto($idConjunto = 0) {
        $comboTipos = "";

        $query = $this->DB_fetch_array("SELECT A.*, B.id_produto FROM tb_produtos_atributos_tipos A LEFT JOIN tb_produtos_conjuntos_atributos B ON B.id_tipo = A.id AND B.id = $idConjunto ORDER BY A.nome");
        if ($query->num_rows) {
            $comboTipos .= '
                <select class="form-control input-sm tipo_atributo_conjunto">
            ';
            foreach ($query->rows as $tipo) {
                if (!$tipo['id_produto'])
                    $comboTipos .= '<option value="' . $tipo['id'] . '">' . $tipo['nome'] . '</option>';
                else
                    $comboTipos .= '<option selected value="' . $tipo['id'] . '">' . $tipo['nome'] . '</option>';
            }

            $comboTipos .= '</select>';
        }

        return $comboTipos;
    }

    private function getTiposAtributo($idAtributo = 0) {
        $comboTipos = "";

        $query = $this->DB_fetch_array("SELECT A.*, B.id_conjunto_atributo FROM tb_produtos_atributos_tipos A LEFT JOIN tb_produtos_atributos B ON B.id_tipo = A.id AND B.id = $idAtributo WHERE A.atributo = 1 ORDER BY A.nome");
        if ($query->num_rows) {
            $comboTipos .= '
                <select class="form-control input-sm tipo_atributo">
                    <option value="">Tipo..</option>
            ';
            foreach ($query->rows as $tipo) {
                if (!$tipo['id_conjunto_atributo'])
                    $comboTipos .= '<option value="' . $tipo['id'] . '">' . $tipo['nome'] . '</option>';
                else
                    $comboTipos .= '<option selected value="' . $tipo['id'] . '">' . $tipo['nome'] . '</option>';
            }

            $comboTipos .= '</select>';
        }

        return $comboTipos;
    }

    private function addAtributoAction() {
        $idConjunto = 0;

        if (isset($_POST['id_conjunto']) && $_POST['id_conjunto'] != "")
            $idConjunto = $_POST['id_conjunto'];

        $atributos = "";

        if ($idConjunto != "") {
            $atributos = '
                                    <div class="row row-atributo novo-atributo">
                                        <div class="col-lg-12">
                                            <div class="panel panel-default toggle">
                                                <!-- Start .panel -->
                                                <div class="panel-heading">
                                                    <h4 class="panel-title">Atributo
                                                        <span class="panel-heading-remove">                                                            
                                                            <button type="button" data-id-conjunto="' . $idConjunto . '" class="btn btn-success btn-xs mr5 mb10 btn-add-atributo">Adicionar</button>
                                                            <button type="button" data-id="" data-nome="" class="btn btn-danger btn-xs mr5 mb10 btn-remove-atributo">Remover</button>   
                                                        </span>
                                                    </h4>
                                                    <div class="panel-controls panel-controls-right btn-toggle-inner">
                                                        <a class="toggle panel-minimize" href="#">
                                                            <i class="toggle-atributo icomoon-icon-minus"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="panel-body" style="display:none">
                                                    <input type="hidden" class="id_conjunto_atributo" value="' . $idConjunto . '" />
                                                    <div class="form-group">
                                                        <label class="col-lg-2 control-label" for="">Imagem:</label>
                                                        <div class="col-lg-10">
                                                             <input class="files btn btn-default atributo_imagem" data-id="" data-id-conjunto="' . $idConjunto . '" name="imagem" type="file" value="" /> 
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="col-lg-2 control-label" for="">Ampliar fotos:</label>
                                                        <div class="col-lg-10">
                                                            <label class="pointer" for="ampliar_fotos' . $_POST['c'] . '0">Sim</label><input class="form-control ampliar_fotos" name="ampliar_fotos' . $_POST['c'] . '" id="ampliar_fotos' . $_POST['c'] . '0" type="radio" value="1" /> 
                                                            <label class="pointer" for="ampliar_fotos' . $_POST['c'] . '1">Não</label><input checked class="form-control ampliar_fotos" name="ampliar_fotos' . $_POST['c'] . '" id="ampliar_fotos' . $_POST['c'] . '1" type="radio" value="0" /> 
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="col-lg-2 control-label" for="">Nome:</label>
                                                        <div class="col-lg-10">
                                                             <input class="form-control nome_atributo" required name="nome" id="nome" type="text" value="" /> 
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                    <label class="col-lg-2 control-label" for="">Descrição ou Video:</label>
                                                        <div class="col-lg-10">
                                                             <input class="form-control descricao_atributo" required name="descricao" id="descricao" type="text" value="" /> 
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="col-lg-2 control-label" for="">Preço:</label>
                                                        <div class="col-lg-10">
                                                             <input class="form-control preco_atributo" required name="custo" id="custo" type="text" value="" /> 
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="col-lg-2 control-label" for="">Selecionado:</label>
                                                        <div class="col-lg-10">
                                                            <label class="pointer" for="selecionado' . $_POST['c'] . '0">Sim</label><input class="form-control selecionado" name="selecionado' . $_POST['c'] . '" id="selecionado' . $_POST['c'] . '0" type="radio" value="1" /> 
                                                            <label class="pointer" for="selecionado' . $_POST['c'] . '1">Não</label><input checked class="form-control selecionado falso" name="selecionado' . $_POST['c'] . '" id="selecionado' . $_POST['c'] . '1" type="radio" value="0" /> 
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="col-lg-2 control-label" for="">Tipo:</label>
                                                        <div class="col-lg-10">
                                                             ' . $this->getTiposAtributo() . '
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-lg-offset-2 col-lg-10">
                                                            <button data-id="" type="button" class="btn btn-primary mr5 mb10 btn-salvar-atributo">Salvar</button>
                                                        </div>
                                                    </div>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    </div>
        ';
        }
        echo $atributos;
    }

    private function getAtributos($idConjunto = 0) {
        $conjunto = $this->DB_fetch_array("SELECT * FROM tb_produtos_conjuntos_atributos WHERE id = $idConjunto");

        $atributos = '
                                    <div class="row row-atributo">
                                        <div class="col-lg-12">
                                            <div class="panel panel-default toggle">
                                                <!-- Start .panel -->
                                                <div class="panel-heading">
                                                    <h4 class="panel-title">Atributo
                                                        <span class="panel-heading-remove">                                                            
                                                            <button type="button" data-id-conjunto="' . $idConjunto . '" class="btn btn-success btn-xs mr5 mb10 btn-add-atributo">Adicionar</button>
                                                            <button type="button" data-id="" data-nome="" class="btn btn-danger btn-xs mr5 mb10 btn-remove-atributo">Remover</button>
                                                        </span>
                                                    </h4>
                                                    <div class="panel-controls panel-controls-right btn-toggle-inner">
                                                        <a class="toggle panel-minimize" href="#">
                                                            <i class="toggle-atributo icomoon-icon-minus"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="panel-body" style="display:none">
                                                    <input type="hidden" class="id_conjunto_atributo" value="' . $idConjunto . '" />
                                                    <div class="form-group">
                                                        <label class="col-lg-2 control-label" for="">Imagem:</label>
                                                        <div class="col-lg-10">
                                                             <input class="files btn btn-default atributo_imagem" data-id="" data-id-conjunto="' . $idConjunto . '" name="imagem" type="file" value="" /> 
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="col-lg-2 control-label" for="">Ampliar fotos:</label>
                                                        <div class="col-lg-10">
                                                            <label class="pointer" for="ampliar_fotos' . $idConjunto . '0">Sim</label><input class="form-control ampliar_fotos" name="ampliar_fotos' . $idConjunto . '" id="ampliar_fotos' . $idConjunto . '0" type="radio" value="1" /> 
                                                            <label class="pointer" for="ampliar_fotos' . $idConjunto . '1">Não</label><input checked class="form-control ampliar_fotos" name="ampliar_fotos' . $idConjunto . '" id="ampliar_fotos' . $idConjunto . '1" type="radio" value="0" /> 
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="col-lg-2 control-label" for="">Nome:</label>
                                                        <div class="col-lg-10">
                                                             <input class="form-control nome_atributo" required name="nome" id="nome" type="text" value="" /> 
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                    <label class="col-lg-2 control-label" for="">Descrição ou Video:</label>
                                                        <div class="col-lg-10">
                                                             <input class="form-control descricao_atributo" required name="descricao" id="descricao" type="text" value="" /> 
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="col-lg-2 control-label" for="">Preço:</label>
                                                        <div class="col-lg-10">
                                                             <input class="form-control preco_atributo" required name="custo" id="custo" type="text" value="" /> 
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="col-lg-2 control-label" for="">Selecionado:</label>
                                                        <div class="col-lg-10">
                                                            <label class="pointer" for="selecionado' . $idConjunto . '0">Sim</label><input class="form-control selecionado" name="selecionado' . $idConjunto . '0" id="selecionado' . $idConjunto . '0" type="radio" value="1" /> 
                                                            <label class="pointer" for="selecionado' . $idConjunto . '1">Não</label><input checked class="form-control selecionado falso" name="selecionado' . $idConjunto . '0" id="selecionado' . $idConjunto . '1" type="radio" value="0" /> 
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="col-lg-2 control-label" for="">Tipo:</label>
                                                        <div class="col-lg-10">
                                                             ' . $this->getTiposAtributo() . '
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-lg-offset-2 col-lg-10">
                                                            <button data-id="" type="button" class="btn btn-primary mr5 mb10 btn-salvar-atributo">Salvar</button>
                                                        </div>
                                                    </div>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    </div>
        ';

        $query = $this->DB_fetch_array("SELECT A.*, REPLACE(FORMAT(A.custo,2),',','') custo FROM tb_produtos_atributos A WHERE A.id_conjunto_atributo = $idConjunto ORDER BY A.ordem");

        if ($query->num_rows) {
            $atributos = '';
            foreach ($query->rows as $atributo) {
                $nome = "Atributo";
                if ($atributo['nome'] != "")
                    $nome = $atributo['nome'];


                $style_image_file = ' style="margin-top: 10px" ';

                if ($atributo['imagem'] != "") {
                    $imagem = '<a href="' . $this->upload_path . $atributo['imagem'] . '" rel="prettyPhoto"><img style="max-height: 100px;" src="' . $this->getImageFileSized($atributo['imagem'], 705, 530) . '" alt="" class="image marginR10"/></a><br><span class="close-img s16 entypo-icon-close pointer"></span>';
                } else {
                    $imagem = '<a href="" rel="prettyPhoto"><img style="max-height: 100px;" src="images/no-image.gif" alt="" class="image marginR10"/></a><br><span class="close-img s16 entypo-icon-close pointer"></span>';
                }

                $sim = "";
                $nao = "checked";

                if ($atributo['selecionado'] == 1) {
                    $sim = "checked";
                    $nao = "";
                }

                $sim_ampliar = "";
                $nao_ampliar = "checked";

                if ($atributo['ampliar_fotos'] == 1) {
                    $sim_ampliar = "checked";
                    $nao_ampliar = "";
                }


                $atributos .= '
                                    <div class="row row-atributo">
                                        <div class="col-lg-12">
                                            <div class="panel panel-default toggle">
                                                <!-- Start .panel -->
                                                <div class="panel-heading">
                                                    <h4 class="panel-title">' . $nome . '
                                                        <span class="panel-heading-remove">
                                                            <button type="button" data-id-conjunto="' . $idConjunto . '"  data-id="' . $atributo['id'] . '" data-nome="' . $atributo['nome'] . '" class="btn btn-warning btn-xs mr5 mb10 btn-rel-atributo">Relacionar</button>
                                                            <button type="button" data-id-conjunto="' . $idConjunto . '"  data-id="' . $atributo['id'] . '" data-nome="' . $conjunto->rows[0]['nome'] . '" class="btn btn-info btn-xs mr5 mb10 btn-order-atributo">Ordenar</button>
                                                            <button type="button" data-id-conjunto="' . $idConjunto . '" class="btn btn-success btn-xs mr5 mb10 btn-add-atributo">Adicionar</button>
                                                            <button type="button" data-id="' . $atributo['id'] . '" data-nome="' . $atributo['nome'] . '" class="btn btn-danger btn-xs mr5 mb10 btn-remove-atributo">Remover</button>
                                                        </span>
                                                    </h4>
                                                    <div class="panel-controls panel-controls-right btn-toggle-inner">
                                                        <a class="toggle panel-minimize" href="#">
                                                            <i class="toggle-atributo icomoon-icon-minus"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="panel-body" style="display:none">
                                                    <input type="hidden" class="id_conjunto_atributo" value="' . $idConjunto . '" />
                                                    <div class="form-group">
                                                        <label class="col-lg-2 control-label" for="">Imagem:</label>
                                                        <div class="col-lg-10">
                                                             ' . $imagem . '
                                                             <input ' . $style_image_file . 'data-id="' . $atributo['id'] . '" class="files btn btn-default atributo_imagem" data-id-conjunto="' . $idConjunto . '" name="imagem" type="file" value="" /> 
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="col-lg-2 control-label" for="">Ampliar fotos:</label>
                                                        <div class="col-lg-10">
                                                            <label class="pointer" for="ampliar_fotos' . $atributo['id'] . '0">Sim</label><input ' . $sim_ampliar . ' class="form-control ampliar_fotos" name="ampliar_fotos' . $atributo['id'] . '" id="ampliar_fotos' . $atributo['id'] . '0" type="radio" value="1" /> 
                                                            <label class="pointer" for="ampliar_fotos' . $atributo['id'] . '1">Não</label><input ' . $nao_ampliar . ' class="form-control ampliar_fotos" name="ampliar_fotos' . $atributo['id'] . '" id="ampliar_fotos' . $atributo['id'] . '1" type="radio" value="0" /> 
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="col-lg-2 control-label" for="">Nome:</label>
                                                        <div class="col-lg-10">
                                                             <input class="form-control nome_atributo" required name="nome" type="text" value="' . $atributo['nome'] . '" /> 
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                    <label class="col-lg-2 control-label" for="">Descrição ou Video:</label>
                                                        <div class="col-lg-10">
                                                             <input class="form-control descricao_atributo" required name="descricao" type="text" value="' . $atributo['descricao'] . '" /> 
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="col-lg-2 control-label" for="">Preço:</label>
                                                        <div class="col-lg-10">
                                                             <input class="form-control preco_atributo" required name="custo" type="text" value="' . $this->formataMoedaShow($atributo['custo']) . '" /> 
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="col-lg-2 control-label" for="">Selecionado:</label>
                                                        <div class="col-lg-10">
                                                            <label class="pointer" for="selecionado' . $atributo['id'] . '0">Sim </label><input ' . $sim . ' class="form-control selecionado" name="selecionado' . $atributo['id'] . '" id="selecionado' . $atributo['id'] . '0" type="radio" value="1" />
                                                            <label class="pointer" for="selecionado' . $atributo['id'] . '1">Não </label><input ' . $nao . ' class="form-control selecionado falso" name="selecionado' . $atributo['id'] . '" id="selecionado' . $atributo['id'] . '1" type="radio" value="0" />
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="col-lg-2 control-label" for="">Tipo:</label>
                                                        <div class="col-lg-10">
                                                             ' . $this->getTiposAtributo($atributo['id']) . '
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-lg-offset-2 col-lg-10">
                                                            <button data-id="' . $atributo['id'] . '" data-nome="' . $atributo['nome'] . '" type="button" class="btn btn-primary mr5 mb10 btn-salvar-atributo">Salvar</button>
                                                        </div>
                                                    </div>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                ';
            }
        }

        return $atributos;
    }

    private function delImageAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            $this->noPermission();

        $resposta = new \stdClass();

        $id = $_POST['id'];

        $dados = $this->DB_fetch_array("SELECT * FROM tb_produtos_atributos WHERE id = $id");

        if ($dados->rows[0]['imagem'] != "") {
            $this->deleteFile("tb_produtos_atributos", "imagem", "id=$id", $this->crop_atributo_sizes);
        }

        $query = $this->DB_update("tb_produtos_atributos", "imagem = NULL WHERE id = $id");
        if ($query) {
            $resposta->type = "success";
            $resposta->message = "Imagem removida com sucesso!";
            $this->inserirRelatorio("Removeu imagem do atributo id: [$id]");
        } else {
            $resposta->type = "error";
            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
        }

        echo json_encode($resposta);
    }

    private function getTableConjuntoAction() {
        if (!$this->permissions[$this->permissao_ref]['ler'])
            $this->noPermission();

        $id = $_POST['id'];

        $query = $this->DB_fetch_array("SELECT * FROM tb_produtos_conjuntos_atributos WHERE id_produto = $id ORDER BY ordem");


        $content = '
                                    <table cellpadding="0" cellspacing="0" border="0" class="tabletools table table-striped table-bordered sortable_conjunto" width="100%">
                                        <thead>
                                            <tr>
                                                <th width="5%" class="center">Ordem</th>
                                                <th width="85%" class="tal">Nome</th>
                                                <th class="center" width="5%" >Arraste</th>
                                            </tr>
                                        </thead>
                                        <tbody>

        ';

        if ($query->num_rows) {
            $ordem = 1;
            foreach ($query->rows as $row) {


                $content .= '
                                                    <tr id="' . $row['id'] . '" class="pointer">
                                                        <td class="center" width="5%" >' . $ordem . '</td>
                                                        <td class="tal" width="85%" >' . $row['nome'] . '</td>
                                                        <td class="center" width="5%"><span class="s16 iconic-icon-move"></span</td>
                                                    </tr>

                ';


                $ordem++;
            }
        }

        $content .= '
            
                                        </tbody>
                                    </table>
        ';

        $content .= '<div style="float:right"><span class="s16 iconic-icon-move"></span>Arraste para ordenar!</div>';



        echo $content;
    }

    private function getTableAtributoAction() {
        if (!$this->permissions[$this->permissao_ref]['ler'])
            $this->noPermission();

        $id = $_POST['id'];

        $query = $this->DB_fetch_array("SELECT * FROM tb_produtos_atributos WHERE id_conjunto_atributo = $id ORDER BY ordem");


        $content = '
                                    <table cellpadding="0" cellspacing="0" border="0" class="tabletools table table-striped table-bordered sortable_conjunto" width="100%">
                                        <thead>
                                            <tr>
                                                <th width="5%" class="center">Ordem</th>
                                                <th width="10%" class="tal">Imagem</th>
                                                <th width="80%" class="tal">Nome</th>
                                                <th width="5%" class="center">Arraste</th>
                                            </tr>
                                        </thead>
                                        <tbody>

        ';

        if ($query->num_rows) {
            $ordem = 1;
            foreach ($query->rows as $row) {

                if ($row['imagem'] != "")
                    $imagem = $this->upload_path . $row['imagem'];
                else
                    $imagem = "images/no-image.gif";

                $content .= '
                                                    <tr id="' . $row['id'] . '" class="pointer">
                                                        <td class="center" width="5%" style="height:auto; line-height:44px">' . $ordem . '</td>
                                                        <td class="center" width="10%" style="height:auto; line-height:44px"><a href="' . $imagem . '" rel="prettyPhoto"><img height="50px" class="image marginR10" src="' . $imagem . '" /></a></td>
                                                        <td class="tal" width="85%" style="height:auto; line-height:44px">' . $row['nome'] . '</td>
                                                        <td class="center" width="5%" style="height:auto; line-height:44px"><span class="s16 iconic-icon-move"></span</td>
                                                    </tr>

                ';


                $ordem++;
            }
        }

        $content .= '
            
                                        </tbody>
                                    </table>
        ';

        $content .= '<div style="float:right"><span class="s16 iconic-icon-move"></span>Arraste para ordenar!</div>';



        echo $content;
    }

    private function getTableRelAction() {
        if (!$this->permissions[$this->permissao_ref]['ler'])
            $this->noPermission();

        $id = $_POST['id'];
        $idProduto = $_POST['id_produto'];
        $idConjunto = $_POST['id_conjunto'];

        $query = $this->DB_fetch_array("SELECT * FROM tb_produtos_conjuntos_atributos WHERE id_produto = $idProduto AND id != $idConjunto ORDER BY ordem");

        $content = '';

        if ($query->num_rows) {
            $content .= '<table cellpadding="0" cellspacing="0" border="0" class="tabletools table table-striped table-bordered sortable" width="100%"><tbody>';
            foreach ($query->rows as $row) {
                $content .= '
                            <tr>
                                <td class="tal" width="50%">' . $row['nome'] . '</td>
                                <td class="center"  width="50%">
                                    <label class="checkbox-inline pointer selecionar_atributo"  for="habilitar-' . $row['id'] . '0"><input data-id-pai="' . $id . '" data-id-filho="' . $row['id'] . '" type="radio" class="habilitar-' . $row['id'] . '" name="habilitar-' . $row['id'] . '" id="habilitar-' . $row['id'] . '0" value="0" ' . $this->getCheckedRelAtributo($id, $row['id'], 1) . ' checked> <span style="padding-left:20px;">Habilitado</span></label>
                                    <label class="checkbox-inline pointer selecionar_atributo"  for="habilitar-' . $row['id'] . '1"><input data-id-pai="' . $id . '" data-id-filho="' . $row['id'] . '" type="radio" class="habilitar-' . $row['id'] . '" name="habilitar-' . $row['id'] . '" id="habilitar-' . $row['id'] . '1" value="1" ' . $this->getCheckedRelAtributo($id, $row['id'], 1) . '> <span style="padding-left:20px;">Desabilitado</span></label>
                                </td>
                            </tr>
                        ';
            }
            $content .= '</tbody></table>';
        }

        echo $content;
    }

    private function getConjuntosAction() {
        if (!$this->permissions[$this->permissao_ref]['ler'])
            $this->noPermission();

        $idProduto = "";
        if (isset($_POST['id_produto']) && $_POST['id_produto'] != "")
            $idProduto = $_POST['id_produto'];

        $query = $this->DB_fetch_array("SELECT * FROM tb_produtos_conjuntos_atributos A WHERE id_produto = $idProduto ORDER BY A.ordem ");

        $content = '';



        if ($query->num_rows) {
            foreach ($query->rows as $row) {
                $content .= '
                    
                    <div class="row row-conjunto" id="conjunto_' . $row['id'] . '">
                        <div class="col-lg-12">
                            <div class="panel panel-default toggle">
                                <!-- Start .panel -->
                                <div class="panel-heading">
                                    <h4 class="panel-title">' . $row['nome'] . '
                                        <button type="button" data-id="' . $idProduto . '" class="btn btn-info btn-xs mr5 mb10 panel-heading-remove-right btn-order-conjunto">Ordenar Conjuntos</button>
                                        <button type="button" data-id="' . $row['id'] . '" data-nome="' . $row['nome'] . '" class="btn btn-danger btn-xs mr5 mb10 panel-heading-remove btn-remove-conjunto">Remover</button>
                                    </h4>
                                    <div class="panel-controls panel-controls-right btn-toggle">
                                        <a class="toggle panel-minimize" href="#">
                                            <i class="toggle-conjunto icomoon-icon-minus"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="panel-body" style="display:none">
                                    <input type="hidden" class="id_conjunto_atributo" value="' . $row['id'] . '" />
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label" for="">Nome do Conjunto:</label>
                                        <div class="col-lg-8">
                                            <input class="form-control nome_conjunto" required type="text" value="' . $row['nome'] . '" /> 
                                        </div>                                     
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label" for="descricao">Descrição:</label>
                                        <div class="col-lg-8">
                                            <textarea id="descricao" name="descricao" class="form-control elastic descricao" rows="2">' . stripslashes($row['descricao']) . '</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group" style="border-bottom:none; padding-bottom: 20px">
                                        <label class="col-lg-2 control-label" for="">Descrição de Aviso Desabilitado:</label>
                                        <div class="col-lg-8">
                                            <textarea class="form-control elastic descricao_aviso_desabilitado" rows="2">' . stripslashes($row['descricao_aviso_desabilitado']) . '</textarea>
                                        </div>
                                        
                                        <div class="col-lg-2">
                                            <button type="button" class="btn btn-primary mr5 mb10 btn-save-conjunto">Salvar</button>
                                        </div>
                                    </div>
                                    ' . $this->getAtributos($row['id']) . '
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    
                ';
            }
        }

        echo json_encode(Array('content' => $content));
    }

    private function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();

        $id = $this->getParameter("id");

        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");
        /*
          if ($dados->rows[0]['imagem'] != "") {
          $this->deleteFile($this->table, "imagem", "id=$id", $this->crop_capa_sizes);
          }

          $fotos = $this->DB_fetch_array("SELECT * FROM $this->table2 WHERE id_produto = $id");
          if ($fotos->num_rows) {
          foreach ($fotos->rows as $foto) {
          $this->deleteFile($this->table2, "url", "id=" . $foto['id'], $this->crop_sizes);
          }
          $this->DB_delete($this->table2, "id_produto=$id");
          }
         *
         */

        $this->DB_update($this->table, "apagado = 1 WHERE id = $id");
        $this->DB_update("tb_produtos_personalizados", "apagado = 1 WHERE id_produto = $id");
        $this->DB_delete("tb_produtos_destaques", "id_produto = $id");
        $this->DB_delete("tb_produtos_destaques_personalizados", "id_produto = $id");
        $this->DB_delete("tb_produtos_destaques_pronto_entrega", "id_produto = $id");


        $this->inserirRelatorio("Apagou produto: [" . $dados->rows[0]['nome'] . "] id: [$id]");
        /*
          $this->DB_delete($this->table, "id=$id");
          $this->DB_delete($this->_seo_table, "id=" . $dados->rows[0]['id_seo']);
         *
         */

        echo $this->getModule();
    }

    private function saveAction() {
        $seo = new stdClass();
        $seo->breadcrumbs = "produto/";
        $seo->pagina = "produto";

        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormulario($formulario);
        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {
            $validacao = $this->validaUpload($formulario);

            if (!$validacao->return) {
                echo json_encode($validacao);
            } else {
                $resposta = new \stdClass();

                if (isset($_POST['seo_url_breadcrumbs']) && $_POST['seo_url_breadcrumbs'] != "") {
                    $seo->breadcrumbs = $this->formataBreadcrumbs($_POST['seo_url_breadcrumbs']);
                }

                $seo->url_secundaria = $formulario->nome;
                $seo->seo_scripts = $formulario->seo_scripts;

                if ($_POST["seo_title"] == "")
                    $_POST["seo_title"] = $formulario->nome;

                require_once __sys_path__ . "System/includes/seo.php";

                if ($seo_request->response) {

                    if ($_POST['qtd_minima'] == "")
                        $_POST['qtd_minima'] = "NULL";

                    if ($_POST['prazo_producao'] == "")
                        $_POST['prazo_producao'] = "NULL";

                    if ($_POST['prazo_producao_adic'] == "")
                        $_POST['prazo_producao_adic'] = "NULL";

                    if ($_POST['porcentagem_fabrica'] != "")
                        $_POST['porcentagem_fabrica'] = $this->formataMoedaBd($_POST['porcentagem_fabrica']);
                    else
                        $_POST['porcentagem_fabrica'] = "NULL";

                    if ($_POST['comissao_venda'] != "")
                        $_POST['comissao_venda'] = $this->formataMoedaBd($_POST['comissao_venda']);
                    else
                        $_POST['comissao_venda'] = "NULL";

                    if ($_POST['dimensao_largura'] != "")
                        $_POST['dimensao_largura'] = $this->formataMoedaBd($_POST['dimensao_largura']);
                    else
                        $_POST['dimensao_largura'] = "NULL";

                    if ($_POST['dimensao_altura'] != "")
                        $_POST['dimensao_altura'] = $this->formataMoedaBd($_POST['dimensao_altura']);
                    else
                        $_POST['dimensao_altura'] = "NULL";

                    if ($_POST['dimensao_profundidade'] != "")
                        $_POST['dimensao_profundidade'] = $this->formataMoedaBd($_POST['dimensao_profundidade']);
                    else
                        $_POST['dimensao_profundidade'] = "NULL";

                    if ($_POST['peso'] != "")
                        $_POST['peso'] = $this->formataMoedaBd($_POST['peso']);
                    else
                        $_POST['peso'] = "NULL";

                    if ($_POST['custo'] != "")
                        $_POST['custo'] = $this->formataMoedaBd($_POST['custo']);
                    else
                        $_POST['custo'] = "NULL";


                    if ($_POST['porcentagem_ipi'] != "")
                        $_POST['porcentagem_ipi'] = $this->formataMoedaBd($_POST['porcentagem_ipi']);
                    else
                        $_POST['porcentagem_ipi'] = "NULL";


                    if ($_POST['frete_embutido'] != "")
                        $_POST['frete_embutido'] = $this->formataMoedaBd($_POST['frete_embutido']);
                    else
                        $_POST['frete_embutido'] = "NULL";

                    if ($_POST['id_frete_terrestre'] == "")
                        $_POST['id_frete_terrestre'] = "NULL";

                    if ($_POST['id_frete_aereo'] == "")
                        $_POST['id_frete_aereo'] = "NULL";

                    $data = $this->formularioObjeto($_POST, $this->table);

                    if ($this->imagem_capa != "") {
                        $data->imagem = $this->imagem_capa;
                        if (isset($data->id) && $data->id != "")
                            $this->deleteFile($this->table, "imagem", "id=" . $data->id, $this->crop_capa_sizes);
                    }

                    if ($this->imagem_icone != "") {
                        $data->icone = $this->imagem_icone;
                        if (isset($data->id) && $data->id != "")
                            $this->deleteFile($this->table, "icone", "id=" . $data->id, $this->crop_icone_sizes);
                    }

                    if ($formulario->id == "") {
                        //criar
                        if (!$this->permissions[$this->permissao_ref]['gravar'])
                            exit();

                        $data->id_seo = $seo_request->id;

                        $ordem = $this->DB_fetch_array("SELECT MAX(id) ordem FROM $this->table");
                        if ($ordem->num_rows) {
                            $data->ordem = $ordem->rows[0]['ordem'] + 1;
                        } else {
                            $data->ordem = 0;
                        }

                        foreach ($data as $key => $value) {
                            $fields[] = $key;
                            if ($value != "NULL")
                                $values[] = "'$value'";
                            else
                                $values[] = "$value";
                        }

                        $query = $this->DB_insert($this->table, implode(',', $fields), implode(',', $values));
                        if ($query->query) {

                            if (isset($_POST['categorias'])) {
                                foreach ($_POST['categorias'] as $categoria) {
                                    $this->DB_insert("tb_produtos_produtos_has_tb_produtos_categorias", "id_produto, id_categoria", "$query->insert_id,$categoria");
                                }
                            }

                            $contaazul = new ContaAzul($this);
                            $contaazul->newProduct($query->insert_id);

                            $resposta->type = "success";
                            $resposta->message = "Registro cadastrado com sucesso!";
                            $this->inserirRelatorio("Cadastrou produto: [" . $data->nome . "]");
                        } else {
                            $resposta->type = "error";
                            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                        }
                    } else {
                        //alterar
                        if (!$this->permissions[$this->permissao_ref]['editar'])
                            exit();

                        foreach ($data as $key => $value) {
                            if ($value != "NULL")
                                $fields_values[] = "$key='$value'";
                            else
                                $fields_values[] = "$key=$value";
                        }

                        $query = $this->DB_update($this->table, implode(',', $fields_values) . " WHERE id=" . $data->id);
                        if ($query) {
                            $this->DB_delete("tb_produtos_produtos_has_tb_produtos_categorias", "id_produto = $data->id");
                            if (isset($_POST['categorias'])) {
                                foreach ($_POST['categorias'] as $categoria) {
                                    $this->DB_insert("tb_produtos_produtos_has_tb_produtos_categorias", "id_produto, id_categoria", "$data->id,$categoria");
                                }
                            }

                            $resposta->type = "success";
                            $resposta->message = "Registro alterado com sucesso!";
                            $this->inserirRelatorio("Alterou produto: [" . $data->nome . "]");
                        } else {
                            $resposta->type = "error";
                            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                        }
                    }
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }

                echo json_encode($resposta);
            }
        }
    }

    private function delConjuntoAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();

        $resposta = new \stdClass();

        $formulario = $this->formularioObjeto($_POST);

        $query = $this->DB_fetch_array("SELECT * FROM tb_produtos_conjuntos_atributos A WHERE A.id = $formulario->id");
        if ($query->num_rows)
            $conjunto = $query->rows[0];

        if ($formulario->id != "") {
            $dados = $this->DB_fetch_array("SELECT * FROM tb_produtos_atributos WHERE id_conjunto_atributo = $formulario->id");
            if ($dados->num_rows) {
                foreach ($dados->rows as $row) {
                    if ($row['imagem'] != "") {
                        $this->deleteFile("tb_produtos_atributos", "imagem", "id = {$row['id']}", $this->crop_atributo_sizes);
                    }
                }
            }

            $this->DB_delete("tb_produtos_atributos", "id_conjunto_atributo = $formulario->id");
            $query = $this->DB_delete("tb_produtos_conjuntos_atributos", " id = " . $formulario->id);
            if ($query) {
                $resposta->type = "success";
                $resposta->message = "Registro [{$conjunto['nome']}] apagado com sucesso!";
                $this->inserirRelatorio("Apagou conjunto de atributos: [" . $conjunto['nome'] . "] id [" . $formulario->id . "]");
            } else {
                $resposta->type = "error";
                $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
            }
        } else {
            $resposta->type = "error";
            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
        }

        echo json_encode($resposta);
    }

    private function delAtributoAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();

        $resposta = new \stdClass();
        if (isset($_POST['id']) && $_POST['id'] != "") {
            $formulario = $this->formularioObjeto($_POST);

            $query = $this->DB_fetch_array("SELECT * FROM tb_produtos_atributos A WHERE A.id = $formulario->id");
            if ($query->num_rows)
                $atributo = $query->rows[0];

            if ($formulario->id != "") {
                $dados = $this->DB_fetch_array("SELECT * FROM tb_produtos_atributos WHERE id = $formulario->id");
                if ($dados->rows[0]['imagem'] != "") {
                    $this->deleteFile("tb_produtos_atributos", "imagem", "id = $formulario->id", $this->crop_atributo_sizes);
                }

                $query = $this->DB_delete("tb_producao_servicos_has_atributos", " id_atributo = " . $formulario->id);
                $query = $this->DB_delete("tb_produtos_atributos", " id = " . $formulario->id);
                
                if ($query) {
                    $resposta->type = "success";
                    $resposta->message = "Registro [{$atributo['nome']}] apagado com sucesso!";
                    $this->inserirRelatorio("Apagou atributo: [" . $atributo['nome'] . "] id [" . $formulario->id . "]");
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            } else {
                $resposta->type = "error";
                $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
            }
        } else {
            $resposta->type = "success";
            $resposta->message = "Registro apagado com sucesso!";
        }
        echo json_encode($resposta);
    }

    private function saveConjuntoAction() {
        if (!$this->permissions[$this->permissao_ref]['editar'])
            exit();

        $resposta = new \stdClass();

        $formulario = $this->formularioObjeto($_POST, "tb_produtos_conjuntos_atributos");

        if ($formulario->id != "") {
            foreach ($formulario as $key => $value) {
                if ($value != "NULL")
                    $fields_values[] = "$key='$value'";
                else
                    $fields_values[] = "$key=$value";
            }

            $query = $this->DB_update("tb_produtos_conjuntos_atributos", implode(',', $fields_values) . " WHERE id=" . $formulario->id);
            if ($query) {
                $resposta->type = "success";
                $resposta->message = "Registro [$formulario->nome] alterado com sucesso!";
                $this->inserirRelatorio("Alterou conjunto de atributos: [" . $formulario->nome . "] id [" . $formulario->id . "]");
            } else {
                $resposta->type = "error";
                $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
            }
        } else {
            $resposta->type = "error";
            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
        }

        echo json_encode($resposta);
    }

    private function saveAtributoAction() {
        if (!$this->permissions[$this->permissao_ref]['editar'])
            exit();

        $resposta = new \stdClass();

        //$data = $this->formularioObjeto($_POST, "tb_produtos_atributos");
        $data = $this->formularioObjeto($_POST);

        if ($data->custo != "")
            $data->custo = $this->formataMoedaBd($data->custo);
        else
            $data->custo = "NULL";

        if (!isset($data->id_tipo) || $data->id_tipo == "")
            $data->id_tipo = "NULL";


        if (!isset($data->selecionado))
            $data->selecionado = "0";

        if ($data->id != "") {
            foreach ($data as $key => $value) {
                if ($value != "NULL")
                    $fields_values[] = "$key='$value'";
                else
                    $fields_values[] = "$key=$value";
            }

            $resposta->query = implode(',', $fields_values);

            $query = $this->DB_update("tb_produtos_atributos", implode(',', $fields_values) . " WHERE id=" . $data->id);
            if ($query) {
                $resposta->type = "success";

                if ($data->nome != "") {
                    $resposta->message = "Registro [$data->nome] alterado com sucesso!";
                    $this->inserirRelatorio("Alterou conjunto de atributos: [" . $data->nome . "] id [" . $data->id . "]");
                } else {
                    $resposta->message = "Registro  alterado com sucesso!";
                    $this->inserirRelatorio("Alterou conjunto de atributos id [" . $data->id . "]");
                }
            } else {
                $resposta->type = "error";
                $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
            }
        } else {
            $ordem = $this->DB_fetch_array("SELECT MAX(id) ordem FROM tb_produtos_atributos");
            if ($ordem->num_rows) {
                $data->ordem = $ordem->rows[0]['ordem'] + 1;
            } else {
                $data->ordem = 0;
            }

            foreach ($data as $key => $value) {
                if($key != "id"){
                    $fields[] = $key;
                    if ($value != "NULL")
                        $values[] = "'$value'";
                    else
                        $values[] = "$value";
                }
            }

            $query = $this->DB_insert("tb_produtos_atributos", implode(',', $fields), implode(',', $values));
            if ($query->query) {
                $resposta->type = "success";
                $resposta->message = "Registro cadastrado com sucesso!";
                $this->inserirRelatorio("Cadastrou produto: [" . $data->nome . "]");
            } else {
                $resposta->type = "error";
                $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
            }
        }

        echo json_encode($resposta);
    }

    private function uploadFileAction() {
        $resposta = new \stdClass();
        $resposta->return = true;

        //$formulario = $this->formularioObjeto($_POST, "tb_produtos_atributos");
        $formulario = $this->formularioObjeto($_POST);

        if (is_uploaded_file($_FILES["imagem"]["tmp_name"])) {

            $upload = $this->uploadFile("imagem", array("jpg", "jpeg", "gif", "png"), $this->crop_atributo_sizes);
            if ($upload->return) {
                $this->imagem_atributo = $upload->file_uploaded;
            }
        }

        if (!isset($upload)) {
            $resposta->type = "attention";
            $resposta->message = "Imagem não selecionada.";
            $resposta->return = false;
        } else if (isset($upload) && !$upload->return) {
            $resposta->type = "attention";
            $resposta->message = $upload->message;
            $resposta->return = false;
        } else {

            if ($formulario->custo != "")
                $formulario->custo = $this->formataMoedaBd($formulario->custo);
            else
                $formulario->custo = "NULL";

            if (!isset($formulario->id_tipo) || $formulario->id_tipo == "")
                $formulario->id_tipo = "NULL";

            if (!isset($formulario->selecionado))
                $formulario->selecionado = "0";

            if ($formulario->id != "") {
                if (!$this->permissions[$this->permissao_ref]['editar'])
                    exit();

                $dados = $this->DB_fetch_array("SELECT * FROM tb_produtos_atributos WHERE id = $formulario->id");

                if ($dados->rows[0]['imagem'] != "") {
                    $this->deleteFile("tb_produtos_atributos", "imagem", "id = $formulario->id", $this->crop_atributo_sizes);
                }

                $query = $this->DB_update("tb_produtos_atributos", "ampliar_fotos = $formulario->ampliar_fotos, id_conjunto_atributo = $formulario->id_conjunto_atributo, id_tipo = $formulario->id_tipo,  imagem = '$this->imagem_atributo', nome = '$formulario->nome', descricao = '$formulario->descricao', custo = $formulario->custo, selecionado = $formulario->selecionado WHERE id = $formulario->id AND id_conjunto_atributo = $formulario->id_conjunto_atributo");
                if ($query) {
                    $resposta->type = "success";
                    $resposta->message = "Arquivo enviado com sucesso!";
                    $resposta->imagem = $this->root_path . "uploads/" . $this->imagem_atributo;
                    $resposta->return = false;

                    $this->inserirRelatorio("Alterou imagem de atributo id [" . $formulario->id . "]");
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            } else {
                if (!$this->permissions[$this->permissao_ref]['gravar'])
                    exit();

                $order = $this->DB_fetch_array("SELECT MAX(id) ordem FROM tb_produtos_atributos");
                if ($order->num_rows && $order->rows[0]['ordem'] != "") {
                    $ordem = $order->rows[0]['ordem'] + 1;
                } else {
                    $ordem = 0;
                }

                $query = $this->DB_insert("tb_produtos_atributos", "id_conjunto_atributo, id_tipo, imagem, nome, descricao, custo, selecionado,ampliar_fotos, ordem", "$formulario->id_conjunto_atributo, $formulario->id_tipo, '$this->imagem_atributo', '$formulario->nome', '$formulario->descricao', $formulario->custo, $formulario->selecionado, $formulario->ampliar_fotos, $ordem");
                if ($query->query) {
                    $resposta->type = "success";
                    $resposta->message = "Arquivo enviado com sucesso!";
                    $resposta->imagem = $this->root_path . "uploads/" . $this->imagem_atributo;
                    $resposta->return = false;

                    $this->inserirRelatorio("Cadastrou imagem de atributo id [" . $query->insert_id . "]");
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            }
        }


        echo json_encode($resposta);
    }

    private function validaFormulario($form) {

        $resposta = new \stdClass();
        $resposta->return = true;

        $cat = false;
        if (!isset($_POST['categorias']) || count($_POST['categorias']) < 1)
            $cat = true;

        if ($form->nome == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "nome";
            $resposta->return = false;
            return $resposta;
        } else if ($form->resumo == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "resumo";
            $resposta->return = false;
            return $resposta;
        } else if ($form->texto == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "texto";
            $resposta->return = false;
            return $resposta;
        } else if ($cat) {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "box2View";
            $resposta->return = false;
            return $resposta;
        } else {
            return $resposta;
        }
    }

    private function validaUpload($form) {

        $resposta = new \stdClass();
        $resposta->return = true;

        if (is_uploaded_file($_FILES["fileupload"]["tmp_name"])) {
            $upload = $this->uploadFile("fileupload", array("jpg", "jpeg", "gif", "png"), $this->crop_capa_sizes);
            if ($upload->return) {
                $this->imagem_capa = $upload->file_uploaded;
            }
        }

        if (is_uploaded_file($_FILES["icone"]["tmp_name"])) {
            $uploadIcone = $this->uploadFile("icone", array("jpg", "jpeg", "gif", "png"), $this->crop_icone_sizes);
            if ($uploadIcone->return) {
                $this->imagem_icone = $uploadIcone->file_uploaded;
            }
        }

        if ((!isset($form->id) || $form->id == "") && !isset($upload)) {
            $resposta->type = "attention";
            $resposta->message = "Imagem não selecionada.";
            $resposta->return = false;
            return $resposta;
        } else if (isset($upload) && !$upload->return) {
            $resposta->type = "attention";
            $resposta->message = $upload->message;
            $resposta->return = false;
            return $resposta;
        } else if ((!isset($form->id) || $form->id == "") && !isset($uploadIcone)) {
            $resposta->type = "attention";
            $resposta->message = "Ícone não selecionado.";
            $resposta->return = false;
            return $resposta;
        } else if (isset($uploadIcone) && !$uploadIcone->return) {
            $resposta->type = "attention";
            $resposta->message = $uploadIcone->message;
            $resposta->return = false;
            return $resposta;
        } else {
            return $resposta;
        }
    }

    private function uploadAction() {

        if ($this->getParameter("id") && $this->getParameter("session")) {

            $consulta = $this->DB_fetch_array("SELECT * FROM tb_admin_users WHERE session = '{$this->getParameter('session')}'");
            if ($consulta->num_rows) {

                $this->id = $this->getParameter("id");

                if (is_uploaded_file($_FILES['file']['tmp_name'])) {

                    echo $_FILES['file']['tmp_name'];

                    $upload = $this->uploadFile("file", array("jpg", "jpeg", "gif", "png"), $this->crop_sizes);
                    if ($upload->return) {

                        $file_uploaded = $upload->file_uploaded;

                        $dados = $this->DB_fetch_array("SELECT id, MAX(ordem) as ordem FROM $this->table2 WHERE id_produto=" . $this->id . " GROUP BY id_produto");
                        if ($dados->num_rows == 0) {
                            $ordem = 1;
                        } else {
                            $ordem = $dados->rows[0]['ordem'];
                            $ordem++;
                        }

                        $fields = array('id_produto', 'stats', 'ordem', 'url');
                        $values = array($this->id, '1', "'" . $ordem . "'", "'" . $file_uploaded . "'");

                        $query = $this->DB_insert($this->table2, implode(',', $fields), implode(',', $values));
                        $idFoto = $query->insert_id;
                        if ($query->query) {
                            $_SESSION['admin_id'] = $consulta->rows[0]['id'];
                            $_SESSION['admin_nome'] = $consulta->rows[0]['nome'];
                            $this->inserirRelatorio("Cadastrou imagem produto id produto: [" . $this->id . "], id foto: [" . $idFoto . "]");
                        }

                        echo '{"jsonrpc" : "2.0", "result" : null, "id" : "id"}';
                    }
                }
            }
        }
    }

    private function uploadDelAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            $this->noPermission(false);

        $this->id = $this->getParameter("id");
        $fotos = $this->DB_fetch_array("SELECT * FROM $this->table2 WHERE id = $this->id");
        if ($fotos->num_rows) {
            foreach ($fotos->rows as $foto) {
                $this->deleteFile($this->table2, "url", "id=" . $foto['id'], $this->crop_sizes);
            }
        }
        $this->inserirRelatorio("Apagou imagem produto legenda: [" . $fotos->rows[0]['legenda'] . "] id: [$this->id]");
        $this->DB_delete($this->table2, "id=$this->id");
    }

    private function orderAction() {

        if (!$this->permissions[$this->permissao_ref]['editar'])
            $this->noPermission(false);

        $this->ordenarRegistros($_POST["array"], $this->table);
    }

    private function orderConjuntoAction() {
        if (!$this->permissions[$this->permissao_ref]['editar'])
            $this->noPermission(false);
        $this->ordenarRegistros($_POST["array"], "tb_produtos_conjuntos_atributos");
    }

    private function orderAtributoAction() {
        if (!$this->permissions[$this->permissao_ref]['editar'])
            $this->noPermission(false);

        $this->ordenarRegistros($_POST["array"], "tb_produtos_atributos");
    }

    private function orderGalleryAction() {

        if (!$this->permissions[$this->permissao_ref]['editar'])
            $this->noPermission(false);

        $this->ordenarRegistros($_POST["array"], $this->table2);
    }

    private function editPhotoAction() {
        if (!$this->permissions[$this->permissao_ref]['editar'])
            $this->noPermission(false);

        $id = $_POST['id'];
        $legenda = $_POST['legenda'];

        $fields_values = array(
            "legenda='" . $legenda . "'"
        );

        $query = $this->DB_update($this->table2, implode(',', $fields_values) . " WHERE id=" . $id);

        $resposta = new stdClass();

        if ($query) {
            $resposta->type = "success";
            $this->inserirRelatorio("Editou imagem produto legenda: [" . $legenda . "] id: [" . $id . "]");
        } else {
            $resposta->type = "error";
            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
        }

        echo json_encode($resposta);
    }

    private function setAtributoRelAction() {
        if (!$this->permissions[$this->permissao_ref]['editar'])
            $this->noPermission(false);

        $pai = $_POST['pai'];
        $filho = $_POST['filho'];
        $valor = $_POST['valor'];

        $this->DB_delete("tb_produtos_atributos_has_conjuntos_atributos", "id_atributo = $pai AND id_conjunto = $filho");
        if ($valor == 1)
            $this->DB_insert("tb_produtos_atributos_has_conjuntos_atributos", "id_atributo,id_conjunto,desabilitado", "$pai,$filho,$valor");
    }

    private function getCheckedRelAtributo($pai, $filho, $valor) {
        $query = $this->DB_fetch_array("SELECT * FROM tb_produtos_atributos_has_conjuntos_atributos WHERE id_atributo = $pai AND id_conjunto = $filho AND desabilitado = $valor ");
        if ($query->num_rows)
            return "checked";
    }

    private function verifyCustomAction() {
        if (!$this->permissions[$this->permissao_ref]['ler'])
            $this->noPermission(false);
        if (isset($_POST['id']) && $_POST['id'] != "") {
            $id = $_POST['id'];
            //vamos achar o atributo que está sendo usado e selecionado em algum produto personalizado (hummmmmmm que legal!)
            $query = $this->DB_fetch_array("SELECT B.id, B.nome, C.id_conjunto_atributo FROM tb_produtos_personalizados_has_tb_produtos_atributos A INNER JOIN tb_produtos_personalizados B ON B.id = A.id_produto_personalizado INNER JOIN tb_produtos_atributos C ON C.id = A.id_atributo WHERE A.id_atributo = $id AND A.selecionado = 1");
            if ($query->num_rows) {
                echo "<hr><br><h4 style='margin:0;padding:0'>Importante!</h4><div>O seguinte atributo se encontra selecionado nos seguintes produtos personalizados:</div><br>";
                foreach ($query->rows as $row) {
                    echo "<div>id: {$row['id']}: <a href='productcustom/edit/id/{$row['id']}#conjunto_{$row['id_conjunto_atributo']}' target='_blank'>{$row['nome']}</a></div>";
                }
                echo "<hr><br><div>Clique em OK se realmente deseja continuar!</div>";
            }
        }
    }

    private function verifyCustomConjuntoAction() {
        if (!$this->permissions[$this->permissao_ref]['ler'])
            $this->noPermission(false);

        $id = $_POST['id'];
        //vamos achar o atributo que está sendo usado e selecionado em algum produto personalizado (hummmmmmm que legal!)
        $query = $this->DB_fetch_array("SELECT B.id, B.nome, C.id_conjunto_atributo FROM tb_produtos_personalizados_has_tb_produtos_atributos A INNER JOIN tb_produtos_personalizados B ON B.id = A.id_produto_personalizado INNER JOIN tb_produtos_atributos C ON C.id = A.id_atributo WHERE C.id_conjunto_atributo = $id AND A.selecionado = 1");
        if ($query->num_rows) {
            echo "<hr><br><h4 style='margin:0;padding:0'>Importante!</h4><div>O seguinte conjunto se encontra selecionado nos seguintes produtos personalizados:</div><br>";
            foreach ($query->rows as $row) {
                echo "<div>id: {$row['id']}: <a href='productcustom/edit/id/{$row['id']}#conjunto_{$row['id_conjunto_atributo']}' target='_blank'>{$row['nome']}</a></div>";
            }
            echo "<hr><br><div>Clique em OK se realmente deseja continuar!</div>";
        }
    }

    private function duplicateAction() {
        if (!$this->permissions[$this->permissao_ref]['editar'])
            $this->noPermission(false);

        $id = $this->getParameter("id");

        $seos = $this->DB_fetch_array("SELECT A.* FROM tb_seo_paginas A INNER JOIN tb_produtos_produtos B ON B.id_seo = A.id WHERE B.id = $id");
        if ($seos->num_rows) {
            foreach ($seos->rows as $seo) {
                $separador = "";
                $fields = "";
                $values = "";
                foreach ($seo as $key => $value) {
                    if ($key != "id") {
                        if ($key == "seo_url") {
                            if (!$this->validaUrlAmiga($value)) {
                                $value = $value . "-" . uniqid();
                            }
                        }

                        $fields .= $separador . $key;
                        $values .= $separador . "'$value'";
                        $separador = ", ";
                    }
                }
            }

            $query = $this->DB_insert("tb_seo_paginas", $fields, $values);
            $idSeo = $query->insert_id;

            if ($query->query) {
                $produtos = $this->DB_fetch_array("SELECT * FROM tb_produtos_produtos WHERE id = $id");
                if ($produtos->num_rows) {
                    $ordem = $this->DB_fetch_array("SELECT MAX(id) ordem FROM tb_produtos_produtos");
                    foreach ($produtos->rows as $produto) {
                        $separador = "";
                        $fields = "";
                        $values = "";
                        foreach ($produto as $key => $value) {
                            if ($key != "id" && $key != "id_seo" && $key != "ordem" && $key != "data") {
                                if ($value == "")
                                    $value = "NULL";


                                if ($key == "imagem" && $value != "") {
                                    $imagem = $this->duplicateFile($value, array("jpg", "jpeg", "gif", "png"), $this->crop_capa_sizes);
                                    if ($imagem->return) {
                                        $value = $imagem->file_uploaded;
                                    }
                                }


                                if ($key == "icone" && $value != "") {
                                    $icone = $this->duplicateFile($value, array("jpg", "jpeg", "gif", "png"), $this->crop_icone_sizes);
                                    if ($icone->return) {
                                        $value = $icone->file_uploaded;
                                    }
                                }



                                $fields .= $separador . $key;
                                if ($value == "NULL")
                                    $values .= $separador . "$value";
                                else
                                    $values .= $separador . "'$value'";
                                $separador = ", ";
                            }
                        }
                        $fields .= ", id_seo, ordem";
                        $values .= ", $idSeo, {$ordem->rows[0]['ordem']}";
                    }

                    $query = $this->DB_insert("tb_produtos_produtos", $fields, $values);
                    $idProduto = $query->insert_id;
                    if ($query->query) {
                        /*
                          $fotos = $this->DB_fetch_array("SELECT * FROM tb_produtos_fotos WHERE id_produto = $id");
                          if ($fotos->num_rows) {
                          foreach ($fotos->rows as $foto) {
                          $separador = "";
                          $fields = "";
                          $values = "";
                          foreach ($foto as $key => $value) {
                          if ($key != "id" && $key != "id_produto" && $key != "data") {
                          if ($value == "")
                          $value = "NULL";

                          if ($key == "url" && $value != "") {
                          $url = $this->duplicateFile($value, array("jpg", "jpeg", "gif", "png"), $this->crop_sizes);
                          if ($url->return) {
                          $value = $url->file_uploaded;
                          }
                          }

                          $fields .= $separador . $key;
                          if ($value == "NULL")
                          $values .= $separador . "$value";
                          else
                          $values .= $separador . "'$value'";
                          $separador = ", ";
                          }
                          }
                          $fields .= ", id_produto";
                          $values .= ", $idProduto";

                          $query = $this->DB_insert("tb_produtos_fotos", $fields, $values);
                          }
                          }
                         *
                         */

                        $conjuntos = $this->DB_fetch_array("SELECT * FROM tb_produtos_conjuntos_atributos WHERE id_produto = $id ORDER BY ordem");
                        if ($conjuntos->num_rows) {
                            foreach ($conjuntos->rows as $conjunto) {
                                $separador = "";
                                $fields = "";
                                $values = "";
                                foreach ($conjunto as $key => $value) {
                                    if ($key != "id" && $key != "id_produto" && $key != "data") {

                                        if ($value == "")
                                            $value = "NULL";

                                        $fields .= $separador . $key;
                                        if ($value == "NULL")
                                            $values .= $separador . "$value";
                                        else
                                            $values .= $separador . "'$value'";
                                        $separador = ", ";
                                    }
                                }
                                $fields .= ", id_produto";
                                $values .= ", $idProduto";
                                $query = $this->DB_insert("tb_produtos_conjuntos_atributos", $fields, $values);
                                $idConjunto = $query->insert_id;
                                $idsConjuntos[$conjunto['id']] = $idConjunto;
                                if ($query->query) {
                                    $atributos = $this->DB_fetch_array("SELECT * FROM tb_produtos_atributos WHERE id_conjunto_atributo = {$conjunto['id']} ORDER BY ordem");
                                    if ($atributos->num_rows) {
                                        foreach ($atributos->rows as $atributo) {
                                            $separador = "";
                                            $fields = "";
                                            $values = "";
                                            foreach ($atributo as $key => $value) {
                                                if ($key != "id" && $key != "id_conjunto_atributo" && $key != "data") {

                                                    if ($value == "") {
                                                        $value = "NULL";
                                                    }

                                                    if ($key == "imagem" && $value != "") {
                                                        $imagem = $this->duplicateFile($value, array("jpg", "jpeg", "gif", "png"), $this->crop_atributo_sizes);
                                                        if ($imagem->return) {
                                                            $value = $imagem->file_uploaded;
                                                        }
                                                    }

                                                    $fields .= $separador . $key;
                                                    if ($value == "NULL") {
                                                        $values .= $separador . "$value";
                                                    } else {
                                                        $values .= $separador . "'" . addslashes($value) . "'";
                                                    }
                                                    $separador = ", ";
                                                }
                                            }

                                            $fields .= ", id_conjunto_atributo";
                                            $values .= ", $idConjunto";


                                            $query = $this->DB_insert("tb_produtos_atributos", $fields, $values);
                                            $idAtributo = $query->insert_id;
                                            if ($query->query) {
                                                $desabilitados = $this->DB_fetch_array("SELECT * FROM tb_produtos_atributos_has_conjuntos_atributos WHERE id_atributo = {$atributo['id']}");
                                                if ($desabilitados->num_rows) {
                                                    foreach ($desabilitados->rows as $desabilitado) {
                                                        $desabilitar[$idAtributo] = $desabilitado['id_conjunto'];
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            //se houver conjuntos para desabilitar
                            if (isset($desabilitar)) {
                                foreach ($desabilitar as $key => $value) {
                                    $this->DB_insert("tb_produtos_atributos_has_conjuntos_atributos", "id_atributo,id_conjunto,desabilitado", "$key,$idsConjuntos[$value],1");
                                }
                            }
                        }
                        header("Location: " . $this->system_path . $this->getModule() . "/edit/id/" . $idProduto);
                    } else {
                        echo '<script>alert("Ocorreu um erro, tente novamente mais tarde!");</script>';
                        header("Location: " . $this->system_path . $this->getModule());
                    }
                }
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
