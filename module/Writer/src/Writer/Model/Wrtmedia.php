<?php
namespace Writer\Model;

use Zend\InputFilter\Factory as InputFactory;     // <-- Add this import
use Zend\InputFilter\InputFilter;                 // <-- Add this import
use Zend\InputFilter\InputFilterAwareInterface;   // <-- Add this import
use Zend\InputFilter\InputFilterInterface;        // <-- Add this import

class Wrtmedia implements InputFilterAwareInterface
{
    public $id_wrtmedia;
    public $fk_writer;
    public $fk_enterprise_user;
    public $fk_media_user;
    public $created_by;
    public $created_at;
    public $updated_by;
    public $updated_at;
    public $fk_wrtmedia_status;
    public $first_draft_title;
    public $first_draft_body;
    public $second_draft_title;
    public $second_draft_body;
    public $revision_suggestion;
    public $order_no;

    protected $inputFilter;                       // <-- Add this variable

    public function exchangeArray($data)
    {
        $this->id_wrtmedia         = (isset($data['id_wrtmedia']))         ? $data['id_wrtmedia']         : null;
        $this->fk_writer           = (isset($data['fk_writer']))           ? $data['fk_writer']           : null;
        $this->fk_enterprise_user  = (isset($data['fk_enterprise_user']))  ? $data['fk_enterprise_user']  : null;
        $this->fk_media_user       = (isset($data['fk_media_user']))       ? $data['fk_media_user']       : null;
        $this->created_by          = (isset($data['created_by']))          ? $data['created_by']          : null;
        $this->created_at          = (isset($data['created_at']))          ? $data['created_at']          : null;
        $this->updated_by          = (isset($data['updated_by']))          ? $data['updated_by']          : null;
        $this->updated_at          = (isset($data['updated_at']))          ? $data['updated_at']          : null;
        $this->fk_wrtmedia_status  = (isset($data['fk_wrtmedia_status']))  ? $data['fk_wrtmedia_status']  : null;
        $this->first_draft_title   = (isset($data['first_draft_title']))   ? $data['first_draft_title']   : null;
        $this->first_draft_body    = (isset($data['first_draft_body']))    ? $data['first_draft_body']    : null;
        $this->second_draft_title  = (isset($data['second_draft_title']))  ? $data['second_draft_title']  : null;
        $this->second_draft_body   = (isset($data['second_draft_body']))   ? $data['second_draft_body']   : null;
        $this->revision_suggestion = (isset($data['revision_suggestion'])) ? $data['revision_suggestion'] : null;
        $this->order_no            = (isset($data['order_no']))            ? $data['order_no']            : null;
    }

    // Add the following method:
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    // Add content to this method:
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                'name'     => 'id_wrtmedia',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}