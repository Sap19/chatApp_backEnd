<?php
namespace App\Controller;


use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Http\Exception\NotFoundException;
use Firebase\JWT\JWT;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Utility\Security;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\ORM\Entity;
use Cake\ORM\ResultSet;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 *
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class UsersController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');
        $this->Auth-> allow([ 'add', 'login', 'view', 'indexs']);
    }
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index() // passes all the information in the Users table and turns it to json to be read by the request
    {
        
        $users= $this->Users->find('all', ['limit' => $all]);
        $this->set([
            'users' => $users,
            '_serialize' => ['users']
        ]);
    }
    public function indexs() // passes all the Users in the table but only passes user_id and username
    {
        $users= $this->Users->find('all', ['limit' => $all]);
       
        foreach ($users as $user) {
            $userInfo[] = [
                'id' => $user['id'],
                'username' => $user['username'],
            ];
        }
        $this->set([
            'users' => $userInfo,
            '_serialize' => ['users']
        ]);
    }
    public function view($id = null) // allow specific User to be found by User_id passed in  
    {
        
        $users = $this->Users->find('all')->where(['username' => $id]);
      
        foreach($users as $user)
        $userinfo[] = [
            'id' => $user->id,
            'username' => $id,
        ];

        $this->set([
            'user' => $userinfo,
            '_serialize' => ['user']
        ]);
        
    }
    public function add() // adds user to the user table and returns a JWT token for authentication 
    {

        $user = $this->Users->newEntity();
        if ($this->request->is('post')) 
        {
            $admin=$this->Auth->identify();
            if($this->request->getData('super_user') === 1){
                if($admin['super_user'] === 1){
                    $user = $this->Users->patchEntity($user, $this->request->getData());
                    if ($this->Users->save($user)) {
                        $payload = [
                            'sub' => $user['id'],
                                    'exp' =>  time() + 604800,
                                    'role' => $user['super_user']
                        ];
                        $this->set('data', [
                            'id' => $user['id'],
                            'token' => JWT::encode($payload,
                            Security::getSalt(),"HS256")
                        ]);
                    }
                    else
                    {
                        $this->set('data', [
                            'message' => "Failed User with that username or email already exsist"
                        ]);
                    }     
                } else {
                    $this->set('data', [
                        'message' => "You are not authorized to create an admin account!"
                    ]);
                }
            }
            else
            {
                $user = $this->Users->patchEntity($user, $this->request->getData());
                if ($this->Users->save($user)) 
                {
                    $payload = [
                        'sub' => $user['id'],
                                'exp' =>  time() + 604800,
                                'role' => $user['super_user']
                    ];
                    $this->set('data', [
                        'id' => $user['id'],
                        'token' => JWT::encode($payload,
                        Security::getSalt(),"HS256")
                    ]);
                }
                else
                    {
                        $this->set('data', [
                            'message' => "Failed"
                        ]);
                    }   
            }
        }
        $this->set([
            'user' => $user,
            '_serialize' => ['id', 'data'],
        ]);
    }
    public function login() // logins in user if information passed through is correct and gives a JWT token to authenticate.
    {
        if ($this->request->is('post')) {
            $user = $this->Auth->identify();
            if ($user) 
            {
                $payload = [
                    'sub' => $user['id'],
                            'exp' =>  time() + 604800,
                            'role' => $user['super_user']
                ];
            $this->set([
                'success' => true,
                'data' => [
                    'token' => JWT::encode($payload,
                    Security::getSalt(),"HS256")
                ],
                '_serialize' => ['success', 'data']
            ]);
            }
            else
            {
                throw new UnauthorizedException('Invalid username or password');
            }
        }
    }
}