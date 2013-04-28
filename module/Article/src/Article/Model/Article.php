<?php
namespace Article\Model;

use Zend\InputFilter\Factory as InputFactory;     
use Zend\InputFilter\InputFilter;                 
use Zend\InputFilter\InputFilterAwareInterface;   
use Zend\InputFilter\InputFilterInterface;  

class Article implements InputFilterAwareInterface
{

    public $id_article;
    public $label;
    public $title;
    public $body;
    public $abstract;
    public $author;
    public $pub_date;
    public $rank;
    public $is_featuring;
    public $created_at;
    public $created_by;
    public $updated_at;
    public $updated_by;
    protected $inputFilter;                       // <-- Add this variable

    public function exchangeArray($data)
    {
        $this->id_article   = (isset($data['id_article']))   ? $data['id_article']   : null;
        $this->label        = (isset($data['label']))        ? $data['label']        : null;
        $this->title        = (isset($data['title']))        ? $data['title']        : null;
        $this->body         = (isset($data['body']))         ? $data['body']         : null;
        $this->abstract     = (isset($data['abstract']))     ? $data['abstract']     : null;
        $this->author       = (isset($data['author']))       ? $data['author']       : null;
        $this->pub_date     = (isset($data['pub_date']))     ? $data['pub_date']     : null;
        $this->rank         = (isset($data['rank']))         ? $data['rank']         : null;
        $this->is_featuring = (isset($data['is_featuring'])) ? $data['is_featuring'] : null;
        $this->created_at   = (isset($data['created_at']))   ? $data['created_at']   : null;
        $this->created_by   = (isset($data['created_by']))   ? $data['created_by']   : null;
        $this->updated_at   = (isset($data['updated_at']))   ? $data['updated_at']   : null;
        $this->updated_by   = (isset($data['updated_by']))   ? $data['updated_by']   : null;
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
                'name'     => 'id_article',
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