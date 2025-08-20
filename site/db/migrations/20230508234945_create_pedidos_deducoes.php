<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePedidosDeducoes extends AbstractMigration
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

        $table = $this->table('tb_pedidos_deducoes', ['id'=>false, 'collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id_pedido',            'integer',  ['null'=>true])
                ->addColumn('id_produto_carrinho',  'biginteger')
                ->addColumn('descricao',            'text',  ['null'=>true])
                ->addColumn('valor',                'float',    ['null'=>true])
                ->addColumn('data',                 'timestamp',['null'=>true,'default'=>'CURRENT_TIMESTAMP'])
                ->addForeignKey('id_produto_carrinho', 'tb_carrinho_produtos_historico', 'id', ['update'=>'RESTRICT', 'delete'=>'CASCADE'])
                ->addForeignKey('id_pedido', 'tb_pedidos_pedidos', 'id', ['update'=>'RESTRICT', 'delete'=>'CASCADE'])
                ->create();

    }
}
