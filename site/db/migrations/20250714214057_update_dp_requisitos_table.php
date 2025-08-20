<?php
declare(strict_types=1);


use Phinx\Migration\AbstractMigration;

class UpdateDpRequisitosTable extends AbstractMigration
{
    public function change()
    {
        // A tabela já existe, então vamos alterá-la
        $table = $this->table('dp_requisitos');

        // Remove a coluna e a FK se existirem
        if ($table->hasColumn('id_prerequisito')) {
            $table->dropForeignKey('id_prerequisito')->removeColumn('id_prerequisito');
        }

        // Adiciona as novas colunas após 'id_ordem'
        $table->addColumn('nome', 'string', ['limit' => 255, 'after' => 'id_ordem'])
              ->addColumn('descricao', 'string', ['limit' => 255, 'after' => 'nome'])
              ->addColumn('ordem', 'integer', ['after' => 'descricao'])
              ->update();
    }
}
