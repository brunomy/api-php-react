<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateAdminEmpresa extends AbstractMigration
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

        $table = $this->table('tb_admin_empresa', ['collation' => 'utf8mb4_swedish_ci']);
        $table->addColumn('nome', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('logomarca', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('favicon', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('endereco', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('bairro', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('cep', 'string', ['limit' => 45, 'null' => true])
            ->addColumn('cidade', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('estado', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('email', 'string', ['limit' => 45, 'null' => true])
            ->addColumn('fone', 'string', ['limit' => 45, 'null' => true])
            ->addColumn('link_facebook', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('link_instagram', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('link_youtube', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('email_host', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('email_port', 'string', ['limit' => 11, 'null' => true])
            ->addColumn('email_user', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('email_password', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('email_padrao', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('email_mkt', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('token_mkt', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('autenticado', 'string', ['limit' => 255, 'null' => true, 'default' => '0'])
            ->addColumn('comissao_vendas', 'float');

        $table->create();
        
    }
}
