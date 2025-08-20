<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePedidosEnderecos extends AbstractMigration
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

        $table = $this->table('tb_pedidos_enderecos', ['id'=>false, 'primary_key' => ['id','id_pedido','id_cidade'], 'collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id',                   'integer',  ['identity'=>true])
                ->addColumn('id_pedido',            'integer')
                ->addColumn('id_cidade',            'integer')
                ->addColumn('cep',                  'string',   ['null'=>true,'limit'=>45])
                ->addColumn('endereco',             'string',   ['null'=>true])
                ->addColumn('numero',               'string',   ['null'=>true,'limit'=>45])
                ->addColumn('bairro',               'string',   ['null'=>true,'limit'=>150])
                ->addColumn('complemento',          'string',   ['null'=>true])
                ->addColumn('data',                 'timestamp',['null'=>true,'default'=>'CURRENT_TIMESTAMP'])
                ->addForeignKey('id_pedido', 'tb_pedidos_pedidos', 'id', ['update'=>'RESTRICT', 'delete'=>'CASCADE'])
                ->addForeignKey('id_cidade', 'tb_utils_cidades', 'id', ['update'=>'RESTRICT', 'delete'=>'RESTRICT'])
                ->create();

    }
}
