<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class CreateCrmCrm extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {

        $table = $this->table('tb_crm_crm', ['collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id_user',              'string',  ['null'=>true, 'limit'=>45])
                ->addColumn('nome',                 'string',  ['null'=>true])
                ->addColumn('email',                'string',  ['null'=>true, 'limit'=>124])
                ->addColumn('telefone',             'string',  ['null'=>true, 'limit'=>45])
                ->addColumn('cpf_cnpj',             'string',  ['null'=>true, 'limit'=>45])
                ->addColumn('data',                 'datetime',['null'=>true, 'default'=>'CURRENT_TIMESTAMP'])
                ->addColumn('ultima_atualizacao',   'datetime',['null'=>true, 'default'=>'CURRENT_TIMESTAMP'])
                ->addColumn('possui_orcamento',     'integer', ['null'=>true, 'limit' => MysqlAdapter::INT_TINY])
                ->addColumn('id_cliente',           'integer', ['null'=>true])
                ->addColumn('descricao',            'string',  ['null'=>true, 'limit'=>512])
                ->addColumn('finalizado',           'integer', ['null'=>true, 'limit' => MysqlAdapter::INT_TINY])
                ->addForeignKey('id_cliente', 'tb_clientes_clientes', 'id', ['delete'=>'RESTRICT','update'=>'RESTRICT'])
                ->create();

    }
}
