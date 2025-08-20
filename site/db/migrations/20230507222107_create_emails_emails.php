<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateEmailsEmails extends AbstractMigration
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

        $table = $this->table('tb_emails_emails', ['id'=>false, 'primary_key' => 'id', 'collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id',                   'biginteger',   ['identity'=>true])
                ->addColumn('nome',                 'string',       ['null'=>true, 'limit'=>60])
                ->addColumn('email',                'string',       ['null'=>true])
                ->addColumn('nascimento',           'date',         ['null'=>true])
                ->addColumn('data',                 'timestamp',    ['null'=>true, 'default'=>'CURRENT_TIMESTAMP'])
                ->addIndex('email',['unique'=>true])
                ->create();

    }
}
