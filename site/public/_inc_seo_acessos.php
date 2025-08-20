<?php

if (isset($currentpage)) {

    if ($sistema->detect->isTablet()) {
        $dispositivo = 2;
    } else if ($sistema->detect->isMobile()) {
        $dispositivo = 3;
    } else {
        $dispositivo = 1;
    }

    if (isset($_SERVER['HTTP_REFERER'])) {
        $query = $sistema->DB_insert("tb_seo_acessos", "id_seo,date,ip,session,browser,origem,utm_source,utm_medium,utm_campaign,utm_content,utm_term,dispositivo", $currentpage['id_seo'] . ",NOW(),'" . $_SERVER['REMOTE_ADDR'] . "','" . $_SESSION["seo_session"] . "','" . $_SERVER['HTTP_USER_AGENT'] . "','" . $_SERVER['HTTP_REFERER'] . "','" . $utm_source . "','" . $utm_medium . "','" . $utm_campaign . "','" . $utm_content . "','" . $utm_term . "','" . $dispositivo . "'");
        $id_seo_inserted = $sistema->DB_last_inserted_id();
        //$sistema->DB_insert("tb_seo_acessos_historicos", "id,id_seo,date,ip,session,browser,origem,utm_source,utm_medium,utm_campaign,utm_content,utm_term,dispositivo", $id_seo_inserted.",".$currentpage['id_seo'] . ",NOW(),'" . $_SERVER['REMOTE_ADDR'] . "','" . $_SESSION["seo_session"] . "','" . $_SERVER['HTTP_USER_AGENT'] . "','" . $_SERVER['HTTP_REFERER'] . "','" . $utm_source . "','" . $utm_medium . "','" . $utm_campaign . "','" . $utm_content . "','" . $utm_term . "','" . $dispositivo . "'");
    } else {
        $query = $sistema->DB_insert("tb_seo_acessos", "id_seo,date,ip,session,browser,utm_source,utm_medium,utm_campaign,utm_content,utm_term,dispositivo", $currentpage['id_seo'] . ",NOW(),'" . $_SERVER['REMOTE_ADDR'] . "','" . $_SESSION["seo_session"] . "','" . $_SERVER['HTTP_USER_AGENT'] . "','" . $utm_source . "','" . $utm_medium . "','" . $utm_campaign . "','" . $utm_content . "','" . $utm_term . "','" . $dispositivo . "'");
        $id_seo_inserted = $sistema->DB_last_inserted_id();
        //$sistema->DB_insert("tb_seo_acessos_historicos", "id,id_seo,date,ip,session,browser,utm_source,utm_medium,utm_campaign,utm_content,utm_term,dispositivo", $id_seo_inserted.",".$currentpage['id_seo'] . ",NOW(),'" . $_SERVER['REMOTE_ADDR'] . "','" . $_SESSION["seo_session"] . "','" . $_SERVER['HTTP_USER_AGENT'] . "','" . $utm_source . "','" . $utm_medium . "','" . $utm_campaign . "','" . $utm_content . "','" . $utm_term . "','" . $dispositivo . "'");
    }

    if (!isset($_SESSION['localidade_analytics'])) :
        ?>
        <script>
            var idSeo = "<?php echo $query->insert_id; ?>";
            var ip = "<?php echo $_SERVER['REMOTE_ADDR']; ?>";
            var session = "<?php echo $_SESSION["seo_session"]; ?>";
        </script>
        <?php
    endif;
}
?>
