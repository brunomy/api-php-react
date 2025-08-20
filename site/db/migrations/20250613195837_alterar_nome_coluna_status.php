<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AlterarNomeColunaStatus extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('dp_departamentos');

        // Renomeando a coluna
        $table->renameColumn('status', 'stats')->update();
    }
}
