<?php
namespace Topic\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container as SessionContainer;
use Topic\Model\Topic;
use Topic\Form\TopicForm;
use Topic\Model\Tpcontact;
use Topic\Form\TpcontactForm;
use User\Model\User;
use DateTime;
use Zend\File\Transfer\Adapter\Http as FileHttp;
use Zend\Validator\File\Size as FileSize;
use Zend\Validator\File\Extension as FileExt;
use Attachment\Model\Attachment;

class TopicController extends AbstractActionController
{
    protected $userTable;
    protected $topicTable;
    protected $tpcontactTable;
    protected $productTable;
    protected $attachmentTable;

    public function indexAction()
    {
        $arr_type_allowed = array(2);
        $cur_user = $this->_auth($arr_type_allowed);

        return new ViewModel(array(
            'user' => $cur_user,
            'past_topics' => $this->getTopicTable()->fetchPastTopic($cur_user),
            'current_topics' => $this->getTopicTable()->fetchCurrentTopic($cur_user),
            'topics' => $this->getTopicTable()->fetchTopicByUser($cur_user),
        ));
    }

    public function addAction()
    {        
        $arr_type_allowed = array(2);
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
        $tpcontact = $this->getTpcontactTable()->fetchTpcontactByFkTopic($topic->id_topic);
        $all_users = $this->getUserTable()->fetchAll();

        return new ViewModel(array(
            'topic' => $topic,
            'user'  => $cur_user,
            'usertype' => $this->_getCurrentUserType(),
            'id'    => $id,
            'tpcontact' => $tpcontact,
            'all_users' => $all_users,
            'products' => $this->getProductTable()->fetchAll(),
        ));
    }

    public function editAction()
    {
        $arr_type_allowed = array(2);
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

    public function currentAction()
    {
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);

        return new ViewModel(array(
            'user' => $cur_user,
            'past_topics' => $this->getTopicTable()->fetchPastTopic(),
            'current_topics' => $this->getTopicTable()->fetchCurrentTopic(),
            'topics' => $this->getTopicTable()->fetchAll(),
            'tpcontacts' => $this->getTpcontactTable()->fetchTpcontactByUser($cur_user),
        ));
    }

    public function mgmtAction()
    {        
        //for enterprise user, to see all the topics
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);

