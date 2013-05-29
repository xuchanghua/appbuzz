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
    protected $rankTable;
    protected $articleTable;

    public function indexAction()
    {
        //redirect to the enterprise/media/admin index page if session is authorized.
        $this->_authenticateSession();

        $user = $this->_getSessionUser();    
        //content 
        $articles = $this->getArticleTable()->fetchAll();
        $rank = $this->getRankTable()->getRankByObj('article');
        $captcha = new \Zend\Captcha\Image(array(
            'Expiration' => '300',
            'wordlen'    => '5',
            'Height'     => '50',
            'Width'      => '200',
            //'Font'       => 'C:\Windows\Fonts\georgia.ttf',
            'Font'       => 'data/georgia.ttf',
            'ImgDir'     => 'data/images',
        ));
        $captcha->setImgDir('public/verifycode/images');
        $captcha_med = new \Zend\Captcha\Image(array(
            'Expiration' => '300',
            'wordlen'    => '5',
            'Height'     => '50',
            'Width'      => '200',
            //'Font'       => 'C:\Windows\Fonts\georgia.ttf',
            'Font'       => 'data/georgia.ttf',
            'ImgDir'     => 'data/images',
        ));
        $captcha_med->setImgDir('public/verifycode/images');
        return new ViewModel(array(
            'session_user' => $user,
            'articles' => $articles,
            'rank' => $rank,
            'captcha' => $captcha,
            'captcha_med' => $captcha_med,
        ));        
    }

    public function testAction()
    {
	
    }

    public function loginAction()
    {
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

    public function getArticleTable()
    {
        if (!$this->articleTable) {
        $sm = $this->getServiceLocator();
        $this->articleTable = $sm->get('Article\Model\ArticleTable');
        }
        return $this->articleTable;
    }

    public function getRankTable()
    {
        if (!$this->rankTable) {
        $sm = $this->getServiceLocator();
        $this->rankTable = $sm->get('Article\Model\RankTable');
        }
        return $this->rankTable;
    }

    protected function _authorizeUser($type, $user, $pass)
    {        
        $authentication  =  (isset($user)) && 
                            (isset($pass)) && 
                            ($this->getUserTable()->checkUser($user)) &&
                            ($this->getUserTable()->getUserByName($user)->fk_user_type==$type) &&
                            ($this->getUserTable()->getUserByName($user)->password==$pass);
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
            return $this->redirect()->toRoute("enterprise", 
                array(
                    'action' => 'index',
                ));
        }
        if($this->_authorizeUser(2, $username, $password))
        {
            return $this->redirect()->toRoute("media", 
                array(
                    'action' => 'index',
                ));
        }
        if($this->_authorizeUser(3, $username, $password))
        {
            return $this->redirect()->toRoute("admin", 
                array(
                    'action' => 'index',
                ));
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
