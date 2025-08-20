<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePedidosDesistencias extends AbstractMigration
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

        $table = $this->table('tb_pedidos_desistencias', ['collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id_pedido',            'integer',  ['null'=>true])
                ->addColumn('id_motivo',            'integer')
                ->addForeignKey('id_motivo', 'tb_pedidos_motivos_desistencia', 'id', ['update'=>'RESTRICT', 'delete'=>'RESTRICT'])
                ->addForeignKey('id_pedido', 'tb_pedidos_pedidos', 'id', ['update'=>'RESTRICT', 'delete'=>'RESTRICT'])
                ->create();

    }
}
