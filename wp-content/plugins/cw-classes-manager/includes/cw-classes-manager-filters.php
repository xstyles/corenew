<?php

/**
 * CW Class Filters.
 *
 * Filters
 *
 * @package CW Class
 * @subpackage Filters
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/** Filters *******************************************************************/

// Apply WordPress defined filters
add_filter('cw_class_single_get_the_description', 'cw_class_filter_kses', 1);
add_filter('cw_class_edit_get_the_description',   'cw_class_filter_kses', 1);
add_filter('cw_class_single_get_the_venue',       'cw_class_filter_kses', 1);
add_filter('cw_class_description_before_save',    'cw_class_filter_kses', 1);
add_filter('cw_class_single_get_the_report',      'wp_filter_kses',          1);
add_filter('cw_class_report_before_save',         'wp_filter_kses',          1);
add_filter('cw_class_title_before_save',          'strip_tags',              1);
add_filter('cw_class_venue_before_save',          'strip_tags',              1);
add_filter('cw_class_duration_before_save',       'strip_tags',              1);
add_filter('cw_class_single_get_the_duration',    'strip_tags',              1);
add_filter('cw_class_single_get_the_title',       'strip_tags',              1);
add_filter('cw_class_get_the_title',              'strip_tags',              1);
add_filter('cw_class_get_the_excerpt',            'strip_tags',              1);

add_filter('cw_class_get_the_excerpt',            'force_balance_tags');
add_filter('cw_class_single_get_the_description', 'force_balance_tags');
add_filter('cw_class_single_get_the_report',      'force_balance_tags');

add_filter('cw_class_get_the_excerpt',            'wptexturize');
add_filter('cw_class_single_get_the_description', 'wptexturize');
add_filter('cw_class_get_the_title',              'wptexturize');
add_filter('cw_class_single_get_the_title',       'wptexturize');
add_filter('cw_class_single_get_the_report',      'wptexturize');

add_filter('cw_class_get_the_excerpt',            'convert_smilies');
add_filter('cw_class_single_get_the_description', 'convert_smilies');
add_filter('cw_class_single_get_the_report',      'convert_smilies');

add_filter('cw_class_get_the_excerpt',            'convert_chars');
add_filter('cw_class_single_get_the_description', 'convert_chars');
add_filter('cw_class_single_get_the_report',      'convert_chars');

add_filter('cw_class_get_the_excerpt',            'wpautop');
add_filter('cw_class_single_get_the_description', 'wpautop');
add_filter('cw_class_single_get_the_report',      'wpautop');

add_filter('cw_class_single_get_the_description', 'make_clickable', 9);
add_filter('cw_class_single_get_the_report',      'make_clickable', 9);

add_filter('cw_class_get_the_excerpt',            'stripslashes_deep', 5);
add_filter('cw_class_single_get_the_description', 'stripslashes_deep', 5);
add_filter('cw_class_single_get_the_report',      'stripslashes_deep', 5);
add_filter('cw_class_single_get_the_venue',       'stripslashes_deep', 5);
add_filter('cw_class_single_get_the_title',       'stripslashes_deep', 5);
add_filter('cw_class_get_the_title',              'stripslashes_deep', 5);
add_filter('cw_class_edit_get_the_description',   'stripslashes_deep', 5);

add_filter('cw_class_single_get_the_report',      'cw_class_make_nofollow_filter');
add_filter('cw_class_single_get_the_report',      'cw_class_make_nofollow_filter');
add_filter('cw_class_single_get_the_description', 'cw_class_make_nofollow_filter');

add_filter('cw_class_single_get_the_date', 'cw_class_append_ical_link', 10, 2);

/**
 * Custom kses filtering for cw_class excerpt content.
 *
 * inspired by bp_activity_filter_kses
 *
 * @package CW Class
 * @subpackage Filters
 *
 * @since CW Class (1.0.0)
 */
function cw_class_filter_kses($content)
{
  global $allowedtags;

  $activity_allowedtags = $allowedtags;
  $activity_allowedtags['span']          = array();
  $activity_allowedtags['span']['class'] = array();
  $activity_allowedtags['a']['class']    = array();
  $activity_allowedtags['a']['id']       = array();
  $activity_allowedtags['a']['rel']      = array();
  $activity_allowedtags['img']           = array();
  $activity_allowedtags['img']['src']    = array();
  $activity_allowedtags['img']['alt']    = array();
  $activity_allowedtags['img']['width']  = array();
  $activity_allowedtags['img']['height'] = array();
  $activity_allowedtags['img']['class']  = array();
  $activity_allowedtags['img']['id']     = array();
  $activity_allowedtags['img']['title']  = array();
  $activity_allowedtags['code']          = array();

  $activity_allowedtags = apply_filters('cw_class_filter_kses', $activity_allowedtags);
  return wp_kses($content, $activity_allowedtags);
}

/**
 * Add rel=nofollow to a link.
 *
 * inspired bp_activity_make_nofollow_filter
 *
 * @package CW Class
 * @subpackage Filters
 *
 * @since CW Class (1.0.0)
 */
