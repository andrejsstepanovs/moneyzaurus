<?php

namespace Application\Helper\ResendPassword;

use Application\Helper\Mail\Helper as MailHelper;

/**
 * Class Helper
 *
 * @package Application\Helper\ResendPassword
 */
class Helper extends MailHelper
{
    /**
     * @return string
     */
    public function getNewPassword()
    {
        return uniqid();
    }
}
