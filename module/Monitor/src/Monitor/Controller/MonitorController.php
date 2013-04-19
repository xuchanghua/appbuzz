<?php
namespace Monitor\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Monitor\Model\Monitor;
use Monitor\Form\MonitorForm;
use Monitor\Model\Keyword;
use Monitor\Form\KeywordForm;
use Monitor\Model\Configure;
use Monitor\Form\ConfigureForm;
use Credit\Model\Credit;
use Credit\Model\Creditlog;
use User\Model\User;
use Zend\Session\Container as SessionContainer;
use DateTime, DateInterval;

class MonitorController extends AbstractActionController
{
    protected $monitorTable;
    protected $userTable;
    protected $productTable;
    protected $keywordTable;
    protected $creditTable;
    protected $creditlogTable;

    public function indexAction()
    {
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_user = $this->getUserTable()->getUserByName($cur_user)->id;
        $this->is_bought($id_user);

        //get Monitor
        $monitorSet = $this->getMonitorTable()->fetchValidMonitorByFkEntUser($id_user);
        $monitor = $monitorSet->current();
        $id_monitor = $monitor->id_monitor;
        //get two Keywords
        $keyword1 = $this->getKeywordTable()->getKeywordByMonitor($id_monitor, 1);
        $keyword2 = $this->getKeywordTable()->getKeywordByMonitor($id_monitor, 2);

        return new ViewModel(array(
            'user' => $cur_user,
            'keyword1' => $keyword1,
            'keyword2' => $keyword2,
        ));
    }

    public function buyAction()
    {
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);

