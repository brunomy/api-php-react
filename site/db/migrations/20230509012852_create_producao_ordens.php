<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateProducaoOrdens extends AbstractMigration
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

        $table = $this->table('tb_producao_ordens', ['collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id_carrinho_produto',  'biginteger', ['null'=>true])
                ->addColumn('id_atributo',          'biginteger', ['null'=>true])
                ->addColumn('id_user',              'integer')
                ->addColumn('id_servico',           'integer', ['null'=>true])
                ->addColumn('unidade',              'integer', ['default'=>1])
                ->addColumn('servico',              'string',  ['null'=>true])
                ->addColumn('tempo',                'integer', ['null'=>true])
                ->addColumn('ordem',                'integer', ['null'=>true])
                ->addColumn('date',                 'date',    ['null'=>true])
                ->addColumn('concluido',            'integer', ['null'=>true])
                ->addForeignKey('id_user', 'tb_admin_users', 'id', ['update'=>'RESTRICT', 'delete'=>'RESTRICT'])
                ->addForeignKey(['id_carrinho_produto','id_atributo'], 'tb_carrinho_atributos_historico', ['id_carrinho_produto_historico','id_atributo'], ['update'=>'RESTRICT', 'delete'=>'RESTRICT'])
                ->addForeignKey('id_servico', 'tb_producao_servicos', 'id', ['update'=>'RESTRICT', 'delete'=>'RESTRICT'])
                ->create();

    }
}
