<?php

namespace Oh\DateExtraValidatorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class DateExtra extends Constraint
{
    /**
     * Messages. Valid replacements for each message are {{ min }}, {{ max }}, {{ value }}
     * @var string
     */
    public $minMessage = 'You cannot choose a date before {{ min }}.';
    public $maxMessage = 'You cannot choose a date after {{ max }}.';
    public $invalidMessage = 'The date is invalid';
    
    /**
     * Min and max dates for validation
     * eg '+1 year', '-1 year', 'now' etc
     * The defaults are the dates that correspond to the minimum and maximum values for a 32-bit signed integer
     * @see http://uk3.php.net/manual/en/function.date.php
     * @var string
     */
    public $min = 'Fri, 13 Dec 1901 20:45:54 GMT';
    public $max = 'Tue, 19 Jan 2038 03:14:07 GMT';
    
    /**
     * You can display the date in the error message according to this format
     * false value uses the intl format for the supplied timezone
     * eg. 'd/M/Y'
     * @var string
     */
    public $format = false;
    
    
    /**
     * One of
     *  \IntlDateFormatter::NONE,
        \IntlDateFormatter::FULL,
        \IntlDateFormatter::LONG,
        \IntlDateFormatter::MEDIUM,
        \IntlDateFormatter::SHORT,
     * @var IntlDateFormatter static
     */
    public $intlDateFormat = \IntlDateFormatter::MEDIUM;
    
    public $intlTimeFormat = \IntlDateFormatter::SHORT;
    
    /**
     * These are the defaults which are merged with the date array value, if it's supplied
     * @var array
     */
    public $array = array('year'=>0, 'month'=>0, 'day'=>0, 'hour'=>0, 'minute'=>0, 'second'=>0);
    
    /**
     * The timezone in string format eg. 'Europe/London'
     * @var string
     */
    public $timezone = false;
    
    /**
     * Returns the timestamp for the supplied min value 
     * @return float
     */
    public function getMinTimestamp() {
        return strtotime($this->min);
    }
    
    /**
     * Returns the timestamp for the supplied max value 
     * @return float
     */
    public function getMaxTimestamp() {
        return strtotime($this->max);
    }
    
    /**
     * Returns the timezone if specified
     * @return string
     */
    public function getTimezone()
    {
        if(!$this->timezone) {
            return date_default_timezone_get();
        }
        
        return $this->timezone;

    }
    
    public function getFormat() 
    {
        return $this->format;
    }
    
    /**
     * Should we use the IntlDateFormatter class to format the result
     * @see Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToLocalizedStringTransformer
     * @see http://uk.php.net/manual/en/class.intldateformatter.php
     * 
     * @return boolean
     */
    public function isFormatIntlDate() 
    {
        if(!$this->getFormat()) {
            return true;
        }
        
        return false;
    }

}
