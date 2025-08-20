<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class CreateSeoPaginas extends AbstractMigration
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

        $table = $this->table('tb_seo_paginas', ['collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('seo_title',            'string',  ['null'=>true, 'limit'=>128])
                ->addColumn('seo_description',      'string',  ['null'=>true])
                ->addColumn('seo_keywords',         'string',  ['null'=>true, 'limit'=>128])
                ->addColumn('seo_url',              'string',  ['null'=>true])
                ->addColumn('seo_url_breadcrumbs',  'string',  ['null'=>true])
                ->addColumn('seo_pagina',           'string',  ['null'=>true, 'limit'=>45])
                ->addColumn('seo_pagina_dinamica',  'integer', ['null'=>true])
                ->addColumn('seo_pagina_referencia','string',  ['null'=>true, 'limit'=>45])
                ->addColumn('seo_pagina_conteudo',  'text',  ['null'=>true, 'limit' => MysqlAdapter::TEXT_MEDIUM])
                ->addColumn('seo_scripts',          'text',    ['null'=>true])
                ->addIndex(['id', 'seo_pagina'], ['name' => 'idx_1'])
                ->addIndex('seo_url', ['name' => 'idx_2'])
                ->addIndex('seo_url_breadcrumbs', ['name' => 'idx_3'])
                ->create();

    }
}
