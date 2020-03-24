<?php
namespace App\Controller;


use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Http\Exception\NotFoundException;



class ThreadsController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');
        $this->Auth->allow(["index"]);
    }
    public function index()
    {
        $threads = $this->paginate($this->Threads);
        
        $this->set([
            'Threads' => $threads,
            '_serialize' => ['Threads']
        ]);
    }
    public function add()
    {
        $threads = $this->Threads->newEntity();
        if ($this->request->is('post')) {
        
            $threads = $this->Threads->patchEntity($threads, $this->request->getData());
            
            if ($this->Threads->save($threads)) {
                $this->set([
                    'New Thread' => $threads,
                    '_serialize' => ['New Thread']
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