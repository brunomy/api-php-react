<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddBlingFieldsToCompaniesTable extends AbstractMigration
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
        $table  ->addColumn('invoice_bling_client_id', 'string',['null'=>true, 'after' => 'payment_cielo_environment'])
                ->addColumn('invoice_bling_client_secret', 'string',['null'=>true, 'after' => 'invoice_bling_client_id'])
                ->addColumn('invoice_bling_autorization_code', 'string',['null'=>true, 'after' => 'invoice_bling_client_secret'])
                ->addColumn('invoice_bling_refresh_token', 'string',['null'=>true, 'after' => 'invoice_bling_autorization_code'])
                ->addColumn('invoice_bling_token_expires_at', 'timestamp',['null'=>true, 'after' => 'invoice_bling_refresh_token'])
                ->update();
    }
}
