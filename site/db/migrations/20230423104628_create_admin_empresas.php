<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateAdminEmpresas extends AbstractMigration
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

        $table = $this->table('tb_admin_empresas', ['collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('razao_social',             'string',       ['null'=>true])
                ->addColumn('nome_fantasia',            'string',       ['null'=>true])
                ->addColumn('cnpj',                     'string',       ['null'=>true])
                ->addColumn('payment_rede_token',       'string',       ['null'=>true])
                ->addColumn('payment_rede_store_id',    'string',       ['null'=>true])
                ->addColumn('payment_rede_environment', 'string',       ['null'=>true])
                ->addColumn('stats',                    'smallinteger', ['null'=>true])
                ->create();

    }
}
