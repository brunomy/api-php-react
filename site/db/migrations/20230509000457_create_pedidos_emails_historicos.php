<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePedidosEmailsHistoricos extends AbstractMigration
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

        $table = $this->table('tb_pedidos_emails_historicos', ['id'=>false, 'collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id_pedido',            'integer')
                ->addColumn('alvo',                 'string',   ['null'=>true,'limit'=>60])
                ->addColumn('nome',                 'string',   ['null'=>true])
                ->addColumn('email',                'string',   ['null'=>true])
                ->addColumn('link',                 'string',   ['null'=>true])
                ->addColumn('usuario',              'string',   ['null'=>true])
                ->addColumn('data',                 'timestamp',['null'=>true,'default'=>'CURRENT_TIMESTAMP'])
                ->addForeignKey('id_pedido', 'tb_pedidos_pedidos', 'id', ['update'=>'RESTRICT', 'delete'=>'CASCADE'])
                ->create();

    }
}
