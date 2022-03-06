<?php

/**
 * CW Class Template.
 *
 * Template functions
 *
 * @package CW Class
 * @subpackage Template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/** Type Filrer ***************************************************************/

function cw_class_type_filter()
{
  if (!cw_class_has_types()) {
    return;
  }

  $selected_type = '';

  if (!empty($_REQUEST['type'])) {
    $selected_type = sanitize_title($_REQUEST['type']);
  }
?>

  <form id="cw_class-types-filter-form" action="">

    <select name="type">

      <option value="">---</option>

      <?php foreach (cw_class()->types as $type) : ?>

        <option value="<?php echo esc_attr($type->slug); ?>" <?php selected($selected_type, $type->slug); ?>><?php echo esc_attr($type->name); ?></option>

      <?php endforeach; ?>

    </select>

    <input type="submit" value="<?php esc_attr_e('Filter', 'cw_class'); ?>" />

  </form>
  <?php
}

/** Main Loop *****************************************************************/

/**
 * The main CW Class template loop class.
 *
 * @package CW Class
 * @subpackage Template
 *
 * @since CW Class (1.0.0)
 */
class cw_class_Template
{

  /**
   * The loop iterator.
   *
   * @access public
   * @var int
   */
  public $current_cw_class = -1;

  /**
   * The number of CW Class returned by the paged query.
   *
   * @access public
   * @var int
   */
  public $current_cw_class_count;

  /**
   * Total number of CW Class matching the query.
   *
   * @access public
   * @var int
   */
  public $total_cw_class_count;

  /**
   * Array of CW Class located by the query.
   *
   * @access public
   * @var array
   */
  public $cw_classes;

  /**
   * The Rendez Vou object currently being iterated on.
   *
   * @access public
   * @var object
   */
  public $cw_class;

  /**
   * A flag for whether the loop is currently being iterated.
   *
   * @access public
   * @var bool
   */
  public $in_the_loop;

  /**
   * Array of item ids to filter on.
   *
   * @access public
   * @var array
   */
  public $item_ids;

  /**
   * Component slug.
   *
   * @access public
   * @var string
   */
  public $component;

  /**
   * Include private CW Class ?
   *
   * @access public
   * @var bool
   */
  public $show_private;

  /**
   * The ID of the user to whom the displayed CW Class belong.
   *
   * @access public
   * @var int
   */
  public $user_id;

  /**
   * The page number being requested.
   *
   * @access public
   * @var int
   */
  public $pag_page;

  /**
   * The number of items to display per page of results.
   *
   * @access public
   * @var int
   */
  public $pag_num;

  /**
   * An HTML string containing pagination links.
   *
   * @access public
   * @var string
   */
  public $pag_links;

  /**
   * A string to match against.
   *
   * @access public
   * @var string
   */
  public $search_terms;

  /**
   * comma separated list of CW Class ids or array.
   *
   * @access public
   * @var array|string
   */
  public $exclude;

  /**
   * A database column to order the results by.
   *
   * @access public
   * @var string
   */
  public $order_by;

  /**
   * The direction to sort the results (ASC or DESC)
   *
   * @access public
   * @var string
   */
  public $sort_order;

  /**
   * The type to filter the results with
   *
   * @access public
   * @var string
   */
  public $type;

