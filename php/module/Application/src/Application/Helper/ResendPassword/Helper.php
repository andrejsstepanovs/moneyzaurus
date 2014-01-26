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
     * @param string           $message
     * @param string           $subject
     * @param string           $fromEmail
     *
     * @return Message
     */
    public function getMailMessage(\Db\ActiveRecord $user, $message, $subject, $fromEmail)
    {
        $mimeParts = array();

        $html = new MimePart(implode('<br />', $message));
        $html->type = 'text/html';
        $mimeParts[] = $html;

        $body = new MimeMessage();
        $body->setParts($mimeParts);

        $mail = new Message();
        $mail->addTo($user->getEmail())
             ->setEncoding('UTF-8')
             ->setSubject($subject)
             ->setFrom($fromEmail)
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
}
