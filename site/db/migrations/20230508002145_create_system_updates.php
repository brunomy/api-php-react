<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class CreateSystemUpdates extends AbstractMigration
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

        $table = $this->table('tb_system_updates', ['collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('tag_name',         'string',   ['null'=>true])
                ->addColumn('tag_message',      'string',   ['null'=>true])
                ->addColumn('tag_url',          'string',   ['null'=>true])
                ->addColumn('tag_created_at',   'datetime', ['null'=>true])
                ->addColumn('commit_hash',      'string',   ['null'=>true])
                ->addColumn('author',           'string',   ['null'=>true,'limit'=>45])
                ->addColumn('author_url',       'string',   ['null'=>true])
                ->addColumn('author_avatar',    'string',   ['null'=>true])
                ->addColumn('status',           'enum',     ['null'=>true, 'values'=>['waiting','executing','applied','error']])
                ->addColumn('proccess_message', 'text',     ['null'=>true,'limit' => MysqlAdapter::TEXT_MEDIUM])
                ->addColumn('initialized_by_user','integer',['null'=>true])
                ->addColumn('applied_at',       'datetime', ['null'=>true])
                ->addColumn('created_at',       'datetime', ['null'=>true])
                ->create();

    }
}
