<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class CreateInstitucionalPaginas extends AbstractMigration
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

        $table = $this->table('tb_institucional_paginas', ['id'=>false, 'primary_key' => ['id','id_categoria'], 'collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id',                       'integer',  ['identity'=>true])
                ->addColumn('id_categoria',             'integer')
                ->addColumn('id_seo',                   'integer',  ['null'=>true])
                ->addColumn('ordem',                    'integer')
                ->addColumn('stats',                    'integer')
                ->addColumn('nome',                     'string')
                ->addColumn('texto',                    'text',     ['limit' => MysqlAdapter::TEXT_MEDIUM])
                ->addColumn('imagem',                   'string',   ['null'=>true])
                ->addColumn('data',                     'date',     ['null'=>true])
                ->addColumn('resumo',                   'text',     ['null'=>true])
                ->addColumn('listar_produtos',          'integer',  ['null'=>true,'default'=>0])
                ->addColumn('lista_produtos',           'string',   ['null'=>true])
                ->addForeignKey('id_categoria', 'tb_institucional_categorias', 'id', ['update'=>'CASCADE', 'delete'=>'RESTRICT'])
                ->addIndex('id_seo')
                ->create();

    }
}
