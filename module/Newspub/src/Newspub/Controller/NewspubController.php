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

    public function indexAction()
    {     
        //Authenticate the user information from the session
        $cur_user = $this->_authenticateSession(1);

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
        $cur_user = $this->_authenticateSession(1);

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
        ));        
    }

    public function detailAction()
    {
        $cur_user = $this->_authenticateSession(1);

        $id = (int)$this->params()->fromRoute('id',0);        
        if (!$id) {
            return $this->redirect()->toRoute('newspub', array(
                'action' => 'index'
            ));
        }

        return new ViewModel(array(
            'np' => $this->getNewspubTable()->getNewspub($id),
            'user' => $cur_user,
            'id' => $id,
        ));
    }

    public function editAction()
    {
        $cur_user = $this->_authenticateSession(1);

        $id = (int)$this->params()->fromRoute('id',0);        
        if (!$id) {
            return $this->redirect()->toRoute('newspub', array(
                'action' => 'index',
            ));
        }
        $newspub = $this->getNewspubTable()->getNewspub($id);
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
            'np'   => $this->getNewspubTable()->getNewspub($id),
            'user' => $cur_user,
            'form' => $form,
            'id'   => $id,
        ));
    }

    public function adminAction()
    {
        //authenticate the admin user:
        $cur_user = $this->_authenticateSession(3);


        return new ViewModel(array(
            'user' => $cur_user,
            'newspubs' => $this->getNewspubTable()->fetchAll(),
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
