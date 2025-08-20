<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateProdutosDestaquesPersonalizados extends AbstractMigration
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

        $table = $this->table('tb_produtos_destaques_personalizados', ['collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id_personalizado',     'integer',  ['null'=>true])
                ->addColumn('id_produto',           'integer',  ['null'=>true])
                ->addColumn('ordem',                'integer',  ['null'=>true])
                ->addColumn('data',                 'timestamp',['null'=>true,'default'=>'CURRENT_TIMESTAMP'])
                ->addForeignKey(['id_personalizado','id_produto'], 'tb_produtos_personalizados', ['id','id_produto'], ['update'=>'SET_NULL', 'delete'=>'CASCADE'])
                ->create();

    }
}
