<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSeoHistoricos extends AbstractMigration
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

        $table = $this->table('tb_seo_acessos_historicos', ['id'=>false, 'primary_key' => ['id','id_seo'], 'collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id',                   'biginteger', ['identity'=>true])
                ->addColumn('id_seo',               'integer')
                ->addColumn('date',                 'datetime',['null'=>true])
                ->addColumn('ip',                   'string',  ['null'=>true, 'limit'=>45])
                ->addColumn('session',              'string',  ['null'=>true, 'limit'=>45])
                ->addColumn('browser',              'text',    ['null'=>true])
                ->addColumn('origem',               'string',  ['null'=>true])
                ->addColumn('pais',                 'string',  ['null'=>true, 'limit'=>128])
                ->addColumn('estado',               'string',  ['null'=>true, 'limit'=>45])
                ->addColumn('cidade',               'string',  ['null'=>true, 'limit'=>128])
                ->addColumn('utm_source',           'string',  ['null'=>true])
                ->addColumn('utm_medium',           'string',  ['null'=>true])
                ->addColumn('utm_term',             'string',  ['null'=>true])
                ->addColumn('utm_content',          'string',  ['null'=>true])
                ->addColumn('utm_campaign',         'string',  ['null'=>true])
                ->addColumn('dispositivo',          'integer', ['null'=>true])
                ->addColumn('cadastro',             'integer', ['null'=>true])
                ->addColumn('contato',              'integer', ['null'=>true])
                ->addColumn('compra',               'integer', ['null'=>true])
                ->addColumn('faturado',             'integer', ['null'=>true])
                ->addColumn('faturamento',          'float',   ['null'=>true])
                ->addIndex('id_seo')
                ->addIndex('session', ['name'=>'idx_1'])
                ->create();

    }
}
