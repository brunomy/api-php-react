<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MudancasDPUsers extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('dp_users');

        // Remover colunas antigas
        $table
            ->removeColumn('gerenciamento')
            ->removeColumn('atividades')
            ->removeColumn('checklists')
            ->removeColumn('remessas');

        // Adicionar nova coluna de enum
        $table->addColumn('permissao', 'enum', [
            'values' => ['gerente', 'atividades', 'checklists', 'remessas'],
            'null' => true,
            'after' => 'telefone'
        ]);

        $table->update();
    }
}
