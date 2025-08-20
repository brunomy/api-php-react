<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Table\Column;

final class CreateAdminLogs extends AbstractMigration
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
        $column = new Column();
        $column->setName('id')
                    ->setType('biginteger')
                    ->setIdentity(true);

        $table = $this->table('tb_admin_logs', ['id'=>false, 'primary_key' => 'id','collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn($column)
                ->addColumn('usuario',      'string',       ['null'=>true, 'limit'=>45])
                ->addColumn('atividade',    'text',         ['null'=>true])
                ->addColumn('date',         'datetime',     ['null'=>true])
                ->addColumn('id_usuario',   'integer',      ['null'=>true])
                ->addForeignKey('id_usuario', 'tb_admin_users', 'id', ['delete'=>'CASCADE','update'=>'SET_NULL'])
                ->create();

    }
}
