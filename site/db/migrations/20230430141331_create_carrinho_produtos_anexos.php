<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCarrinhoProdutosAnexos extends AbstractMigration
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

        $table = $this->table('tb_carrinho_produtos_anexos', ['id'=>false, 'primary_key' => 'id_produto_historico', 'collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id_produto_historico', 'biginteger')
                ->addColumn('arquivo',              'string',       ['null'=>true])
                ->addColumn('data',                 'timestamp',    ['default'=>'CURRENT_TIMESTAMP'])
                ->addForeignKey('id_produto_historico', 'tb_carrinho_produtos_historico', 'id', ['delete'=>'CASCADE','update'=>'NO_ACTION'])
                ->create();

    }
}
