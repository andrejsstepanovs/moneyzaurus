<?php

namespace Application\Log;

use Zend\Log\Logger as ZendLogger;
use Zend\Log\Writer\Stream as LogWriterStream;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class Logger
 *
 * @package Application\Log
 */
class Logger implements FactoryInterface
{
    /** log directory path */
    const DIR_NAME = './data/log/';

    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed|ZendLogger
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this->_getLogger()->addWriter($this->_getWriter());
    }

    /**
     * @return string
     */
    protected function _getFileName()
    {
        $filename = str_replace('/', DIRECTORY_SEPARATOR, self::DIR_NAME);
        $filename .= 'error' . date('Y') . '.log';

        return $filename;
    }

    /**
     * @return LogWriterStream
     */
    protected function _getWriter()
    {
        $filename = $this->_getFileName();
        $writer = new LogWriterStream($filename);

        return $writer;
    }

    /**
     * @return ZendLogger
     */
    protected function _getLogger()
    {
        $logger = new ZendLogger;

        return $logger;
    }
}
