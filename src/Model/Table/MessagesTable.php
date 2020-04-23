<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class MessagesTable extends Table
{
    public function initialize(array $config)
    {
        // Allows time stamp to be saved to the database
        $this->addBehavior('Timestamp');

        // Connects foreign key to the right table 
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
        ]);
        // Connects foreign key to the right table 
        $this->belongsTo('Threads', [
            'foreignKey' => 'thread_id',
        ]);
    }
    public function validationDefault(Validator $validator)
    {
        // validates infomration needed
        $validator
            ->notEmpty('body')
            ->requirePresence('body');
        return $validator;
    }

}