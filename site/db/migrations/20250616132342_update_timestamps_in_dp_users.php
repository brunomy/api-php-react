<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateTimestampsInDpUsers extends AbstractMigration
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
        $table = $this->table('dp_users');

        // Alterar created_at para default CURRENT_TIMESTAMP
        if ($table->hasColumn('created_at')) {
            $table->changeColumn('created_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'null' => false
            ]);
        }

        // Alterar deleted_at para aceitar NULL e default NULL
        if ($table->hasColumn('deleted_at')) {
            $table->changeColumn('deleted_at', 'datetime', [
                'null' => true,
                'default' => null
            ]);
        }

        $table->update();
    }
}