  /**
   * Constructor method.
   *
   * @since CW Class (1.0.0)
   */
  public function __construct($args = array())
  {

    $defaults = array(
      'attendees' => array(), // one or more attendee ids
      'organizer'  => false,   // the organize id of the cw_class
      'per_page'  => 20,
      'page'    => 1,
      'search'    => false,
      'exclude'  => false,   // comma separated list or array of cw_class ids.
      'orderby'   => 'modified',
      'order'     => 'DESC',
      'page_arg'  => 'rpage',
      'group_id'  => false,
      'type'      => '',
      'no_cache'  => false,
    );

    // Parse arguments
    $r = bp_parse_args($args, $defaults, 'cw_class_template_args');

    // Set which pagination page
    if (isset($_GET[$r['page_arg']])) {
      $pag_page = intval($_GET[$r['page_arg']]);
    } else {
      $pag_page = $r['page'];
    }

    // Setup variables
    $this->pag_page     = $pag_page;
    $this->pag_num      = $r['per_page'];
    $this->attendees    = $r['attendees'];
    $this->organizer    = (int) $r['organizer'];
    $this->search_terms = $r['search'];
    $this->exclude      = $r['exclude'];
    $this->page_arg     = $r['page_arg'];
    $this->order_by     = $r['orderby'];
    $this->sort_order   = $r['order'];
    $this->group_id     = $r['group_id'];
    $this->type         = $r['type'];
    $this->no_cache     = $r['no_cache'];

    // Get the CW Class
    $cw_classes      = cw_class_get_items(array(
      'attendees' => $this->attendees,
      'organizer'  => $this->organizer,
      'per_page'  => $this->pag_num,
      'page'    => $this->pag_page,
      'search'    => $this->search_terms,
      'exclude'  => $this->exclude,
      'orderby'   => $this->order_by,
      'order'     => $this->sort_order,
      'group_id'  => $this->group_id,
      'type'      => $this->type,
      'no_cache'  => $this->no_cache,
    ));

    // Setup the CW Class to loop through
    $this->cw_classes            = $cw_classes['cw_class_items'];
    $this->total_cw_class_count = $cw_classes['total'];

    if (empty($this->cw_classes)) {
      $this->cw_class_count       = 0;
      $this->total_cw_class_count = 0;
    } else {
      $this->cw_class_count = count($this->cw_classes);
    }

    if ((int) $this->total_cw_class_count && (int) $this->pag_num) {
      $add_args = array();

      if (!empty($this->type)) {
        $add_args['type'] = $this->type;
      }

      $this->pag_links = paginate_links(array(
        'base'      => esc_url(add_query_arg($this->page_arg, '%#%')),
        'format'    => '',
        'total'     => ceil((int) $this->total_cw_class_count / (int) $this->pag_num),
        'current'   => $this->pag_page,
        'prev_text' => _x('&larr;', 'cw_class pagination previous text', 'cw_class'),
        'next_text' => _x('&rarr;', 'cw_class pagination next text',     'cw_class'),
        'mid_size'  => 1,
        'add_args'  => $add_args,
      ));

      // Remove first page from pagination
      $this->pag_links = str_replace('?'      . $r['page_arg'] . '=1', '', $this->pag_links);
      $this->pag_links = str_replace('&#038;' . $r['page_arg'] . '=1', '', $this->pag_links);
    }
  }

  /**
   * Whether there are CW Class available in the loop.
   *
   * @since CW Class (1.0.0)
   *
   * @see cw_manager_has_classes()
   *
   * @return bool True if there are items in the loop, otherwise false.
   */
  public function has_cw_classes()
  {
    if ($this->cw_class_count) {
      return true;
    }

    return false;
  }

  /**
   * Set up the next CW Class and iterate index.
   *
   * @since CW Class (1.0.0)
   *
   * @return object The next CW Class to iterate over.
   */
  public function next_cw_class()
  {

    $this->current_cw_class++;

    $this->cw_class = $this->cw_classes[$this->current_cw_class];

    return $this->cw_class;
  }

  /**
   * Rewind the CW Class and reset CW Class index.
   *
   * @since CW Class (1.0.0)
   */
  public function rewind_cw_classes()
  {

    $this->current_cw_class = -1;

    if ($this->cw_class_count > 0) {
      $this->cw_class = $this->cw_classes[0];
    }
  }

  /**
   * Whether there are CW Classes left in the loop to iterate over.
   *
   * @since CW Class (1.0.0)
   *
   * @return bool True if there are more CW Classes to show,
   *         otherwise false.
   */
  public function cw_classes()
  {

    if ($this->current_cw_class + 1 < $this->cw_class_count) {
      return true;
    } elseif ($this->current_cw_class + 1 == $this->cw_class_count) {
      do_action('cw_classes_loop_end');

      $this->rewind_cw_classes();
    }

    $this->in_the_loop = false;
    return false;
  }

  /**
   * Set up the current CW Class inside the loop.
   *
   * @since CW Class (1.0.0)
   */
  public function the_cw_class()
  {
    $this->in_the_loop  = true;
    $this->cw_class = $this->next_cw_class();

    // loop has just started
    if (0 === $this->current_cw_class) {
      do_action('cw_classes_loop_start');
    }
  }
}

/** The Loop ******************************************************************/

/**
 * Initialize the CW Class loop.
 *
 * @package CW Class
 * @subpackage Template
 *
 * @since CW Class (1.0.0)
 */
function cw_manager_has_classes($args = array())
{
  // init vars
  $organizer = false;
  $attendees = array();
  $type = '';

  // Get the user ID
  if (bp_is_user()) {
    if (bp_is_current_action('schedule')) {
      $organizer = bp_displayed_user_id();
    } else if (bp_is_current_action('attend')) {
      $attendee_id = bp_is_my_profile() ? bp_loggedin_user_id() : bp_displayed_user_id();
      $attendees = array($attendee_id);
    }

    if (bp_is_current_component(cw_class()->get_component_slug()) && !empty($_REQUEST['type'])) {
      $type = sanitize_title($_REQUEST['type']);
    }
  }

  if (bp_is_group() && bp_is_current_action(cw_class()->get_component_slug()) && !empty($_REQUEST['type'])) {
    $type = sanitize_title($_REQUEST['type']);
  }

  // Parse the args
  $r = bp_parse_args($args, array(
    'attendees' => $attendees, // one or more attendee ids
    'organizer'  => $organizer,   // the organize id of the cw_class
    'per_page'  => 20,
    'page'    => 1,
    'search'    => isset($_REQUEST['s']) ? stripslashes($_REQUEST['s']) : '',
    'exclude'  => false,   // comma separated list or array of cw_class ids.
    'orderby'   => 'modified',
    'order'     => 'DESC',
    'page_arg'  => 'rpage',
    'type'      => $type,
  ), 'cw_classes_has_args');

  // Get the CW Class
  $query_loop = new cw_class_Template($r);

  // Setup the global query loop
  cw_class()->query_loop = $query_loop;

  return apply_filters('cw_manager_has_classes', $query_loop->has_cw_classes(), $query_loop);
}

