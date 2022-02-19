<?php

/**
 * CW Class Loader.
 *
 * Loads the component
 *
 * @package CW Class
 * @subpackage Component
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * CW_Class_Component class
 *
 * @package cw_class
 * @subpackage Component
 *
 * @since CW Class (1.0.0)
 */
class CW_Class_Component extends BP_Component
{

  /**
   * Constructor method
   *
   * @package cw_class
   * @subpackage Component
   *
   * @since CW Class (1.0.0)
   */
  function __construct()
  {
    $bp = buddypress();

    parent::start(
      'cw_class',
      cw_class()->get_component_name(),
      cw_class()->includes_dir
    );

    $this->includes();

    $bp->active_components[$this->id] = '1';

    /**
     * Only register the post type on the blog where BuddyPress is activated.
     */
    if (get_current_blog_id() == bp_get_root_blog_id()) {
      add_action('init', array(&$this, 'register_post_types'));
    }
  }

  /**
   * Include files
   *
   * @package cw_class
   * @subpackage Component
   *
   * @since CW Class (1.0.0)
   */
  function includes($includes = array())
  {

    // Files to include
    $includes = array(
      'cw-classes-manager-filters.php',
      'cw-classes-manager-screens.php',
      'cw-classes-manager-editor.php',
      'cw-classes-manager-classes.php',
      'cw-classes-manager-ajax.php',
      'cw-classes-manager-parts.php',
      'cw-classes-manager-template.php',
      'cw-classes-manager-functions.php',
    );

    if (bp_is_active('notifications')) {
      $includes[] = 'cw-classes-manager-notifications.php';
    }

    if (bp_is_active('activity')) {
      $includes[] = 'cw-classes-manager-activity.php';
    }

    if (bp_is_active('groups')) {
      $includes[] = 'cw-classes-manager-groups.php';
    }

    if (is_admin()) {
      $includes[] = 'cw-classes-manager-admin.php';
    }

    parent::includes($includes);
  }

  /**
   * Set up globals
   *
   * @package cw_class
   * @subpackage Component
   *
   * @since CW Class (1.0.0)
   */
  function setup_globals($args = array())
  {

    // Set up the $globals array to be passed along to parent::setup_globals()
    $args = array(
      'slug'                  => cw_class()->get_component_slug(),
      'notification_callback' => 'cw_class_format_notifications',
      'search_string'         => __('Search cw_class...', 'cw_class'),
    );

    // Let BP_Component::setup_globals() do its work.
    parent::setup_globals($args);

    /**
     * Filter to change user's default subnav
     *
     * @since CW Class (1.1.0)
     *
     * @param string default subnav to use (shedule or attend)
     */
    $this->default_subnav = apply_filters('cw_class_member_default_subnav', cw_class()->get_schedule_slug());

    $this->subnav_position = array(
      'schedule' => 10,
      'attend'   => 20,
    );

    if (cw_class()->get_attend_slug() == $this->default_subnav) {
      $this->subnav_position['attend'] = 5;
    }
  }

  /**
   * Set up navigation.
   *
   * @package cw_class
   * @subpackage Component
   *
   * @since CW Class (1.0.0)
   */
  function setup_nav($main_nav = array(), $sub_nav = array())
  {
    // Add 'cw_class' to the main navigation
    $main_nav = array(
      'name'               => cw_class()->get_component_name(),
      'slug'               => $this->slug,
      'position'             => 80,
      'screen_function'     => array('cw_class_Screens', 'public_screen'),
      'default_subnav_slug' => $this->default_subnav
    );

    // Stop if there is no user displayed or logged in
    if (!is_user_logged_in() && !bp_displayed_user_id())
      return;

    // Determine user to use
    if (bp_displayed_user_domain()) {
      $user_domain = bp_displayed_user_domain();
    } elseif (bp_loggedin_user_domain()) {
      $user_domain = bp_loggedin_user_domain();
    } else {
      return;
    }

    $cw_class_link = trailingslashit($user_domain . $this->slug);

    // Add a subnav item under the main cw_class tab
    $sub_nav[] = array(
      'name'            =>  __('Schedule', 'cw_class'),
      'slug'            => cw_class()->get_schedule_slug(),
      'parent_url'      => $cw_class_link,
      'parent_slug'     => $this->slug,
      'screen_function' => array('cw_class_Screens', 'schedule_screen'),
      'position'        => $this->subnav_position['schedule']
    );

    // Add a subnav item under the main cw_class tab
    $sub_nav[] = array(
      'name'            =>  __('Attend', 'cw_class'),
      'slug'            => cw_class()->get_attend_slug(),
      'parent_url'      => $cw_class_link,
      'parent_slug'     => $this->slug,
      'screen_function' => array('cw_class_Screens', 'attend_screen'),
      'position'        => $this->subnav_position['attend']
    );

    parent::setup_nav($main_nav, $sub_nav);
  }

