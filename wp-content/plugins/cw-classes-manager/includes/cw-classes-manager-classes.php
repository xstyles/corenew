<?php

/**
 * CW Class Classes.
 *
 * Editor & Crud Classes
 *
 * @package CW Class
 * @subpackage Classes
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * CW Class Editor Class.
 *
 * This class is used to create the
 * cw_class
 *
 * @since CW Class (1.0.0)
 */
class cw_class_Editor
{

  private static $settings = array();

  private function __construct()
  {
  }

  /**
   * Set the settings
   *
   * @since CW Class (1.0.0)
   */
  public static function set($editor_id, $settings)
  {
    $set = bp_parse_args($settings,  array(
      'component'       => 'cw_class',
      'status'          => 'public',
      'btn_caption'     => __('New cw_class', 'cw_class'),
      'btn_class'       => 'btn-cw_class',
      'action'          => 'cw_class_create',
      'group_id'        => null,
    ), 'cw_class_editor_args');

    self::$settings = array_merge($set, array('cw_class_button_id' => '#' . $editor_id));
    return $set;
  }

  /**
   * Display the button to launch the Editor
   *
   * @since CW Class (1.0.0)
   */
  public static function editor($editor_id, $settings = array())
  {
    $set = self::set($editor_id, $settings);

    $load_editor = apply_filters('cw_class_load_editor', bp_is_my_profile());

    if (current_user_can('publish_cw_classes') && !empty($load_editor)) {

      bp_button(array(
        'id'                => 'create-' . $set['component'] . '-' . $set['status'],
        'component'         => 'cw_class',
        'must_be_logged_in' => true,
        'block_self'        => false,
        'wrapper_id'        => $editor_id,
        'wrapper_class'     => $set['btn_class'],
        'link_class'        => 'add-' .  $set['status'],
        'link_href'         => '#',
        'link_title'        => $set['btn_caption'],
        'link_text'         => $set['btn_caption']
      ));
    }

    self::launch($editor_id);
  }

  /**
   * Starts the editor
   *
   * @uses cw_class_enqueue_editor()
   *
   * @since CW Class (1.0.0)
   */
  public static function launch($editor_id)
  {
    $args = self::$settings;

    // time to enqueue script
    cw_class_enqueue_editor($args);
  }
}

/**
 * cw_class "CRUD" Class.
 *
 * @since CW Class (1.0.0)
 */
class cw_class_Item
{
  public $id;
  public $organizer;
  public $title;
  public $venue;
  public $type;
  public $description;
  public $duration;
  public $privacy;
  public $status;
  public $days;
  public $attendees;
  public $report;
  public $older_date;
  public $def_date;
  public $modified;
  public $group_id;

  /**
   * Constructor.
   *
   * @since CW Class (1.0.0)
   */
  function __construct($id = 0)
  {
    if (!empty($id)) {
      $this->id = $id;
      $this->populate();
    }
  }

  /**
   * request an item id
   *
   * @uses get_post()
   */
  public function populate()
  {
    $cw_class       = get_post($this->id);

    if (is_a($cw_class, 'WP_Post')) {
      $this->id          = $cw_class->ID;
      $this->organizer   = $cw_class->post_author;
      $this->title       = $cw_class->post_title;
      $this->venue       = get_post_meta($cw_class->ID, '_cw_class_venue', true);
      $this->type        = cw_class_get_type($cw_class->ID);
      $this->description = $cw_class->post_excerpt;
      $this->duration    = get_post_meta($cw_class->ID, '_cw_class_duration', true);
      $this->privacy     = 'draft' == $cw_class->post_status ? get_post_meta($cw_class->ID, '_cw_class_status', true) : $cw_class->post_status;
      $this->status      = $cw_class->post_status;
      $this->days        = get_post_meta($cw_class->ID, '_cw_class_days', true);
      $this->attendees   = get_post_meta($this->id, '_cw_class_attendees');
      $this->report      = $cw_class->post_content;
      $this->older_date  = false;

      if (!empty($this->days)) {
        $timestamps = array_keys($this->days);
        rsort($timestamps);
        $this->older_date = date_i18n('Y-m-d H:i:s', $timestamps[0]);
      }

      $this->def_date    = get_post_meta($cw_class->ID, '_cw_class_defdate', true);
      $this->modified    = $cw_class->post_modified;
      $this->group_id    = get_post_meta($cw_class->ID, '_cw_class_group_id', true);
    }
  }

