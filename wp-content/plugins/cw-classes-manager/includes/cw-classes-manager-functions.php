<?php

/**
 * CW Class Functions.
 *
 * Plugin functions
 *
 * @package CW Class
 * @subpackage Functions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Get a cw_class
 *
 * @package CW Class
 * @subpackage Functions
 *
 * @since CW Class (1.0.0)
 */
function cw_class_get_item($id = 0)
{
  if (empty($id))
    return false;

  $cw_class = new CW_Class_Item($id);

  return apply_filters('cw_class_get_item', $cw_class);
}

/**
 * Get cw_classes
 *
 * @package CW Class
 * @subpackage Functions
 *
 * @since CW Class (1.0.0)
 */
function cw_class_get_items($args = array())
{
  $defaults = array(
    'attendees' => array(),      // one or more user ids who may attend to the class
    'organizer' => false,        // the author id of the class
    'per_page'  => 20,
    'page'      => 1,
    'search'    => false,
    'exclude'   => false,        // comma separated list or array of class ids.
    'orderby'   => 'modified',
    'order'     => 'DESC',
    'group_id'  => false,
    'type'      => '',
    'no_cache'  => false,
  );

  $r = bp_parse_args($args, $defaults, 'cw_class_get_items_args');

  if (!$r['no_cache']) {
    $cw_classes = wp_cache_get('cw_class_cw_classes', 'bp');
  }

  if (empty($cw_classes)) {
    $cw_classes = CW_Class_Item::get(array(
      'attendees' => (array) $r['attendees'],
      'organizer' => (int) $r['organizer'],
      'per_page'  => $r['per_page'],
      'page'      => $r['page'],
      'search'    => $r['search'],
      'exclude'   => $r['exclude'],
      'orderby'   => $r['orderby'],
      'order'     => $r['order'],
      'group_id'  => $r['group_id'],
      'type'      => $r['type'],
    ));

    if (!$r['no_cache']) {
      wp_cache_set('cw_class_cw_classes', $cw_classes, 'bp');
    }
  }

  return apply_filters_ref_array('cw_class_get_items', array(&$cw_classes, &$r));
}

/**
 * Launch the CW Class Editor
 *
 * @package CW Class
 * @subpackage Functions
 *
 * @since CW Class (1.0.0)
 */
function cw_class_editor($editor_id, $settings = array())
{
  CW_Class_Editor::editor($editor_id, $settings);
}

/**
 * Prepare the user for js
 *
 * @package CW Class
 * @subpackage Functions
 *
 * @since CW Class (1.0.0)
 */
function cw_class_prepare_user_for_js($users)
{

  $response = array(
    'id'     => intval($users->ID),
    'name'   => $users->display_name,
    'avatar' => htmlspecialchars_decode(bp_core_fetch_avatar(
      array(
        'item_id' => $users->ID,
        'object'  => 'user',
        'type'    => 'full',
        'width'   => 150,
        'height'  => 150,
        'html'    => false
      )
    )),
  );

  return apply_filters('cw_class_prepare_user_for_js', $response, $users);
}

/**
 * Prepare the term for js
 *
 * @package CW Class
 * @subpackage Functions
 *
 * @since CW Class (1.2.0)
 */
function cw_class_prepare_term_for_js($term)
{

  $response = array(
    'id'    => intval($term->term_id),
    'name'  => $term->name,
    'slug'  => $term->slug,
    'count' => intval($term->count),
  );

  return apply_filters('cw_class_prepare_term_for_js', $response, $term);
}

/**
 * Save a CW Class
 *
 * @package CW Class
 * @subpackage Functions
 *
 * @since CW Class (1.0.0)
 */
