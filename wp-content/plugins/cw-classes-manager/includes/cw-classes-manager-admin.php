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
class CW_Admin
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
    add_action('bp_admin_init',            array($this, 'maybe_update'));

    // javascript
    add_action('bp_admin_enqueue_scripts', array($this, 'enqueue_script'));

    // Page
    add_action(bp_core_admin_hook(),       array($this, 'admin_menu'));

    add_action('admin_head',               array($this, 'admin_head'), 999);

    add_action('bp_admin_tabs',            array($this, 'admin_tab'));
  }

  /**
   * Update plugin version if needed
   *
   * @package CW Class
   * @subpackage Admin
   *
   * @since CW Class (1.2.0)
   */
  public function maybe_update()
  {
    if ((int) get_current_blog_id() !== (int) bp_get_root_blog_id()) {
      return;
    }

    $db_version = bp_get_option('cw_class-version', 0);

    if (version_compare($db_version, cw_class()->version, '<')) {

      if ((float) $db_version < 1.4) {
        // Make sure to install emails only once!
        remove_action('bp_core_install_emails', 'cw_class_install_emails');

        /**
         * Make sure the function to install emails is reachable
         * even if the Notifications component is not active.
         */
        if (!bp_is_active('notifications')) {
          require_once(cw_class()->includes_dir . 'cw_class-notifications.php');
        }

        // Install emails
        cw_class_install_emails();
      }

      do_action('cw_class_upgrade');

      // Update the db version
      bp_update_option('cw_class-version', cw_class()->version);
    }
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

    $suffix = SCRIPT_DEBUG ? '' : '.min';
    $manager = cw_class();

    wp_enqueue_style('cw-classes-manager-admin-style', $manager->plugin_css . "cw-classes-manager-admin$suffix.css", array('dashicons'), $manager->version);
    wp_enqueue_script('cw-classes-manager-admin-backbone', $manager->plugin_js . "cw-classes-manager-admin-backbone$suffix.js", array('wp-backbone'), $manager->version, true);
    wp_localize_script('cw-classes-manager-admin-backbone', 'cw_class_admin_vars', array(
      'nonce'               => wp_create_nonce('cw-classes-manager-admin'),
      'placeholder_default' => esc_html__('Name of your class type.', 'cw_class'),
      'placeholder_saving'  => esc_html__('Saving the class type...', 'cw_class'),
      'placeholder_success' => esc_html__('Success: type saved.', 'cw_class'),
      'placeholder_error'   => esc_html__('Error: type not saved', 'cw_class'),
      'alert_notdeleted'    => esc_html__('Error: type not deleted', 'cw_class'),
      'current_edited_type' => esc_html__('Editing: %s', 'cw_class'),
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
    $page  = bp_core_do_network_admin()  ? 'settings.php' : 'options-general.php';

    $hook = add_submenu_page(
      $page,
      __('cw_class Settings', 'cw_class'),
      __('cw_class Settings', 'cw_class'),
      'manage_options',
      'cw_class',
      array($this, 'admin_display')
    );

    add_action("admin_head-$hook", array($this, 'modify_highlight'));
  }

  /**
   * Modify highlighted menu
   *
   * @package CW Class
   * @subpackage Admin
   *
   * @since CW Class (1.2.0)
   */
  public function modify_highlight()
  {
    global $plugin_page, $submenu_file;

    // This tweaks the Settings subnav menu to show only one BuddyPress menu item
    if ($plugin_page == 'cw_class') {
      $submenu_file = 'bp-components';
    }
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

      <h1><?php _e('BuddyPress Settings', 'cw_class'); ?></h1>

      <h2 class="nav-tab-wrapper"><?php bp_core_admin_tabs(esc_html__('CoreWeapons Classes', 'cw_class')); ?></h2>

      <h3><?php esc_html_e('Types', 'cw_class'); ?></h3>

      <p class="description cw_class-guide">
        <?php esc_html_e('Add your type in the field below and hit the return key to save it.', 'cw_class'); ?>
        <?php esc_html_e('To update a type, select it in the list, edit the name and hit the return key to save it.', 'cw_class'); ?>
      </p>

      <div class="cw_class-terms-admin">
        <div class="cw_class-list-terms"></div>
        <div class="cw_class-form"></div>
      </div>

      <script id="tmpl-cw_class-term" type="text/html">
        <span class="cw_class-term-name">{{data.name}}</span> <span class="cw_class-term-actions"><a href="#" class="cw_class-edit-item" data-term_id="{{data.id}}" title="<?php esc_attr_e('Edit type', 'cw_class'); ?>"></a> <a href="#" class="cw_class-delete-item" data-term_id="{{data.id}}" title="<?php esc_attr_e('Delete type', 'cw_class'); ?>"></a></span>
      </script>

      <script id="tmpl-cw_class-new-type-form" type="text/html">
        <style>
          .class-type-form {
            max-width: 25em;
          }

          .class-type-form--container {
            display: flex;
            flex-direction: column;
          }

          .form-field {
            width: 100%;
            margin-top: 1rem;
          }

          .form-field.actions {
            align-self: center;
          }

          .class-type-form .button {
            display: inline-flex;
            flex-direction: row;
            align-items: center;
          }

          .form-field .negative {
            border-color: var(--e-context-error-color);
            color: var(--e-context-error-color);
          }
        </style>

        <div class="class-type-form">
          <form class="class-type-form--container">
            <div class="form-field">
              <label for="type-name">Class type</label>
              <input type="text" id="type-name" />
            </div>
            <div class="form-field">
              <label for="courses">Courses</label>
              <!-- <select name="courses[]" id="courses" multiple>
                <option value="foo" label="Foo"></option>
                <option value="bar" label="Bar"></option>
              </select> -->
              {{ JSON.stringify(data) }}
              <# _.each(data.courses, function (course, index) { #>
              <div class="form-field--checkbox">
                <input type="checkbox" id="course-{{course.index}}" name="courses" value="{{ course.id }}">
                <label for="vehicle1"> {{ course.name }}</label><br>
              </div>
              <# }) #>
            </div>
            <div class="form-field actions">
              <button id="ok" class="button action"><span class="dashicons dashicons-yes"></span></button>
              <button id="cancel" class="button action"><span class="dashicons dashicons-no"></span></button>
              <button id="delete" class="button action negative"><span class="dashicons dashicons-trash"></span></button>
              <button id="edit" class="button action"><span class="dashicons dashicons-edit"></span></button>
            </div>
          </form>
        </div>
      </script>

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

  /**
   * cw_class tab
   *
   * @package CW Class
   * @subpackage Admin
   *
   * @since CW Class (1.2.0)
   */
  public function admin_tab()
  {
    $class = false;

    $current_screen = get_current_screen();

    // Set the active class
    if (!empty($current_screen->id) && strpos($current_screen->id, 'cw_class') !== false) {
      $class = "nav-tab-active";
    }
  ?>
    <a href="<?php echo esc_url(bp_get_admin_url(add_query_arg(array('page' => 'cw_class'), 'admin.php'))); ?>" class="nav-tab <?php echo $class; ?>"><?php esc_html_e('Class Scheduler', 'cw_class'); ?></a>
<?php
  }
}

add_action('bp_init', array('CW_Admin', 'start'), 14);
