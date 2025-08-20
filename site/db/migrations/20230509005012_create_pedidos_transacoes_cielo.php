<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePedidosTransacoesCielo extends AbstractMigration
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

        $table = $this->table('tb_pedidos_transacoes_cielo', ['collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('pedido_id',                    'integer',  ['null'=>true])
                ->addColumn('checkout_cielo_order_number',  'string',   ['limit'=>32])
                ->addColumn('amount',                       'integer')
                ->addColumn('order_number',                 'string',   ['limit'=>32])
                ->addColumn('created_date',                 'string',   ['null'=>true,'limit'=>20])
                ->addColumn('customer_name',                'string',   ['null'=>true,'limit'=>289])
                ->addColumn('customer_identity',            'string',   ['null'=>true,'limit'=>14])
                ->addColumn('customer_email',               'string',   ['null'=>true,'limit'=>64])
                ->addColumn('customer_phone',               'integer',  ['null'=>true])
                ->addColumn('discount_amount',              'integer',  ['null'=>true])
                ->addColumn('shipping_type',                'integer',  ['null'=>true])
                ->addColumn('shipping_name',                'string',   ['null'=>true,'limit'=>128])
                ->addColumn('shipping_price',               'integer',  ['null'=>true])
                ->addColumn('shipping_address_zipcode',     'integer',  ['null'=>true])
                ->addColumn('shipping_address_district',    'string',   ['null'=>true,'limit'=>64])
                ->addColumn('shipping_address_city',        'string',   ['null'=>true,'limit'=>64])
                ->addColumn('shipping_address_state',       'string',   ['null'=>true,'limit'=>64])
                ->addColumn('shipping_address_line1',       'string',   ['null'=>true])
                ->addColumn('shipping_address_line2',       'string',   ['null'=>true])
                ->addColumn('shipping_address_number',      'integer',  ['null'=>true])
                ->addColumn('payment_method_type',          'integer',  ['null'=>true])
                ->addColumn('payment_method_brand',         'integer',  ['null'=>true])
                ->addColumn('payment_method_bank',          'integer',  ['null'=>true])
                ->addColumn('payment_maskedcreditcard',     'string',   ['null'=>true,'limit'=>20])
                ->addColumn('payment_installments',         'integer',  ['null'=>true])
                ->addColumn('payment_antifrauderesult',     'integer',  ['null'=>true])
                ->addColumn('payment_boletonumber',         'string',   ['null'=>true,'limit'=>150])
                ->addColumn('payment_boletoexpirationdate', 'string',   ['null'=>true,'limit'=>10])
                ->addColumn('payment_status',               'integer',  ['null'=>true])
                ->addColumn('tid',                          'string',   ['null'=>true,'limit'=>32])
                ->addForeignKey('pedido_id', 'tb_pedidos_pedidos', 'id', ['update'=>'SET_NULL', 'delete'=>'CASCADE'])
                ->addIndex('checkout_cielo_order_number',['unique'=>true])
                ->create();

    }
}