function cw_class_save($args = array())
{

  $r = bp_parse_args($args, array(
    'id'          => false,
    'organizer'   => bp_loggedin_user_id(),
    'title'       => '',
    'venue'       => '',
    'type'        => 0,
    'description' => '',
    'duration'    => '',
    'privacy'     => '',
    'status'      => 'draft',
    'days'        => array(),   // array( 'timestamp' => array( attendees id ) )
    'attendees'   => array(),  // Attendees id
    'def_date'    => 0,       // timestamp
    'report'      => '',
    'group_id'    => false,
  ), 'cw_class_save_args');

  if (empty($r['title']) || empty($r['organizer'])) {
    return false;
  }

  // Using cw_class
  $cw_class = new CW_Class_Item($r['id']);

  $cw_class->organizer   = (int) $r['organizer'];
  $cw_class->title       = $r['title'];
  $cw_class->venue       = $r['venue'];
  $cw_class->type        = (int) $r['type'];
  $cw_class->description = $r['description'];
  $cw_class->duration    = $r['duration'];
  $cw_class->privacy     = $r['privacy'];
  $cw_class->status      = $r['status'];
  $cw_class->attendees   = $r['attendees'];
  $cw_class->def_date    = $r['def_date'];
  $cw_class->report      = $r['report'];
  $cw_class->group_id    = $r['group_id'];

  // Allow attendees to not attend !
  if ('draft' == $r['status'] && !in_array('none', array_keys($r['days']))) {
    $r['days']['none'] = array();

    // Saving days the first time only
    $cw_class->days    = $r['days'];
  }

  do_action('cw_class_before_saved', $cw_class, $r);

  $id = $cw_class->save();

  do_action('cw_class_after_saved', $cw_class, $r);

  return $id;
}

/**
 * Delete a cw_class
 *
 * @package CW Class
 * @subpackage Functions
 *
 * @since CW Class (1.0.0)
 */
function cw_class_delete_item($id = 0)
{
  if (empty($id))
    return false;

  do_action('cw_class_before_delete', $id);

  $deleted = CW_Class_Item::delete($id);

  if (!empty($deleted)) {
    do_action('cw_class_after_delete', $id, $deleted);
    return true;
  } else {
    return false;
  }
}

/**
 * Set caps
 *
 * @package CW Class
 * @subpackage Functions
 *
 * @since CW Class (1.0.0)
 */
function cw_class_get_caps()
{
  return apply_filters('cw_class_get_caps', array(
    'edit_posts'          => 'edit_cw_classes',
    'edit_others_posts'   => 'edit_others_cw_classes',
    'publish_posts'       => 'publish_cw_classes',
    'read_private_posts'  => 'read_private_cw_classes',
    'delete_posts'        => 'delete_cw_classes',
    'delete_others_posts' => 'delete_others_cw_classes'
  ));
}

/**
 * Display link
 *
 * @package CW Class
 * @subpackage Functions
 *
 * @since CW Class (1.0.0)
 */
function cw_class_get_single_link($id = 0, $organizer_id = 0)
{
  if (empty($id) || empty($organizer_id))
    return false;

  $link = trailingslashit(bp_core_get_user_domain($organizer_id) . buddypress()->cw_class->slug . '/schedule');
  $link = add_query_arg(array('cls' => $id), $link);

  return apply_filters('cw_class_get_single_link', $link, $id, $organizer_id);
}

/**
 * Edit link
 *
 * @package CW Class
 * @subpackage Functions
 *
 * @since CW Class (1.0.0)
 */
function cw_class_get_edit_link($id = 0, $organizer_id = 0)
{
  if (empty($id) || empty($organizer_id))
    return false;

  $link = trailingslashit(bp_core_get_user_domain($organizer_id) . buddypress()->cw_class->slug . '/schedule');
  $link = add_query_arg(array('cls' => $id, 'action' => 'edit'), $link);

  return apply_filters('cw_class_get_edit_link', $link, $id, $organizer_id);
}

/**
 * Delete link
 *
 * @package CW Class
 * @subpackage Functions
 *
 * @since CW Class (1.0.0)
 */
function cw_class_get_delete_link($id = 0, $organizer_id = 0)
{
  if (empty($id) || empty($organizer_id)) {
    return false;
  }

  $link = trailingslashit(bp_core_get_user_domain($organizer_id) . buddypress()->cw_class->slug . '/schedule');
  $link = add_query_arg(array('cls' => $id, 'action' => 'delete'), $link);
  $link = wp_nonce_url($link, 'cw_class_delete');

  return apply_filters('cw_class_get_delete_link', $link, $id, $organizer_id);
}

/**
 * iCal Link
 *
 * @package CW Class
 * @subpackage Functions
 *
 * @since CW Class (1.1.0)
 *
 * @param  int $id           the id of the cw_class
 * @param  int $organizer_id the author id of the cw_class
 * @return string            the iCal link
 */
