<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateIptablesBlacklists extends AbstractMigration
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

        $table = $this->table('tb_iptables_blacklists', ['collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('ip_id',        'biginteger')
                ->addColumn('description',  'string',       ['null'=>true])
                ->addColumn('frequency',    'smallinteger', ['null'=>true])
                ->addColumn('created_at',   'timestamp',    ['default'=>'CURRENT_TIMESTAMP'])
                ->addColumn('deleted_at',   'timestamp',    ['null'=>true])
                ->addForeignKey('ip_id', 'tb_iptables_ips', 'id', ['update'=>'CASCADE', 'delete'=>'CASCADE'])
                ->create();

    }
}
