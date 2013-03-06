<?php
namespace Message\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Message\Model\Message;          // <-- Add this import
use Message\Form\MessageForm;       // <-- Add this import

class MessageController extends AbstractActionController
{
    protected $messageTable;

    public function indexAction()
    {     
	return new ViewModel(array(
	    'messages' => $this->getMessageTable()->fetchAll(),
	));
    }

    public function addAction()
    {
        $form = new MessageForm();
        $form->get('submit')->setValue('Add');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $message = new Message();
            $form->setInputFilter($message->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $message->exchangeArray($form->getData());
                $this->getMessageTable()->saveMessage($message);

                // Redirect to list of messages
                return $this->redirect()->toRoute('message');
            }
        }
        return array('form' => $form);
    }

    public function editAction()
    {
    }

    public function deleteAction()
    {
    }

    public function getMessageTable()
    {
        if (!$this->messageTable) {
	    $sm = $this->getServiceLocator();
	    $this->messageTable = $sm->get('Message\Model\MessageTable');
        }
        return $this->messageTable;
    }
}
