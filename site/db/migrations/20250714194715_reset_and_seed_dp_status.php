<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ResetAndSeedDpStatus extends AbstractMigration
{
    public function up()
    {
        // Remove os dados e reseta o AUTO_INCREMENT
        $this->execute('DELETE FROM dp_status');
        $this->execute('ALTER TABLE dp_status AUTO_INCREMENT = 0');

        // Insere os registros
        $this->execute("INSERT INTO dp_status (id, titulo) VALUES
            (0, 'pendente'),
            (1, 'em andamento'),
            (2, 'parado'),
            (4, 'finalizado')");
    }

    public function down()
    {
        $this->execute('DELETE FROM dp_status');
        $this->execute('ALTER TABLE dp_status AUTO_INCREMENT = 0');
    }
}
