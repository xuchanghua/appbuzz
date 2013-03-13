<?php
namespace Interview\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Interview\Model\Interview;          // <-- Add this import
use Interview\Form\InterviewForm;       // <-- Add this import

class InterviewController extends AbstractActionController
{
    protected $interviewTable;

    public function indexAction()
    {     
	return new ViewModel(array(
	    'interviews' => $this->getInterviewTable()->fetchAll(),
	));
    }

    public function addAction()
    {
        $form = new InterviewForm();
        $form->get('submit')->setValue('Add');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $interview = new Interview();
            $form->setInputFilter($interview->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $interview->exchangeArray($form->getData());
                $this->getInterviewTable()->saveInterview($interview);

                // Redirect to list of interviews
                return $this->redirect()->toRoute('interview');
            }
        }
        return array('form' => $form);
    }

    public function editAction()
    {        
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('interview', array(
                'action' => 'add'
            ));
        }
        $interview = $this->getInterviewTable()->getInterview($id);

        $form  = new InterviewForm();
        $form->bind($interview);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($interview->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getInterviewTable()->saveInterview($form->getData());

                // Redirect to list of interviews
                return $this->redirect()->toRoute('interview');
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

    public function getInterviewTable()
    {
        if (!$this->interviewTable) {
	    $sm = $this->getServiceLocator();
	    $this->interviewTable = $sm->get('Interview\Model\InterviewTable');
        }
        return $this->interviewTable;
    }
}
