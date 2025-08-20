<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddTitleToProductsLists extends AbstractMigration
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

        $table = $this->table('tb_produtos_produtos');
        $table  ->addColumn('titulo_lista_personalizados', 'string', ['null'=>true, 'after' => 'titulo_itens_personalizacao'])
                ->addColumn('descricao_lista_personalizados', 'text', ['null'=>true, 'after' => 'titulo_lista_personalizados'])
                ->update();

    }
}
