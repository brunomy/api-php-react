<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateProdutosAtributos extends AbstractMigration
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

        $table = $this->table('tb_produtos_atributos', ['id'=>false, 'primary_key'=>['id','id_conjunto_atributo'],'collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id',                   'biginteger',['identity'=>true])
                ->addColumn('id_conjunto_atributo', 'biginteger')
                ->addColumn('id_tipo',              'integer',  ['null'=>true])
                ->addColumn('imagem',               'string',   ['null'=>true])
                ->addColumn('nome',                 'string',   ['null'=>true,'limit'=>100])
                ->addColumn('descricao',            'string',   ['null'=>true])
                ->addColumn('custo',                'float',    ['null'=>true])
                ->addColumn('selecionado',          'integer',  ['null'=>true,'default'=>0])
                ->addColumn('ampliar_fotos',        'integer',  ['null'=>true,'default'=>0])
                ->addColumn('ordem',                'integer',  ['null'=>true])
                ->addColumn('data',                 'timestamp',['null'=>true,'default'=>'CURRENT_TIMESTAMP'])
                ->addForeignKey('id_tipo', 'tb_produtos_atributos_tipos', 'id', ['update'=>'SET_NULL', 'delete'=>'NO_ACTION'])
                ->addForeignKey('id_conjunto_atributo', 'tb_produtos_conjuntos_atributos', 'id', ['update'=>'NO_ACTION', 'delete'=>'CASCADE'])
                ->create();

    }
}
