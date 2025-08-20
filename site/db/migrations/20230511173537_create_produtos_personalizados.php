<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateProdutosPersonalizados extends AbstractMigration
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

        $table = $this->table('tb_produtos_personalizados', ['id'=>false, 'primary_key'=>['id','id_produto','id_seo'],'collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id',                   'integer',  ['identity'=>true])
                ->addColumn('id_produto',           'integer')
                ->addColumn('id_seo',               'integer')
                ->addColumn('nome',                 'string',   ['null'=>true])
                ->addColumn('resumo',               'text',     ['null'=>true])
                ->addColumn('texto',                'text',     ['null'=>true])
                ->addColumn('imagem',               'string',   ['null'=>true])
                ->addColumn('googleshop_img',       'string',   ['null'=>true])
                ->addColumn('ordem',                'integer',  ['null'=>true])
                ->addColumn('stats',                'integer',  ['null'=>true])
                ->addColumn('data',                 'timestamp',['null'=>true,'default'=>'CURRENT_TIMESTAMP'])
                ->addColumn('apagado',              'integer',  ['null'=>true,'default'=>0])
                ->addForeignKey('id_produto', 'tb_produtos_produtos', 'id', ['update'=>'NO_ACTION', 'delete'=>'NO_ACTION'])
                ->addForeignKey('id_seo', 'tb_seo_paginas', 'id', ['update'=>'NO_ACTION', 'delete'=>'NO_ACTION'])
                ->addIndex(['apagado','id','id_produto','id_seo','stats'])
                ->create();

    }
}
