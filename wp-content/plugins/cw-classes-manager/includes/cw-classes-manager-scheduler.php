<?php

/**
 * CW Class Admin
 *
 * Admin class
 *
 * @package CW Class
 * @subpackage Activity
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Load Admin class.
 *
 * @package CW Class
 * @subpackage Admin
 *
 * @since CW Class (1.2.0)
 */
class CW_Scheduler
{

  /**
   * Setup Admin.
   *
   * @package CW Class
   * @subpackage Admin
   *
   * @since CW Class (1.2.0)
   *
   * @uses buddypress() to get BuddyPress main instance.
   */
  public static function start()
  {
    $manager = cw_class();

    if (empty($manager->admin)) {
      $manager->admin = new self;
    }

    return $manager->admin;
  }

  /**
   * The constructor
   *
   * @package CW Class
   * @subpackage Admin
   *
   * @since CW Class (1.2.0)
   */
  public function __construct()
  {
    $this->setup_globals();
    $this->setup_hooks();
  }

  /**
   * Set some globals.
   *
   * @package CW Class
   * @subpackage Admin
   *
   * @since CW Class (1.2.0)
   */
  private function setup_globals()
  {
  }

  /**
   * Set the actions & filters
   *
   * @package CW Class
   * @subpackage Admin
   *
   * @since CW Class (1.2.0)
   */
  private function setup_hooks()
  {
    // update plugin's db version
    // add_action('bp_admin_init',            array($this, 'maybe_update'));

    // javascript
    add_action('bp_admin_enqueue_scripts', array($this, 'enqueue_script'));

    // Page
    add_action(bp_core_admin_hook(),       array($this, 'admin_menu'));

    add_action('admin_head',               array($this, 'admin_head'), 999);

    // add_action('bp_admin_tabs',            array($this, 'admin_tab'));
  }

  /**
   * Enqueue script
   *
   * @package CW Class
   * @subpackage Admin
   *
   * @since CW Class (1.2.0)
   */
  public function enqueue_script()
  {
    $current_screen = get_current_screen();

    // Bail if we're not on the cw_class page
    if (empty($current_screen->id) || strpos($current_screen->id, 'cw_class') === false) {
      return;
    }

    // $suffix = SCRIPT_DEBUG ? '' : '.min';
    $suffix = '';
    $manager = cw_class();

    // wp_enqueue_style('cw-classes-manager-admin-style', $manager->plugin_css . "cw-classes-manager-admin$suffix.css", array('dashicons'), $manager->version);
    // wp_enqueue_script('cw-classes-manager-admin-backbone', $manager->plugin_js . "cw-classes-manager-admin-backbone$suffix.js", array('wp-backbone'), $manager->version, true);
    add_action('wp_enqueue_scripts', [ $this, 'cw_calender_css' ]);
    add_action('wp_enqueue_scripts', [ $this, 'cw_calender_js' ]);

    wp_enqueue_script('cw-classes-manager-scheduler', $manager->plugin_js . "cw-classes-manager-scheduler$suffix.js", [], $manager->version, true);
    wp_localize_script('cw-classes-manager-admin-backbone', 'cw_class_admin_vars', array(
      'nonce'                  => wp_create_nonce('cw-classes-manager-scheduler'),
      'placeholder_subject'    => esc_html__('Subject', 'cw_class'),
      'placeholder_start_time' => esc_html__('Start time', 'cw_class'),
      'placeholder_duration'   => esc_html__('Class duration', 'cw_class'),
      'placeholder_topic'      => esc_html__('Topic of the class', 'cw_class'),
      'placeholder_draft'      => esc_html__('Published', 'cw_class'),
      'placeholder_saving'     => esc_html__('Saving class...', 'cw_class'),
      'placeholder_success'    => esc_html__('Success: class saved.', 'cw_class'),
      'placeholder_error'      => esc_html__('Error: class not saved', 'cw_class'),
      'alert_notdeleted'       => esc_html__('Error: class not deleted', 'cw_class'),
      'current_editing_class'  => esc_html__('Editing: %s', 'cw_class'),
    ));
  }

  /**
   * Set the plugin's BuddyPress sub menu
   *
   * @package CW Class
   * @subpackage Admin
   *
   * @since CW Class (1.2.0)
   */
  public function admin_menu()
  {
    // $page  = bp_core_do_network_admin()  ? 'settings.php' : 'options-general.php';

    add_menu_page(
      __('cw_class Settings', 'cw_class'),
      __('cw_class Settings', 'cw_class'),
      'manage_options',
      'schedule-classes',
      array($this, 'admin_display'),
      'dashicons-calendar-alt',
      1,
    );
  }

  /**
   * Display the admin
   *
   * @package CW Class
   * @subpackage Admin
   *
   * @since CW Class (1.2.0)
   */
  public function admin_display()
  {
?>
    <div class="wrap">

      <h1><?php _e('Schedule classes', 'cw_class'); ?></h1>

      <div class="cw-class-scheduler-admin">
        <div class="calendar"></div>
      </div>
    </div>
  <?php
  }

  /**
   * Hide submenu
   *
   * @package CW Class
   * @subpackage Admin
   *
   * @since CW Class (1.2.0)
   */
  public function admin_head()
  {
    $page  = bp_core_do_network_admin()  ? 'settings.php' : 'options-general.php';

    remove_submenu_page($page, 'cw_class');
  }

  // Enqueue Fullcalender in wp

  function cw_calender_css()
  {
    wp_register_style('fullcalendercss', 'https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.css');
    wp_enqueue_style('fullcalendercss');
  }

  function cw_calender_js()
  {
    wp_register_script('fullcalenderjs', 'https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.js');
    wp_enqueue_script('fullcalenderjs');
  }
}

add_action('bp_init', array('CW_Scheduler', 'start'), 14);
