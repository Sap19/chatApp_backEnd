<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class ThreadsTable extends Table
{
    public function initialize(array $config)
    {
        // Allows time stamp to be saved to the database
        $this->addBehavior('Timestamp');
        
        // Connects foreign key to the right table 
        $this->belongsTo('Workspaces', [
            'foreignKey' => 'workspace_id',
        ]);
       
    }
    
}