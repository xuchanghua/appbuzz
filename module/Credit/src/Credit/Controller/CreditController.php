<?php
namespace Credit\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Credit\Model\Credit;          // <-- Add this import
use Credit\Form\CreditForm;       // <-- Add this import

class CreditController extends AbstractActionController
{
    protected $creditTable;

    public function indexAction()
    {     
        /*
	return new ViewModel(array(
	    'credits' => $this->getCreditTable()->fetchAll(),
	));*/
        $request = $this->getRequest();
        $keyword = trim($request->getQuery(''));
        $page = intval($request->getQuery('page',1));
        $paginator = $this->getCreditTable()->getPaginator($keyword, $page, 5, 1);
        $view = new ViewModel(
            array(
                'credits' => $this->getCreditTable()->fetchAll(),
            )
        );
        $view->setVariable('paginator', $paginator);
        return $view;
    }

    public function addAction()
    {
        $form = new CreditForm();
        $form->get('submit')->setValue('Add');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $credit = new Credit();
            $form->setInputFilter($credit->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $credit->exchangeArray($form->getData());
                $this->getCreditTable()->saveCredit($credit);

                // Redirect to list of credits
                return $this->redirect()->toRoute('credit');
            }
        }
        return array('form' => $form);
    }

    public function editAction()
    {        
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('credit', array(
                'action' => 'add'
            ));
        }
        $credit = $this->getCreditTable()->getCredit($id);

        $form  = new CreditForm();
        $form->bind($credit);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($credit->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getCreditTable()->saveCredit($form->getData());

                // Redirect to list of credits
                return $this->redirect()->toRoute('credit');
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

    public function getCreditTable()
    {
        if (!$this->creditTable) {
	    $sm = $this->getServiceLocator();
	    $this->creditTable = $sm->get('Credit\Model\CreditTable');
        }
        return $this->creditTable;
    }
}
