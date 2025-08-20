<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateProdutosAvaliacoes extends AbstractMigration
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

        $table = $this->table('tb_produtos_avaliacoes', ['collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('stats',                'integer',  ['null'=>true])
                ->addColumn('nota',                 'float',    ['null'=>true])
                ->addColumn('avaliacao',            'text',     ['null'=>true])
                ->addColumn('chamada',              'text',     ['null'=>true])
                ->addColumn('nome',                 'string',   ['null'=>true,'limit'=>250])
                ->addColumn('data',                 'timestamp',['null'=>true,'default'=>'CURRENT_TIMESTAMP'])
                ->addColumn('id_cidade',            'integer',  ['null'=>true])
                ->addColumn('id_produto',           'integer',  ['null'=>true])
                ->addColumn('id_pedido',            'integer',  ['null'=>true])
                ->addForeignKey('id_produto', 'tb_produtos_produtos', 'id', ['update'=>'NO_ACTION', 'delete'=>'SET_NULL'])
                ->addForeignKey('id_cidade', 'tb_utils_cidades', 'id', ['update'=>'NO_ACTION', 'delete'=>'SET_NULL'])
                ->create();

    }
}
