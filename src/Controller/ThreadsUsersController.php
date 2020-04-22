<?php
namespace App\Controller;


use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Http\Exception\NotFoundException;



class ThreadsUsersController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');
        $this->Auth->allow(["index", "add"]);
    }
    public function index($id)
    {
        $threadsUsers= $this->ThreadsUsers->find('all')->where(['user_id' => $id]);
        $threadTable = $this->ThreadsUsers->find('all');

        foreach($threadsUsers as $user)
        {
           foreach($threadTable as $table)
           {
               if($table['thread_id'] == $user['thread_id'])
               {
                $userInfo[] =[
                    'id' => $table['id'],
                    'thread_id' => $user['thread_id'],
                    'user_id' => $table['user_id'],
                ];
            }
           }
        }
        $this->set([
            'Threads_Users' => $userInfo,
            '_serialize' => ['Threads_Users']
        ]);
    }
    
    public function add()
    {
        $threadsUsers = $this->ThreadsUsers->newEntity();
        if ($this->request->is('post')) {
        
            $threadsUsers = $this->ThreadsUsers->patchEntity($threadsUsers, $this->request->getData());
            
            if ($this->ThreadsUsers->save($threadsUsers)) {
                $this->set([
                    'New Thread' => $threadsUsers,
                    '_serialize' => ['New Thread']
                ]);
            }
        }
    }
    
    public function isAuthorized($user)
    {
   
        if ($this->request->getParam('action') === 'add') {
            return true;
        }

        

        return parent::isAuthorized($user);
    }
}