/**
 * Get the CW Class returned by the template loop.
 *
 * @since CW Class (1.0.0)
 *
 * @return array List of CW Class.
 */
function get_inloop_current_classes()
{
  return cw_class()->query_loop->cw_classes();
}

/**
 * Get the current CW Class object in the loop.
 *
 * @since CW Class (1.0.0)
 *
 * @return object The current Rendez Vou within the loop.
 */
function get_inloop_current_class()
{
  return cw_class()->query_loop->the_cw_class();
}

/** Loop Output ***************************************************************/

/**
 * Output the pagination count for the current CW Class loop.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_pagination_count()
{
  echo cw_class_get_pagination_count();
}
/**
 * Return the pagination count for the current CW Class loop.
 *
 * @since CW Class (1.0.0)
 *
 * @return string HTML for the pagination count.
 */
function cw_class_get_pagination_count()
{
  $query_loop = cw_class()->query_loop;
  $start_num  = intval(($query_loop->pag_page - 1) * $query_loop->pag_num) + 1;
  $from_num   = bp_core_number_format($start_num);
  $to_num     = bp_core_number_format(($start_num + ($query_loop->pag_num - 1) > $query_loop->total_cw_class_count) ? $query_loop->total_cw_class_count : $start_num + ($query_loop->pag_num - 1));
  $total      = bp_core_number_format($query_loop->total_cw_class_count);
  $pag        = sprintf(_n('Viewing %1$s to %2$s (of %3$s cw_class)', 'Viewing %1$s to %2$s (of %3$s cw_class)', $total, 'cw_class'), $from_num, $to_num, $total);

  return apply_filters('cw_class_get_pagination_count', $pag);
}

/**
 * Output the pagination links for the current CW Class loop.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_pagination_links()
{
  echo cw_class_get_pagination_links();
}
/**
 * Return the pagination links for the current CW Class loop.
 *
 * @since CW Class (1.0.0)
 *
 * @return string HTML for the pagination links.
 */
function cw_class_get_pagination_links()
{
  return apply_filters('cw_class_get_pagination_links', cw_class()->query_loop->pag_links);
}

/**
 * Output the ID of the CW Class currently being iterated on.
 *
 * @since CW Class (1.0.0)
 */
function get_inloop_current_class_id()
{
  echo cw_class_get_the_cw_class_id();
}
/**
 * Return the ID of the CW Class currently being iterated on.
 *
 * @since CW Class (1.0.0)
 *
 * @return int ID of the current CW Class.
 */
function cw_class_get_the_cw_class_id()
{
  return apply_filters('cw_class_get_the_cw_class_id', cw_class()->query_loop->cw_class->ID);
}

/**
 * Output the class of the CW Class row.
 *
 * @since CW Class (1.0.0)
 */
function cw_manager_get_css_classnames()
{
  echo cw_class_get_class();
}

