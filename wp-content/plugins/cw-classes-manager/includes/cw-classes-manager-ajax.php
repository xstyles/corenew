<?php

/**
 * CW Class Ajax.
 *
 * Ajax functions
 *
 * @package CW Class
 * @subpackage Ajax
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Returns the available members
 *
 * @package CW Class
 * @subpackage Ajax
 *
 * @since CW Class (1.0.0)
 */
function cw_class_ajax_get_users()
{

  check_ajax_referer('cw_class-editor');

  $query_args = isset($_REQUEST['query']) ? (array) $_REQUEST['query'] : array();

  $args = bp_parse_args($query_args, array(
    'user_id'      => false,
    'type'         => 'alphabetical',
    'per_page'     => 20,
    'page'         => 1,
    'search_terms' => false,
    'member_type'  => false,
    'exclude'      => array(bp_loggedin_user_id()), // we don't want the organizer to be included in the attendees
  ), 'cw_class_get_users');

  if (!empty($args['member_type']) && 'false' === $args['member_type']) {
    $args['member_type'] = false;
  }

  if (!empty($args['group_id'])) {
    // Get all type of group users
    $args['group_role'] = array('admin', 'mod', 'member');

    $query = new BP_Group_Member_Query($args);
  } else {
    $query = new BP_User_Query($args);
  }

  $response = new stdClass();

  $response->meta = array('total_page' => 0, 'current_page' => 0);

  if (empty($query->results))
    wp_send_json_error($response);

  $users = array_map('cw_class_prepare_user_for_js', array_values($query->results));
  $users = array_filter($users);

  if (!empty($args['per_page'])) {
    $response->meta = array(
      'total_page' => ceil((int) $query->total_users / (int) $args['per_page']),
      'current_page' => (int) $args['page']
    );
  }

  $response->items = $users;

  wp_send_json_success($response);
}
add_action('wp_ajax_cw_class_get_users', 'cw_class_ajax_get_users');

/**
 * Create a rendez vous in draft mode
 *
 * @package CW Class
 * @subpackage Ajax
 *
 * @since CW Class (1.0.0)
 */
function cw_class_ajax_create()
{

  check_ajax_referer('cw_class-editor', 'nonce');

  if (!bp_current_user_can('publish_cw_classes')) {
    wp_send_json_error(__('You cannot create a cw_class.', 'cw_class'));
  }

  // Init the create arguments
  $args = array(
    'title'       => '',
    'venue'       => '',
    'type'        => 0,
    'description' => '',
    'duration'    => '',
    'days'        => array(),
    'attendees'   => array()
  );

  // First attendees
  // $attendees = array_map( 'absint', $_POST['attendees'] );

  // if ( empty( $attendees ) ) {
  // 	wp_send_json_error( __( 'No users were selected.', 'cw_class' ) );
  // }

  // Add to create arguments
  // $args['attendees'] = $attendees;

  // Then fields
  if (empty($_POST['desc']) || !is_array($_POST['desc'])) {
    wp_send_json_error(__('Please describe your cw_class using the What tab.', 'cw_class'));
  } else {
    $fields = $_POST['desc'];
  }

  $required_fields_missing = array();

  foreach ($fields as $field) {
    if ('required' == $field['class'] && empty($field['value'])) {
      $required_fields_missing[] = $field['label'];
    }

    // Add to create arguments
    $args[$field['id']] = $field['value'];
  }

  // Required fields are missing
  if (!empty($required_fields_missing)) {
    wp_send_json_error(__('Please make sure to fill all required fields.', 'cw_class'));
  }

  // Then dates
  if (empty($_POST['maydates']) || !is_array($_POST['maydates'])) {
    wp_send_json_error(__('Please define dates for your cw_class using the When tab.', 'cw_class'));
  } else {
    $dates = $_POST['maydates'];
  }


  $maydates = array();
  $maydates_errors = array();
  foreach ($dates as $date) {
    $timestamp = false;

    if (!empty($date['hour1'])) {

      if (!preg_match('/^[0-2]?[0-9]:[0-5][0-9]$/', $date['hour1'])) {
        $maydates_errors[] = $date['hour1'];
        continue;
      }

      $timestamp = strtotime($date['mysql'] . ' ' . $date['hour1']);
      $maydates[$timestamp] = array();
    }

    if (!empty($date['hour2'])) {

      if (!preg_match('/^[0-2]?[0-9]:[0-5][0-9]$/', $date['hour2'])) {
        $maydates_errors[] = $date['hour2'];
        continue;
      }

      $timestamp = strtotime($date['mysql'] . ' ' . $date['hour2']);
      $maydates[$timestamp] = array();
    }

    if (!empty($date['hour3'])) {

      if (!preg_match('/^[0-2]?[0-9]:[0-5][0-9]$/', $date['hour3'])) {
        $maydates_errors[] = $date['hour3'];
        continue;
      }

      $timestamp = strtotime($date['mysql'] . ' ' . $date['hour3']);
      $maydates[$timestamp] = array();
    }
  }

  // Check duration format
  if (!empty($args['duration']) && !preg_match('/^[0-2]?[0-9]:[0-5][0-9]$/', $args['duration'])) {
    $maydates_errors[] = $args['duration'];
  }

  if (!empty($maydates_errors)) {
    wp_send_json_error(__('Please make sure to respect the format HH:MM when defining time.', 'cw_class'));
  }

  if (!empty($maydates)) {
    $args['days'] = $maydates;
  }

  if (!empty($_POST['group_id'])) {
    $args['group_id'] = absint($_POST['group_id']);
  }

  $cw_class_id = cw_class_save($args);

  if (empty($cw_class_id)) {
    wp_send_json_error(__('The cw_class was not created due to an error.', 'cw_class'));
  } else {
    // url to edit cw_class screen
    wp_send_json_success(esc_url_raw(cw_class_get_edit_link($cw_class_id, bp_loggedin_user_id())));
  }
}
add_action('wp_ajax_create_cw_class', 'cw_class_ajax_create');

