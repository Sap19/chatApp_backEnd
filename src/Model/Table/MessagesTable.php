<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class MessagesTable extends Table
{
    public function initialize(array $config)
    {
        $this->addBehavior('Timestamp');
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
        ]);
        $this->belongsTo('Threads', [
            'foreignKey' => 'thread_id',
        ]);
    }
    public function validationDefault(Validator $validator)
    {
        $validator
            ->notEmpty('body')
            ->requirePresence('body');
        return $validator;
    }

}