<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateProdutosProdutos extends AbstractMigration
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

        $table = $this->table('tb_produtos_produtos', ['id'=>false, 'primary_key'=>['id','id_seo'], 'collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id',                   'integer',      ['identity'=>true])
                ->addColumn('id_seo',               'integer')
                ->addColumn('ncm',                  'string',       ['null'=>true,'limit'=>45])
                ->addColumn('nome',                 'string',       ['null'=>true])
                ->addColumn('resumo',               'text',         ['null'=>true])
                ->addColumn('texto',                'text',         ['null'=>true])
                ->addColumn('texto_adicional',      'text',         ['null'=>true])
                ->addColumn('imagem',               'string',       ['null'=>true])
                ->addColumn('icone',                'string',       ['null'=>true])
                ->addColumn('custo',                'float',        ['null'=>true])
                ->addColumn('qtd_minima',           'integer',      ['null'=>true])
                ->addColumn('prazo_producao',       'integer',      ['null'=>true])
                ->addColumn('unidade_calculada',    'integer',      ['null'=>true,'default'=>1])
                ->addColumn('prazo_producao_adic',  'integer',      ['null'=>true])
                ->addColumn('porcentagem_fabrica',  'float',        ['null'=>true])
                ->addColumn('comissao_venda',       'float',        ['null'=>true])
                ->addColumn('dimensao_largura',     'float',        ['null'=>true])
                ->addColumn('dimensao_altura',      'float',        ['null'=>true])
                ->addColumn('dimensao_profundidade','float',        ['null'=>true])
                ->addColumn('peso',                 'float',        ['null'=>true])
                ->addColumn('titulo_box_frete',     'string',       ['null'=>true,'limit'=>100])
                ->addColumn('texto_box_frete',      'string',       ['null'=>true])
                ->addColumn('frete_embutido',       'float',        ['null'=>true])
                ->addColumn('titulo_itens_personalizacao','string', ['null'=>true])
                ->addColumn('id_frete_terrestre',   'integer',      ['null'=>true])
                ->addColumn('id_frete_aereo',       'integer',      ['null'=>true])
                ->addColumn('ordem',                'integer',      ['null'=>true])
                ->addColumn('stats',                'integer',      ['null'=>true])
                ->addColumn('data',                 'timestamp',    ['null'=>true,'default'=>'CURRENT_TIMESTAMP'])
                ->addColumn('apagado',              'integer',      ['null'=>true,'default'=>0])
                ->addColumn('video_desktop',        'string',       ['null'=>true,'limit'=>45])
                ->addColumn('video_mobile',         'string',       ['null'=>true,'limit'=>45])
                ->addForeignKey('id_frete_terrestre', 'tb_config_conjuntos_fretes', 'id', ['update'=>'SET_NULL', 'delete'=>'CASCADE'])
                ->addForeignKey('id_frete_aereo', 'tb_config_conjuntos_fretes', 'id', ['update'=>'SET_NULL', 'delete'=>'CASCADE'])
                ->addForeignKey('id_seo', 'tb_seo_paginas', 'id', ['update'=>'NO_ACTION', 'delete'=>'CASCADE'])
                ->create();

    }
}
