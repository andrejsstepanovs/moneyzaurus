<?php
namespace Application\Controller;

use Db\Db\ActiveRecord;
use Application\Form\Form\User as UserForm;
use Application\Form\Validator\User as UserValidator;
use Zend\Authentication\Storage\Session;
use Application\Controller\AbstractActionController;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\View\Model\ViewModel;


class UserController extends AbstractActionController
{
    /** @var \Application\Form\Form\User */
    protected $userForm;

    /** @var \Zend\Authentication\Storage\Session */
    protected $storage;

    /** @var \Db\Db\ActiveRecord */
    protected $user;

    /** @var \Application\Form\Validator\Login */
    protected $loginValidator;

    /** @var \Application\Form\Validator\User */
    protected $userValidator;


    /**
     * @return \Db\Db\ActiveRecord
     */
    public function getUser()
    {
        if (null === $this->user) {
            $this->user = new ActiveRecord('user');
        }
        return $this->user;
    }

    /**
     * @return \Zend\Authentication\Storage\Session
     */
    public function getSessionStorage()
    {
        if (null === $this->storage) {
            $this->storage = new Session();
        }

        return $this->storage;
    }

    /**
     * @return \Application\Form\Form\User
     */
    public function getUserForm()
    {
        if (null === $this->userForm) {
            $this->userForm = new UserForm();
        }

        $user = $this->getUser()->load($this->getUserId());


        $formElements = $this->userForm->getElements();

        $formElements['month_start_date']->setValue($user->getMonthStartDate());

        $formElements['default_currency']->setValueOptions($this->getCurrencyValueOptions())
                                         ->setValue($user->getDefaultCurrency());

        $formElements['email']->setValue($user->getEmail());

        return $this->userForm;
    }

    /**
     * @return \Application\Form\Validator\User
     */
    public function getUserValidator()
    {
        if (null === $this->userValidator) {
            $this->userValidator = new UserValidator();
        }

        return $this->userValidator;
    }

    /**
     * Shows user profile.
     *
     * @return array
     */
    public function indexAction()
    {
        $form = $this->getUserForm();

        /** @var \Zend\Http\PhpEnvironment\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {

            $form->setInputFilter($this->getUserValidator()->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {

                $keys = array('month_start_date', 'default_currency');

                $user = $this->getUser()->load($this->getUserId());

                foreach ($keys as $key) {
                    $user->setData($key, $request->getPost($key));
                }

                try {
                    $user->save();
                } catch (\Exception $exc) {
                    //$exc->getMessage();
                }

            } else {
                $this->flashmessenger()->addMessage('Wrong data');
            }
        }

        return array(
            'form'     => $this->getUserForm()
        );
    }

    public function downloadAction()
    {
        $filename = 'moneyzaurus_' . date('Y-m-d') . '.csv';

        header('Content-Disposition: attachment; filename=' . $filename);
        header('Content-Type: application/force-download');
        header('Content-Type: application/octet-stream');
        header('Content-Type: application/download');
        header('Content-Description: File Transfer');
        //header("Content-Length: " . filesize($filename)); // unknown

        $columns = array('id', 'price', 'date', 'created', 'item', 'group', 'currency', 'email');
        $transactions = $this->_getTransactions();

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $viewModel->setVariable('columns', $columns);
        $viewModel->setVariable('transactions', $transactions);
        return $viewModel;
    }

    /**
     * @return \Zend\Db\ResultSet\HydratingResultSet
     */
    protected function _getTransactions()
    {
        $transactionTable = array('t' => 'transaction');

        $select = new Select();
        $select->from($transactionTable)
               ->columns(array('id' => 'transaction_id', 'price', 'date', 'created' => 'date_created'))
               ->join(array('i' => 'item'), 't.id_item = i.item_id', array('item' => 'name'))
               ->join(array('g' => 'group'), 't.id_group = g.group_id', array('group' => 'name'))
               ->join(array('c' => 'currency'), 't.id_currency = c.currency_id', array('currency' => 'currency_id'))
               ->join(array('u' => 'user'), 't.id_user = u.user_id', array('email'))
               ->order('t.date ' . \Zend\Db\Sql\Select::ORDER_DESCENDING);

        $where = array(
            $this->getWhere()->equalTo('t.id_user', $this->getUserId())
        );
        $select->where($where);

        $transactions = $this->getTable('transactions');
        $table = $transactions->getTable();
        $table->setTable($transactionTable);

        /** @var $transactionsResults \Zend\Db\ResultSet\HydratingResultSet */
        $transactionsResults = $table->fetch($select)->buffer();
        return $transactionsResults;
    }


    /**
     * @return \Zend\Db\Sql\Where
     */
    private function getWhere()
    {
        return new Where();
    }

}
