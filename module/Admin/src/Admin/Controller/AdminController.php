<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Model\Admin;          // <-- Add this import
use Admin\Form\AdminForm;       // <-- Add this import
use Zend\Session\Container as SessionContainer;
use User\Model\User;

class AdminController extends AbstractActionController
{
    protected $userTable;
    protected $adminTable;

    public function indexAction()
    {
        //Authenticate the user information from the session
        $this->_authenticateSession();
    }

    public function loginAction()
    {
    }

    public function getAdminTable()
    {
        if (!$this->adminTable) {
	    $sm = $this->getServiceLocator();
	    $this->adminTable = $sm->get('Admin\Model\AdminTable');
        }
        return $this->adminTable;
    }

    public function getUserTable()
    {
        if(!$this->userTable){
            $sm = $this->getServiceLocator();
            $this->userTable = $sm->get('User\Model\UserTable');
        }
        return $this->userTable;
    }

    protected function _authorizeUser($type, $user, $pass)
    {        
         //check if the username or password is empty
        if((!$user)||(!$pass)
            ||(!$this->getUserTable()->checkUser($user))
            ||($this->getUserTable()->getUser($user)->fk_user_type != $type))
        {
            $this->redirect()->toRoute('admin',array('action'=>'login'));
        }        
        //check if the username and the password are corresponded:
        if($this->getUserTable()->getUser($user)->password != $pass)
        {
            echo "<a href='/'>Back</a></br>";
            die("Incorrect Password");
        }

        return true;
    }

    protected function _authenticateSession()
    {        
        $this->session = new SessionContainer('userinfo');
        $username = $this->session->username;
        $password = $this->session->password;
        if($this->_authorizeUser(3, $username, $password))
        {
            echo "Welcome, ".$username;
        }
    }
}