function cw_class_make_nofollow_filter($text = '')
{
  return preg_replace_callback('|<a (.+?)>|i', 'cw_class_make_nofollow_filter_callback', $text);
}

/**
 * Add rel=nofollow to a link.
 *
 * inspired by bp_activity_make_nofollow_filter_callback
 *
 * @package CW Class
 * @subpackage Filters
 *
 * @since CW Class (1.0.0)
 */
function cw_class_make_nofollow_filter_callback($matches)
{
  $text = $matches[1];
  $text = str_replace(array(' rel="nofollow"', " rel='nofollow'"), '', $text);
  return "<a $text rel=\"nofollow\">";
}

/**
 * Add oembed support to cw_class description and report.
 *
 * @package CW Class
 * @subpackage Filters
 *
 * @since CW Class (1.3.0)
 * @uses BP_Embed
 */
function cw_class_allow_oembed($bp_oembed_class = null)
{
  add_filter('cw_class_single_get_the_report', array(&$bp_oembed_class, 'autoembed'), 8);
  add_filter('cw_class_single_get_the_report', array(&$bp_oembed_class, 'run_shortcode'), 7);

  add_filter('cw_class_single_get_the_description', array(&$bp_oembed_class, 'autoembed'), 8);
  add_filter('cw_class_single_get_the_description', array(&$bp_oembed_class, 'run_shortcode'), 7);
}
add_action('bp_core_setup_oembed', 'cw_class_allow_oembed', 10, 1);

/**
 * Map capabilities
 *
 * @package CW Class
 * @subpackage Filters
 *
 * @since CW Class (1.0.0)
 */
function cw_class_map_meta_caps($caps = array(), $cap = '', $user_id = 0, $args = array())
{

  // What capability is being checked?
  switch ($cap) {

      /** Reading ***********************************************************/

    case 'read_private_cw_classes':

      if (!empty($args[0])) {
        // Get the post
        $_post = get_post($args[0]);
        if (!empty($_post)) {

          // Get caps for post type object
          $post_type           = get_post_type_object($_post->post_type);
          $post_meta_attendees = get_post_meta($_post->ID, '_cw_class_attendees');
          $attendees           = !empty($post_meta_attendees) ? (array) $post_meta_attendees : array();
          $caps                = array();

          // Allow author to edit his class
          if ($user_id == $_post->post_author || in_array($user_id, $attendees)) {
            $caps[] = 'exist';

            // Admins can always edit
          } else if (user_can($user_id, 'manage_options')) {
            $caps = array('manage_options');
          } else {
            $caps[] = $post_type->cap->edit_others_posts;
          }
        }
      } else if (user_can($user_id, 'manage_options')) {
        $caps = array('manage_options');
      }

      break;

      /** Publishing ********************************************************/

    case 'publish_cw_classes':

      if (bp_is_my_profile()) {
        $caps = array('exist');
      }

      // Admins can always publish
      if (user_can($user_id, 'manage_options')) {
        $caps = array('manage_options');
      }

      break;

      /** Participate to cw_class *********************************/

    case 'subscribe_cw_class':
      if (!empty($user_id)) {
        $caps = array('exist');
      }

      break;

      /** Editing ***********************************************************/

    case 'edit_cw_classes':

      if (bp_is_my_profile()) {
        $caps = array('exist');
      }

      // Admins can always edit
      if (user_can($user_id, 'manage_options')) {
        $caps = array('manage_options');
      }

      break;

      // Used primarily in wp-admin
    case 'edit_others_cw_classes':

      // Admins can always edit
      if (user_can($user_id, 'manage_options')) {
        $caps = array('manage_options');
      }

      break;

      // Used everywhere
    case 'edit_cw_class':

      if (!empty($args[0])) {
        // Get the post
        $_post = get_post($args[0]);
        if (!empty($_post)) {

          // Get caps for post type object
          $post_type = get_post_type_object($_post->post_type);
          $caps      = array();

          // Allow author to edit his class
          if ($user_id == $_post->post_author) {
            $caps[] = 'exist';

            // Admins can always edit
          } else if (user_can($user_id, 'manage_options')) {
            $caps = array('manage_options');
          } else {
            $caps[] = $post_type->cap->edit_others_posts;
          }
        }
      } else if (user_can($user_id, 'manage_options')) {
        $caps = array('manage_options');
      }

      break;

      /** Deleting **********************************************************/

    case 'delete_cw_class':

      if (!empty($args[0])) {
        // Get the post
        $_post = get_post($args[0]);
        if (!empty($_post)) {

          // Get caps for post type object
          $post_type = get_post_type_object($_post->post_type);
          $caps      = array();

          // Allow author to edit his class
          if ($user_id == $_post->post_author) {
            $caps[] = 'exist';

            // Admins can always edit
          } else if (user_can($user_id, 'manage_options')) {
            $caps = array('manage_options');
          } else {
            $caps[] = $post_type->cap->delete_others_posts;
          }
        }
      } else if (user_can($user_id, 'manage_options')) {
        $caps = array('manage_options');
      }

      break;

      // Moderation override
    case 'delete_cw_classes':
    case 'delete_others_cw_classes':

      // Moderators can always delete
      if (user_can($user_id, 'manage_options')) {
        $caps = array('manage_options');
      }

      break;

      /** Admin *************************************************************/

    case 'cw_classes_moderate':

      // Admins can always moderate
      if (user_can($user_id, 'manage_options')) {
        $caps = array('manage_options');
      }

      break;
  }

  return apply_filters('cw_class_map_meta_caps', $caps, $cap, $user_id, $args);
}
add_filter('map_meta_cap', 'cw_class_map_meta_caps', 10, 4);

