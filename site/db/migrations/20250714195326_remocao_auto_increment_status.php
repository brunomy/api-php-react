<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RemocaoAutoIncrementStatus extends AbstractMigration
{
    public function up()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS = 0');

        $this->execute('ALTER TABLE dp_status MODIFY COLUMN id INT NOT NULL');
        $this->execute('DELETE FROM dp_status');

        // Força inserção com ID explícito, inclusive 0
        $this->execute("INSERT INTO dp_status (id, titulo) VALUES
            (0, 'pendente'),
            (1, 'em andamento'),
            (2, 'parado'),
            (4, 'finalizado')");
        
        $this->execute('SET FOREIGN_KEY_CHECKS = 1');
        
    }

    public function down()
    {
        $this->execute('DELETE FROM dp_status');
        $this->execute('ALTER TABLE dp_status MODIFY COLUMN id INT NOT NULL AUTO_INCREMENT');
        $this->execute('ALTER TABLE dp_status AUTO_INCREMENT = 1');
    }
}