/**
 * Return the class of the CW Class row.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_get_class()
{
  $cw_class = cw_class()->query_loop->cw_class;
  $classes      = array();

  // Rendez Vou status - inherit, private.
  $classes[] = esc_attr($cw_class->post_status);

  $classes = apply_filters('cw_class_get_class', $classes);
  $classes = array_merge($classes, array());
  $retval = 'class="' . join(' ', $classes) . '"';

  return $retval;
}

/**
 * Output the "avatar" of the CW Class row.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_avatar()
{
  echo cw_class_get_avatar();
}

/**
 * Return the "avatar" of the CW Class row.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_get_avatar()
{
  $output = '<div class="cw_class-avatar icon-' . cw_class()->query_loop->cw_class->post_status . '"></div>';

  return apply_filters('cw_class_get_avatar', $output, cw_class()->query_loop->cw_class->ID);
}

/**
 * Output the title of the CW Class.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_the_title()
{
  echo cw_class_get_the_title();
}

/**
 * Return the title of the CW Class.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_get_the_title()
{
  return apply_filters('cw_class_get_the_title', cw_class()->query_loop->cw_class->post_title);
}

/**
 * Output the link of the CW Class.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_the_link()
{
  echo esc_url(cw_class_get_the_link());
}

/**
 * Return the link of the CW Class.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_get_the_link()
{
  $user_can = true;
  $link = cw_class_get_single_link(cw_class()->query_loop->cw_class->ID, cw_class()->query_loop->cw_class->post_author);

  switch (cw_class()->query_loop->cw_class->post_status) {
    case 'private':
      $user_can = current_user_can('read_private_cw_classes', cw_class_get_the_cw_class_id());
      break;

    case 'draft':
      $user_can = current_user_can('edit_cw_class', cw_class_get_the_cw_class_id());
      $link = cw_class_get_edit_link(cw_class()->query_loop->cw_class->ID, cw_class()->query_loop->cw_class->post_author);
      break;
  }

  if (empty($user_can)) {
    return '#noaccess';
  }

  return apply_filters('cw_class_get_the_link', $link);
}

/**
 * Output the date of the CW Class.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_last_modified()
{
  echo cw_class_get_last_modified();
}

/**
 * Return the date of the CW Class.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_get_last_modified()
{
  // Get the post status and switch reading post date
  $post_status = cw_class()->query_loop->cw_class->post_status;

  if ($post_status === "publish") {
    // Published meetings -> use post_modified_gmt
    $last_modified = bp_core_time_since(cw_class()->query_loop->cw_class->post_modified_gmt);
  } else {
    // Draft meetings -> use post_modified. post_modified_gmt is usually all 0s at this point
    $last_modified = bp_core_time_since(cw_class()->query_loop->cw_class->post_modified);
  }

  return apply_filters('cw_class_get_last_modified', sprintf(__('Modified %s', 'cw_class'), $last_modified));
}

/**
 * Output the time till cw_class happens.
 *
 * @since 1.4.0
 */
function cw_class_time_to()
{
  echo cw_class_get_time_to();
}

/**
 * Return the time till cw_class happens.
 *
 * @since 1.4.0
 */
function cw_class_get_time_to()
{
  add_filter('bp_core_time_since_ago_text', 'cw_class_set_time_to_text', 10, 1);

  $time_to = bp_core_time_since(bp_core_current_time(false), get_post_meta(cw_class()->query_loop->cw_class->ID, '_cw_class_defdate', true));

  remove_filter('bp_core_time_since_ago_text', 'cw_class_set_time_to_text', 10, 1);

  return apply_filters('cw_class_get_time_to', sprintf(__('starts in %s', 'cw_class'), $time_to));
}

/**
 * Remove the 'ago' part of the BuddyPress human time diff function
 *
 * @since 1.4.0
 */
function cw_class_set_time_to_text($time_since_text = '')
{
  return _x('%s', 'Used to output the time to wait till the cw_class', 'cw_class');
}

/**
 * Check whether the CW Class has a description.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_has_description()
{
  $user_can = !empty(cw_class()->query_loop->cw_class->post_excerpt);

  switch (cw_class()->query_loop->cw_class->post_status) {
    case 'private':
      $user_can = current_user_can('read_private_cw_classes', cw_class_get_the_cw_class_id());
      break;

    case 'draft':
      $user_can = current_user_can('edit_cw_classes', cw_class_get_the_cw_class_id());
      break;
  }

  return $user_can;
}

function cw_class_the_type()
{
  echo cw_class_get_the_type();
}
add_action('cw_class_after_item_description', 'cw_class_the_type');

function cw_class_get_the_type()
{
  if (!cw_class_has_types()) {
    return false;
  }

  $types = cw_class_get_type(cw_class_get_the_cw_class_id());

  if (empty($types)) {
    return false;
  }

  $type_names = wp_list_pluck($types, 'name');
  $type_name = array_pop($type_names);

  $type_slugs = wp_list_pluck($types, 'slug');
  $type_slug = array_pop($type_slugs);

  $output = sprintf(
    '<div class="item-desc"><a href="?type=%s" title="%s" class="cw_class-type">%s</a></div>',
    esc_attr($type_slug),
    esc_attr__('Filter cw_class having this type', 'cw_class'),
    esc_html($type_name)
  );

  return apply_filters('cw_class_get_the_type', $output, $type_name, $type_slug);
}

/**
 * Output the description of the CW Class.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_the_excerpt()
{
  echo cw_class_get_the_excerpt();
}

/**
 * Return the description of the CW Class.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_get_the_excerpt()
{
  $excerpt = bp_create_excerpt(cw_class()->query_loop->cw_class->post_excerpt);

  return apply_filters('cw_class_get_the_excerpt', $excerpt);
}

/**
 * Output the status (draft/private/public) of the CW Class.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_the_status()
{
  echo cw_class_get_the_status();
}

/**
 * Return the status of the CW Class.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_get_the_status()
{
  $status = __('All members', 'cw_class');
  $cw_class = cw_class()->query_loop->cw_class;

  if ('private' == $cw_class->post_status) {
    $status = __('Restricted', 'cw_class');
  } else if ('draft' == $cw_class->post_status) {
    $status = __('Draft', 'cw_class');
  }

  return apply_filters('cw_class_get_the_status', $status, $cw_class->ID, $cw_class->post_status);
}

/**
 * Output the user's action for the CW Class.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_the_user_actions()
{
  echo cw_class_get_the_user_actions();
}
add_action('cw_class_schedule_actions', 'cw_class_the_user_actions');
add_action('cw_class_attend_actions', 'cw_class_the_user_actions');

/**
 * Return the user's action for the CW Class.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_get_the_user_actions()
{
  $cw_class_id = cw_class()->query_loop->cw_class->ID;
  $user_id = cw_class()->query_loop->cw_class->post_author;

  $edit = $view = false;

  $status = cw_class()->query_loop->cw_class->post_status;

  if ('draft' != $status) {

    $user_can = 'private' == $status ? current_user_can('read_private_cw_classes', $cw_class_id) : current_user_can('read');

    if (!empty($user_can)) {
      $view_link = cw_class_get_single_link($cw_class_id, $user_id);
      $view = '<a href="' . esc_url($view_link) . '" class="button view-cw_class bp-primary-action" id="view-cw_class-' . $cw_class_id . ' ">' . _x('View', 'cw_class view link', 'cw_class') . '</a>';
    }
  }

  $current_action = apply_filters('cw_class_current_action', bp_current_action());

  if (current_user_can('edit_cw_class', $cw_class_id) && 'schedule' == $current_action) {
    $edit_link = cw_class_get_edit_link($cw_class_id, $user_id);
    $edit = '<a href="' . esc_url($edit_link) . '" class="button edit-cw_class bp-primary-action" id="edit-cw_class-' . $cw_class_id . ' ">' . _x('Edit', 'cw_class edit link', 'cw_class') . '</a>';
  }

  // Filter and return the HTML button
  return apply_filters('cw_class_get_the_user_actions', $view . $edit, $view, $edit);
}

/** Single Output ***************************************************************/

