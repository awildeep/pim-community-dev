<?php

namespace Pim\Bundle\ImportExportBundle\Exception;

/**
 * Invalid import value exception
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvalidValueException extends \InvalidArgumentException
{
    protected $rawMessage;
    protected $messageParameters;

    public function __construct($rawMessage, $messageParameters)
    {
        $this->rawMessage = $rawMessage;
        $this->messageParameters = $messageParameters;

        parent::__construct(strtr($rawMessage, $messageParameters));
    }

    public function getRawMessage()
    {
        return $this->rawMessage;
    }

    public function getMessageParameters()
    {
        return $this->messageParameters;
    }
}
