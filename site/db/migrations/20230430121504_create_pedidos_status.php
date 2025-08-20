<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePedidosStatus extends AbstractMigration
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

        $table = $this->table('tb_pedidos_status', ['collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('nome',                 'string',       ['null'=>true, 'limit'=>150])
                ->addColumn('label',                'string',       ['null'=>true, 'limit'=>150])
                ->addColumn('assunto',              'string',       ['null'=>true, 'limit'=>150])
                ->addColumn('mensagem',             'text',         ['null'=>true])
                ->addColumn('ordem',                'integer',      ['null'=>true])
                ->addColumn('mostrar_botao_pagar',  'smallinteger', ['default'=>0])
                ->addColumn('enviar_email',         'smallinteger', ['default'=>0])
                ->addColumn('data',                 'timestamp',    ['default'=>'CURRENT_TIMESTAMP'])
                ->addColumn('cor',                  'string',       ['null'=>true, 'limit'=>45])
                ->addColumn('notificar_vendedor',   'smallinteger', ['default'=>0])
                ->create();

    }
}
