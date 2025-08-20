<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MudarNomeCOlunaOrdens3 extends AbstractMigration
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
        // Garante o default de created_at como CURRENT_TIMESTAMP
        $this->execute("
            ALTER TABLE dp_ordens 
            MODIFY created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ");

        // Move deleted_at para depois de created_at
        $this->execute("
            ALTER TABLE dp_ordens 
            MODIFY deleted_at DATETIME NULL AFTER created_at
        ");
    }

    public function down()
    {
        // Reverte a ordem da coluna (ajuste conforme necessário) e remove o default
        $this->execute("
            ALTER TABLE dp_ordens 
            MODIFY created_at DATETIME NOT NULL
        ");

        $this->execute("
            ALTER TABLE dp_ordens 
            MODIFY deleted_at DATETIME NULL
        ");
    }
}
