<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class InsertInitialDpStatus extends AbstractMigration
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
    public function up()
    {
        // Limpa os dados existentes da tabela
        $this->execute('DELETE FROM dp_status');

        // Insere os novos registros com IDs fixos
        $this->execute("INSERT INTO dp_status (id, titulo) VALUES 
            (0, 'pendente'),
            (1, 'em andamento'),
            (2, 'parado'),
            (4, 'finalizado')");
    }

    public function down()
    {
        // Remove os registros inseridos (opcional)
        $this->execute('DELETE FROM dp_status WHERE id IN (0,1,2,4)');
    }
}
