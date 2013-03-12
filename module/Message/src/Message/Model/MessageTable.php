<?php
namespace Message\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;

class MessageTable
{
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

    //SELECT * FROM user WHERE from = $from;
    public function getMessageFromUser($from)
    {
        //$rowset = $this->tableGateway->select(array('from' => $from));
        $rowset = $this->tableGateway->select(function(Select $select) use ($from) {
            $select->where->equalTo('from', $from);
            $select->order('id_message DESC');
        });
        return $rowset;
    }

    //SELECT * FROM user WHERE to = $to;
    public function getMessageToUser($to)
    {
        $rowset = $this->tableGateway->select(function(Select $select) use ($to) {
            $select->where->equalTo('to', $to)
                   ->or->equalTo('cc', $to)
                   ->or->equalTo('bcc', $to);
            $select->order('id_message DESC');
        });
        return $rowset;
    }

    public function getMessage($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id_message' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveMessage(Message $message)
    {
        $data = array(
            'from'              => $message->from,
            'to'                => $message->to,
            'cc'                => $message->cc,
            'bcc'               => $message->bcc,
            'subject'           => $message->subject,
            'body'              => $message->body,
            'created_at'        => $message->created_at,
            'updated_at'        => $message->updated_at,
            'fk_message_status' => $message->fk_message_status,
        );

        $id = (int)$message->id_message;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getMessage($id)) {
                $this->tableGateway->update($data, array('id_message' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function deleteMessage($id)
    {
        $this->tableGateway->delete(array('id_message' => $id));
    }
}