<?php
namespace App\Controller;


use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Http\Exception\NotFoundException;



class MessagesController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');
        
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