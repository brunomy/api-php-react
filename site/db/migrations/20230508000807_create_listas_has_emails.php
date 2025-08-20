<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateListasHasEmails extends AbstractMigration
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

        $table = $this->table('tb_listas_listas_has_tb_emails_emails', ['id'=>false, 'primary_key' => ['id_lista','id_email'], 'collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id_lista',         'biginteger')
                ->addColumn('id_email',         'biginteger')
                ->addForeignKey('id_lista', 'tb_listas_listas', 'id', ['update'=>'RESTRICT', 'delete'=>'CASCADE'])
                ->addForeignKey('id_email', 'tb_emails_emails', 'id', ['update'=>'RESTRICT', 'delete'=>'CASCADE'])
                ->addIndex('id_lista')
                ->create();

    }
}
