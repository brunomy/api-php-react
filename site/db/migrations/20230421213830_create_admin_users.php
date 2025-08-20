<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateAdminUsers extends AbstractMigration
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

        $table = $this->table('tb_admin_users', ['collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('id_grupo',     'integer')
                ->addColumn('stats',        'smallinteger', ['null'=>true])
                ->addColumn('usuario',      'string',       ['null'=>true, 'limit'=>45])
                ->addColumn('senha',        'string',       ['null'=>true, 'limit'=>100])
                ->addColumn('email',        'string',       ['null'=>true, 'limit'=>45])
                ->addColumn('nome',         'string',       ['null'=>true, 'limit'=>45])
                ->addColumn('avatar',       'string',       ['null'=>true, 'limit'=>255])
                ->addColumn('telefone',     'string',       ['null'=>true, 'limit'=>45])
                ->addColumn('code',         'string',       ['null'=>true, 'limit'=>100])
                ->addColumn('session',      'string',       ['null'=>true, 'limit'=>45])
                ->addColumn('data',         'timestamp',    ['default'=>'CURRENT_TIMESTAMP'])
                ->addColumn('id_agendor',   'string',       ['null'=>true, 'limit'=>255])
                ->addColumn('bate_ponto',   'smallinteger', ['null'=>true])
                ->addColumn('almoco',       'smallinteger', ['null'=>true])
                ->addColumn('hora_entrada', 'time',         ['null'=>true])
                ->addColumn('hora_almoco',  'time',         ['null'=>true])
                ->addColumn('hora_retorno', 'time',         ['null'=>true])
                ->addColumn('hora_saida',   'time',         ['null'=>true])
                ->addColumn('user_key',     'string',       ['null'=>true, 'limit'=>45])
                ->addForeignKey('id_grupo', 'tb_admin_grupos', 'id', ['delete'=>'RESTRICT','update'=>'CASCADE'])
                ->create();

    }
}
