<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class DataSaidaRemessa extends AbstractMigration
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
    public function change(): void
    {
        $this->execute("
            ALTER TABLE dp_remessas
                MODIFY entrega DATE NOT NULL AFTER n_nota,
                ADD COLUMN data_saida DATE NULL DEFAULT NULL AFTER entrega
        ");
    }
}
