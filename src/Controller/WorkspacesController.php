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
        $this->Auth-> allow([ 'index','inWorkspace', 'view']);
    }
    public function index() // gets all the Workspaces and Displays them in json
    {
        $workSpaces = $this->Workspaces->find('all', ['limit' => $all]);
        $this->set([
            'Workspaces' => $workSpaces,
            '_serialize' => ['Workspaces']
        ]);
    }
    public function view($id) // gets a specific workspace by $id
    {
        $workSpaces = $this->Workspaces->get($id);
        $this->set([
            'Workspaces' => $workSpaces,
            '_serialize' => ['Workspaces']
        ]);
    }
    public function inWorkspace($id) // Finds all users that are in the same workspace as the User_id that is passed through
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
    public function add() // adds workspaces 
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
    public function edit($id = null) // allows to edit workspace name by workspace_id passed through
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
    public function delete($id) // allows to Delete workspace by workspace_id passed through
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

    public function isAuthorized($user) // checks if user is authorized 
    {
   
        if ($this->request->getParam('action') === 'add') {
            return true;
        }

    //-------------- Checks if the user_id owns the workspace to be able to edit or delete the workspace --------- 
        if (in_array($this->request->getParam('action'), ['edit', 'delete'])) { 
           
            $workSpacesId = (int)$this->request->getParam('pass.0');
            if ($this->Workspaces->isOwnedBy($workSpacesId, $user['id'])) {
                return true;
            }
        }

        return parent::isAuthorized($user);
    }
}