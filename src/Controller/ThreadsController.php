<?php
namespace App\Controller;


use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\TableRegistry;


class ThreadsController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');
        $this->Auth->allow(["index", "delete", "edit", 'inThread']);
    }

    public function index() // passes all the information in the table and turns it to json to be read by the request
    {
        $threads = $this->Threads->find('all');
        
        $this->set([
            'Threads' => $threads,
            '_serialize' => ['Threads']
        ]);
    }
    public function inThread($id) // passes all the threads from a specific workspace_id
    {
        $threads = $this->Threads->find('all')->where(['workspace_id' => $id]);
        
        $this->set([
            'Threads_in_Workspace' => $threads,
            '_serialize' => ['Threads_in_Workspace']
        ]);
    }

    public function add() // adds new threads to the db
    {
        $threads = $this->Threads->newEntity();
        $ThreadUsers = TableRegistry::get('ThreadsUsers');
        $this->WorkspaceUsers = TableRegistry::get('WorkspaceUsers');
        
        if ($this->request->is('post')) {
        
            $threads = $this->Threads->patchEntity($threads, $this->request->getData());
            
            if ($this->Threads->save($threads)) {
                $this->set([
                    'New Thread' => $threads,
                    '_serialize' => ['New Thread']
                ]);
            }
            

            //---Adds all users in the workspace to the new thread
            $workspacesUser= $this->WorkspaceUsers->find('all')->where(['workspace_id' => $threads->workspace_id]);
            foreach($workspacesUser as $users)
            {
               
                $threadsUsers = $ThreadUsers->newEntity();
                $threadsUsers->thread_id = $threads->id;
                $threadsUsers->user_id = $users['user_id'];

                $ThreadUsers->save($threadsUsers);
                    
            
            }


            
        }
    }
    public function edit($id = null) // allows to edit thread name by thread_id passed through
    {
        $threads = $this->Threads->get($id);
        if ($this->request->is(['post','put']))
        {
            $this->Threads->patchEntity($threads, $this->request->getData());
            if ($this->Threads->save($threads))
            {
                $this->set([
                    'Thread Edited' => $threads,
                    '_serialize' => ['Thread Edited']
                ]);
            }
        
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