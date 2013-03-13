<?php
namespace Topic\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Topic\Model\Topic;          // <-- Add this import
use Topic\Form\TopicForm;       // <-- Add this import

class TopicController extends AbstractActionController
{
    protected $topicTable;

    public function indexAction()
    {     
	return new ViewModel(array(
	    'topics' => $this->getTopicTable()->fetchAll(),
	));
    }

    public function addAction()
    {
        $form = new TopicForm();
        $form->get('submit')->setValue('Add');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $topic = new Topic();
            $form->setInputFilter($topic->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $topic->exchangeArray($form->getData());
                $this->getTopicTable()->saveTopic($topic);

                // Redirect to list of topics
                return $this->redirect()->toRoute('topic');
            }
        }
        return array('form' => $form);
    }

    public function editAction()
    {        
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('topic', array(
                'action' => 'add'
            ));
        }
        $topic = $this->getTopicTable()->getTopic($id);

        $form  = new TopicForm();
        $form->bind($topic);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($topic->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getTopicTable()->saveTopic($form->getData());

                // Redirect to list of topics
                return $this->redirect()->toRoute('topic');
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

    public function getTopicTable()
    {
        if (!$this->topicTable) {
	    $sm = $this->getServiceLocator();
	    $this->topicTable = $sm->get('Topic\Model\TopicTable');
        }
        return $this->topicTable;
    }
}
