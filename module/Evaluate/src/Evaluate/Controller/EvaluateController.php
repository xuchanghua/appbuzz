<?php
namespace Evaluate\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Evaluate\Model\Evaluate;          // <-- Add this import
use Evaluate\Form\EvaluateForm;       // <-- Add this import
use Zend\Session\Container as SessionContainer;
use User\Model\User;
use Zend\File\Transfer\Adapter\Http as FileHttp;
use DateTime;

class EvaluateController extends AbstractActionController
{
    protected $evaluateTable;
    protected $userTable;
    protected $productTable;

    public function indexAction()
    {     

    }

    public function neworderAction()
    {
        //$cur_user = $this->_authenticateSession(1);
        $arr_type_allowed = array(1, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        if(!$id_product = (int)$this->params()->fromQuery('id_product', 0))
        {
            $id_product = $this->getRequest()->getPost()->fk_product;
        }
        $product = $this->getProductTable()->getProduct($id_product);
        $evaluate = new Evaluate();
        $evaluate->fk_product = $product->id_product;
        $evaluate->web_link = $product->web_link;
        $evaluate->appstore_link = $product->appstore_link;
        $evaluate->androidmkt_link = $product->androidmkt_link;

        $form = new EvaluateForm();
        $form->bind($evaluate);
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
            $form->setInputFilter($evaluate->getInputFilter());
            $form->setData($request->getPost());
            //die(var_dump($request->getPost()));
            if($form->isValid()){
                $form->getData()->created_by = $cur_user;
                $form->getData()->created_at = $this->_getDateTime();
                $form->getData()->updated_by = $cur_user;
                $form->getData()->updated_at = $this->_getDateTime();
                $form->getData()->fk_product = $id_product;
                $this->getEvaluateTable()->saveEvaluate($form->getData());   
                $id = $this->getEvaluateTable()->getId(
                    $form->getData()->created_at, 
                    $form->getData()->created_by
                );

                return $this->redirect()->toRoute('evaluate',array(
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

    public function detailAction()
    {
        $arr_type_allowed = array(1, 2, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id = (int)$this->params()->fromRoute('id',0);        
        if (!$id) {
            return $this->redirect()->toRoute('evaluate', array(
                'action' => 'index'
            ));
        }
        $evaluate = $this->getEvaluateTable()->getEvaluate($id);


        return new ViewModel(array(
            'evaluate' => $evaluate,
            'user' => $cur_user,
            'id' => $id,
            'product' => $this->getProductTable()->getProduct($evaluate->fk_product),
        ));
    }    

    public function editAction()
    {
        //$cur_user = $this->_authenticateSession(1);
        $arr_type_allowed = array(1, 2, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id = (int)$this->params()->fromRoute('id',0);
        if(!$id){
            return $this->redirect()->toRoute('evaluate', array(
                'action' => 'index',
            ));
        }
        $evaluate = $this->getEvaluateTable()->getEvaluate($id);
        $product = $this->getProductTable()->getProduct($evaluate->fk_product);
        $eva_created_by = $evaluate->created_by;
        $eva_created_at = $evaluate->created_at;
        $form = new EvaluateForm();
        $form->bind($evaluate);
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
            $form->setInputFilter($evaluate->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $form->getData()->created_by = $eva_created_by;
                $form->getData()->created_at = $eva_created_at;
                $form->getData()->updated_by = $cur_user;
                $form->getData()->updated_at = $this->_getDateTime();
                $this->getEvaluateTable()->saveEvaluate($form->getData());

                return $this->redirect()->toRoute('evaluate',array(
                    'action' => 'detail',
                    'id'     => $id,
                ));
            }
        }

        return new ViewModel(array(
            //'evaluate' => $this->getEvaluateTable()->getEvaluate($id),
            'user'     => $cur_user,
            'form'     => $form,
            'id'       => $id,
            'product'  => $product,
        ));
    }

    public function getEvaluateTable()
    {
        if (!$this->evaluateTable) {
	    $sm = $this->getServiceLocator();
	    $this->evaluateTable = $sm->get('Evaluate\Model\EvaluateTable');
        }
        return $this->evaluateTable;
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
