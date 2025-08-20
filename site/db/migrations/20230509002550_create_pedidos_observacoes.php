<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class CreatePedidosObservacoes extends AbstractMigration
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

        $table = $this->table('tb_pedidos_observacoes', ['collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id_pedido',            'integer')
                ->addColumn('criado_por',           'string',   ['null'=>true,'limit'=>45])
                ->addColumn('deletado_por',         'string',   ['null'=>true,'limit'=>45])
                ->addColumn('ativo',                'integer',  ['limit' => MysqlAdapter::INT_TINY,'default'=>1])
                ->addColumn('categoria',            'string',   ['limit'=>45])
                ->addColumn('texto',                'text',     ['null'=>true,'limit' => MysqlAdapter::TEXT_LONG])
                ->addColumn('data',                 'timestamp',['null'=>true,'default'=>'CURRENT_TIMESTAMP'])
                ->addForeignKey('id_pedido', 'tb_pedidos_pedidos', 'id', ['update'=>'RESTRICT', 'delete'=>'CASCADE'])
                ->create();

    }
}
