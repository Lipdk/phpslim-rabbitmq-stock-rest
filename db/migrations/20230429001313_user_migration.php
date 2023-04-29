<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UserMigration extends AbstractMigration
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
        $table = $this->table('users');
        $table->addColumn('username', 'string', ['limit' => 20])
            ->addColumn('password', 'string', ['limit' => 80])
            ->addColumn('email', 'string', ['limit' => 100])
            ->addColumn('name', 'string', ['limit' => 200])
            ->addColumn('created', 'datetime')
            ->addColumn('updated', 'datetime', ['null' => true])
            ->addIndex(['username', 'email'], ['unique' => true])
            ->create();
    }
}
