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
        $message[] ='Hi!';
        $message[] ='New password is ' . $user->getPassword() . '';

        $mail = new Message();
        $mail->addTo($user->getEmail())
             ->setEncoding('UTF-8')
             ->setSubject('moneyzaurus.com email reset')
             ->setFrom('moneyzaurusapp@gmail.com')
             ->setBody(implode('\r\n', $message));

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
