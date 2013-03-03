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



class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
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
}