  /**
   * Save a cw_class.
   *
   * @since CW Class (1.0.0)
   */
  public function save()
  {
    $this->id          = apply_filters_ref_array('cw_class_id_before_save',          array($this->id,          &$this));
    $this->organizer   = apply_filters_ref_array('cw_class_organizer_before_save',   array($this->organizer,   &$this));
    $this->title       = apply_filters_ref_array('cw_class_title_before_save',       array($this->title,       &$this));
    $this->venue       = apply_filters_ref_array('cw_class_venue_before_save',       array($this->venue,       &$this));
    $this->type        = apply_filters_ref_array('cw_class_type_before_save',        array($this->type,        &$this));
    $this->description = apply_filters_ref_array('cw_class_description_before_save', array($this->description, &$this));
    $this->duration    = apply_filters_ref_array('cw_class_duration_before_save',    array($this->duration,    &$this));
    $this->privacy     = apply_filters_ref_array('cw_class_privacy_before_save',     array($this->privacy,     &$this));
    $this->status      = apply_filters_ref_array('cw_class_status_before_save',      array($this->status,      &$this));
    $this->days        = apply_filters_ref_array('cw_class_days_before_save',        array($this->days,        &$this));
    $this->attendees   = apply_filters_ref_array('cw_class_attendees_before_save',   array($this->attendees,   &$this));
    $this->report      = apply_filters_ref_array('cw_class_report_before_save',      array($this->report,      &$this));
    $this->older_date  = apply_filters_ref_array('cw_class_older_date_before_save',  array($this->older_date,  &$this));
    $this->def_date    = apply_filters_ref_array('cw_class_def_date_before_save',    array($this->def_date,    &$this));
    $this->modified    = apply_filters_ref_array('cw_class_modified_before_save',    array($this->modified,    &$this));
    $this->group_id    = apply_filters_ref_array('cw_class_group_id_before_save',    array($this->group_id,    &$this));

    // Use this, not the filters above
    do_action_ref_array('cw_class_before_save', array(&$this));

    if (empty($this->organizer) || empty($this->title)) {
      return false;
    }

    if (empty($this->status)) {
      $this->status = 'publish';
    }

    // Update.
    if ($this->id) {

      $wp_update_post_args = array(
        'ID'         => $this->id,
        'post_author'   => $this->organizer,
        'post_title'   => $this->title,
        'post_type'     => 'cw_class',
        'post_excerpt'   => $this->description,
        'post_status'   => !empty($this->privacy) ? 'private' : $this->status
      );

      // The report is saved once the class date is past
      if (!empty($this->report)) {
        $wp_update_post_args['post_content'] = $this->report;
      }

      // reset privacy to get rid of the meta now the post has been published
      $this->privacy  = '';
      $this->group_id = get_post_meta($this->id, '_cw_class_group_id', true);

      $result = wp_update_post($wp_update_post_args);

      // Insert.
    } else {

      $wp_insert_post_args = array(
        'post_author'   => $this->organizer,
        'post_title'   => $this->title,
        'post_type'     => 'cw_class',
        'post_excerpt'   => $this->description,
        'post_status'   => 'draft'
      );

      $result = wp_insert_post($wp_insert_post_args);

      // We only need to do that once
      if ($result) {
        if (!empty($this->days) && is_array($this->days)) {
          update_post_meta($result, '_cw_class_days', $this->days);
        }

        // Group
        if (!empty($this->group_id)) {
          update_post_meta($result, '_cw_class_group_id', $this->group_id);
        }
      }
    }

    // Saving metas !
    if (!empty($result)) {

      if (!empty($this->venue)) {
        update_post_meta($result, '_cw_class_venue', $this->venue);
      } else {
        delete_post_meta($result, '_cw_class_venue');
      }

      if (!empty($this->duration)) {
        update_post_meta($result, '_cw_class_duration', $this->duration);
      } else {
        delete_post_meta($result, '_cw_class_duration');
      }

      if (!empty($this->privacy)) {
        update_post_meta($result, '_cw_class_status', $this->privacy);
      } else {
        delete_post_meta($result, '_cw_class_status');
      }

      if (!empty($this->def_date)) {
        update_post_meta($result, '_cw_class_defdate', $this->def_date);
      } else {
        delete_post_meta($result, '_cw_class_defdate');
      }

      if (!empty($this->attendees) && is_array($this->attendees)) {
        $this->attendees = array_map('absint', $this->attendees);

        $in_db = get_post_meta($result, '_cw_class_attendees');

        if (empty($in_db)) {

          foreach ($this->attendees as $attendee) {
            add_post_meta($result, '_cw_class_attendees', absint($attendee));
          }
        } else {
          $to_delete = array_diff($in_db, $this->attendees);
          $to_add    = array_diff($this->attendees, $in_db);

          if (!empty($to_delete)) {
            // Delete item ids
            foreach ($to_delete as $del_attendee) {
              delete_post_meta($result, '_cw_class_attendees', absint($del_attendee));
              // delete user's preferences
              self::attendees_pref($result, $del_attendee);
            }
          }

          if (!empty($to_add)) {
            // Add item ids
            foreach ($to_add as $add_attendee) {
              add_post_meta($result, '_cw_class_attendees', absint($add_attendee));
            }
          }
        }
      } else {
        delete_post_meta($result, '_cw_class_attendees');
      }

      // Set cw_class type
      cw_class_set_type($result, $this->type);

      do_action_ref_array('cw_class_after_meta_update', array(&$this));
    }

    do_action_ref_array('cw_class_after_save', array(&$this));

    return $result;
  }

