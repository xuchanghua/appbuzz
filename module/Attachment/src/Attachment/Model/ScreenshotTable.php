<?php
namespace Attachment\Model;

use Zend\Db\TableGateway\TableGateway;      
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Db\ResultSet\ResultSet;
use DateTime;
use Zend\Db\Sql\Expression as Expr;

class ScreenshotTable
{
    const ORDER_DEFAULT = 0;
    const ORDER_LATEST  = 1;

    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public function fetchScreenshotByFkEva($fk_evaluate)
    {
        $resultSet = $this->tableGateway->select(function(Select $select) use($fk_evaluate){
            $select->where->equalTo('fk_evaluate', $fk_evaluate);
            $select->order('id_screenshot DESC');
        });
        return $resultSet;
    }

    public function fetchScreenshotByFkWrt($fk_writer)
    {
        $resultSet = $this->tableGateway->select(function(Select $select) use($fk_writer){
            $select->where->equalTo('fk_writer', $fk_writer);
            $select->order('id_screenshot DESC');
        });
        return $resultSet;
    }

    public function fetchCountSsByFkEva($fk_evaluate)
    {
        $resultSet = $this->tableGateway->select(function(Select $select) use ($fk_evaluate){
            $select->columns(array("count_ss" => new Expr("count(id_screenshot)")));
            $select->where->equalTo('fk_evaluate',$fk_evaluate);
            $select->group(array("fk_evaluate"));
        });
        $row = $resultSet->current();
        return $row->count_ss;
    }

    public function getScreenshot($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id_screenshot' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function getId($created_at, $created_by)
    {
        $rowset = $this->tableGateway->select(array(
            'created_at' => $created_at,
            'created_by' => $created_by,
        ));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $created_at and $created_by");
        }
        return $row->id_screenshot;
    }

    public function saveScreenshot(Screenshot $screenshot)
    {
        $data = array(
            'filename'    => $screenshot->filename,
            'path'        => $screenshot->path,
            'fk_evaluate' => $screenshot->fk_evaluate,
            'fk_writer'   => $screenshot->fk_writer,
            'created_by'  => $screenshot->created_by,
            'created_at'  => $screenshot->created_at,
        );

        $id = (int)$screenshot->id_screenshot;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getScreenshot($id)) {
                $this->tableGateway->update($data, array('id_screenshot' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function deleteScreenshot($id)
    {
        $this->tableGateway->delete(array('id_screenshot' => $id));
    }

    /**
     * 分页获取数据
     * @param string $keyword 图片title的关键词
     * @param int $page 当前页码，从1开始
     * @param int $itemsPerPage 每页结果条数
     * @return \Zend\Paginator\Paginator
     */
    public function getPaginator(
            $keyword = NULL, 
            $page = 1, 
            $itemsPerPage = 10, 
            $order = self::ORDER_DEFAULT)
    {
        //新建select对象
        $select = new Select('screenshot');
        //构建查询条件
        $closure = function (Where $where) use($keyword) {
                    if ($keyword != '') {
                        $where->like('title', '%' . $keyword . '%');//查询符合特定关键词的结果
                    }
                };
        $select->columns(array('id', 'title', 'artist'))
                ->where($closure);
        if ($order == self::ORDER_LATEST) {
            $select->order('id DESC');//按时间倒排序
        } else {
            $select->order('title ASC');//按标题排序
        }
        //将返回的结果设置为Ablum的实例
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new Screenshot());
        //创建分页用的适配器，第2个参数为数据库adapter，使用全局默认的即可        
        $adapter = new DbSelect($select, $this->tableGateway->getAdapter(), $resultSetPrototype);
        //新建分页
        $paginator = new Paginator($adapter);
        //设置当前页数
        $paginator->setCurrentPageNumber($page);
        //设置一页要返回的结果条数
        $paginator->setItemCountPerPage($itemsPerPage);
        return $paginator;
    }
}