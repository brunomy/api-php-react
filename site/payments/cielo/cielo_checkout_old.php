<?php

class CieloCheckout {

    var $_itens = array();
    var $_parametros = array();
    var $_dados_pedido = array();
    var $_dados_desconto = array();
    var $_frete = array();
    var $_consumidor = array();
    var $_antifraude = array();

    function CieloCheckout($args = array()) {

        if ('array' != gettype($args))
            $args = array();

        $default = array();

        $this->_dados_pedido = $args + $default;
    }

    function error($msg) {

        trigger_error($msg);

        return $this;
    }

    function adicionar($item) {

        $this->_itens[] = $item;

        return $this;
    }

    function dadosPedido($args = array()) {

        if ('array' !== gettype($args))
            return;

        $this->_dados_pedido = $args;
    }

    function dadosDesconto($args = array()) {

        if ('array' !== gettype($args))
            return;

        $this->_dados_desconto = $args;
    }

    function frete($args = array()) {

        if ('array' !== gettype($args))
            return;

        $this->_frete = $args;
    }

    function consumidor($args = array()) {

        if ('array' !== gettype($args))
            return;

        $this->_consumidor = $args;
    }

    function antifraude($args = array()) {

        if ('array' !== gettype($args))
            return;

        $this->_antifraude = $args;
    }

    function mostra($args = array()) {

        $this->_parametros = array();

        $_form[] = '<form name="frmCielo" target="_self" action="https://cieloecommerce.cielo.com.br/Transactional/Order/Index" method="POST">';

        foreach ($this->_dados_pedido as $key => $value) {

            $_form[] = "<input type='hidden' name='$key' value='$value' />";
            $this->_parametros[$key] = $value;
        }
        $i = 0;
        foreach ($this->_itens as $key => $item) {
            $i++;

            foreach ($item as $_key => $value) {

                $_form[] = "<input type='hidden' name='cart_" . $i . "_$_key' value='$value' />";
                $this->_parametros["cart_" . $i . "_$_key"] = $value;
            }
        }

        foreach ($this->_dados_desconto as $key => $value) {

            $_form[] = "<input type='hidden' name='$key' value='$value' />";
            $this->_parametros[$key] = $value;
        }

        foreach ($this->_frete as $key => $value) {

            $_form[] = "<input type='hidden' name='$key' value='$value' />";
            $this->_parametros[$key] = $value;
        }

        foreach ($this->_consumidor as $key => $value) {

            $_form[] = "<input type='hidden' name='$key' value='$value' />";
            $this->_parametros[$key] = $value;
        }

        foreach ($this->_antifraude as $key => $value) {

            $_form[] = "<input type='hidden' name='$key' value='" . (($value==true)?'TRUE':'FALSE') . "' />";
            $this->_parametros[$key] = $value;
        }


        $_form[] = '  <input type="submit" value="Pagar"  />';

        $_form[] = '</form>';

        $return = implode("\n", $_form);

        return $return;
    }

}
?>

