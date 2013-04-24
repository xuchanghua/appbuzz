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
use Monitor\Model\Timerangepicker;
use Monitor\Form\TimerangepickerForm;
use Credit\Model\Credit;
use Credit\Model\Creditlog;
use User\Model\User;
use Zend\Session\Container as SessionContainer;
use DateTime, DateInterval;
use Zend\Math\BigInteger\BigInteger;

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

    public function adminAction()
    {        
        //管理员->订单管理->网络监测
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);


        return new ViewModel(array(
            'user' => $cur_user,
            'monitors' => $this->getMonitorTable()->fetchAllDesc(),
            'all_users' => $this->getUserTable()->fetchAll(),
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

        //get Monitor
        $monitorSet = $this->getMonitorTable()->fetchValidMonitorByFkEntUser($id_user);
        $monitor = $monitorSet->current();
        $id_monitor = $monitor->id_monitor;
        //get two Keywords (object)
        $keyword1 = $this->getKeywordTable()->getKeywordByMonitor($id_monitor, 1);
        $keyword2 = $this->getKeywordTable()->getKeywordByMonitor($id_monitor, 2);

        $str_keyword_1 = $keyword1->keyword;
        $str_keyword_2 = $keyword2->keyword;

        //connect to the monitor database
        $con = mysql_connect("localhost:3306", "root", "rocket2012");
        mysql_set_charset('utf8', $con);
        $charset = mysql_client_encoding($con);
        mysql_select_db("article", $con);

        //query
        $query_keyword1_weibo = "SELECT * FROM t_blog WHERE content LIKE '%".$str_keyword_1."%' AND website_type = 'weibo' ORDER BY id DESC LIMIT 2;";
        $query_keyword1_news  = "SELECT * FROM t_blog WHERE content LIKE '%".$str_keyword_1."%' AND website_type = '新闻'  ORDER BY id DESC LIMIT 2;";
        $query_keyword1_forum = "SELECT * FROM t_blog WHERE content LIKE '%".$str_keyword_1."%' AND website_type = '论坛'  ORDER BY id DESC LIMIT 2;";
        $query_keyword2_weibo = "SELECT * FROM t_blog WHERE content LIKE '%".$str_keyword_2."%' AND website_type = 'weibo' ORDER BY id DESC LIMIT 2;";
        $query_keyword2_news  = "SELECT * FROM t_blog WHERE content LIKE '%".$str_keyword_2."%' AND website_type = '新闻'  ORDER BY id DESC LIMIT 2;";
        $query_keyword2_forum = "SELECT * FROM t_blog WHERE content LIKE '%".$str_keyword_2."%' AND website_type = '论坛'  ORDER BY id DESC LIMIT 2;";
        //$sql = "SELECT * FROM t_blog;";
        $result_keyword1_weibo = mysql_query($query_keyword1_weibo, $con);
        $result_keyword1_news  = mysql_query($query_keyword1_news,  $con);
        $result_keyword1_forum = mysql_query($query_keyword1_forum, $con);
        $result_keyword2_weibo = mysql_query($query_keyword2_weibo, $con);
        $result_keyword2_news  = mysql_query($query_keyword2_news,  $con);
        $result_keyword2_forum = mysql_query($query_keyword2_forum, $con);
        
        mysql_close($con);

        return new ViewModel(array(
            'user' => $cur_user,
            'keyword1' => $keyword1,
            'keyword2' => $keyword2,
            'result_keyword1_weibo' => $result_keyword1_weibo,
            'result_keyword1_news'  => $result_keyword1_news,
            'result_keyword1_forum' => $result_keyword1_forum,
            'result_keyword2_weibo' => $result_keyword2_weibo,
            'result_keyword2_news'  => $result_keyword2_news,
            'result_keyword2_forum' => $result_keyword2_forum,
        ));
    }

    public function timerangepickerAction()
    {
        //企业->网络监测->监测数据->历史数据查询
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_user = $this->getUserTable()->getUserByName($cur_user)->id;
        $this->is_bought($id_user);

        $form = new TimerangepickerForm();
        $form->get('submit')->setValue('查询');
        $request = $this->getRequest();
        if($request->isPost()){
            $timerangepicker = new Timerangepicker();
            $form->setInputFilter($timerangepicker->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid())
            {
                $arr_get_data = $form->getData();
                $start_date = $arr_get_data['start_date'];
                $end_date = $arr_get_data['end_date'];

                //check if the start_date is before the end_date
                $start_Y = (int) substr($start_date,0,4);
                $start_m = (int) substr($start_date,5,2);
                $start_d = (int) substr($start_date,8,2);
                $start_timestamp = mktime(0, 0, 0, $start_m, $start_d, $start_Y);
                $end_Y = (int) substr($end_date,0,4);
                $end_m = (int) substr($end_date,5,2);
                $end_d = (int) substr($end_date,8,2);
                $end_timestamp = mktime(0, 0, 0, $end_m, $end_d, $end_Y);
                if($start_timestamp > $end_timestamp)
                {
                    echo "<a href='/monitor/timerangepicker'>Back</a></br>";
                    die("The start date must be BEFORE the end date!");
                }

                $this->session = new SessionContainer('timerange');
                $this->session->start_date = $start_date;
                $this->session->end_date = $end_date;

                return $this->redirect()->toRoute('monitor', array(
                    'action' => 'history',
                ));
            }
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'form' => $form,
        ));
    }

    public function historyAction()
    {
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_user = $this->getUserTable()->getUserByName($cur_user)->id;
        $this->is_bought($id_user);

        $this->session = new SessionContainer('timerange');
        $start_date = $this->session->start_date;
        $end_date = $this->session->end_date;

        //get Monitor
        $monitorSet = $this->getMonitorTable()->fetchValidMonitorByFkEntUser($id_user);
        $monitor = $monitorSet->current();
        $id_monitor = $monitor->id_monitor;
        //get two Keywords (object)
        $keyword1 = $this->getKeywordTable()->getKeywordByMonitor($id_monitor, 1);
        $keyword2 = $this->getKeywordTable()->getKeywordByMonitor($id_monitor, 2);

        $str_keyword_1 = $keyword1->keyword;
        $str_keyword_2 = $keyword2->keyword;

        //connect to the monitor database
        $con = mysql_connect("localhost:3306", "root", "rocket2012");
        mysql_set_charset('utf8', $con);
        $charset = mysql_client_encoding($con);
        mysql_select_db("article", $con);

        //query
        $query_keyword1_weibo = "SELECT * FROM t_blog WHERE content LIKE '%".$str_keyword_1."%' AND website_type = 'weibo' AND tm_post > '".$start_date."' AND tm_post < '".$end_date."' ORDER BY id DESC;";
        $query_keyword1_news  = "SELECT * FROM t_blog WHERE content LIKE '%".$str_keyword_1."%' AND website_type = '新闻'  AND tm_post > '".$start_date."' AND tm_post < '".$end_date."' ORDER BY id DESC;";
        $query_keyword1_forum = "SELECT * FROM t_blog WHERE content LIKE '%".$str_keyword_1."%' AND website_type = '论坛'  AND tm_post > '".$start_date."' AND tm_post < '".$end_date."' ORDER BY id DESC;";
        $query_keyword2_weibo = "SELECT * FROM t_blog WHERE content LIKE '%".$str_keyword_2."%' AND website_type = 'weibo' AND tm_post > '".$start_date."' AND tm_post < '".$end_date."' ORDER BY id DESC;";
        $query_keyword2_news  = "SELECT * FROM t_blog WHERE content LIKE '%".$str_keyword_2."%' AND website_type = '新闻'  AND tm_post > '".$start_date."' AND tm_post < '".$end_date."' ORDER BY id DESC;";
        $query_keyword2_forum = "SELECT * FROM t_blog WHERE content LIKE '%".$str_keyword_2."%' AND website_type = '论坛'  AND tm_post > '".$start_date."' AND tm_post < '".$end_date."' ORDER BY id DESC;";
        //$sql = "SELECT * FROM t_blog;";
        $result_keyword1_weibo = mysql_query($query_keyword1_weibo, $con);
        $result_keyword1_news  = mysql_query($query_keyword1_news,  $con);
        $result_keyword1_forum = mysql_query($query_keyword1_forum, $con);
        $result_keyword2_weibo = mysql_query($query_keyword2_weibo, $con);
        $result_keyword2_news  = mysql_query($query_keyword2_news,  $con);
        $result_keyword2_forum = mysql_query($query_keyword2_forum, $con);
        
        mysql_close($con);

        return new ViewModel(array(
            'user' => $cur_user,
            'keyword1' => $keyword1,
            'keyword2' => $keyword2,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'result_keyword1_weibo' => $result_keyword1_weibo,
            'result_keyword1_news'  => $result_keyword1_news,
            'result_keyword1_forum' => $result_keyword1_forum,
            'result_keyword2_weibo' => $result_keyword2_weibo,
            'result_keyword2_news'  => $result_keyword2_news,
            'result_keyword2_forum' => $result_keyword2_forum,
        ));
    }

    public function mgmtAction()
    {
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);

        $user = $this->getUserTable()->getUserByName($cur_user);
        $id_user = $user->id;
        $email = $user->email;
        $this->is_bought($id_user);

        //get Monitor
        $monitorSet = $this->getMonitorTable()->fetchValidMonitorByFkEntUser($id_user);
        $monitor = $monitorSet->current();

        return new ViewModel(array(
            'user' => $cur_user,
            'email' => $email,
            'monitor' => $monitor,
        ));
    }

    public function exportsheetAction()
    {
        $arr_type_allowed = array(1);
        $this->session = new SessionContainer('userinfo');
        $username = $this->session->username;
        $password = $this->session->password;
        $usertype = $this->session->usertype;
        if(($this->_checkUser($username, $password, $usertype))
            && ($this->_checkRole($usertype, $arr_type_allowed)))
        {
            $cur_user = $username;
        }

        $id_user = $this->getUserTable()->getUserByName($cur_user)->id;
        $this->is_bought($id_user);

        //get the start_date and end_date from the session
        $this->session = new SessionContainer('timerange');
        $start_date = $this->session->start_date;
        $end_date = $this->session->end_date;

        //get Monitor
        $monitorSet = $this->getMonitorTable()->fetchValidMonitorByFkEntUser($id_user);
        $monitor = $monitorSet->current();
        $id_monitor = $monitor->id_monitor;
        //get two Keywords (object)
        $keyword1 = $this->getKeywordTable()->getKeywordByMonitor($id_monitor, 1);
        $keyword2 = $this->getKeywordTable()->getKeywordByMonitor($id_monitor, 2);

        $str_keyword_1 = $keyword1->keyword;
        $str_keyword_2 = $keyword2->keyword;

        // connection with the database
        $dbhost = "localhost";
        $dbuser = "root";
        $dbpass = "rocket2012";
        $dbname = "article";

        $con = mysql_connect($dbhost,$dbuser,$dbpass);
        mysql_set_charset('utf8', $con);
        mysql_select_db($dbname);

        // require the PHPExcel file
        require './vendor/Classes/PHPExcel.php';

        // simple query
        $query ="SELECT id, project, tm_post, source_site, website_type, is_copied, title, keywords, 
                        content, author_id, author, author_city, author_info, rply_cnt, read_cnt, url, 
                        tone, uid, tm_spider, guanzhu_cnt, fans_cnt, article_cnt, from_device, 
                        forword_cnt, reserved_cnt, article_type, renzhen 
                FROM t_blog 
                WHERE (content LIKE '%".$str_keyword_1."%' OR content LIKE '%".$str_keyword_2."%')
                    AND tm_post > '".$start_date."' AND tm_post < '".$end_date."' 
                ORDER BY id DESC;"; 
        //$query = "SELECT id, username, real_name FROM user;"; 
        $headings = array(
                'id', 'project', 'tm_post', 'source_site', 'website_type', 'is_copied', 'title', 'keywords', 
                'content', 'author_id', 'author', 'author_city', 'author_info', 'rply_cnt', 'read_cnt', 'url', 
                'tone', 'uid', 'tm_spider', 'guanzhu_cnt', 'fans_cnt', 'article_cnt', 'from_device', 
                'forword_cnt', 'reserved_cnt', 'article_type', 'renzhen' 
            );
        //$headings = array('id', 'username', 'real_name');

        if ($result = mysql_query($query) or die(mysql_error())) {
            // Create a new PHPExcel object
            $objPHPExcel = new \PHPExcel();
            $objPHPExcel->getActiveSheet()->setTitle('历史监测数据');

            $rowNumber = 1;
            $col = 'A';
            foreach($headings as $heading) {
                $objPHPExcel->getActiveSheet()->setCellValue($col.$rowNumber,$heading);
                $col++;
            }

            // Loop through the result set
            $rowNumber = 2;
            while ($row = mysql_fetch_row($result)) {
                $col = 'A';
                foreach($row as $cell) {
                    $objPHPExcel->getActiveSheet()->setCellValue($col.$rowNumber,$cell);
                    $col++;
                }
                $rowNumber++;
            }   

            // Freeze pane so that the heading line will not scroll
            $objPHPExcel->getActiveSheet()->freezePane('A2');

            // Save as an Excel BIFF (xls) file
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="历史监测数据.xls"');
            header('Cache-Control: max-age=0');

            $objWriter->save('php://output');
            exit();
        }
        echo 'a problem has occurred... no data retrieved from the database';
    }

    public function exportreportAction()
    {
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_user = $this->getUserTable()->getUserByName($cur_user)->id;
        $this->is_bought($id_user);

        $this->session = new SessionContainer('timerange');
        $start_date = $this->session->start_date;
        $end_date = $this->session->end_date;

        //get Monitor
        $monitorSet = $this->getMonitorTable()->fetchValidMonitorByFkEntUser($id_user);
        $monitor = $monitorSet->current();
        $id_monitor = $monitor->id_monitor;
        //get two Keywords (object)
        $keyword1 = $this->getKeywordTable()->getKeywordByMonitor($id_monitor, 1);
        $keyword2 = $this->getKeywordTable()->getKeywordByMonitor($id_monitor, 2);

        $str_keyword_1 = $keyword1->keyword;
        $str_keyword_2 = $keyword2->keyword;

        //connect to the monitor database
        $con = mysql_connect("localhost:3306", "root", "rocket2012");
        mysql_set_charset('utf8', $con);
        $charset = mysql_client_encoding($con);
        mysql_select_db("article", $con);

        //query
        $query_chart1_keyword1_weibo = "SELECT COUNT(id) AS quantity, SUBSTRING(tm_post,1,10) AS date_post 
                                        FROM t_blog 
                                        WHERE content LIKE '%".$str_keyword_1."%' 
                                        AND website_type = 'weibo' 
                                        AND tm_post > '".$start_date."' AND tm_post < '".$end_date."' 
                                        GROUP BY date_post
                                        ORDER BY date_post ASC;";
        $query_chart1_keyword1_news  = "SELECT COUNT(id) AS quantity, SUBSTRING(tm_post,1,10) AS date_post 
                                        FROM t_blog 
                                        WHERE content LIKE '%".$str_keyword_1."%' 
                                        AND website_type = '新闻'  
                                        AND tm_post > '".$start_date."' AND tm_post < '".$end_date."' 
                                        GROUP BY date_post
                                        ORDER BY date_post ASC;";
        $query_chart1_keyword1_forum = "SELECT COUNT(id) AS quantity, SUBSTRING(tm_post,1,10) AS date_post 
                                        FROM t_blog 
                                        WHERE content LIKE '%".$str_keyword_1."%' 
                                        AND website_type = '论坛'  
                                        AND tm_post > '".$start_date."' AND tm_post < '".$end_date."' 
                                        GROUP BY date_post
                                        ORDER BY date_post ASC;";
        $query_chart2_keyword2_weibo = "SELECT COUNT(id) AS quantity, SUBSTRING(tm_post,1,10) AS date_post 
                                        FROM t_blog 
                                        WHERE content LIKE '%".$str_keyword_2."%' 
                                        AND website_type = 'weibo' 
                                        AND tm_post > '".$start_date."' AND tm_post < '".$end_date."' 
                                        GROUP BY date_post
                                        ORDER BY date_post ASC;";
        $query_chart2_keyword2_news  = "SELECT COUNT(id) AS quantity, SUBSTRING(tm_post,1,10) AS date_post 
                                        FROM t_blog 
                                        WHERE content LIKE '%".$str_keyword_2."%' 
                                        AND website_type = '新闻'  
                                        AND tm_post > '".$start_date."' AND tm_post < '".$end_date."' 
                                        GROUP BY date_post
                                        ORDER BY date_post ASC;";
        $query_chart2_keyword2_forum = "SELECT COUNT(id) AS quantity, SUBSTRING(tm_post,1,10) AS date_post 
                                        FROM t_blog 
                                        WHERE content LIKE '%".$str_keyword_2."%' 
                                        AND website_type = '论坛'  
                                        AND tm_post > '".$start_date."' AND tm_post < '".$end_date."' 
                                        GROUP BY date_post
                                        ORDER BY date_post ASC;";
        $query_chart3_keyword1       = "SELECT COUNT(id) AS quantity, website_type
                                        FROM t_blog
                                        WHERE content LIKE '%".$str_keyword_1."%' 
                                        AND tm_post > '".$start_date."' AND tm_post < '".$end_date."'
                                        GROUP BY website_type";
        $query_chart3_keyword2       = "SELECT COUNT(id) AS quantity, website_type
                                        FROM t_blog
                                        WHERE content LIKE '%".$str_keyword_2."%' 
                                        AND tm_post > '".$start_date."' AND tm_post < '".$end_date."'
                                        GROUP BY website_type";
        $query_chart4_keyword1_weibo = "SELECT COUNT(id) AS quantity, tone
                                        FROM t_blog
                                        WHERE content LIKE '%".$str_keyword_1."%' 
                                        AND website_type = 'weibo'  
                                        AND tm_post > '".$start_date."' AND tm_post < '".$end_date."'
                                        GROUP BY tone";
        $query_chart4_keyword1_news = "SELECT COUNT(id) AS quantity, tone
                                        FROM t_blog
                                        WHERE content LIKE '%".$str_keyword_1."%' 
                                        AND website_type = '新闻'  
                                        AND tm_post > '".$start_date."' AND tm_post < '".$end_date."'
                                        GROUP BY tone";
        $query_chart4_keyword1_forum = "SELECT COUNT(id) AS quantity, tone
                                        FROM t_blog
                                        WHERE content LIKE '%".$str_keyword_1."%' 
                                        AND website_type = '论坛'  
                                        AND tm_post > '".$start_date."' AND tm_post < '".$end_date."'
                                        GROUP BY tone";
        $query_chart5_keyword2_weibo = "SELECT COUNT(id) AS quantity, tone
                                        FROM t_blog
                                        WHERE content LIKE '%".$str_keyword_2."%' 
                                        AND website_type = 'weibo'  
                                        AND tm_post > '".$start_date."' AND tm_post < '".$end_date."'
                                        GROUP BY tone";
        $query_chart5_keyword2_news  = "SELECT COUNT(id) AS quantity, tone
                                        FROM t_blog
                                        WHERE content LIKE '%".$str_keyword_2."%' 
                                        AND website_type = '新闻'  
                                        AND tm_post > '".$start_date."' AND tm_post < '".$end_date."'
                                        GROUP BY tone";
        $query_chart5_keyword2_forum = "SELECT COUNT(id) AS quantity, tone
                                        FROM t_blog
                                        WHERE content LIKE '%".$str_keyword_2."%' 
                                        AND website_type = '论坛'  
                                        AND tm_post > '".$start_date."' AND tm_post < '".$end_date."'
                                        GROUP BY tone";
        $result_chart1_keyword1_weibo = mysql_query($query_chart1_keyword1_weibo, $con);
        $result_chart1_keyword1_news  = mysql_query($query_chart1_keyword1_news,  $con);
        $result_chart1_keyword1_forum = mysql_query($query_chart1_keyword1_forum, $con);
        $result_chart2_keyword2_weibo = mysql_query($query_chart2_keyword2_weibo, $con);
        $result_chart2_keyword2_news  = mysql_query($query_chart2_keyword2_news,  $con);
        $result_chart2_keyword2_forum = mysql_query($query_chart2_keyword2_forum, $con);
        $result_chart3_keyword1       = mysql_query($query_chart3_keyword1, $con);
        $result_chart3_keyword2       = mysql_query($query_chart3_keyword2, $con);
        $result_chart4_keyword1_weibo = mysql_query($query_chart4_keyword1_weibo, $con);
        $result_chart4_keyword1_news  = mysql_query($query_chart4_keyword1_news,  $con);
        $result_chart4_keyword1_forum = mysql_query($query_chart4_keyword1_forum, $con);
        $result_chart5_keyword2_weibo = mysql_query($query_chart5_keyword2_weibo, $con);
        $result_chart5_keyword2_news  = mysql_query($query_chart5_keyword2_news,  $con);
        $result_chart5_keyword2_forum = mysql_query($query_chart5_keyword2_forum, $con);
        
        mysql_close($con);


        return new ViewModel(array(
            'user' => $cur_user,
            'result_chart1_keyword1_weibo' => $result_chart1_keyword1_weibo,
            'result_chart1_keyword1_news'  => $result_chart1_keyword1_news,
            'result_chart1_keyword1_forum' => $result_chart1_keyword1_forum,
            'result_chart2_keyword2_weibo' => $result_chart2_keyword2_weibo,
            'result_chart2_keyword2_news'  => $result_chart2_keyword2_news,
            'result_chart2_keyword2_forum' => $result_chart2_keyword2_forum,
            'result_chart3_keyword1'       => $result_chart3_keyword1,
            'result_chart3_keyword2'       => $result_chart3_keyword2,
            'result_chart4_keyword1_weibo' => $result_chart4_keyword1_weibo,
            'result_chart4_keyword1_news'  => $result_chart4_keyword1_news,
            'result_chart4_keyword1_forum' => $result_chart4_keyword1_forum,
            'result_chart5_keyword2_weibo' => $result_chart5_keyword2_weibo,
            'result_chart5_keyword2_news'  => $result_chart5_keyword2_news,
            'result_chart5_keyword2_forum' => $result_chart5_keyword2_forum,
        ));
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