function cw_class_get_ical_link($id = 0, $organizer_id = 0)
{
  if (empty($id) || empty($organizer_id)) {
    return false;
  }

  $link = trailingslashit(bp_core_get_user_domain($organizer_id) . buddypress()->cw_class->slug . '/schedule/ical/' . $id);

  return apply_filters('cw_class_get_ical_link', $link, $id, $organizer_id);
}

/**
 * Handle cw_class actions (group/member contexts)
 *
 * @package CW Class
 * @subpackage Functions
 *
 * @since CW Class (1.1.0)
 *
 * @return string the cw_class screen id
 */
function cw_class_handle_actions()
{
  $action = isset($_GET['action']) ? $_GET['action'] : false;
  $screen = '';

  // Edit template
  if (!empty($_GET['action']) && 'edit' == $_GET['action'] && !empty($_GET['cls'])) {

    $redirect = remove_query_arg(array('cls', 'action', 'n'), wp_get_referer());

    $cw_class_id = absint($_GET['cls']);

    $cw_class = cw_class_get_item($cw_class_id);

    if (empty($cw_class) || !current_user_can('edit_cw_class', $cw_class_id)) {
      bp_core_add_message(__('cw_class could not be found', 'cw_class'), 'error');
      bp_core_redirect($redirect);
    }

    if ('draft' == $cw_class->status) {
      bp_core_add_message(__('Your cw_class is in draft mode, check informations and publish!', 'cw_class'));
    }

    cw_class()->item = $cw_class;

    $screen = 'edit';

    do_action('cw_class_edit_screen');
  }

  // Display single
  if (!empty($_GET['cls']) && (empty($action) || !in_array($action, array('edit', 'delete')))) {

    $redirect = remove_query_arg(array('cls', 'n', 'action'), wp_get_referer());

    $cw_class_id = absint($_GET['cls']);

    $cw_class = cw_class_get_item($cw_class_id);

    if (is_null($cw_class->organizer)) {
      bp_core_add_message(__('The cw_class was not found.', 'cw_class'), 'error');
      bp_core_redirect($redirect);
    }

    // Public cw_class can be seen by anybody
    $has_access = true;

    if ('private' == $cw_class->status) {
      $has_access = current_user_can('read_private_cw_classes', $cw_class_id);
    }

    if (empty($cw_class) || empty($has_access) || 'draft' == $cw_class->status) {
      bp_core_add_message(__('You do not have access to this cw_class', 'cw_class'), 'error');
      bp_core_redirect($redirect);
    }

    cw_class()->item = $cw_class;

    $screen = 'single';

    do_action('cw_class_single_screen');
  }

  // Publish & Updates.
  if (!empty($_POST['_cw_class_edit']) && !empty($_POST['_cw_class_edit']['id'])) {

    check_admin_referer('cw_class_update');

    $redirect = remove_query_arg(array('cls', 'n', 'action'), wp_get_referer());

    if (!current_user_can('edit_cw_class', absint($_POST['_cw_class_edit']['id']))) {
      bp_core_add_message(__('Editing this cw_class is not allowed.', 'cw_class'), 'error');
      bp_core_redirect($redirect);
    }

    $args = array();
    $action = sanitize_key($_POST['_cw_class_edit']['action']);

    $args = array_diff_key($_POST['_cw_class_edit'], array(
      'action'           => 0,
      'submit'           => 0
    ));

    $args['status'] = 'publish';

    // Make sure the organizer doesn't change if cw_class is edited by someone else
    if (!bp_is_my_profile()) {
      $args['organizer'] = apply_filters('cw_class_edit_action_organizer_id', bp_displayed_user_id(), $args);
    }


    $notify   = !empty($_POST['_cw_class_edit']['notify']) ? 1 : 0;
    $activity = !empty($_POST['_cw_class_edit']['activity']) && empty($args['privacy']) ? 1 : 0;

    do_action("cw_class_before_{$action}", $args, $notify, $activity);

    $id = cw_class_save($args);

    if (empty($id)) {
      bp_core_add_message(__('Editing this cw_class failed.', 'cw_class'), 'error');
    } else {
      bp_core_add_message(__('cw_class successfully edited.', 'cw_class'));
      $redirect = add_query_arg('cls', $id, $redirect);

      // cw_class is edited or published, let's handle notifications & activity
      do_action("cw_class_after_{$action}", $id, $args, $notify, $activity);
    }

    // finally redirect !
    bp_core_redirect($redirect);
  }

  // Set user preferences.
  if (!empty($_POST['_cw_class_prefs']) && !empty($_POST['_cw_class_prefs']['id'])) {

    check_admin_referer('cw_class_prefs');

    $redirect = remove_query_arg(array('n', 'action'), wp_get_referer());

    $cw_class_id = absint($_POST['_cw_class_prefs']['id']);
    $cw_class = cw_class_get_item($cw_class_id);

    $attendee_id = bp_loggedin_user_id();

    $has_access = $attendee_id;

    if (!empty($has_access) && 'private' == $cw_class->status)
      $has_access = current_user_can('read_private_cw_classes', $cw_class_id);

    if (empty($has_access)) {
      bp_core_add_message(__('You do not have access to this cw_class', 'cw_class'), 'error');
      bp_core_redirect($redirect);
    }

    $args = $_POST['_cw_class_prefs'];

    // Get days
    if (!empty($args['days'][$attendee_id]))
      $args['days'] = $args['days'][$attendee_id];
    else
      $args['days'] = array();

    do_action("cw_class_before_attendee_prefs", $args);

    if (!CW_Class_Item::attendees_pref($cw_class_id, $attendee_id, $args['days'])) {
      bp_core_add_message(__('Saving your preferences failed.', 'cw_class'), 'error');
    } else {
      bp_core_add_message(__('Preferences successfully saved.', 'cw_class'));

      // let's handle notifications to the organizer
      do_action("cw_class_after_attendee_prefs", $args, $attendee_id, $cw_class);
    }

    // finally redirect !
    bp_core_redirect($redirect);
  }

  // Delete
  if (!empty($_GET['action']) && 'delete' == $_GET['action'] && !empty($_GET['cls'])) {

    check_admin_referer('cw_class_delete');

    $redirect = remove_query_arg(array('cls', 'action', 'n'), wp_get_referer());

    $cw_class_id = absint($_GET['cls']);

    if (empty($cw_class_id) || !current_user_can('delete_cw_class', $cw_class_id)) {
      bp_core_add_message(__('cw_class could not be found', 'cw_class'), 'error');
      bp_core_redirect($redirect);
    }

    $deleted = cw_class_delete_item($cw_class_id);

    if (!empty($deleted)) {
      bp_core_add_message(__('cw_class successfully cancelled.', 'cw_class'));
    } else {
      bp_core_add_message(__('cw_class could not be cancelled', 'cw_class'), 'error');
    }

    // finally redirect !
    bp_core_redirect($redirect);
  }

  return $screen;
}