        return new ViewModel(array(
            'user' => $cur_user,
        ));
    }

    public function buysixmAction()
    {
        //企业->网络监测->购买6个月服务
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);

        $price = 18000;//对企业用户应收18000元的6个月网络监测费用
        $fk_user = $this->getUserTable()->getUserByName($cur_user)->id;
        $is_sufficient = $this->getCreditTable()->issufficient($price, $fk_user);
        if(!$is_sufficient)
        {
            echo "<a href='/monitor/buy'>Back</a></br>";
            die("Insufficient Credit! Please Charge Your Account!");
        }

        $monitor = new Monitor();
        $monitor->fk_enterprise_user = $fk_user;
        $monitor->duration = "6";
        $monitor->start_date = $this->_getDateTime();
        $monitor->end_date = $this->_getDateTime('P183D');//half year later
        $monitor->created_at = $this->_getDateTime();
        $monitor->created_by = $cur_user;
        $monitor->updated_at = $this->_getDateTime();
        $monitor->updated_by = $cur_user;
        $this->getMonitorTable()->saveMonitor($monitor);

        $id_monitor = $this->getMonitorTable()->getId($monitor->created_at, $monitor->created_by);
        $monitor2 = $this->getMonitorTable()->getMonitor($id_monitor);
        $monitor2->order_no = 51000000 + $id_monitor;
        $this->getMonitorTable()->saveMonitor($monitor2);

        //update the user's credit
        $credit = $this->getCreditTable()->getCreditByFkUser($fk_user);
        $originamount = $credit->amount;
        $credit->amount = $originamount - $price;
        $credit->updated_at = $this->_getDateTime();
        $credit->updated_by = $cur_user;
        $this->getCreditTable()->saveCredit($credit);

        //create creditlog record;
        $creditlog = new Creditlog();
        $creditlog->fk_credit = $credit->id_credit;
        $creditlog->fk_service_type = 8;//企业->网络监测->6个月
        $creditlog->fk_from = $fk_user;
        $creditlog->fk_to = null;
        $creditlog->date_time = $this->_getDateTime();
        $creditlog->amount = $price;
        $creditlog->is_pay = 1;//is pay
        $creditlog->is_charge = 0;//not charge
        $creditlog->order_no = $monitor2->order_no;
        $creditlog->created_at = $this->_getDateTime();
        $creditlog->created_by = $cur_user;
        $this->getCreditlogTable()->saveCreditlog($creditlog);

        //create two keyword records: one for the enterprise itself and another for the competitor:
        $keyword1 = new Keyword();
        $keyword1->fk_monitor = $id_monitor;
        $keyword1->keyword = null;
        $keyword1->fk_keyword_type = 1;// 企业自己的APP
        $keyword1->created_at = $this->_getDateTime();
        $keyword1->created_by = $cur_user;
        $keyword1->updated_at = $this->_getDateTime();
        $keyword1->updated_by = $cur_user;
        $this->getKeywordTable()->saveKeyword($keyword1);

        $keyword2 = new Keyword();
        $keyword2->fk_monitor = $id_monitor;
        $keyword2->keyword = null;
        $keyword2->fk_keyword_type = 2;// 竞争对手的APP
        $keyword2->created_at = $this->_getDateTime();
        $keyword2->created_by = $cur_user;
        $keyword2->updated_at = $this->_getDateTime();
        $keyword2->updated_by = $cur_user;
        $this->getKeywordTable()->saveKeyword($keyword2);

        return $this->redirect()->toRoute('monitor', array(
            'action' => 'configure'
        ));

        return new ViewModel(array(
            'user' => $cur_user,
        ));
    }

    public function buytwelvemAction()
    {
        //企业->网络监测->购买12个月服务
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);

        $price = 30000;//对企业用户应收30000元的6个月网络监测费用
        $fk_user = $this->getUserTable()->getUserByName($cur_user)->id;
        $is_sufficient = $this->getCreditTable()->issufficient($price, $fk_user);
        if(!$is_sufficient)
        {
            echo "<a href='/monitor/buy'>Back</a></br>";
            die("Insufficient Credit! Please Charge Your Account!");
        }

        $monitor = new Monitor();
        $monitor->fk_enterprise_user = $fk_user;
        $monitor->duration = "12";
        $monitor->start_date = $this->_getDateTime();
        $monitor->end_date = $this->_getDateTime('P1Y');//one year later
        $monitor->created_at = $this->_getDateTime();
        $monitor->created_by = $cur_user;
        $monitor->updated_at = $this->_getDateTime();
        $monitor->updated_by = $cur_user;
        $this->getMonitorTable()->saveMonitor($monitor);

        $id_monitor = $this->getMonitorTable()->getId($monitor->created_at, $monitor->created_by);
        $monitor2 = $this->getMonitorTable()->getMonitor($id_monitor);
        $monitor2->order_no = 51000000 + $id_monitor;
        $this->getMonitorTable()->saveMonitor($monitor2);

        //update the user's credit
        $credit = $this->getCreditTable()->getCreditByFkUser($fk_user);
        $originamount = $credit->amount;
        $credit->amount = $originamount - $price;
        $credit->updated_at = $this->_getDateTime();
        $credit->updated_by = $cur_user;
        $this->getCreditTable()->saveCredit($credit);

        //create creditlog record;
        $creditlog = new Creditlog();
        $creditlog->fk_credit = $credit->id_credit;
        $creditlog->fk_service_type = 9;//企业->网络监测->12个月
        $creditlog->fk_from = $fk_user;
        $creditlog->fk_to = null;
        $creditlog->date_time = $this->_getDateTime();
        $creditlog->amount = $price;
        $creditlog->is_pay = 1;//is pay
        $creditlog->is_charge = 0;//not charge
        $creditlog->order_no = $monitor2->order_no;
        $creditlog->created_at = $this->_getDateTime();
        $creditlog->created_by = $cur_user;
        $this->getCreditlogTable()->saveCreditlog($creditlog);

        //create two keyword records: one for the enterprise itself and another for the competitor:
        $keyword1 = new Keyword();
        $keyword1->fk_monitor = $id_monitor;
        $keyword1->keyword = null;
        $keyword1->fk_keyword_type = 1;// 企业自己的APP
        $keyword1->created_at = $this->_getDateTime();
        $keyword1->created_by = $cur_user;
        $keyword1->updated_at = $this->_getDateTime();
        $keyword1->updated_by = $cur_user;
        $this->getKeywordTable()->saveKeyword($keyword1);

        $keyword2 = new Keyword();
        $keyword2->fk_monitor = $id_monitor;
        $keyword2->keyword = null;
        $keyword2->fk_keyword_type = 2;// 竞争对手的APP
        $keyword2->created_at = $this->_getDateTime();
        $keyword2->created_by = $cur_user;
        $keyword2->updated_at = $this->_getDateTime();
        $keyword2->updated_by = $cur_user;
        $this->getKeywordTable()->saveKeyword($keyword2);

        return $this->redirect()->toRoute('monitor', array(
            'action' => 'configure'
        ));

        return new ViewModel(array(
            'user' => $cur_user,
        ));
    }

    public function addAction()
    {
    }

    public function editAction()
    {        
    }

    public function deleteAction()
    {
    }

    public function configureAction()
    {
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_user = $this->getUserTable()->getUserByName($cur_user)->id;
        $this->is_bought($id_user);

        $form = new ConfigureForm();
        $form->get('submit')->setValue('保存');
        $request = $this->getRequest();
        if($request->isPost()){
            $configure = new Configure();
            $form->setInputFilter($configure->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid())
            {
                $arr_get_data = $form->getData();
                $myapp = $arr_get_data['myapp'];
                $competitorapp = $arr_get_data['competitorapp'];

                //get Monitor
                $monitorSet = $this->getMonitorTable()->fetchValidMonitorByFkEntUser($id_user);
                $monitor = $monitorSet->current();
                $id_monitor = $monitor->id_monitor;
                //get two Keywords and update it
                $keyword1 = $this->getKeywordTable()->getKeywordByMonitor($id_monitor, 1);
                $keyword1->keyword = $myapp;
                $keyword1->updated_at = $this->_getDateTime();
                $keyword1->updated_by = $cur_user;
                $this->getKeywordTable()->saveKeyword($keyword1);
                $keyword2 = $this->getKeywordTable()->getKeywordByMonitor($id_monitor, 2);
                $keyword2->keyword = $competitorapp;
                $keyword2->updated_at = $this->_getDateTime();
                $keyword2->updated_by = $cur_user;
                $this->getKeywordTable()->saveKeyword($keyword2);

                return $this->redirect()->toRoute('monitor', array(
                    'action' => 'index',
                ));
            }
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'form' => $form,
            'products' => $this->getProductTable()->fetchProductByUser($cur_user),
        ));
    }

    public function dataAction()
    {
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_user = $this->getUserTable()->getUserByName($cur_user)->id;
        $this->is_bought($id_user);

    }

    public function mgmtAction()
    {
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_user = $this->getUserTable()->getUserByName($cur_user)->id;
        $this->is_bought($id_user);

    }

    public function getMonitorTable()
    {
        if (!$this->monitorTable) {
	    $sm = $this->getServiceLocator();
	    $this->monitorTable = $sm->get('Monitor\Model\MonitorTable');
        }
        return $this->monitorTable;
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

    public function getKeywordTable()
    {
        if(!$this->keywordTable){
            $sm = $this->getServiceLocator();
            $this->keywordTable = $sm->get('Monitor\Model\KeywordTable');
        }
        return $this->keywordTable;
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

    /**
     * Check if the user has bought the monitor service, if not, redirect to the buy page
     * @param $id_user: the id of the user
     * @return redirect()
     */
    protected function is_bought($id_user)
    {
        $orders = $this->getMonitorTable()->fetchValidMonitorByFkEntUser($id_user);
        $count = 0;
        foreach ($orders as $order)
        {
            $count ++;
        }
        if(!$count)
        {
            return $this->redirect()->toRoute('monitor', array(
                'action' => 'buy'
            ));
        }
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

    /**
     * Get the current time with the format which could be accepted by MySQL datetime.
     * @return YYYY-MM-DD HH:MM:SS
     */
    protected function _getDateTime($str_interval = null)
    {        
        date_default_timezone_set("Asia/Shanghai");
        $datetime = new DateTime;
        if($str_interval)
        {
            $datetime->add(new DateInterval($str_interval));
        }
        $strdatetime = $datetime->format(DATE_ATOM);
        $date = substr($strdatetime,0,10);
        $time = substr($strdatetime,11,8);
        $result = $date.' '.$time;
        return $result;
    }
}
