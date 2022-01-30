<?php

class GiftSubscriptionForStudents
{
    /**
     * The single instance of this class
     * @var GiftSubscriptionForStudents
     */
    private static $instance = null;

    /**
     * Returns the single instance of the main plugin class.
     *
     * @return GiftSubscriptionForStudents
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }


    public function __construct()
    {
        $formId = '3';
        add_action('gform_after_submission_' . $formId, [$this, 'addSubToCartPerStudent'], 10, 1);
        add_filter( 'gform_validation', [$this, 'addFilter'], 10, 1);
    }


    public function addSubToCartPerStudent($result)
    {
        // echo var_dump($result);
    }

    public function addFilter($result)
    {
        // Get the value of field ID 1
        $value = rgpost( 'input_17' );
        WC()->cart->add_to_cart($value, 1);
        // if( $value == 'gibberish' ) {
        //     // activate honeypot
        // }
        return $result;
    }
}

// add_action('plugins_loaded', ['GiftSubscriptionForStudents', 'instance']);
new GiftSubscriptionForStudents();
