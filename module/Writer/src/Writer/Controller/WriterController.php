<?php
namespace Writer\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Writer\Model\Writer;          // <-- Add this import
use Writer\Form\WriterForm;       // <-- Add this import

class WriterController extends AbstractActionController
{
    protected $writerTable;

    public function indexAction()
    {     
	return new ViewModel(array(
	    'writers' => $this->getWriterTable()->fetchAll(),
	));
    }

    public function addAction()
    {
        $form = new WriterForm();
        $form->get('submit')->setValue('Add');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $writer = new Writer();
            $form->setInputFilter($writer->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $writer->exchangeArray($form->getData());
                $this->getWriterTable()->saveWriter($writer);

                // Redirect to list of writers
                return $this->redirect()->toRoute('writer');
            }
        }
        return array('form' => $form);
    }

    public function editAction()
    {        
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('writer', array(
                'action' => 'add'
            ));
        }
        $writer = $this->getWriterTable()->getWriter($id);

        $form  = new WriterForm();
        $form->bind($writer);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($writer->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getWriterTable()->saveWriter($form->getData());

                // Redirect to list of writers
                return $this->redirect()->toRoute('writer');
            }
        }

        return array(
            'id' => $id,
            'form' => $form,
        );
    }

    public function deleteAction()
    {
    }

    public function getWriterTable()
    {
        if (!$this->writerTable) {
	    $sm = $this->getServiceLocator();
	    $this->writerTable = $sm->get('Writer\Model\WriterTable');
        }
        return $this->writerTable;
    }
}
