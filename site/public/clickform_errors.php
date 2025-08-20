<?php 
        
        $erro = $_REQUEST['erro'];

        $data = date("d-m-Y");
        $hora = date("H:i:s");
        $ip = $_SERVER['REMOTE_ADDR'];

        //Nome do arquivo:
        $arquivo = "clickform_errors.txt";

        //Texto a ser impresso no log:
        $texto = "[$ip][$data][$hora] : $erro
";

        $manipular = fopen("$arquivo", "a+b");
        fwrite($manipular, $texto);
        fclose($manipular);

?>