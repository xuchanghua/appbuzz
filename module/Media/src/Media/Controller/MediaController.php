<?php
namespace Media\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container as SessionContainer;
use User\Model\User;
use Media\Model\Media;
use Media\Model\Pubmedia;
use Media\Form\MediaForm;
use Media\Form\PubmediaForm;
use DateTime;

class MediaController extends AbstractActionController
{
    protected $userTable;
    protected $mediaTable;
    protected $creditTable;
    protected $creditlogTable;
    protected $topicTable;
    protected $evamediaTable;
    protected $interviewTable;
    protected $wrtmediaTable;
    protected $productTable;
    protected $evaluateTable;
    protected $writerTable;
    protected $pubmediaTable;

    public function indexAction()
    {
        //Authenticate the user information from the session
        //$this->_authenticateSession();
        $arr_type_allowed = array(2, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        $email = $this->_getUserEmail($cur_user);
        $fk_media = $this->_getUserFkMedia($cur_user);

        $form = new MediaForm();
        $form->get('submit')->setValue('保存');
        $request = $this->getRequest();
        if($request->isPost()){
            $media = new Media();
            $form->setInputFilter($media->getInputFilter());
            $form->setData($request->getPost());
            $user = new User();
            $user = $this->getUserTable()->getUserByName($cur_user);
            if($form->isValid()){
                $media->exchangeArray($form->getData());
                $media->created_at = $this->_getDateTime();
                $media->created_by = $cur_user;
                $media->updated_at = $this->_getDateTime();
                $media->updated_by = $cur_user;
                $this->getMediaTable()->saveMedia($media);
                $newMed = $this->getMediaTable()->getMediaByName($media->name);
                $user->fk_media = $newMed->id_media;
                //die(var_dump($user));
                $this->getUserTable()->saveUser($user);
                return $this->redirect()->toRoute('media',array(
                    'action' => 'index',
                ));
            }
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'email' => $email,
            'id_user' => $this->getUserTable()->getUserByName($cur_user)->id,
            'fk_media' => $fk_media,
            'media' => (isset($fk_media))? $this->getMediaTable()->getMedia($fk_media) : null,
            'form' => $form,
            'is_writer' => $this->getUserTable()->getUserByName($cur_user)->is_writer,
            //'products' => $this->getProductTable()->fetchProductByUser($cur_user),
            ));
    }

    public function editAction()
    {        
        $arr_type_allowed = array(2, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id = (int)$this->params()->fromRoute('id',0);
        if (!$id) {
            return $this->redirect()->toRoute('media', array(
                'action' => 'index',
            ));
        }
        $media = $this->getMediaTable()->getMedia($id);
        $md_created_at = $media->created_at;
        $md_created_by = $media->created_by;
        $form = new MediaForm();
        $form->bind($media);
        $form->get('submit')->setAttribute('value','保存');

        $request = $this->getRequest();
        if ($request->isPost()){
            $form->setInputFilter($media->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $form->getData()->created_at = $md_created_at;
                $form->getData()->created_by = $md_created_by;
                $form->getData()->updated_at = $this->_getDateTime();
                $form->getData()->updated_by = $cur_user;
                $this->getMediaTable()->saveMedia($form->getData());

                return $this->redirect()->toRoute('media',array(
                    'action' => 'index',
                ));
            }
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'email' => $this->_getUserEmail($cur_user),
            'fk_media' => $this->_getUserFkMedia($cur_user),
            'media' => (isset($fk_media))? $this->getMediaTable()->getMedia($fk_media) : null,
            'form' => $form,
            'is_writer' => $this->getUserTable()->getUserByName($cur_user)->is_writer,
        ));
    }

    public function adminAction()
    {        
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_media = (int)$this->params()->fromRoute('id',0);
        if (!$id_media) {
            return $this->redirect()->toRoute('user', array(
                'action' => 'admin',
            ));
        }
        $media = $this->getMediaTable()->getMedia($id_media);
        $target_user = $this->getUserTable()->getUserByFkMedia($media->id_media);
        $md_created_at = $media->created_at;
        $md_created_by = $media->created_by;
        $form = new MediaForm();
        $form->bind($media);
        $form->get('submit')->setAttribute('value','保存');

        $request = $this->getRequest();
        if ($request->isPost()){
            $form->setInputFilter($media->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $form->getData()->created_at = $md_created_at;
                $form->getData()->created_by = $md_created_by;
                $form->getData()->updated_at = $this->_getDateTime();
                $form->getData()->updated_by = $cur_user;
                $this->getMediaTable()->saveMedia($form->getData());

                return $this->redirect()->toRoute('user',array(
                    'action' => 'detail',
                    'id'     => $target_user->id,
                ));
            }
        }

        return new ViewModel(array(
            'user'  => $cur_user,
            'media' => $media,
            'form'  => $form,
        ));
    }

    public function myaccountAction()
    {
        //媒体用户首页->我的账户
        $arr_type_allowed = array(2);        
        $cur_user = $this->_auth($arr_type_allowed);
        $user = $this->getUserTable()->getUserByName($cur_user);
        $credit = $this->getCreditTable()->getCreditByFkUser($user->id);
        $creditlog = $this->getCreditlogTable()->fetchLogByFkCreditLimit5($credit->id_credit);

        return new ViewModel(array(
            'user' => $cur_user,
            'credit' => $credit,
            'creditlog' => $creditlog,
            'is_writer' => $this->getUserTable()->getUserByName($cur_user)->is_writer,
        ));
    }

