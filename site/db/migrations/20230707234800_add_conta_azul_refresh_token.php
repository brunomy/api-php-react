<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddContaAzulRefreshToken extends AbstractMigration
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
        $table = $this->table('tb_admin_empresa');
        $table  ->addColumn('contaazul_refresh_token', 'string',   ['null'=>true])
                ->addColumn('contaazul_access_token', 'string',   ['null'=>true])
                ->addColumn('contaazul_access_token_expires_at', 'timestamp',   ['null'=>true])
                ->update();

        $table = $this->table('tb_pedidos_pedidos');
        $table  ->addColumn('contaazul_id', 'string', ['null'=>true, 'after' => 'faturado_por'])
                ->update();

        $table = $this->table('tb_produtos_produtos');
        $table  ->addColumn('contaazul_id', 'string', ['null'=>true, 'after' => 'apagado'])
                ->update();
    }
}
