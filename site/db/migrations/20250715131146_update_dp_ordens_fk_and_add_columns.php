<?php

use Phinx\Migration\AbstractMigration;

class UpdateDpOrdensFkAndAddColumns extends AbstractMigration
{
    public function up()
    {
        // Primeiro, remover as constraints atuais
        $this->execute("ALTER TABLE dp_ordens DROP FOREIGN KEY dp_ordens_ibfk_3");
        $this->execute("ALTER TABLE dp_ordens DROP FOREIGN KEY dp_ordens_ibfk_4");
        $this->execute("ALTER TABLE dp_ordens DROP FOREIGN KEY dp_ordens_ibfk_5");

        // Alterar as colunas para permitir NULL (necessário para SET NULL funcionar)
        $this->table('dp_ordens')
            ->changeColumn('id_pedido', 'integer', ['null' => true])
            ->changeColumn('id_produto', 'integer', ['null' => true])
            ->changeColumn('id_categoria', 'integer', ['null' => true])
            ->update();

        // Adicionar novamente as constraints com ON DELETE SET NULL
        $this->execute("ALTER TABLE dp_ordens ADD CONSTRAINT dp_ordens_ibfk_3 FOREIGN KEY (id_pedido) REFERENCES tb_pedidos_pedidos(id) ON DELETE SET NULL");
        $this->execute("ALTER TABLE dp_ordens ADD CONSTRAINT dp_ordens_ibfk_4 FOREIGN KEY (id_produto) REFERENCES tb_produtos_produtos(id) ON DELETE SET NULL");
        $this->execute("ALTER TABLE dp_ordens ADD CONSTRAINT dp_ordens_ibfk_5 FOREIGN KEY (id_categoria) REFERENCES tb_produtos_categorias(id) ON DELETE SET NULL");

        // Adicionar as novas colunas na ordem correta
        $this->execute("
            ALTER TABLE dp_ordens
            ADD COLUMN nome_produto VARCHAR(255) AFTER id_status,
            ADD COLUMN nome_categoria VARCHAR(255) AFTER nome_produto,
            ADD COLUMN resumo VARCHAR(255) AFTER nome_categoria,
            ADD COLUMN qtd_minima INT AFTER resumo,
            ADD COLUMN quantidade INT AFTER qtd_minima,
            ADD COLUMN agrupavel INT AFTER quantidade,
            ADD COLUMN prazo_producao INT AFTER agrupavel
        ");
    }

    public function down()
    {
        // Remover as colunas adicionadas
        $this->table('dp_ordens')
            ->removeColumn('nome_produto')
            ->removeColumn('nome_categoria')
            ->removeColumn('resumo')
            ->removeColumn('qtd_minima')
            ->removeColumn('quantidade')
            ->removeColumn('agrupavel')
            ->removeColumn('prazo_producao')
            ->update();

        // Remover as novas constraints
        $this->execute("ALTER TABLE dp_ordens DROP FOREIGN KEY dp_ordens_ibfk_3");
        $this->execute("ALTER TABLE dp_ordens DROP FOREIGN KEY dp_ordens_ibfk_4");
        $this->execute("ALTER TABLE dp_ordens DROP FOREIGN KEY dp_ordens_ibfk_5");

        // Voltar para ON DELETE CASCADE e NOT NULL
        $this->table('dp_ordens')
            ->changeColumn('id_pedido', 'integer', ['null' => false])
            ->changeColumn('id_produto', 'integer', ['null' => false])
            ->changeColumn('id_categoria', 'integer', ['null' => false])
            ->update();

        $this->execute("ALTER TABLE dp_ordens ADD CONSTRAINT dp_ordens_ibfk_3 FOREIGN KEY (id_pedido) REFERENCES tb_pedidos_pedidos(id) ON DELETE CASCADE");
        $this->execute("ALTER TABLE dp_ordens ADD CONSTRAINT dp_ordens_ibfk_4 FOREIGN KEY (id_produto) REFERENCES tb_produtos_produtos(id) ON DELETE CASCADE");
        $this->execute("ALTER TABLE dp_ordens ADD CONSTRAINT dp_ordens_ibfk_5 FOREIGN KEY (id_categoria) REFERENCES tb_produtos_categorias(id) ON DELETE CASCADE");
    }
}
