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

    public function composeAction()
    {

    }

    public function inboxAction()
    {

    }

    public function sentAction()
    {

    }

    public function draftAction()
    {
      
    }

    public function gethintAction()
    {
        // Fill up array with names
$a[]="Anna";
$a[]="Brittany";
$a[]="Cinderella";
$a[]="Diana";
$a[]="Eva";
$a[]="Fiona";
$a[]="Gunda";
$a[]="Hege";
$a[]="Inga";
$a[]="Johanna";
$a[]="Kitty";
$a[]="Linda";
$a[]="Nina";
$a[]="Ophelia";
$a[]="Petunia";
$a[]="Amanda";
$a[]="Raquel";
$a[]="Cindy";
$a[]="Doris";
$a[]="Eve";
$a[]="Evita";
$a[]="Sunniva";
$a[]="Tove";
$a[]="Unni";
$a[]="Violet";
$a[]="Liza";
$a[]="Elizabeth";
$a[]="Ellen";
$a[]="Wenche";
$a[]="Vicky";

//get the q parameter from URL
$q=$_GET["q"];

//lookup all hints from array if length of q>0
if (strlen($q) > 0)
  {
  $hint="";
  for($i=0; $i<count($a); $i++)
    {
    if (strtolower($q)==strtolower(substr($a[$i],0,strlen($q))))
      {
      if ($hint=="")
        {
        $hint=$a[$i];
        }
      else
        {
        $hint=$hint." , ".$a[$i];
        }
      }
    }
  }

// Set output to "no suggestion" if no hint were found
// or to the correct values
if ($hint == "")
  {
  $response="no suggestion";
  }
else
  {
  $response=$hint;
  }

//output the response
echo $response;
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
