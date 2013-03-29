<?php
namespace Attachment\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Attachment\Model\Attachment;          // <-- Add this import
use Attachment\Form\AttachmentForm;       // <-- Add this import
use Zend\Session\Container as SessionContainer;
use User\Model\User;
use Zend\File\Transfer\Adapter\Http as FileHttp;
use Zend\Validator\File\Size as FileSize;
use Zend\Validator\File\Extension as FileExt;
use DateTime;

class AttachmentController extends AbstractActionController
{
    protected $userTable;
    protected $attachmentTable;

    public function indexAction()
    {     
    }

    public function uploadAction()
    {
        $arr_type_allowed = array(1, 2, 3);
        $cur_user = $this->_auth($arr_type_allowed);   

        if($this->getRequest()->isPost())
        {
            $file = $this->params()->fromFiles('uploadedfile');
            $max = 40000;//单位比特
            $sizeObj = new FileSize(array("max"=>$max));
            $extObj = new FileExt(array("jpg","gif","png"));
            $adapter = new FileHttp();
            $adapter->setValidators(array($sizeObj, $extObj),$file['name']);
            if(!$adapter->isValid()){
                echo implode("\n",$dataError = $adapter->getMessages());
            }else{                            
                //check if the path exists
                $path = 'public/upload/'.$cur_user;
                if(!is_dir($path))
                {
                    mkdir($path);
                }
                $adapter->setDestination($path);
                if(!$adapter->receive($file['name'])){
                    echo implode("\n", $adapter->getMessages());
                }
                else
                {
                    //create a record in the table 'attachment'
                    $attachment = new Attachment();
                    $attachment->filename = $file['name'];
                    $attachment->path = $path;
                    $attachment->created_by = $cur_user;
                    $attachment->created_at = $this->_getDateTime();
                    $this->getAttachmentTable()->saveAttachment($attachment);
                    //md5() the file name
                    //rename($file['name'], md5($file['name']));
                }
            }
        }
    }

    public function attachbarcodeAction()
    {
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);

        if($this->getRequest()->isPost())
        {
            $file = $this->params()->fromFiles('filedata');
            $max = 4000;
            $sizeObj = new FileSize(array("max"=>$max));
            $extObj = new FileExt(array("jpg","gif"));
            $adapter = new FileHttp();
            $adapter->setValidators(array($sizeObj,$extObj),$file['name']);
            if(!$adapter->isValid()){
                $dataError = $adapter->getMessages();
            }else{
                $adapter->setDestination('public/upload');
                if($adapter->receive($file['name'])){
                    echo "uploaded!";
                }
            }
        }
    }

    public function addAction()
    {
    }

    public function editAction()
    {        
    }

    public function deleteAction()
    {
    }

    public function getAttachmentTable()
    {
        if (!$this->attachmentTable) {
	    $sm = $this->getServiceLocator();
	    $this->attachmentTable = $sm->get('Attachment\Model\AttachmentTable');
        }
        return $this->attachmentTable;
    }

    public function getUserTable()
    {
        if(!$this->userTable){
            $sm = $this->getServiceLocator();
            $this->userTable = $sm->get('User\Model\UserTable');
        }
        return $this->userTable;
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
