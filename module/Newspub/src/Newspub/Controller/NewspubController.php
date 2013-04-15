<?php
namespace Newspub\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Newspub\Model\Newspub;          
use Newspub\Form\NewspubForm;       
use Zend\Session\Container as SessionContainer;
use User\Model\User;
use Zend\File\Transfer\Adapter\Http as FileHttp;
use Zend\Validator\File\Size as FileSize;
use Zend\Validator\File\Extension as FileExt;
use Attachment\Model\Attachment;
use Attachment\Model\Barcode;
use DateTime;
use Newspub\Model\Npmedia;
use Newspub\Form\NpmediaForm;
use Credit\Model\Credit;
use Credit\Model\Creditlog;

class NewspubController extends AbstractActionController
{
    protected $userTable;
    protected $newspubTable;
    protected $productTable;
    protected $attachmentTable;
    protected $barcodeTable;
    protected $npmediaTable;
    protected $creditTable;
    protected $creditlogTable;

    public function indexAction()
    {
        //Authenticate the user information from the session
        //$cur_user = $this->_authenticateSession(1);
        $arr_type_allowed = array(1, 3);
        $cur_user = $this->_auth($arr_type_allowed);

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
        //$cur_user = $this->_authenticateSession(1);
        $arr_type_allowed = array(1, 3);
        $cur_user = $this->_auth($arr_type_allowed);

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
                if(($newspub->fk_pub_mode == 1)&&($newspub->sel_right == null))
                {
                    echo "<a href='/newspub/add'>Back</a></br>";
                    die("Please select at least one media!");
                }
                if($newspub->fk_pub_mode == 1){
                    $price = count($newspub->sel_right) * 350;
                }else{
                    $price = 1500;
                }                
                $fk_user = $this->getUserTable()->getUserByName($cur_user)->id;
                $is_sufficient = $this->getCreditTable()->issufficient($price, $fk_user);
                if(!$is_sufficient)
                {
                    echo "<a href='/newspub/add'>Back</a></br>";
                    die("Insufficient Credit! Please Charge Your Account!");
                }
                $newspub->created_by = $cur_user;
                $newspub->created_at = $this->_getDateTime();
                $newspub->updated_by = $cur_user;
                $newspub->updated_at = $this->_getDateTime();
                $newspub->fk_newspub_status = 1;
                //$newspub->barcode = $id_attachment;
                $this->getNewspubTable()->saveNewspub($newspub);
                $id_newspub = $this->getNewspubTable()->getId(
                        $newspub->created_at, 
                        $newspub->created_by
                    );

                //upload start
                $file = $this->params()->fromFiles('barcode');
                $max = 4000000;//单位比特
                $sizeObj = new FileSize(array("max"=>$max));
                $extObj = new FileExt(array("jpg","gif","png"));
                $adapter = new FileHttp();
                $adapter->setValidators(array($sizeObj, $extObj),$file['name']);
                if(!$adapter->isValid()){
                    echo implode("\n",$dataError = $adapter->getMessages());
                }else{
                    //check if the path exists
                    //path format: /public/upload/user_name/module_name/id_module_name/
                    $path_0    = 'public/upload/';
                    $path_1    = $path_0.$cur_user.'/';
                    $path_2    = $path_1.'newspub/';
                    $path_full = $path_2.$id_newspub.'/';
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
                        //create a record in the table 'barcode'
                        $barcode = new Barcode();
                        $barcode->filename = $file['name'];
                        $barcode->path = $path_full;
                        $barcode->created_by = $cur_user;
                        $barcode->created_at = $this->_getDateTime();
                        $this->getBarcodeTable()->saveBarcode($barcode);
                        $id_barcode = $this->getBarcodeTable()->getId($barcode->created_at, $barcode->created_by);
                        //md5() the file name
                        //rename($file['name'], md5($file['name']));
                    }
                }
                //upload end

                $newspub2 = $this->getNewspubTable()->getNewspub($id_newspub);
                $newspub2->barcode = $id_barcode;
                $newspub2->order_no = 10000000 + $newspub2->id_newspub;
                $this->getNewspubTable()->saveNewspub($newspub2);

