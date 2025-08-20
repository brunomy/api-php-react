<?php

use Phinx\Migration\AbstractMigration;

class UpdateDpDependenciasTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('dp_dependencias');

        // Remover FK e coluna se existirem
        if ($table->hasForeignKey('id_dependencia')) {
            $table->dropForeignKey('id_dependencia');
        }

        if ($table->hasColumn('id_dependencia')) {
            $table->removeColumn('id_dependencia');
        }

        // Adicionar colunas se ainda não existirem
        if (!$table->hasColumn('nome')) {
            $table->addColumn('nome', 'string', ['limit' => 255, 'after' => 'id_requisito']);
        }

        if (!$table->hasColumn('descricao')) {
            $table->addColumn('descricao', 'string', ['limit' => 255, 'after' => 'nome']);
        }

        if (!$table->hasColumn('cor')) {
            $table->addColumn('cor', 'string', ['limit' => 7, 'after' => 'descricao']);
        }

        $table->update();
    }
}