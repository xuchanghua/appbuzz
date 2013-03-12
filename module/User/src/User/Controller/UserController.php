<?php
namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Header\Cookie;
use Zend\Http\PhpEnvironment\Response;
use Zend\Session\Container as SessionContainer;
use User\Model\User;
use User\Form\UserForm;

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
        //redirect to the media index page
        $this->redirect()->toRoute('media');
    }

    public function checkadminuserAction()
    {        
        $postUser = $_POST['username'];
        $postPass = $_POST['password'];
        //Authorize the user:
        $this->_authorizeUser('3', $postUser, $postPass);
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
        $this->redirect()->toRoute('admin');
    }

    public function signupAction()
    {
        $form = new UserForm();
        $form->get('submit')->setValue('Sign Up!!!');
        $request = $this->getRequest();
        if($request->isPost()){
            $user = new User();
            $form->setInputFilter($user->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $user->exchangeArray($form->getData());
                $this->getUserTable()->saveUser($user);
                //Set Session for the authorized user:
                $this->session = new SessionContainer('userinfo');
                $this->session->username = $user->username;
                $this->session->password = $user->password;
                switch($user->fk_user_type)
                {
                    case 1:
                        return $this->redirect()->toRoute('enterprise');
                        break;
                    case 2:
                        return $this->redirect()->toRoute('media');
                        break;
                    case 3:
                        return $this->redirect()->toRoute('admin');
                        break;
                    default:
                        die("no such user type!");
                }
                //return $this->redirect()->toRoute('enterprise');
            }
        }
        return array('form' => $form);
    }

    public function logoutAction()
    {
        $this->session = new SessionContainer('userinfo');
        unset($this->session->username);
        unset($this->session->password);    
        return $this->redirect()->toRoute('home');
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
