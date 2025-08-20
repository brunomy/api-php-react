<?php
use Phinx\Migration\AbstractMigration;

class AddIdOrigemToClient extends AbstractMigration
{
    public function change()
    {
        // $table = $this->table('tb_clientes_clientes');

        // $table
        //     ->addColumn('id_origem', 'integer', [
        //         'null' => true,
        //         'after' => 'id'
        //     ])
        //     ->addForeignKey('id_origem', 'tb_origem_lead', 'id', [
        //         'delete'=> 'SET_NULL',
        //         'update'=> 'NO_ACTION'
        //     ])
        //     ->update();
    }
}
