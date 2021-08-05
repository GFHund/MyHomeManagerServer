<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class SessionTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $sessionTable = $this->table('session',['id' => false,'primary_key'=>'id']);
        $sessionTable->addColumn('id','string',['limit' => 40,'null' => false])
        ->addColumn('created','datetime',['null' => true,'default' => 'CURRENT_TIMESTAMP'])
        ->addColumn('modified','datetime',['null' => true,'default' => 'CURRENT_TIMESTAMP'])
        ->addColumn('data','binary',['null' => true,'default' => null])
        ->addColumn('expires','integer',['null' => true,'default' => null])
        ->create();
    }
}
