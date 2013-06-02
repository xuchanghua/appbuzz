<?php
namespace Credit\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Credit\Model\Credit;          // <-- Add this import
use Credit\Model\Constant;
use Credit\Form\CreditForm;       // <-- Add this import
use Credit\Model\Creditlog;
use Credit\Model\Withdraw;
use Credit\Form\CreditlogForm;
use Credit\Form\ConstantForm;
use Credit\Form\WithdrawForm;
use Zend\Session\Container as SessionContainer;
use DateTime;
use Zend\Mail\Message;
use Zend\Mail\Transport\Sendmail as SendmailTransport;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\Mail\Transport\SmtpOptions;


class CreditController extends AbstractActionController
{
    protected $creditTable;
    protected $userTable;
    protected $creditlogTable;
    protected $constantTable;
    protected $withdrawTable;

    public function indexAction()
    {     
    }

    public function addAction()
    {
    }

    public function editAction()
    {
    }

    public function priceAction()
    {
        //管理员->产品价格
        $arr_type_allowed = array(4);
        $cur_user = $this->_auth($arr_type_allowed);

        $prices = $this->getConstantTable()->fetchAllPrices();

        return new ViewModel(array(
            'user' => $cur_user,
            'prices' => $prices,
        ));
    }

    public function changepriceAction()
    {
        //管理员->修改价格
        $arr_type_allowed = array(4);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_constant = (int) $this->params()->fromRoute('id', 0);
        if(!$id_constant){
            return $this->redirect()->toRoute('application');
        }

        $constant = $this->getConstantTable()->getConstant($id_constant);
        $cst_name             = $constant->name;
        $cst_description      = $constant->description;
        $cst_fk_constant_type = $constant->fk_constant_type;
        $cst_created_at       = $constant->created_at;
        $cst_created_by       = $constant->created_by;
        $form = new ConstantForm();
        $form->bind($constant);
        $form->get('submit')->setAttribute('value', '保存');
        $request = $this->getRequest();
        if($request->isPost()){
            $form->getInputFilter($constant->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $form->getData()->name             = $cst_name;
                $form->getData()->description      = $cst_description;
                $form->getData()->fk_constant_type = $cst_fk_constant_type;
                $form->getData()->created_at       = $cst_created_at;
                $form->getData()->created_by       = $cst_created_by;
                $form->getData()->updated_at       = $this->_getDateTime();
                $form->getData()->updated_by       = $cur_user;
                $this->getConstantTable()->saveConstant($form->getData());

                return $this->redirect()->toRoute('credit', array(
                    'action' => 'price',
                ));
            }
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'form' => $form,
            'constant' => $constant,
        ));
    }

    public function discountAction()
    {        
        //管理员->产品折扣管理
        $arr_type_allowed = array(4);
        $cur_user = $this->_auth($arr_type_allowed);


        return new ViewModel(array(
            'user' => $cur_user,
        ));
    }

    public function exportlogAction()
    {
        //所有用户->导出所有交易记录
        //$arr_type_allowed = array(1, 2, 3);
        //$cur_user = $this->_auth($arr_type_allowed);

        $id_credit = (int) $this->params()->fromRoute('id', 0);
        if (!$id_credit) {
            return $this->redirect()->toRoute('application');
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

    public function adminAction()
    {
        //管理员->用户管理->媒体收款申请
        $arr_type_allowed = array(4);
        $cur_user = $this->_auth($arr_type_allowed);

        $withdraws = $this->getWithdrawTable()->fetchAll();

        return new ViewModel(array(
            'user' => $cur_user,
            'withdraws' => $withdraws,
        ));
    }

    public function withdrawAction()
    {
        //媒体->我的账户->申请收款;
        $arr_type_allowed = array(2);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_credit = (int) $this->params()->fromRoute('id', 0);
        if (!$id_credit) {
            return $this->redirect()->toRoute('application');
        }

        $fk_user = $this->getCreditTable()->getCredit($id_credit)->fk_user;
        $credit_amount = $this->getCreditTable()->getCreditByFkUser($fk_user)->amount;
        $form = new WithdrawForm();
        $form->get('submit')->setValue('申请收款');
        $request = $this->getRequest();
        if($request->isPost()){
            $withdraw = new Withdraw();
            $form->setInputFilter($withdraw->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $withdraw->exchangeArray($form->getData());
                if($credit_amount >= $withdraw->amount)
                {
                    $withdraw->fk_user = $fk_user;
                    $withdraw->created_at = $this->_getDateTime();
                    $withdraw->created_by = $cur_user;
                    $withdraw->fk_withdraw_status = 1;
                    $this->getWithdrawTable()->saveWithdraw($withdraw);

                    $id_withdraw = $this->getWithdrawTable()->getId($withdraw->created_at, $withdraw->created_by);
                    $withdraw2 = $this->getWithdrawTable()->getWithdraw($id_withdraw);
                    $withdraw2->order_no = 82000000 + $id_withdraw;
                    $this->getWithdrawTable()->saveWithdraw($withdraw2);

                    //change the credit amount and log it
                    $price = $withdraw2->amount;
                    $credit = $this->getCreditTable()->getCreditByFkUser($fk_user);
                    $originamount = $credit->amount;
                    $credit->amount = $originamount - $price;
                    $credit->updated_at = $this->_getDateTime();
                    $credit->updated_by = $cur_user;
                    $this->getCreditTable()->saveCredit($credit);

                    //create creditlog record;
                    $creditlog = new Creditlog();
                    $creditlog->fk_credit = $credit->id_credit;
                    $creditlog->fk_service_type = 20;//媒体->收款
                    $creditlog->fk_from = null;
                    $creditlog->fk_to = $fk_user;
                    $creditlog->date_time = $this->_getDateTime();
                    $creditlog->amount = $price;
                    $creditlog->is_pay = 1;//is pay
                    $creditlog->is_charge = 0;//not charge
                    $creditlog->order_no = $withdraw2->id_withdraw;
                    $creditlog->created_at = $this->_getDateTime();
                    $creditlog->created_by = $cur_user;
                    $this->getCreditlogTable()->saveCreditlog($creditlog);

                    //send a email to admin@appbuzz.cn
                    $to = "admin@appbuzz.cn";
                    $date = $this->_getDateTime();
                    $message = new Message();
                    $message->addTo($to)
                            ->addFrom("credit@furnihome.asia")
                            ->setSubject('媒体用户 '.$cur_user.'申请收款 '.$withdraw2->amount.' 元');
                    $html = new MimePart(
                        '<p>管理员，</p>
                        <p>媒体用户 '.$cur_user.'申请收款 '.$withdraw2->amount.' 元</p>
                        <p>银行账号： '.$withdraw2->bank_account.'</p>
                        <p>开户行： '.$withdraw2->bank_name.'</p>
                        <p>姓名： '.$withdraw2->owner_name.'</p>
                        <br><br>
                        <p>顺颂商祺，</p>
                        <p>APPbuzz.cn网站管理团队</p>
                        <p>'.substr($date, 0, 10). '</p>');
                    $html->type = "text/html";
                    $body = new MimeMessage();
                    $body->addPart($html);
                    $message->setBody($body);
                    $transport = new SendmailTransport();
                    $transport->send($message);

                    return $this->redirect()->toRoute('media', array(
                        'action' => 'myaccount',
                    ));
                }else{
                    echo "<a href='/'>Back</a></br>";
                    die("Please don't require to withdraw the amount which is more than your grant total!");
                }                
            }
            /*else{
                die(var_dump($form->getMessages()));
            }*/
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'form' => $form,
            'id_credit' => $id_credit,
        ));
    }

    public function transferredAction()
    {
        //管理员->用户信息->现金账户->充值
        $arr_type_allowed = array(3, 4);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_withdraw = (int) $this->params()->fromRoute('id', 0);
        if(!$id_withdraw) {
            return $this->redirect()->toRoute('credit', array(
                'action' => 'admin'
            ));
        }

        $withdraw = $this->getWithdrawTable()->getWithdraw($id_withdraw);
        $withdraw->fk_withdraw_status = 2;
        $this->getWithdrawTable()->saveWithdraw($withdraw);

        return $this->redirect()->toRoute('credit', array(
            'action' => 'admin'
        ));
    }

    public function chargeAction()
    {
        //管理员->用户信息->现金账户->充值
        $arr_type_allowed = array(3, 4);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_credit = (int) $this->params()->fromRoute('id', 0);
        if (!$id_credit) {
            return $this->redirect()->toRoute('user', array(
                'action' => 'admin'
            ));
        }
        $credit = $this->getCreditTable()->getCredit($id_credit);
        $originamount = $credit->amount;
        $origindeposit = $credit->deposit;
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
                $form->getData()->deposit = $origindeposit;
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
                $log->deposit = 0;
                $log->remaining_balance = $credit2->amount;
                $log->remaining_deposit = $credit2->deposit;
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

    public function notifyAction()
    {
        //企业->充值
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);

        return new ViewModel(array(
            'user' => $cur_user,
        ));
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

    public function getConstantTable()
    {
        if(!$this->constantTable){
            $sm = $this->getServiceLocator();
            $this->constantTable = $sm->get('Credit\Model\ConstantTable');
        }
        return $this->constantTable;
    }

    public function getWithdrawTable()
    {
        if(!$this->withdrawTable){
            $sm = $this->getServiceLocator();
            $this->withdrawTable = $sm->get('Credit\Model\WithdrawTable');
        }
        return $this->withdrawTable;
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
