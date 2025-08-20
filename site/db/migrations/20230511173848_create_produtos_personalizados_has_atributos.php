<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateProdutosPersonalizadosHasAtributos extends AbstractMigration
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

        $table = $this->table('tb_produtos_personalizados_has_tb_produtos_atributos', ['id'=>false, 'primary_key'=>['id_produto_personalizado','id_atributo'],'collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id_produto_personalizado', 'integer')
                ->addColumn('id_atributo',              'biginteger')
                ->addColumn('selecionado',              'integer',  ['null'=>true])
                ->addColumn('data',                     'timestamp',['null'=>true,'default'=>'CURRENT_TIMESTAMP'])
                ->addForeignKey('id_produto_personalizado', 'tb_produtos_personalizados', 'id', ['update'=>'NO_ACTION', 'delete'=>'CASCADE'])
                ->addForeignKey('id_atributo', 'tb_produtos_atributos', 'id', ['update'=>'NO_ACTION', 'delete'=>'CASCADE'])
                ->addIndex('id_produto_personalizado')
                ->create();

    }
}