  /**
   * Set up the component entries in the WordPress Admin Bar.
   *
   * @package cw_class
   * @subpackage Component
   *
   * @since CW Class (1.0.0)
   */
  public function setup_admin_bar($wp_admin_nav = array())
  {
    $bp = buddypress();

    // Menus for logged in user
    if (is_user_logged_in()) {

      // Setup the logged in user variables
      $user_domain      = bp_loggedin_user_domain();
      $cw_class_link = trailingslashit($user_domain . $this->slug);

      // Add the "Example" sub menu
      $wp_admin_nav[0] = array(
        'parent' => $bp->my_account_menu_id,
        'id'     => 'my-account-' . $this->id,
        'title'  => __('cw_class', 'cw_class'),
        'href'   => trailingslashit($cw_class_link)
      );

      // Personal
      $wp_admin_nav[$this->subnav_position['schedule']] = array(
        'parent' => 'my-account-' . $this->id,
        'id'     => 'my-account-' . $this->id . '-schedule',
        'title'  => __('Schedule', 'cw_class'),
        'href'   => trailingslashit($cw_class_link . cw_class()->get_schedule_slug())
      );

      // Screen two
      $wp_admin_nav[$this->subnav_position['attend']] = array(
        'parent' => 'my-account-' . $this->id,
        'id'     => 'my-account-' . $this->id . '-attend',
        'title'  => __('Attend', 'cw_class'),
        'href'   => trailingslashit($cw_class_link . cw_class()->get_attend_slug())
      );

      // Sort WP Admin Nav
      ksort($wp_admin_nav);
    }

    parent::setup_admin_bar($wp_admin_nav);
  }

  /**
   * Register the cw_class post type
   *
   * @package cw_class
   * @subpackage Component
   *
   * @since CW Class (1.0.0)
   */
  function register_post_types()
  {
    // Set up some labels for the post type
    $manager_labels = array(
      'name'               => __('cw_class',                                                  'cw_class'),
      'singular'           => _x('cw_class',                   'cw_class singular',           'cw_class'),
      'menu_name'          => _x('cw_class',                   'cw_class menu name',          'cw_class'),
      'all_items'          => _x('All cw_class',               'cw_class all items',          'cw_class'),
      'singular_name'      => _x('cw_class',                   'cw_class singular name',      'cw_class'),
      'add_new'            => _x('Add New cw_class',           'cw_class add new',            'cw_class'),
      'edit_item'          => _x('Edit cw_class',              'cw_class edit item',          'cw_class'),
      'new_item'           => _x('New cw_class',               'cw_class new item',           'cw_class'),
      'view_item'          => _x('View cw_class',              'cw_class view item',          'cw_class'),
      'search_items'       => _x('Search cw_class',            'cw_class search items',       'cw_class'),
      'not_found'          => _x('No cw_class Found',          'cw_class not found',          'cw_class'),
      'not_found_in_trash' => _x('No cw_class Found in Trash', 'cw_class not found in trash', 'cw_class')
    );

    $manager_args = array(
      'label'              => _x('cw_class',                    'cw_class label',              'cw_class'),
      'labels'            => $manager_labels,
      'public'            => false,
      'rewrite'           => false,
      'show_ui'           => false,
      'show_in_admin_bar' => false,
      'show_in_nav_menus' => false,
      'capabilities'      => cw_class_get_caps(),
      'capability_type'   => array('cw_class', 'cw_classes'),
      'delete_with_user'  => true,
      'supports'          => array('title', 'author')
    );

    // Register the post type for attachements.
    register_post_type('cw_class', $manager_args);

    parent::register_post_types();
  }

  /**
   * Register the cw_class types taxonomy
   *
   * @package cw_class
   * @subpackage Component
   *
   * @since CW Class (1.2.0)
   */
  public function register_taxonomies()
  {
    // Register the taxonomy
    register_taxonomy('cw_class_type', 'cw_class', array(
      'public' => false,
    ));
  }
}

/**
 * Loads rendez vous component into the $bp global
 *
 * @package cw_class
 * @subpackage Component
 *
 * @since CW Class (1.0.0)
 */
function cw_classes_manager_load_component()
{
  $bp = buddypress();

  $bp->cw_class = new CW_Class_Component;
}
