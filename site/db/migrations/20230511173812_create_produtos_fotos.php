<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateProdutosFotos extends AbstractMigration
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

        $table = $this->table('tb_produtos_fotos', ['id'=>false, 'primary_key'=>['id','id_produto'],'collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id',                   'integer',  ['identity'=>true])
                ->addColumn('id_produto',           'integer')
                ->addColumn('url',                  'string',   ['null'=>true])
                ->addColumn('legenda',              'string',   ['null'=>true,'limit'=>100])
                ->addColumn('ordem',                'integer',  ['null'=>true])
                ->addColumn('stats',                'integer',  ['null'=>true])
                ->addColumn('data',                 'timestamp',['null'=>true,'default'=>'CURRENT_TIMESTAMP'])
                ->addForeignKey('id_produto', 'tb_produtos_produtos', 'id', ['update'=>'NO_ACTION', 'delete'=>'NO_ACTION'])
                ->create();

    }
}
