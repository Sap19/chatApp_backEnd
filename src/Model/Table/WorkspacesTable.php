<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class WorkspacesTable extends Table
{
    public function initialize(array $config)
    {
        // Allows time stamp to be saved to the database
        $this->addBehavior('Timestamp');

        // Connects foreign key to the right table 
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
        ]);
    }
    public function validationDefault(Validator $validator)
    {
        //Information validators 
        $validator
            ->notEmpty('name')
            ->requirePresence('name');
        return $validator;
    }
    
    //Searches if the User_id passed throuhg is the owner of the WorkspacesId passed in through
    public function isOwnedBy($workSpacesId, $userId)
    {
    return $this->exists(['id' => $workSpacesId, 'owner_user_id' => $userId]);
    }
}