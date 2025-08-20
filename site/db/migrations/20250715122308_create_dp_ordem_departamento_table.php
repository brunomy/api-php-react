<?php

use Phinx\Migration\AbstractMigration;

class CreateDpOrdemDepartamentoTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('dp_ordem_departamento');
        
        $table
            ->addColumn('id_departamento', 'integer')
            ->addColumn('id_ordem', 'integer')
            ->addForeignKey('id_departamento', 'dp_departamentos', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION'
            ])
            ->addForeignKey('id_ordem', 'dp_ordens', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION'
            ])
            ->create();
    }
}
