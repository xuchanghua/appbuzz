<?php
namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use User\Model\User;

class UserController extends AbstractActionController
{
    protected $userTable;

    public function indexAction()
    {
    }

    public function checkenterpriseuserAction()
    {
        $users = $this->getUserTable()->fetchAll();

        $postUser = $_POST['username'];
        $postPass = $_POST['password'];

        //check if the username is exist:
        if(!$this->getUserTable()->checkUser($postUser))
        {
            die( "The user was not exist.");
        }
        //check if the username and the password are corresponded:
        if($this->getUserTable()->getUser($postUser)->password != $postPass)
        {
            die("Incorrect Password");
        }

    }

    public function checkmediauserAction()
    {
    }

    public function checkadminuserAction()
    {
    }

    public function getUserTable()
    {
        if(!$this->userTable){
            $sm = $this->getServiceLocator();
            $this->userTable = $sm->get('User\Model\UserTable');
        }
        return $this->userTable;
    }
}
