<?php
namespace Enterprise\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container as SessionContainer;
use User\Model\User;
use Enterprise\Model\Enterprise;
use Enterprise\Form\EnterpriseForm;
use DateTime;

class EnterpriseController extends AbstractActionController
{
    protected $userTable;
    protected $enterpriseTable;
    protected $productTable;
    protected $newspubTable;
    protected $evaluateTable;
    protected $creditTable;
    protected $creditlogTable;
    protected $monitorTable;
    protected $writerTable;
    protected $tpcontactTable;
    protected $interviewTable;

    public function indexAction()
    {
        //Authenticate the user information from the session
        //$cur_user = $this->_authenticateSession(1);
        $arr_type_allowed = array(1);
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
                $enterprise->created_at = $this->_getDateTime();
                $enterprise->created_by = $cur_user;
                $enterprise->updated_at = $this->_getDateTime();
                $enterprise->updated_by = $cur_user;
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
            'id_user' => $this->getUserTable()->getUserByName($cur_user)->id,
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

        $id_enterprise = (int)$this->params()->fromRoute('id',0);
        if (!$id_enterprise) {
            return $this->redirect()->toRoute('enterprise', array(
                'action' => 'index',
            ));
        }
        $enterprise = $this->getEnterpriseTable()->getEnterprise($id_enterprise);
        $ent_created_at = $enterprise->created_at;
        $ent_created_by = $enterprise->created_by;
        $form = new EnterpriseForm();
        $form->bind($enterprise);
        $form->get('submit')->setAttribute('value','保存');

        $request = $this->getRequest();
        if ($request->isPost()){
            $form->setInputFilter($enterprise->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $form->getData()->created_at = $ent_created_at;
                $form->getData()->created_by = $ent_created_by;
                $form->getData()->updated_at = $this->_getDateTime();
                $form->getData()->updated_by = $cur_user;
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

    public function adminAction()
    {
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_enterprise = (int)$this->params()->fromRoute('id',0);
        if (!$id_enterprise) {
            return $this->redirect()->toRoute('user', array(
                'action' => 'admin',
            ));
        }
        $enterprise = $this->getEnterpriseTable()->getEnterprise($id_enterprise);
        $target_user = $this->getUserTable()->getUserByFkEnt($id_enterprise);
        $ent_created_at = $enterprise->created_at;
        $ent_created_by = $enterprise->created_by;
        $form = new EnterpriseForm();
        $form->bind($enterprise);
        $form->get('submit')->setAttribute('value','保存');

        $request = $this->getRequest();
        if ($request->isPost()){
            $form->setInputFilter($enterprise->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $form->getData()->created_at = $ent_created_at;
                $form->getData()->created_by = $ent_created_by;
                $form->getData()->updated_at = $this->_getDateTime();
                $form->getData()->updated_by = $cur_user;
                $this->getEnterpriseTable()->saveEnterprise($form->getData());

                return $this->redirect()->toRoute('user',array(
                    'action' => 'detail',
                    'id'     => $target_user->id,
                ));
            }
        }

        return new ViewModel(array(
            'user'       => $cur_user,
            'enterprise' => $enterprise,
            'form'       => $form,
        ));
    }

    public function enterpriseinfoAction()
    {
    }

    public function myaccountAction()
    {
        //企业用户首页->我的账户
        $arr_type_allowed = array(1);        
        $cur_user = $this->_auth($arr_type_allowed);

        $user = $this->getUserTable()->getUserByName($cur_user);
        $credit = $this->getCreditTable()->getCreditByFkUser($user->id);
        $creditlog = $this->getCreditlogTable()->fetchLogByFkCreditLimit5($credit->id_credit);

        return new ViewModel(array(
            'user' => $cur_user,
            'credit' => $credit,
            'creditlog' => $creditlog,
            'paginator' => $this->getCreditlogTable()->getPaginator($credit->id_credit, null, 1, 5, 1),
        ));
    }

    public function myorderAction()
    {
        //$cur_user = $this->_authenticateSession(1);        
        $arr_type_allowed = array(1, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        $target_user = $this->getUserTable()->getUserByName($cur_user);
        $products    = $this->getProductTable()->fetchProductByUser($cur_user);
        $all_users   = $this->getUserTable()->fetchAll();

        $monitor5   = $this->getMonitorTable()->fetchMonitorByFkEntUserLimit5($target_user->id);
        $newspub5   = $this->getNewspubTable()->getNewspubByUserLimit5($cur_user);
        $evaluate5  = $this->getEvaluateTable()->fetchEvaluateByUserLimit5($cur_user);
        $writer5    = $this->getWriterTable()->fetchWriterByUserLimit5($cur_user);
        $tpcontact5 = $this->getTpcontactTable()->fetchTpcontactByUserLimit5($cur_user);
        $interview5 = $this->getInterviewTable()->fetchIntviewByFkEntUserLimit5($target_user->id);

        return new ViewModel(array(
            'user'       => $cur_user,
            'products'   => $products,
            'all_users'  => $all_users,
            'monitor5'   => $monitor5,
            'newspub5'   => $newspub5,
            'evaluate5'  => $evaluate5,
            'writer5'    => $writer5,
            'tpcontact5' => $tpcontact5,
            'interview5' => $interview5,
            'now' => $this->_getDateTime(),
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

    public function getCreditTable()
    {
        if(!$this->creditTable){
            $sm = $this->getServiceLocator();
            $this->creditTable = $sm->get('Credit\Model\CreditTable');
        }
        return $this->creditTable;
    }

    public function getCreditlogTable()
    {
        if(!$this->creditlogTable){
            $sm = $this->getServiceLocator();
            $this->creditlogTable = $sm->get('Credit\Model\CreditlogTable');
        }
        return $this->creditlogTable;
    }

    public function getMonitorTable()
    {
        if(!$this->monitorTable){
            $sm = $this->getServiceLocator();
            $this->monitorTable = $sm->get('Monitor\Model\MonitorTable');
        }
        return $this->monitorTable;
    }

    public function getWriterTable()
    {
        if(!$this->writerTable){
            $sm = $this->getServiceLocator();
            $this->writerTable = $sm->get('Writer\Model\WriterTable');
        }
        return $this->writerTable;
    }

    public function getTpcontactTable()
    {
        if(!$this->tpcontactTable){
            $sm = $this->getServiceLocator();
            $this->tpcontactTable = $sm->get('Topic\Model\TpcontactTable');
        }
        return $this->tpcontactTable;
    }

    public function getInterviewTable()
    {
        if(!$this->interviewTable){
            $sm = $this->getServiceLocator();
            $this->interviewTable = $sm->get('Interview\Model\InterviewTable');
        }
        return $this->interviewTable;
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

    /**
     * Get the current time with the format which could be accepted by MySQL datetime.
     * @return YYYY-MM-DD HH:MM:SS
     */
    protected function _getDateTime()
    {        
        date_default_timezone_set("Asia/Shanghai");
        $datetime = new DateTime;
        $strdatetime = $datetime->format(DATE_ATOM);
        $date = substr($strdatetime,0,10);
        $time = substr($strdatetime,11,8);
        $result = $date.' '.$time;
        return $result;
    }
}