/**
 * Output the edit form action for the CW Class.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_single_the_form_action()
{
  $action = trailingslashit(bp_core_get_user_domain(cw_class()->item->organizer) . buddypress()->cw_class->slug . '/schedule');
  return apply_filters('cw_class_single_the_form_action', $action, cw_class()->item);
}

/**
 * Output the ID of the CW Class.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_single_the_id()
{
  echo cw_class_single_get_the_id();
}

/**
 * Return the ID of the CW Class.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_single_get_the_id()
{
  return apply_filters('cw_class_single_get_the_id', cw_class()->item->id);
}

/**
 * Output the title of the CW Class.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_single_the_title()
{
  echo cw_class_single_get_the_title();
}

/**
 * Return the title of the CW Class.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_single_get_the_title()
{
  return apply_filters('cw_class_single_get_the_title', cw_class()->item->title);
}

/**
 * Output the permalink of the CW Class.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_single_permalink()
{
  echo cw_class_single_get_permalink();
}

function cw_class_single_get_permalink()
{
  $id = cw_class_single_get_the_id();
  $organizer = cw_class()->item->organizer;
  $link = cw_class_get_single_link($id, $organizer);

  return apply_filters('cw_class_single_get_permalink', $link, $id, $organizer);
}

/**
 * Output the edit link of the CW Class.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_single_edit_link()
{
  echo cw_class_single_get_edit_link();
}

function cw_class_single_get_edit_link()
{
  $id = cw_class_single_get_the_id();
  $organizer = cw_class()->item->organizer;
  $link = cw_class_get_edit_link($id, $organizer);

  return apply_filters('cw_class_single_get_edit_link', $link, $id, $organizer);
}

/**
 * Output the description of the CW Class.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_single_the_description()
{
  echo cw_class_single_get_the_description();
}

/**
 * Return the description of the CW Class.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_single_get_the_description()
{
  $screen = !empty(cw_class()->screens->screen) ? cw_class()->screens->screen : 'single';

  return apply_filters("cw_class_{$screen}_get_the_description", cw_class()->item->description);
}

/**
 * Output the venue of the CW Class.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_single_the_venue()
{
  echo cw_class_single_get_the_venue();
}

/**
 * Return the venue of the CW Class.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_single_get_the_venue()
{
  return apply_filters('cw_class_single_get_the_venue', cw_class()->item->venue);
}

/**
 * Check if the current CW Class has a type.
 *
 * @since CW Class (1.2.0)
 */
function cw_class_single_has_type()
{
  return (bool) apply_filters('cw_class_single_has_type', cw_class_has_types(cw_class()->item), cw_class()->item);
}

/**
 * Output the type of the CW Class.
 *
 * @since CW Class (1.2.0)
 */
function cw_class_single_the_type()
{
  echo cw_class_single_get_the_type();
}

