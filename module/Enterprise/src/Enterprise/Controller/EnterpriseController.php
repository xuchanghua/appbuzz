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
    protected $productTable;
    protected $newspubTable;
    protected $evaluateTable;

    public function indexAction()
    {
        //Authenticate the user information from the session
        //$cur_user = $this->_authenticateSession(1);
        $arr_type_allowed = array(1, 3);
        $cur_user = $this->_auth($arr_type_allowed);

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
            'enterprise' => (isset($fk_enterprise))? $this->getEnterpriseTable()->getEnterprise($fk_enterprise) : null,
            'form' => $form,
            'products' => $this->getProductTable()->fetchProductByUser($cur_user),
            ));
    }

    public function editAction()
    {
        //$cur_user = $this->_authenticateSession(1);
        $arr_type_allowed = array(1, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id = (int)$this->params()->fromRoute('id',0);
        if (!$id) {
            return $this->redirect()->toRoute('enterprise', array(
                'action' => 'index',
            ));
        }
        $enterprise = $this->getEnterpriseTable()->getEnterprise($id);
        $form = new EnterpriseForm();
        $form->bind($enterprise);
        $form->get('submit')->setAttribute('value','保存');

        $request = $this->getRequest();
        if ($request->isPost()){
            $form->setInputFilter($enterprise->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $this->getEnterpriseTable()->saveEnterprise($form->getData());

                return $this->redirect()->toRoute('enterprise',array(
                    'action' => 'index',
                ));
            }
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'email' => $this->_getUserEmail($cur_user),
            'fk_enterprise' => $this->_getUserFkEnterprise($cur_user),
            'enterprise' => (isset($fk_enterprise))? $this->getEnterpriseTable()->getEnterprise($fk_enterprise) : null,
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
        //$cur_user = $this->_authenticateSession(1);        
        $arr_type_allowed = array(1, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        return new ViewModel(array(
            'user' => $cur_user,
            'newspub' => $this->getNewspubTable()->getNewspubByUser($cur_user),
            'products' => $this->getProductTable()->fetchProductByUser($cur_user),
            'evaluate' => $this->getEvaluateTable()->fetchEvaluateByUser($cur_user),
        ));
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

    public function getProductTable()
    {
        if (!$this->productTable) {
        $sm = $this->getServiceLocator();
        $this->productTable = $sm->get('Product\Model\ProductTable');
        }
        return $this->productTable;
    }

    public function getEnterpriseTable()
    {
        if(!$this->enterpriseTable){
            $sm = $this->getServiceLocator();
            $this->enterpriseTable = $sm->get('Enterprise\Model\EnterpriseTable');
        }
        return $this->enterpriseTable;
    }

    public function getNewspubTable()
    {
        if (!$this->newspubTable) {
        $sm = $this->getServiceLocator();
        $this->newspubTable = $sm->get('Newspub\Model\NewspubTable');
        }
        return $this->newspubTable;
    }

    public function getEvaluateTable()
    {
        if (!$this->evaluateTable) {
        $sm = $this->getServiceLocator();
        $this->evaluateTable = $sm->get('Evaluate\Model\EvaluateTable');
        }
        return $this->evaluateTable;
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
        if((!$this->getUserTable()->checkUser($user))||
            ($this->getUserTable()->getUserByName($user)->fk_user_type != $type))
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

    protected function _auth($arr_type_allowed)
    {
        $this->session = new SessionContainer('userinfo');
        $username = $this->session->username;
        $password = $this->session->password;
        $usertype = $this->session->usertype;
        if(($this->_checkUser($username, $password, $usertype))
            && ($this->_checkRole($usertype, $arr_type_allowed)))
        {
            echo "Welcome, ".$username."</br>";
            return $username;
        }
    }

    /**
     * Check if the username, password, and usertype are corresponded
     * @param string $user: the username (from the session)
     * @param string $pass: the password (from the session)
     * @param int $type: the usertype (from the string)
     */
    protected function _checkUser($user, $pass, $type)
    {
         //check if the username or password or usertype is empty
        if((!$user)||(!$pass)||(!$type))
        {
            echo "<a href='/'>Back</a></br>";
            die("Please input the username and password!");
        }
        //check if the username and the password are corresponded:
        if($this->getUserTable()->getUserByName($user)->password != $pass)
        {
            echo "<a href='/'>Back</a></br>";
            die("The username and password are NOT corresponded! Please login again!");
        }
        //check if the username and the usertype are corresponded:
        if($this->getUserTable()->getUserByName($user)->fk_user_type != $type)
        {
            echo "<a href='/'>Back</a></br>";
            die("User information error! Please login again!");
        }
        return true;
    }

    /**
     * Check if the current user type is allowed
     * @param int $type: the current user type (from session)
     * @param array $arr_type_allowed: the allowed user type
     * @return boolean
     */
    protected function _checkRole($type, $arr_type_allowed)
    {
        foreach ($arr_type_allowed as $ta)
        {
            if($type == $ta)
            {
                return true;
            }
        }
        echo "<a href='/'>Back</a></br>";
        die("Insufficient privilege!");
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