    public function myorderAction()
    {
        //媒体用户首页->我的订单
        $arr_type_allowed = array(2);
        $cur_user = $this->_auth($arr_type_allowed);

        $all_users = $this->getUserTable()->fetchAll();
        $products  = $this->getProductTable()->fetchAll();
        $evaluates = $this->getEvaluateTable()->fetchAll();
        $writers   = $this->getWriterTable()->fetchAll();

        $topic5     = $this->getTopicTable()->fetchTopicByUserLimit5($cur_user);
        $evamedia5  = $this->getEvamediaTable()->fetchEvamediaByUserDescLimit5($cur_user);
        $interview5 = $this->getInterviewTable()->fetchInterviewByUserLimit5($cur_user);
        $wrtmedia5  = $this->getWrtmediaTable()->fetchWrtmediaByUserLimit5($cur_user);

        return new ViewModel(array(
            'user'       => $cur_user,
            'all_users'  => $all_users,
            'products'   => $products,
            'evaluates'  => $evaluates,
            'writers'    => $writers,
            'topic5'     => $topic5,
            'evamedia5'  => $evamedia5,
            'interview5' => $interview5,
            'wrtmedia5'  => $wrtmedia5,
        ));        
    }

    public function publistAction()
    {
        //管理员->发稿媒体列表
        $arr_type_allowed = array(3, 4);        
        $cur_user = $this->_auth($arr_type_allowed);

        return new ViewModel(array(
            'user' => $cur_user,
            'pubmedias' => $this->getPubmediaTable()->fetchAll(),
        ));
    }

    public function addpubmediaAction()
    {
        //管理员->创建发稿媒体
        $arr_type_allowed = array(3, 4);        
        $cur_user = $this->_auth($arr_type_allowed);

        $form = new PubmediaForm();
        $form->get('submit')->setValue('创建发稿媒体');
        $request = $this->getRequest();
        if($request->isPost()){
            $pubmedia = new Pubmedia();
            $form->setInputFilter($pubmedia->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $pubmedia->exchangeArray($form->getData());
                $pubmedia->created_at = $this->_getDateTime();
                $pubmedia->created_by = $cur_user;
                $pubmedia->updated_at = $this->_getDateTime();
                $pubmedia->updated_by = $cur_user;
                $this->getPubmediaTable()->savePubmedia($pubmedia);
            }
            return $this->redirect()->toRoute('media', array(
                'action' => 'publist',
            ));
        }
        return new ViewModel(array(
            'user' => $cur_user,
            'form' => $form,
        ));
    }

    public function topicpublishAction()
    {
    }

    public function appevaluatingAction()
    {
    }

    public function appinterviewAction()
    {
    }

    public function editorAction()
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

    public function getMediaTable()
    {
        if(!$this->mediaTable){
            $sm = $this->getServiceLocator();
            $this->mediaTable = $sm->get('Media\Model\MediaTable');
        }
        return $this->mediaTable;
    }

    public function getPubmediaTable()
    {
        if(!$this->pubmediaTable){
            $sm = $this->getServiceLocator();
            $this->pubmediaTable = $sm->get('Media\Model\PubmediaTable');
        }
        return $this->pubmediaTable;
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

    public function getTopicTable()
    {
        if(!$this->topicTable){
            $sm = $this->getServiceLocator();
            $this->topicTable = $sm->get('Topic\Model\TopicTable');
        }
        return $this->topicTable;
    }

    public function getEvamediaTable()
    {
        if(!$this->evamediaTable){
            $sm = $this->getServiceLocator();
            $this->evamediaTable = $sm->get('Evaluate\Model\EvamediaTable');
        }
        return $this->evamediaTable;
    }

    public function getInterviewTable()
    {
        if(!$this->interviewTable){
            $sm = $this->getServiceLocator();
            $this->interviewTable = $sm->get('Interview\Model\InterviewTable');
        }
        return $this->interviewTable;
    }

    public function getWrtmediaTable()
    {
        if(!$this->wrtmediaTable){
            $sm = $this->getServiceLocator();
            $this->wrtmediaTable = $sm->get('Writer\Model\WrtmediaTable');
        }
        return $this->wrtmediaTable;
    }

    public function getProductTable()
    {
        if(!$this->productTable){
            $sm = $this->getServiceLocator();
            $this->productTable = $sm->get('Product\Model\ProductTable');
        }
        return $this->productTable;
    }

    public function getEvaluateTable()
    {
        if(!$this->evaluateTable){
            $sm = $this->getServiceLocator();
            $this->evaluateTable = $sm->get('Evaluate\Model\EvaluateTable');
        }
        return $this->evaluateTable;
    }

    public function getWriterTable()
    {
        if(!$this->writerTable){
            $sm = $this->getServiceLocator();
            $this->writerTable = $sm->get('Writer\Model\WriterTable');
        }
        return $this->writerTable;
    }

    protected function _authorizeUser($type, $user, $pass)
    {        
         //check if the username or password is empty
        if((!$user)||(!$pass))
        {
            echo "<a href='/'>Back</a></br>";
            die("Username or Password cannot be empty!");
        }
        //check if the username is exist, and if it's a media user
        if((!$this->getUserTable()->checkUser($user))
            ||($this->getUserTable()->getUserByName($user)->fk_user_type != $type))
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

    protected function _authenticateSession()
    {        
        $this->session = new SessionContainer('userinfo');
        $username = $this->session->username;
        $password = $this->session->password;
        if($this->_authorizeUser(2, $username, $password))
        {
            echo "Welcome, ".$username;
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

    protected function _getUserFkMedia($user)
    {
        return $this->getUserTable()->getUserByName($user)->fk_media;
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
