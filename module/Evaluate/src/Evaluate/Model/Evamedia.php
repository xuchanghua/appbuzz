<?php
namespace Evaluate\Model;

use Zend\InputFilter\Factory as InputFactory;     // <-- Add this import
use Zend\InputFilter\InputFilter;                 // <-- Add this import
use Zend\InputFilter\InputFilterAwareInterface;   // <-- Add this import
use Zend\InputFilter\InputFilterInterface;        // <-- Add this import

class Evamedia implements InputFilterAwareInterface
{
    public $id_evamedia;
    public $fk_evaluate;
    public $fk_enterprise_user;
    public $fk_media_user;
    public $created_by;
    public $created_at;
    public $updated_by;
    public $updated_at;
    public $fk_evaluate_status;
    public $news_link;

    protected $inputFilter;                       // <-- Add this variable

    public function exchangeArray($data)
    {
        $this->id_evamedia        = (isset($data['id_evamedia']))        ? $data['id_evamedia']        : null;
        $this->fk_evaluate        = (isset($data['fk_evaluate']))        ? $data['fk_evaluate']        : null;
        $this->fk_enterprise_user = (isset($data['fk_enterprise_user'])) ? $data['fk_enterprise_user'] : null;
        $this->fk_media_user      = (isset($data['fk_media_user']))      ? $data['fk_media_user']      : null;
        $this->created_by         = (isset($data['created_by']))         ? $data['created_by']         : null;
        $this->created_at         = (isset($data['created_at']))         ? $data['created_at']         : null;
        $this->updated_by         = (isset($data['updated_by']))         ? $data['updated_by']         : null;
        $this->updated_at         = (isset($data['updated_at']))         ? $data['updated_at']         : null;
        $this->fk_evaluate_status = (isset($data['fk_evaluate_status'])) ? $data['fk_evaluate_status'] : null;
        $this->news_link          = (isset($data['news_link']))          ? $data['news_link']          : null;
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
                'name'     => 'id_evamedia',
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