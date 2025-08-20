<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddCieloTokenToEmpresas extends AbstractMigration
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
        $table = $this->table('tb_admin_empresas');
        $table  ->addColumn('payment_cielo_merchant_id', 'string',   ['null'=>true,'after'=>'payment_rede_environment'])
                ->addColumn('payment_cielo_merchant_key', 'string',   ['null'=>true,'after'=>'payment_cielo_merchant_id'])
                ->addColumn('payment_cielo_environment', 'string',   ['null'=>true,'after'=>'payment_cielo_merchant_key'])
                ->addColumn('servico_pagamento_padrao', 'string',   ['null'=>true,'after'=>'cnpj'])
                ->update();
    }
}
