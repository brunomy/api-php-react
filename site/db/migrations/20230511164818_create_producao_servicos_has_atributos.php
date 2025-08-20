<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateProducaoServicosHasAtributos extends AbstractMigration
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

        $table = $this->table('tb_producao_servicos_has_atributos', ['id'=>false, 'primary_key'=>['id_servico','id_atributo'],'collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id_servico',           'integer')
                ->addColumn('id_atributo',          'biginteger')
                ->addForeignKey('id_servico', 'tb_producao_servicos', 'id', ['update'=>'NO_ACTION', 'delete'=>'NO_ACTION'])
                ->addForeignKey('id_atributo', 'tb_produtos_atributos', 'id', ['update'=>'NO_ACTION', 'delete'=>'NO_ACTION'])
                ->addIndex('id_servico')
                ->create();

    }
}
