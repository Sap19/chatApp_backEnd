<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class WorkspacesTable extends Table
{
    public function initialize(array $config)
    {
        $this->addBehavior('Timestamp');
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
        ]);
    }
    public function validationDefault(Validator $validator)
    {
        $validator
            ->notEmpty('name')
            ->requirePresence('name');
        return $validator;
    }
    
    public function isOwnedBy($workSpacesId, $userId)
    {
    return $this->exists(['id' => $workSpacesId, 'owner_user_id' => $userId]);
    }
}