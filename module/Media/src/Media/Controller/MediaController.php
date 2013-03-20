<?php
namespace Media\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container as SessionContainer;
use User\Model\User;
use Media\Model\Media;
use Media\Form\MediaForm;

class MediaController extends AbstractActionController
{
    protected $userTable;
    protected $mediaTable;

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
            'fk_media' => $fk_media,
            'media' => (isset($fk_media))? $this->getMediaTable()->getMedia($fk_media) : null,
            'form' => $form,
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
        $form = new MediaForm();
        $form->bind($media);
        $form->get('submit')->setAttribute('value','保存');

        $request = $this->getRequest();
        if ($request->isPost()){
            $form->setInputFilter($media->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
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
}
