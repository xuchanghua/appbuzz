<?php
namespace Interview\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Interview\Model\Interview;          // <-- Add this import
use Interview\Form\InterviewForm;       // <-- Add this import
use Zend\Session\Container as SessionContainer;
use User\Model\User;
use DateTime;

class InterviewController extends AbstractActionController
{
    protected $interviewTable;
    protected $productTable;
    protected $userTable;

    public function indexAction()
    {
        //媒体->采访管理
        $arr_type_allowed = array(2);
        $cur_user = $this->_auth($arr_type_allowed);

        return new ViewModel(array(
            'user' => $cur_user,    
            'current_interview' => $this->getInterviewTable()->fetchCurrentInterview($cur_user),
            'past_interview' => $this->getInterviewTable()->fetchPastInterview($cur_user),
            'products' => $this->getProductTable()->fetchAll(),    
            'is_writer' => $this->getUserTable()->getUserByName($cur_user)->is_writer,
        ));
    }

    public function adminAction()
    {
        //管理员->订单管理->媒体采访
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);

        return new ViewModel(array(
            'user' => $cur_user,    
            'interview' => $this->getInterviewTable()->fetchAllDesc(),
            'products' => $this->getProductTable()->fetchAll(),    
            'all_users' => $this->getUserTable()->fetchAll()
        ));
    }

    public function addAction()
    {
        //媒体->采访邀约
        $arr_type_allowed = array(2);
        $cur_user = $this->_auth($arr_type_allowed);

        $form = new InterviewForm();
        $form->get('submit')->setValue('发出邀约');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $interview = new Interview();
            $form->setInputFilter($interview->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $interview->exchangeArray($form->getData());
                $product = $this->getProductTable()->getProduct($interview->fk_product);
                $enterprise_user = $this->getUserTable()->getUserByName($product->created_by);
                $media_user = $this->getUserTable()->getUserByName($cur_user);
                $interview->fk_enterprise_user = $enterprise_user->id;
                $interview->fk_media_user = $media_user->id;
                $interview->fk_interview_status = 1;//invited
                $interview->created_at = $this->_getDateTime();
                $interview->created_by = $cur_user;
                $interview->updated_at = $this->_getDateTime();
                $interview->updated_by = $cur_user;
                $this->getInterviewTable()->saveInterview($interview);

                $id_interview = $this->getInterviewTable()->getId($interview->created_at, $interview->created_by);

                $interview2 = $this->getInterviewTable()->getInterview($id_interview);
                $interview2->order_no = 52000000 + $id_interview;
                $this->getInterviewTable()->saveInterview($interview2);

                // Redirect to list of interview detail
                return $this->redirect()->toRoute('interview', array(
                    'action' => 'detail',
                    'id'     => $id_interview,
                ));
            }
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'form' => $form,
            'all_products' => $this->getProductTable()->fetchAll(),
            'is_writer' => $this->getUserTable()->getUserByName($cur_user)->is_writer,
        ));
    }

    public function detailAction()
    {
        //媒体->采访管理->邀约详情
        $arr_type_allowed = array(2, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_interview = (int)$this->params()->fromRoute('id', 0);
        if(!$id_interview){
            return $this->redirect()->toRoute('interview', array(
                'action' => 'index'
            ));
        }
        $interview = $this->getInterviewTable()->getInterview($id_interview);
        $product = $this->getProductTable()->getProduct($interview->fk_product);

        return new ViewModel(array(
            'user' => $cur_user,
            'user_type' => $this->getUserTable()->getUserByName($cur_user)->fk_user_type,
            'interview' => $interview,
            'product' => $product,
            'is_writer' => $this->getUserTable()->getUserByName($cur_user)->is_writer,
        ));
    }

    public function editAction()
    {        
        //媒体->采访管理->修改订单
        $arr_type_allowed = array(2, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_interview = (int)$this->params()->fromRoute('id', 0);
        if(!$id_interview){
            return $this->redirect()->toRoute('interview', array(
                'action' => 'index'
            ));
        }
        $interview = $this->getInterviewTable()->getInterview($id_interview);
        $product = $this->getProductTable()->getProduct($interview->fk_product);
        $itv_fk_product          = $interview->fk_product;
        $itv_fk_enterprise_user  = $interview->fk_enterprise_user;
        $itv_fk_media_user       = $interview->fk_media_user;
        $itv_order_no            = $interview->order_no;
        $itv_created_at          = $interview->created_at;
        $itv_created_by          = $interview->created_by;
        $itv_fk_interview_status = $interview->fk_interview_status;
        $form  = new InterviewForm();
        $form->bind($interview);
        $form->get('submit')->setAttribute('value', '提交');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($interview->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $form->getData()->fk_product          = $itv_fk_product;
                $form->getData()->fk_enterprise_user  = $itv_fk_enterprise_user;
                $form->getData()->fk_media_user       = $itv_fk_media_user;
                $form->getData()->order_no            = $itv_order_no;
                $form->getData()->created_at          = $itv_created_at;
                $form->getData()->created_by          = $itv_created_by;
                $form->getData()->fk_interview_status = $itv_fk_interview_status;
                $form->getData()->updated_at          = $this->_getDateTime();
                $form->getData()->updated_by          = $cur_user;
                $this->getInterviewTable()->saveInterview($form->getData());

                // Redirect to list of interview detail
                return $this->redirect()->toRoute('interview', array(
                    'action' => 'detail',
                    'id'     => $id_interview,
                ));
            }
        }

        return array(
            'user' => $cur_user,
            'user_type' => $this->getUserTable()->getUserByName($cur_user)->fk_user_type,
            'form' => $form,
            'interview' => $interview,
            'product' => $product,
            'is_writer' => $this->getUserTable()->getUserByName($cur_user)->is_writer,
        );
    }

    public function cancelAction()
    {
        //媒体->采访管理->撤销订单
        $arr_type_allowed = array(2);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_interview = (int)$this->params()->fromRoute('id', 0);
        if(!$id_interview){
            return $this->redirect()->toRoute('interview', array(
                'action' => 'index'
            ));
        }
        $interview = $this->getInterviewTable()->getInterview($id_interview);
        $interview->fk_interview_status = 4;//canceled
        $this->getInterviewTable()->saveInterview($interview);

        return $this->redirect()->toRoute('interview', array(
            'action' => 'index',
        )); 
    }

    public function entcurrentAction()
    {
        //企业->媒体采访->当前邀约
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);
        $fk_enterprise_user = $this->getUserTable()->getUserByName($cur_user)->id;

        return new ViewModel(array(
            'user' => $cur_user,    
            'current_interview' => $this->getInterviewTable()->fetchCurrentEntInterview($fk_enterprise_user),
            'products' => $this->getProductTable()->fetchAll(),    
        ));
    }

    public function entpastAction()
    {
        //企业->媒体采访->过往邀约
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);
        $fk_enterprise_user = $this->getUserTable()->getUserByName($cur_user)->id;

        return new ViewModel(array(
            'user' => $cur_user,
            'past_interview' => $this->getInterviewTable()->fetchPastEntInterview($fk_enterprise_user),
            'products' => $this->getProductTable()->fetchAll(),    
        ));
    }

    public function entdetailAction()
    {
        //企业->媒体采访->邀约详情
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_interview = (int)$this->params()->fromRoute('id', 0);
        if(!$id_interview){
            return $this->redirect()->toRoute('enterprise', array(
                'action' => 'index'
            ));
        }
        $interview = $this->getInterviewTable()->getInterview($id_interview);
        $product = $this->getProductTable()->getProduct($interview->fk_product);

        return new ViewModel(array(
            'user' => $cur_user,
            'interview' => $interview,
            'product' => $product,
        ));
    }

    public function answerAction()
    {
        //企业->媒体采访->回答问题
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_interview = (int)$this->params()->fromRoute('id', 0);
        if(!$id_interview){
            return $this->redirect()->toRoute('interview', array(
                'action' => 'entcurrent',
            ));
        }

        $interview = $this->getInterviewTable()->getInterview($id_interview);
        $product = $this->getProductTable()->getProduct($interview->fk_product);
        $itv_fk_product          = $interview->fk_product;
        $itv_fk_enterprise_user  = $interview->fk_enterprise_user;
        $itv_fk_media_user       = $interview->fk_media_user;
        $itv_date_time           = $interview->date_time;
        $itv_purpose             = $interview->purpose;
        $itv_outline             = $interview->outline;
        $itv_order_no            = $interview->order_no;
        $itv_created_at          = $interview->created_at;
        $itv_created_by          = $interview->created_by;
        $itv_fk_interview_status = $interview->fk_interview_status;

        $form  = new InterviewForm();
        $form->bind($interview);
        $form->get('submit')->setAttribute('value', '提交');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($interview->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $form->getData()->fk_product          = $itv_fk_product;
                $form->getData()->fk_enterprise_user  = $itv_fk_enterprise_user;
                $form->getData()->fk_media_user       = $itv_fk_media_user;
                $form->getData()->date_time           = $itv_date_time;
                $form->getData()->purpose             = $itv_purpose;
                $form->getData()->outline             = $itv_outline;
                $form->getData()->order_no            = $itv_order_no;
                $form->getData()->created_at          = $itv_created_at;
                $form->getData()->created_by          = $itv_created_by;
                $form->getData()->fk_interview_status = $itv_fk_interview_status;
                $form->getData()->updated_at          = $this->_getDateTime();
                $form->getData()->updated_by          = $cur_user;
                $this->getInterviewTable()->saveInterview($form->getData());

                // Redirect to list of interview detail
                return $this->redirect()->toRoute('interview', array(
                    'action' => 'entdetail',
                    'id'     => $id_interview,
                ));
            }
            /*else{
                die(var_dump($form->getMessages()));
            }*/
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'interview' => $interview,
            'product' => $product,
            'form' => $form,
        ));
    }

    public function completeAction()
    {
        //媒体->采访邀约->结束采访
        $arr_type_allowed = array(2);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_interview = (int)$this->params()->fromRoute('id', 0);
        if(!$id_interview){
            return $this->redirect()->toRoute('interview', array(
                'action' => 'index',
            ));
        }
        $interview = $this->getInterviewTable()->getInterview($id_interview);
        $interview->fk_interview_status = 5;//completed
        $this->getInterviewTable()->saveInterview($interview);

        return $this->redirect()->toRoute('interview', array(
            'action' => 'detail',
            'id'     => $id_interview,
        ));
    }

    public function entaccAction()
    {
        //企业->媒体采访->接受邀请
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_interview = (int)$this->params()->fromRoute('id', 0);
        if(!$id_interview){
            return $this->redirect()->toRoute('interview', array(
                'action' => 'entdetail',
                'id'     => $id_interview,
            ));
        }
        $interview = $this->getInterviewTable()->getInterview($id_interview);
        $interview->fk_interview_status = 2;//accepted
        $this->getInterviewTable()->saveInterview($interview);

        return $this->redirect()->toRoute('interview', array(
            'action' => 'entdetail',
            'id'     => $id_interview,
        ));
    }

    public function entrejAction()
    {
        //企业->媒体采访->拒绝邀请
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_interview = (int)$this->params()->fromRoute('id', 0);
        if(!$id_interview){
            return $this->redirect()->toRoute('interview', array(
                'action' => 'entdetail',
                'id'     => $id_interview,
            ));
        }
        $interview = $this->getInterviewTable()->getInterview($id_interview);
        $interview->fk_interview_status = 3;//rejected
        $this->getInterviewTable()->saveInterview($interview);

        return $this->redirect()->toRoute('interview', array(
            'action' => 'entdetail',
            'id'     => $id_interview,
        ));
    }

    public function deleteAction()
    {
    }

    public function getInterviewTable()
    {
        if (!$this->interviewTable) {
	    $sm = $this->getServiceLocator();
	    $this->interviewTable = $sm->get('Interview\Model\InterviewTable');
        }
        return $this->interviewTable;
    }

    public function getProductTable()
    {
        if(!$this->productTable){
            $sm = $this->getServiceLocator();
            $this->productTable = $sm->get('Product\Model\ProductTable');
        }
        return $this->productTable;
    }

    public function getUserTable()
    {
        if(!$this->userTable){
            $sm = $this->getServiceLocator();
            $this->userTable = $sm->get('User\Model\UserTable');
        }
        return $this->userTable;
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
