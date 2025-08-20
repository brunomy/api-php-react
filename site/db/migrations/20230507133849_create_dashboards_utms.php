<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateDashboardsUtms extends AbstractMigration
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

        $table = $this->table('tb_dashboards_utms', ['id'=>false, 'collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('date',                 'date',     ['null'=>true])
                ->addColumn('utm_source',           'string',   ['null'=>true])
                ->addColumn('utm_medium',           'string',   ['null'=>true])
                ->addColumn('utm_term',             'string',   ['null'=>true])
                ->addColumn('utm_content',          'string',   ['null'=>true])
                ->addColumn('utm_campaign',         'string',   ['null'=>true])
                ->addColumn('visitas',              'biginteger',['default'=>0])
                ->addColumn('cadastros',            'integer',  ['null'=>true])
                ->addColumn('contatos',             'integer',  ['null'=>true])
                ->addColumn('compras',              'integer',  ['null'=>true])
                ->addColumn('faturados',            'integer',  ['null'=>true])
                ->addColumn('faturamentos',          'float',    ['null'=>true])
                ->create();

    }
}