/**
 * Generates an iCal file using the cw_class datas
 *
 * @package CW Class
 * @subpackage Functions
 *
 * @since CW Class (1.1.0)
 *
 * @return string calendar file
 */
function cw_class_download_ical()
{
  $ical_page = array(
    'is'  => (bool) bp_is_current_action('schedule') && 'ical' == bp_action_variable(0),
    'cls' => (int)  bp_action_variable(1),
  );

  apply_filters('cw_class_download_ical', (array) $ical_page);

  if (empty($ical_page['is'])) {
    return;
  }

  $redirect    = wp_get_referer();
  $user_attend = trailingslashit(bp_loggedin_user_domain() . buddypress()->cw_class->slug . '/attend');

  if (empty($ical_page['cls'])) {
    bp_core_add_message(__('The cw_class was not found.', 'cw_class'), 'error');
    bp_core_redirect($redirect);
  }

  $cw_class = cw_class_get_item($ical_page['cls']);

  // Redirect the user to the login form
  if (!is_user_logged_in()) {
    bp_core_no_access(array(
      'redirect' => $_SERVER['REQUEST_URI'],
    ));

    return;
  }

  // Redirect if no class found
  if (empty($cw_class->organizer) || empty($cw_class->attendees)) {
    bp_core_add_message(__('The class was not found.', 'cw_class'), 'error');
    bp_core_redirect($user_attend);
  }

  // Redirect if not an attendee
  if ($cw_class->organizer != bp_loggedin_user_id() && !in_array(bp_loggedin_user_id(), $cw_class->attendees)) {
    bp_core_add_message(__('You are not attending this class.', 'cw_class'), 'error');
    bp_core_redirect($user_attend);
  }

  // Redirect if def date is not set
  if (empty($cw_class->def_date)) {
    bp_core_add_message(__('The class is not set yet.', 'cw_class'), 'error');
    bp_core_redirect($redirect);
  }

  $hourminutes = explode(':', $cw_class->duration);

  // Redirect if can't use the duration
  if (!is_array($hourminutes) && count($hourminutes) < 2) {
    bp_core_add_message(__('the duration is not set the right way.', 'cw_class'), 'error');
    bp_core_redirect($redirect);
  }

  $minutes = intval($hourminutes[1]) + (intval($hourminutes[0]) * 60);
  $end_date = strtotime('+' . $minutes . ' minutes', $cw_class->def_date);

  // Dates are stored as UTC althought values are local, we need to reconvert
  $date_start = date_i18n('Y-m-d H:i:s', $cw_class->def_date, true);
  $date_end   = date_i18n('Y-m-d H:i:s', $end_date, true);

  $tz_string = get_option('timezone_string');

  if (!empty($tz_string)) {
    date_default_timezone_set($tz_string);
  }

  status_header(200);
  header('Cache-Control: cache, must-revalidate');
  header('Pragma: public');
  header('Content-Description: File Transfer');
  header('Content-Disposition: attachment; filename=cw_class_' . $cw_class->id . '.ics');
  header('Content-Type: text/calendar');
?>
  BEGIN:VCALENDAR<?php echo "\n"; ?>
  VERSION:2.0<?php echo "\n"; ?>
  PRODID:-//hacksw/handcal//NONSGML v1.0//EN<?php echo "\n"; ?>
  CALSCALE:GREGORIAN<?php echo "\n"; ?>
  BEGIN:VEVENT<?php echo "\n"; ?>
  DTEND:<?php echo gmdate('Ymd\THis\Z', strtotime($date_end)); ?><?php echo "\n"; ?>
  UID:<?php echo uniqid(); ?><?php echo "\n"; ?>
  DTSTAMP:<?php echo gmdate('Ymd\THis\Z', time()); ?><?php echo "\n"; ?>
  LOCATION:<?php echo esc_html(preg_replace('/([\,;])/', '\\\$1', $cw_class->venue)); ?><?php echo "\n"; ?>
  DESCRIPTION:<?php echo esc_html(preg_replace('/([\,;])/', '\\\$1', $cw_class->description)); ?><?php echo "\n"; ?>
  URL;VALUE=URI:<?php echo esc_url(cw_class_get_single_link($cw_class->id, $cw_class->organizer)); ?><?php echo "\n"; ?>
  SUMMARY:<?php echo esc_html(preg_replace('/([\,;])/', '\\\$1', $cw_class->title)); ?><?php echo "\n"; ?>
  DTSTART:<?php echo gmdate('Ymd\THis\Z', strtotime($date_start)); ?><?php echo "\n"; ?>
  END:VEVENT<?php echo "\n"; ?>
  END:VCALENDAR<?php echo "\n"; ?>
<?php
  exit();
}
add_action('bp_actions', 'cw_class_download_ical');

