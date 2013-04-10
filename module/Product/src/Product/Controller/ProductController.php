<?php
namespace Product\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Product\Model\Product;          // <-- Add this import
use Product\Form\ProductForm;       // <-- Add this import
use Zend\Session\Container as SessionContainer;
use User\Model\User;
use Zend\File\Transfer\Adapter\Http as FileHttp;
use DateTime;

class ProductController extends AbstractActionController
{
    protected $productTable;
    protected $userTable;

    public function indexAction()
    {             
        //Authenticate the user information from the session
        //$cur_user = $this->_authenticateSession(1);
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);
        $products = $this->getProductTable()->fetchProductByUser($cur_user);
/*
        foreach ($products as $product) {
            //$product->test_new_var = '############';
            var_dump($product);
            echo "<br>";
        };
*/
        return new ViewModel(array(
            'user' => $cur_user,
            'products' =>$products,
            ));
    }

    public function adminAction()
    {
        //only admin user can see all the apps
        //$cur_user = $this->_authenticateSession(3);
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);


        return new ViewModel(array(
            'user' => $cur_user,
            'products' => $this->getProductTable()->fetchAll(),
        ));
    }

    public function addAction()
    {
        //$cur_user = $this->_authenticateSession(1);
        $arr_type_allowed = array(1, 3);
        $cur_user = $this->_auth($arr_type_allowed);        

        $form = new ProductForm();
        $form->get('submit')->setValue('保存');
        $request = $this->getRequest();
        if($request->isPost()){
            $product = new Product();
            $form->setInputFilter($product->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $product->exchangeArray($form->getData());
                $product->created_by = $cur_user;
                $product->created_at = $this->_getDateTime();
                $product->updated_by = $cur_user;
                $product->updated_at = $this->_getDateTime();
                $this->getProductTable()->saveProduct($product);

                return $this->redirect()->toRoute('product',array(
                    'action' => 'detail',
                    'id'     => $product->id_product,
                ));
            }
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'user_type' => $this->getUserTable()->getUserByName($cur_user)->fk_user_type,
            'form' => $form,
        ));
    }

    public function detailAction()
    {
        //$cur_user = $this->_authenticateSession(1);
        $arr_type_allowed = array(1, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id = (int)$this->params()->fromRoute('id',0);
        if(!$id){
            return $this->redirect()->toRoute('application',array(
                'action' => 'index'
            ));
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'user_type' => $this->getUserTable()->getUserByName($cur_user)->fk_user_type,
            'product' => $this->getProductTable()->getProduct($id),
            'id' => $id,
        ));
    }

    public function editAction()
    {
        //$cur_user = $this->_authenticateSession(1);
        $arr_type_allowed = array(1, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id = (int)$this->params()->fromRoute('id',0);
        if(!$id){
            return $this->redirect()->toRoute('application', array(
                'action' => 'index'
            ));
        }
        $product = $this->getProductTable()->getProduct($id);
        $product_created_by = $product->created_by;
        $product_created_at = $product->created_at;
        $form = new ProductForm();
        $form->bind($product);
        $form->get('submit')->setAttribute('value','保存');

        $request = $this->getRequest();
        if($request->isPost()){
            $form->setInputFilter($product->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $form->getData()->created_by = $product_created_by;
                $form->getData()->created_at = $product_created_at;
                $form->getData()->updated_by = $cur_user;
                $form->getData()->updated_at = $this->_getDateTime();
                $this->getProductTable()->saveProduct($form->getData());

                return $this->redirect()->toRoute('product', array(
                    'action' => 'detail',
                    'id'     => $id,
                ));
            }
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'user_type' => $this->getUserTable()->getUserByName($cur_user)->fk_user_type,
            'form' => $form,
            'id' => $id,
        ));
    }

    public function getProductTable()
    {
        if (!$this->productTable) {
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
