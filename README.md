OhDateExtraValidatorBundle
==========================

A minimum and maximum date validator for Symfony2

Installation
------------

Install this bundle as usual by adding to composer.json:
    
    "oh/date-extra-validator-bundle": "dev-master"

Register the bundle in `app/AppKernel.php`:

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new Oh\DateExtraValidatorBundle\OhDateExtraValidatorBundle(),
        );
    }

Usage
------------

### Add the constraints to your model
    ...
    use Oh\DateExtraValidatorBundle\Validator\Constraints as OhAssert;

    /**
     * @ORM\Entity()
     */
    class Event
    {
        /**
         * @ORM\Column(type="datetime") 
         * 
         * @OhAssert\DateExtra(min="-1 year", max="+1 year", intlTimeFormat=\IntlDateFormatter::NONE)
         */
        protected $start_time;
    }

Or in your yml (WARNING! untested - I use annotations)

    Acme\DemoBundle\Entity\AcmeEntity:
        properties:
            start_time:
                - Oh\DateExtraValidatorBundle\Validator\Constraints\DateExtra: {min="-1 year", max="+1 year", intlTimeFormat=\IntlDateFormatter::NONE}



Options
-------

### Messages

When you validate your model through a form you should see the error messages as defined in Oh\DateExtraValidatorBundle\Validator\Constraints\DateExtra.

    public $minMessage = 'You cannot choose a date before {{ min }}.';
    public $maxMessage = 'You cannot choose a date after {{ max }}.';
    public $invalidMessage = 'The date is invalid';
    ...

Each message can use `{{ min }}`, `{{ max }}` and `{{ value }}` so you can for example put:

    /**
     * @OhAssert\DateExtra(minMessage="The date you supplied, {{ value }}, should be between {{ min }} and {{ max }}", min="-1 year", max="+1 year")
     */

The format of the date in the error messages can be a normal date string or an [IntlDateFormatter](http://uk.php.net/manual/en/class.intldateformatter.php) date.

    /**
     * @OhAssert\DateExtra(format="Y-m-d",min="-1 year", max="+1 year")
     * or
     * @OhAssert\DateExtra(intlDateFormat=\IntlDateFormatter::LONG, intlTimeFormat=\IntlDateFormatter::LONG, min="-1 year", max="+1 year")
     */

For the UK timezone Europe/London the 2 examples above would output "You cannot choose a date before 2011-09-04." and "You cannot choose a date before September 4, 2011 8:10:34 PM GMT+01:00."

You can also set the timezone manually by specifying it in the constructor (eg `timezone="Europe/London"`)

### Valid values

The class can handle most date formats; `\DateTime`, `array('year'=>2012,'month'=>9,'day'=>4)`, unix timestamp (eg `1346789811`), string (eg 2012-09-04) or an object that returns one of these values in __toString()


Credits
-------

* Ollie Harridge [ollietb](https://github.com/ollietb) as main author.