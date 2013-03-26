<?php
namespace Newspub\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Newspub\Model\Newspub;          
use Newspub\Form\NewspubForm;       
use Zend\Session\Container as SessionContainer;
use User\Model\User;
use Zend\File\Transfer\Adapter\Http as FileHttp;
use DateTime;

class NewspubController extends AbstractActionController
{
    protected $userTable;
    protected $newspubTable;
    protected $productTable;

    public function indexAction()
    {     
        //Authenticate the user information from the session
        //$cur_user = $this->_authenticateSession(1);
        $arr_type_allowed = array(1, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        $request = $this->getRequest();
        $keyword = trim($request->getQuery(''));
        $page = intval($request->getQuery('page',1));
        $paginator = $this->getNewspubTable()->getPaginator($keyword, $page, 5, 1, $cur_user);
        $view = new ViewModel(array(
            'user' => $cur_user,
            'newspub' => $this->getNewspubTable()->getNewspubByUser($cur_user),
            )
        );
        $view->setVariable('paginator', $paginator);
        return $view;
        /*
        return new ViewModel(array(
            'user' => $cur_user,
            'newspub' => $this->getNewspubTable()->getNewspubByUser($cur_user),
            ));    */    
    }

    public function addAction()
    {
        //$cur_user = $this->_authenticateSession(1);
        $arr_type_allowed = array(1, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        //handle the form
        $form = new NewspubForm();
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
            $newspub = new Newspub();
            $form->setInputFilter($newspub->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $newspub->exchangeArray($form->getData());
                $newspub->created_by = $cur_user;
                $newspub->created_at = $this->_getDateTime();
                $newspub->updated_by = $cur_user;
                $newspub->updated_at = $this->_getDateTime();
                $newspub->fk_newspub_status = 1;
                $this->getNewspubTable()->saveNewspub($newspub);
                
                return $this->redirect()->toRoute('newspub',array(
                    'action'=>'detail',
                    'id'    => $newspub->id_newspub,
                ));
            }
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'form' => $form,
            'newspub' => $this->getNewspubTable()->getNewspubByUser($cur_user),
            'products' => $this->getProductTable()->fetchProductByUser($cur_user),
        ));        
    }

