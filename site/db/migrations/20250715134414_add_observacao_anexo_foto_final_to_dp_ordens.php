<?php

use Phinx\Migration\AbstractMigration;

class AddObservacaoAnexoFotoFinalToDpOrdens extends AbstractMigration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE dp_ordens
            ADD COLUMN observacao VARCHAR(255) AFTER resumo,
            ADD COLUMN anexo VARCHAR(255) AFTER observacao,
            ADD COLUMN foto_final VARCHAR(255) AFTER anexo
        ");
    }

    public function down()
    {
        $this->table('dp_ordens')
            ->removeColumn('observacao')
            ->removeColumn('anexo')
            ->removeColumn('foto_final')
            ->update();
    }
}