                //update the user's credit
                $credit = $this->getCreditTable()->getCreditByFkUser($fk_user);
                $originamount = $credit->amount;
                $credit->amount = $originamount - $price;
                $credit->updated_at = $this->_getDateTime();
                $credit->updated_by = $cur_user;
                $this->getCreditTable()->saveCredit($credit);

                //create creditlog record;
                $creditlog = new Creditlog();
                $creditlog->fk_credit = $credit->id_credit;
                if($newspub2->fk_pub_mode == 1){
                    $creditlog->fk_service_type = 2;//新闻发布->单篇发布
                }else{
                    $creditlog->fk_service_type = 3;//新闻发布->打包发布
                }
                $creditlog->fk_from = $fk_user;
                $creditlog->fk_to = null;
                $creditlog->date_time = $this->_getDateTime();
                $creditlog->amount = $price;
                $creditlog->is_pay = 1;//is pay
                $creditlog->is_charge = 0;//not charge
                $creditlog->created_at = $this->_getDateTime();
                $creditlog->created_by = $cur_user;
                $this->getCreditlogTable()->saveCreditlog($creditlog);                

                //save npmedia for single payment newspub
                if($newspub->fk_pub_mode == 1)
                {
                    $arr_sel_media = $newspub->sel_right;
                    foreach ($arr_sel_media as $s_m)
                    {
                        $npmedia = new Npmedia();
                        $npmedia->fk_newspub = $id_newspub;
                        $npmedia->fk_media_user = $s_m;
                        $npmedia->fk_npmedia_status = 1;
                        $npmedia->created_at = $this->_getDateTime();
                        $npmedia->created_by = $cur_user;
                        $npmedia->updated_at = $this->_getDateTime();
                        $npmedia->updated_by = $cur_user;
                        $this->getNpmediaTable()->saveNpmedia($npmedia);
                    }
                }
                
