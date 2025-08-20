<?php
use Phinx\Migration\AbstractMigration;

class CreateTbProdutosPreDep2 extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('tb_produtos_pre_dep');
        $table
            ->addColumn('id_pre', 'integer')
            ->addColumn('id_dep', 'integer')
            ->addForeignKey('id_pre', 'tb_produtos_prerequisitos', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
            ->addForeignKey('id_dep', 'tb_produtos_dependencias', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
            ->create();
    }
}