<?php
namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Header\Cookie;
use Zend\Http\PhpEnvironment\Response;
use Zend\Session\Container as SessionContainer;
use User\Model\User;
use User\Form\UserForm;

class UserController extends AbstractActionController
{
    protected $userTable;

    public function indexAction()
    {
    }

    public function checkenterpriseuserAction()
    {
        $postUser = $_POST['username'];
        $postPass = $_POST['password'];
        //Authorize the user:
        $this->_authorizeUser('1', $postUser, $postPass);
        //Set Session for the authorized user:
        $this->session = new SessionContainer('userinfo');
        $this->session->username = $postUser;
        $this->session->password = $postPass;
        $this->session->usertype = 1;
        //Set Cookies for the authorized user:
        setcookie(
            "username",
            $postUser,
            time() + ( 7 * 24 * 3600),
            '/',
            'local.appbuzz'
            );
        setcookie(
            "password",
            $postUser,
            time() + ( 7 * 24 * 3600),
            '/',
            'local.appbuzz'
            );
        //redirect to the enterprise index page
        $this->redirect()->toRoute('enterprise');
    }

    public function checkmediauserAction()
    {
        $postUser = $_POST['username'];
        $postPass = $_POST['password'];
        //Authorize the user:
        $this->_authorizeUser('2', $postUser, $postPass);
        //Set Session for the authorized user:
        $this->session = new SessionContainer('userinfo');
        $this->session->username = $postUser;
        $this->session->password = $postPass;
        $this->session->usertype = 2;
        //Set Cookies for the authorized user:
        setcookie(
            "username",
            $postUser,
            time() + ( 7 * 24 * 3600),
            '/',
            'local.appbuzz'
            );
        setcookie(
            "password",
            $postUser,
            time() + ( 7 * 24 * 3600),
            '/',
            'local.appbuzz'
            );
        //redirect to the media index page
        $this->redirect()->toRoute('media');
    }

    public function checkadminuserAction()
    {        
        $postUser = $_POST['username'];
        $postPass = $_POST['password'];
        //Authorize the user:
        $this->_authorizeUser('3', $postUser, $postPass);
        //Set Session for the authorized user:
        $this->session = new SessionContainer('userinfo');
        $this->session->username = $postUser;
        $this->session->password = $postPass;
        $this->session->usertype = 3;
        //Set Cookies for the authorized user:
        setcookie(
            "username",
            $postUser,
            time() + ( 7 * 24 * 3600),
            '/',
            'local.appbuzz'
            );
        setcookie(
            "password",
            $postUser,
            time() + ( 7 * 24 * 3600),
            '/',
            'local.appbuzz'
            );
        //redirect to the enterprise index page
        $this->redirect()->toRoute('admin');
    }

    public function signupAction()
    {
        $form = new UserForm();
        $form->get('submit')->setValue('立即注册');
        $request = $this->getRequest();
        if($request->isPost()){
            $user = new User();
            $form->setInputFilter($user->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $user->exchangeArray($form->getData());
                if($user->password == $user->confirmpassword)
                {
                    $this->getUserTable()->saveUser($user);
                }
                else
                {
                    echo "<a href='/user/signup'>Back</a></br>";
                    die("Password and confirm password must be corresponded!");
                }
                //Set Session for the authorized user:
                $this->session = new SessionContainer('userinfo');
                $this->session->username = $user->username;
                $this->session->password = $user->password;
                $this->session->usertype = $user->fk_user_type;
                switch($user->fk_user_type)
                {
                    case 1:
                        return $this->redirect()->toRoute('enterprise');
                        break;
                    case 2:
                        return $this->redirect()->toRoute('media');
                        break;
                    case 3:
                        return $this->redirect()->toRoute('admin');
                        break;
                    default:
                        die("no such user type!");
                }
                //return $this->redirect()->toRoute('enterprise');
            }
        }
        return array('form' => $form);
    }

    public function addAction()
    {
        //用户管理->创建用户 （可以创建企业、媒体、管理用户）        
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);

        $form = new UserForm();
        $form->get('submit')->setValue('创建用户');
        $request = $this->getRequest();
        if($request->isPost()){
            $user = new User();
            $form->setInputFilter($user->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $user->exchangeArray($form->getData());
                if($user->password == $user->confirmpassword)
                {
                    $this->getUserTable()->saveUser($user);
                }
                else
                {
                    echo "<a href='/user/add'>Back</a></br>";
                    die("Password and confirm password must be corresponded!");
                }

                return $this->redirect()->toRoute('user', array(
                    'action' => 'admin',
                ));
            }
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'form' => $form,
        ));
    }    

    public function logoutAction()
    {
        $this->session = new SessionContainer('userinfo');
        unset($this->session->username);
        unset($this->session->password); 
        unset($this->session->usertype);   
        return $this->redirect()->toRoute('home');
    }

    public function adminAction()
    {
        //用户管理->所有用户
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);



        return new ViewModel(array(
            'user' => $cur_user,
            'allusers' => $this->getUserTable()->fetchAllDesc(),
        ));
    }

    public function detailAction()
    {        
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);
        $id = (int)$this->params()->fromRoute('id', 0);
        if(!$id) {
            return $this->redirect()->toRoute('user',array(
                'action' => 'admin'
            ));
        }
        $target_user = $this->getUserTable()->getUser($id);

        return new ViewModel(array(
            'user' => $cur_user,
            'target_user' => $target_user,
        ));
    }

    public function editAction()
    {        
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id = (int)$this->params()->fromRoute('id', 0);
        if(!$id) {
            return $this->redirect()->toRoute('user',array(
                'action' => 'admin'
            ));
        }

        $target_user = $this->getUserTable()->getUser($id);
        //die(var_dump($target_user));
        $form = new UserForm();
        //die(var_dump($form));
        $form->bind($target_user);
        $form->get('submit')->setAttribute('value','保存');

        $request = $this->getRequest();
        if($request->isPost()){
            $form->setInputFilter($target_user->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $this->getUserTable()->saveUser($form->getData());
                return $this->redirect()->toRoute('user', array(
                    'action' => 'detail',
                    'id'     => $target_user->id,
                ));
            }
        }
        return new ViewModel(array(
            'user' => $cur_user,
            'target_user' => $target_user,
            'form' => $form,
        ));
    }

    public function deleteAction()
    {        
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id = (int)$this->params()->fromRoute('id', 0);
        if(!$id) {
            return $this->redirect()->toRoute('user',array(
                'action' => 'admin'
            ));
        }

        $this->getUserTable()->deleteUser($id);
        return $this->redirect()->toRoute('user',array(
                'action' => 'admin'
            ));
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
}