                return $this->redirect()->toRoute('newspub',array(
                    'action'=>'detail',
                    'id'    => $id_newspub,
                ));
            }/*else{
                die(var_dump($form->getMessages()));
            }*/
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'form' => $form,
            'newspub' => $this->getNewspubTable()->getNewspubByUser($cur_user),
            'products' => $this->getProductTable()->fetchProductByUser($cur_user),
            'medias' => $this->getUserTable()->fetchUserByFkType(2),
        ));        
    }

    public function detailAction()
    {
        //$cur_user = $this->_authenticateSession(1);
        $arr_type_allowed = array(1, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id = (int)$this->params()->fromRoute('id',0);        
        if (!$id) {
            return $this->redirect()->toRoute('newspub', array(
                'action' => 'index'
            ));
        }
        $np = $this->getNewspubTable()->getNewspub($id);
        if($np->barcode)
        {
            $barcode = $this->getBarcodeTable()->getBarcode($np->barcode);
            $barcode_path = '/upload/'.$np->created_by.'/newspub/'.$id.'/'.$barcode->filename;
        }
        else
        {
            $barcode_path = '#';
        }

        return new ViewModel(array(
            'np' => $np,
            'user' => $cur_user,
            'user_type' => $this->getUserTable()->getUserByName($cur_user)->fk_user_type,
            'all_users' => $this->getUserTable()->fetchAll(),
            'id' => $id,
            'product' => $this->getProductTable()->getProduct($np->fk_product),
            'barcode_path' => $barcode_path,
            'npmedia' => $this->getNpmediaTable()->fetchNpmediaByFkNewspub($id),
        ));
    }

    public function editAction()
    {
        //$cur_user = $this->_authenticateSession(1);
        $arr_type_allowed = array(1, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_newspub = (int)$this->params()->fromRoute('id',0);        
        if (!$id_newspub) {
            return $this->redirect()->toRoute('newspub', array(
                'action' => 'index',
            ));
        }
        $newspub = $this->getNewspubTable()->getNewspub($id_newspub);
        if($newspub->barcode)
        {
            $barcode = $this->getBarcodeTable()->getBarcode($newspub->barcode);
            $barcode_path = '/upload/'.$cur_user.'/newspub/'.$id_newspub.'/'.$barcode->filename;
        }
        else
        {
            $barcode_path = '#';
        }
        $product = $this->getProductTable()->getProduct($newspub->fk_product);
        $np_created_by = $newspub->created_by;
        $np_created_at = $newspub->created_at;
        $np_fk_newspub_status = $newspub->fk_newspub_status;
        $np_order_no = $newspub->order_no;
        $np_barcode = $newspub->barcode;
        $form = new NewspubForm();
        $form->bind($newspub);
        $form->get('submit')->setAttribute('value','保存');

        $request = $this->getRequest();
        if ($request->isPost()){
            //upload start
            $file = $this->params()->fromFiles('barcode');
            if(!$file['name'])
            {
                //if the barcode is not pick up:
                //skip the upload section
            }
            else
            {
                $max = 400000;//单位比特
                $sizeObj = new FileSize(array("max"=>$max));
                $extObj = new FileExt(array("jpg","gif","png"));
                $adapter = new FileHttp();
                $adapter->setValidators(array($sizeObj, $extObj),$file['name']);
                if(!$adapter->isValid()){
                    echo implode("\n",$dataError = $adapter->getMessages());
                }else{
                    //check if the path exists
                    //path format: /public/upload/user_name/module_name/id_module_name/
                    $path_0    = 'public/upload/';
                    $path_1    = $path_0.$cur_user.'/';
                    $path_2    = $path_1.'newspub/';
                    $path_full = $path_2.$id_newspub.'/';
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
                        //create a record in the table 'barcode'
                        $barcode = new Barcode();
                        $barcode->filename = $file['name'];
                        $barcode->path = $path_full;
                        $barcode->created_by = $cur_user;
                        $barcode->created_at = $this->_getDateTime();
                        $this->getBarcodeTable()->saveBarcode($barcode);
                        $id_barcode = $this->getBarcodeTable()->getId($barcode->created_at, $barcode->created_by);
                        //md5() the file name
                        //rename($file['name'], md5($file['name']));
                    }
                }
            }
            //upload end

            $form->setInputFilter($newspub->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $form->getData()->created_by = $np_created_by;
                $form->getData()->created_at = $np_created_at;
                $form->getData()->fk_newspub_status = $np_fk_newspub_status;
                $form->getData()->order_no = $np_order_no;
                $form->getData()->updated_by = $cur_user;
                $form->getData()->updated_at = $this->_getDateTime();
                if(isset($id_barcode))
                {
                    $form->getData()->barcode = $id_barcode;
                }
                else
                {
                    $form->getData()->barcode = $np_barcode;
                }
                $this->getNewspubTable()->saveNewspub($form->getData());   

                return $this->redirect()->toRoute('newspub',array(
                    'action'=>'detail',
                    'id'    => $id_newspub,
                ));
            }
            else
            {
                die(var_dump($form->getMessages()));
            }
        }
        return new ViewModel(array(
            'np'      => $this->getNewspubTable()->getNewspub($id_newspub),
            'user'    => $cur_user,
            'form'    => $form,
            'id'      => $id_newspub,
            'product' => $product,
            'barcode_path' => $barcode_path,
        ));
    }

    public function adminAction()
    {
        //authenticate the admin user:
        //$cur_user = $this->_authenticateSession(3);
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);


        return new ViewModel(array(
            'user' => $cur_user,
            'newspubs' => $this->getNewspubTable()->fetchAllDesc(),
        ));
    }

    public function neworderAction()
    {
        //$cur_user = $this->_authenticateSession(1);
        $arr_type_allowed = array(1, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        if(!$id_product = (int)$this->params()->fromQuery('id_product',0))
        {
            $id_product = $this->getRequest()->getPost()->fk_product;
        }
        //echo "id_product = ".$id_product;
        $product = $this->getProductTable()->getProduct($id_product);
        $newspub = new Newspub();
        $newspub->fk_product = $product->id_product;
        $newspub->download_link = $product->web_link;
        $newspub->appstore_links = $product->appstore_link;
        $newspub->androidmkt_link = $product->androidmkt_link;        
        //die(var_dump($newspub));

        $form = new NewspubForm();
        $form->bind($newspub);
        $form->get('submit')->setAttribute('value','保存');

        $request = $this->getRequest();
        if ($request->isPost()){            

            $form->setInputFilter($newspub->getInputFilter());
            $form->setData($request->getPost());
            //die(var_dump($request->getPost()));
            if($form->isValid()){
                $form->getData()->created_by = $cur_user;
                $form->getData()->created_at = $this->_getDateTime();
                $form->getData()->updated_by = $cur_user;
                $form->getData()->updated_at = $this->_getDateTime();
                $form->getData()->fk_newspub_status = 1;
                $form->getData()->fk_product = $id_product;
                $this->getNewspubTable()->saveNewspub($form->getData());                   
                $id_newspub = $this->getNewspubTable()->getId(
                    $form->getData()->created_at, 
                    $form->getData()->created_by
                );

            //upload start
            $file = $this->params()->fromFiles('barcode');
            $max = 400000;//单位比特
            $sizeObj = new FileSize(array("max"=>$max));
            $extObj = new FileExt(array("jpg","gif","png"));
            $adapter = new FileHttp();
            $adapter->setValidators(array($sizeObj, $extObj),$file['name']);
            if(!$adapter->isValid()){
                echo implode("\n",$dataError = $adapter->getMessages());
            }else{
                //check if the path exists
                //path format: /public/upload/user_name/module_name/id_module_name/
                $path_0    = 'public/upload/';
                $path_1    = $path_0.$cur_user.'/';
                $path_2    = $path_1.'newspub/';
                $path_full = $path_2.$id_newspub.'/';
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
                    //create a record in the table 'barcode'
                    $barcode = new Barcode();
                    $barcode->filename = $file['name'];
                    $barcode->path = $path_full;
                    $barcode->created_by = $cur_user;
                    $barcode->created_at = $this->_getDateTime();
                    $this->getBarcodeTable()->saveBarcode($barcode);
                    $id_barcode = $this->getBarcodeTable()->getId($barcode->created_at, $barcode->created_by);
                    //md5() the file name
                    //rename($file['name'], md5($file['name']));
                }
            }
            //upload end

                $newspub2 = $this->getNewspubTable()->getNewspub($id_newspub);
                $newspub2->barcode = $id_barcode;
                $this->getNewspubTable()->saveNewspub($newspub2);

                return $this->redirect()->toRoute('newspub',array(
                    'action'=>'detail',
                    'id'    => $id_newspub,
                ));
            }
        }

        return new ViewModel(array(
            'product' => $product,
            'user' => $cur_user,
            'form' => $form,
        ));
    }

    public function paidAction()
    {
        //管理员->新闻发布->确认付款
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_newspub = (int)$this->params()->fromRoute('id',0);        
        if (!$id_newspub) {
            return $this->redirect()->toRoute('newspub', array(
                'action' => 'admin'
            ));
        }

        $newspub = $this->getNewspubTable()->getNewspub($id_newspub);
        $newspub->fk_newspub_status = 2;
        $newspub->updated_by = $cur_user;
        $newspub->updated_at = $this->_getDateTime();
        $this->getNewspubTable()->saveNewspub($newspub);

        return $this->redirect()->toRoute('newspub', array(
            'action' => 'admin',
        ));

        return new ViewModel(array(
            'user' => $cur_user,
        ));
    }

    public function completedAction()
    {
        //管理员->新闻发布->确认付款
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_newspub = (int)$this->params()->fromRoute('id',0);        
        if (!$id_newspub) {
            return $this->redirect()->toRoute('newspub', array(
                'action' => 'admin'
            ));
        }

        $newspub = $this->getNewspubTable()->getNewspub($id_newspub);
        $newspub->fk_newspub_status = 3;
        $newspub->updated_by = $cur_user;
        $newspub->updated_at = $this->_getDateTime();
        $this->getNewspubTable()->saveNewspub($newspub);

        return $this->redirect()->toRoute('newspub', array(
            'action' => 'admin',
        ));

        return new ViewModel(array(
            'user' => $cur_user,
        ));
    }

    public function mediaaccAction()
    {
        //管理员用户->媒体接受
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_npmedia = (int)$this->params()->fromRoute('id',0);        
        if (!$id_npmedia) {
            return $this->redirect()->toRoute('newspub', array(
                'action' => 'admin',
            ));
        }
        $npmedia = $this->getNpmediaTable()->getNpmedia($id_npmedia);
        $npmedia->fk_npmedia_status = 2;
        $npmedia->updated_at = $this->_getDateTime();
        $npmedia->updated_by = $cur_user;
        $this->getNpmediaTable()->saveNpmedia($npmedia);

        return $this->redirect()->toRoute('newspub', array(
            'action' => 'detail',
            'id'     => $npmedia->fk_newspub,
        ));
    }

    public function mediarejAction()
    {
        //管理员用户->媒体拒绝
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_npmedia = (int)$this->params()->fromRoute('id',0);        
        if (!$id_npmedia) {
            return $this->redirect()->toRoute('newspub', array(
                'action' => 'admin',
            ));
        }
        $npmedia = $this->getNpmediaTable()->getNpmedia($id_npmedia);
        $npmedia->fk_npmedia_status = 3;
        $npmedia->updated_at = $this->_getDateTime();
        $npmedia->updated_by = $cur_user;
        $this->getNpmediaTable()->saveNpmedia($npmedia);

        return $this->redirect()->toRoute('newspub', array(
            'action' => 'detail',
            'id'     => $npmedia->fk_newspub,
        ));
    }

    public function publishAction()
    {
        //管理员用户->发布
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_npmedia = (int)$this->params()->fromRoute('id',0);        
        if (!$id_npmedia) {
            return $this->redirect()->toRoute('newspub', array(
                'action' => 'admin',
            ));
        }
        $npmedia = $this->getNpmediaTable()->getNpmedia($id_npmedia);
        $np_fk_newspub = $npmedia->fk_newspub;
        $np_fk_media_user = $npmedia->fk_media_user;
        $np_created_at = $npmedia->created_at;
        $np_created_by = $npmedia->created_by;        
        $form = new NpmediaForm();
        $form->bind($npmedia);
        $form->get('submit')->setAttribute('value','保存并发布');

        $request = $this->getRequest();
        if($request->isPost()){
            $form->setInputFilter($npmedia->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $form->getData()->fk_newspub = $np_fk_newspub;
                $form->getData()->fk_media_user = $np_fk_media_user;
                $form->getData()->created_at = $np_created_at;
                $form->getData()->created_by = $np_created_by;
                $form->getData()->fk_npmedia_status = 4;
                $form->getData()->updated_at = $this->_getDateTime();
                $form->getData()->updated_by = $cur_user;
                $this->getNpmediaTable()->saveNpmedia($form->getData());

                return $this->redirect()->toRoute('newspub', array(
                    'action' => 'detail',
                    'id'     => $npmedia->fk_newspub,
                ));
            }
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'form' => $form,
            'id'   => $id_npmedia,
        ));
    }

    public function cancelAction()
    {
        //管理员用户->企业取消
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_npmedia = (int)$this->params()->fromRoute('id',0);        
        if (!$id_npmedia) {
            return $this->redirect()->toRoute('newspub', array(
                'action' => 'admin',
            ));
        }
        $npmedia = $this->getNpmediaTable()->getNpmedia($id_npmedia);
        $npmedia->fk_npmedia_status = 5;
        $npmedia->updated_at = $this->_getDateTime();
        $npmedia->updated_by = $cur_user;
        $this->getNpmediaTable()->saveNpmedia($npmedia);

        return $this->redirect()->toRoute('newspub', array(
            'action' => 'detail',
            'id'     => $npmedia->fk_newspub,
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

    public function ajaxAction()
    {
    }

    public function getNpmediaTable()
    {
        if (!$this->npmediaTable) {
        $sm = $this->getServiceLocator();
        $this->npmediaTable = $sm->get('Newspub\Model\NpmediaTable');
        }
        return $this->npmediaTable;
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

    public function getProductTable()
    {
        if (!$this->productTable) {
        $sm = $this->getServiceLocator();
        $this->productTable = $sm->get('Product\Model\ProductTable');
        }
        return $this->productTable;
    }

    public function getAttachmentTable()
    {
        if (!$this->attachmentTable) {
        $sm = $this->getServiceLocator();
        $this->attachmentTable = $sm->get('Attachment\Model\AttachmentTable');
        }
        return $this->attachmentTable;
    }

    public function getBarcodeTable()
    {
        if (!$this->barcodeTable) {
        $sm = $this->getServiceLocator();
        $this->barcodeTable = $sm->get('Attachment\Model\BarcodeTable');
        }
        return $this->barcodeTable;
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
