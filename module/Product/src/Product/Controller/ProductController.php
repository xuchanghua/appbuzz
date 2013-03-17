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

    }

    public function adminAction()
    {
        //only admin user can see all the apps
        $cur_user = $this->_authenticateSession(3);


        return new ViewModel(array(
            'user' => $cur_user,
            'products' => $this->getProductTable()->fetchAll(),
        ));
    }

    public function addAction()
    {
        $cur_user = $this->_authenticateSession(1);

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
            'form' => $form,

        ));
    }

    public function detailAction()
    {
        $cur_user = $this->_authenticateSession(1);

        $id = (int)$this->params()->fromRoute('id',0);
        if(!$id){
            return $this->redirect()->toRoute('application',array(
                'action' => 'index'
            ));
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'product' => $this->getProductTable()->getProduct($id),
            'id' => $id,
        ));
    }

    public function editAction()
    {
        $cur_user = $this->_authenticateSession(1);

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
