<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Table\Column;

final class CreateCarrinhoProdutoHistorico extends AbstractMigration
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

        $table = $this->table('tb_carrinho_produtos_historico', ['id'=>false, 'primary_key' => 'id','collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn($column)
                ->addColumn('id_pedido',            'integer',      ['null'=>true])
                ->addColumn('id_personalizado',     'integer',      ['null'=>true])
                ->addColumn('id_produto',           'integer',      ['null'=>true])
                ->addColumn('id_seo',               'integer')
                ->addColumn('nome_produto',         'string',       ['null'=>true])
                ->addColumn('session',              'string',       ['null'=>true, 'limit'=>30])
                ->addColumn('custo',                'double',       ['null'=>true])
                ->addColumn('valor_produto',        'double',       ['null'=>true])
                ->addColumn('valor_editado',        'double',       ['null'=>true])
                ->addColumn('quantidade',           'integer',      ['null'=>true])
                ->addColumn('desconto',             'double',       ['null'=>true])
                ->addColumn('desconto_fabrica',     'double',       ['null'=>true])
                ->addColumn('descricao_desconto',   'string',       ['null'=>true])
                ->addColumn('peso',                 'float',        ['null'=>true])
                ->addColumn('observacao',           'text',         ['null'=>true])
                ->addColumn('foto_final',           'string',       ['null'=>true])
                ->addColumn('mesa',                 'integer',      ['null'=>true])
                ->addColumn('data',                 'timestamp',    ['default'=>'CURRENT_TIMESTAMP'])
                ->addColumn('frete_embutido',       'float',        ['null'=>true])
                ->addForeignKey('id_pedido', 'tb_pedidos_pedidos', 'id', ['delete'=>'SET_NULL','update'=>'SET_NULL'])
                ->addIndex(['id', 'id_produto', 'id_seo', 'session'], ['name' => 'idx_1'])
                ->addIndex('session', ['name' => 'idx_2'])
                ->addIndex(['id', 'id_produto', 'session'], ['name' => 'idx_3'])
                ->create();

    }
}
