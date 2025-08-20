<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateExpedicoesAndRemessasTables extends AbstractMigration
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
        // === TABELA: tb_expedicoes_expedicoes ===
        $expedicoes = $this->table('tb_expedicoes_expedicoes');

        $expedicoes
            ->addColumn('id_remessa', 'integer', ['null' => true, 'after' => 'id'])
            ->addForeignKey('id_remessa', 'dp_remessas', 'id', ['delete' => 'SET NULL', 'update' => 'NO_ACTION'])
            ->addColumn('titulo', 'string', ['limit' => 50, 'null' => false, 'after' => 'id_transportadora'])
        ->update();

        // === TABELA: dp_remessas ===
        $remessas = $this->table('dp_remessas');

        if ($remessas->hasColumn('id_expedicao')) {
            $remessas->removeColumn('id_expedicao');
        }

        $remessas
            ->addColumn('id_pedido', 'integer', ['null' => false, 'after' => 'id'])
            ->addForeignKey('id_pedido', 'tb_pedidos_pedidos', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])

            ->addColumn('n_nota', 'string', ['limit' => 50, 'null' => true, 'after' => 'titulo'])

            ->addColumn('data_coleta', 'date', ['null' => false])
            ->addColumn('entrega', 'date', ['null' => false])
            ->addColumn('nome', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('telefone', 'string', ['limit' => 20, 'null' => false])
            ->addColumn('cpf_cnpj', 'string', ['limit' => 20, 'null' => true])
            ->addColumn('cep', 'string', ['limit' => 10, 'null' => false])
            ->addColumn('id_estado', 'integer', ['null' => false])
            ->addForeignKey('id_estado', 'tb_utils_estados', 'id', ['delete' => 'RESTRICT', 'update' => 'NO_ACTION'])

            ->addColumn('id_cidade', 'integer', ['null' => false])
            ->addForeignKey('id_cidade', 'tb_utils_cidades', 'id', ['delete' => 'RESTRICT', 'update' => 'NO_ACTION'])

            ->addColumn('endereco', 'string', ['limit' => 150, 'null' => false])
            ->addColumn('numero', 'string', ['limit' => 10, 'null' => false])
            ->addColumn('bairro', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('complemento', 'string', ['limit' => 100, 'null' => false])
        ->update();
    }
}
