<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePedidosStatusHasStatusUserNotification extends AbstractMigration
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

        $table = $this->table('tb_pedidos_status_has_users_notification', ['id'=>false, 'primary_key'=>['id_pedido_status','id_usuario'], 'collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id_pedido_status',         'integer')
                ->addColumn('id_usuario',               'integer')
                ->addForeignKey('id_pedido_status', 'tb_pedidos_status', 'id', ['update'=>'RESTRICT', 'delete'=>'RESTRICT'])
                ->addForeignKey('id_usuario', 'tb_admin_users', 'id', ['update'=>'RESTRICT', 'delete'=>'RESTRICT'])
                ->addIndex('id_pedido_status')
                ->create();

    }
}
