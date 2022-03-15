<?php

/**
 * CW Class Activity
 *
 * Activity functions
 *
 * @package CW Class
 * @subpackage Activity
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Displays a checkbox to allow the user to generate an activity
 *
 * @package CW Class
 * @subpackage Activity
 *
 * @since CW Class (1.0.0)
 */
function cw_class_activity_edit_form()
{
?>
  <p>
    <label for="cw_class-edit-activity" class="normal">
      <input type="checkbox" id="cw_class-edit-activity" name="_cw_class_edit[activity]" value="1" <?php disabled(1, cw_class_single_get_privacy()); ?>> <?php esc_html_e('Record an activity for all members', 'cw_class'); ?>
    </label>
  </p>
<?php
}
add_action('cw_class_edit_form_after_dates', 'cw_class_activity_edit_form');

/**
 * Register the activity actions
 *
 * @package CW Class
 * @subpackage Activity
 *
 * @since CW Class (1.0.0)
 */
function cw_class_register_activity_actions()
{
  $bp = buddypress();

  // Bail if activity is not active
  if (!bp_is_active('activity')) {
    return false;
  }

  bp_activity_set_action(
    $bp->cw_class->id,
    'new_cw_class',
    __('New cw_class', 'cw_class'),
    'cw_class_format_activity_action',
    __('New cw_class', 'cw_class'),
    array('activity', 'member')
  );

  bp_activity_set_action(
    $bp->cw_class->id,
    'updated_cw_class',
    __('Updated a cw_class', 'cw_class'),
    'cw_class_format_activity_action',
    __('Updated a cw_class', 'cw_class'),
    array('activity', 'member')
  );

  do_action('cw_class_register_activity_actions');
}
add_action('bp_register_activity_actions', 'cw_class_register_activity_actions');

/**
 * format callback
 *
 * @package CW Class
 * @subpackage Activity
 *
 * @since CW Class (1.0.0)
 */
function cw_class_format_activity_action($action, $activity)
{
  $cw_class_id = $activity->item_id;
  $organizer      = $activity->secondary_item_id;

  if ($activity->component != buddypress()->cw_class->id) {
    $cw_class_id = $activity->secondary_item_id;
    $organizer      = $activity->user_id;
  }

  $cw_class_url = cw_class_get_single_link($cw_class_id, $organizer);

  $cw_class_title = bp_activity_get_meta($activity->id, 'cw_class_title');

  // Should only be empty at the time of class creation
  if (empty($cw_class_title)) {

    $cw_class = cw_class_get_item($cw_class_id);
    if (is_a($cw_class, 'CW_Class_Item')) {
      $cw_class_title = $cw_class->title;
      bp_activity_update_meta($activity->id, 'cw_class_title', $cw_class_title);
    }
  }

  $cw_class_link  = '<a href="' . esc_url($cw_class_url) . '">' . esc_html($cw_class_title) . '</a>';

  $user_link = bp_core_get_userlink($activity->user_id);

  $action_part = __('scheduled a new', 'cw_class');

  if ('updated_cw_class' == $activity->type) {
    $action_part = __('updated a', 'cw_class');
  }

  $action  = sprintf(__('%1$s %2$s cw_class, %3$s', 'cw_class'), $user_link, $action_part, $cw_class_link);

  return apply_filters('cw_class_format_activity_action', $action, $activity);
}

/**
 * Publish!
 *
 * @package CW Class
 * @subpackage Activity
 *
 * @since CW Class (1.0.0)
 */
