<?php
use Phinx\Migration\AbstractMigration;

class CreateTableOrigemLead extends AbstractMigration
{
    public function change()
    {
        $this->table('tb_origem_lead')
            ->addColumn('nome', 'string', ['limit' => 255])
            ->addColumn('stats', 'integer', ['default' => 0])
            ->addColumn('deleted_at', 'datetime', ['null' => true])
            ->create();
    }
}
