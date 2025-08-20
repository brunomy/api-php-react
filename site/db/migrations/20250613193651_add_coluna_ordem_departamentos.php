<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddColunaOrdemDepartamentos extends AbstractMigration
{
    public function change(): void
    {
        $this->table('dp_departamentos')
            ->addColumn('ordem', 'integer', ['null' => true, 'after' => 'descricao']) // ou ajuste 'after' conforme a posição desejada
        ->update();
    }
}
