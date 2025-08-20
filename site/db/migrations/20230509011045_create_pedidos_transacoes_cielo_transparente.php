<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePedidosTransacoesCieloTransparente extends AbstractMigration
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

        $table = $this->table('tb_pedidos_transacoes_cielo_transparente', ['collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id_pedido',                    'integer',  ['null'=>true])
                ->addColumn('date',                         'timestamp',['null'=>true,'default'=>'CURRENT_TIMESTAMP'])
                ->addColumn('Tid',                          'string',   ['null'=>true])
                ->addColumn('MerchantOrderId',              'string',   ['null'=>true])
                ->addColumn('PaymentId',                    'string',   ['null'=>true])
                ->addColumn('Amount',                       'integer',  ['null'=>true])
                ->addColumn('Installments',                 'integer',  ['null'=>true])
                ->addColumn('Provider',                     'string',   ['null'=>true])
                ->addColumn('ReturnCode',                   'string',   ['null'=>true])
                ->addColumn('ReturnMessage',                'string',   ['null'=>true])
                ->addColumn('Status',                       'string',   ['null'=>true])
                ->addColumn('CardNumber',                   'string',   ['null'=>true])
                ->addColumn('Holder',                       'string',   ['null'=>true])
                ->addColumn('ExpirationDate',               'string',   ['null'=>true])
                ->addColumn('Brand',                        'string',   ['null'=>true])
                ->addForeignKey('id_pedido', 'tb_pedidos_pedidos', 'id', ['update'=>'CASCADE', 'delete'=>'CASCADE'])
                ->create();

    }
}
