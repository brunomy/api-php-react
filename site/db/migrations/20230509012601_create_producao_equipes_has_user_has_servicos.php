<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateProducaoEquipesHasUserHasServicos extends AbstractMigration
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

        $table = $this->table('tb_producao_equipes_has_users_has_servicos', ['id'=>false, 'collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id_equipe',        'integer')
                ->addColumn('id_user',          'integer')
                ->addColumn('id_servico',       'integer', ['null'=>true])
                ->addForeignKey('id_user', 'tb_admin_users', 'id', ['update'=>'RESTRICT', 'delete'=>'RESTRICT'])
                ->addForeignKey('id_equipe', 'tb_producao_equipes', 'id', ['update'=>'RESTRICT', 'delete'=>'RESTRICT'])
                ->addForeignKey('id_servico', 'tb_producao_servicos', 'id', ['update'=>'RESTRICT', 'delete'=>'RESTRICT'])
                ->create();

    }
}
