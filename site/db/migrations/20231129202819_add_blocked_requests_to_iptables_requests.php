<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddBlockedRequestsToIptablesRequests extends AbstractMigration
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
        $table = $this->table('tb_iptables_requests');
        $table  ->addColumn('blocked', 'smallinteger', ['default'=>0, 'after' => 'uri'])
                ->update();
    }
}
