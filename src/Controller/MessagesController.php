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
        $this->Auth-> allow([ 'add']);
    }

    public function add()
    {
        $message = $this->Messages->newEntity();
        if ($this->request->is('post')) {
        
            $message = $this->Messages->patchEntity($message, $this->request->getData());
            
            if ($this->Messages->save($message)) {
                $this->set([
                    'New Message' => $message,
                    '_serialize' => ['New Message']
                ]);
            }
        }
    }

}