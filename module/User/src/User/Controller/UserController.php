<?php
namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use User\Model\User;

class UserController extends AbstractActionController
{
    public function indexAction()
    {
    }

    public function checkenterpriseuserAction()
    {
        $users = $this->getUserTable()->fetchAll();

        foreach($users as $user)
        {
            echo $user->username."</br>";
            echo $user->password."</br>";
        }
        $userrow = $this->getUserTable()->getUser("admin");
        echo $userrow->username."</br>";
        echo $userrow->password."</br>";
/*
        return new ViewModel(array(
            'users' => $this->getUserTable()->fetchAll(),
        ));
*/
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
