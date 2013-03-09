<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Form\LoginForm;
use Zend\Session\Container as SessionContainer;
use User\Model\User;


class IndexController extends AbstractActionController
{
    protected $userTable;

    public function indexAction()
    {
        //redirect to the enterprise/media/admin index page if session is authorized.
        $this->_authenticateSession();

        $user = $this->_getSessionUser();        
        return new ViewModel(array(
            'session_user' => $user,
        ));        
    }

    public function testAction()
    {
	
    }

    public function loginAction()
    {
/*
        $db = $this->_getParam('db');
 
        $loginForm = new Default_Form_Login();
 
        if ($loginForm->isValid($_POST)) {
 
            $adapter = new Zend_Auth_Adapter_DbTable(
                $db,
                'users',
                'username',
                'password',
                'MD5(CONCAT(?, password_salt))'
                );
 
            $adapter->setIdentity($loginForm->getValue('username'));
            $adapter->setCredential($loginForm->getValue('password'));
 
            $auth   = Zend_Auth::getInstance();
            $result = $auth->authenticate($adapter);
 
            if ($result->isValid()) {
                $this->_helper->FlashMessenger('Successful Login');
                $this->_redirect('/');
                return;
            }
 
        }
 
        $this->view->loginForm = $loginForm;
*/
    }

    public function loginFormAction()
    {
        $request = $this->getRequest();  
        $this->view->assign('action', $request->getBaseURL()."/user/auth");  
        $this->view->assign('title', 'Login Form');
        $this->view->assign('username', 'User Name');    
        $this->view->assign('password', 'Password');     
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
        $authentication  =  (isset($user)) && 
                            (isset($pass)) && 
                            ($this->getUserTable()->checkUser($user)) &&
                            ($this->getUserTable()->getUser($user)->fk_user_type==$type) &&
                            ($this->getUserTable()->getUser($user)->password==$pass);
        if($authentication)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    protected function _authenticateSession()
    {        
        $this->session = new SessionContainer('userinfo');
        $username = $this->session->username;
        $password = $this->session->password;
        if($this->_authorizeUser(1, $username, $password))
        {
            return $this->redirect()->toRoute("enterprise");
        }
        if($this->_authorizeUser(2, $username, $password))
        {
            return $this->redirect()->toRoute("media");
        }
        if($this->_authorizeUser(3, $username, $password))
        {
            return $this->redirect()->toRoute("admin");
        }
    }

    protected function _getSessionUser()
    {
        $this->session = new SessionContainer('userinfo');
        $user["name"] = $this->session->username;
        $user["pass"] = $this->session->password;
        return $user;
    }
}
