<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateGaleriasFotos extends AbstractMigration
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

        $table = $this->table('tb_galerias_fotos', ['id'=>false, 'primary_key' => ['id','id_galeria'], 'collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id',                   'biginteger', ['identity'=>true])
                ->addColumn('id_galeria',           'integer')
                ->addColumn('ordem',                'integer', ['null'=>true])
                ->addColumn('stats',                'integer', ['null'=>true])
                ->addColumn('url',                  'string',  ['null'=>true])
                ->addColumn('legenda',              'string',  ['null'=>true,'limit'=>100])
                ->addForeignKey('id_galeria', 'tb_galerias_galerias', 'id', ['delete'=>'CASCADE','update'=>'CASCADE'])
                ->create();

    }
}
