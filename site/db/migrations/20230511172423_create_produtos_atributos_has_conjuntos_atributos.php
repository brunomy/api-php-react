<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateProdutosAtributosHasConjuntosAtributos extends AbstractMigration
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

        $table = $this->table('tb_produtos_atributos_has_conjuntos_atributos', ['id'=>false, 'primary_key'=>['id_atributo','id_conjunto'],'collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id_atributo',          'biginteger')
                ->addColumn('id_conjunto',          'biginteger')
                ->addColumn('desabilitado',         'integer',  ['null'=>true,'default'=>0])
                ->addForeignKey('id_atributo', 'tb_produtos_atributos', 'id', ['update'=>'NO_ACTION', 'delete'=>'CASCADE'])
                ->addForeignKey('id_conjunto', 'tb_produtos_conjuntos_atributos', 'id', ['update'=>'NO_ACTION', 'delete'=>'CASCADE'])
                ->addIndex('id_atributo')
                ->create();

    }
}
