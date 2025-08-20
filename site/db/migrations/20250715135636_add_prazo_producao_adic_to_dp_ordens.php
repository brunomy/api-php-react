<?php

use Phinx\Migration\AbstractMigration;

class AddPrazoProducaoAdicToDpOrdens extends AbstractMigration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE dp_ordens
            ADD COLUMN prazo_producao_adic INT AFTER prazo_producao
        ");
    }

    public function down()
    {
        $this->table('dp_ordens')
            ->removeColumn('prazo_producao_adic')
            ->update();
    }
}
