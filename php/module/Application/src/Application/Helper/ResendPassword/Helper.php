<?php

namespace Application\Helper\ResendPassword;

use Application\Helper\AbstractHelper;
use Zend\Mail\Message;
use Zend\Mime\Part as MimePart;
use Zend\Mime\Message as MimeMessage;
use \Zend\Mime\Mime;

/**
 * Class Helper
 *
 * @package Application\Helper\ResendPassword
 */
class Helper extends AbstractHelper
{
    /**
     * @param string $toEmail
     * @param string $htmlBody
     * @param string $subject
     * @param string $fromEmail
     *
     * @return Message
     */
    public function getMailMessage($toEmail, $htmlBody, $subject, $fromEmail)
    {
        $mimeParts = array();

        $html = new MimePart($htmlBody);
        $html->type = Mime::TYPE_HTML;
        $mimeParts[] = $html;

        $body = new MimeMessage();
        $body->setParts($mimeParts);

        $mail = new Message();
        $mail->addTo($toEmail)
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
