<?php
namespace App\Controller;


use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Http\Exception\NotFoundException;




class WorkspacesController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');

    }

    public function add()
    {
        $workSpaces = $this->Workspaces->newEntity();
        if ($this->request->is('post')) {
        
            $workSpaces = $this->Workspaces->patchEntity($workSpaces, $this->request->getData());
            
            $workSpaces->owner_user_id = $this->Auth->user('id');
            if ($this->Workspaces->save($workSpaces)) {
                $this->set([
                    'Work Space' => $workSpaces,
                    '_serialize' => ['Work Space']
                ]);
            }
        }
    }
    public function edit($id = null)
    {
        $workSpaces = $this->Workspaces->get($id);
        if ($this->request->is(['post','put']))
        {
            $this->Workspaces->patchEntity($workSpaces, $this->request->getData());
            if ($this->Workspaces->save($workSpaces))
            {
                $this->set([
                    'Work Space' => $workSpaces,
                    '_serialize' => ['Work Space']
                ]);
            }
        
        }
     
    }


    public function isAuthorized($user)
    {
   
        if ($this->request->getParam('action') === 'add') {
            return true;
        }

    
        if (in_array($this->request->getParam('action'), ['edit', 'delete'])) {
           
            $workSpacesId = (int)$this->request->getParam('pass.0');
            if ($this->Workspaces->isOwnedBy($workSpacesId, $user['id'])) {
                return true;
            }
        }

        return parent::isAuthorized($user);
    }
}