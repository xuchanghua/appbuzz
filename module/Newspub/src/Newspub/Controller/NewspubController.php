<?php
namespace Newspub\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Newspub\Model\Newspub;          
use Newspub\Form\NewspubForm;       
use Zend\Session\Container as SessionContainer;
use User\Model\User;

class NewspubController extends AbstractActionController
{
    protected $userTable;
    protected $newspubTable;

    public function indexAction()
    {     
        //Authenticate the user information from the session
        $cur_user = $this->_authenticateSession();

        //handle the form
        $form = new NewspubForm();
        $form->get('submit')->setValue('保存');
        $request = $this->getRequest();
        if($request->isPost()){
            $newspub = new Newspub();
            $form->setInputFilter($newspub->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $newspub->exchangeArray($form->getData());
                $newspub->created_by = $cur_user;
                $newspub->created_at = time();
                $newspub->updated_by = $cur_user;
                $newspub->updated_at = time();
                $newspub->fk_newspub_status = 1;
                $this->getNewspubTable()->saveNewspub($newspub);
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
        $cur_user = $this->_authenticateSession();

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
        $cur_user = $this->_authenticateSession();

        $id = (int)$this->params()->fromRoute('id',0);        
        if (!$id) {
            return $this->redirect()->toRoute('newspub', array(
                'action' => 'index',
            ));
        }
        $newspub = $this->getNewspubTable()->getNewspub($id);

        $form = new NewspubForm();
        $form->bind($newspub);
        $form->get('submit')->setAttribute('value','保存');

        $request = $this->getRequest();
        if ($request->isPost()){
            $form->setInputFilter($newspub->getInputFilter());
            $form->setData($request->getPost());

            if($form->isValid()){             
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
        if((!$this->getUserTable()->checkUser($user))||($this->getUserTable()->getUser($user)->fk_user_type != $type))
        {
            echo "<a href='/'>Back</a></br>";
            die("The user was not exist.");
        }
        //check if the username and the password are corresponded:
        if($this->getUserTable()->getUser($user)->password != $pass)
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
        if($this->_authorizeUser(1, $username, $password))
        {
            echo "Welcome, ".$username;
            return $username;
        }
    }
}
