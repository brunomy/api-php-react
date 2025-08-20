<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateConfigCidades extends AbstractMigration
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

        $table = $this->table('tb_config_cidades', ['collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id_estado',            'integer')
                ->addColumn('nome',                 'string',   ['null'=>true,'limit'=>60])
                ->addColumn('cep_init',             'integer',  ['null'=>true])
                ->addColumn('cep_end',              'integer',  ['null'=>true])
                ->addColumn('capital',              'integer',  ['null'=>true, 'default'=>0])
                ->addColumn('data',                 'timestamp',['null'=>true, 'default'=>'CURRENT_TIMESTAMP'])
                ->addForeignKey('id_estado', 'tb_config_estados', 'id', ['delete'=>'RESTRICT','update'=>'RESTRICT'])
                ->create();

    }
}
