<?php
/*
 
 * @author      Mohammad Abu Musa <m.abumusa@gmail.com>
 *
 */

namespace Mautic\WebhookBundle\Validator\Constraints;

use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;



class IsJsonValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof IsJson) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\IsJson');
        }
        
        if (null === $value || '' === $value) {
            return;
        }
        $result = json_decode($value);

        if (json_last_error() !== JSON_ERROR_NONE) {
            if ($this->context instanceof ExecutionContextInterface) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ value }}', $this->formatValue($value))
                    ->setCode(IsJson::IS_NOT_JSON_ERROR)
                    ->addViolation();
            } else {
                $this->buildViolation($constraint->message)
                    ->setParameter('{{ value }}', $this->formatValue($value))
                    ->setCode(IsJson::IS_NOT_JSON_ERROR)
                    ->addViolation();
            }
        }
    }    
}