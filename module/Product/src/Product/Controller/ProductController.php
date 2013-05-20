<?php
namespace Product\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Product\Model\Product;          // <-- Add this import
use Product\Form\ProductForm;       // <-- Add this import
use Attachment\Model\Barcode;
use Attachment\Model\Appicon;
use Zend\File\Transfer\Adapter\Http as FileHttp;
use Zend\Validator\File\Size as FileSize;
use Zend\Validator\File\Extension as FileExt;
use Zend\Session\Container as SessionContainer;
use User\Model\User;
use DateTime;

class ProductController extends AbstractActionController
{
    protected $productTable;
    protected $userTable;
    protected $barcodeTable;
    protected $appiconTable;

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
                $id_product = $this->getProductTable()->getId(
                        $product->created_at, 
                        $product->created_by
                    );

                //upload start
                //1. barcode
                $file = $this->params()->fromFiles('barcode');
                $max = 4000000;//单位比特
                $sizeObj = new FileSize(array("max"=>$max));
                $extObj = new FileExt(array("jpg","gif","png"));
                $adapter = new FileHttp();
                $adapter->setValidators(array($sizeObj, $extObj),$file['name']);
                if(!$adapter->isValid()){
                    echo implode("\n",$dataError = $adapter->getMessages());
                }else{
                    //check if the path exists
                    //path format: /public/upload/user_name/module_name/id_module_name/
                    $path_0    = 'public/upload/';
                    $path_1    = $path_0.$cur_user.'/';
                    $path_2    = $path_1.'product/';
                    $path_full = $path_2.$id_product.'/';
                    if(!is_dir($path_1))
                    {
                        mkdir($path_1);
                    }
                    if(!is_dir($path_2))
                    {
                        mkdir($path_2);
                    }
                    if(!is_dir($path_full))
                    {
                        mkdir($path_full);
                    }
                    $adapter->setDestination($path_full);
                    if(!$adapter->receive($file['name'])){
                        echo implode("\n", $adapter->getMessages());
                    }
                    else
                    {
                        //create a record in the table 'barcode'
                        $barcode = new Barcode();
                        $barcode->filename = $file['name'];
                        $barcode->path = $path_full;
                        $barcode->created_by = $cur_user;
                        $barcode->created_at = $this->_getDateTime();
                        $this->getBarcodeTable()->saveBarcode($barcode);
                        $id_barcode = $this->getBarcodeTable()->getId($barcode->created_at, $barcode->created_by);
                        //md5() the file name
                        //rename($file['name'], md5($file['name']));
                    }
                }
                //2. icon
                $icon = $this->params()->fromFiles('fk_appicon');
                $adapter = new FileHttp();
                /*$adapter->setValidators(array($sizeObj, $extObj),$icon['name']);
                if(!$adapter->isValid()){
                    echo implode("\n",$dataError = $adapter->getMessages());
                }else{*/
                    //check if the path exists
                    //path format: /public/upload/user_name/module_name/id_module_name/
                    $path_0    = 'public/upload/';
                    $path_1    = $path_0.$cur_user.'/';
                    $path_2    = $path_1.'product/';
                    $path_3    = $path_2.$id_product.'/';
                    $path_full = $path_3.'icon/';
                    if(!is_dir($path_1))
                    {
                        mkdir($path_1);
                    }
                    if(!is_dir($path_2))
                    {
                        mkdir($path_2);
                    }
                    if(!is_dir($path_3))
                    {
                        mkdir($path_3);
                    }
                    if(!is_dir($path_full))
                    {
                        mkdir($path_full);
                    }
                    $adapter->setDestination($path_full);
                    if(!$adapter->receive($icon['name'])){
                        echo implode("\n", $adapter->getMessages());
                    }
                    else
                    {
                        //create a record in the table 'appicon'
                        $appicon = new Appicon();
                        $appicon->filename = $icon['name'];
                        $appicon->path = $path_full;
                        $appicon->created_by = $cur_user;
                        $appicon->created_at = $this->_getDateTime();
                        $this->getAppiconTable()->saveAppicon($appicon);
                        $id_appicon = $this->getAppiconTable()->getId($appicon->created_at, $appicon->created_by);
                        //md5() the file name
                        //rename($file['name'], md5($file['name']));
                    }
                //}
                //upload end

