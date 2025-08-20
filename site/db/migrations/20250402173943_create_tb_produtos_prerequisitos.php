<?php

use Phinx\Migration\AbstractMigration;

class CreateTbProdutosPrerequisitos extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('tb_produtos_prerequisitos');
        $table
            ->addColumn('nome', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('descricao', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('ordem', 'integer', ['null' => true])
            ->addColumn('stats', 'integer', ['null' => true])
            ->addColumn('data', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
            ->create();
    }
}