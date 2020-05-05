<?php
namespace App\Controller;


use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\TableRegistry;


class WorkspaceUsersController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');
        $this->Auth->allow(["index", "inWorkspace","UsersInWorkspace"]);
    }

    // Gets all the users that are inside of the workspace_id passed in 
    public function UsersInWorkspace($work_id)
    {
        $workspacesUser = $this->WorkspaceUsers->find('all')->where(['workspace_id' => $work_id]);
        $this->Users = TableRegistry::get('Users');
        $user = $this->Users->find('all');

        foreach($workspacesUser as $workSpace)
        {
            foreach($user as $userInfo)
            {
                if($workSpace['user_id'] == $userInfo['id'])
                {
                    $infoReturn[] = [
                        "id" => $workSpace['id'],
                        "workspace_id" => $workSpace['workspace_id'],
                        "user_id" => $workSpace['user_id'],
                        "username" => $userInfo['username']
                    ];
                }
            }

        }

        $this->set([
            'WorkSpace_Users' => $infoReturn,
            '_serialize' => ['WorkSpace_Users']
        ]);
    }

   // Finds all users that are in the same workspace as the User_id that is passed through
    public function inWorkspace($id)
    {
        $workspacesUser= $this->WorkspaceUsers->find('all')->where(['user_id' => $id]);
        $workspacesTable = $this->WorkspaceUsers->find('all');
        
       foreach($workspacesUser as $user)
        {
           foreach($workspacesTable as $table)
           {
               if($table['workspace_id'] == $user['workspace_id'])
               {
                $userInfo[] =[
                    'id' => $table['id'],
                    'WorkSpace_id' => $user['workspace_id'],
                    'user_id' => $table['user_id'],
                ];
            }
           }
        }
        $this->set([
            'WorkSpace_Users' => $userInfo,
            '_serialize' => ['WorkSpace_Users']
        ]);
    }
    // Displays all workspaces the user is in 
   public function index($id)
    {
        $workspacesUser= $this->WorkspaceUsers->find('all')->where(['user_id' => $id]);
        $this->set([
            'WorkSpaces' => $workspacesUser,
            '_serialize' => ['WorkSpaces']
        ]);
    }
  
    // adds to the workUser table 
    public function add()
    {
        $workspacesUser = $this->WorkspaceUsers->newEntity();
        if ($this->request->is('post')) {
        
            $workspacesUser = $this->WorkspaceUsers->patchEntity($workspacesUser, $this->request->getData());
            
            if ($this->WorkspaceUsers->save($workspacesUser)) {
                $this->set([
                    'New_WorkSpace_User' => $workspacesUser,
                    '_serialize' => ['New_WorkSpace_User']
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