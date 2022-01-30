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


    private function __construct()
    {
        $formId = '3';
        add_action('gform_after_submission_' . $formId, [$this, '_addSubToCartPerStudent'], 10, 1);
    }


    private function _addSubToCartPerStudent($result)
    {
        var_dump($result);
        // add_filter( 'gform_validation', [$this, '_addFilter'], 10, 1);
    }

    private function _addFilter($result)
    {
        // Get the value of field ID 1
        $value = rgpost( 'input_17' );
        if( $value == 'gibberish' ) {
            // activate honeypot 
        }
        var_dump($result);
        return $result;
    }
}

add_action('plugins_loaded', array('GiftSubscriptionForStudents', 'instance'));
