<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCassinoMesas extends AbstractMigration
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

        $table = $this->table('tb_cassino_mesas', ['collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id_user',              'integer',      ['null'=>true])
                ->addColumn('nome',                 'string',       ['null'=>true, 'limit'=>45])
                ->addColumn('tipo',                 'string',       ['null'=>true, 'limit'=>45])
                ->addColumn('chaveAcesso',          'string',       ['null'=>true, 'limit'=>45])
                ->addColumn('rodadas_registros_json', 'text',       ['null'=>true])
                ->addColumn('rodadas_qtd_json',       'text',       ['null'=>true])
                ->addColumn('rodadas_historico_json', 'text',       ['null'=>true])
                ->addColumn('bonus_historico_json',   'text',       ['null'=>true])
                ->addColumn('stats',                'integer',      ['null'=>true])
                ->addColumn('titulo',               'string',       ['null'=>true])
                ->create();

    }
}
