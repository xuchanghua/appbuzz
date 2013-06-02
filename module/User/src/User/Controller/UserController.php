<?php
namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Header\Cookie;
use Zend\Http\PhpEnvironment\Response;
use Zend\Session\Container as SessionContainer;
use User\Model\User;
use User\Form\UserForm;
use User\Form\changepasswordForm;
use User\Form\ForgotpasswordForm;
use Credit\Model\Credit;
use DateTime;
use Zend\Mail\Message;
use Zend\Mail\Transport\Sendmail as SendmailTransport;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\Mail\Transport\SmtpOptions;

class UserController extends AbstractActionController
{
    protected $userTable;
    protected $creditTable;
    protected $enterpriseTable;
    protected $mediaTable;
    protected $creditlogTable;

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
        //$this->_authorizeUser('3', $postUser, $postPass);
        if((!$postUser)||(!$postPass))
        {
            echo "<a href='/'>Back</a></br>";
            die("Username or Password cannot be empty!");
        }
        if((!$this->getUserTable()->checkUser($postUser))/*
            ||($this->getUserTable()->getUserByName($postUser)->fk_user_type != $type)*/)
        {
            echo "<a href='/'>Back</a></br>";
            die("The user was not exist.");
        }
        $target_user = $this->getUserTable()->getUserByName($postUser);
        if(!($target_user->fk_user_type == 3 || $target_user->fk_user_type == 4))
        {
            echo "<a href='/'>Back</a></br>";
            die("Incorrect Usertype!");
        }
        if($this->getUserTable()->getUserByName($postUser)->password != $postPass)
        {
            echo "<a href='/'>Back</a></br>";
            die("Incorrect Password");
        }
        //Set Session for the authorized user:
        $this->session = new SessionContainer('userinfo');
        $this->session->username = $target_user->username;
        $this->session->password = $target_user->password;
        $this->session->usertype = $target_user->fk_user_type;
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
        if(isset($_GET["q"]))
        {
            $q = $_GET["q"];
            if(strlen($q) > 0)
            {
                $is_user_exist = $this->getUserTable()->checkUser($q);
                if($is_user_exist)
                {
                    echo "抱歉，该用户名已被使用，请使用其他用户名进行注册";
                }
            }
        }
        //handle the form
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
                    $user->fk_user_type = 1;//enterprise user
                    $user->is_writer = 0;
                    $user->created_at = $this->_getDateTime();
                    $user->created_by = $user->username;
                    $user->updated_at = $this->_getDateTime();
                    $user->updated_by = $user->username;
                    $this->getUserTable()->saveUser($user);
                    //create credit for the user
                    $user2 = $this->getUserTable()->getUserByName($user->username);
                    $credit = new Credit();
                    $credit->fk_user = $user2->id;
                    $credit->fk_user_type = $user2->fk_user_type;
                    $credit->amount = 0;
                    $credit->created_at = $this->_getDateTime();
                    $credit->created_by = $user2->username;
                    $credit->updated_at = $this->_getDateTime();
                    $credit->updated_by = $user2->username;
                    $this->getCreditTable()->saveCredit($credit);
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
            }/*else{
                die(var_dump($form->getMessages()));
            }*/
        }
        return new ViewModel(array(
            'form' => $form,
            //'users' => $this->getUserTable()->fetchAll(),
        ));
    }

    public function mediasignupAction()
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
                    $user->fk_user_type = 2;//media user
                    $user->created_at = $this->_getDateTime();
                    $user->created_by = $user->username;
                    $user->updated_at = $this->_getDateTime();
                    $user->updated_by = $user->username;
                    $this->getUserTable()->saveUser($user);
                    //create credit for the user
                    $user2 = $this->getUserTable()->getUserByName($user->username);
                    $credit = new Credit();
                    $credit->fk_user = $user2->id;
                    $credit->fk_user_type = $user2->fk_user_type;
                    $credit->amount = 0;
                    $credit->created_at = $this->_getDateTime();
                    $credit->created_by = $user2->username;
                    $credit->updated_at = $this->_getDateTime();
                    $credit->updated_by = $user2->username;
                    $this->getCreditTable()->saveCredit($credit);
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

    public function termsAction()
    {

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
                    $user->created_at = $this->_getDateTime();
                    $user->created_by = $cur_user;
                    $user->updated_at = $this->_getDateTime();
                    $user->updated_by = $cur_user;
                    $this->getUserTable()->saveUser($user);
                    //create credit for the user
                    $user2 = $this->getUserTable()->getUserByName($user->username);
                    $credit = new Credit();
                    $credit->fk_user = $user2->id;
                    $credit->fk_user_type = $user2->fk_user_type;
                    $credit->amount = 0;
                    $credit->created_at = $this->_getDateTime();
                    $credit->created_by = $cur_user;
                    $credit->updated_at = $this->_getDateTime();
                    $credit->updated_by = $cur_user;
                    $this->getCreditTable()->saveCredit($credit);
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
        $arr_type_allowed = array(3, 4);
        $cur_user = $this->_auth($arr_type_allowed);



        return new ViewModel(array(
            'user' => $cur_user,
            'allusers' => $this->getUserTable()->fetchAllDesc(),
        ));
    }

    public function detailAction()
    {        
        $arr_type_allowed = array(3, 4);
        $cur_user = $this->_auth($arr_type_allowed);
        $id = (int)$this->params()->fromRoute('id', 0);
        if(!$id) {
            return $this->redirect()->toRoute('user',array(
                'action' => 'admin'
            ));
        }
        $target_user = $this->getUserTable()->getUser($id);
        if(($target_user->fk_user_type == 1) && ($target_user->fk_enterprise > 0)){
            $enterprise = $this->getEnterpriseTable()->getEnterprise($target_user->fk_enterprise);
            $media = null;
        }elseif(($target_user->fk_user_type == 2) && ($target_user->fk_media > 0)){
            $enterprise = null;
            $media = $this->getMediaTable()->getMedia($target_user->fk_media);
        }else{
            $enterprise = null;
            $media = null;
        }
        $credit = $this->getCreditTable()->getCreditByFkUser($target_user->id);
        $creditlog = $this->getCreditlogTable()->fetchLogByFkCreditLimit5($credit->id_credit);

        return new ViewModel(array(
            'user'        => $cur_user,
            'target_user' => $target_user,
            'enterprise'  => $enterprise,
            'media'       => $media,
            'credit'      => $credit,
            'creditlog'   => $creditlog,
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
        $tu_created_at    = $target_user->created_at;
        $tu_created_by    = $target_user->created_by;
        $tu_fk_enterprise = $target_user->fk_enterprise;
        $tu_fk_media      = $target_user->fk_media;
        //$tu_is_writer     = $target_user->is_writer;
        $form = new UserForm();
        $form->bind($target_user);
        $form->get('submit')->setAttribute('value','保存');

        $request = $this->getRequest();
        if($request->isPost()){
            $form->setInputFilter($target_user->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $form->getData()->created_at    = $tu_created_at;
                $form->getData()->created_by    = $tu_created_by;
                $form->getData()->fk_enterprise = $tu_fk_enterprise;
                $form->getData()->fk_media      = $tu_fk_media;
                $form->getData()->updated_at    = $this->_getDateTime();
                $form->getData()->updated_by    = $cur_user;

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

    public function forgotpasswordAction()
    {
        if(isset($_POST["username"]))
        {
            $username = $_POST["username"];
            $target_user = $this->getUserTable()->getUserByName($username);
            if($target_user)
            {
                $to = $target_user->email;
                $password = $target_user->password;
                $date = $this->_getDateTime();
                $message = new Message();
                $message->addTo($to)
                        ->addFrom('noreply@furnihome.asia')
                        ->setSubject('Appbuzz.cn密码找回邮件（此为系统邮件，请勿回复）');


                $html = new MimePart(
                    '<p>尊敬的'.$username.'，</p>
                    <p>你在appbuzz.cn网站上的密码是：</p>
                    <p>'.$password.'</p>
                    <p>如有必要，请及时修改并妥善保管您的密码。</p>
                    <p>（此为系统邮件，请勿回复）</p>
                    <br><br>
                    <p>顺颂商祺，</p>
                    <p>APPbuzz.cn网站管理团队</p>
                    <p>'.substr($date, 0, 10).'</p>');
                $html->type = "text/html";
 
                $body = new MimeMessage();
                $body->addPart($html);
 
                $message->setBody($body);
 
                $transport = new SendmailTransport();
                $transport->send($message);
            }
            $this->redirect()->toRoute('application');
        }
    }

    public function changepasswordAction()
    {
        $arr_type_allowed = array(1, 2, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id = (int)$this->params()->fromRoute('id', 0);
        if(!$id) {
            return $this->redirect()->toRoute('/');
        }

        $target_user = $this->getUserTable()->getUser($id);
        $tu_created_at    = $target_user->created_at;
        $tu_created_by    = $target_user->created_by;
        $tu_fk_enterprise = $target_user->fk_enterprise;
        $tu_fk_media      = $target_user->fk_media;

        $original_password = $target_user->password;
        $form = new UserForm();
        $form->bind($target_user);
        $form->get('submit')->setAttribute('value','修改密码');        
        $request = $this->getRequest();
        if($request->isPost()){
            $form->setInputFilter($target_user->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                if($original_password != $form->getData()->originalpassword)
                {
                    echo "<a href='/'>Back</a></br>";
                    die("INCORRECT original password!");
                }
                elseif($form->getData()->password != $form->getData()->confirmpassword)
                {
                    echo "<a href='/'>Back</a></br>";
                    die("Password and confirm password must be corresponded!");
                }
                $form->getData()->created_at    = $tu_created_at;
                $form->getData()->created_by    = $tu_created_by;
                $form->getData()->fk_enterprise = $tu_fk_enterprise;
                $form->getData()->fk_media      = $tu_fk_media;
                $form->getData()->updated_at    = $this->_getDateTime();
                $form->getData()->updated_by    = $cur_user;
                $this->getUserTable()->saveUser($form->getData());
                //change the session
                $this->session = new SessionContainer('userinfo');
                $this->session->username = $form->getData()->username;
                $this->session->password = $form->getData()->password;
                $this->session->usertype = $form->getData()->fk_user_type;

                return $this->redirect()->toRoute('home');
            }
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'target_user' => $target_user,
            'form' => $form,
        ));
    }

    public function checkAction()
    {        
        if(isset($_GET["q"]))
        {
            $q = $_GET["q"];
            if(strlen($q) > 0)
            {
                $is_user_exist = $this->getUserTable()->checkUser($q);
                if($is_user_exist){
                    echo "抱歉，该用户名已被使用，请使用其他用户名进行注册<br>";die();
                }else{
                    die();
                }
            }else{
                die();
            }
        }else{
            die();
        }
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

    public function getCreditTable()
    {
        if(!$this->creditTable){
            $sm = $this->getServiceLocator();
            $this->creditTable = $sm->get('Credit\Model\CreditTable');
        }
        return $this->creditTable;
    }

    public function getCreditlogTable()
    {
        if(!$this->creditlogTable){
            $sm = $this->getServiceLocator();
            $this->creditlogTable = $sm->get('Credit\Model\CreditlogTable');
        }
        return $this->creditlogTable;
    }

    public function getEnterpriseTable()
    {
        if(!$this->enterpriseTable){
            $sm = $this->getServiceLocator();
            $this->enterpriseTable = $sm->get('Enterprise\Model\EnterpriseTable');
        }
        return $this->enterpriseTable;
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