/**
 * Check whether types have been created.
 *
 * @package CW Class
 * @subpackage Functions
 *
 * @since CW Class (1.2.0)
 *
 * @param int|CW_Class_Item $cw_class_id ID or object for the cw_class
 * @uses cw_class_get_terms()
 * @return bool Whether the taxonomy exists.
 */
function cw_class_has_types($cw_class = null)
{
  $manager = cw_class();

  if (empty($manager->types)) {
    $types = cw_class_get_terms(array('hide_empty' => false));
    $manager->types = $types;
  } else {
    $types = $manager->types;
  }

  if (empty($types)) {
    return false;
  }

  $retval = true;

  if (!empty($cw_class)) {
    if (!is_a($cw_class, 'CW_Class_Item')) {
      $cw_class = cw_class_get_item($cw_class);
    }

    $retval = !empty($cw_class->type);
  }

  return $retval;
}

/**
 * Set type for a cw_class.
 *
 * @package CW Class
 * @subpackage Functions
 *
 * @since CW Class (1.2.0)
 *
 * @param int    $cw_class_id ID of the cw_class.
 * @param string $type           cw_class type.
 * @return See {@see bp_set_object_terms()}.
 */
function cw_class_set_type($cw_class_id, $type)
{
  if (!empty($type) && !cw_class_term_exists($type)) {
    return false;
  }

  $retval = bp_set_object_terms($cw_class_id, $type, 'cw_class_type');

  // Clear cache.
  if (!is_wp_error($retval)) {
    wp_cache_delete($cw_class_id, 'cw_class_type');

    do_action('cw_class_set_type', $cw_class_id, $type);
  }

  return $retval;
}

