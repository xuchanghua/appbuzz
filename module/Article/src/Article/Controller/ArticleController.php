<?php
namespace Article\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Article\Model\Article;          // <-- Add this import
use Article\Form\ArticleForm;       // <-- Add this import
use Article\Form\RankForm;
use Article\Model\Banner;
use Article\Form\BannerForm;
use Zend\Session\Container as SessionContainer;
use User\Model\User;
use DateTime;
use Zend\File\Transfer\Adapter\Http as FileHttp;
use Zend\Validator\File\Size as FileSize;
use Zend\Validator\File\Extension as FileExt;

class ArticleController extends AbstractActionController
{
    protected $articleTable;
    protected $userTable;
    protected $rankTable;
    protected $productTable;
    protected $bannerTable;

    public function indexAction()
    {     
        //管理员->首页管理->文章列表
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);

        $articles = $this->getArticleTable()->fetchAllDesc();

        return new ViewModel(array(
            'user' => $cur_user,
            'articles' => $articles,
        ));
    }

    public function rankAction()
    {
        //管理员->首页管理->排序管理
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);

        $article_rank = $this->getRankTable()->getRankByObj('article');
        $articles = $this->getArticleTable()->fetchAll();

        return new ViewModel(array(
            'user' => $cur_user,
            'rank' => $article_rank,
            'articles' => $articles,
        ));
    }

    public function editrankAction()
    {
        //管理员->首页管理->调整排序
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_rank = 1;//1=>article
        $rank = $this->getRankTable()->getRank($id_rank);
        $form = new RankForm();
        $form->bind($rank);
        $form->get('submit')->setAttribute('value','保存');

        $request = $this->getRequest();
        if($request->isPost()){
            $form->setInputFilter($rank->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $this->getRankTable()->saveRank($form->getData());

                return $this->redirect()->toRoute('article', array(
                    'action' => 'rank',
                ));
            }
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'form' => $form,
            'articles' => $this->getArticleTable()->fetchAll(),
        ));
    }

    public function apprankAction()
    {
        //管理员->首页管理->推荐APP排序
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);

        $recapp_rank = $this->getRankTable()->getRankByObj('product');
        $products = $this->getProductTable()->fetchAll();

        return new ViewModel(array(
            'user' => $cur_user,
            'rank' => $recapp_rank,
            'products' => $products,
        ));
    }

    public function editapprankAction()
    {
        //管理员->首页管理->调整推荐APP排序
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_rank = 2;//1=>推荐app
        $rank = $this->getRankTable()->getRank($id_rank);
        $form = new RankForm();
        $form->bind($rank);
        $form->get('submit')->setAttribute('value','保存');

        $request = $this->getRequest();
        if($request->isPost()){
            $form->setInputFilter($rank->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $this->getRankTable()->saveRank($form->getData());

                return $this->redirect()->toRoute('article', array(
                    'action' => 'apprank',
                ));
            }
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'form' => $form,
            'products' => $this->getProductTable()->fetchAll(),
        ));
    }

    public function latestapprankAction()
    {
        //管理员->首页管理->最新注册APP排序
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);

        $recapp_rank = $this->getRankTable()->getRank(3);
        $products = $this->getProductTable()->fetchAllDesc();

        return new ViewModel(array(
            'user' => $cur_user,
            'rank' => $recapp_rank,
            'products' => $products,
        ));
    }

    public function editlatestapprankAction()
    {
        //管理员->首页管理->调整最新注册APP排序
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_rank = 3;//3=>最新注册app
        $rank = $this->getRankTable()->getRank($id_rank);
        $form = new RankForm();
        $form->bind($rank);
        $form->get('submit')->setAttribute('value','保存');

        $request = $this->getRequest();
        if($request->isPost()){
            $form->setInputFilter($rank->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $this->getRankTable()->saveRank($form->getData());

                return $this->redirect()->toRoute('article', array(
                    'action' => 'latestapprank',
                ));
            }
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'form' => $form,
            'products' => $this->getProductTable()->fetchAllDesc(),
        ));
    }

    public function addAction()
    {
        //管理员->首页管理->添加文章
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);

        $form = new ArticleForm();
        $form->get('submit')->setValue('保存');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $article = new Article();
            $form->setInputFilter($article->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $article->exchangeArray($form->getData());
                $article->created_by = $cur_user;
                $article->created_at = $this->_getDateTime();
                $article->updated_by = $cur_user;
                $article->updated_at = $this->_getDateTime();
                $this->getArticleTable()->saveArticle($article);
                $id_article = $this->getArticleTable()->getId($article->created_at, $article->created_by);                

                // Redirect to list of articles
                return $this->redirect()->toRoute('article', array(
                    'action' => 'detail',
                    'id'     => $id_article,
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
        //管理员->首页管理->文章详情
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_article = (int) $this->params()->fromRoute('id',0);
        if(!$id_article){
            return $this->redirect()->toRoute('admin', array(
                'action' => 'index',
            ));
        }
        $article = $this->getArticleTable()->getArticle($id_article);

        return new ViewModel(array(
            'user' => $cur_user,
            'article' => $article,
        ));
    }

    public function pubviewAction()
    {
        //无需登录凭证->点击首页新闻标题->详细新闻

        $id_article = (int) $this->params()->fromRoute('id',0);
        if(!$id_article){
            return $this->redirect()->toRoute('admin', array(
                'action' => 'index',
            ));
        }
        $article = $this->getArticleTable()->getArticle($id_article);

        return new ViewModel(array(
            'article' => $article,
        ));
    }

    public function editAction()
    {                
        //管理员->首页管理->编辑首页文章
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_article = (int) $this->params()->fromRoute('id', 0);
        if (!$id_article) {
            return $this->redirect()->toRoute('article', array(
                'action' => 'index'
            ));
        }
        $article = $this->getArticleTable()->getArticle($id_article);
        $artl_created_at = $article->created_at;
        $artl_created_by = $article->created_by;

        $form  = new ArticleForm();
        $form->bind($article);
        $form->get('submit')->setAttribute('value', '保存');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($article->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $form->getData()->created_at = $artl_created_at;
                $form->getData()->created_by = $artl_created_by;
                $form->getData()->updated_at = $this->_getDateTime();
                $form->getData()->updated_by = $cur_user;
                $this->getArticleTable()->saveArticle($form->getData());

                // Redirect to list of articles
                return $this->redirect()->toRoute('article', array(
                    'action' => 'detail',
                    'id'     => $id_article,
                ));
            }
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'form' => $form,
        ));
    }

    public function addbannerAction()
    {
        //管理员->横幅管理->添加横幅
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);

        $form = new BannerForm();
        $form->get('submit')->setValue('保存');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $banner = new Banner();
            $form->setInputFilter($banner->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {                
                $banner->exchangeArray($form->getData());
                $banner->created_by = $cur_user;
                $banner->created_at = $this->_getDateTime();
                $banner->updated_by = $cur_user;
                $banner->updated_at = $this->_getDateTime();
                $this->getBannerTable()->saveBanner($banner);
                $id_banner = $this->getBannerTable()->getId($banner->created_at, $banner->created_by);  

                $file = $this->params()->fromFiles('filename');
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
                    $path_1    = $path_0.'banner/';
                    $path_full = $path_1.$id_banner.'/';
                    if(!is_dir($path_1))
                    {
                        mkdir($path_1);
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
                        $banner2 = $this->getBannerTable()->getBanner($id_banner);
                        $banner2->filename = $file['name'];
                        $banner2->path = $path_full;
                        $this->getBannerTable()->saveBanner($banner2);
                    }
                }
                unset($adapter);              

                // Redirect to list of articles
                return $this->redirect()->toRoute('article', array(
                    'action' => 'bannerdetail',
                    'id'     => $id_banner,
                ));
            }
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'form' => $form,
        ));
    }

    public function bannerdetailAction()
    {
        //管理员->横幅管理->横幅详细信息
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_banner = (int) $this->params()->fromRoute('id',0);
        if(!$id_banner){
            return $this->redirect()->toRoute('article', array(
                'action' => 'bannerlist',
            ));
        }
        $banner = $this->getBannerTable()->getBanner($id_banner);


        return new ViewModel(array(
            'user' => $cur_user,
            'banner' => $banner,
        ));
    }

    public function bannerlistAction()
    {
        //管理员->横幅管理->横幅列表
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);

        $banners = $this->getBannerTable()->fetchAllDesc();

        return new ViewModel(array(
            'user' => $cur_user,
            'banners' => $banners,
        ));
    }

    public function bannerrankAction()
    {
        //管理员->横幅管理->横幅排序
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);

        $banner_rank = $this->getRankTable()->getRankByObj('banner');
        $banners = $this->getBannerTable()->fetchAll();

        return new ViewModel(array(
            'user' => $cur_user,
            'rank' => $banner_rank,
            'banners' => $banners,
        ));
    }

    public function editbannerrankAction()
    {
        //管理员->横幅管理->调整横幅排序
        $arr_type_allowed = array(3);
        $cur_user = $this->_auth($arr_type_allowed);

        $id_rank = 4;//4=>横幅
        $rank = $this->getRankTable()->getRank($id_rank);
        $form = new RankForm();
        $form->bind($rank);
        $form->get('submit')->setAttribute('value','保存');

        $request = $this->getRequest();
        if($request->isPost()){
            $form->setInputFilter($rank->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $this->getRankTable()->saveRank($form->getData());

                return $this->redirect()->toRoute('article', array(
                    'action' => 'bannerrank',
                ));
            }
        }

        return new ViewModel(array(
            'user' => $cur_user,
            'form' => $form,
            'banners' => $this->getBannerTable()->fetchAllDesc(),
        ));
    }

    public function deleteAction()
    {
    }

    public function getArticleTable()
    {
        if (!$this->articleTable) {
	    $sm = $this->getServiceLocator();
	    $this->articleTable = $sm->get('Article\Model\ArticleTable');
        }
        return $this->articleTable;
    }

    public function getRankTable()
    {
        if (!$this->rankTable) {
        $sm = $this->getServiceLocator();
        $this->rankTable = $sm->get('Article\Model\RankTable');
        }
        return $this->rankTable;
    }

    public function getProductTable()
    {
        if (!$this->productTable) {
        $sm = $this->getServiceLocator();
        $this->productTable = $sm->get('Product\Model\ProductTable');
        }
        return $this->productTable;
    }

    public function getUserTable()
    {
        if(!$this->userTable){
            $sm = $this->getServiceLocator();
            $this->userTable = $sm->get('User\Model\UserTable');
        }
        return $this->userTable;
    }

    public function getBannerTable()
    {
        if(!$this->bannerTable){
            $sm = $this->getServiceLocator();
            $this->bannerTable = $sm->get('Article\Model\BannerTable');
        }
        return $this->bannerTable;
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
