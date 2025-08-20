<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateProdutosHasDescontos extends AbstractMigration
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

        $table = $this->table('tb_produtos_has_tb_descontos', ['id'=>false, 'primary_key'=>['id_desconto','id_produto'],'collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id_produto',         'integer')
                ->addColumn('id_desconto',        'integer')
                ->addForeignKey('id_desconto', 'tb_produtos_descontos', 'id', ['update'=>'NO_ACTION', 'delete'=>'NO_ACTION'])
                ->addForeignKey('id_produto', 'tb_produtos_produtos', 'id', ['update'=>'NO_ACTION', 'delete'=>'NO_ACTION'])
                ->addIndex('id_desconto')
                ->create();

    }
}