  /**
   * Set an attendee's preferences.
   *
   * @since CW Class (1.0.0)
   */
  public static function attendees_pref($id = 0, $user_id = 0, $prefs = array())
  {
    if (empty($id) || empty($user_id)) {
      return false;
    }

    $days      = get_post_meta($id, '_cw_class_days', true);
    $attendees = get_post_meta($id, '_cw_class_attendees');

    if (empty($days) || !is_array($days)) {
      return false;
    }

    $check_days = array_keys($days);

    foreach ($check_days as $day) {
      // User has not set or didn't chose this day so far
      if (!in_array($user_id, $days[$day])) {
        if (in_array($day, $prefs))
          $days[$day] = array_merge($days[$day], array($user_id));
        // User choosed this day, remove it if not in prefs
      } else {
        if (!in_array($day, $prefs))
          $days[$day] = array_diff($days[$day], array($user_id));
      }
    }

    update_post_meta($id, '_cw_class_days', $days);

    // We have a guest! Should only happen for public cw_class
    if (!in_array($user_id, $attendees) && !empty($prefs)) {
      add_post_meta($id, '_cw_class_attendees', absint($user_id));
    }

    return true;
  }

  /**
   * The selection query
   *
   * @since CW Class (1.0.0)
   * @param array $args arguments to customize the query
   * @uses bp_parse_args
   */
  public static function get($args = array())
  {

    $defaults = array(
      'attendees' => array(), // one or more user ids who may attend to the class
      'organizer' => false,   // the author id of the class
      'per_page'  => 20,
      'page'      => 1,
      'search'    => false,
      'exclude'   => false,   // comma separated list or array of class ids.
      'orderby'   => 'modified',
      'order'     => 'DESC',
      'group_id'  => false,
    );

    $r = bp_parse_args($args, $defaults, 'cw_class_get_query_args');

    $cw_class_status = array('publish', 'private');

    $draft_status = apply_filters('cw_class_get_query_draft_status', bp_is_my_profile());

    if ($draft_status || bp_current_user_can('bp_moderate')) {
      $cw_class_status[] = 'draft';
    }

    $query_args = array(
      'post_status'   => $cw_class_status,
      'post_type'       => 'cw_class',
      'posts_per_page' => $r['per_page'],
      'paged'         => $r['page'],
      'orderby'      => $r['orderby'],
      'order'          => $r['order'],
    );

    if (!empty($r['organizer'])) {
      $query_args['author'] = $r['organizer'];
    }

    if (!empty($r['exclude'])) {
      $exclude = $r['exclude'];

      if (!is_array($exclude)) {
        $exclude = explode(',', $exclude);
      }

      $query_args['post__not_in'] = $exclude;
    }

    // component is defined, we can zoom on specific ids
    if (!empty($r['attendees'])) {
      // We really want an array!
      $attendees = (array) $r['attendees'];

      $query_args['meta_query'] = array(
        array(
          'key'     => '_cw_class_attendees',
          'value'   => $attendees,
          'compare' => 'IN',
        )
      );
    }

    if (!empty($r['group_id'])) {
      $group_query = array(
        'key'     => '_cw_class_group_id',
        'value'   => $r['group_id'],
        'compare' => '=',
      );

      if (empty($query_args['meta_query'])) {
        $query_args['meta_query'] = array($group_query);
      } else {
        $query_args['meta_query'][] = $group_query;
      }
    }

    if (!empty($r['type'])) {
      $query_args['tax_query'] = array(array(
        'field'    => 'slug',
        'taxonomy' => 'cw_class_type',
        'terms'    => $r['type'],
      ));
    }

    $cw_class_items = new WP_Query(apply_filters('cw_class_query_args', $query_args));

    return array('cw_class_items' => $cw_class_items->posts, 'total' => $cw_class_items->found_posts);
  }

