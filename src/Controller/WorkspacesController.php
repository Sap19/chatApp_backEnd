<?php
namespace App\Controller;


use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\TableRegistry;



class WorkspacesController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');
        $this->Auth-> allow([ 'index','inWorkspace']);
    }
    public function index()
    {
        $workSpaces = $this->Workspaces->find('all', ['limit' => $all]);
        $this->set([
            'Workspaces' => $workSpaces,
            '_serialize' => ['Workspaces']
        ]);
    }
    public function inWorkspace($id)
    {
        $this->WorkspaceUsers = TableRegistry::get('WorkspaceUsers');
        $workSpacesUsers = $this->WorkspaceUsers->find('all')->where(['user_id' => $id]);

        $workspace = $this->Workspaces->find('all');
       
        foreach($workSpacesUsers as $user)
        {
           foreach($workspace as $table)
           {
               if($table['id'] == $user['workspace_id'])
               {
                $userInfo[] =[
                    'id' => $table['id'],
                    'user_id' => $user['user_id'],
                    'name' => $table['name'],
                    'created' =>$table['created'],
                    'owner_user_id' =>$table['owner_user_id']
                ];
            }
           }
        }
        $this->set([
            'Workspaces' => $userInfo,
            '_serialize' => ['Workspaces']
        ]);
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
    public function delete($id)
    {
        $this->request->allowMethod(['post', 'delete']);

        $workSpaces = $this->Workspaces->get($id);
        if ($this->Workspaces->delete($workSpaces))
        {
            $this->set([
                'Work Space Deleted' => $workSpaces,
                '_serialize' => ['Work Space Deleted']
            ]);
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