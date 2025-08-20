<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateAdminEmailNotification extends AbstractMigration
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

        $table = $this->table('tb_admin_email_notification', ['id'=>false, 'primary_key'=>['id_form','id_user'], 'collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id_form',     'integer')
                ->addColumn('id_user',     'integer')
                ->addForeignKey('id_form', 'tb_admin_forms', 'id', ['delete'=>'CASCADE','update'=>'CASCADE'])
                ->addForeignKey('id_user', 'tb_admin_users', 'id', ['delete'=>'CASCADE','update'=>'CASCADE'])
                ->addIndex(['id_form'])
                ->create();

    }
}
