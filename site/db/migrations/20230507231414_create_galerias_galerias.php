<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateGaleriasGalerias extends AbstractMigration
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

        $table = $this->table('tb_galerias_galerias', ['collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id_seo',       'integer',  ['null'=>true])
                ->addColumn('ordem',        'integer',  ['null'=>true])
                ->addColumn('stats',        'integer',  ['null'=>true])
                ->addColumn('data',         'date',     ['null'=>true])
                ->addColumn('titulo',       'string',   ['null'=>true,'limit'=>100])
                ->addColumn('cor',          'string',   ['null'=>true,'limit'=>45])
                ->addColumn('imagem',       'string',   ['null'=>true,'limit'=>100])
                ->create();

    }
}
