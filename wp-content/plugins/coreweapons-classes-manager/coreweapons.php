<?php

/**
 * Plugin Name:     Coreweapons Classes Manager
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     Schedule and attend online education classes via video-conference (Google Meet)
 * Author:          VGo Solutions
 * Author URI:      https://vgosolutions.com
 * Text Domain:     coreweapons
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Coreweapons
 */

class CoreWeaponsClassesManager
{
  /**
   * The single instance of this class
   * @var CoreWeaponsClassesManager
   */
  private static $instance = null;

  /**
   * Returns the single instance of the main plugin class.
   *
   * @return CoreWeaponsClassesManager
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
    // Needs to run after Rendez Vouz has loaded its dependant classes
    self::load_dependant_classes();
  }


  /**
   * Loads classes after plugins for classes dependant on other plugin files.
   */
  private static function load_dependant_classes()
  {
    // Add gifting basic membership for seat (deposit)
    require_once 'includes/gift-subscription-for-students.php';

    // Add gifting scription for students
    require_once 'includes/gift-subscription-for-students.php';

    // Add all students that can access a newly created class/meeting
    require_once 'includes/schedule-class-for-students.php';

    // Register student subscription input field for gravity forms
    require_once 'includes/gf-subscription-per-student-field.php';

    //Repeater form(11) for Parents with student hidden field and subject choices radiobuttons prepopulated from another nested form(10)
    require_once 'includes/repeater-form-student-purchase.php';

    // Purchase subject course for students (children)
    // require_once 'includes/subject-purchase-form.php';
    // wp_enqueue_script('subject-purchase-form-repeater-field-validation', __DIR__ . '/includes/repeater-form-validation.js', [], false, true);
  }
}

/**
 * Transformers, roll out!
 */
add_action('plugins_loaded', ['CoreWeaponsClassesManager', 'instance']);
