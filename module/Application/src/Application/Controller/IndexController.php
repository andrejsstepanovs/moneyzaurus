<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;


class IndexController extends AbstractActionController
{
    public function indexAction()
    {

        $inlineScript = $this->getServiceLocator()->get('viewhelpermanager')->get('inlineScript');
//        $inlineScript->createData();

//        \DEBUG::dump($inlineScript);

        $inlineScript->appendScript('
            $(document).ready(
                //$.mobile.showPageLoadingMsg("a", "No spinner", true);
            );
         ');

//        $result = $myHelper('some value'); //if it implements __invoke() method
//
//
//        $scripts = $this->inlineScript();
//        \DEBUG::dump($scripts);

        return array(
            'messages' => $this->flashmessenger()->getMessages()
        );
    }

}