        return new ViewModel(array(
            'user' => $cur_user,
            'topics' => $this->getTopicTable()->fetchAll(),
            'users' => $this->getUserTable()->fetchAll(),
            'tpcontacts' => $this->getTpcontactTable()->fetchTpcontactByUser($cur_user),
        ));
    }

    public function contactAction()
    {
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_topic = (int)$this->params()->fromRoute('id', 0);
        if (!$id_topic) {
            return $this->redirect()->toRoute('topic', array(
                'action' => 'current',
            ));
        }
        $topic = $this->getTopicTable()->getTopic($id_topic);
        $enterprise_user = $this->getUserTable()->getUserByName($cur_user);
        $media_user = $this->getUserTable()->getUserByName($topic->created_by);
        
        //handle the form
        $form = new TpcontactForm();
        $form->get('submit')->setValue('提交');
        $request = $this->getRequest();
        if($request->isPost()){
            $tpcontact = new Tpcontact();
            $form->setInputFilter($tpcontact->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $tpcontact->exchangeArray($form->getData());
                $tpcontact->fk_topic = $topic->id_topic;
                $tpcontact->fk_enterprise_user = $enterprise_user->id;
                $tpcontact->fk_media_user = $media_user->id;
                //fk_product is post with the form
                $tpcontact->created_at = $this->_getDateTime();
                $tpcontact->created_by = $cur_user;
                $tpcontact->updated_at = $this->_getDateTime();
                $tpcontact->updated_by = $cur_user;
                //create the order_no later after get the id
                //matching_degree is post with the form
                $tpcontact->fk_tpcontact_status = 1;
                $this->getTpcontactTable()->saveTpcontact($tpcontact);
                //save the order number
                $id_tpcontact = $this->getTpcontactTable()->getId(
                    $tpcontact->created_at,
                    $tpcontact->created_by
                );

                //upload start
                $file = $this->params()->fromFiles('attachment');
                $max = 50000000;
                $sizeObj = new FileSize(array("max"=>$max));
                $adapter = new FileHttp();
                $adapter->setValidators(array($sizeObj),$file['name']);
                if(!$adapter->isValid()){
                    echo implode("\n", $dataError = $adapter->getMessages());
                }else{
                    $path_0    = 'public/upload/';
                    $path_1    = $path_0.$cur_user.'/';
                    $path_2    = $path_1.'tpcontact/';
                    $path_full = $path_2.$id_tpcontact.'/';
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
                        //create a record in the table 'attachment'
                        $attachment = new Attachment();
                        $attachment->filename = $file['name'];
                        $attachment->path = $path_full;
                        $attachment->created_by = $cur_user;
                        $attachment->created_at = $this->_getDateTime();
                        $this->getAttachmentTable()->saveAttachment($attachment);
                        $id_attachment = $this->getAttachmentTable()->getId($attachment->created_at, $attachment->created_by);
                    }
                }
                //upload end

                $tpcontact2 = $this->getTpcontactTable()->getTpcontact($id_tpcontact);
                $tpcontact2->attachment = $id_attachment;
                $tpcontact2->order_no = 40000000 + $id_tpcontact;
                $this->getTpcontactTable()->saveTpcontact($tpcontact2);

                return $this->redirect()->toRoute('topic', array(
                    'action' => 'current',
                ));   
            }
            else
            {
                die(var_dump($form->getMessages()));
            }
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'topic' => $topic,
            'form' => $form,
            'products' => $this->getProductTable()->fetchProductByUser($cur_user),
        ));        
    }

    public function editcontactAction()
    {
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_tpcontact = (int)$this->params()->fromRoute('id', 0);
        if (!$id_tpcontact) {
            return $this->redirect()->toRoute('topic', array(
                'action' => 'current',
            ));
        }

        $tpcontact = $this->getTpcontactTable()->getTpcontact($id_tpcontact);
        if($tpcontact->attachment)
        {
            $attachment = $this->getAttachmentTable()->getAttachment($tpcontact->attachment);
            $attachment_path = '/upload/'.$cur_user.'/tpcontact/'.$id_tpcontact.'/'.$attachment->filename;
            $attachment_name = $attachment->filename;
        }
        else
        {
            $attachment_path = '#';
            $attachment_name = '未上传附件';
        }
        $id_topic = $tpcontact->fk_topic;
        $tc_fk_topic            = $tpcontact->fk_topic;
        $tc_fk_enterprise_user  = $tpcontact->fk_enterprise_user;
        $tc_fk_media_user       = $tpcontact->fk_media_user;
        $tc_created_at          = $tpcontact->created_at;
        $tc_created_by          = $tpcontact->created_by;
        $tc_order_no            = $tpcontact->order_no;
        $tc_fk_tpcontact_status = $tpcontact->fk_tpcontact_status;
        $tc_attachment          = $tpcontact->attachment;
        $form = new TpcontactForm();
        $form->bind($tpcontact);
        $form->get('submit')->setAttribute('value', '保存');

        $request = $this->getRequest();
        if($request->isPost()){
            //upload start
            $file = $this->params()->fromFiles('attachment');
            if(!$file['name'])
            {
                //if the attachment is not pick up:
                //skip the upload section
            }
            else
            {
                $max = 50000000;
                $sizeObj = new FileSize(array("max"=>$max));
                $adapter = new FileHttp();
                $adapter->setValidators(array($sizeObj), $file['name']);
                if(!$adapter->isValid()){
                    echo implode("\n", $dataError = $adapter->getMessages());
                }else{
                    $path_0    = 'public/upload/';
                    $path_1    = $path_0.$cur_user.'/';
                    $path_2    = $path_1.'tpcontact/';
                    $path_full = $path_2.$id_tpcontact.'/';
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
                        $attachment = new Attachment();
                        $attachment->filename = $file['name'];
                        $attachment->path = $path_full;
                        $attachment->created_by = $cur_user;
                        $attachment->created_at = $this->_getDateTime();
                        $this->getAttachmentTable()->saveAttachment($attachment);
                        $id_attachment = $this->getAttachmentTable()->getId($attachment->created_at, $attachment->created_by);
                    }
                }
            }
            //upload end

            $form->setInputFilter($tpcontact->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $form->getData()->fk_topic = $tc_fk_topic;
                $form->getData()->fk_enterprise_user = $tc_fk_enterprise_user;
                $form->getData()->fk_media_user = $tc_fk_media_user;
                $form->getData()->created_at = $tc_created_at;
                $form->getData()->created_by = $tc_created_by;
                $form->getData()->order_no = $tc_order_no;
                $form->getData()->fk_tpcontact_status = $tc_fk_tpcontact_status;
                $form->getData()->updated_at = $this->_getDateTime();
                $form->getData()->updated_by = $cur_user;
                if(isset($id_attachment))
                {
                    $form->getData()->attachment = $id_attachment;
                }
                else
                {
                    $form->getData()->attachment = $tc_attachment;
                }
                $this->getTpcontactTable()->saveTpcontact($form->getData());

                return $this->redirect()->toRoute('topic', array(
                    'action' => 'viewcontact',
                    'id'     => $id_topic,
                ));
            }
            else
            {
                die(var_dump($form->getMessages()));
            }
        }
        return new ViewModel(array(
            'user' => $cur_user,
            'topic' => $this->getTopicTable()->getTopic($id_topic),
            'tc' => $this->getTpcontactTable()->getTpcontact($id_tpcontact),
            'form' => $form,
            'products' => $this->getProductTable()->fetchProductByUser($cur_user),
            'attachment_path' => $attachment_path,
            'attachment_name' => $attachment_name,
        ));
    }

    public function viewcontactAction()
    {
        $arr_type_allowed = array(1, 2);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_topic = (int)$this->params()->fromRoute('id', 0);
        if (!$id_topic) {
            return $this->redirect()->toRoute('topic', array(
                'action' => 'current',
            ));
        }
        $topic = $this->getTopicTable()->getTopic($id_topic);
        $tpcontact = $this->getTpcontactTable()->getTpcontactByFkTpAndUser($id_topic, $cur_user);
        
        $view = array(
            'user' => $cur_user,
            'user_type' => $this->getUserTable()->getUserByName($cur_user)->fk_user_type,
            'topic' => $topic,
            'tpcontact' => $tpcontact,
            'attachment' => $this->getAttachmentTable()->getAttachment($tpcontact->attachment),
        );

        if($tpcontact)
        {
            $product = $this->getProductTable()->getProduct($tpcontact->fk_product);
            $view['product'] = $product;
        }

        return new ViewModel($view);
    }

    public function contactinfoAction()
    {
        //媒体->选题管理->查看(单个企业提交的“我要联系”订单)
        $arr_type_allowed = array(2);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_tpcontact = (int)$this->params()->fromRoute('id', 0);
        if (!$id_tpcontact) {
            return $this->redirect()->toRoute('topic', array(
                'action' => 'index',
            ));
        }
        $tpcontact = $this->getTpcontactTable()->getTpcontact($id_tpcontact);
        $id_topic = $tpcontact->fk_topic;
        $topic = $this->getTopicTable()->getTopic($id_topic);
        $product = null;
        if($tpcontact->fk_product)
        {
            $id_product = $tpcontact->fk_product;
            $product = $this->getProductTable()->getProduct($id_product);
        }
        $attachment = null;
        if($tpcontact->attachment)
        {
            $attachment = $this->getAttachmentTable()->getAttachment($tpcontact->attachment);
        }

        return new ViewModel(array(
            'user'       => $cur_user,
            'tpcontact'  => $tpcontact,
            'topic'      => $topic,
            'product'    => $product,
            'attachment' => $attachment,
        ));
    }

    public function contactlinkAction()
    {
        $arr_type_allowed = array(2);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_tpcontact = (int)$this->params()->fromRoute('id', 0);
        if (!$id_tpcontact) {
            return $this->redirect()->toRoute('topic', array(
                'action' => 'index',
            ));
        }
        $tpcontact = $this->getTpcontactTable()->getTpcontact($id_tpcontact);
        $tc_fk_topic           = $tpcontact->fk_topic;
        $tc_fk_enterprise_user = $tpcontact->fk_enterprise_user;
        $tc_fk_media_user      = $tpcontact->fk_media_user;
        $tc_fk_product         = $tpcontact->fk_product;
        $tc_created_at         = $tpcontact->created_at;
        $tc_created_by         = $tpcontact->created_by;
        $tc_order_no           = $tpcontact->order_no;
        $tc_attachment         = $tpcontact->attachment;

        $form = new TpcontactForm();
        $form->bind($tpcontact);
        if($tpcontact->fk_tpcontact_status ==1){
            $form->get('submit')->setAttribute('value', '完成选题');
        }else{
            $form->get('submit')->setAttribute('value', '提交');
        }
        

        $request = $this->getRequest();
        if($request->isPost()){
            $form->setInputFilter($tpcontact->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $form->getData()->fk_topic = $tc_fk_topic;
                $form->getData()->fk_enterprise_user = $tc_fk_enterprise_user;
                $form->getData()->fk_media_user = $tc_fk_media_user;
                $form->getData()->fk_product = $tc_fk_product;
                $form->getData()->created_at = $tc_created_at;
                $form->getData()->created_by = $tc_created_by;
                $form->getData()->order_no = $tc_order_no;
                $form->getData()->attachment = $tc_attachment;
                $form->getData()->fk_tpcontact_status = 2;//finished
                $form->getData()->updated_at = $this->_getDateTime();
                $form->getData()->updated_by = $cur_user;
                $this->getTpcontactTable()->saveTpcontact($form->getData());

                return $this->redirect()->toRoute('topic', array(
                    'action' => 'detail',
                    'id'     => $tpcontact->fk_topic,
                ));
            }else{
                die(var_dump($form->getMessages()));
            }
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'tpcontact' => $tpcontact,
            'form' => $form,
        ));
    }

    public function testwordchsAction()
    {
        require_once './vendor/Classes/PHPWord.php';

$PHPWord = new \PHPWord();

$document = $PHPWord->loadTemplate('Template.docx');

$document->setValue('Value1', 'Sun 太阳');
$document->setValue('Value2', 'Mercury 水星');
$document->setValue('Value3', 'Venus 金星');
$document->setValue('Value4', 'Earth 地球');
$document->setValue('Value5', 'Mars 火星');
$document->setValue('Value6', 'Jupiter 木星');
$document->setValue('Value7', 'Saturn 土星');
$document->setValue('Value8', 'Uranus 天王星');
$document->setValue('Value9', 'Neptun 海王星');
$document->setValue('Value10', 'Pluto 冥王星');

$document->setValue('weekday', date('l'));
$document->setValue('time', date('H:i'));

$document->save('Solarsystem.docx');
    }

    public function testwordAction()
    {
        // Include the PHPWord.php, all other classes were loaded by an autoloader
        require_once './vendor/Classes/PHPWord.php';

        // Create a new PHPWord Object
        $PHPWord = new \PHPWord();

        // Every element you want to append to the word document is placed in a section. So you need a section:
        $section = $PHPWord->createSection();

        // After creating a section, you can append elements:
        $section->addText('Hello world!');

        // You can directly style your text by giving the addText function an array:
        $section->addText('Hello world! I am formatted.', array('name'=>'Tahoma', 'size'=>16, 'bold'=>true));

        // If you often need the same style again you can create a user defined style to the word document
        // and give the addText function the name of the style:
        $PHPWord->addFontStyle('myOwnStyle', array('name'=>'Verdana', 'size'=>14, 'color'=>'1B2232'));
        $section->addText('Hello world! I am formatted by a user defined style', 'myOwnStyle');

        // You can also putthe appended element to local object an call functions like this:
        $PHPWord->addFontStyle('myFont', array('name'=>'Verdana', 'size'=>22, 'bold'=>true));
        $myTextElement = $section->addText('Hello World!','myFont');

        // At least write the document to webspace:
        $objWriter = \PHPWord_IOFactory::createWriter($PHPWord, 'Word2007');
        $objWriter->save('data/word/helloWorld.docx');
    }

    public function getTopicTable()
    {
        if (!$this->topicTable) {
	    $sm = $this->getServiceLocator();
	    $this->topicTable = $sm->get('Topic\Model\TopicTable');
        }
        return $this->topicTable;
    }

    public function getAttachmentTable()
    {
        if (!$this->attachmentTable) {
            $sm = $this->getServiceLocator();
            $this->attachmentTable = $sm->get('Attachment\Model\AttachmentTable');
        }
        return $this->attachmentTable;
    }

    public function getTpcontactTable()
    {
        if (!$this->tpcontactTable) {
        $sm = $this->getServiceLocator();
        $this->tpcontactTable = $sm->get('Topic\Model\TpcontactTable');
        }
        return $this->tpcontactTable;
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
        if(!$this->productTable){
            $sm = $this->getServiceLocator();
            $this->productTable = $sm->get('Product\Model\ProductTable');
        }
        return $this->productTable;
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
