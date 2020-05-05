<?php
namespace App\Controller;


use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\TableRegistry;


class FriendsController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');
        $this->Auth->allow(["index", "delete", "edit", "view"]);
    }

    public function index() // passes all the information in the table and turns it to json to be read by the request
    {
        $friends = $this->Friends->find('all');
        
        $this->set([
            'friends' => $friends,
            '_serialize' => ['friends']
        ]);
    }
    public function view($id) // passes all the threads from a specific workspace_id
    {
        $friends = $this->Friends->find('all')->where(['user_id' => $id]);
        
        $this->set([
            'friends list' => $friends,
            '_serialize' => ['friends list']
        ]);
    }

    public function add() // adds a new friend to the list on both users
    {
        $friends = $this->Friends->newEntity();
        $this->Users = TableRegistry::get('Users');
        //$this->WorkspaceUsers = TableRegistry::get('WorkspaceUsers');
        
        if ($this->request->is('post')) {
        
            $friends = $this->Friends->patchEntity($friends, $this->request->getData());
            
            if ($this->Friends->save($friends)) {
                $this->set([
                    'New friend' => $friends,
                    '_serialize' => ['New friend']
                ]);
            }
        
               // adds friend to the other users friends list 
               $user = $this->Users->get($friends->user_id);
               $friendsList = $this->Friends->newEntity();
                $friendsList->friend_user_id = $friends->user_id;
                $friendsList->user_id = $friends->friend_user_id;
                $friendsList->friend_username = $user->username;
                $this->Friends->save($friendsList);
                    
            


            
        }
    }
    
    public function delete($id) //  allows to delete thread by thread_id passed through
    {
        $this->request->allowMethod(['post', 'delete']);

        $threads = $this->Threads->get($id);
        if ($this->Threads->delete($threads))
        {
            $this->set([
                'Thread Deleted' => $threads,
                '_serialize' => ['Thread Deleted']
            ]);
        }
        
    }
    public function isAuthorized($user) // checks if user is authorized 
    {
   
        if ($this->request->getParam('action') === 'add') {
            return true;
        }

        
        return parent::isAuthorized($user);
    }
}