/**
 * Return the type of the CW Class.
 *
 * @since CW Class (1.2.0)
 */
function cw_class_single_get_the_type()
{
  $type = '';

  if (!empty(cw_class()->item->type)) {
    $types = wp_list_pluck(cw_class()->item->type, 'name');
    $type = array_pop($types);
  }

  return apply_filters('cw_class_single_get_the_type', $type, cw_class()->item->type);
}

/**
 * Output the selectbox to choose type for the CW Class.
 *
 * @since CW Class (1.2.0)
 */
function cw_class_single_edit_the_type()
{
  echo cw_class_single_edit_get_the_type();
}

/**
 * Return the selectbox to choose type for the CW Class.
 *
 * @since CW Class (1.2.0)
 */
function cw_class_single_edit_get_the_type()
{
  $manager = cw_class();

  if (empty($manager->types)) {
    $types = cw_class_get_terms(array('hide_empty' => false));
    $manager->types = $types;
  } else {
    $types = $manager->types;
  }

  $output = '<select name="_cw_class_edit[type]"><option value="">---</option>';

  $selected_type = 0;

  if (!empty(cw_class()->item->type)) {
    $selected_types = wp_list_pluck(cw_class()->item->type, 'term_id');
    $selected_type = array_pop($selected_types);
  }

  foreach ($types as $type) {
    $output .= '<option value="' . intval($type->term_id) . '" ' . selected($type->term_id, $selected_type, false) . '>' . esc_attr($type->name) . '</option>';
  }

  $output .= '</select>';

  return apply_filters('cw_class_single_edit_get_the_type', $output, $selected_type, $types, cw_class()->item);
}

