<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateInstitucionalFotos extends AbstractMigration
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

        $table = $this->table('tb_institucional_fotos', ['id'=>false, 'primary_key' => ['id','id_pagina'], 'collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id',                   'biginteger', ['identity'=>true])
                ->addColumn('id_pagina',           'integer')
                ->addColumn('ordem',                'integer')
                ->addColumn('stats',                'integer')
                ->addColumn('url',                  'string')
                ->addColumn('legenda',              'string',  ['null'=>true,'limit'=>45])
                ->addForeignKey('id_pagina', 'tb_institucional_paginas', 'id', ['delete'=>'CASCADE','update'=>'CASCADE'])
                ->create();

    }
}
