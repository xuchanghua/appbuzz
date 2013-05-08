<?php
namespace Writer\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Writer\Model\Writer;          // <-- Add this import
use Writer\Form\WriterForm;       // <-- Add this import
use Writer\Form\WrtmediaForm;
use Zend\Session\Container as SessionContainer;
use User\Model\User;
use Zend\File\Transfer\Adapter\Http as FileHttp;
use Zend\Validator\File\Size as FileSize;
use Zend\Validator\File\Extension as FileExt;
use DateTime;
use Writer\Model\Wrtmedia;
use Attachment\Model\Barcode;
use Attachment\Model\Screenshot;
use Credit\Model\Credit;
use Credit\Model\Creditlog;

class WriterController extends AbstractActionController
{
    protected $writerTable;
    protected $userTable;
    protected $productTable;
    protected $wrtmediaTable;
    protected $barcodeTable;
    protected $screenshotTable;
    protected $creditTable;
    protected $creditlogTable;

    public function indexAction()
    {     
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);

        $request = $this->getRequest();
        $keyword = trim($request->getQuery(''));
        $page = intval($request->getQuery('page',1));
        $paginator = $this->getWriterTable()->getPaginator($keyword, $page, 5, 1, $cur_user);
        $view = new ViewModel(array(
            'user' => $cur_user,
            'writer' => $this->getWriterTable()->fetchWriterByUser($cur_user),
            'products' => $this->getProductTable()->fetchAll(),
            )
        );
        $view->setVariable('paginator', $paginator);
        return $view;
    }

    public function adminAction()
    {
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);

        return new ViewModel(array(
            'user' => $cur_user,
            //'writer' => $this->getWriterTable()->fetchAll(),
            //'wrtmedia' => $this->getWrtmediaTable()->fetchAllDesc(),
            'products' => $this->getProductTable()->fetchAll(),
            'all_users' => $this->getUserTable()->fetchAll(),
            'wrtjoinwm' => $this->getWriterTable()->fetchAllJoinLeftWrtmediaDesc(),
        ));
    }

    public function neworderAction()
    {
        $cur_user = $this->_authenticateSession(1);

        if(!$id_product = (int)$this->params()->fromQuery('id_product', 0))
        {
            $id_product = $this->getRequest()->getPost()->fk_product;
        }
        $product = $this->getProductTable()->getProduct($id_product);
        $writer = new Writer();
        $writer->fk_product = $product->id_product;
        $writer->web_link = $product->web_link;
        $writer->appstore_link = $product->appstore_link;
        $writer->androidmkt_link = $product->androidmkt_link;

        $form = new WriterForm();
        $form->bind($writer);
        $form->get('submit')->setAttribute('value', '保存');

        $request = $this->getRequest();
        if($request->isPost()){
            $form->setInputFilter($writer->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $form->getData()->created_by = $cur_user;
                $form->getData()->created_at = $this->_getDateTime();
                $form->getData()->updated_by = $cur_user;
                $form->getData()->updated_at = $this->_getDateTime();
                $form->getData()->fk_product = $id_product;
                $this->getWriterTable()->saveWriter($form->getData());   
                $id_writer = $this->getWriterTable()->getId(
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
                $path_2    = $path_1.'writer/';
                $path_full = $path_2.$id_writer.'/';
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

                $writer2 = $this->getWriterTable()->getWriter($id_writer);
                $writer2->barcode = $id_barcode;
                $this->getWriterTable()->saveWriter($writer2);

                return $this->redirect()->toRoute('writer',array(
                    'action'=>'detail',
                    'id'    => $id_writer,
                ));
            }
        }

        return new ViewModel(array(
            'product' => $product,
            'user' => $cur_user,
            'form' => $form,
        ));
    }

    public function detailAction()
    {
        $arr_type_allowed = array(1, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id = (int)$this->params()->fromRoute('id',0);        
        if (!$id) {
            return $this->redirect()->toRoute('writer', array(
                'action' => 'index'
            ));
        }
        $writer = $this->getWriterTable()->getWriter($id);
        $owner = $this->getUserTable()->getUserByName($writer->created_by);
        if($writer->barcode)
        {
            $barcode = $this->getBarcodeTable()->getBarcode($writer->barcode);
            $barcode_path = '/upload/'.$owner->username.'/writer/'.$id.'/'.$barcode->filename;
        }
        else
        {
            $barcode_path = '#';
        }

        /*$arr_media_assignees = array();
        $wrtmedia = $this->getWrtmediaTable()->fetchWrtmediaByFkWrt($id);
        if($wrtmedia)
        {
            foreach ($wrtmedia as $wm)
            {
                $media_user = $this->getUserTable()->getUser($wm->fk_media_user);
                $arr_media_assignees[] = $media_user->username;
            }
        }*/

        return new ViewModel(array(
            'writer' => $writer,
            'user' => $cur_user,
            'user_type' => $this->getUserTable()->getUserByName($cur_user)->fk_user_type,
            'allusers' => $this->getUserTable()->fetchAll(),
            'id' => $id,
            'product' => $this->getProductTable()->getProduct($writer->fk_product),
            //'media_assignees' => $arr_media_assignees,
            'wrtmedias' => $this->getWrtmediaTable()->fetchWmExRejByMedByFkWrt($id),//not include those rejected by the media
            'barcode_path' => $barcode_path,
            'screenshots' => $this->getScreenshotTable()->fetchScreenshotByFkWrt($id),
        ));
    }    

    public function editAction()
    {
        $arr_type_allowed = array(1, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_writer = (int)$this->params()->fromRoute('id',0);
        if(!$id_writer){
            return $this->redirect()->toRoute('writer', array(
                'action' => 'index',
            ));
        }
        $writer = $this->getWriterTable()->getWriter($id_writer);
        $owner = $this->getUserTable()->getUserByName($writer->created_by);
        if($writer->barcode)
        {
            $barcode = $this->getBarcodeTable()->getBarcode($writer->barcode);
            $barcode_path = '/upload/'.$owner->username.'/writer/'.$id_writer.'/'.$barcode->filename;
        }
        else
        {
            $barcode_path = '#';
        }
        $product = $this->getProductTable()->getProduct($writer->fk_product);
        $wrt_created_by = $writer->created_by;
        $wrt_created_at = $writer->created_at;
        $wrt_barcode    = $writer->barcode;
        $wrt_order_no   = $writer->order_no;
        $form = new WriterForm();
        $form->bind($writer);
        $form->get('submit')->setAttribute('value','保存');

        $request = $this->getRequest();
        if($request->isPost()){   
            //upload start
            //1. barcode
            $file = $this->params()->fromFiles('barcode');
            if(!$file['name'])
            {
                //if the barcode is not pick up:
                //skip the upload section
            }
            else
            {
                $max = 400000;
                $sizeObj = new FileSize(array("max"=>$max));
                $extObj = new FileExt(array("jpg", "gif", "png"));
                $adapter = new FileHttp();
                $adapter->setValidators(array($sizeObj, $extObj), $file['name']);
                if(!$adapter->isValid()){
                    echo implode("\n", $dataError = $adapter->getMessages());
                }else{
                    //check if the path exists
                    //path format: /public/upload/user_name/module_name/id_module_name/
                    $path_0    = 'public/upload/';
                    $path_1    = $path_0.$owner->username.'/';
                    $path_2    = $path_1.'writer/';
                    $path_full = $path_2.$id_writer.'/';
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
            //2. screenshot
            $screen_shot = $this->params()->fromFiles('screen_shot');
            foreach($screen_shot as $ss)
            {
                $adapter = new FileHttp();
                $path_0    = 'public/upload/';
                $path_1    = $path_0.$owner->username.'/';
                $path_2    = $path_1.'writer/';
                $path_3    = $path_2.$id_writer.'/';
                $path_full = $path_3.'screenshot/';
                if(!is_dir($path_1))
                {
                    mkdir($path_1);
                }
                if(!is_dir($path_2))
                {
                    mkdir($path_2);
                }
                if(!is_dir($path_3))
                {
                    mkdir($path_3);
                }
                if(!is_dir($path_full))
                {
                    mkdir($path_full);
                }
                $adapter->setDestination($path_full);
                if(!$adapter->receive($ss['name'])){
                    echo implode("\n", $adapter->getMessages());
                }
                else
                {
                    //create a record in the table 'screenshot'
                    $screenshot = new Screenshot();
                    $screenshot->filename = $ss['name'];
                    $screenshot->path = $path_full;
                    $screenshot->fk_writer = $id_writer;
                    $screenshot->created_by = $cur_user;
                    $screenshot->created_at = $this->_getDateTime();
                    $this->getScreenshotTable()->saveScreenshot($screenshot);
                }   
                unset($adapter);
            }
            //upload end
            $form->setInputFilter($writer->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $form->getData()->created_by = $wrt_created_by;
                $form->getData()->created_at = $wrt_created_at;
                $form->getData()->order_no   = $wrt_order_no;
                $form->getData()->updated_by = $cur_user;
                $form->getData()->updated_at = $this->_getDateTime();
                if(isset($id_barcode))
                {
                    $form->getData()->barcode = $id_barcode;
                }
                else
                {
                    $form->getData()->barcode = $wrt_barcode;
                }
                $this->getWriterTable()->saveWriter($form->getData());

                return $this->redirect()->toRoute('writer',array(
                    'action' => 'detail',
                    'id'     => $id_writer,
                ));
            }
        }

        /*
        $arr_media_assignees = array();
        $wrtmedia = $this->getWrtmediaTable()->fetchWrtmediaByFkWrt($id);
        if($wrtmedia)
        {
            foreach ($wrtmedia as $wm)
            {
                $media_user = $this->getUserTable()->getUser($wm->fk_media_user);
                $arr_media_assignees[] = $media_user->username;
            }
        }
        */

        return new ViewModel(array(
            //'writer' => $this->getWriterTable()->getWriter($id),
            'order_no' => $wrt_order_no,
            'user'     => $cur_user,
            'user_type' => $this->getUserTable()->getUserByName($cur_user)->fk_user_type,
            'form'     => $form,
            'id'       => $id_writer,
            'product'  => $product,
            //'media_assignees' => $arr_media_assignees,
            'barcode_path' => $barcode_path,            
            'screenshots' => $this->getScreenshotTable()->fetchScreenshotByFkWrt($id_writer),
        ));
    }

    public function addAction()
    {
        //$cur_user = $this->_authenticateSession(1);
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);

        //handle the form
        $form = new WriterForm();
        $form->get('submit')->setValue('保存');
        $request = $this->getRequest();
        if($request->isPost()){
            $writer = new Writer();
            $form->setInputFilter($writer->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $writer->exchangeArray($form->getData());

                /*$price = 1500;//对企业用户应收1500元的新闻撰写费用
                $fk_user = $this->getUserTable()->getUserByName($cur_user)->id;
                $is_sufficient = $this->getCreditTable()->issufficient($price, $fk_user);
                if(!$is_sufficient)
                {
                    echo "<a href='/writer/add'>Back</a></br>";
                    die("Insufficient Credit! Please Charge Your Account!");
                }*/

                $writer->fk_writer_status = 1; //draft deal.
                $writer->created_by = $cur_user;
                $writer->created_at = $this->_getDateTime();
                $writer->updated_by = $cur_user;
                $writer->updated_at = $this->_getDateTime();
                $this->getWriterTable()->saveWriter($writer);
                $id_writer = $this->getWriterTable()->getId(
                        $writer->created_at,
                        $writer->created_by
                    );

                //upload start
                //1. barcode
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
                    $path_2    = $path_1.'writer/';
                    $path_full = $path_2.$id_writer.'/';
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
                //2. screen shot
                $screen_shot = $this->params()->fromFiles('screen_shot');
                foreach($screen_shot as $ss)
                {
                    $adapter = new FileHttp();
                        $path_0    = 'public/upload/';
                        $path_1    = $path_0.$cur_user.'/';
                        $path_2    = $path_1.'writer/';
                        $path_3    = $path_2.$id_writer.'/';
                        $path_full = $path_3.'screenshot/';
                        if(!is_dir($path_1))
                        {
                            mkdir($path_1);
                        }
                        if(!is_dir($path_2))
                        {
                            mkdir($path_2);
                        }
                        if(!is_dir($path_3))
                        {
                            mkdir($path_3);
                        }
                        if(!is_dir($path_full))
                        {
                            mkdir($path_full);
                        }
                        $adapter->setDestination($path_full);
                        if(!$adapter->receive($ss['name'])){
                            echo implode("\n", $adapter->getMessages());
                        }
                        else
                        {
                            //create a record in the table 'screenshot'
                            $screenshot = new Screenshot();
                            $screenshot->filename = $ss['name'];
                            $screenshot->path = $path_full;
                            $screenshot->fk_writer = $id_writer;
                            $screenshot->created_by = $cur_user;
                            $screenshot->created_at = $this->_getDateTime();
                            $this->getScreenshotTable()->saveScreenshot($screenshot);
                        }
                    unset($adapter);
                }
                //upload end

                $writer2 = $this->getWriterTable()->getWriter($id_writer);
                $writer2->barcode = $id_barcode;
                $writer2->order_no = 31000000 + $id_writer;
                $this->getWriterTable()->saveWriter($writer2);

                //update the user's credit
                //$credit = $this->consume($fk_user, $price);

                //create creditlog record;
                /*$creditlog = new Creditlog();
                $creditlog->fk_credit = $credit->id_credit;
                $creditlog->fk_service_type = 6;//企业->我要撰稿
                $creditlog->fk_from = $fk_user;
                $creditlog->fk_to = null;
                $creditlog->date_time = $this->_getDateTime();
                $creditlog->amount = $price;
                $creditlog->is_pay = 1;//is pay
                $creditlog->is_charge = 0;//not charge
                $creditlog->order_no = $writer2->order_no;
                $creditlog->created_at = $this->_getDateTime();
                $creditlog->created_by = $cur_user;
                $this->getCreditlogTable()->saveCreditlog($creditlog);*/

                return $this->redirect()->toRoute('writer',array(
                    'action'=>'detail',
                    'id'    => $id_writer,
                ));
            }
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'form' => $form,
            'writer' => $this->getWriterTable()->fetchWriterByUser($cur_user),
            'products' => $this->getProductTable()->fetchProductByUser($cur_user),
            'js_products' => $this->getProductTable()->fetchProductByUser($cur_user),
        ));                
    }

    public function confirmAction()
    {
        $arr_type_allowed = array(1, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_writer = (int)$this->params()->fromRoute('id',0);        
        if (!$id_writer) {
            return $this->redirect()->toRoute('writer', array(
                'action' => 'index'
            ));
        }
        $writer = $this->getWriterTable()->getWriter($id_writer);
        $target_user = $this->getUserTable()->getUserByName($writer->created_by);
        $fk_user = $target_user->id;
        $credit = $this->getCreditTable()->getCreditByFkUser($fk_user);

        //对企业用户应收$price = $order_limit * 1500元的撰稿费用
        $price = $writer->order_limit * 1500;
        $is_sufficient = $this->getCreditTable()->issufficient($price, $fk_user);
        if(!$is_sufficient)
        {
            echo "<a href='/evaluate/add'>Back</a></br>";
            die("Insufficient Credit! Please Charge Your Account!");
        }
        else
        {
            //update the $writer, change the $fk_writer_status to 2 (frozen)
            $writer->fk_writer_status = 2;
            $writer->updated_by = $cur_user;
            $writer->updated_at = $this->_getDateTime();
            $this->getWriterTable()->saveWriter($writer);
            //update the $credit->deposit
            $origindeposit = $credit->deposit;
            $credit->deposit = $origindeposit + $price;
            $credit->updated_by = $cur_user;
            $credit->updated_at = $this->_getDateTime();
            $this->getCreditTable()->saveCredit($credit);
            //log the changes
            $creditlog = new Creditlog();
            $creditlog->fk_credit = $credit->id_credit;
            $creditlog->fk_service_type = 6;//企业->我要撰稿
            $creditlog->fk_from = $fk_user;
            $creditlog->fk_to = null;
            $creditlog->date_time = $this->_getDateTime();
            $creditlog->amount = 0;//do not pay any money here
            $creditlog->remaining_balance = $credit->amount;
            $creditlog->is_pay = 0;//is not pay
            $creditlog->is_charge = 0; //is not charge
            $creditlog->deposit = $price;
            $creditlog->remaining_deposit = $credit->deposit;
            $creditlog->is_pay_deposit = 0;//is not pay the deposit
            $creditlog->is_charge_deposit = 1;//is charge the deposit
            $creditlog->order_no = $writer->order_no;
            $creditlog->created_at = $this->_getDateTime();
            $creditlog->created_by = $cur_user;
            $this->getCreditlogTable()->saveCreditlog($creditlog);

            return $this->redirect()->toRoute('writer', array(
                'action' => 'detail',
                'id'     => $id_writer,
            ));
        }
    }

    public function reqlistAction()
    {
        //媒体->自由撰稿人->需求列表
        $arr_type_allowed = array(2, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        return new ViewModel(array(
            'user' => $cur_user,
            'writer' => $this->getWriterTable()->fetchAllDesc(),
            'products' => $this->getProductTable()->fetchAll(),
            'wrtmedia' => $this->getWrtmediaTable()->fetchWrtmediaByUser($cur_user),
            'is_writer' => $this->getUserTable()->getUserByName($cur_user)->is_writer,
        ));
    }

    public function mgmtAction()
    {
        //媒体->自由撰稿人->撰稿管理
        $arr_type_allowed = array(2, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        return new ViewModel(array(
            'user' => $cur_user,
            'writer' => $this->getWriterTable()->fetchAllDesc(),
            'products' => $this->getProductTable()->fetchAll(),
            'wrtmedia' => $this->getWrtmediaTable()->fetchWrtmediaByUser($cur_user),
            'is_writer' => $this->getUserTable()->getUserByName($cur_user)->is_writer,
        ));
    }

    public function mediaaccAction()
    {
        //媒体->自由撰稿人->需求列表->接受订单
        $arr_type_allowed = array(2);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_writer = (int)$this->params()->fromRoute('id',0);        
        if (!$id_writer) {
            return $this->redirect()->toRoute('writer', array(
                'action' => 'reqlist'
            ));
        }
        $writer = $this->getWriterTable()->getWriter($id_writer);
        $product = $this->getProductTable()->getProduct($writer->fk_product);
        $media_user = $this->getUserTable()->getUserByName($cur_user);
        $enterprise_user = $this->getUserTable()->getUserByName($writer->created_by);

        $wrtmedia = new Wrtmedia();
        $wrtmedia->fk_writer = $id_writer;
        $wrtmedia->fk_media_user = $media_user->id;
        $wrtmedia->fk_enterprise_user = $enterprise_user->id;
        $wrtmedia->created_by = $cur_user;
        $wrtmedia->created_at = $this->_getDateTime();
        $wrtmedia->updated_by = $cur_user;
        $wrtmedia->updated_at = $this->_getDateTime();
        $wrtmedia->fk_wrtmedia_status = 3;//accept the order
        $this->getWrtmediaTable()->saveWrtmedia($wrtmedia);
        //save the order number
        $id_wrtmedia = $this->getWrtmediaTable()->getId($wrtmedia->created_at, $wrtmedia->created_by);
        $wrtmedia2 = $this->getWrtmediaTable()->getWrtmedia($id_wrtmedia);
        $wrtmedia2->order_no = 32000000 + $id_wrtmedia;
        $this->getWrtmediaTable()->saveWrtmedia($wrtmedia2);

        return $this->redirect()->toRoute('writer', array(
            'action' => 'reqlist',
        ));

        return new ViewModel(array(
            'user' => $cur_user,
            'is_writer' => $this->getUserTable()->getUserByName($cur_user)->is_writer,
        ));
    }

    public function mediarejAction()
    {
        //媒体->自由撰稿人->需求列表->拒绝订单
        $arr_type_allowed = array(2);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_writer = (int)$this->params()->fromRoute('id',0);        
        if (!$id_writer) {
            return $this->redirect()->toRoute('writer', array(
                'action' => 'reqlist'
            ));
        }

        $writer = $this->getWriterTable()->getWriter($id_writer);
        $product = $this->getProductTable()->getProduct($writer->fk_product);
        $media_user = $this->getUserTable()->getUserByName($cur_user);
        $enterprise_user = $this->getUserTable()->getUserByName($writer->created_by);

        $wrtmedia = new Wrtmedia();
        $wrtmedia->fk_writer = $id_writer;
        $wrtmedia->fk_media_user = $media_user->id;
        $wrtmedia->fk_enterprise_user = $enterprise_user->id;
        $wrtmedia->created_by = $cur_user;
        $wrtmedia->created_at = $this->_getDateTime();
        $wrtmedia->updated_by = $cur_user;
        $wrtmedia->updated_at = $this->_getDateTime();
        $wrtmedia->fk_wrtmedia_status = 2;//reject the order
        $this->getWrtmediaTable()->saveWrtmedia($wrtmedia);
        //save the order number
        $id_wrtmedia = $this->getWrtmediaTable()->getId($wrtmedia->created_at, $wrtmedia->created_by);
        $wrtmedia2 = $this->getWrtmediaTable()->getWrtmedia($id_wrtmedia);
        $wrtmedia2->order_no = 32000000 + $id_wrtmedia;
        $this->getWrtmediaTable()->saveWrtmedia($wrtmedia2);

        return $this->redirect()->toRoute('writer', array(
            'action' => 'reqlist',
        ));

        return new ViewModel(array(
            'user' => $cur_user,
            'is_writer' => $this->getUserTable()->getUserByName($cur_user)->is_writer,
        ));
    }

    public function entaccAction()
    {
        //企业->新闻撰稿->企业接受        
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_wrtmedia = (int)$this->params()->fromRoute('id',0);        
        if (!$id_wrtmedia) {
            return $this->redirect()->toRoute('writer', array(
                'action' => 'index'
            ));
        }

        $wrtmedia = $this->getWrtmediaTable()->getWrtmedia($id_wrtmedia);
        $wrtmedia->updated_by = $cur_user;
        $wrtmedia->updated_at = $this->_getDateTime();
        $wrtmedia->fk_wrtmedia_status = 5;//accept the order
        $this->getWrtmediaTable()->saveWrtmedia($wrtmedia);

        return $this->redirect()->toRoute('writer', array(
            'action' => 'detail',
            'id'     => $wrtmedia->fk_writer,
        ));

        return new ViewModel(array(
            'user' => $cur_user,
        ));
    }

    public function entrejAction()
    {
        //企业->新闻撰稿->企业拒绝        
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_wrtmedia = (int)$this->params()->fromRoute('id',0);        
        if (!$id_wrtmedia) {
            return $this->redirect()->toRoute('writer', array(
                'action' => 'index'
            ));
        }

        $wrtmedia = $this->getWrtmediaTable()->getWrtmedia($id_wrtmedia);
        $wrtmedia->updated_by = $cur_user;
        $wrtmedia->updated_at = $this->_getDateTime();
        $wrtmedia->fk_wrtmedia_status = 4;//reject the order
        $this->getWrtmediaTable()->saveWrtmedia($wrtmedia);

        return $this->redirect()->toRoute('writer', array(
            'action' => 'detail',
            'id'     => $wrtmedia->fk_writer,
        ));

        return new ViewModel(array(
            'user' => $cur_user,
        ));
    }

    public function wrtinfoAction()
    {
        //媒体->自由撰稿人->需求列表->查看撰稿订单信息
        $arr_type_allowed = array(2);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_writer = (int)$this->params()->fromRoute('id',0);        
        if (!$id_writer) {
            return $this->redirect()->toRoute('writer', array(
                'action' => 'reqlist'
            ));
        }
        $writer = $this->getWriterTable()->getWriter($id_writer);
        $wrtmedia = $this->getWrtmediaTable()->getWrtmediaByUserAndFkWrt($cur_user, $writer->id_writer);        
        if($writer->barcode)
        {
            $barcode = $this->getBarcodeTable()->getBarcode($writer->barcode);
            $barcode_path = '/upload/'.$writer->created_by.'/writer/'.$id_writer.'/'.$barcode->filename;
        }
        else
        {
            $barcode_path = '#';
        }

        return new ViewModel(array(
            'writer' => $writer,
            'user' => $cur_user,
            'id' => $id_writer,
            'product' => $this->getProductTable()->getProduct($writer->fk_product),
            'wrtmedia' => $wrtmedia,
            'barcode_path' => $barcode_path,
            'is_writer' => $this->getUserTable()->getUserByName($cur_user)->is_writer,
        ));
    }

    public function wrtinfoentAction()
    {
        //企业->撰稿管理->查看稿件(对于一稿、二稿提交的订单)
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_wrtmedia = (int)$this->params()->fromRoute('id',0);        
        if (!$id_wrtmedia) {
            return $this->redirect()->toRoute('writer', array(
                'action' => 'index'
            ));
        }
        $wrtmedia = $this->getWrtmediaTable()->getWrtmedia($id_wrtmedia);
        $id_writer = $wrtmedia->fk_writer;
        $writer = $this->getWriterTable()->getWriter($id_writer);        
        if($writer->barcode)
        {
            $barcode = $this->getBarcodeTable()->getBarcode($writer->barcode);
            $barcode_path = '/upload/'.$writer->created_by.'/writer/'.$id_writer.'/'.$barcode->filename;
        }
        else
        {
            $barcode_path = '#';
        }

        return new ViewModel(array(
            'writer' => $writer,
            'user' => $cur_user,
            'id' => $id_writer,
            'product' => $this->getProductTable()->getProduct($writer->fk_product),
            'wrtmedia' => $wrtmedia,
            'barcode_path' => $barcode_path,
        ));
    }

    public function firstdraftAction()
    {
        //媒体->自由撰稿人->编辑一稿              
        $arr_type_allowed = array(2);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_wrtmedia = (int)$this->params()->fromRoute('id',0);        
        if (!$id_wrtmedia) {
            return $this->redirect()->toRoute('writer', array(
                'action' => 'reqlist'
            ));
        }
        $wrtmedia = $this->getWrtmediaTable()->getWrtmedia($id_wrtmedia);
        $id_writer = $wrtmedia->fk_writer;
        $writer = $this->getWriterTable()->getWriter($id_writer);

        $wm_fk_writer = $wrtmedia->fk_writer;
        $wm_fk_enterprise_user = $wrtmedia->fk_enterprise_user;
        $wm_fk_media_user = $wrtmedia->fk_media_user;
        $wm_created_by = $wrtmedia->created_by;
        $wm_created_at = $wrtmedia->created_at;
        $wm_order_no = $wrtmedia->order_no;

        $form = new WrtmediaForm();
        $form->bind($wrtmedia);
        $form->get('submit')->setAttribute('value', '提交');

        $request = $this->getRequest();
        if($request->isPost()){
            $form->setInputFilter($wrtmedia->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                //keep the old values:
                $form->getData()->fk_writer = $wm_fk_writer;
                $form->getData()->fk_enterprise_user = $wm_fk_enterprise_user;
                $form->getData()->fk_media_user = $wm_fk_media_user;
                $form->getData()->created_by = $wm_created_by;
                $form->getData()->created_at = $wm_created_at;
                $form->getData()->order_no = $wm_order_no;
                //update the following values:
                $form->getData()->updated_by = $cur_user;
                $form->getData()->updated_at = $this->_getDateTime();
                $form->getData()->fk_wrtmedia_status = 6; //first_draft_submitted
                $this->getWrtmediaTable()->saveWrtmedia($form->getData());

                return $this->redirect()->toRoute('writer', array(
                    'action' => 'wrtinfo',
                    'id'     => $id_writer,
                ));
            }
        }

        if($writer->barcode)
        {
            $barcode = $this->getBarcodeTable()->getBarcode($writer->barcode);
            $barcode_path = '/upload/'.$writer->created_by.'/writer/'.$id_writer.'/'.$barcode->filename;
        }
        else
        {
            $barcode_path = '#';
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'form' => $form,
            'writer' => $writer,
            'wrtmedia' => $wrtmedia,
            'product' => $this->getProductTable()->getProduct($writer->fk_product),
            'barcode_path' => $barcode_path,
            'is_writer' => $this->getUserTable()->getUserByName($cur_user)->is_writer,
        ));
    }

    public function revisionAction()
    {
        //企业->转告管理->编辑修改意见              
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_wrtmedia = (int)$this->params()->fromRoute('id',0);        
        if (!$id_wrtmedia) {
            return $this->redirect()->toRoute('writer', array(
                'action' => 'index',
            ));
        }
        $wrtmedia = $this->getWrtmediaTable()->getWrtmedia($id_wrtmedia);
        $id_writer = $wrtmedia->fk_writer;
        $writer = $this->getWriterTable()->getWriter($id_writer);

        $wm_fk_writer          = $wrtmedia->fk_writer;
        $wm_fk_enterprise_user = $wrtmedia->fk_enterprise_user;
        $wm_fk_media_user      = $wrtmedia->fk_media_user;
        $wm_created_by         = $wrtmedia->created_by;
        $wm_created_at         = $wrtmedia->created_at;
        $wm_first_draft_title  = $wrtmedia->first_draft_title;
        $wm_first_draft_body   = $wrtmedia->first_draft_body;
        $wm_order_no           = $wrtmedia->order_no;

        $form = new WrtmediaForm();
        $form->bind($wrtmedia);
        $form->get('submit')->setAttribute('value', '提交');

        $request = $this->getRequest();
        if($request->isPost()){
            $form->setInputFilter($wrtmedia->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                //keep the old values:
                $form->getData()->fk_writer = $wm_fk_writer;
                $form->getData()->fk_enterprise_user = $wm_fk_enterprise_user;
                $form->getData()->fk_media_user = $wm_fk_media_user;
                $form->getData()->created_by = $wm_created_by;
                $form->getData()->created_at = $wm_created_at;
                $form->getData()->first_draft_title = $wm_first_draft_title;
                $form->getData()->first_draft_body = $wm_first_draft_body;
                $form->getData()->order_no = $wm_order_no;
                //update the following values:
                $form->getData()->updated_by = $cur_user;
                $form->getData()->updated_at = $this->_getDateTime();
                $form->getData()->fk_wrtmedia_status = 7; //first_draft_awaiting_correction
                $this->getWrtmediaTable()->saveWrtmedia($form->getData());

                return $this->redirect()->toRoute('writer', array(
                    'action' => 'wrtinfoent',
                    'id'     => $id_wrtmedia,
                ));
            }
        }

        if($writer->barcode)
        {
            $barcode = $this->getBarcodeTable()->getBarcode($writer->barcode);
            $barcode_path = '/upload/'.$writer->created_by.'/writer/'.$id_writer.'/'.$barcode->filename;
        }
        else
        {
            $barcode_path = '#';
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'form' => $form,
            'writer' => $writer,
            'wrtmedia' => $wrtmedia,
            'product' => $this->getProductTable()->getProduct($writer->fk_product),
            'barcode_path' => $barcode_path,
        ));
    }

    public function seconddraftAction()
    {
        //媒体->自由撰稿人->编辑二稿              
        $arr_type_allowed = array(2);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_wrtmedia = (int)$this->params()->fromRoute('id',0);        
        if (!$id_wrtmedia) {
            return $this->redirect()->toRoute('writer', array(
                'action' => 'reqlist'
            ));
        }
        $wrtmedia = $this->getWrtmediaTable()->getWrtmedia($id_wrtmedia);
        $id_writer = $wrtmedia->fk_writer;
        $writer = $this->getWriterTable()->getWriter($id_writer);

        $wm_fk_writer           = $wrtmedia->fk_writer;
        $wm_fk_enterprise_user  = $wrtmedia->fk_enterprise_user;
        $wm_fk_media_user       = $wrtmedia->fk_media_user;
        $wm_created_by          = $wrtmedia->created_by;
        $wm_created_at          = $wrtmedia->created_at;
        $wm_first_draft_title   = $wrtmedia->first_draft_title;
        $wm_first_draft_body    = $wrtmedia->first_draft_body;
        $wm_revision_suggestion = $wrtmedia->revision_suggestion;
        $wm_order_no            = $wrtmedia->order_no;

        $form = new WrtmediaForm();
        $form->bind($wrtmedia);
        $form->get('submit')->setAttribute('value', '提交');

        $request = $this->getRequest();
        if($request->isPost()){
            $form->setInputFilter($wrtmedia->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                //keep the old values:
                $form->getData()->fk_writer = $wm_fk_writer;
                $form->getData()->fk_enterprise_user = $wm_fk_enterprise_user;
                $form->getData()->fk_media_user = $wm_fk_media_user;
                $form->getData()->created_by = $wm_created_by;
                $form->getData()->created_at = $wm_created_at;
                $form->getData()->first_draft_title = $wm_first_draft_title;
                $form->getData()->first_draft_body = $wm_first_draft_body;
                $form->getData()->revision_suggestion = $wm_revision_suggestion;
                $form->getData()->order_no = $wm_order_no;
                //update the following values:
                $form->getData()->updated_by = $cur_user;
                $form->getData()->updated_at = $this->_getDateTime();
                $form->getData()->fk_wrtmedia_status = 8; //second_draft_submitted
                $this->getWrtmediaTable()->saveWrtmedia($form->getData());

                return $this->redirect()->toRoute('writer', array(
                    'action' => 'wrtinfo',
                    'id'     => $id_writer,
                ));
            }
        }

        if($writer->barcode)
        {
            $barcode = $this->getBarcodeTable()->getBarcode($writer->barcode);
            $barcode_path = '/upload/'.$writer->created_by.'/writer/'.$id_writer.'/'.$barcode->filename;
        }
        else
        {
            $barcode_path = '#';
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'form' => $form,
            'writer' => $writer,
            'wrtmedia' => $wrtmedia,
            'product' => $this->getProductTable()->getProduct($writer->fk_product),
            'barcode_path' => $barcode_path,
            'is_writer' => $this->getUserTable()->getUserByName($cur_user)->is_writer,
        ));
    }

    public function passAction()
    {
        //企业->转告管理->稿件通过              
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_wrtmedia = (int)$this->params()->fromRoute('id',0);        
        if (!$id_wrtmedia) {
            return $this->redirect()->toRoute('writer', array(
                'action' => 'index',
            ));
        }
        $wrtmedia = $this->getWrtmediaTable()->getWrtmedia($id_wrtmedia);
        $id_writer = $wrtmedia->fk_writer;
        $writer = $this->getWriterTable()->getWriter($id_writer);

        $wrtmedia->fk_wrtmedia_status = 9;//draft_passed
        $wrtmedia->updated_by = $cur_user;
        $wrtmedia->updated_at = $this->_getDateTime();
        $this->getWrtmediaTable()->saveWrtmedia($wrtmedia);

        return $this->redirect()->toRoute('writer', array(
            'action' => 'wrtinfoent',
            'id'     => $id_wrtmedia,
        ));

        return new ViewModel(array(
            'user' => $cur_user,
            'form' => $form,
            'writer' => $writer,
            'wrtmedia' => $wrtmedia,
            'product' => $this->getProductTable()->getProduct($writer->fk_product),
        ));
    }

    public function printfirstdraftAction()
    {
        //企业->新闻撰写->撰稿管理->打印一稿
        $arr_type_allowed = array(1);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_wrtmedia = (int)$this->params()->fromRoute('id',0);        
        if (!$id_wrtmedia) {
            return $this->redirect()->toRoute('writer', array(
                'action' => 'index',
            ));
        }
        $wrtmedia = $this->getWrtmediaTable()->getWrtmedia($id_wrtmedia);

        //PHPWord start
        require_once './vendor/Classes/PHPWord.php';

        $PHPWord = new \PHPWord();
        $section = $PHPWord->createSection();
        $styleTable = array('borderSize'=>6, 'borderColor'=>'006699', 'cellMargin'=>80);
        $styleCell = array('valign'=>'center');
        $fontStyle = array('bold'=>true, 'align'=>'center');
        $PHPWord->addTableStyle('myTableStyle', $styleTable);
        $table = $section->addTable('myTableStyle');

        $table->addRow();
        $table->addCell(2000, $styleCell)->addText("新闻标题", $fontStyle);
        $table->addCell()->addText($wrtmedia->first_draft_title);
        $table->addRow();
        $table->addCell(2000, $styleCell)->addText("新闻正文", $fontStyle);
        $table->addCell()->addText($wrtmedia->first_draft_body);
        $table->addRow();
        $table->addCell(2000, $styleCell)->addText("作者", $fontStyle);
        $table->addCell()->addText($wrtmedia->created_by);
        $table->addRow();
        $table->addCell(2000, $styleCell)->addText("上传时间", $fontStyle);
        $table->addCell()->addText($wrtmedia->updated_by);

        $objWriter = \PHPWord_IOFactory::createWriter($PHPWord, 'Word2007');
        $objWriter->save('data/word/testfirstdraft.docx');

        //PHPWord end

        return $this->redirect()->toRoute('writer', array(
            'action' => 'wrtinfoent',
            'id'     => $id_wrtmedia,
        )); 
    }

    public function deletescreenshotAction()
    {
        //删除撰稿配图
        $arr_type_allowed = array(1, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_screenshot = (int)$this->params()->fromRoute('id',0);        
        if (!$id_screenshot) {
            return $this->redirect()->toRoute('application', array(
                'action' => 'index',
            ));
        }
        $screenshot = $this->getScreenshotTable()->getScreenshot($id_screenshot);
        $fk_writer = $screenshot->fk_writer;
        $this->getScreenshotTable()->deleteScreenshot($id_screenshot);
        return $this->redirect()->toRoute('writer', array(
            'action' => 'detail',
            'id'     => $fk_writer,
        ));
    }

    public function deletescreenshotfromeditAction()
    {
        //删除撰稿配图
        $arr_type_allowed = array(1, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_screenshot = (int)$this->params()->fromRoute('id',0);        
        if (!$id_screenshot) {
            return $this->redirect()->toRoute('application', array(
                'action' => 'index',
            ));
        }
        $screenshot = $this->getScreenshotTable()->getScreenshot($id_screenshot);
        $fk_writer = $screenshot->fk_writer;
        $this->getScreenshotTable()->deleteScreenshot($id_screenshot);
        return $this->redirect()->toRoute('writer', array(
            'action' => 'edit',
            'id'     => $fk_writer,
        ));
    }

    public function getWriterTable()
    {
        if (!$this->writerTable) {
	    $sm = $this->getServiceLocator();
	    $this->writerTable = $sm->get('Writer\Model\WriterTable');
        }
        return $this->writerTable;
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

    public function getWrtmediaTable()
    {
        if (!$this->wrtmediaTable) {
            $sm = $this->getServiceLocator();
            $this->wrtmediaTable = $sm->get('Writer\Model\WrtmediaTable');
        }
        return $this->wrtmediaTable;
    }

    public function getBarcodeTable()
    {
        if (!$this->barcodeTable) {
            $sm = $this->getServiceLocator();
            $this->barcodeTable = $sm->get('Attachment\Model\BarcodeTable');
        }
        return $this->barcodeTable;
    }

    public function getScreenshotTable()
    {
        if (!$this->screenshotTable) {
            $sm = $this->getServiceLocator();
            $this->screenshotTable = $sm->get('Attachment\Model\ScreenshotTable');
        }
        return $this->screenshotTable;
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

    /**
     * Update the Credit of a user who consumed the given amount of money
     * @param int $fk_user: the id of the user (who consumed)
     * @param int $price: the amount of money the user consumed
     */
    public function consume($fk_user, $price)
    {
        $credit = $this->getCreditTable()->getCreditByFkUser($fk_user);
        $originamount = $credit->amount;
        $credit->amount = $originamount - $price;
        $credit->updated_at = $this->_getDateTime();
        $credit->updated_by = $cur_user;
        $this->getCreditTable()->saveCredit($credit);

        return $credit;
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
