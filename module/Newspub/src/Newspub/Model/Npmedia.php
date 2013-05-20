<?php
namespace Newspub\Model;

use Zend\InputFilter\Factory as InputFactory;     // <-- Add this import
use Zend\InputFilter\InputFilter;                 // <-- Add this import
use Zend\InputFilter\InputFilterAwareInterface;   // <-- Add this import
use Zend\InputFilter\InputFilterInterface;        // <-- Add this import

class Npmedia implements InputFilterAwareInterface
{
    public $id_npmedia;
    public $fk_newspub;
    public $fk_media_user;
    public $fk_npmedia_status;
    public $news_link;
    public $score;
    public $created_at;
    public $created_by;
    public $updated_at;
    public $updated_by;

    protected $inputFilter;                       // <-- Add this variable

    public function exchangeArray($data)
    {
        $this->id_npmedia        = (isset($data['id_npmedia']))        ? $data['id_npmedia']        : null;
        $this->fk_newspub        = (isset($data['fk_newspub']))        ? $data['fk_newspub']        : null;
        $this->fk_media_user     = (isset($data['fk_media_user']))     ? $data['fk_media_user']     : null;
        $this->fk_npmedia_status = (isset($data['fk_npmedia_status'])) ? $data['fk_npmedia_status'] : null;
        $this->news_link         = (isset($data['news_link']))         ? $data['news_link']         : null;
        $this->score             = (isset($data['score']))             ? $data['score']             : null;
        $this->created_at        = (isset($data['created_at']))        ? $data['created_at']        : null;
        $this->created_by        = (isset($data['created_by']))        ? $data['created_by']        : null;
        $this->updated_at        = (isset($data['updated_at']))        ? $data['updated_at']        : null;
        $this->updated_by        = (isset($data['updated_by']))        ? $data['updated_by']        : null;
        //count
        $this->count_nm          = (isset($data['count_nm']))          ? $data['count_nm']          : null;
    }

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
                'name'     => 'id_npmedia',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name' => 'score',
                'required' => false,
            )));

            $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }
}