    public function detailAction()
    {
        //$cur_user = $this->_authenticateSession(1);
        $arr_type_allowed = array(1, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id = (int)$this->params()->fromRoute('id',0);        
        if (!$id) {
            return $this->redirect()->toRoute('newspub', array(
                'action' => 'index'
            ));
        }
        $np = $this->getNewspubTable()->getNewspub($id);

        return new ViewModel(array(
            'np' => $np,
            'user' => $cur_user,
            'id' => $id,
            'product' => $this->getProductTable()->getProduct($np->fk_product),
        ));
    }

    public function editAction()
    {
        //$cur_user = $this->_authenticateSession(1);
        $arr_type_allowed = array(1, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id = (int)$this->params()->fromRoute('id',0);        
        if (!$id) {
            return $this->redirect()->toRoute('newspub', array(
                'action' => 'index',
            ));
        }
        $newspub = $this->getNewspubTable()->getNewspub($id);
        $product = $this->getProductTable()->getProduct($newspub->fk_product);
        $np_created_by = $newspub->created_by;
        $np_created_at = $newspub->created_at;
        $form = new NewspubForm();
        $form->bind($newspub);
        $form->get('submit')->setAttribute('value','保存');

        $request = $this->getRequest();
        if ($request->isPost()){
            //upload start
            $adapter = new FileHttp();
            $adapter->setDestination('public');
            if (!$adapter->receive()) 
            {
                echo implode("\n", $adapter->getMessages());
                //die($adapter->getMessages());
            }
            //upload end
            $form->setInputFilter($newspub->getInputFilter());
            $form->setData($request->getPost());
            //die(var_dump($request->getPost()));
            if($form->isValid()){
                $form->getData()->created_by = $np_created_by;
                $form->getData()->created_at = $np_created_at;
                $form->getData()->updated_by = $cur_user;
                $form->getData()->updated_at = $this->_getDateTime();
                $form->getData()->fk_newspub_status = 1;
                $this->getNewspubTable()->saveNewspub($form->getData());   

                return $this->redirect()->toRoute('newspub',array(
                    'action'=>'detail',
                    'id'    => $id,
                ));
            }
        }

        return new ViewModel(array(
            'np'      => $this->getNewspubTable()->getNewspub($id),
            'user'    => $cur_user,
            'form'    => $form,
            'id'      => $id,
            'product' => $product,
        ));
    }

    public function adminAction()
    {
        //authenticate the admin user:
        //$cur_user = $this->_authenticateSession(3);
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);


        return new ViewModel(array(
            'user' => $cur_user,
            'newspubs' => $this->getNewspubTable()->fetchAllDesc(),
        ));
    }

    public function neworderAction()
    {
        //$cur_user = $this->_authenticateSession(1);
        $arr_type_allowed = array(1, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        if(!$id_product = (int)$this->params()->fromQuery('id_product',0))
        {
            $id_product = $this->getRequest()->getPost()->fk_product;
        }
        //echo "id_product = ".$id_product;
        $product = $this->getProductTable()->getProduct($id_product);
        $newspub = new Newspub();
        $newspub->fk_product = $product->id_product;
        $newspub->download_link = $product->web_link;
        $newspub->appstore_links = $product->appstore_link;
        $newspub->androidmkt_link = $product->androidmkt_link;        
        //die(var_dump($newspub));

        $form = new NewspubForm();
        $form->bind($newspub);
        $form->get('submit')->setAttribute('value','保存');

        $request = $this->getRequest();
        if ($request->isPost()){
            //upload start
            $adapter = new FileHttp();
            $adapter->setDestination('public');
            if (!$adapter->receive()) 
            {
                echo implode("\n", $adapter->getMessages());
                //die($adapter->getMessages());
            }
            //upload end
            $form->setInputFilter($newspub->getInputFilter());
            $form->setData($request->getPost());
            //die(var_dump($request->getPost()));
            if($form->isValid()){
                $form->getData()->created_by = $cur_user;
                $form->getData()->created_at = $this->_getDateTime();
                $form->getData()->updated_by = $cur_user;
                $form->getData()->updated_at = $this->_getDateTime();
                $form->getData()->fk_newspub_status = 1;
                $form->getData()->fk_product = $id_product;
                $this->getNewspubTable()->saveNewspub($form->getData());   
                $id = $this->getNewspubTable()->getId(
                    $form->getData()->created_at, 
                    $form->getData()->created_by
                );
                return $this->redirect()->toRoute('newspub',array(
                    'action'=>'detail',
                    'id'    => $id,
                ));
            }
        }

        return new ViewModel(array(
            'product' => $product,
            'user' => $cur_user,
            'form' => $form,
        ));
    }

    public function paidAction()
    {
        //管理员->新闻发布->确认付款
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_newspub = (int)$this->params()->fromRoute('id',0);        
        if (!$id_newspub) {
            return $this->redirect()->toRoute('newspub', array(
                'action' => 'admin'
            ));
        }

        $newspub = $this->getNewspubTable()->getNewspub($id_newspub);
        $newspub->fk_newspub_status = 2;
        $newspub->updated_by = $cur_user;
        $newspub->updated_at = $this->_getDateTime();
        $this->getNewspubTable()->saveNewspub($newspub);

        return $this->redirect()->toRoute('newspub', array(
            'action' => 'admin',
        ));

        return new ViewModel(array(
            'user' => $cur_user,
        ));
    }

    public function completedAction()
    {
        //管理员->新闻发布->确认付款
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_newspub = (int)$this->params()->fromRoute('id',0);        
        if (!$id_newspub) {
            return $this->redirect()->toRoute('newspub', array(
                'action' => 'admin'
            ));
        }

        $newspub = $this->getNewspubTable()->getNewspub($id_newspub);
        $newspub->fk_newspub_status = 3;
        $newspub->updated_by = $cur_user;
        $newspub->updated_at = $this->_getDateTime();
        $this->getNewspubTable()->saveNewspub($newspub);

        return $this->redirect()->toRoute('newspub', array(
            'action' => 'admin',
        ));

        return new ViewModel(array(
            'user' => $cur_user,
        ));
    }

    public function uploadAction()
    {
        if ($this->getRequest()->isPost()) 
        {
            $adapter = new FileHttp();
            $adapter->setDestination('public');
            if (!$adapter->receive()) 
            {
                echo implode("\n", $adapter->getMessages());
            }
        }
    }

    public function ajaxAction()
    {
    }

    public function getNewspubTable()
    {
        if (!$this->newspubTable) {
	    $sm = $this->getServiceLocator();
	    $this->newspubTable = $sm->get('Newspub\Model\NewspubTable');
        }
        return $this->newspubTable;
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
