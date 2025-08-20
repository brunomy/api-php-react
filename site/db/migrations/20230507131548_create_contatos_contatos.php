<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateContatosContatos extends AbstractMigration
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

        $table = $this->table('tb_contatos_contatos', ['collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('nome',                 'string',  ['null'=>true, 'limit'=>45])
                ->addColumn('email',                'string',  ['null'=>true, 'limit'=>45])
                ->addColumn('fone',                 'string',  ['null'=>true, 'limit'=>45])
                ->addColumn('mensagem',             'text',    ['null'=>true])
                ->addColumn('ip',                   'string',  ['null'=>true, 'limit'=>45])
                ->addColumn('data',                 'timestamp',['null'=>true, 'default'=>'CURRENT_TIMESTAMP'])
                ->addColumn('assunto',              'string',  ['null'=>true, 'limit'=>45])
                ->addColumn('empresa',              'string',  ['null'=>true, 'limit'=>45])
                ->addColumn('session',              'string',  ['null'=>true, 'limit'=>50])
                ->addColumn('id_seo',               'integer', ['null'=>true])
                ->addColumn('celular',              'string',  ['null'=>true, 'limit'=>45])
                ->addForeignKey('id_seo', 'tb_seo_paginas', 'id', ['delete'=>'SET_NULL','update'=>'SET_NULL'])
                ->create();

    }
}
