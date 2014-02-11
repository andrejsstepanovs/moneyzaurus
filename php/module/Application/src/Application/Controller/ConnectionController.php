<?php
namespace Application\Controller;

use Application\Form\Form\User as UserForm;
use Application\Form\Validator\User as UserValidator;
use Zend\Authentication\Storage\Session;

use Application\Form\Form\Connection as ConnectionForm;
use Application\Form\Validator\Connection as ConnectionValidator;
/**
 * Class ConnectionController
 *
 * @package Application\Controller
 */
class ConnectionController extends AbstractActionController
{
    /** @var ConnectionForm */
    protected $_connectionForm;

    /** @var ConnectionValidator */
    protected $_connectionValidator;

    /**
     * @return ConnectionForm
     */
    protected function getForm()
    {
        if (null === $this->_connectionForm) {
            $this->_connectionForm = new ConnectionForm;
        }

        return $this->_connectionForm;
    }

    /**
     * @return ConnectionValidator
     */
    protected function getValidator()
    {
        if (null === $this->_connectionValidator) {
            $this->_connectionValidator = new ConnectionValidator();
        }

        return $this->_connectionValidator;
    }

    public function indexAction()
    {
        $form = $this->getForm();

        $request = $this->getRequest();

        if ($request->isPost()) {
            $form->setInputFilter($this->getValidator()->getInputFilter());
            $form->setData($request->getPost());

            $email   = $request->getPost()->get('email');
            $message = $request->getPost()->get('message');

            if ($form->isValid()) {


            }
        }

        return array(
            'form' => $form
        );
    }

}
