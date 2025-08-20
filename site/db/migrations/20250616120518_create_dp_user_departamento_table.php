<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateDpUserDepartamentoTable extends AbstractMigration
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
        // $table = $this->table('dp_user_departamento');
        
        // $table
        //     ->addColumn('id_departamento', 'integer')
        //     ->addColumn('id_user', 'integer')
        //     ->addForeignKey('id_departamento', 'dp_departamentos', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
        //     ->addForeignKey('id_user', 'dp_users', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
        //     ->create();
    }
}
