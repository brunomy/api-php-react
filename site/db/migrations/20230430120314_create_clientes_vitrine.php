<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateClientesVitrine extends AbstractMigration
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

        $table = $this->table('tb_clientes_vitrine', ['collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('nome',                 'string',  ['null'=>true, 'limit'=>100])
                ->addColumn('imagem',               'string',  ['null'=>true])
                ->addColumn('link',                 'string',  ['null'=>true])
                ->addColumn('target',               'string',  ['null'=>true, 'limit'=>20])
                ->addColumn('ordem',                'integer', ['null'=>true])
                ->addColumn('stats',                'smallinteger',['null'=>true, 'limit'=>1])
                ->addColumn('data',                 'timestamp',  ['default'=>'CURRENT_TIMESTAMP'])
                ->create();

    }
}
