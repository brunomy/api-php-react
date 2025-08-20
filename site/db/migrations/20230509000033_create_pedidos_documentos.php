<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePedidosDocumentos extends AbstractMigration
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

        $table = $this->table('tb_pedidos_documentos', ['collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id_pedido',            'integer')
                ->addColumn('cartao_frente',        'string',   ['null'=>true])
                ->addColumn('cartao_verso',         'string',   ['null'=>true])
                ->addColumn('documento_frente',     'string',   ['null'=>true])
                ->addColumn('documento_verso',      'string',   ['null'=>true])
                ->addColumn('selfie',               'string',   ['null'=>true])
                ->addColumn('created_at',           'timestamp',['null'=>true,'default'=>'CURRENT_TIMESTAMP'])
                ->addColumn('default',              'integer',  ['null'=>true,'default'=>0])
                ->addForeignKey('id_pedido', 'tb_pedidos_pedidos', 'id', ['update'=>'RESTRICT', 'delete'=>'RESTRICT'])
                ->create();

    }
}
