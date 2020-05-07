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
use  Cake\I18n\FrozenTime;
use Cake\Core\Configure;
use Cake\Auth\DefaultPasswordHasher;
use Cake\Mailer\TransportFactory;
use Cake\Mailer\Email;


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
        $this->Auth-> allow([ 'add', 'login', 'view', 'indexs', 'forgotPassword', 'resetPassword']);
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


	/**
	 * Generate Password Reset Token
	 *
	 * @param User $user
	 * @param mixed int
	 * @return void
	 */
    public function generatePasswordResetToken($user, int $expiration = 15): string 
    {
       
		$data = [
            'id' => $user->id,
			'expiration' => (new FrozenTime())->addMinutes($expiration)
        ];
        
        $key = Security::getSalt();

		return base64_encode(Security::encrypt(json_encode($data), $key));
	}

	/**
	 * Validate Password Reset Token
	 *
	 * @param string $value
	 * @return User|null
	 */
    public function validatePasswordResetToken($value)
    {   
        
        $key = Security::getSalt();
       
		if ($token = Security::decrypt(base64_decode($value), $key)) {
            
            $token = json_decode($token, true);
            $exp = new FrozenTime($token['expiration']);
			if  ($exp->gt(new FrozenTime())) {
               
                return $this->Users->get($token['id']);
                
			}
        }

		return null;
    }

    //----- Sends a link with a token to the User's Email so they can reset the password
    public function forgotPassword($Useremail)
    {
        
        if($this->request->is('post'))
        {
            
       
            $user = $this->Users->find('all')->where(['email'=>$Useremail])->first();
            
            $tokenID = $this->generatePasswordResetToken($user);
            
           
          dd($tokenID);

                //$this->Flash->success('Reset Password link has been sent to your email ('.$Useremail.')! Please Check Your Email.');
                $this->set([
                    'email' => "Email Has Been Sent",
                    '_serialize' => ['email'],
                ]);
            
                TransportFactory::setConfig('sendgrid', [
                    'host' => 'smtp.sendgrid.net',
                    'port' => 587,
                    'username' => 'apikey',
                    'password' => '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',
                    'className' => 'Smtp'
                  ]);
                  $email = new Email('default');
                  $email->setTransport('sendgrid');
                  $email->setEmailFormat('html');
                  $email->setFrom('steven.portillo@hydracor.net', 'ChatApp');
                  $email->setSubject('Please Confirm your rest password');
                  $email->setTo($Useremail);
                  $email->send('Hello '.$Useremail.' <br/> Please Click the link below to reset your password <br/><br/><a href="http://206.189.202.188:8082/resetPassword.html?email='.$Useremail.'&token='.$tokenID.'">Reset Password</a>');
               
        }
    }
// ------- When user submits reset password it validates if the token belongs to that user and if it hasnt expired and changes passowrd to new password
    public function resetPassword($Useremail)
    {
        
        $token = $this->request->getQuery('token');
        $password = $this->request->getQuery('password');
       
        
        $user= $this->Users->find('all')->where(['email'=>$Useremail])->first();
       
        if ($this->request->is('post')) 
        {
           
           
            $tokenCheck = $this->validatePasswordResetToken($token);
            $user->password = $password ;
         
            if ($this->Users->save($user) && $tokenCheck['id'] ==  $user->id) 
            {
                
                $this->set([
                    'password' => "Your password has been updated.",
                    '_serialize' => ['password'],
                ]);
                
            }
            else
            {
            $this->set([
                'password' => "Token Expired.",
                '_serialize' => ['password'],
            ]);
            }
        
        }
    }





}