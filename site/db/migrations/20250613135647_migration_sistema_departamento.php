<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MigrationSistemaDepartamento extends AbstractMigration
{
    public function change()
    {
        // ✅ Criando a tabelas

        $this->table('dp_status', ['id' => true])
            ->addColumn('titulo', 'string', [
                'limit' => 25,
                'null' => false,
            ])
        ->create();

        $this->table('dp_departamentos', ['id' => true])
            ->addColumn('nome', 'string', [
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('descricao', 'string', [
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('status', 'integer', [
                'default' => 0,
                'null' => false,
            ])
            ->addColumn('deleted_at', 'datetime', [
                'null' => true,
            ])
        ->create();

        $this->table('dp_categoria_departamento', ['id' => true])
            ->addColumn('id_departamento', 'integer', ['null' => false])
            ->addColumn('id_categoria', 'integer', ['null' => false])
            ->addForeignKey('id_departamento', 'dp_departamentos', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
            ->addForeignKey('id_categoria', 'tb_produtos_categorias', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
        ->create();

        $this->table('dp_conf_etapas', ['id' => true])
            ->addColumn('id_categoria', 'integer', ['null' => false])
            ->addColumn('id_departamento', 'integer', ['null' => false])
            ->addColumn('titulo', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('deleted_at', 'datetime', ['null' => true])
            ->addForeignKey('id_categoria', 'tb_produtos_categorias', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
            ->addForeignKey('id_departamento', 'dp_departamentos', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
        ->create();

        $this->table('dp_conf_atividades', ['id' => true])
            ->addColumn('id_conf_etapa', 'integer', ['null' => false])
            ->addColumn('titulo', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('deleted_at', 'datetime', ['null' => true])
            ->addForeignKey('id_conf_etapa', 'dp_conf_etapas', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
        ->create();

        $this->table('dp_conf_volumes', ['id' => true])
            ->addColumn('id_conf_atividade', 'integer', ['null' => false])
            ->addColumn('titulo', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('deleted_at', 'datetime', ['null' => true])
            ->addForeignKey('id_conf_atividade', 'dp_conf_atividades', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
        ->create();

        $this->table('dp_conf_checklists', ['id' => true])
            ->addColumn('id_conf_atividade', 'integer', ['null' => false])
            ->addColumn('titulo', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('deleted_at', 'datetime', ['null' => true])
            ->addForeignKey('id_conf_atividade', 'dp_conf_atividades', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
        ->create();

        $this->table('dp_remessas', ['id' => true])
            ->addColumn('id_status', 'integer', ['null' => true])
            ->addColumn('titulo', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('created_at', 'datetime', ['null' => true])
            ->addColumn('deleted_at', 'datetime', ['null' => true])
            ->addForeignKey('id_status', 'dp_status', 'id', [
                'delete' => 'SET_NULL', // pode ser ajustado para CASCADE se preferir
                'update' => 'NO_ACTION',
            ])
        ->create();

        $this->table('dp_embalagens', ['id' => true])
            ->addColumn('id_remessa', 'integer', ['null' => false])
            ->addColumn('descricao', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('comprimento', 'float', ['null' => false])
            ->addColumn('largura', 'float', ['null' => false])
            ->addColumn('altura', 'float', ['null' => false])
            ->addColumn('peso', 'float', ['null' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addForeignKey('id_remessa', 'dp_remessas', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
        ->create();

        $this->table('dp_users', ['id' => true])
            ->addColumn('id_departamento', 'integer', ['null' => false])
            ->addColumn('token', 'string', ['limit' => 288, 'null' => false])
            ->addColumn('nome', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('descricao', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('email', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('telefone', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('usuario', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('senha', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('gerenciamento', 'integer', ['default' => 0, 'null' => false])
            ->addColumn('atividades', 'integer', ['default' => 0, 'null' => false])
            ->addColumn('checklists', 'integer', ['default' => 0, 'null' => false])
            ->addColumn('remessas', 'integer', ['default' => 0, 'null' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('deleted_at', 'datetime', ['null' => false])
            ->addForeignKey('id_departamento', 'dp_departamentos', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
        ->create();

        $this->table('dp_equipes', ['id' => true])
            ->addColumn('id_user', 'integer', ['null' => false])
            ->addColumn('nome', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('descricao', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('deleted_at', 'datetime', ['null' => false])
            ->addForeignKey('id_user', 'dp_users', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
        ->create();

        $this->table('dp_funcionarios', ['id' => true])
            ->addColumn('id_equipe', 'integer', ['null' => false])
            ->addColumn('nome', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('funcao', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('usuario', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('senha', 'string', ['limit' => 4, 'null' => false])
            ->addColumn('codigo', 'string', ['limit' => 288, 'null' => false])
            ->addColumn('deleted_at', 'datetime', ['null' => true])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addForeignKey('id_equipe', 'dp_equipes', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
        ->create();

        $this->table('dp_ordens', ['id' => true])
            ->addColumn('id_remessas', 'integer', ['null' => false])
            ->addColumn('id_carrinho_produtos_historico', 'biginteger', ['null' => false])
            ->addColumn('id_pedido', 'integer', ['null' => false])
            ->addColumn('id_produto', 'integer', ['null' => false])
            ->addColumn('id_categoria', 'integer', ['null' => false])
            ->addColumn('id_status', 'integer', ['null' => true])
            ->addColumn('deleted_at', 'datetime', ['null' => true])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addForeignKey('id_remessas', 'dp_remessas', 'id', [
                'delete' => 'CASCADE', 'update' => 'NO_ACTION'
            ])
            ->addForeignKey('id_carrinho_produtos_historico', 'tb_carrinho_produtos_historico', 'id', [
                'delete' => 'CASCADE', 'update' => 'NO_ACTION'
            ])
            ->addForeignKey('id_pedido', 'tb_pedidos_pedidos', 'id', [
                'delete' => 'CASCADE', 'update' => 'NO_ACTION'
            ])
            ->addForeignKey('id_produto', 'tb_produtos_produtos', 'id', [
                'delete' => 'CASCADE', 'update' => 'NO_ACTION'
            ])
            ->addForeignKey('id_categoria', 'tb_produtos_categorias', 'id', [
                'delete' => 'CASCADE', 'update' => 'NO_ACTION'
            ])
            ->addForeignKey('id_status', 'dp_status', 'id', [
                'delete' => 'SET_NULL', // pode ser ajustado para CASCADE se preferir
                'update' => 'NO_ACTION',
            ])
        ->create();

        $this->table('dp_requisitos', ['id' => true])
            ->addColumn('id_ordem', 'integer', ['null' => false])
            ->addColumn('id_prerequisito', 'integer', ['null' => false])
            ->addColumn('status', 'integer', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addForeignKey('id_ordem', 'dp_ordens', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
            ->addForeignKey('id_prerequisito', 'tb_produtos_prerequisitos', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
        ->create();

        $this->table('dp_dependencias', ['id' => true])
            ->addColumn('id_requisito', 'integer', ['null' => false])
            ->addColumn('id_dependencia', 'integer', ['null' => false])
            ->addColumn('status', 'integer', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addForeignKey('id_requisito', 'dp_requisitos', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
            ->addForeignKey('id_dependencia', 'tb_produtos_dependencias', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
        ->create();

        $this->table('dp_requisito_anexo', ['id' => true])
            ->addColumn('id_requisito', 'integer', ['null' => false])
            ->addColumn('caminho', 'string', ['limit' => 200, 'null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addForeignKey('id_requisito', 'dp_requisitos', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
        ->create();

        $this->table('dp_etapas', ['id' => true])
            ->addColumn('id_ordem', 'integer', ['null' => false])
            ->addColumn('id_conf_etapa', 'integer', ['null' => false])
            ->addForeignKey('id_ordem', 'dp_ordens', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
            ->addForeignKey('id_conf_etapa', 'dp_conf_etapas', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
        ->create();

        $this->table('dp_atividades', ['id' => true])
            ->addColumn('id_conf_atividade', 'integer', ['null' => false])
            ->addColumn('id_ordem', 'integer', ['null' => false])
            ->addColumn('id_etapa', 'integer', ['null' => false])
            ->addColumn('id_equipe', 'integer', ['null' => false])
            ->addColumn('id_status', 'integer', ['null' => true])
            ->addColumn('data', 'date', ['null' => false])
            ->addColumn('inicio', 'datetime', ['null' => true])
            ->addColumn('fim', 'datetime', ['null' => true])
            ->addColumn('status', 'integer', ['null' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('deleted_at', 'datetime', ['null' => true])
            ->addForeignKey('id_conf_atividade', 'dp_conf_atividades', 'id', [
                'delete' => 'CASCADE', 'update' => 'NO_ACTION'
            ])
            ->addForeignKey('id_ordem', 'dp_ordens', 'id', [
                'delete' => 'CASCADE', 'update' => 'NO_ACTION'
            ])
            ->addForeignKey('id_etapa', 'dp_etapas', 'id', [
                'delete' => 'CASCADE', 'update' => 'NO_ACTION'
            ])
            ->addForeignKey('id_equipe', 'dp_equipes', 'id', [
                'delete' => 'CASCADE', 'update' => 'NO_ACTION'
            ])
            ->addForeignKey('id_status', 'dp_status', 'id', [
                'delete' => 'SET_NULL', 'update' => 'NO_ACTION'
            ])
        ->create();

        $this->table('dp_checklists', ['id' => true])
            ->addColumn('id_atividade', 'integer', ['null' => false])
            ->addColumn('id_conf_checklist', 'integer', ['null' => false])
            ->addColumn('observacao', 'string', ['limit' => 200, 'null' => true])
            ->addColumn('status', 'integer', ['default' => 0, 'null' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addForeignKey('id_atividade', 'dp_atividades', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
            ->addForeignKey('id_conf_checklist', 'dp_conf_checklists', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
        ->create();

        $this->table('dp_volumes', ['id' => true])
            ->addColumn('id_conf_volume', 'integer', ['null' => false])
            ->addColumn('id_atividade', 'integer', ['null' => false])
            ->addColumn('id_embalagem', 'integer', ['null' => false])
            ->addColumn('id_remessa', 'integer', ['null' => false])
            ->addColumn('comprimento', 'float', ['null' => false])
            ->addColumn('largura', 'float', ['null' => false])
            ->addColumn('altura', 'float', ['null' => false])
            ->addColumn('peso', 'float', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addForeignKey('id_conf_volume', 'dp_conf_volumes', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
            ->addForeignKey('id_atividade', 'dp_atividades', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
            ->addForeignKey('id_embalagem', 'dp_embalagens', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
            ->addForeignKey('id_remessa', 'dp_remessas', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
        ->create();

        $this->table('dp_historico', ['id' => true])
            ->addColumn('id_atividade', 'integer', ['null' => false])
            ->addColumn('id_funcionario', 'integer', ['null' => false])
            ->addColumn('descricao', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addForeignKey('id_atividade', 'dp_atividades', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
            ->addForeignKey('id_funcionario', 'dp_funcionarios', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
        ->create();


        // Alterando tabelas
        $this->table('tb_produtos_dependencias')
            ->addColumn('cor', 'string', [
                'limit' => 7,
                'null' => true,
                'after' => 'descricao'
            ])
        ->update();
    }
}
