<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateDashboardsPaginas extends AbstractMigration
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

        $table = $this->table('tb_dashboards_paginas', ['id'=>false, 'collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id_seo',               'integer',  ['null'=>true])
                ->addColumn('qtd',                  'integer',  ['null'=>true])
                ->addColumn('date',                 'date',     ['null'=>true])
                ->create();

    }
}
