<?php
namespace Writer\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Writer\Model\Writer;          // <-- Add this import
use Writer\Form\WriterForm;       // <-- Add this import
use Zend\Session\Container as SessionContainer;
use User\Model\User;
use Zend\File\Transfer\Adapter\Http as FileHttp;
use DateTime;
use Writer\Model\Wrtmedia;

class WriterController extends AbstractActionController
{
    protected $writerTable;
    protected $userTable;
    protected $productTable;
    protected $wrtmediaTable;

    public function indexAction()
    {     
        $arr_type_allowed = array(1, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        $request = $this->getRequest();
        $keyword = trim($request->getQuery(''));
        $page = intval($request->getQuery('page',1));
        $paginator = $this->getWriterTable()->getPaginator($keyword, $page, 5, 1, $cur_user);
        $view = new ViewModel(array(
            'user' => $cur_user,
            'writer' => $this->getWriterTable()->fetchWriterByUser($cur_user),
            'products' => $this->getProductTable()->fetchAll(),
            )
        );
        $view->setVariable('paginator', $paginator);
        return $view;
    }

    public function neworderAction()
    {
        $cur_user = $this->_authenticateSession(1);

        if(!$id_product = (int)$this->params()->fromQuery('id_product', 0))
        {
            $id_product = $this->getRequest()->getPost()->fk_product;
        }
        $product = $this->getProductTable()->getProduct($id_product);
        $writer = new Writer();
        $writer->fk_product = $product->id_product;
        $writer->web_link = $product->web_link;
        $writer->appstore_link = $product->appstore_link;
        $writer->androidmkt_link = $product->androidmkt_link;

        $form = new WriterForm();
        $form->bind($writer);
        $form->get('submit')->setAttribute('value', '保存');

        $request = $this->getRequest();
        if($request->isPost()){
            //upload start
            $adapter = new FileHttp();
            $adapter->setDestination('public');
            if (!$adapter->receive()) 
            {
                echo implode("\n", $adapter->getMessages());
                //die($adapter->getMessages());
            }
            //upload end
            $form->setInputFilter($writer->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $form->getData()->created_by = $cur_user;
                $form->getData()->created_at = $this->_getDateTime();
                $form->getData()->updated_by = $cur_user;
                $form->getData()->updated_at = $this->_getDateTime();
                $form->getData()->fk_product = $id_product;
                $this->getWriterTable()->saveWriter($form->getData());   
                $id_writer = $this->getWriterTable()->getId(
                    $form->getData()->created_at, 
                    $form->getData()->created_by
                );

                //start: handle the assigned media
                $arr_get = $form->getData();
                $str_wrtmedias = trim($arr_get->wrtmedia);
                $arr_wrtmedias = explode(";", $str_wrtmedias);
                foreach ($arr_wrtmedias as $wm)
                {
                    $wrtmedia = new Wrtmedia();
                    $wrtmedia->fk_writer = $id_writer;
                    $enterprise_user = $this->getUserTable()->getUserByName($cur_user);
                    $wrtmedia->fk_enterprise_user = $enterprise_user->id;
                    if($this->getUserTable()->checkUser($wm)) {
                        $media_user = $this->getUserTable()->getUserByName($wm);
                        $wrtmedia->fk_media_user = $media_user->id;
                        $wrtmedia->created_by = $cur_user;
                        $wrtmedia->created_at = $this->_getDateTime();
                        $wrtmedia->updated_by = $cur_user;
                        $wrtmedia->updated_at = $this->_getDateTime();
                        $this->getWrtmediaTable()->saveWrtmedia($wrtmedia);
                    }
                    else
                    {
                        continue;
                    }
                }


                return $this->redirect()->toRoute('writer',array(
                    'action'=>'detail',
                    'id'    => $id_writer,
                ));
            }
        }

        return new ViewModel(array(
            'product' => $product,
            'user' => $cur_user,
            'form' => $form,
        ));
    }

    public function detailAction()
    {
        $cur_user = $this->_authenticateSession(1);

        $id = (int)$this->params()->fromRoute('id',0);        
        if (!$id) {
            return $this->redirect()->toRoute('writer', array(
                'action' => 'index'
            ));
        }
        $writer = $this->getWriterTable()->getWriter($id);

        /*$arr_media_assignees = array();
        $wrtmedia = $this->getWrtmediaTable()->fetchWrtmediaByFkWrt($id);
        if($wrtmedia)
        {
            foreach ($wrtmedia as $wm)
            {
                $media_user = $this->getUserTable()->getUser($wm->fk_media_user);
                $arr_media_assignees[] = $media_user->username;
            }
        }*/

        return new ViewModel(array(
            'writer' => $writer,
            'user' => $cur_user,
            'allusers' => $this->getUserTable()->fetchAll(),
            'id' => $id,
            'product' => $this->getProductTable()->getProduct($writer->fk_product),
            //'media_assignees' => $arr_media_assignees,
            'wrtmedias' => $this->getWrtmediaTable()->fetchWmExRejByMedByFkWrt($id),//not include those rejected by the media
        ));
    }    

    public function editAction()
    {
        $cur_user = $this->_authenticateSession(1);

        $id = (int)$this->params()->fromRoute('id',0);
        if(!$id){
            return $this->redirect()->toRoute('writer', array(
                'action' => 'index',
            ));
        }
        $writer = $this->getWriterTable()->getWriter($id);
        $product = $this->getProductTable()->getProduct($writer->fk_product);
        $eva_created_by = $writer->created_by;
        $eva_created_at = $writer->created_at;
        $form = new WriterForm();
        $form->bind($writer);
        $form->get('submit')->setAttribute('value','保存');

        $request = $this->getRequest();
        if($request->isPost()){            
            //upload start
            $adapter = new FileHttp();
            $adapter->setDestination('public');
            if (!$adapter->receive()) 
            {
                echo implode("\n", $adapter->getMessages());
                //die($adapter->getMessages());
            }
            //upload end
            $form->setInputFilter($writer->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $form->getData()->created_by = $eva_created_by;
                $form->getData()->created_at = $eva_created_at;
                $form->getData()->updated_by = $cur_user;
                $form->getData()->updated_at = $this->_getDateTime();
                $this->getWriterTable()->saveWriter($form->getData());

                return $this->redirect()->toRoute('writer',array(
                    'action' => 'detail',
                    'id'     => $id,
                ));
            }
        }

        $arr_media_assignees = array();
        $wrtmedia = $this->getWrtmediaTable()->fetchWrtmediaByFkWrt($id);
        if($wrtmedia)
        {
            foreach ($wrtmedia as $wm)
            {
                $media_user = $this->getUserTable()->getUser($wm->fk_media_user);
                $arr_media_assignees[] = $media_user->username;
            }
        }

        return new ViewModel(array(
            //'writer' => $this->getWriterTable()->getWriter($id),
            'user'     => $cur_user,
            'form'     => $form,
            'id'       => $id,
            'product'  => $product,
            'media_assignees' => $arr_media_assignees,
        ));
    }

    public function addAction()
    {
        //$cur_user = $this->_authenticateSession(1);
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);

        //handle the form
        $form = new WriterForm();
        $form->get('submit')->setValue('保存');
        $request = $this->getRequest();
        if($request->isPost()){
            //upload start
            $adapter = new FileHttp();
            $adapter->setDestination('public');
            if (!$adapter->receive()) 
            {
                echo implode("\n", $adapter->getMessages());
                //die($adapter->getMessages());
            }
            //upload end
            $writer = new Writer();
            $form->setInputFilter($writer->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $writer->exchangeArray($form->getData());
                $writer->created_by = $cur_user;
                $writer->created_at = $this->_getDateTime();
                $writer->updated_by = $cur_user;
                $writer->updated_at = $this->_getDateTime();
                $this->getWriterTable()->saveWriter($writer);
                $id_writer = $this->getWriterTable()->getId(
                        $writer->created_at,
                        $writer->created_by
                    );
                /*
                //start: handle the assigned media
                $arr_get = $form->getData();
                $str_wrtmedias = trim($arr_get["wrtmedia"]);
                $arr_wrtmedias = explode(";", $str_wrtmedias);
                foreach ($arr_wrtmedias as $wm)
                {
                    $wrtmedia = new Wrtmedia();    
                    $wrtmedia->fk_writer = $id_writer;
                    $enterprise_user = $this->getUserTable()->getUserByName($cur_user);
                    $wrtmedia->fk_enterprise_user = $enterprise_user->id;
                    if($this->getUserTable()->checkUser($wm)) {
                        $media_user = $this->getUserTable()->getUserByName($wm);
                        $wrtmedia->fk_media_user = $media_user->id;                        
                        $wrtmedia->created_by = $cur_user;
                        $wrtmedia->created_at = $this->_getDateTime();
                        $wrtmedia->updated_by = $cur_user;
                        $wrtmedia->updated_at = $this->_getDateTime();
                        $this->getWrtmediaTable()->saveWrtmedia($wrtmedia);
                    }
                    else
                    {
                        continue;
                    }
                }
                //end: handle the assigned media
                */
                return $this->redirect()->toRoute('writer',array(
                    'action'=>'detail',
                    'id'    => $id_writer,
                ));
            }
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'form' => $form,
            'writer' => $this->getWriterTable()->fetchWriterByUser($cur_user),
            'products' => $this->getProductTable()->fetchProductByUser($cur_user),
        ));                
    }

    public function reqlistAction()
    {
        //媒体->自由撰稿人->需求列表
        $arr_type_allowed = array(2, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        return new ViewModel(array(
            'user' => $cur_user,
            'writer' => $this->getWriterTable()->fetchAllDesc(),
            'products' => $this->getProductTable()->fetchAll(),
            'wrtmedia' => $this->getWrtmediaTable()->fetchWrtmediaByUser($cur_user),
        ));
    }

    public function mediaaccAction()
    {
        //媒体->自由撰稿人->需求列表->接受订单
        $arr_type_allowed = array(2);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_writer = (int)$this->params()->fromRoute('id',0);        
        if (!$id_writer) {
            return $this->redirect()->toRoute('writer', array(
                'action' => 'reqlist'
            ));
        }
        $writer = $this->getWriterTable()->getWriter($id_writer);
        $product = $this->getProductTable()->getProduct($writer->fk_product);
        $media_user = $this->getUserTable()->getUserByName($cur_user);
        $enterprise_user = $this->getUserTable()->getUserByName($writer->created_by);

        $wrtmedia = new Wrtmedia();
        $wrtmedia->fk_writer = $id_writer;
        $wrtmedia->fk_media_user = $media_user->id;
        $wrtmedia->fk_enterprise_user = $enterprise_user->id;
        $wrtmedia->created_by = $cur_user;
        $wrtmedia->created_at = $this->_getDateTime();
        $wrtmedia->updated_by = $cur_user;
        $wrtmedia->updated_at = $this->_getDateTime();
        $wrtmedia->fk_wrtmedia_status = 3;//accept the order
        $this->getWrtmediaTable()->saveWrtmedia($wrtmedia);

        return $this->redirect()->toRoute('writer', array(
            'action' => 'reqlist',
        ));

        return new ViewModel(array(
            'user' => $cur_user,
        ));
    }

    public function mediarejAction()
    {
        //媒体->自由撰稿人->需求列表->拒绝订单
        $arr_type_allowed = array(2);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_writer = (int)$this->params()->fromRoute('id',0);        
        if (!$id_writer) {
            return $this->redirect()->toRoute('writer', array(
                'action' => 'reqlist'
            ));
        }

        $writer = $this->getWriterTable()->getWriter($id_writer);
        $product = $this->getProductTable()->getProduct($writer->fk_product);
        $media_user = $this->getUserTable()->getUserByName($cur_user);
        $enterprise_user = $this->getUserTable()->getUserByName($writer->created_by);

        $wrtmedia = new Wrtmedia();
        $wrtmedia->fk_writer = $id_writer;
        $wrtmedia->fk_media_user = $media_user->id;
        $wrtmedia->fk_enterprise_user = $enterprise_user->id;
        $wrtmedia->created_by = $cur_user;
        $wrtmedia->created_at = $this->_getDateTime();
        $wrtmedia->updated_by = $cur_user;
        $wrtmedia->updated_at = $this->_getDateTime();
        $wrtmedia->fk_wrtmedia_status = 2;//reject the order
        $this->getWrtmediaTable()->saveWrtmedia($wrtmedia);

        return $this->redirect()->toRoute('writer', array(
            'action' => 'reqlist',
        ));

        return new ViewModel(array(
            'user' => $cur_user,
        ));
    }

    public function entaccAction()
    {
        //企业->新闻撰稿->企业接受        
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_wrtmedia = (int)$this->params()->fromRoute('id',0);        
        if (!$id_wrtmedia) {
            return $this->redirect()->toRoute('writer', array(
                'action' => 'index'
            ));
        }

        $wrtmedia = $this->getWrtmediaTable()->getWrtmedia($id_wrtmedia);
        $wrtmedia->updated_by = $cur_user;
        $wrtmedia->updated_at = $this->_getDateTime();
        $wrtmedia->fk_wrtmedia_status = 5;//accept the order
        $this->getWrtmediaTable()->saveWrtmedia($wrtmedia);

        return $this->redirect()->toRoute('writer', array(
            'action' => 'detail',
            'id'     => $wrtmedia->fk_writer,
        ));

        return new ViewModel(array(
            'user' => $cur_user,
        ));
    }

    public function entrejAction()
    {
        //企业->新闻撰稿->企业拒绝        
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_wrtmedia = (int)$this->params()->fromRoute('id',0);        
        if (!$id_wrtmedia) {
            return $this->redirect()->toRoute('writer', array(
                'action' => 'index'
            ));
        }

        $wrtmedia = $this->getWrtmediaTable()->getWrtmedia($id_wrtmedia);
        $wrtmedia->updated_by = $cur_user;
        $wrtmedia->updated_at = $this->_getDateTime();
        $wrtmedia->fk_wrtmedia_status = 4;//reject the order
        $this->getWrtmediaTable()->saveWrtmedia($wrtmedia);

        return $this->redirect()->toRoute('writer', array(
            'action' => 'detail',
            'id'     => $wrtmedia->fk_writer,
        ));

        return new ViewModel(array(
            'user' => $cur_user,
        ));
    }

    public function wrtinfoAction()
    {
        //媒体->自由撰稿人->需求列表->查看撰稿订单信息
        $arr_type_allowed = array(2);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_writer = (int)$this->params()->fromRoute('id',0);        
        if (!$id_writer) {
            return $this->redirect()->toRoute('writer', array(
                'action' => 'reqlist'
            ));
        }
        $writer = $this->getWriterTable()->getWriter($id_writer);
        $wrtmedia = $this->getWrtmediaTable()->getWrtmediaByUserAndFkWrt($cur_user, $writer->id_writer);

        return new ViewModel(array(
            'writer' => $writer,
            'user' => $cur_user,
            'id' => $id_writer,
            'product' => $this->getProductTable()->getProduct($writer->fk_product),
            'wrtmedia' => $wrtmedia,
        ));
    }

    public function getWriterTable()
    {
        if (!$this->writerTable) {
	    $sm = $this->getServiceLocator();
	    $this->writerTable = $sm->get('Writer\Model\WriterTable');
        }
        return $this->writerTable;
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

    public function getWrtmediaTable()
    {
        if (!$this->wrtmediaTable) {
            $sm = $this->getServiceLocator();
            $this->wrtmediaTable = $sm->get('Writer\Model\WrtmediaTable');
        }
        return $this->wrtmediaTable;
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