/**
 * Output the duration of the CW Class.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_single_the_duration()
{
  echo cw_class_single_get_the_duration();
}

/**
 * Return the duration of the CW Class.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_single_get_the_duration()
{
  return apply_filters('cw_class_single_get_the_duration', cw_class()->item->duration);
}

/**
 * Output the privacy of the CW Class.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_single_the_privacy()
{
  echo cw_class_single_get_the_privacy();
}

function cw_class_single_is_published()
{
  return 'draft' != cw_class()->item->status;
}

function cw_class_single_get_privacy()
{
  $privacy = 'draft' == cw_class()->item->status ? cw_class()->item->privacy : cw_class()->item->status;

  $retval = 0;

  if (in_array($privacy, array(1, 'private')))
    $retval = 1;

  return apply_filters('cw_class_single_get_privacy', $retval);
}

function cw_class_single_get_the_privacy()
{
  $privacy = cw_class_single_get_privacy();
  return apply_filters('cw_class_single_get_the_privacy', checked(1, $privacy, false));
}

/**
 * Output the users prefs for the CW Class.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_single_the_dates($view = 'single')
{
  echo cw_class_single_get_the_dates($view);
}

function cw_class_single_get_the_dates($view = 'single')
{
  // First add organizer
  $all_attendees = (array) cw_class()->item->attendees;

  if (!in_array(cw_class()->item->organizer, $all_attendees))
    $all_attendees = array_merge(array(cw_class()->item->organizer), $all_attendees);

  // Then remove current_user as we want him to be in last position
  if ('edit' != $view) {
    if (!cw_class_single_date_set() && bp_loggedin_user_id())
      $attendees = array_diff($all_attendees, array(bp_loggedin_user_id()));
    else
      $attendees = $all_attendees;
  } else {
    $attendees = $all_attendees;
  }

  $days = cw_class()->item->days;

  if (empty($days)) {
    return false;
  }

  ksort($days);
  $header = array_keys($days);

  $output  = '<table id="cw_class-attendees-prefs">';
  $output .= '<thead>';
  $output .= '<tr><th>&nbsp;</th>';

  foreach ($header as $date) {
    $output .= '<th class="cw_class-date">';

    if (is_long($date)) {
      $output .= '<div class="date">' . date_i18n(get_option('date_format'), $date) . '</div>';
      $output .= '<div class="time">' . date_i18n(get_option('time_format'), $date) . '</div>';
    } else {
      $output .= '<div class="none">' . esc_html__('None', 'cw_class') . '</div>';
    }

    $output .= '</th>';
  }

  $output .= '</tr></thead>';
  $output .= '<tbody>';

  //rows
  foreach ($attendees as $attendee) {
    $user_link = trailingslashit(bp_core_get_user_domain($attendee));
    $user_name = bp_core_get_user_displayname($attendee);
    $tr_class = $attendee == bp_loggedin_user_id() ? 'edited' : false;

    $output .= '<tr class="' . $tr_class . '"><td>';

    if ('edit' == $view) {
      // Make sure the organizer is not removed from attendees
      if ($attendee == cw_class()->item->organizer) {
        $output .= '<input type="hidden" name="_cw_class_edit[attendees][]" value="' . $attendee . '"/>';
      } else {
        $output .= '<input type="checkbox" name="_cw_class_edit[attendees][]" value="' . $attendee . '" checked="true"/>&nbsp;';
      }
    }

    $output .= '<a href="' . esc_url($user_link) . '" title="' . esc_attr($user_name) . '">' . bp_core_fetch_avatar(
      array(
        'object'  => 'user',
        'item_id' => $attendee,
        'type'    => 'thumb',
        'class'   => 'mini',
        'width'   => 20,
        'height'  => 20
      )
    ) . ' ' . $user_name . '</a></td>';

    foreach ($header as $date) {
      $class = in_array($attendee, $days[$date]) ? 'active' : 'inactive';
      if ('none' == $date) {
        $class .= ' impossible';
      }
      $output .= '<td class="' . $class . '">&nbsp;</td>';
    }
    $output .= '</tr>';
  }

  $ending_rows = array(
    'total'        => '<td>' . esc_html__('Total', 'cw_class') . '</td>',
  );

  if ('edit' != $view) {
    $ending_rows['editable_row'] = '<td><a href="' . esc_url(bp_loggedin_user_domain()) . '" title="' . esc_attr(bp_get_loggedin_user_username()) . '">' . bp_core_fetch_avatar(
      array(
        'object'  => 'user',
        'item_id' => bp_loggedin_user_id(),
        'type'    => 'thumb',
        'class'   => 'mini',
        'width'   => 20,
        'height'  => 20
      )
    ) . ' ' . esc_html(bp_get_loggedin_user_fullname()) . '</a></td>';
    // Set definitive date
  } else {
    $ending_rows['editable_row'] = '<td id="cw_class-set">' . esc_html__('Set date', 'cw_class') . '</td>';
  }

  foreach ($header as $date) {
    $checked = checked(true, in_array(bp_loggedin_user_id(), $days[$date]), false);
    $ending_rows['total']        .= '<td><strong>' . count($days[$date]) . '</strong></td>';

    // Let the user set his prefs
    if ('edit' != $view) {
      $class = false;

      if ('none' == $date)
        $class = ' class="none-resets-cb"';

      $ending_rows['editable_row'] .= '<td><input type="checkbox" name="_cw_class_prefs[days][' . bp_loggedin_user_id() . '][]" value="' . $date . '" ' . $checked . $class . '/></td>';
      // Let the organizer choose the definitive date
    } else {
      $def_date = !empty(cw_class()->item->def_date) ? cw_class()->item->def_date : false;

      if ('none' != $date)
        $ending_rows['editable_row'] .= '<td><input type="radio" name="_cw_class_edit[def_date]" value="' . $date . '" ' . checked($date, $def_date, false) . '/></td>';
      else
        $ending_rows['editable_row'] .= '<td></td>';
    }
  }

  if ('edit' != $view) {
    // Date is set, changes cannot be done anymore
    if (!cw_class_single_date_set()) {
      if ('private' == cw_class()->item->privacy) {
        // If private, display the row only if current user is an attendee or the author
        if (bp_loggedin_user_id() == cw_class()->item->organizer || in_array(bp_loggedin_user_id(), $all_attendees)) {
          $output .= '<tr class="edited">' . $ending_rows['editable_row'] . '</tr>';
        }
      } else {
        if (current_user_can('subscribe_cw_class')) {
          $output .= '<tr class="edited">' . $ending_rows['editable_row'] . '</tr>';
        }
      }
      // Display totals
      $output .= '<tr>' . $ending_rows['total'] . '</tr>';
    }
  } else {
    // Display totals
    $output .= '<tr>' . $ending_rows['total'] . '</tr>';
    // Display the radio to set the date
    if ('draft' != cw_class()->item->status) {
      $output .= '<tr>' . $ending_rows['editable_row'] . '</tr>';
    }
  }

  $output .= '</tbody>';
  $output .= '</table>';

  if (!is_user_logged_in() && 'publish' == cw_class()->item->status && !cw_class_single_date_set()) {
    $output .= '<div id="message" class="info"><p>' . __('If you want to set your preferences about this cw_class, please log in.', 'cw_class') . '</p></div>';
  }

  return apply_filters('cw_class_single_get_the_dates', $output, $view);
}

/**
 * A report may be created for the CW Class.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_single_can_report()
{
  if (empty(cw_class()->item->def_date))
    return false;

  if (cw_class()->item->def_date > strtotime(current_time('mysql')))
    return false;

  return true;
}

/**
 * Output the report editor for the CW Class.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_single_edit_report()
{
  // add some filters, inspired by bbPress
  add_filter('tiny_mce_plugins',   'cw_class_tiny_mce_plugins');
  add_filter('teeny_mce_plugins',  'cw_class_tiny_mce_plugins');
  add_filter('teeny_mce_buttons',  'cw_class_teeny_mce_buttons');
  add_filter('quicktags_settings', 'cw_class_quicktags_settings');

  wp_editor(cw_class()->item->report, 'cw_class-edit-report', array(
    'textarea_name'     => '_cw_class_edit[report]',
    'media_buttons'     => false,
    'textarea_rows'     => 12,
    'tinymce'           => apply_filters('cw_class_single_edit_report_tinymce', false),
    'teeny'             => true,
    'quicktags'         => true,
    'dfw'               => false,
  ));

  // remove the filters, inspired by bbPress
  remove_filter('tiny_mce_plugins',   'cw_class_tiny_mce_plugins');
  remove_filter('teeny_mce_plugins',  'cw_class_tiny_mce_plugins');
  remove_filter('teeny_mce_buttons',  'cw_class_teeny_mce_buttons');
  remove_filter('quicktags_settings', 'cw_class_quicktags_settings');
}

/**
 * Report for the CW Class exists ?
 *
 * @since CW Class (1.0.0)
 */