function cw_class_published_activity($id = 0, $args = array(), $notify = false, $activity = false)
{
  if (empty($id) || empty($activity))
    return;

  $cw_class = cw_class_get_item($id);
  $cw_class_url = cw_class_get_single_link($id, $cw_class->organizer);

  $cw_class_link  = '<a href="' . esc_url($cw_class_url) . '">' . esc_html($cw_class->title) . '</a>';

  $user_link = bp_core_get_userlink($cw_class->organizer);

  $action_part = __('scheduled a new', 'cw_class');

  $action  = sprintf(__('%1$s %2$s cw_class, %3$s', 'cw_class'), $user_link, $action_part, $cw_class_link);

  $content = false;

  if (!empty($cw_class->description)) {
    $content = bp_create_excerpt($cw_class->description);
  }

  $activity_id = bp_activity_add(apply_filters('cw_class_published_activity_args', array(
    'action'            => $action,
    'content'           => $content,
    'component'         => buddypress()->cw_class->id,
    'type'              => 'new_cw_class',
    'primary_link'      => $cw_class_url,
    'user_id'           => $cw_class->organizer,
    'item_id'           => $cw_class->id,
    'secondary_item_id' => $cw_class->organizer
  )));

  if (!empty($activity_id)) {
    bp_activity_update_meta($activity_id, 'cw_class_title', $cw_class->title);
  }

  return true;
}
add_action('cw_class_after_publish', 'cw_class_published_activity', 10, 4);

/**
 * Updated!
 *
 * @package CW Class
 * @subpackage Activity
 *
 * @since CW Class (1.0.0)
 */
function cw_class_updated_activity($id = 0, $args = array(), $notify = false, $activity = false)
{
  if (empty($id) || empty($activity))
    return;

  $manager = cw_class();

  if (empty($manager->item->id)) {
    $cw_class = cw_class_get_item($id);
  } else {
    $cw_class = $manager->item;
  }

  $cw_class_url = cw_class_get_single_link($id, $cw_class->organizer);

  $cw_class_link  = '<a href="' . esc_url($cw_class_url) . '">' . esc_html($cw_class->title) . '</a>';

  $user_link = bp_core_get_userlink($cw_class->organizer);

  $action_part = __('updated a', 'cw_class');

  $action  = sprintf(__('%1$s %2$s cw_class, %3$s', 'cw_class'), $user_link, $action_part, $cw_class_link);

  $activity_id = bp_activity_add(apply_filters('cw_class_updated_activity_args', array(
    'action'            => $action,
    'component'         => buddypress()->cw_class->id,
    'type'              => 'updated_cw_class',
    'primary_link'      => $cw_class_url,
    'user_id'           => $cw_class->organizer,
    'item_id'           => $cw_class->id,
    'secondary_item_id' => $cw_class->organizer
  )));

  if (!empty($activity_id)) {
    bp_activity_update_meta($activity_id, 'cw_class_title', $cw_class->title);
  }

  return true;
}
add_action('cw_class_after_update', 'cw_class_updated_activity', 11, 4);

/**
 * Deletes activities of a cancelled cw_class
 *
 * @package CW Class
 * @subpackage Activity
 *
 * @since CW Class (1.0.0)
 */
function cw_class_delete_item_activities($cw_class_id = 0, $cw_class = null)
{
  if (empty($cw_class_id))
    return;

  $cw_class_status = 'publish';

  if (is_a($cw_class, 'WP_Post')) {
    $cw_class_status = $cw_class->post_status;
  } else if (is_a($cw_class, 'CW_Class_Item')) {
    $cw_class_status = $cw_class->status;
  }

  // No need to delete activities in case of drafts
  if (!empty($cw_class) && 'draft' == $cw_class_status) {
    return;
  }

  $types = array('new_cw_class', 'updated_cw_class');
  $args = apply_filters(
    'cw_class_delete_item_activities_args',
    array(
      'item_id'   => $cw_class_id,
      'component' => buddypress()->cw_class->id,
    )
  );

  foreach ($types as $type) {
    $args['type'] = $type;

    bp_activity_delete_by_item_id($args);
  }
}
add_action('cw_class_after_delete',                 'cw_class_delete_item_activities', 10, 2);
add_action('cw_class_groups_component_deactivated', 'cw_class_delete_item_activities', 10, 2);
add_action('cw_class_groups_member_removed',        'cw_class_delete_item_activities', 10, 2);
