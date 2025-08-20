<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUserPermissioes extends AbstractMigration
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

        $table = $this->table('tb_user_permissoes', ['id'=>false, 'primary_key' => ['id_usuario','id_funcao'], 'collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id_usuario',       'integer')
                ->addColumn('id_funcao',        'integer')
                ->addColumn('ler',              'integer',  ['null'=>true])
                ->addColumn('gravar',           'integer',  ['null'=>true])
                ->addColumn('excluir',          'integer',  ['null'=>true])
                ->addColumn('editar',           'integer',  ['null'=>true])
                ->addForeignKey('id_funcao', 'tb_admin_funcoes', 'id', ['update'=>'RESTRICT', 'delete'=>'CASCADE'])
                ->addForeignKey('id_usuario', 'tb_admin_users', 'id', ['update'=>'RESTRICT', 'delete'=>'CASCADE'])
                ->addIndex('id_usuario')
                ->create();

    }
}
