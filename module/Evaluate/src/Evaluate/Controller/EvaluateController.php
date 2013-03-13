<?php
namespace Evaluate\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Evaluate\Model\Evaluate;          // <-- Add this import
use Evaluate\Form\EvaluateForm;       // <-- Add this import

class EvaluateController extends AbstractActionController
{
    protected $evaluateTable;

    public function indexAction()
    {     
	return new ViewModel(array(
	    'evaluates' => $this->getEvaluateTable()->fetchAll(),
	));
    }

    public function addAction()
    {
        $form = new EvaluateForm();
        $form->get('submit')->setValue('Add');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $evaluate = new Evaluate();
            $form->setInputFilter($evaluate->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $evaluate->exchangeArray($form->getData());
                $this->getEvaluateTable()->saveEvaluate($evaluate);

                // Redirect to list of evaluates
                return $this->redirect()->toRoute('evaluate');
            }
        }
        return array('form' => $form);
    }

    public function editAction()
    {        
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('evaluate', array(
                'action' => 'add'
            ));
        }
        $evaluate = $this->getEvaluateTable()->getEvaluate($id);

        $form  = new EvaluateForm();
        $form->bind($evaluate);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($evaluate->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getEvaluateTable()->saveEvaluate($form->getData());

                // Redirect to list of evaluates
                return $this->redirect()->toRoute('evaluate');
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

    public function getEvaluateTable()
    {
        if (!$this->evaluateTable) {
	    $sm = $this->getServiceLocator();
	    $this->evaluateTable = $sm->get('Evaluate\Model\EvaluateTable');
        }
        return $this->evaluateTable;
    }
}
