<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateBannersAvulsos extends AbstractMigration
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

        $table = $this->table('tb_banners_avulsos', ['collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('titulo',   'string',  ['null'=>true])
                ->addColumn('imagem1',  'string',  ['null'=>true])
                ->addColumn('link1',    'string',  ['null'=>true])
                ->addColumn('target1',  'string',  ['null'=>true, 'limit'=>20])
                ->addColumn('imagem2',  'string',  ['null'=>true])
                ->addColumn('link2',    'string',  ['null'=>true])
                ->addColumn('target2',  'string',  ['null'=>true, 'limit'=>20])
                ->addColumn('stats',    'integer',  ['null'=>true])
                ->addColumn('data',     'timestamp',  ['default'=>'CURRENT_TIMESTAMP'])
                ->create();

    }
}
