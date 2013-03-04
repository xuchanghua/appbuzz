<?php
namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Header\Cookie;
use Zend\Http\PhpEnvironment\Response;
use Zend\Session\Container as SessionContainer;
use User\Model\User;

class UserController extends AbstractActionController
{
    protected $userTable;

    public function indexAction()
    {
    }

    public function checkenterpriseuserAction()
    {
        $postUser = $_POST['username'];
        $postPass = $_POST['password'];
        //Authorize the user:
        $this->_authorizeUser('1', $postUser, $postPass);
        //Set Session for the authorized user:
        $this->session = new SessionContainer('userinfo');
        $this->session->username = $postUser;
        $this->session->password = $postPass;
        //Set Cookies for the authorized user:
        setcookie(
            "username",
            $postUser,
            time() + ( 7 * 24 * 3600),
            '/',
            'local.appbuzz'
            );
        setcookie(
            "password",
            $postUser,
            time() + ( 7 * 24 * 3600),
            '/',
            'local.appbuzz'
            );
        //redirect to the enterprise index page
        $this->redirect()->toRoute('enterprise');
    }

    public function checkmediauserAction()
    {
        $postUser = $_POST['username'];
        $postPass = $_POST['password'];
        //Authorize the user:
        $this->_authorizeUser('2', $postUser, $postPass);
        //Set Cookies for the authorized user:
        setcookie(
            "username",
            $postUser,
            time()+3600,
            '/',
            'local.appbuzz'
            );
        setcookie(
            "password",
            $postUser,
            time()+3600,
            '/',
            'local.appbuzz'
            );
        //redirect to the media index page
        $this->redirect()->toRoute('media');
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

    protected function _authorizeUser($type, $user, $pass)
    {        
         //check if the username or password is empty
        if((!$user)||(!$pass))
        {
            echo "<a href='/'>Back</a></br>";
            die("Username or Password cannot be empty!");
        }
        //check if the username is exist, and if it's a enterprise user
        if((!$this->getUserTable()->checkUser($user))||($this->getUserTable()->getUser($user)->fk_user_type != $type))
        {
            echo "<a href='/'>Back</a></br>";
            die("The user was not exist.");
        }
        //check if the username and the password are corresponded:
        if($this->getUserTable()->getUser($user)->password != $pass)
        {
            echo "<a href='/'>Back</a></br>";
            die("Incorrect Password");
        }

        return true;
    }
}
