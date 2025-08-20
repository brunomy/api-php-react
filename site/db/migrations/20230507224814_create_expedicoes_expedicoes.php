<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateExpedicoesExpedicoes extends AbstractMigration
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

        $table = $this->table('tb_expedicoes_expedicoes', ['collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id_pedido',                'integer')
                ->addColumn('id_transportadora',        'integer')
                ->addColumn('departamento',             'string',   ['limit'=>45])
                ->addColumn('volumes',                  'integer',  ['default'=>1])
                ->addColumn('data_coleta',              'date')
                ->addColumn('n_nota',                   'string',   ['null'=>true,'limit'=>150])
                ->addColumn('nome',                     'string',   ['null'=>true,'limit'=>100])
                ->addColumn('telefone',                 'string',   ['null'=>true,'limit'=>25])
                ->addColumn('cpf_cnpj',                 'string',   ['null'=>true,'limit'=>25])
                ->addColumn('id_cidade',                'integer')
                ->addColumn('id_estado',                'integer')
                ->addColumn('cep',                      'string',   ['null'=>true,'limit'=>45])
                ->addColumn('endereco',                 'string',   ['null'=>true])
                ->addColumn('numero',                   'string',   ['null'=>true,'limit'=>45])
                ->addColumn('bairro',                   'string',   ['null'=>true,'limit'=>150])
                ->addColumn('complemento',              'string',   ['null'=>true])
                ->addColumn('coleta_confirmada',        'timestamp',['null'=>true])
                ->addColumn('id_user',                  'integer',  ['null'=>true])
                ->addForeignKey('id_user',              'tb_admin_users', 'id',                 ['delete'=>'SET_NULL','update'=>'RESTRICT'])
                ->addForeignKey('id_transportadora',    'tb_expedicoes_transportadoras', 'id',  ['delete'=>'RESTRICT','update'=>'RESTRICT'])
                ->addForeignKey('id_pedido',            'tb_pedidos_pedidos', 'id',             ['delete'=>'RESTRICT','update'=>'RESTRICT'])
                ->addForeignKey('id_cidade',            'tb_utils_cidades', 'id',               ['delete'=>'RESTRICT','update'=>'RESTRICT'])
                ->addForeignKey('id_estado',            'tb_utils_estados', 'id',               ['delete'=>'RESTRICT','update'=>'RESTRICT'])
                ->create();

    }
}