/**
 * Get type for a cw_class.
 *
 * @package CW Class
 * @subpackage Functions
 *
 * @since CW Class (1.2.0)
 *
 * @param  int $cw_class_id     ID of the cw_class.
 * @return array|WP_Error The requested term data or empty array if no terms found. WP_Error if any of the $taxonomies don't exist.
 */
function cw_class_get_type($cw_class_id)
{
  $types = wp_cache_get($cw_class_id, 'cw_class_type');

  if (false === $types) {
    $types = bp_get_object_terms($cw_class_id, 'cw_class_type');

    if (!is_wp_error($types)) {
      wp_cache_set($cw_class_id, $types, 'cw_class_type');
    }
  }

  return apply_filters('cw_class_get_type', $types, $cw_class_id);
}

/** WP Taxonomy wrapper functions **/

/**
 * Check taxonomy exists on BuddyPress root blog.
 *
 * @package CW Class
 * @subpackage Functions
 *
 * @since CW Class (1.2.0)
 *
 * @param string $taxonomy Name of taxonomy object
 * @uses taxonomy_exists()
 * @return bool Whether the taxonomy exists.
 */
function cw_class_taxonomy_exists($taxonomy)
{
  if (!bp_is_root_blog()) {
    switch_to_blog(bp_get_root_blog_id());
  }

  $retval = taxonomy_exists($taxonomy);

  restore_current_blog();

  return $retval;
}

/**
 * Check a type exists on BuddyPress root blog.
 *
 * @package CW Class
 * @subpackage Functions
 *
 * @since CW Class (1.2.0)
 *
 * @param int|string $term The term to check
 * @uses term_exists()
 * @return bool Whether the taxonomy exists.
 */
function cw_class_term_exists($term, $taxonomy = 'cw_class_type')
{
  if (!bp_is_root_blog()) {
    switch_to_blog(bp_get_root_blog_id());
  }

  $retval = term_exists($term, $taxonomy);

  restore_current_blog();

  return $retval;
}

/**
 * Get terms for the cw_class type taxonomy.
 *
 * @package CW Class
 * @subpackage Functions
 *
 * @since CW Class (1.2.0)
 *
 * @param array|string $args
 * @param string|array $taxonomies Taxonomy name or list of Taxonomy names.
 * @uses get_terms()
 * @return array|WP_Error List of Term Objects and their children. Will return WP_Error, if any of $taxonomies
 *                        do not exist.
 */
function cw_class_get_terms($args = '', $taxonomies = 'cw_class_type')
{
  if (!bp_is_root_blog()) {
    switch_to_blog(bp_get_root_blog_id());
  }

  $retval = get_terms($taxonomies, $args);

  restore_current_blog();

  return $retval;
}

/**
 * Get a term for the cw_class type taxonomy.
 *
 * @package CW Class
 * @subpackage Functions
 *
 * @since CW Class (1.2.0)
 *
 * @param int|object $term If integer, will get from database. If object will apply filters and return $term.
 * @param string $output Constant OBJECT, ARRAY_A, or ARRAY_N
 * @param string $filter Optional, default is raw or no WordPress defined filter will applied.
 * @param string $taxonomy Taxonomy name that $term is part of.
 * @return mixed|null|WP_Error Term Row from database. Will return null if $term is empty. If taxonomy does not
 * exist then WP_Error will be returned.
 */
function cw_class_get_term($term, $output = OBJECT, $filter = 'raw', $taxonomy = 'cw_class_type')
{
  if (!bp_is_root_blog()) {
    switch_to_blog(bp_get_root_blog_id());
  }

  $retval = get_term($term, $taxonomy, $output, $filter);
  $subjects = cw_class_get_term_meta($retval->term_id);

  restore_current_blog();

  return $retval;
}

