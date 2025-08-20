<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCarrinhoAtributosHistorico extends AbstractMigration
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

        $table = $this->table('tb_carrinho_atributos_historico', ['id'=>false, 'primary_key' => ['id_carrinho_produto_historico','id_atributo'], 'collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id_carrinho_produto_historico',    'biginteger')
                ->addColumn('id_conjunto_atributo',             'biginteger')
                ->addColumn('id_atributo',                      'biginteger')
                ->addColumn('nome_conjunto',                    'string',       ['null'=>true])
                ->addColumn('nome_atributo',                    'string',       ['null'=>true])
                ->addColumn('custo',                            'double',       ['null'=>true])
                ->addColumn('selecionado',                      'integer',      ['null'=>true])
                ->addColumn('texto',                            'string',       ['null'=>true])
                ->addColumn('arquivo',                          'string',       ['null'=>true])
                ->addColumn('cor',                              'string',       ['null'=>true])
                ->addColumn('valor',                            'string',       ['null'=>true])
                ->addColumn('data',                             'timestamp',    ['default'=>'CURRENT_TIMESTAMP'])
                ->addForeignKey('id_carrinho_produto_historico', 'tb_carrinho_produtos_historico', 'id', ['delete'=>'CASCADE','update'=>'NO_ACTION'])
                ->create();

    }
}
