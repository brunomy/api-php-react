<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RemoveIdDepartamentoFromDpUsers extends AbstractMigration
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

        if ($table->hasForeignKey('id_departamento')) {
            $table->dropForeignKey('id_departamento');
        }

        if ($table->hasColumn('id_departamento')) {
            $table->removeColumn('id_departamento');
        }

        $table->update();
    }
}
