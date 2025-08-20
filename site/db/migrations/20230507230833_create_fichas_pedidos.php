<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateFichasPedidos extends AbstractMigration
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

        $table = $this->table('tb_fichas_pedidos', ['collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id_modelo',      'integer',  ['null'=>true])
                ->addColumn('id_pedido',      'integer',  ['null'=>true])
                ->addColumn('id_cidade',      'integer',  ['null'=>true])
                ->addColumn('id_estado',      'integer',  ['null'=>true])
                ->addColumn('keywords',       'string',   ['null'=>true])
                ->addColumn('foto',           'string',   ['null'=>true])
                ->addForeignKey('id_modelo', 'tb_fichas_modelos', 'id', ['delete'=>'RESTRICT','update'=>'RESTRICT'])
                ->create();

    }
}
