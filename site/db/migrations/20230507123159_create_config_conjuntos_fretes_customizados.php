<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateConfigConjuntosFretesCustomizados extends AbstractMigration
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

        $table = $this->table('tb_config_conjuntos_fretes_customizados', ['id'=>false, 'collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id_conjunto',              'integer')
                ->addColumn('id_estado',                'integer')
                ->addColumn('id_cidade',                'integer',  ['null'=>true])
                ->addColumn('prazo',                    'integer',  ['null'=>true])
                ->addColumn('preco',                    'float',    ['null'=>true])
                ->addColumn('data',                     'timestamp',['null'=>true, 'default'=>'CURRENT_TIMESTAMP'])
                ->addForeignKey('id_cidade', 'tb_config_cidades', 'id', ['delete'=>'CASCADE','update'=>'SET_NULL'])
                ->addForeignKey('id_conjunto', 'tb_config_conjuntos_fretes', 'id', ['delete'=>'CASCADE','update'=>'RESTRICT'])
                ->addForeignKey('id_estado', 'tb_config_estados', 'id', ['delete'=>'CASCADE','update'=>'RESTRICT'])
                ->create();

    }
}
