<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateBannersBanners extends AbstractMigration
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

        $table = $this->table('tb_banners_banners', ['collation' => 'utf8mb4_swedish_ci']);
        $table  ->addColumn('nome',         'string')
                ->addColumn('subtitulo',    'string',  ['null'=>true])
                ->addColumn('titulo_preco', 'string',  ['null'=>true])
                ->addColumn('preco',        'string',  ['null'=>true, 'limit'=>100])
                ->addColumn('imagem',       'string',  ['null'=>true])
                ->addColumn('link',         'string',  ['null'=>true])
                ->addColumn('target',       'string',  ['null'=>true, 'limit'=>45])
                ->addColumn('texto_link',   'string',  ['null'=>true, 'limit'=>100])
                ->addColumn('stats',        'integer',  ['null'=>true])
                ->addColumn('ordem',        'integer',  ['null'=>true])
                ->addColumn('data',         'timestamp',  ['default'=>'CURRENT_TIMESTAMP'])
                ->create();

    }
}
