<?php
namespace Evaluate\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Evaluate\Model\Evaluate;          // <-- Add this import
use Evaluate\Form\EvaluateForm;       // <-- Add this import
use Zend\Session\Container as SessionContainer;
use User\Model\User;
use Zend\File\Transfer\Adapter\Http as FileHttp;
use Zend\Validator\File\Size as FileSize;
use Zend\Validator\File\Extension as FileExt;
use DateTime;
use Evaluate\Model\Evamedia;
use Evaluate\Form\EvamediaForm;
use Attachment\Model\Barcode;

class EvaluateController extends AbstractActionController
{
    protected $evaluateTable;
    protected $userTable;
    protected $productTable;
    protected $evamediaTable;
    protected $barcodeTable;

    public function indexAction()
    {     
        $arr_type_allowed = array(1, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        $request = $this->getRequest();
        $keyword = trim($request->getQuery(''));
        $page = intval($request->getQuery('page',1));
        $cur_user_role = $this->getUserTable()->getUserByName($cur_user)->fk_user_type;
        $view = new ViewModel(array(
            'user' => $cur_user,
            'products' => $this->getProductTable()->fetchAll(),
            )
        );
        if($cur_user_role == 1)
        {            
            $paginator = $this->getEvaluateTable()->getPaginator($keyword, $page, 5, 1, $cur_user);
            $view->setVariable('evaluate',$this->getEvaluateTable()->fetchEvaluateByUser($cur_user));
        }
        else
        {
            $paginator = $this->getEvaluateTable()->getPaginator($keyword, $page, 5, 1, null);
            $view->setVariable('evaluate',$this->getEvaluateTable()->fetchAll());
        }
        $view->setVariable('paginator', $paginator);
        return $view;
    }

    public function neworderAction()
    {
        //$cur_user = $this->_authenticateSession(1);
        $arr_type_allowed = array(1, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        if(!$id_product = (int)$this->params()->fromQuery('id_product', 0))
        {
            $id_product = $this->getRequest()->getPost()->fk_product;
        }
        $product = $this->getProductTable()->getProduct($id_product);
        $evaluate = new Evaluate();
        $evaluate->fk_product = $product->id_product;
        $evaluate->web_link = $product->web_link;
        $evaluate->appstore_link = $product->appstore_link;
        $evaluate->androidmkt_link = $product->androidmkt_link;

        $form = new EvaluateForm();
        $form->bind($evaluate);
        $form->get('submit')->setAttribute('value', '保存');

        $request = $this->getRequest();
        if($request->isPost()){
            $form->setInputFilter($evaluate->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $form->getData()->created_by = $cur_user;
                $form->getData()->created_at = $this->_getDateTime();
                $form->getData()->updated_by = $cur_user;
                $form->getData()->updated_at = $this->_getDateTime();
                $form->getData()->fk_product = $id_product;
                $this->getEvaluateTable()->saveEvaluate($form->getData());   
                $id_evaluate = $this->getEvaluateTable()->getId(
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
                $path_2    = $path_1.'evaluate/';
                $path_full = $path_2.$id_evaluate.'/';
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
                
                $evaluate2 = $this->getEvaluateTable()->getEvaluate($id_evaluate);
                $evaluate2->barcode = $id_barcode;
                $this->getEvaluateTable()->saveEvaluate($evaluate2);

                return $this->redirect()->toRoute('evaluate',array(
                    'action'=>'detail',
                    'id'    => $id_evaluate,
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
        $arr_type_allowed = array(1, 2, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id = (int)$this->params()->fromRoute('id',0);        
        if (!$id) {
            return $this->redirect()->toRoute('evaluate', array(
                'action' => 'index'
            ));
        }
        $evaluate = $this->getEvaluateTable()->getEvaluate($id);
        if($evaluate->barcode)
        {
            $barcode = $this->getBarcodeTable()->getBarcode($evaluate->barcode);
            $barcode_path = '/upload/'.$cur_user.'/evaluate/'.$id.'/'.$barcode->filename;
        }
        else
        {
            $barcode_path = '#';
        }

        /*$arr_media_assignees = array();
        $evamedia = $this->getEvamediaTable()->fetchEvamediaByFkEva($id);
        if($evamedia)
        {
            foreach ($evamedia as $em)
            {
                $media_user = $this->getUserTable()->getUser($em->fk_media_user);
                $arr_media_assignees[] = $media_user->username;
            }
        }*/

        return new ViewModel(array(
            'evaluate' => $evaluate,
            'user' => $cur_user,
            'allusers' => $this->getUserTable()->fetchAll(),
            'id' => $id,
            'product' => $this->getProductTable()->getProduct($evaluate->fk_product),
            //'media_assignees' => $arr_media_assignees,
            'evamedias' => $this->getEvamediaTable()->fetchEmExRejByMedByFkEva($id),//not include those rejected by the media
            'barcode_path' => $barcode_path,
        ));
    }    

    public function editAction()
    {
        //$cur_user = $this->_authenticateSession(1);
        $arr_type_allowed = array(1, 2, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_evaluate = (int)$this->params()->fromRoute('id',0);
        if(!$id_evaluate){
            return $this->redirect()->toRoute('evaluate', array(
                'action' => 'index',
            ));
        }
        $evaluate = $this->getEvaluateTable()->getEvaluate($id_evaluate);
        if($evaluate->barcode)
        {
            $barcode = $this->getBarcodeTable()->getBarcode($evaluate->barcode);
            $barcode_path = '/upload/'.$cur_user.'/evaluate/'.$id_evaluate.'/'.$barcode->filename;
        }
        else
        {
            $barcode_path = '#';
        }
        $product = $this->getProductTable()->getProduct($evaluate->fk_product);
        $eva_created_by = $evaluate->created_by;
        $eva_created_at = $evaluate->created_at;
        $eva_barcode    = $evaluate->barcode;
        $form = new EvaluateForm();
        $form->bind($evaluate);
        $form->get('submit')->setAttribute('value','保存');

        $request = $this->getRequest();
        if($request->isPost()){
            //upload start
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
                    $path_1    = $path_0.$cur_user.'/';
                    $path_2    = $path_1.'evaluate/';
                    $path_full = $path_2.$id_evaluate.'/';
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

            $form->setInputFilter($evaluate->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $form->getData()->created_by = $eva_created_by;
                $form->getData()->created_at = $eva_created_at;
                $form->getData()->updated_by = $cur_user;
                $form->getData()->updated_at = $this->_getDateTime();
                if(isset($id_barcode))
                {
                    $form->getData()->barcode = $id_barcode;
                }
                else
                {
                    $form->getData()->barcode = $eva_barcode;
                }
                $this->getEvaluateTable()->saveEvaluate($form->getData());

                return $this->redirect()->toRoute('evaluate',array(
                    'action' => 'detail',
                    'id'     => $id_evaluate,
                ));
            }
        }

        /*
        $arr_media_assignees = array();
        $evamedia = $this->getEvamediaTable()->fetchEvamediaByFkEva($id);
        if($evamedia)
        {
            foreach ($evamedia as $em)
            {
                $media_user = $this->getUserTable()->getUser($em->fk_media_user);
                $arr_media_assignees[] = $media_user->username;
            }
        }
        */

        return new ViewModel(array(
            //'evaluate' => $this->getEvaluateTable()->getEvaluate($id),
            'user'     => $cur_user,
            'form'     => $form,
            'id'       => $id_evaluate,
            'product'  => $product,
            //'media_assignees' => $arr_media_assignees,
            'barcode_path' => $barcode_path,
        ));
    }

    public function addAction()
    {
        //$cur_user = $this->_authenticateSession(1);
        $arr_type_allowed = array(1, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        //handle the form
        $form = new EvaluateForm();
        $form->get('submit')->setValue('提交订单');
        $request = $this->getRequest();
        if($request->isPost()){
            $evaluate = new Evaluate();
            $form->setInputFilter($evaluate->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $evaluate->exchangeArray($form->getData());
                //die(var_dump($form->getData()));
                $evaluate->created_by = $cur_user;
                $evaluate->created_at = $this->_getDateTime();
                $evaluate->updated_by = $cur_user;
                $evaluate->updated_at = $this->_getDateTime();
                $this->getEvaluateTable()->saveEvaluate($evaluate);
                $id_evaluate = $this->getEvaluateTable()->getId(
                        $evaluate->created_at,
                        $evaluate->created_by
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
                    $path_2    = $path_1.'evaluate/';
                    $path_full = $path_2.$id_evaluate.'/';
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

                $evaluate2 = $this->getEvaluateTable()->getEvaluate($id_evaluate);
                $evaluate2->barcode = $id_barcode;
                $this->getEvaluateTable()->saveEvaluate($evaluate2);

                /*
                //start: handle the assigned media
                $arr_get = $form->getData();
                $str_evamedias = trim($arr_get["evamedia"]);
                $arr_evamedias = explode(";", $str_evamedias);
                foreach ($arr_evamedias as $em)
                {
                    $evamedia = new Evamedia();    
                    $evamedia->fk_evaluate = $id_evaluate;
                    //$em->fk_enterprise 
                    $enterprise_user = $this->getUserTable()->getUserByName($cur_user);
                    $evamedia->fk_enterprise_user = $enterprise_user->id;
                    if($this->getUserTable()->checkUser($em)) {
                        $media_user = $this->getUserTable()->getUserByName($em);
                        $evamedia->fk_media_user = $media_user->id;                        
                        $evamedia->created_by = $cur_user;
                        $evamedia->created_at = $this->_getDateTime();
                        $evamedia->updated_by = $cur_user;
                        $evamedia->updated_at = $this->_getDateTime();
                        $this->getEvamediaTable()->saveEvamedia($evamedia);
                    }
                    else
                    {
                        continue;
                    }
                }
                //end: handle the assigned media
                */

                return $this->redirect()->toRoute('evaluate',array(
                    'action'=>'detail',
                    'id'    => $id_evaluate,
                ));
            }
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'form' => $form,
            'evaluate' => $this->getEvaluateTable()->fetchEvaluateByUser($cur_user),
            'products' => $this->getProductTable()->fetchProductByUser($cur_user),
        ));        
    }

    public function mgmtAction()
    {
        //媒体->评测管理
        $arr_type_allowed = array(2, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        return new ViewModel(array(
            'user' => $cur_user,
        ));
    }

    public function invitationAction()
    {
        //媒体->评测邀约
        $arr_type_allowed = array(2, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        return new ViewModel(array(
            'user' => $cur_user,
            'evaluate' => $this->getEvaluateTable()->fetchAllDesc(),
            'products' => $this->getProductTable()->fetchAll(),
            'evamedia' => $this->getEvamediaTable()->fetchEvamediaByUser($cur_user),
        ));        
    }

    public function mediaaccAction()
    {
        //媒体->评测邀约->接受订单
        $arr_type_allowed = array(2);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_evaluate = (int)$this->params()->fromRoute('id',0);        
        if (!$id_evaluate) {
            return $this->redirect()->toRoute('evaluate', array(
                'action' => 'invitation'
            ));
        }

        $evaluate = $this->getEvaluateTable()->getEvaluate($id_evaluate);
        $product = $this->getProductTable()->getProduct($evaluate->fk_product);
        $media_user = $this->getUserTable()->getUserByName($cur_user);
        $enterprise_user = $this->getUserTable()->getUserByName($evaluate->created_by);

        $evamedia = new Evamedia();
        $evamedia->fk_evaluate = $id_evaluate;
        $evamedia->fk_media_user = $media_user->id;
        $evamedia->fk_enterprise_user = $enterprise_user->id;
        $evamedia->created_by = $cur_user;
        $evamedia->created_at = $this->_getDateTime();
        $evamedia->updated_by = $cur_user;
        $evamedia->updated_at = $this->_getDateTime();
        $evamedia->fk_evaluate_status = 3;//accept the order
        $this->getEvamediaTable()->saveEvamedia($evamedia);
        //save the order number
        $id_evamedia = $this->getEvamediaTable()->getId($evamedia->created_at, $evamedia->created_by);
        $evamedia2 = $this->getEvamediaTable()->getEvamedia($id_evamedia);
        $evamedia2->order_no = 20000000 + $id_evamedia;
        $this->getEvamediaTable()->saveEvamedia($evamedia2);

        return $this->redirect()->toRoute('evaluate',array(
            'action'=>'invitation',
        ));

        return new ViewModel(array(
            'user' => $cur_user,
        ));        
    }

    public function mediarejAction()
    {
        //媒体->评测邀约->拒绝订单
        $arr_type_allowed = array(2);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_evaluate = (int)$this->params()->fromRoute('id',0);        
        if (!$id_evaluate) {
            return $this->redirect()->toRoute('evaluate', array(
                'action' => 'invitation'
            ));
        }

        $evaluate = $this->getEvaluateTable()->getEvaluate($id_evaluate);
        $product = $this->getProductTable()->getProduct($evaluate->fk_product);
        $media_user = $this->getUserTable()->getUserByName($cur_user);
        $enterprise_user = $this->getUserTable()->getUserByName($evaluate->created_by);

        $evamedia = new Evamedia();    
        $evamedia->fk_evaluate = $id_evaluate;
        $evamedia->fk_media_user = $media_user->id;
        $evamedia->fk_enterprise_user = $enterprise_user->id;
        $evamedia->created_by = $cur_user;
        $evamedia->created_at = $this->_getDateTime();
        $evamedia->updated_by = $cur_user;
        $evamedia->updated_at = $this->_getDateTime();
        $evamedia->fk_evaluate_status = 2;//reject the order
        $this->getEvamediaTable()->saveEvamedia($evamedia);
        //save the order number
        $id_evamedia = $this->getEvamediaTable()->getId($evamedia->created_at, $evamedia->created_by);
        $evamedia2 = $this->getEvamediaTable()->getEvamedia($id_evamedia);
        $evamedia2->order_no = 20000000 + $id_evamedia;
        $this->getEvamediaTable()->saveEvamedia($evamedia2);

        return $this->redirect()->toRoute('evaluate',array(
            'action'=>'invitation',
        ));

        return new ViewModel(array(
            'user' => $cur_user,
        ));  
    }

    public function entaccAction()
    {
        //企业->我要评测->企业接受
        $arr_type_allowed = array(1, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_evamedia = (int)$this->params()->fromRoute('id',0);        
        if (!$id_evamedia) {
            return $this->redirect()->toRoute('evaluate', array(
                'action' => 'index'
            ));
        }

        $evamedia = $this->getEvamediaTable()->getEvamedia($id_evamedia);
        $evamedia->updated_by = $cur_user;
        $evamedia->updated_at = $this->_getDateTime();
        $evamedia->fk_evaluate_status = 5;//accept the order
        $this->getEvamediaTable()->saveEvamedia($evamedia);

        return $this->redirect()->toRoute('evaluate',array(
            'action' => 'detail',
            'id'     => $evamedia->fk_evaluate,
        ));

        return new ViewModel(array(
            'user' => $cur_user,
        ));  
    }

    public function entrejAction()
    {
        //企业->我要评测->企业拒绝
        $arr_type_allowed = array(1, 3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_evamedia = (int)$this->params()->fromRoute('id',0);        
        if (!$id_evamedia) {
            return $this->redirect()->toRoute('evaluate', array(
                'action' => 'index'
            ));
        }

        $evamedia = $this->getEvamediaTable()->getEvamedia($id_evamedia);
        $evamedia->updated_by = $cur_user;
        $evamedia->updated_at = $this->_getDateTime();
        $evamedia->fk_evaluate_status = 4;//reject the order
        $this->getEvamediaTable()->saveEvamedia($evamedia);

        return $this->redirect()->toRoute('evaluate',array(
            'action' => 'detail',
            'id'     => $evamedia->fk_evaluate,
        ));

        return new ViewModel(array(
            'user' => $cur_user,
        ));  
    }

    public function evainfoAction()
    {
        //媒体->评测邀约->查看评测信息
        $arr_type_allowed = array(2);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_evaluate = (int)$this->params()->fromRoute('id',0);        
        if (!$id_evaluate) {
            return $this->redirect()->toRoute('evaluate', array(
                'action' => 'invitation'
            ));
        }
        $evaluate = $this->getEvaluateTable()->getEvaluate($id_evaluate);
        $evamedia = $this->getEvamediaTable()->getEvemediaByUserAndFkEva($cur_user, $evaluate->id_evaluate);
        if($evaluate->barcode)
        {
            $barcode = $this->getBarcodeTable()->getBarcode($evaluate->barcode);
            $barcode_path = '/upload/'.$evaluate->created_by.'/evaluate/'.$id_evaluate.'/'.$barcode->filename;
        }
        else
        {
            $barcode_path = '#';
        }

        return new ViewModel(array(
            'evaluate' => $evaluate,
            'user' => $cur_user,
            'id' => $id_evaluate,
            'product' => $this->getProductTable()->getProduct($evaluate->fk_product),
            'evamedia' => $evamedia,
            'barcode_path' => $barcode_path,
        ));
    }

    public function editnewslinkAction()
    {
        //媒体->评测APP->添加评论链接        
        $arr_type_allowed = array(2);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_evaluate = (int)$this->params()->fromRoute('id',0);        
        if (!$id_evaluate) {
            return $this->redirect()->toRoute('evaluate', array(
                'action' => 'invitation'
            ));
        }
        $evaluate = $this->getEvaluateTable()->getEvaluate($id_evaluate);
        $evamedia = $this->getEvamediaTable()->getEvemediaByUserAndFkEva($cur_user, $evaluate->id_evaluate);

        $em_fk_evaluate        = $evamedia->fk_evaluate;
        $em_fk_enterprise_user = $evamedia->fk_enterprise_user;
        $em_fk_media_user      = $evamedia->fk_media_user;
        $em_fk_evaluate_status = $evamedia->fk_evaluate_status;
        $em_created_by         = $evamedia->created_by;
        $em_created_at         = $evamedia->created_at;
        $em_order_no           = $evamedia->order_no;

        $form = new EvamediaForm();
        $form->bind($evamedia);
        $form->get('submit')->setAttribute('value','保存');

        $request = $this->getRequest();
        if($request->isPost()){   
            $form->setInputFilter($evaluate->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $form->getData()->fk_evaluate        = $em_fk_evaluate;
                $form->getData()->fk_enterprise_user = $em_fk_enterprise_user;
                $form->getData()->fk_media_user      = $em_fk_media_user;
                $form->getData()->fk_evaluate_status = $em_fk_evaluate_status;
                $form->getData()->order_no           = $em_order_no;
                $form->getData()->created_by         = $em_created_by;
                $form->getData()->created_at         = $em_created_at;
                $form->getData()->updated_by         = $cur_user;
                $form->getData()->updated_at         = $this->_getDateTime();
                $this->getEvamediaTable()->saveEvamedia($form->getData());

                return $this->redirect()->toRoute('evaluate',array(
                    'action' => 'evainfo',
                    'id'     => $id_evaluate,
                ));
            }
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'form' => $form,
            'evaluate' => $evaluate,
            'evamedia' => $evamedia,
            'product' => $this->getProductTable()->getProduct($evaluate->fk_product),
        ));
    }

    public function getEvaluateTable()
    {
        if (!$this->evaluateTable) {
	    $sm = $this->getServiceLocator();
	    $this->evaluateTable = $sm->get('Evaluate\Model\EvaluateTable');
        }
        return $this->evaluateTable;
    }

    public function getEvamediaTable()
    {
        if (!$this->evamediaTable) {
        $sm = $this->getServiceLocator();
        $this->evamediaTable = $sm->get('Evaluate\Model\EvamediaTable');
        }
        return $this->evamediaTable;
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

    public function getBarcodeTable()
    {
        if (!$this->barcodeTable) {
            $sm = $this->getServiceLocator();
            $this->barcodeTable = $sm->get('Attachment\Model\BarcodeTable');
        }
        return $this->barcodeTable;
    }

    protected function _getEvamedia(Evaluate $evaluate)
    {
        return $evamedia = $evaluate->evamedia;
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
