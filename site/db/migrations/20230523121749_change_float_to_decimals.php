<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ChangeFloatToDecimals extends AbstractMigration
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
    public function up(): void
    {

        //tb_produtos_atributos
        $table = $this->table('tb_produtos_atributos');
        $table  ->changeColumn('custo', 'decimal', ['null'=>true,'precision'=>10,'scale'=>2])
                ->update();

        /*-----------------------------------------------------------
            NÃO QUIS ALTERAR TUDO PORQUE NÃO SABIA QUAL IMPACTO 
            PODERIA CAUSAR NO SISTEMA, POR ISSO DECIDI ALTERAR SÓ 
            O QUE PRECISA NO MOMENTO E DEIXAR MAPEADO AS OUTRAS CASO 
            SEJA NECESSÁRIO ALTERAR FUTURAMENTE
        -----------------------------------------------------------*/
        

        //tb_admin_empresa
        /*$table = $this->table('tb_admin_empresa');
        $table  ->changeColumn('comissao_vendas', 'decimal', ['precision'=>10,'scale'=>2])
                ->update();


        //tb_pedidos_pedidos
        $table = $this->table('tb_pedidos_pedidos');
        $table  ->changeColumn('porcentagem_comissao', 'decimal', ['null'=>true,'precision'=>10,'scale'=>2])
                ->update();


        //tb_carrinho_produtos_historico
        $table = $this->table('tb_carrinho_produtos_historico');
        $table  ->changeColumn('peso', 'decimal', ['null'=>true,'precision'=>10,'scale'=>2])
                ->changeColumn('frete_embutido', 'decimal', ['null'=>true,'precision'=>10,'scale'=>2])
                ->update();


        //tb_config_conjuntos_fretes
        $table = $this->table('tb_config_conjuntos_fretes');
        $table  ->changeColumn('preco_padrao', 'decimal', ['null'=>true,'precision'=>10,'scale'=>2])
                ->changeColumn('preco_capital_padrao', 'decimal', ['null'=>true,'precision'=>10,'scale'=>2])
                ->update();


        //tb_config_conjuntos_fretes_customizados
        $table = $this->table('tb_config_conjuntos_fretes_customizados');
        $table  ->changeColumn('preco', 'decimal', ['null'=>true,'precision'=>10,'scale'=>2])
                ->update();


        //tb_seo_acessos
        $table = $this->table('tb_seo_acessos');
        $table  ->changeColumn('faturamento', 'decimal', ['null'=>true,'precision'=>10,'scale'=>2])
                ->update();


        //tb_seo_acessos_historicos
        $table = $this->table('tb_seo_acessos_historicos');
        $table  ->changeColumn('faturamento', 'decimal', ['null'=>true,'precision'=>10,'scale'=>2])
                ->update();


        //tb_dashboards_utms
        $table = $this->table('tb_dashboards_utms');
        $table  ->changeColumn('faturamentos', 'decimal', ['null'=>true,'precision'=>10,'scale'=>2])
                ->update();


        //tb_pedidos_deducoes
        $table = $this->table('tb_pedidos_deducoes');
        $table  ->changeColumn('valor', 'decimal', ['null'=>true,'precision'=>10,'scale'=>2])
                ->update();


        //tb_produtos_produtos
        $table = $this->table('tb_produtos_produtos');
        $table  ->changeColumn('custo', 'decimal', ['null'=>true,'precision'=>10,'scale'=>2])
                ->changeColumn('porcentagem_fabrica', 'decimal', ['null'=>true,'precision'=>10,'scale'=>2])
                ->changeColumn('comissao_venda', 'decimal', ['null'=>true,'precision'=>10,'scale'=>2])
                ->changeColumn('dimensao_largura', 'decimal', ['null'=>true,'precision'=>10,'scale'=>2])
                ->changeColumn('dimensao_altura', 'decimal', ['null'=>true,'precision'=>10,'scale'=>2])
                ->changeColumn('dimensao_profundidade', 'decimal', ['null'=>true,'precision'=>10,'scale'=>2])
                ->changeColumn('peso', 'decimal', ['null'=>true,'precision'=>10,'scale'=>2])
                ->changeColumn('frete_embutido', 'decimal', ['null'=>true,'precision'=>10,'scale'=>2])
                ->update();


        //tb_produtos_avaliacoes
        /*$table = $this->table('tb_produtos_avaliacoes');
        $table  ->changeColumn('nota', 'decimal', ['null'=>true,'precision'=>10,'scale'=>2])
                ->update();


        //tb_produtos_descontos
        $table = $this->table('tb_produtos_descontos');
        $table  ->changeColumn('porcentagem_fabrica', 'decimal', ['null'=>true,'precision'=>10,'scale'=>2])
                ->changeColumn('valor', 'decimal', ['null'=>true,'precision'=>10,'scale'=>2])
                ->update();*/
    }

    public function down(): void
    {

        //tb_produtos_atributos
        $table = $this->table('tb_produtos_atributos');
        $table  ->changeColumn('custo', 'float', ['null'=>true])
                ->update();

        //tb_admin_empresa
        /*$table = $this->table('tb_admin_empresa');
        $table  ->changeColumn('comissao_vendas', 'float')
                ->update();

        //tb_pedidos_pedidos
        $table = $this->table('tb_pedidos_pedidos');
        $table  ->changeColumn('porcentagem_comissao', 'float', ['null'=>true])
                ->update();

        //tb_carrinho_produtos_historico
        $table = $this->table('tb_carrinho_produtos_historico');
        $table  ->changeColumn('peso', 'float', ['null'=>true])
                ->changeColumn('frete_embutido', 'float', ['null'=>true])
                ->update();

        //tb_config_conjuntos_fretes
        $table = $this->table('tb_config_conjuntos_fretes');
        $table  ->changeColumn('preco_padrao', 'float', ['null'=>true])
                ->changeColumn('preco_capital_padrao', 'float', ['null'=>true])
                ->update();

        //tb_config_conjuntos_fretes_customizados
        $table = $this->table('tb_config_conjuntos_fretes_customizados');
        $table  ->changeColumn('preco', 'float', ['null'=>true])
                ->update();

        //tb_seo_acessos
        $table = $this->table('tb_seo_acessos');
        $table  ->changeColumn('faturamento', 'float', ['null'=>true])
                ->update();

        //tb_seo_acessos_historicos
        $table = $this->table('tb_seo_acessos_historicos');
        $table  ->changeColumn('faturamento', 'float', ['null'=>true])
                ->update();

        //tb_dashboards_utms
        $table = $this->table('tb_dashboards_utms');
        $table  ->changeColumn('faturamentos', 'float', ['null'=>true])
                ->update();

        //tb_pedidos_deducoes
        $table = $this->table('tb_pedidos_deducoes');
        $table  ->changeColumn('valor', 'float', ['null'=>true])
                ->update();

        //tb_produtos_produtos
        $table = $this->table('tb_produtos_produtos');
        $table  ->changeColumn('custo', 'float', ['null'=>true])
                ->changeColumn('porcentagem_fabrica', 'float', ['null'=>true])
                ->changeColumn('comissao_venda', 'float', ['null'=>true])
                ->changeColumn('dimensao_largura', 'float', ['null'=>true])
                ->changeColumn('dimensao_altura', 'float', ['null'=>true])
                ->changeColumn('dimensao_profundidade', 'float', ['null'=>true])
                ->changeColumn('peso', 'float', ['null'=>true])
                ->changeColumn('frete_embutido', 'float', ['null'=>true])
                ->update();

        //tb_produtos_avaliacoes
        /*$table = $this->table('tb_produtos_avaliacoes');
        $table  ->changeColumn('nota', 'float', ['null'=>true])
                ->update();

        //tb_produtos_descontos
        $table = $this->table('tb_produtos_descontos');
        $table  ->changeColumn('porcentagem_fabrica', 'float', ['null'=>true])
                ->changeColumn('valor', 'float', ['null'=>true])
                ->update();*/
    }
}
