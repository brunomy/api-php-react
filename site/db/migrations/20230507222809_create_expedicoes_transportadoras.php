<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateExpedicoesTransportadoras extends AbstractMigration
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

        $table = $this->table('tb_expedicoes_transportadoras', ['collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('nome',                 'string',   ['limit'=>45])
                ->addColumn('atendente',            'string',   ['null'=>true, 'limit'=>45])
                ->addColumn('telefone_atendente',   'string',   ['null'=>true, 'limit'=>45])
                ->addColumn('email_atendente',      'string',   ['null'=>true, 'limit'=>45])
                ->addColumn('gerente',              'string',   ['null'=>true, 'limit'=>45])
                ->addColumn('telefone_gerente',     'string',   ['null'=>true, 'limit'=>45])
                ->addColumn('email_gerente',        'string',   ['null'=>true, 'limit'=>45])
                ->create();

    }
}
