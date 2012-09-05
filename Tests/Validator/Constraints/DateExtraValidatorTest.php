<?php

namespace Oh\DateExtraValidatorBundle\Tests\Validator\Constraints;

use Oh\DateExtraValidatorBundle\Validator\Constraints\DateExtra;
use Oh\DateExtraValidatorBundle\Validator\Constraints\DateExtraValidator;

class DateExtraValidatorTest extends \PHPUnit_Framework_TestCase
{
    protected $context;
    protected $validator;

    protected function setUp()
    {
        $this->context = $this->getMock('Symfony\Component\Validator\ExecutionContext', array(), array(), '', false);
        $this->validator = new DateExtraValidator();
        $this->validator->initialize($this->context);
    }

    protected function tearDown()
    {
        $this->context = null;
        $this->validator = null;
    }

    public function testNullIsValid()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate(null, new DateExtra());
    }

    public function testEmptyStringIsValid()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate('', new DateExtra());
    }

    public function testDateTimeClassIsValid()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate(new \DateTime(), new DateExtra());
    }

    /**
     * @expectedException Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testExpectsStringCompatibleType()
    {
        $this->validator->validate(new \stdClass(), new DateExtra());
    }

    /**
     * @dataProvider getValidDates
     */
    public function testValidDates($date)
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate($date, new DateExtra());
    }

    public function getValidDates()
    {
        return array(
            array('2010-01-01'),
            array('1955-12-12'),
            array('2030-05-31'),
            array(array('year'=>2012,'month'=>'10','day'=>10)),
            array(1346710062)
        );
    }

    /**
     * @dataProvider getInvalidDates
     */
    public function testInvalidDates($date)
    {
        $constraint = new DateExtra(array(
            'invalidMessage' => 'myMessage'
        ));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('myMessage');

        $this->validator->validate($date, $constraint);
    }
    
    public function getInvalidDates()
    {
        return array(
            array('foobar'),
            array('foobar 2010-13-01'),
            array('2010-13-01 foobar'),
            array('2010-13-01'),
            array('2010-04-32')
        );
    }
    
    public function testMinDate()
    {
        $constraint = new DateExtra(array(
            'min' => '2012-09-01',
        ));
        
        $date = '2012-08-01';
        
        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('You cannot choose a date before {{ min }}.', array(
                '{{ min }}' => 'Sep 1, 2012 12:00 AM', 
                '{{ max }}' => 'Jan 19, 2038 3:14 AM', 
                '{{ value }}' => 'Aug 1, 2012 12:00 AM'));
        
        $this->validator->validate($date, $constraint);
        
    }
    
    public function testMaxDate()
    {
        $constraint = new DateExtra(array(
            'max' => '2012-10-01'
        ));
 
        
        $date = '2012-11-01';
        
        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('You cannot choose a date after {{ max }}.', array(
                '{{ min }}' => 'Dec 13, 1901 8:45 PM', 
                '{{ max }}' => 'Oct 1, 2012 12:00 AM', 
                '{{ value }}' => 'Nov 1, 2012 12:00 AM'));
        
        $this->validator->validate($date, $constraint);
        
    }
    
    public function testDateFormat()
    {
        
        $constraint = new DateExtra(array(
            'format' => 'd-m-Y H:i:s',
            'max' => '2012-10-01',
        ));
        
        $date = '2012-11-01';
        
        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('You cannot choose a date after {{ max }}.', array(
                '{{ min }}' => '13-12-1901 20:45:54', 
                '{{ max }}' => '01-10-2012 00:00:00',
                '{{ value }}' => '01-11-2012 00:00:00'));
        
        $this->validator->validate($date, $constraint);
        
    }
    
    public function testValidDate()
    {
        $constraint = new DateExtra(array(
            'min' => '2012-09-01',
            'max' => '2012-10-01'
        ));
        

        $date = '2012-09-10';
        
        $this->context->expects($this->never())
            ->method('addViolation');   
        
        $this->validator->validate($date, $constraint);
        
    }
    
    public function testRelativeDate()
    {
        $constraint = new DateExtra(array(
            'min' => '-1 year',
            'max' => '+1 year'
        ));
        

        $date = date('d-m-Y');
        
        $this->context->expects($this->never())
            ->method('addViolation');   
        
        $this->validator->validate($date, $constraint);
        
    }
    
    public function testRelativeDateMin()
    {
        $constraint = new DateExtra(array(
            'min' => '-1 year',
        ));
        

        $date = date('d-m-Y', strtotime('Aug 1, 2009 12:00 AM'));
        
        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('You cannot choose a date before {{ min }}.', array(
                '{{ min }}' => date('M j, Y g:i A', strtotime('-1 year')), 
                '{{ max }}' => 'Jan 19, 2038 3:14 AM',
                '{{ value }}' => 'Aug 1, 2009 12:00 AM'));
        
        $this->validator->validate($date, $constraint);
        
    }
    
    public function testIntlFormat()
    {
        $constraint = new DateExtra(array(
            'intlTimeFormat' => \IntlDateFormatter::NONE,
            'min' => 'Aug 1, 2010'
        ));
        

        $date = date('d-m-Y', strtotime('Aug 1, 2009'));
        
        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('You cannot choose a date before {{ min }}.', array(
                '{{ min }}' => 'Aug 1, 2010',
                '{{ max }}' => 'Jan 19, 2038',
                '{{ value }}' => 'Aug 1, 2009'));
        
        $this->validator->validate($date, $constraint);
        
    }

    public function testTimezone()
    {
        $constraint = new DateExtra(array(
            'format' => 'd-m-Y H:i:s',
            'min' => '2012-10-01 01:00:00',
            'timezone' => 'UTC'
        ));
        
        $dateObj = new \DateTime('2012-10-01 01:00:00', new \DateTimeZone('Europe/London'));

        $date = $dateObj->getTimestamp();
        
        // 1 hour difference
        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('You cannot choose a date before {{ min }}.', array(
                '{{ min }}' => '01-10-2012 01:00:00',
                '{{ max }}' => '19-01-2038 03:14:07',
                '{{ value }}' => '01-10-2012 00:00:00'));
        
        $this->validator->validate($date, $constraint);
        
    }
    
    public function testFarFutureValues() {
        $constraint = new DateExtra(array(
            'max' => '2012-10-01 01:00:00',
            'format' => 'd-m-Y H:i:s',
        ));
        
        $date = '2055-10-01';
        
        // 1 hour difference
        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('You cannot choose a date after {{ max }}.', array(
                '{{ min }}' => '13-12-1901 20:45:54',
                '{{ max }}' => '01-10-2012 01:00:00',
                '{{ value }}' => '01-10-2055 00:00:00'));
        
        $this->validator->validate($date, $constraint);
    }

}