function cw_class_single_has_report()
{
  return !empty(cw_class()->item->report);
}

/**
 * Output the report of the CW Class.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_single_the_report()
{
  echo cw_class_single_get_the_report();
}

function cw_class_single_get_the_report()
{
  return apply_filters('cw_class_single_get_the_report', cw_class()->item->report);
}

/**
 * Is the date of the CW Class set ?
 *
 * @since CW Class (1.0.0)
 */
function cw_class_single_date_set()
{
  return !empty(cw_class()->item->def_date);
}

/**
 * Output the date of the CW Class.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_single_the_date()
{
  echo cw_class_single_get_the_date();
}
function cw_class_single_get_the_date()
{
  $date_set = cw_class()->item->def_date;

  if (empty($date_set)) {
    return false;
  }

  if (!is_numeric($date_set)) {
    return esc_html($date_set);
  }

  $date = '<span class="date" data-timestamp="' . $date_set . '">' . date_i18n(get_option('date_format'), $date_set) . '</span>';
  $time = '<span class="time" data-timestamp="' . $date_set . '">' . date_i18n(get_option('time_format'), $date_set) . '</span>';

  $output = sprintf(__('%s at %s', 'cw_class'), $date, $time);

  return apply_filters('cw_class_single_get_the_date', $output, cw_class()->item);
}

/**
 * Output the actions for the CW Class.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_single_the_action($view = 'single')
{
  echo cw_class_single_get_the_action($view);
}

function cw_class_single_get_the_action($view = 'single')
{
  $action = 'choose';

  if ('edit' == $view) {
    $action = 'update';

    if ('draft' == cw_class()->item->status)
      $action = 'publish';
  }

  return apply_filters('cw_class_single_get_the_action', $action, $view);
}

/**
 * Output the submits for the CW Class.
 *
 * @since CW Class (1.0.0)
 */
function cw_class_single_the_submit($view = 'single')
{
  if (!bp_loggedin_user_id())
    return;

  if ('edit' == $view) {
    $caption = 'draft' == cw_class()->item->status ? __('Publish cw_class', 'cw_class') : __('Edit cw_class', 'cw_class');

    if (current_user_can('delete_cw_class', cw_class()->item->id)) :
      $delete_link = cw_class_get_delete_link(cw_class()->item->id, cw_class()->item->organizer);

      if (!empty($delete_link)) : ?>

        <a href="<?php echo esc_url($delete_link); ?>" class="button delete-cw_class bp-secondary-action" id="delete-cw_class-<?php echo cw_class()->item->id; ?>"><?php esc_html_e('Cancel cw_class', 'cw_class'); ?></a>

      <?php endif;

    endif;

    if (current_user_can('edit_cw_class', cw_class()->item->id)) : ?>
      <input type="submit" name="_cw_class_edit[submit]" id="cw_class-edit-submit" value="<?php echo esc_attr($caption); ?>" class="bp-primary-action" />
    <?php endif;
  } else if (current_user_can('subscribe_cw_class')) {

    if ('publish' != cw_class()->item->status && !in_array(bp_loggedin_user_id(), cw_class()->item->attendees) && bp_loggedin_user_id() != cw_class()->item->organizer) {
      return;
    }

    ?>
    <input type="submit" name="_cw_class_prefs[submit]" id="cw_class-prefs-submit" value="<?php echo esc_attr(__('Save preferences', 'cw_class')); ?>" class="bp-primary-action" />
    <?php

    if ('edit' != $view && current_user_can('edit_cw_class', cw_class()->item->id) && empty(cw_class()->item->def_date)) {
    ?>
      <a href="<?php echo esc_url(cw_class_get_edit_link(cw_class()->item->id, cw_class()->item->organizer)); ?>#cw_class-set" class="button bp-secondary-action last"><?php esc_html_e('Set the date', 'cw_class'); ?></a>
      <div class="clear"></div>
<?php
    }
  }
}
