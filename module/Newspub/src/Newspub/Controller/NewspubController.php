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
    protected $pubmediaTable;
    protected $creditlogTable;
    protected $constantTable;

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
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);

        //handle the form
        $form = new NewspubForm();
        $form->get('submit')->setValue('创建订单');
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

                //对企业应收账款如下：
                /*if($newspub->fk_pub_mode == 1){
                    $price = count($newspub->sel_right) * 350;//单篇发布：每篇350元
                }else{
                    $price = 1500;//打包发布：一共1500元
                }                
                $fk_user = $this->getUserTable()->getUserByName($cur_user)->id;
                $is_sufficient = $this->getCreditTable()->issufficient($price, $fk_user);
                if(!$is_sufficient)
                {
                    echo "<a href='/newspub/add'>Back</a></br>";
                    die("Insufficient Credit! Please Charge Your Account!");
                }*/
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

                /*
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
                */

                $newspub2 = $this->getNewspubTable()->getNewspub($id_newspub);
                //$newspub2->barcode = $id_barcode;
                $newspub2->order_no = 11000000 + $newspub2->id_newspub;
                $this->getNewspubTable()->saveNewspub($newspub2);

                //update the user's credit
                /*$credit = $this->getCreditTable()->getCreditByFkUser($fk_user);
                //$originamount = $credit->amount;
                $origindeposit = $credit->deposit;
                //$credit->amount = $originamount - $price;
                $credit->deposit = $origindeposit + $price;                
                $credit->updated_at = $this->_getDateTime();
                $credit->updated_by = $cur_user;
                $this->getCreditTable()->saveCredit($credit);
                $credit2 = $this->getCreditTable()->getCredit($credit->id_credit);*/

                //create creditlog record;
                /*$creditlog = new Creditlog();
                $creditlog->fk_credit = $credit2->id_credit;
                if($newspub2->fk_pub_mode == 1){
                    $creditlog->fk_service_type = 2;//新闻发布->单篇发布
                }else{
                    $creditlog->fk_service_type = 3;//新闻发布->打包发布
                }
                $creditlog->fk_from = $fk_user;
                $creditlog->fk_to = null;
                $creditlog->date_time = $this->_getDateTime();
                $creditlog->amount = 0;//do not pay any money here
                $creditlog->remaining_balance = $credit2->amount;
                $creditlog->is_pay = 0;//is not pay
                $creditlog->is_charge = 0;//is not charge
                $creditlog->deposit = $price;
                $creditlog->is_pay_deposit = 0;//is not pay the deposit
                $creditlog->is_charge_deposit = 1;//is charge the deposit
                $creditlog->remaining_deposit = $credit2->deposit;
                $creditlog->order_no = $newspub2->order_no;
                $creditlog->created_at = $this->_getDateTime();
                $creditlog->created_by = $cur_user;
                $this->getCreditlogTable()->saveCreditlog($creditlog);*/

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
                    /*'action'=>'detail',
                    'id'    => $id_newspub,*/
                    'action' => 'index',
                ));
            }/*else{
                die(var_dump($form->getMessages()));
            }*/
        }

        //get the price
        $price_newspub_single = $this->getConstantTable()->getConstant(3)->value;
        $price_newspub_multiple = $this->getConstantTable()->getConstant(4)->value;

        return new ViewModel(array(
            'user' => $cur_user,
            'form' => $form,
            'newspub' => $this->getNewspubTable()->getNewspubByUser($cur_user),
            'products' => $this->getProductTable()->fetchProductByUser($cur_user),
            'js_products' => $this->getProductTable()->fetchProductByUser($cur_user),
            //'medias' => $this->getUserTable()->fetchUserByFkType(2),
            'medias' => $this->getPubmediaTable()->fetchAll(),
            'barcodes' => $this->getBarcodeTable()->fetchBarcodeByUser($cur_user),
            'price_newspub_single' => $price_newspub_single,
            'price_newspub_multiple' => $price_newspub_multiple,
        ));        
    }

    public function confirmAction()
    {
        //企业用户->订单详情->确认草稿状态的订单(将会冻结与服务价格等额的资金)
        $arr_type_allowed = array(1, 3, 4);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_newspub = (int)$this->params()->fromRoute('id',0);        
        if (!$id_newspub) {
            return $this->redirect()->toRoute('newspub', array(
                'action' => 'index'
            ));
        }
        $newspub = $this->getNewspubTable()->getNewspub($id_newspub);
        $target_user = $this->getUserTable()->getUserByName($newspub->created_by);
        $fk_user = $target_user->id;
        $credit = $this->getCreditTable()->getCreditByFkUser($fk_user);
        $count = $this->getNpmediaTable()->getCountNmByFkNewspub($id_newspub);
        //get the price
        $price_newspub_single = $this->getConstantTable()->getConstant(3)->value;
        $price_newspub_multiple = $this->getConstantTable()->getConstant(4)->value;
        //对企业应收账款如下：
        if($newspub->fk_pub_mode == 1){
            $price = $count * $price_newspub_single;//单篇发布
        }else{
            $price = $price_newspub_multiple;//打包发布
        }
        $is_sufficient = $this->getCreditTable()->issufficient($price, $fk_user);
        if(!$is_sufficient)
        {
            echo "<a href='/newspub/add'>Back</a></br>";
            die("Insufficient Credit! Please Charge Your Account!");
        }
        else
        {
            //update the $newspub, change the $fk_newspub_status to 2 (frozen)
            $newspub->fk_newspub_status = 2;
            $newspub->updated_by = $cur_user;
            $newspub->updated_at = $this->_getDateTime();
            $this->getNewspubTable()->saveNewspub($newspub);
            //update the $credit->deposit
            $origindeposit = $credit->deposit;
            $credit->deposit = $origindeposit + $price;
            $credit->updated_by = $cur_user;
            $credit->updated_at = $this->_getDateTime();
            $this->getCreditTable()->saveCredit($credit);
            //log the change
            $creditlog = new Creditlog();
            $creditlog->fk_credit = $credit->id_credit;
            if($newspub->fk_pub_mode == 1){
                $creditlog->fk_service_type = 2;//新闻发布->单篇发布->订单确认
            }else{
                $creditlog->fk_service_type = 3;//新闻发布->打包发布->订单确认
            }
            $creditlog->fk_from = $fk_user;
            $creditlog->fk_to = null;
            $creditlog->date_time = $this->_getDateTime();
            $creditlog->amount = 0;//do not pay any money here
            $creditlog->remaining_balance = $credit->amount;
            $creditlog->is_pay = 0;//is not pay
            $creditlog->is_charge = 0;//is not charge
            $creditlog->deposit = $price;
            $creditlog->remaining_deposit = $credit->deposit;
            $creditlog->is_pay_deposit = 0;//is not pay the deposit
            $creditlog->is_charge_deposit = 1;//is charge the deposit
            $creditlog->order_no = $newspub->order_no;
            $creditlog->created_at = $this->_getDateTime();
            $creditlog->created_by = $cur_user;
            $this->getCreditlogTable()->saveCreditlog($creditlog);

            return $this->redirect()->toRoute('newspub', array(
                'action'=>'detail',
                'id'    => $id_newspub,
            ));
        }

        

    }

    public function detailAction()
    {
        //$cur_user = $this->_authenticateSession(1);
        $arr_type_allowed = array(1, 3, 4);
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
            //$barcode_path = '/upload/'.$np->created_by.'/newspub/'.$id.'/'.$barcode->filename;
            $barcode_path = substr($barcode->path.$barcode->filename, 6);
        }
        else
        {
            $barcode_path = '#';
        }

        $price_newspub_single = $this->getConstantTable()->getConstant(3)->value;
        $price_newspub_multiple = $this->getConstantTable()->getConstant(4)->value;

        return new ViewModel(array(
            'np' => $np,
            'user' => $cur_user,
            'user_type' => $this->getUserTable()->getUserByName($cur_user)->fk_user_type,
            'all_users' => $this->getUserTable()->fetchAll(),
            'id' => $id,
            'product' => $this->getProductTable()->getProduct($np->fk_product),
            'barcode_path' => $barcode_path,
            'npmedia' => $this->getNpmediaTable()->fetchNpmediaByFkNewspub($id),
            'newspub' => $np,
            'pubmedias' => $this->getPubmediaTable()->fetchAll(),
            'price_newspub_single' => $price_newspub_single,
            'price_newspub_multiple' => $price_newspub_multiple,
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
        $owner = $this->getUserTable()->getUserByName($newspub->created_by);
        if($newspub->barcode)
        {
            $barcode = $this->getBarcodeTable()->getBarcode($newspub->barcode);
            //$barcode_path = '/upload/'.$owner->username.'/newspub/'.$id_newspub.'/'.$barcode->filename;
            $barcode_path = substr($barcode->path.$barcode->filename, 6);
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
            /*else
            {
                die(var_dump($form->getMessages()));
            }*/
        }
        return new ViewModel(array(
            'np'      => $this->getNewspubTable()->getNewspub($id_newspub),
            'user'    => $cur_user,
            'form'    => $form,
            'id'      => $id_newspub,
            'product' => $product,
            'barcode_path' => $barcode_path,
            'user_type' => $this->getUserTable()->getUserByName($cur_user)->fk_user_type,
        ));
    }

    public function adminAction()
    {
        //管理员->订单管理->新闻发布
        $arr_type_allowed = array(3, 4);
        $cur_user = $this->_auth($arr_type_allowed);


        return new ViewModel(array(
            'user' => $cur_user,
            'newspubs' => $this->getNewspubTable()->fetchAllDesc(),
        ));
    }

    public function addnpmediaAction()
    {
        //管理员用户->订单详情->选择媒体并发布新闻链接
        $arr_type_allowed = array(3, 4);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_newspub = (int)$this->params()->fromRoute('id', 0);
        if(!$id_newspub){
            return $this->redirect()->toRoute('newspub', array(
                'action' => 'admin',
            ));
        }
        $newspub = $this->getNewspubTable()->getNewspub($id_newspub);

        $form = new NpmediaForm();
        $form->get('submit')->setAttribute('value','保存并发布');
        $request = $this->getRequest();
        if($request->isPost()){
            $npmedia = new Npmedia();
            $form->setInputFilter($npmedia->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $npmedia->exchangeArray($form->getData());
                $npmedia->fk_newspub = $id_newspub;
                $npmedia->created_at = $this->_getDateTime();
                $npmedia->created_by = $cur_user;
                $npmedia->fk_npmedia_status = 4;
                $npmedia->updated_at = $this->_getDateTime();
                $npmedia->updated_by = $cur_user;
                $this->getNpmediaTable()->saveNpmedia($npmedia);

                return $this->redirect()->toRoute('newspub',array(
                    'action'=>'detail',
                    'id'    => $id_newspub,
                ));
            }
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'newspub' => $newspub,
            'all_media_users' => $this->getUserTable()->fetchUserByFkType(2),
            'form' => $form,
            'pubmedias' => $this->getPubmediaTable()->fetchAll(),
        ));
    }

    public function scoreAction()
    {
        //企业用户->订单详情->评分
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_npmedia = (int)$this->params()->fromRoute('id',0);        
        if (!$id_npmedia) {
            return $this->redirect()->toRoute('newspub', array(
                'action' => 'index',
            ));
        }
        $npmedia = $this->getNpmediaTable()->getNpmedia($id_npmedia);
        $newspub = $this->getNewspubTable()->getNewspub($npmedia->fk_newspub);
        $np_fk_newspub = $npmedia->fk_newspub;
        $np_fk_media_user = $npmedia->fk_media_user;
        $np_created_at = $npmedia->created_at;
        $np_created_by = $npmedia->created_by;        
        $np_fk_npmedia_status = $npmedia->fk_npmedia_status;
        $np_news_link = $npmedia->news_link;
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
                $form->getData()->fk_npmedia_status = $np_fk_npmedia_status;
                $form->getData()->news_link = $np_news_link;
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
            'id_npmedia' => $id_npmedia,

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

    public function remittanceAction()
    {
        //管理员->新闻发布->确认付款
        //(从用户被冻结的资金中扣除与服务价格等值的资金)
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_newspub = (int)$this->params()->fromRoute('id',0);        
        if (!$id_newspub) {
            return $this->redirect()->toRoute('newspub', array(
                'action' => 'admin'
            ));
        }

        $newspub = $this->getNewspubTable()->getNewspub($id_newspub);
        $fk_user = $this->getUserTable()->getUserByName($newspub->created_by)->id;
        $credit = $this->getCreditTable()->getCreditByFkUser($fk_user);
        if($newspub->fk_pub_mode == 1){
            $count = $this->getNpmediaTable()->getCountNmByFkNewspub($id_newspub);
            $price = 350 * $count;
        }elseif($newspub->fk_pub_mode == 2){
            $price = 1500;
        }
        //update the $newspub, change the fk_newspub_status to 4
        $newspub->fk_newspub_status = 4;//账款结清
        $newspub->updated_by = $cur_user;
        $newspub->updated_at = $this->_getDateTime();
        $this->getNewspubTable()->saveNewspub($newspub);
        //update the credit
        $originamount = $credit->amount;
        $origindeposit = $credit->deposit;
        $credit->amount = $originamount - $price;
        $credit->deposit = $origindeposit - $price;
        $credit->updated_by = $cur_user;
        $credit->updated_at = $this->_getDateTime();
        $this->getCreditTable()->saveCredit($credit);
        //log the change of the amount
        $creditlog = New Creditlog();
        $creditlog->fk_credit = $credit->id_credit;
        $creditlog->fk_service_type = 10;//新闻发布->单篇发布->结账
        $creditlog->fk_from = $fk_user;
        $creditlog->fk_to = null;
        $creditlog->date_time = $this->_getDateTime();
        $creditlog->amount = $price;
        $creditlog->remaining_balance = $credit->amount;
        $creditlog->is_pay = 1;//is pay
        $creditlog->is_charge = 0; //is not charge
        $creditlog->deposit = $price;
        $creditlog->remaining_deposit = $credit->deposit;
        $creditlog->is_pay_deposit = 1;//is pay the deposit
        $creditlog->is_charge_deposit = 0;//is charge the deposit
        $creditlog->order_no = $newspub->order_no;
        $creditlog->created_at = $this->_getDateTime();
        $creditlog->created_by = $cur_user;
        $this->getCreditlogTable()->saveCreditlog($creditlog);


        return $this->redirect()->toRoute('newspub', array(
            'action' => 'admin',
        ));

        return new ViewModel(array(
            'user' => $cur_user,
        ));
    }

    public function doneAction()
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
        $newspub->fk_newspub_status = 3;//订单工作任务完成
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
        $arr_type_allowed = array(3, 4);
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
        $arr_type_allowed = array(3, 4);
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
        $newspub = $this->getNewspubTable()->getNewspub($npmedia->fk_newspub);
        $target_user = $this->getUserTable()->getUserByName($newspub->created_by);
        $fk_user = $target_user->id;
        //get the price
        $price_newspub_single = $this->getConstantTable()->getConstant(3)->value;
        //check if the whole order has been finished
        $is_completed = $this->getNpmediaTable()->is_completed($newspub->id_newspub);
        if($is_completed)
        {
            //update the newspub, change the status to 4
            $newspub->fk_newspub_status = 4;
            $newspub->updated_by = $cur_user;
            $newspub->updated_at = $this->_getDateTime();
            $this->getNewspubTable()->saveNewspub($newspub);
            //return the money to the enterprise user where the npmedia orders were canceled
            $canceled_npmedias = $this->getNpmediaTable()->fetchCanceledNpmediaByFkNewspub($newspub->id_newspub);
            $price = $price_newspub_single * count($canceled_npmedias);
            //update the credit, 
            $credit = $this->getCreditTable()->getCreditByFkUser($fk_user);
            $origindeposit = $credit->deposit;
            $credit->deposit = $origindeposit - $price;
            $credit->updated_at = $this->_getDateTime();
            $credit->updated_by = $cur_user;
            $this->getCreditTable()->saveCredit($credit);
            //log the change
            $creditlog = new Creditlog();
            $creditlog->fk_credit = $credit->id_credit;
            $creditlog->fk_service_type = 10;//新闻发布->单篇发布->订单结束
            $creditlog->fk_from = null;
            $creditlog->fk_to = $fk_user;
            $creditlog->date_time = $this->_getDateTime();
            $creditlog->amount = 0;
            $creditlog->remaining_balance = $credit->amount;
            $creditlog->is_pay = 0;//is not pay
            $creditlog->is_charge = 0; //is not charge
            $creditlog->deposit = $price;
            $creditlog->remaining_deposit = $credit->deposit;
            $creditlog->is_pay_deposit = 1;//is pay the deposit
            $creditlog->is_charge_deposit = 0;//is charge the deposit
            $creditlog->order_no = $newspub->order_no;
            $creditlog->created_at = $this->_getDateTime();
            $creditlog->created_by = $cur_user;
            $this->getCreditlogTable()->saveCreditlog($creditlog);                    
        }

        return $this->redirect()->toRoute('newspub', array(
            'action' => 'detail',
            'id'     => $npmedia->fk_newspub,
        ));
    }

    public function multiplefinishAction()
    {
        //管理员用户->打包发布->结束订单
        $arr_type_allowed = array(3, 4);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_newspub = (int)$this->params()->fromRoute('id',0);        
        if (!$id_newspub) {
            return $this->redirect()->toRoute('newspub', array(
                'action' => 'admin',
            ));
        }
        $newspub = $this->getNewspubTable()->getNewspub($id_newspub);
        $target_user = $this->getUserTable()->getUserByName($newspub->created_by);
        $fk_user = $target_user->id;
        $price = 1500;
        //change the $newspub->fk_newspub_status to 4 (remittance)
        $newspub->fk_newspub_status = 4;
        $newspub->updated_by = $cur_user;
        $newspub->updated_at = $this->_getDateTime();
        $this->getNewspubTable()->saveNewspub($newspub);
        //update the credit, 
        $credit = $this->getCreditTable()->getCreditByFkUser($fk_user);
        $originamount = $credit->amount;
        $origindeposit = $credit->deposit;
        $credit->amount = $originamount - $price;
        $credit->deposit = $origindeposit - $price;
        $credit->updated_at = $this->_getDateTime();
        $credit->updated_by = $cur_user;
        $this->getCreditTable()->saveCredit($credit);
        //log the change
        $creditlog = New Creditlog();
        $creditlog->fk_credit = $credit->id_credit;
        $creditlog->fk_service_type = 5;//新闻发布->多篇发布->结账
        $creditlog->fk_from = $fk_user;
        $creditlog->fk_to = null;
        $creditlog->date_time = $this->_getDateTime();
        $creditlog->amount = $price;
        $creditlog->remaining_balance = $credit->amount;
        $creditlog->is_pay = 1;//is pay
        $creditlog->is_charge = 0; //is not charge
        $creditlog->deposit = $price;
        $creditlog->remaining_deposit = $credit->deposit;
        $creditlog->is_pay_deposit = 1;//is pay the deposit
        $creditlog->is_charge_deposit = 0;//is charge the deposit
        $creditlog->order_no = $newspub->order_no;
        $creditlog->created_at = $this->_getDateTime();
        $creditlog->created_by = $cur_user;
        $this->getCreditlogTable()->saveCreditlog($creditlog);

        return $this->redirect()->toRoute('newspub', array(
            'action' => 'detail',
            'id'     => $id_newspub,
        ));
    }

    public function publishAction()
    {
        //管理员用户->发布
        $arr_type_allowed = array(3, 4);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_npmedia = (int)$this->params()->fromRoute('id',0);        
        if (!$id_npmedia) {
            return $this->redirect()->toRoute('newspub', array(
                'action' => 'admin',
            ));
        }

        //get the price
        $price_newspub_single = $this->getConstantTable()->getConstant(3)->value;
        $price_newspub_multiple = $this->getConstantTable()->getConstant(4)->value;

        $npmedia = $this->getNpmediaTable()->getNpmedia($id_npmedia);
        $newspub = $this->getNewspubTable()->getNewspub($npmedia->fk_newspub);
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

                $target_user = $this->getUserTable()->getUserByName($npmedia->created_by);
                $fk_user = $target_user->id;
                $credit = $this->getCreditTable()->getCreditByFkUser($fk_user);
                $origindeposit = $credit->deposit;
                $originamount  = $credit->amount;
                $price = $price_newspub_single;
                $credit->deposit = $origindeposit - $price;
                $credit->amount  = $originamount - $price;
                $credit->updated_by = $cur_user;
                $credit->updated_at = $this->_getDateTime();
                $this->getCreditTable()->saveCredit($credit);

                //log the change of the amount
                $creditlog = New Creditlog();
                $creditlog->fk_credit = $credit->id_credit;
                $creditlog->fk_service_type = 6;//新闻发布->单篇发布->结账
                $creditlog->fk_from = $fk_user;
                $creditlog->fk_to = null;
                $creditlog->date_time = $this->_getDateTime();
                $creditlog->amount = $price;
                $creditlog->remaining_balance = $credit->amount;
                $creditlog->is_pay = 1;//is pay
                $creditlog->is_charge = 0; //is not charge
                $creditlog->deposit = $price;
                $creditlog->remaining_deposit = $credit->deposit;
                $creditlog->is_pay_deposit = 1;//is pay the deposit
                $creditlog->is_charge_deposit = 0;//is charge the deposit
                $creditlog->order_no = $newspub->order_no;
                $creditlog->created_at = $this->_getDateTime();
                $creditlog->created_by = $cur_user;
                $this->getCreditlogTable()->saveCreditlog($creditlog);

                //check if the whole order has been finished
                $is_completed = $this->getNpmediaTable()->is_completed($newspub->id_newspub);
                if($is_completed)
                {
                    //update the newspub, change the status to 4
                    $newspub->fk_newspub_status = 4;
                    $newspub->updated_by = $cur_user;
                    $newspub->updated_at = $this->_getDateTime();
                    $this->getNewspubTable()->saveNewspub($newspub);
                    //return the money to the enterprise user where the npmedia orders were canceled
                    $canceled_npmedias = $this->getNpmediaTable()->fetchCanceledNpmediaByFkNewspub($newspub->id_newspub);
                    $price = $price_newspub_single * count($canceled_npmedias);
                    //update the credit, 
                    $credit = $this->getCreditTable()->getCreditByFkUser($fk_user);
                    $origindeposit = $credit->deposit;
                    $credit->deposit = $origindeposit - $price;
                    $credit->updated_at = $this->_getDateTime();
                    $credit->updated_by = $cur_user;
                    $this->getCreditTable()->saveCredit($credit);
                    //log the change
                    $creditlog = new Creditlog();
                    $creditlog->fk_credit = $credit->id_credit;
                    $creditlog->fk_service_type = 10;//新闻发布->单篇发布->订单结束
                    $creditlog->fk_from = null;
                    $creditlog->fk_to = $fk_user;
                    $creditlog->date_time = $this->_getDateTime();
                    $creditlog->amount = 0;
                    $creditlog->remaining_balance = $credit->amount;
                    $creditlog->is_pay = 0;//is not pay
                    $creditlog->is_charge = 0; //is not charge
                    $creditlog->deposit = $price;
                    $creditlog->remaining_deposit = $credit->deposit;
                    $creditlog->is_pay_deposit = 1;//is pay the deposit
                    $creditlog->is_charge_deposit = 0;//is charge the deposit
                    $creditlog->order_no = $newspub->order_no;
                    $creditlog->created_at = $this->_getDateTime();
                    $creditlog->created_by = $cur_user;
                    $this->getCreditlogTable()->saveCreditlog($creditlog);                    
                }

                return $this->redirect()->toRoute('newspub', array(
                    'action' => 'detail',
                    'id'     => $npmedia->fk_newspub,
                ));
            }
            else
            {
                die(var_dump($form->getMessages()));
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
        //管理员用户或企业用户->企业取消
        $arr_type_allowed = array(1, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_npmedia = (int)$this->params()->fromRoute('id',0);        
        if (!$id_npmedia) {
            return $this->redirect()->toRoute('/');
        }

        //get the price
        $price_newspub_single = $this->getConstantTable()->getConstant(3)->value;
        $price_newspub_multiple = $this->getConstantTable()->getConstant(4)->value;

        $npmedia = $this->getNpmediaTable()->getNpmedia($id_npmedia);
        $npmedia->fk_npmedia_status = 5;
        $npmedia->updated_at = $this->_getDateTime();
        $npmedia->updated_by = $cur_user;
        $this->getNpmediaTable()->saveNpmedia($npmedia);
        $newspub = $this->getNewspubTable()->getNewspub($npmedia->fk_newspub);
        $target_user = $this->getUserTable()->getUserByName($newspub->created_by);
        $fk_user = $target_user->id;

        //check if the whole order has been finished
        $is_completed = $this->getNpmediaTable()->is_completed($newspub->id_newspub);
        if($is_completed)
        {
            //update the newspub, change the status to 4
            $newspub->fk_newspub_status = 4;
            $newspub->updated_by = $cur_user;
            $newspub->updated_at = $this->_getDateTime();
            $this->getNewspubTable()->saveNewspub($newspub);
            //return the deposit to the enterprise user where the npmedia orders were canceled
            $canceled_npmedias = $this->getNpmediaTable()->fetchCanceledNpmediaByFkNewspub($newspub->id_newspub);
            $price = $price_newspub_single * count($canceled_npmedias);
            //update the credit, 
            $credit = $this->getCreditTable()->getCreditByFkUser($fk_user);
            $origindeposit = $credit->deposit;
            $credit->deposit = $origindeposit - $price;
            $credit->updated_at = $this->_getDateTime();
            $credit->updated_by = $cur_user;
            $this->getCreditTable()->saveCredit($credit);
            //log the change
            $creditlog = new Creditlog();
            $creditlog->fk_credit = $credit->id_credit;
            $creditlog->fk_service_type = 10;//新闻发布->单篇发布->订单结束
            $creditlog->fk_from = null;
            $creditlog->fk_to = $fk_user;
            $creditlog->date_time = $this->_getDateTime();
            $creditlog->amount = 0;
            $creditlog->remaining_balance = $credit->amount;
            $creditlog->is_pay = 0;//is not pay
            $creditlog->is_charge = 0; //is not charge
            $creditlog->deposit = $price;
            $creditlog->remaining_deposit = $credit->deposit;
            $creditlog->is_pay_deposit = 1;//is pay the deposit
            $creditlog->is_charge_deposit = 0;//is charge the deposit
            $creditlog->order_no = $newspub->order_no;
            $creditlog->created_at = $this->_getDateTime();
            $creditlog->created_by = $cur_user;
            $this->getCreditlogTable()->saveCreditlog($creditlog);                    
        }

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

    public function getPubmediaTable()
    {
        if(!$this->pubmediaTable){
            $sm = $this->getServiceLocator();
            $this->pubmediaTable = $sm->get('Media\Model\PubmediaTable');
        }
        return $this->pubmediaTable;
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

    public function getConstantTable()
    {
        if(!$this->constantTable){
            $sm = $this->getServiceLocator();
            $this->constantTable = $sm->get('Credit\Model\ConstantTable');
        }
        return $this->constantTable;
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
