<?php

/**
 * CW Class Screens.
 *
 * Manage screens
 *
 * @package CW Class
 * @subpackage Screens
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Main Screen Class.
 *
 * @package CW Class
 * @subpackage Screens
 *
 * @since CW Class (1.0.0)
 */
class cw_class_Screens
{

  /**
   * The constructor
   *
   * @package CW Class
   * @subpackage Screens
   *
   * @since CW Class (1.0.0)
   */
  public function __construct()
  {
    $this->setup_globals();
    $this->setup_filters();
    $this->setup_actions();
  }

  /**
   * Starts screen management
   *
   * @package CW Class
   * @subpackage Screens
   *
   * @since CW Class (1.0.0)
   */
  public static function manage_screens()
  {
    $manager = cw_class();

    if (empty($manager->screens)) {
      $manager->screens = new self;
    }

    return $manager->screens;
  }

  /**
   * Set some globals
   *
   * @package CW Class
   * @subpackage Screens
   *
   * @since CW Class (1.0.0)
   */
  public function setup_globals()
  {

    $this->template      = '';
    $this->template_dir  = cw_class()->includes_dir . 'templates';
    $this->screen = '';
  }

  /**
   * Set filters
   *
   * @package CW Class
   * @subpackage Screens
   *
   * @since CW Class (1.0.0)
   */
  private function setup_filters()
  {
    if (bp_is_current_component('cw_class')) {
      add_filter('bp_located_template',   array($this, 'template_filter'), 20, 2);
      add_filter('bp_get_template_stack', array($this, 'add_to_template_stack'), 10, 1);
    }
  }

  /**
   * Set Actions
   *
   * @package CW Class
   * @subpackage Screens
   *
   * @since CW Class (1.0.0)
   */
  private function setup_actions()
  {
    add_action('cw_class_schedule', array($this, 'schedule_actions'));
  }

  /**
   * Filter the located template
   *
   * @package CW Class
   * @subpackage Screens
   *
   * @since CW Class (1.0.0)
   */
  public function template_filter($found_template = '', $templates = array())
  {
    $bp = buddypress();

    // Bail if theme has it's own template for content.
    if (!empty($found_template))
      return $found_template;

    // Current theme do use theme compat, no need to carry on
    if ($bp->theme_compat->use_with_current_theme)
      return false;

    return apply_filters('cw_class_load_template_filter', $found_template);
  }

  /**
   * Add template dir to stack (not used)
   *
   * @package CW Class
   * @subpackage Screens
   *
   * @since CW Class (1.0.0)
   */
  public function add_to_template_stack($templates = array())
  {
    // Adding the plugin's provided template to the end of the stack
    // So that the theme can override it.
    return array_merge($templates, array($this->template_dir));
  }

  /**
   * Shedule Screen
   *
   * @package CW Class
   * @subpackage Screens
   *
   * @since CW Class (1.0.0)
   */
  public static function schedule_screen()
  {

    do_action('cw_class_schedule');

    self::load_template('', 'schedule');
  }

  /**
   * Attend Screen
   *
   * @package CW Class
   * @subpackage Screens
   *
   * @since CW Class (1.0.0)
   */
  public static function attend_screen()
  {

    do_action('cw_class_attend');

    // We'll only use members/single/plugins
    self::load_template('', 'attend');
  }

  /**
   * Load the templates
   *
   * @package CW Class
   * @subpackage Screens
   *
   * @since CW Class (1.0.0)
   */
  public static function load_template($template = '', $screen = '')
  {
    $manager = cw_class();
    /****
     * Displaying Content
     */
    $manager->screens->template = $template;

    if (!empty($manager->screens->screen))
      $screen = $manager->screens->screen;

    if (buddypress()->theme_compat->use_with_current_theme && !empty($template)) {
      add_filter('bp_get_template_part', array(__CLASS__, 'template_part'), 10, 3);
    } else {
      // You can only use this method for users profile pages
      if (!bp_is_directory()) {

        $manager->screens->template = 'members/single/plugins';
        add_action('bp_template_title',   "cw_class_{$screen}_title");
        add_action('bp_template_content', "cw_class_{$screen}_content");
      }
    }

    /* This is going to look in wp-content/plugins/[plugin-name]/includes/templates/ first */
    bp_core_load_template(apply_filters("cw_class_template_{$screen}", $manager->screens->template));
  }

  /**
   * Filter template part (not used)
   *
   * @package CW Class
   * @subpackage Screens
   *
   * @since CW Class (1.0.0)
   */
  public static function template_part($templates, $slug, $name)
  {
    if ($slug != 'members/single/plugins') {
      return $templates;
    }
    return array(cw_class()->screens->template . '.php');
  }

  /**
   * Set Actions
   *
   * @package CW Class
   * @subpackage Screens
   *
   * @since CW Class (1.0.0)
   */
  public function schedule_actions()
  {
    $this->screen = cw_class_handle_actions();
  }
}
add_action('bp_init', array('cw_class_Screens', 'manage_screens'));
