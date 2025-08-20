<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CriacaoColunaIdFaturado extends AbstractMigration
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
        $table = $this->table('tb_pedidos_pedidos');
        
        $table->addColumn('id_empresa_faturado', 'integer', ['null' => true, 'after' => 'id_cliente'])
            ->addForeignKey('id_empresa_faturado', 'tb_admin_empresas', 'id', [
                'delete' => 'SET NULL',
                'update' => 'NO_ACTION'
            ])
            ->update();
    }
}


