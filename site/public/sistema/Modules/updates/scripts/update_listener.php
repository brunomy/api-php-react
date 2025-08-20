<?php
    
    $file = dirname(dirname(dirname(dirname(__DIR__)))) . '/sistema/System/Core/configs.json';

    $db_config = json_decode(file_get_contents($file), true)['db'];

    $connection = mysqli_connect($db_config['host'], $db_config['user'], $db_config['password'], $db_config['database']);
    $connection->set_charset('utf8');

    if (!$connection) {
        echo "Não foi possível se conectar ao banco de dados!" . PHP_EOL;
        echo "Errno: " . mysqli_connect_errno() . PHP_EOL;
        echo "Error: " . mysqli_connect_error() . PHP_EOL;
        die();
    }
        
    $result = $connection->query("SELECT * FROM tb_system_updates WHERE status = 'waiting' LIMIT 1");

    if ($result->num_rows == 1) {
        $update = $result->fetch_assoc();

        $connection->query("UPDATE tb_system_updates SET status = 'executing' WHERE id = " . $update['id']);

        if ($connection->affected_rows != 1) {
            echo 'Não foi possível atualizar o registro! - "executing"' . PHP_EOL;
            die();
        }

        $script  = dirname(__FILE__) . '/update.sh ' . $update['commit_hash'] . ' "' . implode($db_config, ' ') . '"';

        $proc = proc_open($script, [
            '1' => ['pipe', 'w'],
            '2' => ['pipe', 'w']
        ], $pipes);

        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $proc = proc_close($proc);

        if ($proc == 0) {
            $stdout = addslashes($stdout);
            $connection->query("UPDATE tb_system_updates SET status = 'applied', applied_at = '" . date('Y-m-d H:i:s') . "', proccess_message = '{$stdout}' WHERE id = " . $update['id']);

            if ($connection->affected_rows != 1) {
                echo 'Não foi possível atualizar o registro! - "applied"' . PHP_EOL;
            }

            die();
        }

        if ($proc != 0) {
            $stderr = addslashes($stderr);
            $connection->query("UPDATE tb_system_updates SET status = 'error', proccess_message = '{$stderr}' WHERE id = " . $update['id']);

            if ($connection->affected_rows != 1) {
                echo 'Não foi possível atualizar o registro! - "error"' . PHP_EOL;
            }
        }

        die(json_encode([
            'proc_return' => $proc,
            'stdout' => $stdout,
            'stderr' => $stderr
        ]) . PHP_EOL);
    }

    die();
?>