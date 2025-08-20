<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateProdutosDescontosHasCategorias extends AbstractMigration
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

        $table = $this->table('tb_produtos_descontos_has_tb_produtos_categorias', ['id'=>false, 'primary_key'=>['id_desconto','id_categoria'],'collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id_desconto',          'integer')
                ->addColumn('id_categoria',         'integer')
                ->addForeignKey('id_desconto', 'tb_produtos_descontos', 'id', ['update'=>'NO_ACTION', 'delete'=>'CASCADE'])
                ->addForeignKey('id_categoria', 'tb_produtos_categorias', 'id', ['update'=>'NO_ACTION', 'delete'=>'CASCADE'])
                ->addIndex('id_desconto')
                ->create();

    }
}