/*** Editor filters, inspired by bbPress way of dealing with it ***/


/**
 * Edit TinyMCE plugins to match core behaviour
 *
 * @package CW Class
 * @subpackage Filters
 *
 * @since CW Class (1.0.0)
 */
function cw_class_tiny_mce_plugins($plugins = array())
{

  // Unset fullscreen
  foreach ($plugins as $key => $value) {
    if ('fullscreen' === $value) {
      unset($plugins[$key]);
      break;
    }
  }

  return apply_filters('cw_class_get_tiny_mce_plugins', $plugins);
}

/**
 * Edit TeenyMCE buttons to match allowedtags
 *
 * @package CW Class
 * @subpackage Filters
 *
 * @since CW Class (1.0.0)
 */
function cw_class_teeny_mce_buttons($buttons = array())
{

  // Remove some buttons from TeenyMCE
  $buttons = array_diff($buttons, array(
    'underline',
    'justifyleft',
    'justifycenter',
    'justifyright',
    'aligncenter',
    'alignleft',
    'alignright',
    'numlist',
    'bullist'
  ));

  return apply_filters('cw_class_teeny_mce_buttons', $buttons);
}

/**
 * Edit TinyMCE quicktags buttons to match allowedtags
 *
 * @package CW Class
 * @subpackage Filters
 *
 * @since CW Class (1.0.0)
 */
function cw_class_quicktags_settings($settings = array())
{

  // Get buttons out of settings
  $buttons_array = explode(',', $settings['buttons']);

  // Diff the ones we don't want out
  $buttons = array_diff($buttons_array, array(
    'ins',
    'more',
    'spell',
    'img',
    'ul',
    'li',
    'ol'
  ));

  // Put them back into a string in the $settings array
  $settings['buttons'] = implode(',', $buttons);

  return apply_filters('cw_class_quicktags_settings', $settings);
}

/**
 * Append a link to download the iCalendar file of the cw_class
 *
 * If for some reason, the dates/hours are not consistent, simply use
 * remove_filter( 'cw_class_single_get_the_date', 'cw_class_append_ical_link' );
 * till i'll fix the issue ;)
 *
 * @package CW Class
 * @subpackage Filters
 *
 * @since CW Class (1.1.0)
 *
 * @param  string $output      the definitive date output for the cw_class
 * @param  cw_class_Item $cw_class the cw_class object
 * @return string              HTML Output
 */
function cw_class_append_ical_link($output = '', $cw_class = null)
{
  if (empty($output) || empty($cw_class)) {
    return $output;
  }

  if (bp_loggedin_user_id() != $cw_class->organizer && !in_array(bp_loggedin_user_id(), $cw_class->attendees)) {
    return $output;
  }

  $output .= ' <a href="' . esc_url(cw_class_get_ical_link($cw_class->id, $cw_class->organizer)) . '" title="' . esc_attr__('Download the iCal file', 'cw_class') . '" class="ical-link"><span></span></a>';

  return $output;
}

/**
 * Adds cw_class slug into groups forbidden names
 *
 * @package CW Class
 * @subpackage Filters
 *
 * @since  CW Class (1.1.0)
 *
 * @param  array  $names the groups forbidden names
 * @uses   buddypress() to get the BuddyPress main instance
 * @return array        the same names + cw_class forbidden ones.
 */
function cw_class_forbidden_names($names = array())
{
  // Get the cw_class slug
  $cw_class_slug = buddypress()->cw_class->slug;

  $forbidden = array($cw_class_slug);

  // Just in case!
  if ('cw_class' != $cw_class_slug) {
    $forbidden[] = 'cw_class';
  }

  return array_merge($names, $forbidden);
}
add_filter('groups_forbidden_names', 'cw_class_forbidden_names', 10, 1);

/**
 * Customize the login message
 *
 * @package CW Class
 * @subpackage Filters
 *
 * @since  CW Class (1.1.0)
 *
 * @param  string $message  the login message
 * @param  string $redirect the url to redirect to once logged in
 * @uses   buddypress()     to get BuddyPress instance
 * @return string           the login message
 */
function cw_class_login_message($message = '', $redirect = '')
{
  if (!empty($redirect) && false !== strpos($redirect, buddypress()->cw_class->slug . '/schedule/ical')) {
    $message = __('You must log in to download the calendar file.', 'cw_class');
  }

  return $message;
}
add_filter('bp_wp_login_error', 'cw_class_login_message', 10, 2);