/**
 * Insert a new cw_class type
 *
 * @package CW Class
 * @subpackage Ajax
 *
 * @since CW Class (1.2.0)
 *
 * @uses  cw_class_taxonomy_exists
 * @uses  cw_class_insert_term()
 * @uses  cw_class_get_term()
 */
function cw_class_ajax_insert_term()
{
  if (!isset($_POST['cw_class_type_name'])) {
    wp_send_json_error();
  }

  if (!isset($_POST['cw_class_subscription_id'])) {
    wp_send_json_error();
  }

  check_ajax_referer('cw_class-admin', 'nonce');

  if (!current_user_can('manage_options')) {
    wp_send_json_error();
  }

  if (!cw_class_taxonomy_exists('cw_class_type')) {
    wp_send_json_error();
  }

  $term = esc_html($_POST['cw_class_type_name']);

  $inserted = cw_class_insert_term($term);

  if (empty($inserted['term_id']) || is_wp_error($inserted)) {
    wp_send_json_error();
  }

  $term = cw_class_prepare_term_for_js(cw_class_get_term($inserted['term_id']));

  if (empty($term)) {
    wp_send_json_error();
  }

  wp_send_json_success($term);
}
add_action('wp_ajax_cw_class_insert_term', 'cw_class_ajax_insert_term');

/**
 * Get all cw_class types
 *
 * @package CW Class
 * @subpackage Ajax
 *
 * @since CW Class (1.2.0)
 *
 * @uses  cw_class_taxonomy_exists
 * @uses  cw_class_get_terms()
 */
function cw_class_ajax_get_terms()
{
  check_ajax_referer('cw_class-admin', 'nonce');

  if (!current_user_can('manage_options')) {
    wp_send_json_error();
  }

  if (!cw_class_taxonomy_exists('cw_class_type')) {
    wp_send_json_error();
  }

  $terms = cw_class_get_terms(array('hide_empty' => false));
  $terms = array_map('cw_class_prepare_term_for_js', array_values($terms));
  $terms = array_filter($terms);

  wp_send_json_success($terms);
}
add_action('wp_ajax_cw_class_get_terms', 'cw_class_ajax_get_terms');

/**
 * Delete a cw_class types
 *
 * @package CW Class
 * @subpackage Ajax
 *
 * @since CW Class (1.2.0)
 *
 * @uses  cw_class_taxonomy_exists
 * @uses  cw_class_delete_term()
 */
function cw_class_ajax_delete_term()
{
  if (!isset($_POST['cw_class_type_id'])) {
    wp_send_json_error();
  }

  check_ajax_referer('cw_class-admin', 'nonce');

  if (!current_user_can('manage_options')) {
    wp_send_json_error();
  }

  if (!cw_class_taxonomy_exists('cw_class_type')) {
    wp_send_json_error();
  }

  $term_id = intval($_POST['cw_class_type_id']);

  $deleted = cw_class_delete_term($term_id);

  if (empty($deleted) || is_wp_error($deleted)) {
    wp_send_json_error();
  }

  wp_send_json_success();
}
add_action('wp_ajax_cw_class_delete_term', 'cw_class_ajax_delete_term');

/**
 * Update a cw_class types
 *
 * @package CW Class
 * @subpackage Ajax
 *
 * @since CW Class (1.2.0)
 *
 * @todo  use a wrapper function for taxonomy_exists, wp_update_term making sure current blog is BuddyPress root blog
 */
function cw_class_ajax_update_term()
{
  if (!isset($_POST['cw_class_type_id']) || empty($_POST['cw_class_type_name'])) {
    wp_send_json_error();
  }

  check_ajax_referer('cw_class-admin', 'nonce');

  if (!current_user_can('manage_options')) {
    wp_send_json_error();
  }

  if (!cw_class_taxonomy_exists('cw_class_type')) {
    wp_send_json_error();
  }

  $term_id   = intval($_POST['cw_class_type_id']);
  $term_name = esc_html($_POST['cw_class_type_name']);
  $subscription_id = esc_html($_POST['cw_class_subscription_id']);

  $updated = cw_class_update_term($term_id, ['name' => $term_name, 'subscription' => $subscription_id]);

  if (empty($updated) || is_wp_error($updated)) {
    wp_send_json_error();
  }

  wp_send_json_success();
}
add_action('wp_ajax_cw_class_update_term', 'cw_class_ajax_update_term');


/**
 * Get all subject subscription products to show to admin
 *
 * @package CW Class
 * @subpackage Ajax
 *
 * @since CW Class (1.2.0)
 *
 * @return void
 */
function cw_class_ajax_get_subs()
{
  if (!current_user_can('manage_options')) {
    wp_send_json_error();
  }

  $subs = cw_class_get_all_class_subs();

  if (!count($subs)) wp_send_json_error();

  wp_send_json_success($subs);
}
add_action('wp_ajax_cw_class_get_subs', 'cw_class_ajax_get_subs');
