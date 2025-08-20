<?php

use Phinx\Migration\AbstractMigration;

class CreateTbProdutosDependencias extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('tb_produtos_dependencias');
        $table
            ->addColumn('nome', 'string', [
                'limit' => 255,
                'null' => true,
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci'
            ])
            ->addColumn('descricao', 'string', [
                'limit' => 255,
                'null' => true,
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci'
            ])
            ->addColumn('ordem', 'integer', ['null' => true])
            ->addColumn('stats', 'integer', ['null' => true])
            ->addColumn('data', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'null' => true
            ])
            ->create();
    }
}
