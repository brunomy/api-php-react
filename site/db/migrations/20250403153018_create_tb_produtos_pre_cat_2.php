<?php
use Phinx\Migration\AbstractMigration;

class CreateTbProdutosPreCat2 extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('tb_produtos_pre_cat');
        $table
            ->addColumn('id_pre', 'integer')
            ->addColumn('id_cat', 'integer')
            ->addForeignKey('id_pre', 'tb_produtos_prerequisitos', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
            ->addForeignKey('id_cat', 'tb_produtos_categorias', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
            ->create();
    }
}