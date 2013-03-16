<?php
namespace Enterprise\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container as SessionContainer;
use User\Model\User;
use Enterprise\Model\Enterprise;
use Enterprise\Form\EnterpriseForm;

class EnterpriseController extends AbstractActionController
{
    protected $userTable;
    protected $enterpriseTable;

    public function indexAction()
    {
        //Authenticate the user information from the session
        $cur_user = $this->_authenticateSession(1);
        $email = $this->_getUserEmail($cur_user);
        $fk_enterprise = $this->_getUserFkEnterprise($cur_user);

        $form = new EnterpriseForm();
        $form->get('submit')->setValue('保存');
        $request = $this->getRequest();
        if($request->isPost()){
            $enterprise = new Enterprise();
            $form->setInputFilter($enterprise->getInputFilter());
            $form->setData($request->getPost());
            $user = new User();
            $user = $this->getUserTable()->getUserByName($cur_user);
            if($form->isValid()){
                $enterprise->exchangeArray($form->getData());
                $this->getEnterpriseTable()->saveEnterprise($enterprise);
                $newEnt = $this->getEnterpriseTable()->getEnterpriseByName($enterprise->name);
                $user->fk_enterprise = $newEnt->id_enterprise;
                //die(var_dump($user));
                $this->getUserTable()->saveUser($user);
                return $this->redirect()->toRoute('enterprise',array(
                    'action' => 'index',
                ));
            }
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'email' => $email,
            'fk_enterprise' => $fk_enterprise,
            'form' => $form,
            ));
    }

    public function enterpriseinfoAction()
    {
    }

    public function myaccountAction()
    {
    }

    public function myorderAction()
    {
    }

    public function netmonitorAction()
    {
    }

    public function appevaluatingAction()
    {
    }

    public function newseditAction()
    {
    }

    public function topicAction()
    {
    }

    public function interviewAction()
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

    public function getEnterpriseTable()
    {
        if(!$this->enterpriseTable){
            $sm = $this->getServiceLocator();
            $this->enterpriseTable = $sm->get('Enterprise\Model\EnterpriseTable');
        }
        return $this->enterpriseTable;
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
        if((!$this->getUserTable()->checkUser($user))||($this->getUserTable()->getUserByName($user)->fk_user_type != $type))
        {
            echo "<a href='/'>Back</a></br>";
            die("The user was not exist.");
        }
        //check if the username and the password are corresponded:
        if($this->getUserTable()->getUserByName($user)->password != $pass)
        {
            echo "<a href='/'>Back</a></br>";
            die("Incorrect Password");
        }

        return true;
    }

    protected function _authenticateSession($fk_user_type)
    {        
        $this->session = new SessionContainer('userinfo');
        $username = $this->session->username;
        $password = $this->session->password;
        if($this->_authorizeUser($fk_user_type, $username, $password))
        {
            echo "Welcome, ".$username;
            return $username;
        }
    }

    protected function _getUserEmail($user)
    {
        return $this->getUserTable()->getUserByName($user)->email;
    }

    protected function _getUserFkEnterprise($user)
    {
        return $this->getUserTable()->getUserByName($user)->fk_enterprise;
    }
}
