<?php
namespace App\Controller;


use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Http\Exception\NotFoundException;
use Firebase\JWT\JWT;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Utility\Security;


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
        $this->Auth-> allow([ 'add', 'login']);
    }
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        $users = $this->paginate($this->Users);
        
        $this->set([
            'users' => $users,
            '_serialize' => ['users']
        ]);
    }

    public function view($id = null)
    {
        
        $user = $this->Users->get($id);
        $this->set([
            'user' => $user,
            '_serialize' => ['user']
        ]);
        
    }
    public function add()
    {

        $user = $this->Users->newEntity();
        if ($this->request->is('post')) 
        {
            $admin=$this->Auth->identify();
            if($this->request->getData('super_user') === 1){
                if($admin['super_user'] === 1){
                    $user = $this->Users->patchEntity($user, $this->request->getData());
                    if ($this->Users->save($user)) {
                        $this->set('data', [
                            'id' => $user['id'],
                            'token' => JWT::encode(
                                [
                                    'sub' => $user['id'],
                                    'exp' =>  time() + 604800,
                                    'role' => $user['super_user']
                                ],
                            Security::getSalt())
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
                    $this->set('data', [
                        'id' => $user['id'],
                        'token' => JWT::encode(
                            [
                                'sub' => $user['id'],
                                'exp' =>  time() + 604800,
                                'role' => $user['super_user']
                            ],
                        Security::getSalt())
                    ]);
                }
                else
                    {
                        $this->set('data', [
                            'message' => "Failed User with that username or email already exsist"
                        ]);
                    }   
            }
        }
        $this->set([
            'user' => $user,
            '_serialize' => ['id', 'data'],
        ]);
    }
    public function login()
    {
        if ($this->request->is('post')) {
            $user = $this->Auth->identify();
            if ($user) 
            {
            $this->set([
                'success' => true,
                'data' => [
                    'token' => JWT::encode([
                        'sub' => $user['id'],
                        'exp' =>  time() + 604800,
                        'role' => $user['super_user']
                    ],
                    Security::getSalt())
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