<?php

namespace Application\Helper\ResendPassword;

use Application\Helper\AbstractHelper;
use Zend\Mail\Message;
use Zend\Mime\Part as MimePart;
use Zend\Mime\Message as MimeMessage;

/**
 * Class Helper
 *
 * @package Application\Helper\ResendPassword
 */
class Helper extends AbstractHelper
{
    /**
     * @param \Db\ActiveRecord $user
     *
     * @return Message
     */
    public function getMailMessage(\Db\ActiveRecord $user)
    {
        $mimeParts = array();
        $message = $this->getBodyData($user);

        $html = new MimePart(implode('<br />', $message));
        $html->type = 'text/html';
        $mimeParts[] = $html;

        $body = new MimeMessage();
        $body->setParts($mimeParts);

        $mail = new Message();
        $mail->addTo($user->getEmail())
             ->setEncoding('UTF-8')
             ->setSubject('New moneyzaurus.com password')
             ->setFrom('moneyzaurusapp@gmail.com')
             ->setBody($body);

        return $mail;
    }

    /**
     * @return string
     */
    public function getNewPassword()
    {
        return uniqid();
    }

    /**
     * @param \Db\ActiveRecord $user
     *
     * @return array
     */
    protected function getBodyData(\Db\ActiveRecord $user)
    {
        $message = array();
        $message[] = '<html>';
        $message[] = '<body>';
        $message[] = '<h3>Hi ' . $user->getEmail() . '!</h3>';
        $message[] = '';
        $message[] = 'You asked for new password?';
        $message[] = '';
        $message[] = 'Here it is: <strong>' . $user->getPassword() . '</strong>';
        $message[] = '';
        $message[] = '';
        $message[] = 'Have a nice day!';
        $message[] = '';
        $message[] = '<a href="http://www.moneyzaurus.com" target="_blank">moneyzaurus.com</a>';
        $message[] = '</body>';
        $message[] = '</html>';

        return $message;
    }
}
