<?php
/*
 
 * @author      Mohammad Abu Musa <m.abumusa@gmail.com>
 *
 */

namespace Mautic\WebhookBundle\Validator\Constraints;
use Symfony\Component\Validator\Constraint;

class IsJson extends Constraint
{
    const IS_NOT_JSON_ERROR = 'bc0b5fa9-1f6c-42e9-a28f-c61ad3501d5f';

    protected static $errorNames = array(
        self::IS_NOT_JSON_ERROR => 'IS_NOT_JSON_ERROR',
    );

    public $message = 'This value should be JSON.';
    
}


