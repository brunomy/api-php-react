<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class StatusPendenteId0 extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function up()
    {
        // Desativa temporariamente a verificação de foreign key
        $this->execute('SET FOREIGN_KEY_CHECKS=0');

        // Altera o id atual de 1 para 0
        $this->execute("UPDATE dp_status SET id = 0 WHERE id = 1");

        // Define o próximo AUTO_INCREMENT para 1
        $this->execute("ALTER TABLE dp_status AUTO_INCREMENT = 1");

        // Reativa as foreign keys
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        // Desfaz as alterações se necessário
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        $this->execute("UPDATE dp_status SET id = 1 WHERE id = 0");
        $this->execute("ALTER TABLE dp_status AUTO_INCREMENT = 2");
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }
}
