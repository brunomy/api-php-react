<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateInformacaoDestaque extends AbstractMigration
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

        $table = $this->table('tb_informacao_destaque', ['collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('texto',            'string',   ['null'=>true])
                ->addColumn('cor_texto',        'string',   ['limit'=>45])
                ->addColumn('descricao',        'string',   ['null'=>true])
                ->addColumn('cor_descricao',    'string',   ['limit'=>45])
                ->addColumn('bg_color',         'string',   ['limit'=>45])
                ->addColumn('texto_botao',      'string',   ['limit'=>128])
                ->addColumn('cor_texto_botao',  'string',   ['limit'=>45])
                ->addColumn('cor_botao',        'string',   ['limit'=>45])
                ->addColumn('ativo',            'string',   ['limit'=>1])
                ->addColumn('link',             'string')
                ->create();

    }
}