                $product2 = $this->getProductTable()->getProduct($id_product);
                $product2->barcode = $id_barcode;
                $product2->fk_appicon = $id_appicon;
                $this->getProductTable()->saveProduct($product2);

                return $this->redirect()->toRoute('product',array(
                    'action' => 'detail',
                    'id'     => $id_product,
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
        $product = $this->getProductTable()->getProduct($id);
        if($product->barcode){
            $barcode = $this->getBarcodeTable()->getBarcode($product->barcode);
            //$barcode_path = '/upload/'.$product->created_by.'/product/'.$id.'/'.$barcode->filename;
            $barcode_path = substr($barcode->path.$barcode->filename, 6);
        }else{
            $barcode_path = '';
        }
        if($product->fk_appicon){
            $appicon = $this->getAppiconTable()->getAppicon($product->fk_appicon);
            $appicon_path = substr($appicon->path.$appicon->filename, 6);
        }else{
            $appicon_path = '';
        }
        return new ViewModel(array(
            'user' => $cur_user,
            'user_type' => $this->getUserTable()->getUserByName($cur_user)->fk_user_type,
            'product' => $this->getProductTable()->getProduct($id),
            'id' => $id,
            'barcode_path' => $barcode_path,
            'appicon_path' => $appicon_path,
        ));
    }

    public function editAction()
    {
        //$cur_user = $this->_authenticateSession(1);
        $arr_type_allowed = array(1, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_product = (int)$this->params()->fromRoute('id',0);
        if(!$id_product){
            return $this->redirect()->toRoute('application', array(
                'action' => 'index'
            ));
        }
        $product = $this->getProductTable()->getProduct($id_product);
        if($product->barcode){
            $barcode = $this->getBarcodeTable()->getBarcode($product->barcode);
            //$barcode_path = '/upload/'.$cur_user.'/product/'.$id_product.'/'.$barcode->filename;
            $barcode_path = substr($barcode->path.$barcode->filename, 6);
        }else{
            $barcode_path = '';
        }
        if($product->fk_appicon){
            $appicon = $this->getAppiconTable()->getAppicon($product->fk_appicon);
            $appicon_path = substr($appicon->path.$appicon->filename, 6);
        }else{
            $appicon_path = '';
        }
        $product_created_by = $product->created_by;
        $product_created_at = $product->created_at;
        $product_barcode = $product->barcode;
        $product_appicon = $product->fk_appicon;
        $form = new ProductForm();
        $form->bind($product);
        $form->get('submit')->setAttribute('value','保存');

        $request = $this->getRequest();
        if($request->isPost()){
            //upload start
            //1. barcode
            $file = $this->params()->fromFiles('barcode');
            if(!$file['name'])
            {
                //if the barcode is not pick up:
                //skip the upload section
            }
            else
            {
                $max = 4000000;//单位比特
                $sizeObj = new FileSize(array("max"=>$max));
                $extObj = new FileExt(array("jpg","gif","png"));
                $adapter = new FileHttp();
                $adapter->setValidators(array($sizeObj, $extObj),$file['name']);
                if(!$adapter->isValid()){
                    echo implode("\n",$dataError = $adapter->getMessages());
                }else{
                    //check if the path exists
                    //path format: /public/upload/user_name/module_name/id_module_name/
                    $path_0    = 'public/upload/';
                    $path_1    = $path_0.$cur_user.'/';
                    $path_2    = $path_1.'product/';
                    $path_full = $path_2.$id_product.'/';
                    if(!is_dir($path_1))
                    {
                        mkdir($path_1);
                    }
                    if(!is_dir($path_2))
                    {
                        mkdir($path_2);
                    }
                    if(!is_dir($path_full))
                    {
                        mkdir($path_full);
                    }
                    $adapter->setDestination($path_full);
                    if(!$adapter->receive($file['name'])){
                        echo implode("\n", $adapter->getMessages());
                    }
                    else
                    {
                        //create a record in the table 'barcode'
                        $barcode = new Barcode();
                        $barcode->filename = $file['name'];
                        $barcode->path = $path_full;
                        $barcode->created_by = $cur_user;
                        $barcode->created_at = $this->_getDateTime();
                        $this->getBarcodeTable()->saveBarcode($barcode);
                        $id_barcode = $this->getBarcodeTable()->getId($barcode->created_at, $barcode->created_by);
                        //md5() the file name
                        //rename($file['name'], md5($file['name']));
                    }
                }
            }
            //2. appicon
            $icon = $this->params()->fromFiles('fk_appicon');
            if(!$icon['name'])
            {
                //if the barcode is not pick up:
                //skip the upload section
            }
            else
            {
                $adapter = new FileHttp();
                    $path_0    = 'public/upload/';
                    $path_1    = $path_0.$cur_user.'/';
                    $path_2    = $path_1.'product/';
                    $path_3    = $path_2.$id_product.'/';
                    $path_full = $path_3.'icon/';
                    if(!is_dir($path_1))
                    {
                        mkdir($path_1);
                    }
                    if(!is_dir($path_2))
                    {
                        mkdir($path_2);
                    }
                    if(!is_dir($path_3))
                    {
                        mkdir($path_3);
                    }
                    if(!is_dir($path_full))
                    {
                        mkdir($path_full);
                    }
                    $adapter->setDestination($path_full);
                    if(!$adapter->receive($icon['name'])){
                        echo implode("\n", $adapter->getMessages());
                    }
                    else
                    {
                        //create a record in the table 'appicon'
                        $appicon = new Appicon();
                        $appicon->filename = $icon['name'];
                        $appicon->path = $path_full;
                        $appicon->created_by = $cur_user;
                        $appicon->created_at = $this->_getDateTime();
                        $this->getAppiconTable()->saveAppicon($appicon);
                        $id_appicon = $this->getAppiconTable()->getId($appicon->created_at, $appicon->created_by);
                        //md5() the file name
                        //rename($file['name'], md5($file['name']));
                    }
                
            }
            //upload end

            $form->setInputFilter($product->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $form->getData()->created_by = $product_created_by;
                $form->getData()->created_at = $product_created_at;
                $form->getData()->updated_by = $cur_user;
                $form->getData()->updated_at = $this->_getDateTime();
                if(isset($id_barcode)){
                    $form->getData()->barcode = $id_barcode;
                }else{
                    $form->getData()->barcode = $product_barcode;
                }
                if(isset($id_appicon)){
                    $form->getData()->fk_appicon = $id_appicon;
                }else{
                    $form->getData()->fk_appicon = $product_appicon;
                }
                $this->getProductTable()->saveProduct($form->getData());

                return $this->redirect()->toRoute('product', array(
                    'action' => 'detail',
                    'id'     => $id_product,
                ));
            }
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'user_type' => $this->getUserTable()->getUserByName($cur_user)->fk_user_type,
            'form' => $form,
            'id' => $id_product,
            'barcode_path' => $barcode_path,
            'appicon_path' => $appicon_path,
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

    public function getBarcodeTable()
    {
        if (!$this->barcodeTable) {
        $sm = $this->getServiceLocator();
        $this->barcodeTable = $sm->get('Attachment\Model\BarcodeTable');
        }
        return $this->barcodeTable;
    }

    public function getAppiconTable()
    {
        if (!$this->appiconTable) {
        $sm = $this->getServiceLocator();
        $this->appiconTable = $sm->get('Attachment\Model\AppiconTable');
        }
        return $this->appiconTable;
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
