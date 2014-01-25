<?php

namespace Application\Helper\ResendPassword;

use Application\Helper\AbstractHelper;
use Zend\Mail\Message;

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
        $message = array();
        $message[] ='Hi! New password is ' . $user->getPassword() . '';

        $htmlPart = new \Zend\Mime\Part(implode('', $message));
        $htmlPart->type = 'text/html';

        $textPart = new \Zend\Mime\Part(implode('\r\n', $message));
        $textPart->type = 'text/plain';

        $body = new \Zend\Mime\Message();
        $body->setParts(array($htmlPart, $textPart));


        $mail = new Message();
        $mail->addTo($user->getEmail());
        $mail->setEncoding('UTF-8');
        $mail->setSubject('moneyzaurus.com email reset');
        $mail->setFrom('service@moneyzaurus.com');
        $mail->setBody($body);

        return $mail;
    }

    /**
     * @return string
     */
    public function getNewPassword()
    {
        return uniqid();
    }
}