/**
 * Get all subscriptions for a class type by taxonomy.
 *
 * @param int|object $term If integer, will get from database. If object will apply filters and return $term.
 * @param string $output Constant OBJECT, ARRAY_A, or ARRAY_N
 * @param string $filter Optional, default is raw or no WordPress defined filter will applied.
 * @param string $taxonomy Taxonomy name that $term is part of.
 * @return mixed|null|WP_Error Subscriptions for the database. Will return null if $term is empty.
 * If taxonomy does not exist then WP_error will be returned.
 */
function cw_class_courses_for_term($term, $output = OBJECT, $filter = 'raw', $taxonomy = 'cw_class_type')
{
  if (!bp_is_root_blog()) {
    switch_to_blog(bp_get_root_blog_id());
  }

  $fetched_term = get_term($term, $taxonomy, $output, $filter);
  $courses = get_term_meta($fetched_term->term_id, 'cw_class_courses');

  restore_current_blog();

  return $courses;
}

/**
 * Insert a term for the cw_class type taxonomy.
 *
 * @package CW Class
 * @subpackage Functions
 *
 * @since CW Class (1.2.0)
 *
 * @param string       $term     The term to add
 * @param array|string $args
 * @param string       $taxonomy The taxonomy to which to add the term.
 * @uses wp_insert_term()
 * @return array|WP_Error An array containing the `term_id` and `term_taxonomy_id`,
 *                        {@see WP_Error} otherwise.
 */
function cw_class_insert_term($term, $args = array(), $taxonomy = 'cw_class_type')
{
  if (!bp_is_root_blog()) {
    switch_to_blog(bp_get_root_blog_id());
  }

  $retval = wp_insert_term($term, $taxonomy, $args);

  restore_current_blog();

  return $retval;
}

/**
 * Update a term for the cw_class type taxonomy.
 *
 * @package CW Class
 * @subpackage Functions
 *
 * @since CW Class (1.2.0)
 *
 * @param int $term_id The ID of the term
 * @param array|string $args Overwrite term field values
 * @param string       $taxonomy The taxonomy to which to update the term.
 * @uses wp_update_term()
 * @return array|WP_Error Returns Term ID and Taxonomy Term ID
 */
function cw_class_update_term($term_id, $args = array(), $taxonomy = 'cw_class_type')
{
  if (!bp_is_root_blog()) {
    switch_to_blog(bp_get_root_blog_id());
  }

  $retval = wp_update_term($term_id, $taxonomy, $args);

  restore_current_blog();

  return $retval;
}

/**
 * Delete a term for the cw_class type taxonomy.
 *
 * @package CW Class
 * @subpackage Functions
 *
 * @since CW Class (1.2.0)
 *
 * @param int $term_id The ID of the term
 * @param array|string $args Optional. Change 'default' term id and override found term ids.
 * @param string       $taxonomy The taxonomy to which to update the term.
 * @uses wp_update_term()
 * @return bool|WP_Error Returns false if not term; true if completes delete action.
 */
function cw_class_delete_term($term_id, $args = array(), $taxonomy = 'cw_class_type')
{
  if (!bp_is_root_blog()) {
    switch_to_blog(bp_get_root_blog_id());
  }

  $retval = wp_delete_term($term_id, $taxonomy, $args);

  restore_current_blog();

  return $retval;
}

function cw_class_get_term_meta($term_id, $key = 'cw_class_courses', $single = false)
{
  if (!bp_is_root_blog()) {
    switch_to_blog(bp_get_root_blog_id());
  }

  $retval = get_term_meta($term_id, $key, $single);

  restore_current_blog();

  return $retval;
}

function cw_class_add_term_meta($term_id, $key = 'cw_class_courses', $value)
{
  if (!bp_is_root_blog()) {
    switch_to_blog(bp_get_root_blog_id());
  }

  $retval = add_term_meta($term_id, $key, is_array($value) ? serialize($value) : $value);

  restore_current_blog();

  return $retval;
}

function cw_class_update_term_meta($term_id, $key = 'cw_class_courses', $value)
{
  if (!bp_is_root_blog()) {
    switch_to_blog(bp_get_root_blog_id());
  }

  $retval = update_term_meta($term_id, $key, is_array($value) ? serialize($value) : $value);

  restore_current_blog();

  return $retval;
}


function cw_class_get_all_class_courses()
{
  $products = wc_get_products([
    'type' => 'subscription',
    'category' => 'subjects',
    'post_status' => 'publish',
  ]);

  return $products;
}
