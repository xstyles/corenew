<?php

/**
 * Plugin Name:     CoreWeapons Classes Manager
 * Description:     Schedule and Attend CoreWeapons classes. Limit students to only attend classes of purchased subscriptions.
 * Author:          Giridhar
 * Text Domain:     cw-cw_class
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         cw-classes-manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;


if (!class_exists('CW_ClassesManager')) :

  class CW_ClassesManager
  {
    /**
     * The single instance of this class
     * @var CW_ClassesManager
     */
    private static $instance = null;

    /**
     * Required BuddyPress version for the plugin.
     *
     * @package CW Classes Manager
     *
     * @since CW Classes Manager (1.0.0)
     *
     * @var      string
     */
    public static $required_bp_version = '2.5.0';

    /**
     * BuddyPress config.
     *
     * @package CW Classes Manager
     *
     * @since CW Classes Manager (1.0.0)
     *
     * @var      array
     */
    public static $bp_config = array();

    /**
     * Returns the single instance of the main plugin class.
     *
     * @return CW_ClassesManager
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
      // First you will set your plugin's globals
      $this->setup_globals();
      // Then include the needed files
      $this->includes();
      // Then hook to BuddyPress actions & filters
      $this->setup_hooks();
    }

    /**
     * Sets some globals for the plugin
     *
     * @package CW Classes Manager
     *
     * @since CW Classes Manager (1.0.0)
     */
    private function setup_globals()
    {

      // Define a global that will hold the current version number
      $this->version       = '1.4.2';

      // Define a global to get the textdomain of your plugin.
      $this->domain        = 'cw-classes-manager';

      $this->file          = __FILE__;
      $this->basename      = plugin_basename($this->file);

      // Define a global that we can use to construct file paths throughout the component
      $this->plugin_dir    = plugin_dir_path($this->file);

      // Define a global that we can use to construct file paths starting from the includes directory
      $this->includes_dir  = trailingslashit($this->plugin_dir . 'includes');

      // Define a global that we can use to construct file paths starting from the includes directory
      $this->lang_dir      = trailingslashit($this->plugin_dir . 'languages');


      $this->plugin_url    = plugin_dir_url($this->file);
      $this->includes_url  = trailingslashit($this->plugin_url . 'includes');

      // Define a global that we can use to construct url to the javascript scripts needed by the component
      $this->plugin_js     = trailingslashit($this->includes_url . 'js');

      // Define a global that we can use to construct url to the css needed by the component
      $this->plugin_css    = trailingslashit($this->includes_url . 'css');
    }

    /**
     * Include the component's loader.
     *
     * @package CW Classes Manager
     *
     * @since CW Classes Manager (1.0.0)
     */
    private function includes()
    {
      if (self::bail())
        return;

      require($this->includes_dir . 'cw-classes-manager-loader.php');
    }

    /**
     * Sets the key hooks to add an action or a filter to
     *
     * @package CW Classes Manager
     *
     * @since CW Classes Manager (1.0.0)
     */
    private function setup_hooks()
    {

      if (!self::bail()) {
        // Load the component
        add_action('bp_loaded', 'cw_classes_manager_load_component');

        // Enqueue the needed script and css files
        add_action('bp_enqueue_scripts', array($this, 'enqueue_scripts'));

        // loads the languages..
        add_action('bp_loaded', array($this, 'load_textdomain'));
      } else {
        // Display a warning message in network admin or admin
        add_action(self::$bp_config['network_active'] ? 'network_admin_notices' : 'admin_notices', array($this, 'warning'));
      }
    }

    /**
     * Display a warning message to admin
     *
     * @package CW Classes Manager
     *
     * @since CW Classes Manager (1.0.0)
     */
    public function warning()
    {
      $warnings = array();

      if (!self::version_check()) {
        $warnings[] = sprintf(__('CW Classes Manager requires at least version %s of BuddyPress.', 'cw-classes-manager'), self::$required_bp_version);
      }

      if (!empty(self::$bp_config)) {
        $config = self::$bp_config;
      } else {
        $config = self::config_check();
      }

      if (!bp_core_do_network_admin() && !$config['blog_status']) {
        $warnings[] = __('CW Classes Manager requires to be activated on the blog where BuddyPress is activated.', 'cw-classes-manager');
      }

      if (bp_core_do_network_admin() && !$config['network_status']) {
        $warnings[] = __('CW Classes Manager and BuddyPress need to share the same network configuration.', 'cw-classes-manager');
      }

      if (!empty($warnings)) :
?>
        <div id="message" class="error">
          <?php foreach ($warnings as $warning) : ?>
            <p><?php echo esc_html($warning); ?></p>
          <?php endforeach; ?>
        </div>
<?php
      endif;
    }

    /**
     * Enqueue scripts if your component is loaded
     *
     * @package CW Classes Manager
     *
     * @since CW Classes Manager (1.0.0)
     */
    public function enqueue_scripts()
    {
      $load_scripts = apply_filters('cw_classes_manager_load_scripts', bp_is_current_component('cw_classes_manager'));

      if (empty($load_scripts)) {
        return;
      }

      $suffix = SCRIPT_DEBUG ? '' : '.min';

      wp_register_script('cw-classes-manager-plupload', includes_url("js/plupload/wp-plupload$suffix.js"), array(), $this->version, 1);
      wp_localize_script('cw-classes-manager-plupload', 'pluploadL10n', array());
      wp_register_script('cw-classes-manager-media-views', includes_url("js/media-views$suffix.js"), array('utils', 'media-models', 'cw-classes-manager-plupload', 'jquery-ui-sortable'), $this->version, 1);
      wp_register_script('cw-classes-manager-media-editor', includes_url("js/media-editor$suffix.js"), array('shortcode', 'cw-classes-manager-media-views'), $this->version, 1);
      wp_register_script('cw-classes-manager-modal', $this->plugin_js . "cw-classes-manager-backbone$suffix.js", array('cw-classes-manager-media-editor', 'jquery-ui-datepicker'), $this->version, 1);

      // Allow themes to override modal style
      $modal_style = apply_filters('cw_classes_manager_modal_css', $this->plugin_css . "cw_class-editor$suffix.css", $suffix);
      wp_register_style('cw-classes-manager-modal-style', $modal_style, array('media-views'), $this->version);

      // Allow themes to override global style
      $global_style = apply_filters(
        'cw_classes_manager_global_css',
        array(
          'style' => $this->plugin_css . "cw_class$suffix.css",
          'deps'  =>  array('dashicons'),
        ),
        $suffix
      );

      wp_enqueue_style('cw-classes-manager-style', $global_style['style'], (array) $global_style['deps'], $this->version);
      wp_enqueue_script('cw-classes-manager-script', $this->plugin_js . "cw_class$suffix.js", array('jquery'), $this->version, 1);
      wp_localize_script('cw-classes-manager-script', 'cw_classes_manager_vars', array(
        'confirm'  => esc_html__('Are you sure you want to cancel this class ?', 'cw-classes-manager'),
        'noaccess' => esc_html__('You do not have access to this class.', 'cw-classes-manager'),
      ));
    }

    /** Utilities *****************************************************************************/

    /**
     * Checks BuddyPress version
     *
     * @package CW Classes Manager
     *
     * @since CW Classes Manager (1.0.0)
     */
    public static function version_check()
    {
      // taking no risk
      if (!defined('BP_VERSION'))
        return false;

      return version_compare(BP_VERSION, self::$required_bp_version, '>=');
    }

    /**
     * Checks if your plugin's config is similar to BuddyPress
     *
     * @package CW Classes Manager
     *
     * @since CW Classes Manager (1.0.0)
     */
    public static function config_check()
    {
      /**
       * blog_status    : true if your plugin is activated on the same blog
       * network_active : true when your plugin is activated on the network
       * network_status : BuddyPress & your plugin share the same network status
       */
      self::$bp_config = array(
        'blog_status'    => false,
        'network_active' => false,
        'network_status' => true
      );

      if (get_current_blog_id() == bp_get_root_blog_id()) {
        self::$bp_config['blog_status'] = true;
      }

      $network_plugins = get_site_option('active_sitewide_plugins', array());

      // No Network plugins
      if (empty($network_plugins))
        return self::$bp_config;

      $cw_classes_manager_basename = plugin_basename(__FILE__);

      // Looking for BuddyPress and your plugin
      $check = array(buddypress()->basename, $cw_classes_manager_basename);

      // Are they active on the network ?
      $network_active = array_diff($check, array_keys($network_plugins));

      // If result is 1, your plugin is network activated
      // and not BuddyPress or vice & versa. Config is not ok
      if (count($network_active) == 1)
        self::$bp_config['network_status'] = false;

      // We need to know if the plugin is network activated to choose the right
      // notice ( admin or network_admin ) to display the warning message.
      self::$bp_config['network_active'] = isset($network_plugins[$cw_classes_manager_basename]);

      return self::$bp_config;
    }

    /**
     * Bail if BuddyPress config is different than this plugin
     *
     * @package CW Classes Manager
     *
     * @since CW Classes Manager (1.0.0)
     */
    public static function bail()
    {
      $retval = false;

      $config = self::config_check();

      if (!self::version_check() || !$config['blog_status'] || !$config['network_status'])
        $retval = true;

      return $retval;
    }

    /**
     * Loads the translation files
     *
     * @package CW Classes Manager
     *
     * @since CW Classes Manager (1.0.0)
     *
     * @uses get_locale() to get the language of WordPress config
     * @uses load_texdomain() to load the translation if any is available for the language
     * @uses load_plugin_textdomain() to load the translation if any is available for the language
     */
    public function load_textdomain()
    {
      // Traditional WordPress plugin locale filter
      $locale        = apply_filters('plugin_locale', get_locale(), $this->domain);
      $mofile        = sprintf('%1$s-%2$s.mo', $this->domain, $locale);

      // Setup paths to a rendez-vous subfolder in WP LANG DIR
      $mofile_global = WP_LANG_DIR . '/rendez-vous/' . $mofile;

      // Look in global /wp-content/languages/rendez-vous folder
      if (!load_textdomain($this->domain, $mofile_global)) {

        // Look in local /wp-content/plugins/rendez-vous/languages/ folder
        // or /wp-content/languages/plugins/
        // load_plugin_textdomain($this->domain, false, basename($this->plugin_dir) . '/languages');
      }
    }

    /**
     * Get the component name of the plugin
     *
     * @package CW Classes Manager
     *
     * @since CW Classes Manager (1.2.0)
     *
     * @uses apply_filters() call 'cw_classes_manager_get_component_name' to override default component name
     */
    public static function get_component_name()
    {
      return apply_filters('cw_classes_manager_get_component_name', __('CW Classes Manager', 'cw-classes-manager'));
    }

    /**
     * Get the component slug of the plugin
     *
     * @package CW Classes Manager
     *
     * @since CW Classes Manager (1.2.0)
     *
     * @uses apply_filters() call 'cw_classes_manager_get_component_slug' to override default component slug
     */
    public static function get_component_slug()
    {
      // Defining the slug in this way makes it possible for site admins to override it
      if (!defined('CW_CLASSES_MANAGER_SLUG')) {
        define('CW_CLASSES_MANAGER_SLUG', 'classes');
      }

      return CW_CLASSES_MANAGER_SLUG;
    }

    /**
     * Get the schedule slug of the component
     *
     * @package CW Classes Manager
     *
     * @since CW Classes Manager (1.2.0)
     *
     * @uses apply_filters() call 'cw_classes_manager_get_schedule_slug' to override default schedule slug
     */
    public static function get_schedule_slug()
    {
      return 'schedule';
    }

    /**
     * Get the attend slug of the component
     *
     * @package CW Classes Manager
     *
     * @since CW Classes Manager (1.2.0)
     *
     * @uses apply_filters() call 'cw_classes_manager_get_attend_slug' to override default attend slug
     */
    public static function get_attend_slug()
    {
      return 'attend';
    }
  }
endif;

// BuddyPress is loaded and initialized, let's start !
function cw_class()
{
  return CW_ClassesManager::instance();
}

/**
 * Transformers, roll out!
 */
add_action('bp_include', 'cw_class');