  /**
   * Delete a cw_class
   *
   * @since CW Class (1.0.0)
   * @uses wp_delete_post()
   */
  public static function delete($cw_class_id = 0)
  {
    if (empty($cw_class_id))
      return false;

    $deleted = wp_delete_post($cw_class_id, true);

    return $deleted;
  }
}


if (!class_exists('cw_class_Upcoming_Widget')) :
  /**
   * List the upcoming cw_class for the loggedin user
   *
   * @since 1.4.0
   */
  class cw_class_Upcoming_Widget extends WP_Widget
  {

    /**
     * Constructor
     */
    public function __construct()
    {
      $widget_ops = array('description' => __('List the upcoming cw_class for the loggedin user.', 'cw_class'));
      parent::__construct(false, $name = __('Upcoming cw_class', 'cw_class'), $widget_ops);
    }

    /**
     * Register the widget
     */
    public static function register_widget()
    {
      register_widget('cw_class_Upcoming_Widget');
    }

    /**
     * Filter the query for this specific widget use
     */
    public function filter_cw_class_query($query_args = array())
    {
      $upcoming_args = array_merge(
        $query_args,
        array(
          'post_status' => array('private', 'publish'),
          'meta_query'  => array(
            'relation' => 'AND',
            array(
              'key'     => '_cw_class_attendees',
              'value'   => array(bp_loggedin_user_id()),
              'compare' => 'IN',
            ),
            'cw_class_date' => array(
              'key'     => '_cw_class_defdate',
              'value'   => bp_core_current_time(true, 'timestamp'),
              'compare' => '>=',
            )
          ),
          'orderby' => 'cw_class_date',
          'order'   => 'ASC',
        )
      );

      $allowed_keys = array(
        'post_status'    => true,
        'post_type'      => true,
        'posts_per_page' => true,
        'paged'          => true,
        'orderby'        => true,
        'order'          => true,
        'meta_query'     => true
      );

      return array_intersect_key($upcoming_args, $allowed_keys);
    }

    /**
     * Display the widget on front end
     */
    public function widget($args = array(), $instance = array())
    {
      // Display nothing if the current user is not set
      if (!is_user_logged_in()) {
        return;
      }

      // Default per_page is 5
      $number = 5;

      // No cw_class items to show !? Stop!
      if (!empty($instance['number'])) {
        $number = (int) $instance['number'];
      }

      add_filter('cw_class_query_args', array($this, 'filter_cw_class_query'), 10, 1);

      $has_cw_class = cw_manager_has_classes(array('per_page' => $number, 'no_cache' => true));

      remove_filter('cw_class_query_args', array($this, 'filter_cw_class_query'), 10, 1);

      // Display nothing if there are no upcoming cw_class
      if (!$has_cw_class) {
        return;
      }

      // Default title is nothing
      $title = '';

      if (!empty($instance['title'])) {
        $title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);
      }

      echo $args['before_widget'];

      if (!empty($title)) {
        echo $args['before_title'] . $title . $args['after_title'];
      }

?>
      <ul>

        <?php while (get_inloop_current_classes()) : get_inloop_current_class(); ?>

          <li>
            <a href="<?php cw_class_the_link(); ?>" title="<?php echo esc_attr(cw_class_get_the_title()); ?>"><?php cw_class_the_title(); ?></a>
            <small class="time-to"><?php cw_class_time_to(); ?></small>
          </li>

        <?php endwhile; ?>

      </ul>
    <?php

      echo $args['after_widget'];
    }

    /**
     * Update widget preferences
     */
    public function update($new_instance, $old_instance)
    {
      $instance = array();

      if (!empty($new_instance['title'])) {
        $instance['title'] = strip_tags(wp_unslash($new_instance['title']));
      }

      $instance['number'] = (int) $new_instance['number'];

      return $instance;
    }

    /**
     * Display the form in Widgets Administration
     */
    public function form($instance = array())
    {
      // Default to nothing
      $title = '';

      if (isset($instance['title'])) {
        $title = $instance['title'];
      }

      // Number default to 5
      $number = 5;

      if (!empty($instance['number'])) {
        $number = absint($instance['number']);
      }
    ?>
      <p>
        <label for="<?php echo $this->get_field_id('title'); ?>"><?php esc_html_e('Title:', 'cw_class') ?></label>
        <input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr($title); ?>" />
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of upcoming cw_class to show:', 'cw_class'); ?></label>
        <input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" />
      </p>
<?php
    }
  }

endif;

add_action('bp_widgets_init', array('cw_class_Upcoming_Widget', 'register_widget'), 10);
