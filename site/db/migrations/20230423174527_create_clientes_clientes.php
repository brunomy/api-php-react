<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;


final class CreateClientesClientes extends AbstractMigration
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

        $table = $this->table('tb_clientes_clientes', ['collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('pessoa',               'integer', ['null'=>true])
                ->addColumn('nome',                 'string',  ['null'=>true, 'limit'=>100])
                ->addColumn('razao_social',         'string',  ['null'=>true])
                ->addColumn('cnpj',                 'string',  ['null'=>true, 'limit'=>200])
                ->addColumn('inscricao_estadual',   'string',  ['null'=>true, 'limit'=>200])
                ->addColumn('cpf',                  'string',  ['null'=>true, 'limit'=>20])
                ->addColumn('email',                'string',  ['null'=>true, 'limit'=>150])
                ->addColumn('telefone',             'string',  ['null'=>true, 'limit'=>20])
                ->addColumn('senha',                'string',  ['null'=>true, 'limit'=>100])
                ->addColumn('cep',                  'string',  ['null'=>true, 'limit'=>45])
                ->addColumn('endereco',             'string',  ['null'=>true, 'limit'=>150])
                ->addColumn('numero',               'string',  ['null'=>true, 'limit'=>45])
                ->addColumn('bairro',               'string',  ['null'=>true, 'limit'=>100])
                ->addColumn('complemento',          'string',  ['null'=>true])
                ->addColumn('id_cidade',            'integer', ['null'=>true])
                ->addColumn('id_estado',            'integer', ['null'=>true])
                ->addColumn('stats',                'integer', ['default'=>'0'])
                ->addColumn('code',                 'string',  ['null'=>true, 'limit'=>100])
                ->addColumn('ip',                   'string',  ['null'=>true, 'limit'=>45])
                ->addColumn('session',              'string',  ['null'=>true, 'limit'=>50])
                ->addColumn('ultimo_acesso',        'datetime',['null'=>true])
                ->addColumn('utm_source',           'string',  ['null'=>true])
                ->addColumn('data',                 'timestamp',['default'=>'CURRENT_TIMESTAMP'])
                ->addColumn('poder_aquisitivo',     'integer',['null'=>true, 'limit'=>MysqlAdapter::INT_TINY])
                ->addColumn('engajamento',          'integer',['null'=>true, 'limit'=>MysqlAdapter::INT_TINY])
                ->addColumn('observacao',           'text',    ['null'=>true, 'limit'=>MysqlAdapter::TEXT_LONG])
                ->addColumn('ultimo_contato',       'datetime',['null'=>true])
                ->addColumn('deleted_at',           'timestamp',['null'=>true])
                ->addColumn('deleted_name',         'string',  ['null'=>true, 'limit'=>100])
                ->addColumn('deleted_email',        'string',  ['null'=>true, 'limit'=>150])
                ->addColumn('deleted_phone',        'string',  ['null'=>true, 'limit'=>20])
                ->addColumn('deleted_cnpj',         'string',  ['null'=>true, 'limit'=>200])
                ->addColumn('deleted_cpf',          'string',  ['null'=>true, 'limit'=>20])
                ->addIndex('email', ['unique' => true])
                ->create();

    }
}
