<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateLinksLinks extends AbstractMigration
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

        $table = $this->table('tb_links_links', ['collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id_pai',           'integer',  ['null'=>true])
                ->addColumn('nome',             'string',   ['null'=>true,'limit'=>45])
                ->addColumn('link',             'string',   ['null'=>true])
                ->addColumn('target',           'string',   ['null'=>true,'limit'=>20])
                ->addColumn('imagem',           'string',   ['null'=>true])
                ->addColumn('menu_aberto',      'integer',  ['null'=>true,'default'=>0])
                ->addColumn('ordem',            'integer',  ['null'=>true])
                ->addColumn('stats',            'integer',  ['null'=>true])
                ->addColumn('data',             'timestamp',['null'=>true,'default'=>'CURRENT_TIMESTAMP'])
                ->addForeignKey('id_pai', 'tb_links_links', 'id', ['update'=>'CASCADE', 'delete'=>'SET_NULL'])
                ->create();

    }
}
