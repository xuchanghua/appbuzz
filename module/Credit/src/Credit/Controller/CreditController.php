<?php
namespace Credit\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Credit\Model\Credit;          // <-- Add this import
use Credit\Form\CreditForm;       // <-- Add this import
use Credit\Model\Creditlog;
use Credit\Form\CreditlogForm;
use Zend\Session\Container as SessionContainer;
use DateTime;



class CreditController extends AbstractActionController
{
    protected $creditTable;
    protected $userTable;
    protected $creditlogTable;

    public function indexAction()
    {     
    }

    public function addAction()
    {
    }

    public function editAction()
    {
    }

    public function exportlogAction()
    {
        //所有用户->导出所有交易记录
        //$arr_type_allowed = array(1, 2, 3);
        //$cur_user = $this->_auth($arr_type_allowed);

        $id_credit = (int) $this->params()->fromRoute('id', 0);
        if (!$id_credit) {
            return $this->redirect()->toRoute('/');
        }

        $logs = $this->getCreditlogTable()->fetchExportLogByFkCredit($id_credit);
        $headings = array(
            'id', '', '', '', '', '交易时间', 
            '交易金额', '是否为支付', '是否为充值', '订单号', '创建时间', '创建人', '服务类型', '付款人', '收款人'
        );

        require './vendor/Classes/PHPExcel.php';
        if($logs){
            $objPHPExcel = new \PHPExcel();
            $objPHPExcel->getActiveSheet()->setTitle('我的交易记录');

            $rowNumber = 1;
            $col = 'A';
            foreach($headings as $heading){
                $objPHPExcel->getActiveSheet()->setCellValue($col.$rowNumber,$heading);
                $col++;
            }

            $rowNumber = 2;
            foreach($logs as $row){
                $col = 'A';
                foreach($row as $cell) {
                    $objPHPExcel->getActiveSheet()->setCellValue($col.$rowNumber,$cell);
                    $col++;
                }
                $rowNumber++;
            }   

            // Freeze pane so that the heading line will not scroll
            $objPHPExcel->getActiveSheet()->freezePane('A2');

            //删除空的4列
            $objPHPExcel->getActiveSheet()->removeColumn('B', 4);

            // Save as an Excel BIFF (xls) file
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="我的交易记录.xls"');
            header('Cache-Control: max-age=0');

            $objWriter->save('php://output');
            exit();
        }

    }

    public function chargeAction()
    {
        //管理员->用户信息->现金账户->充值
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_credit = (int) $this->params()->fromRoute('id', 0);
        if (!$id_credit) {
            return $this->redirect()->toRoute('user', array(
                'action' => 'admin'
            ));
        }
        $credit = $this->getCreditTable()->getCredit($id_credit);
        $originamount = $credit->amount;
        $target_user = $this->getUserTable()->getUser($credit->fk_user);
        $creditlog = $this->getCreditlogTable()->fetchLogByFkCredit($credit->id_credit);

        $form  = new CreditForm();
        $form->bind($credit);
        $form->get('submit')->setAttribute('value', '确认充值');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($credit->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $form->getData()->updated_at = $this->_getDateTime();
                $form->getData()->updated_by = $cur_user;
                $chargeamount = $form->getData()->chargeamount;
                $form->getData()->amount = $originamount + $chargeamount;
                $this->getCreditTable()->saveCredit($form->getData());
                //create a credit log
                $credit2 = $this->getCreditTable()->getCredit($id_credit);
                $log = new Creditlog();
                $log->fk_credit = $id_credit;
                $log->fk_service_type = 1;// charge
                $log->fk_from = null;
                $log->fk_to = $credit2->fk_user;
                $log->date_time = $this->_getDateTime();
                $log->amount = $chargeamount;
                $log->is_pay = 0;// not pay
                $log->is_charge = 1; // is charge
                $log->created_at = $this->_getDateTime();
                $log->created_by = $cur_user;
                $this->getCreditlogTable()->saveCreditlog($log);

                $id_creditlog = $this->getCreditlogTable()->getId($log->created_at, $log->created_by);
                $log2 = $this->getCreditlogTable()->getCreditlog($id_creditlog);
                $log2->order_no = 90000000 + (int)$id_creditlog;
                $this->getCreditlogTable()->saveCreditlog($log2);

                return $this->redirect()->toRoute('user', array(
                    'action' => 'detail',
                    'id'     => $target_user->id,
                ));
            }
        }

        return array(
            'user' => $cur_user,
            'credit' => $credit,
            'target_user' => $target_user,
            'form' => $form,
            'creditlog' => $creditlog,
        );
    }

    public function deleteAction()
    {
    }

    public function getCreditTable()
    {
        if (!$this->creditTable) {
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

    public function getUserTable()
    {
        if(!$this->userTable){
            $sm = $this->getServiceLocator();
            $this->userTable = $sm->get('User\Model\UserTable');
        }
        return $this->userTable;
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
