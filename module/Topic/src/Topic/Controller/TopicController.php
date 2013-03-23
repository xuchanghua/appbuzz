<?php
namespace Topic\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container as SessionContainer;
use Topic\Model\Topic;
use Topic\Form\TopicForm;
use User\Model\User;
use DateTime;

class TopicController extends AbstractActionController
{
    protected $userTable;
    protected $topicTable;

    public function indexAction()
    {
        $arr_type_allowed = array(2, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        return new ViewModel(array(
            'user' => $cur_user,
            'topics' => $this->getTopicTable()->fetchTopicByUser($cur_user),
        ));
    }

    public function addAction()
    {        
        $arr_type_allowed = array(2, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        $form = new TopicForm();
        $form->get('submit')->setValue('保存');
        $request = $this->getRequest();
        if($request->isPost()){
            $topic = new Topic();
            $form->setInputFilter($topic->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $topic->exchangeArray($form->getData());
                $topic->created_by = $cur_user;
                $topic->created_at = $this->_getDateTime();
                $topic->updated_by = $cur_user;
                $topic->updated_at = $this->_getDateTime();
                $this->getTopicTable()->saveTopic($topic);

                return $this->redirect()->toRoute('topic',array(
                    'action' => 'detail',
                    'id'     => $topic->id_topic,
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
        $arr_type_allowed = array(1, 2, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id = (int)$this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('topic', array(
                'action' => 'index'
            ));
        }
        $topic = $this->getTopicTable()->getTopic($id);

        return new ViewModel(array(
            'topic' => $topic,
            'user'  => $cur_user,
            'usertype' => $this->_getCurrentUserType(),
            'id'    => $id,
        ));
    }

    public function editAction()
    {
        $arr_type_allowed = array(2, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id = (int)$this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->fromRoute('topic', array(
                'action' => 'index',
            ));
        }
        $topic = $this->getTopicTable()->getTopic($id);
        $tp_created_by = $topic->created_by;
        $tp_created_at = $topic->created_at;
        $form = new TopicForm();
        $form->bind($topic);
        $form->get('submit')->setAttribute('value','保存');

        $request = $this->getRequest();
        if ($request->isPost()){
            $form->setInputFilter($topic->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $form->getData()->created_by = $tp_created_by;
                $form->getData()->created_at = $tp_created_at;
                $form->getData()->updated_by = $cur_user;
                $form->getData()->updated_at = $this->_getDateTime();
                $this->getTopicTable()->saveTopic($form->getData());

                return $this->redirect()->toRoute('topic', array(
                    'action' => 'detail',
                    'id'     => $id,
                ));
            }
        }

        return new ViewModel(array(
            'topic' => $this->getTopicTable()->getTopic($id),
            'user'  => $cur_user,
            'form'  => $form,
            'id'    => $id,
        ));
    }

    public function mgmtAction()
    {        
        $arr_type_allowed = array(1, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        return new ViewModel(array(
            'user' => $cur_user,
            'topics' => $this->getTopicTable()->fetchAll(),
        ));
    }

    public function contactAction()
    {
        $arr_type_allowed = array(1, 3);
        $cur_user = $this->_auth($arr_type_allowed);
        
    }

    public function getTopicTable()
    {
        if (!$this->topicTable) {
	    $sm = $this->getServiceLocator();
	    $this->topicTable = $sm->get('Topic\Model\TopicTable');
        }
        return $this->topicTable;
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

    protected function _getCurrentUserType()
    {        
        $this->session = new SessionContainer('userinfo');
        return $usertype = $this->session->usertype;
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
