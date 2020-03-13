<?php
use Migrations\AbstractMigration;

class Workspaces extends AbstractMigration
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
        $table = $this->table('workspaces');
        
        $table->addColumn('name', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('created', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('owner_user_id', 'integer', [
            'default' => null,
            'limit' => 11,
            'null' => false,
        ]);
       
        
        $table->create();
    }
}
