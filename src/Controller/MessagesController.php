<?php
namespace App\Controller;


use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\ORM\Entity;
use Cake\ORM\ResultSet;

class MessagesController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');
        $this->Auth->allow(["index"]);
    }
    public function index($id = null)
    {
        $query = $this->Messages->find('all')->where(['thread_id' => $id]);
        //$message = $this->paginate($this->Messages->getAlias($this->Threads));
        //$message = $this->Messages->thread_id->get($thread_id);
        //$query = $this->Messages->find('all');
        //$query = $this->Messages->find('ownedBy', ['thread' => $id]);
        //dd($query);
        //$row = $query->first();
       
        $this->set([
            'Messages' => $query,
            '_serialize' => ['Messages']
        ]);
        
    }
    public function add()
    {
        $message = $this->Messages->newEntity();
        if ($this->request->is('post')) {
        
            $message = $this->Messages->patchEntity($message, $this->request->getData());
            $message->user_id = $this->Auth->user('id');
            if ($this->Messages->save($message)) {
                $this->set([
                    'New Message' => $message,
                    '_serialize' => ['New Message']
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