<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePedidosPedidos extends AbstractMigration
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
        /*
            STATUS
            1:Pendente
            2:Emitida
            3:Cancelada
            4:Enviada - Aguardando recibo
            5:Rejeitada
            6:Autorizada
            7:Emitida DANFE
            8:Registrada
            9:Enviada - Aguardando protocolo
            10:Denegada
        */

        $table = $this->table('tb_pedidos_pedidos', ['collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('code',                     'string',       ['null'=>true, 'limit'=>10])
                ->addColumn('id_empresa',               'integer',      ['null'=>true])
                ->addColumn('id_cliente',               'integer',      ['null'=>true])
                ->addColumn('valor_cupom',              'double',       ['null'=>true])
                ->addColumn('tipo_cupom',               'integer',      ['default'=>0])
                ->addColumn('mensagem_cupom',           'string',       ['null'=>true])
                ->addColumn('valor_frete',              'double',       ['null'=>true])
                ->addColumn('frete',                    'string',       ['null'=>true])
                ->addColumn('subtotal',                 'double',       ['null'=>true])
                ->addColumn('descontos',                'double',       ['null'=>true])
                ->addColumn('prazo_entrega',            'date',         ['null'=>true])
                ->addColumn('dias_entrega',             'integer',      ['null'=>true])
                ->addColumn('id_status',                'integer',      ['null'=>true, 'default'=>1])
                ->addColumn('metodo_pagamento',         'string',       ['null'=>true, 'limit'=>150])
                ->addColumn('avista',                   'integer',      ['default'=>0])
                ->addColumn('valor_final',              'double',       ['null'=>true])
                ->addColumn('data',                     'timestamp',    ['default'=>'CURRENT_TIMESTAMP'])
                ->addColumn('data_competencia',         'timestamp',    ['default'=>'CURRENT_TIMESTAMP'])
                ->addColumn('metodo_pagamento_id',      'string',       ['null'=>true, 'limit'=>150])
                ->addColumn('status_pagamento',         'integer',      ['null'=>true])
                ->addColumn('status',                   'integer',      ['null'=>true])
                ->addColumn('tipo_pagamento',           'string',       ['null'=>true])
                ->addColumn('n_nota',                   'string',       ['null'=>true, 'limit'=>150])
                ->addColumn('serie',                    'string',       ['null'=>true, 'limit'=>150])
                ->addColumn('codigo_rastreamento',      'string',       ['null'=>true, 'limit'=>150])
                ->addColumn('situacao',                 'string',       ['null'=>true, 'limit'=>150])
                ->addColumn('mensagem',                 'text',         ['null'=>true])
                ->addColumn('chaveAcesso',              'string',       ['null'=>true])
                ->addColumn('linkDanfe',                'string',       ['null'=>true])
                ->addColumn('idNotaFiscal',             'string',       ['null'=>true, 'limit'=>100])
                ->addColumn('faturado_por',             'string',       ['null'=>true])
                ->addColumn('entrega_transportadora',   'string',       ['null'=>true])
                ->addColumn('entrega_cotacao',          'string',       ['null'=>true, 'limit'=>45])
                ->addColumn('entrega_coleta',           'string',       ['null'=>true, 'limit'=>45])
                ->addColumn('entrega_valor',            'string',       ['null'=>true, 'limit'=>45])
                ->addColumn('entrega_dia_coleta',       'string',       ['null'=>true, 'limit'=>45])
                ->addColumn('ip',                       'string',       ['null'=>true])
                ->addColumn('session',                  'string',       ['null'=>true, 'limit'=>45])
                ->addColumn('observacoes_gerais',       'text',         ['null'=>true])
                ->addColumn('agendor',                  'integer',      ['null'=>true])
                ->addColumn('id_vendedor',              'integer',      ['null'=>true])
                ->addColumn('entregue',                 'date',         ['null'=>true])
                ->addColumn('usuario_editando_pedido',  'integer',      ['null'=>true])
                ->addColumn('usuario_editando_ultimo_ping', 'datetime', ['null'=>true])
                ->addColumn('porcentagem_comissao',     'float',        ['null'=>true])
                ->addColumn('orc_status',               'string',       ['null'=>true, 'limit'=>45])
                ->addColumn('orc_etapa',                'string',       ['null'=>true, 'limit'=>45])
                ->addColumn('orc_id_crm',               'integer',      ['null'=>true])
                ->addColumn('orc_data',                 'timestamp',    ['default'=>'CURRENT_TIMESTAMP'])
                ->addColumn('tipo_cliente',             'string',       ['null'=>true])
                ->addColumn('marcado',                  'integer',      ['null'=>true])
                ->addForeignKey('id_cliente', 'tb_clientes_clientes', 'id', ['delete'=>'SET_NULL','update'=>'NO_ACTION'])
                ->addForeignKey('id_status', 'tb_pedidos_status', 'id', ['delete'=>'SET_NULL','update'=>'SET_NULL'])
                ->addIndex(['code', 'id', 'id_cliente'], ['name' => 'idx_2'])
                ->create();

    }
}
