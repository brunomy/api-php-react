<?php
session_start();

require_once '../sistema/System/Core/Loader.php';

use System\Core\Bootstrap;

require_once "../_system.php";

$sistema = new _sys();

$sistema->DB_connect();

$id = "";
if (isset($_GET['id']))
    $id = $_GET['id'];

if ($id != "") {
    $query = $sistema->DB_fetch_array("SELECT * FROM tb_utils_cidades WHERE id_estado = $id");
    if ($query->num_rows) {
        ?> 
        <option value="">Escolha sua cidade...</option>
        <?php 
        foreach ($query->rows as $row) {
            ?>
            <option data-cidade="<?php echo $row['cidade']; ?>" value="<?php echo $row['id']; ?>"><?php echo $row['cidade']; ?></option>
            <?php
        }
    }
}
?>
<script>
/*
    $('.cidade option[value="' + cidade + '"]').prop("selected", true);
    $('.cidade option').each(function () {
        if ($(this).attr("data-cidade") == resultadoCEP.cidade) {
            $(this).prop("selected", true);
        }
    });
*/
</script>