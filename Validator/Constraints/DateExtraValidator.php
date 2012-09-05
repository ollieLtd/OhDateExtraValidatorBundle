<?php

namespace Oh\DateExtraValidatorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToLocalizedStringTransformer;

class DateExtraValidator extends ConstraintValidator
{

    public $constraint;

    /**
     * 
     * @param [array,float,string,\DateTime] $value The value to validate
     * @param $constraint Symfony\Component\Validator\Constraint\DateExtra
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value || '' === $value) {
            return;
        }
        
        $this->constraint = $constraint;
        
        $minConstraint = $constraint->getMinTimestamp();
        $maxConstraint = $constraint->getMaxTimestamp();
        
        if(is_object($value) && method_exists($value, '__toString')) {
            $value = (string) $value;
        }
        
        //convert value to timestamp
        if($value instanceOf \DateTime) {
            $timestamp = $value->format('U');
        } elseif(is_array($value)) {
            $array = array_merge($constraint->array, $value);
            $timestamp = mktime($array['year'], $array['month'], $array['day'], $array['hour'], $array['minute'], $array['second']);
        } elseif(is_numeric($value)) {
            $timestamp = $value;
        } elseif(is_string($value)) {

            // I do it this way to avoid Exceptions
            $dateTime = date_create($value);
            
            if(!$dateTime) {
                $this->context->addViolation($constraint->invalidMessage);
                return;
            }
            
            $dateTime->setTimezone(new \DateTimeZone($this->constraint->getTimezone()));

            $timestamp = $dateTime->format('U');
            
            if(!$timestamp) {
                $this->context->addViolation($constraint->invalidMessage);
                return;
            }
        }  elseif(is_string($value)) {
            $this->context->addViolation($constraint->invalidMessage);
            return;
        }  else {
            throw new UnexpectedTypeException($value, 'one of "Valid Date String / DateTime Object / Array / Timestamp"');
        }
        
        // The value is below the minimum
        if($minConstraint > 0){
            if($minConstraint > $timestamp) {
                $this->context->addViolation($constraint->minMessage, array(
                    '{{ value }}' => $this->formatTimestamp($timestamp), 
                    '{{ min }}' => $this->formatTimestamp($minConstraint),
                    '{{ max }}' => $this->formatTimestamp($maxConstraint),
                    ));
            }
        }
        
        // The value is above the minimum
        if($maxConstraint > 0){
            if($timestamp > $maxConstraint) {
                $this->context->addViolation($constraint->maxMessage, array(
                    '{{ value }}' => $this->formatTimestamp($timestamp), 
                    '{{ min }}' => $this->formatTimestamp($minConstraint),
                    '{{ max }}' => $this->formatTimestamp($maxConstraint),
                    ));
            }
        }

    }
    
    /**
     * uses Symfony's DateTimeToLocalizedStringTransformer to format a string
     * @param \DateTime $dateTime
     * @return string A formatted timestamp
     */
    public function getLocalisedString(\DateTime $dateTime) {
        
        $formatter = new DateTimeToLocalizedStringTransformer(null, $this->constraint->getTimezone(), $this->constraint->intlDateFormat, $this->constraint->intlTimeFormat);
        
        return $formatter->transform($dateTime);
    }
    
    /**
     * Formats a timestamp according to the constraint parameters
     * @param float $timestamp
     * @return string A formatted timestamp
     */
    public function formatTimestamp($timestamp) {
        
        // use the users local format
        if($this->constraint->isFormatIntlDate()) {
            $formatted = $this->getLocalisedString(new \DateTime('@'.$timestamp));
        }
        // otherwise assume its a string
        else {

            $dateObj = new \DateTime('@'.$timestamp);
            // the timestamp is always UTC so we have to set the timezone seperately
            $dateObj->setTimezone(new \DateTimeZone($this->constraint->getTimezone()));

            $formatted = $dateObj->format($this->constraint->getFormat());
        }
        
        return $formatted;
    }
}
