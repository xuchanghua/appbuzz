<?php
namespace Message\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Message\Model\Message;          // <-- Add this import
use Message\Form\MessageForm;       // <-- Add this import
use Zend\Session\Container as SessionContainer;
use User\Model\User;

class MessageController extends AbstractActionController
{
    protected $userTable;
    protected $messageTable;

    public function indexAction()
    {    
        //$this->_authenticateSession();
        $arr_type_allowed = array(1, 2, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        //get username from session:
        $this->session = new SessionContainer('userinfo');
        $session_user = $this->session->username;
        return new ViewModel(array(
            'messages' => $this->getMessageTable()->getMessageToUser($session_user),
            'is_writer' => $this->getUserTable()->getUserByName($cur_user)->is_writer,
        ));
        /*
	    return new ViewModel(array(
	        'messages' => $this->getMessageTable()->fetchAll(),
	    ));
        */
    }

    public function composeAction()
    {
        //$this->_authenticateSession();
        $arr_type_allowed = array(1, 2, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        //get username from session:
        $this->session = new SessionContainer('userinfo');
        $session_user = $this->session->username;

        //handle the form
        $form = new MessageForm();
        $form->get('submit')->setValue('发送');
        $request = $this->getRequest();
        if($request->isPost()){
            $message = new Message();
            $form->setInputFilter($message->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $message->exchangeArray($form->getData());
                $message->from = $session_user;
                $message->fk_message_status = 2; //sent message;
                $message->created_at = time();
                $message->updated_at = time();
                $this->getMessageTable()->saveMessage($message);
                //redirect to the inbox page
                $this->redirect()->toRoute('message/inbox');
            }
        }
        return array('form' => $form);
    }

    public function readAction()
    {
        //$this->_authenticateSession();
        $arr_type_allowed = array(1, 2, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id = (int)$this->params()->fromRoute('id',0);
        return new ViewModel(array(
            'message' => $this->getMessageTable()->getMessage($id),
        ));
    }

    public function sentAction()
    {
        //$this->_authenticateSession();
        $arr_type_allowed = array(1, 2, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        //get username from session:
        $this->session = new SessionContainer('userinfo');
        $session_user = $this->session->username;
        return new ViewModel(array(
            'messages' => $this->getMessageTable()->getMessageFromUser($session_user),
        ));
    }

    public function draftAction()
    {
      
    }

    public function getMessageTable()
    {
        if (!$this->messageTable) {
        $sm = $this->getServiceLocator();
        $this->messageTable = $sm->get('Message\Model\MessageTable');
        }
        return $this->messageTable;
    }

    public function getUserTable()
    {
        if(!$this->userTable){
            $sm = $this->getServiceLocator();
            $this->userTable = $sm->get('User\Model\UserTable');
        }
        return $this->userTable;
    }

    protected function _authorizeUser($user, $pass)
    {        
         //check if the username or password is empty
        if((!$user)||(!$pass))
        {
            echo "<a href='/'>Back</a></br>";
            die("Username or Password cannot be empty!");
        }
        //check if the username is exist
        if(!$this->getUserTable()->checkUser($user))
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
        if($this->_authorizeUser($username, $password))
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

    public function gethintAction()
    {
        // Fill up array with names
$a[]="Anna";
$a[]="Brittany";
$a[]="Cinderella";
$a[]="Diana";
$a[]="Eva";
$a[]="Fiona";
$a[]="Gunda";
$a[]="Hege";
$a[]="Inga";
$a[]="Johanna";
$a[]="Kitty";
$a[]="Linda";
$a[]="Nina";
$a[]="Ophelia";
$a[]="Petunia";
$a[]="Amanda";
$a[]="Raquel";
$a[]="Cindy";
$a[]="Doris";
$a[]="Eve";
$a[]="Evita";
$a[]="Sunniva";
$a[]="Tove";
$a[]="Unni";
$a[]="Violet";
$a[]="Liza";
$a[]="Elizabeth";
$a[]="Ellen";
$a[]="Wenche";
$a[]="Vicky";

//get the q parameter from URL
$q=$_GET["q"];

//lookup all hints from array if length of q>0
if (strlen($q) > 0)
  {
  $hint="";
  for($i=0; $i<count($a); $i++)
    {
    if (strtolower($q)==strtolower(substr($a[$i],0,strlen($q))))
      {
      if ($hint=="")
        {
        $hint=$a[$i];
        }
      else
        {
        $hint=$hint." , ".$a[$i];
        }
      }
    }
  }

// Set output to "no suggestion" if no hint were found
// or to the correct values
if ($hint == "")
  {
  $response="no suggestion";
  }
else
  {
  $response=$hint;
  }

//output the response
echo $response;
    }

}
