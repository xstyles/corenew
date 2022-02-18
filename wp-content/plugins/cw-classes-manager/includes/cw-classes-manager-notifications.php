<?php

/**
 * CW Class Notifications.
 *
 * Screen and Email notification functions
 *
 * @package CW Class
 * @subpackage Notifications
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Displays a checkbox to allow notifications to be sent
 *
 * @package CW Class
 * @subpackage Notifications
 *
 * @since CW Class (1.0.0)
 */
function cw_class_notifications_edit_form()
{
?>
  <p>
    <label for="cw_class-edit-notify" class="normal">
      <input type="checkbox" id="cw_class-edit-notify" name="_cw_class_edit[notify]" value="1" checked="true"> <?php esc_html_e('Notify attendees of changes about this cw_class', 'cw_class'); ?>
    </label>
  </p>
<?php
}
add_action('cw_class_edit_form_after_dates', 'cw_class_notifications_edit_form', 9);

/**
 * Let the user customize email settings
 *
 * @package CW Class
 * @subpackage Notifications
 *
 * @since CW Class (1.0.0)
 */
function cw_class_screen_notification_settings()
{
  $user_id = bp_displayed_user_id() ? bp_displayed_user_id() : bp_loggedin_user_id();

  $schedule_notifications = get_user_meta($user_id, 'notification_cw_class_schedule', true);
  $attend_notifications   = get_user_meta($user_id, 'notification_cw_class_attend', true);

  if (empty($schedule_notifications)) {
    $schedule_notifications = 'yes';
  }

  if (empty($attend_notifications)) {
    $attend_notifications = 'yes';
  }
?>
  <table class="notification-settings" id="cw_class-notification-settings">

    <thead>
      <tr>
        <th class="icon"></th>
        <th class="title"><?php esc_html_e('cw_class', 'cw_class') ?></th>
        <th class="yes"><?php esc_html_e('Yes', 'cw_class') ?></th>
        <th class="no"><?php esc_html_e('No', 'cw_class') ?></th>
      </tr>
    </thead>

    <tbody>
      <tr>
        <td></td>
        <td><?php esc_html_e('An attendee replyed about a cw_class you scheduled', 'cw_class') ?></td>
        <td class="yes"><input type="radio" name="notifications[notification_cw_class_schedule]" value="yes" <?php checked('yes', $schedule_notifications) ?> /></td>
        <td class="no"><input type="radio" name="notifications[notification_cw_class_schedule]" value="no" <?php checked('no', $schedule_notifications) ?> /></td>
      </tr>
      <tr>
        <td></td>
        <td><?php esc_html_e('You are invited to participate to a cw_class', 'cw_class') ?></td>
        <td class="yes"><input type="radio" name="notifications[notification_cw_class_attend]" value="yes" <?php checked('yes', $attend_notifications) ?> /></td>
        <td class="no"><input type="radio" name="notifications[notification_cw_class_attend]" value="no" <?php checked('no', $attend_notifications) ?> /></td>
      </tr>

      <?php do_action('cw_class_screen_notification_settings'); ?>

    </tbody>
  </table>
<?php
}
add_action('bp_notification_settings', 'cw_class_screen_notification_settings');

/**
 * Send a screen notifications & email one when a rendez vous is published
 *
 * @package CW Class
 * @subpackage Notifications
 *
 * @since 1.0.0
 * @since 1.4.0 Use the BP Emails
 */
function cw_class_published_notification($id = 0, $args = array(), $notify = 0)
{
  $bp = buddypress();

  if (empty($id) || empty($notify)) {
    return;
  }

  $cw_class = cw_class_get_item($id);
  $attendees = $cw_class->attendees;

  if (empty($attendees)) {
    return;
  }

  // Remove the organizer
  if (in_array($cw_class->organizer, $attendees)) {
    $attendees = array_diff($attendees, array($cw_class->organizer));
  }

  $organizer_name = bp_core_get_user_displayname($cw_class->organizer);
  $cw_class_link = cw_class_get_single_link($id, $cw_class->organizer);
  $cw_class_content = stripslashes($cw_class->title) . "\n\n" . stripslashes($cw_class->description);
  $organizer_name = stripslashes($organizer_name);

  // Append the custom message, if any
  if (!empty($args['message'])) {
    $cw_class_content .= "\n\n" . stripslashes($args['message']);
  }

  $cw_class_content = wp_kses($cw_class_content, array());

  // Set the BP Email tokens
  $bp_email_args = array(
    'tokens' => array(
      'organizer.name'     => $organizer_name,
      'rendezvous.content' => $cw_class_content,
      'rendezvous.url'     => esc_url($cw_class_link),
      'rendezvous.title'   => $cw_class->title,
    ),
  );

  // This way we'll have all needed users data at one and we will avoid spam users
  $users = bp_core_get_users(array(
    'type' => 'alphabetical',
    'include' => implode(',', $attendees)
  ));

  foreach ($users['users'] as $attendee) {

    // Screen Notification
    bp_notifications_add_notification(array(
      'user_id'           => $attendee->ID,
      'item_id'           => $id,
      'secondary_item_id' => $cw_class->organizer,
      'component_name'    => $bp->cw_class->id,
      'component_action'  => 'cw_class_attend',
    ));

    // Sending Emails
    if ('no' == get_user_meta($attendee->ID, 'notification_cw_class_attend', true)) {
      continue;
    }

    // Send the Email
    bp_send_email('cw_class-new-item', (int) $attendee->ID, $bp_email_args);
  }
}
add_action('cw_class_after_publish', 'cw_class_published_notification', 10, 3);

/**
 * Help to check if a notification needs to be send
 *
 * @package CW Class
 * @subpackage Notifications
 *
 * @since CW Class (1.0.0)
 */
function cw_class_maybe_notify_updates($args = array(), $notify = 0)
{
  $cw_class = cw_class();

  if (empty($notify) || empty($args)) {
    return;
  }

  $cw_class->item = cw_class_get_item(absint($args['id']));

  if (empty($cw_class->item)) {
    return;
  }

  if (empty($cw_class->item->def_date) && !empty($args['def_date'])) {
    $cw_class->item->date_fixed = $args['def_date'];
  }

  if (empty($cw_class->item->report) && !empty($args['report'])) {
    $cw_class->item->report_created = 1;
  }
}
add_action('cw_class_before_update', 'cw_class_maybe_notify_updates', 10, 2);

/**
 * Send a screen notifications & email one when a rendez vous is updated
 *
 * Date set or report created
 *
 * @package CW Class
 * @subpackage Notifications
 *
 * @since 1.0.0
 * @since 1.4.0 Use the BP Emails
 */
function cw_class_updated_notification($id = 0, $args = array(), $notify = 0)
{
  $bp = buddypress();
  $manager = cw_class();

  if (empty($id) || empty($notify) || empty($manager->item) || $id != $manager->item->id || empty($args['message'])) {
    return;
  }

  $has_updated = !empty($manager->item->date_fixed) || !empty($manager->item->report_created) ? true : false;

  if (empty($has_updated) && empty($args['message'])) {
    return;
  }

  // Only allow 1 message per day.
  if (empty($has_updated)  && 1 == get_transient('cw_class_last_message_' . $id)) {
    return;
  }

  $cw_class = $manager->item;
  $attendees = $cw_class->attendees;

  if (empty($attendees)) {
    return;
  }

  // Remove the organizer
  if (in_array($cw_class->organizer, $attendees)) {
    $attendees = array_diff($attendees, array($cw_class->organizer));
  }

  $organizer_name = bp_core_get_user_displayname($cw_class->organizer);
  $cw_class_link = cw_class_get_single_link($id, $cw_class->organizer);

  $cw_class_content = stripslashes($cw_class->title);
  $manager_updated_action = false;
  $component_action = false;

  if (!empty($manager->item->date_fixed)) {
    if (is_numeric($manager->item->date_fixed)) {
      $cw_class_content .= "\n\n" . sprintf(
        __('Date fixed to %1$s at %2$s', 'cw_class'),
        date_i18n(get_option('date_format'), $manager->item->date_fixed),
        date_i18n(get_option('time_format'), $manager->item->date_fixed)
      );
      $cw_class_content .= "\n\n" . sprintf(__('Download the <a href="%s">Calendar file</a>', 'cw_class'), cw_class_get_ical_link($id, $cw_class->organizer));
    } else {
      $cw_class_content .= "\n\n" . sprintf(
        __('Date fixed to %s', 'cw_class'),
        esc_html($manager->item->date_fixed)
      );
    }

    $manager_updated_action = __('fixed the date', 'cw_class');
    $component_action = 'cw_class_fixed';
  } else if (!empty($manager->item->report_created)) {
    $manager_updated_action = __('created the report', 'cw_class');
    $component_action = 'cw_class_report';
  } else if (!empty($args['message'])) {
    $manager_updated_action = __('sent a message', 'cw_class');
    $component_action = 'cw_class_message';
    set_transient('cw_class_last_message_' . $id, 1, 24 * HOUR_IN_SECONDS);
  }

  $organizer_name = stripslashes($organizer_name);

  // Append the custom message, if any
  if (!empty($args['message'])) {
    $cw_class_content .= "\n\n" . stripslashes($args['message']);
  }

  // allow the link tag so that the calendar link can be inserted into the email content
  $cw_class_content = wp_kses($cw_class_content, array('a' => array('href' => true)));

  // Set the BP Email tokens
  $bp_email_args = array(
    'tokens' => array(
      'organizer.name'     => $organizer_name,
      'rendezvous.action'  => $manager_updated_action,
      'rendezvous.content' => $cw_class_content,
      'rendezvous.url'     => esc_url($cw_class_link),
      'rendezvous.title'   => $cw_class->title,
    ),
  );

  // This way we'll have all needed users data at one and we will avoid spam users
  $users = bp_core_get_users(array(
    'type' => 'alphabetical',
    'include' => implode(',', $attendees)
  ));

  foreach ($users['users'] as $attendee) {

    if ('cw_class_message' != $component_action) {
      // Screen Notification
      bp_notifications_add_notification(array(
        'user_id'           => $attendee->ID,
        'item_id'           => $id,
        'secondary_item_id' => $cw_class->organizer,
        'component_name'    => $bp->cw_class->id,
        'component_action'  => $component_action,
      ));
    }

    // Sending Emails
    if ('no' == get_user_meta($attendee->ID, 'notification_cw_class_attend', true)) {
      continue;
    }

    // Send the Email
    bp_send_email('cw_class-item-edited', (int) $attendee->ID, $bp_email_args);
  }
}
add_action('cw_class_after_update', 'cw_class_updated_notification', 10, 3);


/**
 * Send a screen notifications & email one when an attendee set his preferences
 *
 * @package CW Class
 * @subpackage Notifications
 *
 * @since 1.0.0
 * @since 1.4.0 Use the BP Emails
 */
function cw_class_notify_organizer($args = array(), $attendee_id = 0, $cw_class = null)
{
  $bp = buddypress();

  if (empty($attendee_id) || empty($cw_class) || $attendee_id == $cw_class->organizer) {
    return;
  }

  // Screen Notification
  bp_notifications_add_notification(array(
    'user_id'           => $cw_class->organizer,
    'item_id'           => $cw_class->id,
    'secondary_item_id' => $attendee_id,
    'component_name'    => $bp->cw_class->id,
    'component_action'  => 'cw_class_schedule',
  ));

  // Sending Emails
  if ('no' == get_user_meta($cw_class->organizer, 'notification_cw_class_schedule', true)) {
    return;
  }

  $cw_class_link = cw_class_get_single_link($cw_class->id, $cw_class->organizer);
  $cw_class_title = stripslashes($cw_class->title);
  $cw_class_content = wp_kses($cw_class_title, array());

  $attendee_name = bp_core_get_user_displayname($attendee_id);
  $attendee_name = stripslashes($attendee_name);

  // Send the Email
  bp_send_email('cw_class-preference-set', (int) $cw_class->organizer, array(
    'tokens' => array(
      'attendee.name'     => $attendee_name,
      'rendezvous.content' => $cw_class_content,
      'rendezvous.url'     => esc_url($cw_class_link),
      'rendezvous.title'   => $cw_class_title,
    ),
  ));
}
add_action('cw_class_after_attendee_prefs', 'cw_class_notify_organizer', 10, 3);


/**
 * Mark screen notifications
 *
 * @package CW Class
 * @subpackage Notifications
 *
 * @since CW Class (1.0.0)
 */
function cw_class_remove_all_attend_notifications()
{

  if (!isset($_GET['n'])) {
    return;
  }

  $bp = buddypress();

  /**
   * Would be nice if BuddyPress allowed marking notifications for an array of actions..
   */
  bp_notifications_mark_notifications_by_type(bp_loggedin_user_id(), buddypress()->cw_class->id, 'cw_class_attend');
  bp_notifications_mark_notifications_by_type(bp_loggedin_user_id(), buddypress()->cw_class->id, 'cw_class_fixed');
  bp_notifications_mark_notifications_by_type(bp_loggedin_user_id(), buddypress()->cw_class->id, 'cw_class_report');
}
add_action('cw_class_attend', 'cw_class_remove_all_attend_notifications');


/**
 * Mark screen notifications
 *
 * @package CW Class
 * @subpackage Notifications
 *
 * @since CW Class (1.0.0)
 */
function cw_class_remove_current_attend_notifications()
{

  if (!isset($_GET['rdv']) || !isset($_GET['n'])) {
    return;
  }

  $bp = buddypress();

  /**
   * Removing all for the current user & current cw_class
   */
  bp_notifications_mark_notifications_by_item_id(bp_loggedin_user_id(), $_GET['rdv'], buddypress()->cw_class->id, false);
}

add_action('cw_class_single_screen', 'cw_class_remove_current_attend_notifications');


/**
 * Mark notifications
 *
 * @package CW Class
 * @subpackage Notifications
 *
 * @since CW Class (1.0.0)
 */
function cw_class_remove_all_schedule_notifications()
{

  if (!isset($_GET['n'])) {
    return;
  }

  $bp = buddypress();

  /**
   * Would be nice if BuddyPress allowed marking notifications for an array of actions..
   */
  bp_notifications_mark_notifications_by_type(bp_loggedin_user_id(), buddypress()->cw_class->id, 'cw_class_schedule');
}
add_action('cw_class_schedule', 'cw_class_remove_all_schedule_notifications');


/**
 * Format screen notifications
 *
 * @package CW Class
 * @subpackage Notifications
 *
 * @since CW Class (1.0.0)
 */
function cw_class_format_notifications($action, $item_id, $secondary_item_id, $total_items, $format = 'string')
{
  $bp = buddypress();

  switch ($action) {
    case 'cw_class_schedule':

      if ((int) $total_items > 1) {
        $cw_class_link = add_query_arg(
          array('n' => $total_items),
          trailingslashit(bp_loggedin_user_domain() . $bp->cw_class->slug . '/schedule')
        );
        $title = __('cw_class preferences updated', 'cw_class');
        $text = sprintf(__('%d cw_class preferences updated', 'cw_class'), (int) $total_items);
        $filter = 'cw_class_multiple_userset_notification';
      } else {
        $cw_class_link = add_query_arg(
          array('n' => 1),
          cw_class_get_single_link($item_id, bp_loggedin_user_id())
        );
        $user_fullname = bp_core_get_user_displayname($secondary_item_id, false);
        $title = __('View the cw_class', 'cw_class');
        $text =  sprintf(__('%s set his preferences about a cw_class', 'cw_class'), $user_fullname);
        $filter = 'cw_class_single_userset_notification';
      }

      break;

    case 'cw_class_attend':

      if ((int) $total_items > 1) {
        $cw_class_link = add_query_arg(
          array('n' => $total_items),
          trailingslashit(bp_loggedin_user_domain() . $bp->cw_class->slug . '/attend')
        );
        $title = __('cw_class sheduled', 'cw_class');
        $text = sprintf(__('%d cw_class sheduled', 'cw_class'), (int) $total_items);
        $filter = 'cw_class_multiple_attend_notification';
      } else {
        $cw_class_link = add_query_arg(
          array('n' => 1),
          cw_class_get_single_link($item_id, $secondary_item_id)
        );
        $user_fullname = bp_core_get_user_displayname($secondary_item_id, false);
        $title = __('View the cw_class', 'cw_class');
        $text =  sprintf(__('%s scheduled a cw_class', 'cw_class'), $user_fullname);
        $filter = 'cw_class_single_attend_notification';
      }

      break;

    case 'cw_class_fixed':

      if ((int) $total_items > 1) {
        $cw_class_link = add_query_arg(
          array('n' => $total_items),
          trailingslashit(bp_loggedin_user_domain() . $bp->cw_class->slug . '/attend')
        );
        $title = __('cw_class fixed', 'cw_class');
        $text = sprintf(__('%d cw_class fixed', 'cw_class'), (int) $total_items);
        $filter = 'cw_class_multiple_fixed_notification';
      } else {
        $cw_class_link = add_query_arg(
          array('n' => 1),
          cw_class_get_single_link($item_id, $secondary_item_id)
        );
        $user_fullname = bp_core_get_user_displayname($secondary_item_id, false);
        $title = __('View the cw_class', 'cw_class');
        $text =  sprintf(__('%s fixed a cw_class', 'cw_class'), $user_fullname);
        $filter = 'cw_class_single_fixed_notification';
      }

      break;

    case 'cw_class_report':

      if ((int) $total_items > 1) {
        $cw_class_link = add_query_arg(
          array('n' => $total_items),
          trailingslashit(bp_loggedin_user_domain() . $bp->cw_class->slug . '/attend')
        );
        $title = __('cw_class report created', 'cw_class');
        $text = sprintf(__('%d cw_class reports created', 'cw_class'), (int) $total_items);
        $filter = 'cw_class_multiple_report_notification';
      } else {
        $cw_class_link = add_query_arg(
          array('n' => 1),
          cw_class_get_single_link($item_id, $secondary_item_id)
        );
        $user_fullname = bp_core_get_user_displayname($secondary_item_id, false);
        $title = __('View the cw_class', 'cw_class');
        $text =  sprintf(__('%s created a report for a cw_class', 'cw_class'), $user_fullname);
        $filter = 'cw_class_single_report_notification';
      }

      break;
  }

  /**
   * If on notifications read screen remove the n arguments to
   * avoid re runing the mark notification function
   */

  if (bp_is_user_notifications() && bp_is_current_action('read')) {
    $cw_class_link = remove_query_arg('n', $cw_class_link);
  }

  if ('string' == $format) {
    $return = apply_filters($filter, '<a href="' . esc_url($cw_class_link) . '" title="' . esc_attr($title) . '">' . esc_html($text) . '</a>', $cw_class_link, (int) $total_items, $item_id, $secondary_item_id);
  } else {
    $return = apply_filters($filter, array(
      'text' => $text,
      'link' => esc_url($cw_class_link)
    ), $cw_class_link, (int) $total_items, $item_id, $secondary_item_id);
  }

  do_action('cw_class_format_notifications', $action, $item_id, $secondary_item_id, $total_items);

  return $return;
}

/**
 * Deletes notifications for a cancelled cw_class
 *
 * @package CW Class
 * @subpackage Notifications
 *
 * @since CW Class (1.0.0)
 */
function cw_class_delete_item_notifications($cw_class_id = 0, $cw_class = null)
{
  if (empty($cw_class_id)) {
    return;
  }

  // No need to delete activities in case of drafts
  if (!empty($cw_class) && 'draft' == $cw_class->post_status) {
    return;
  }

  // delete all notifications
  BP_Notifications_Notification::delete(array(
    'item_id'           => $cw_class_id,
    'component_name'    => buddypress()->cw_class->id,
  ));
}
add_action('cw_class_after_delete', 'cw_class_delete_item_notifications', 9, 2);

/**
 * Remove a user's notification data.
 *
 * @package CW Class
 * @subpackage Notifications
 *
 * @since CW Class (1.0.0)
 */
function cw_class_remove_all_user_notifications($user_id = 0)
{
  if (empty($user_id)) {
    return false;
  }

  // delete all notifications user sent to others
  BP_Notifications_Notification::delete(array(
    'secondary_item_id' => $user_id,
    'component_name'    => buddypress()->cw_class->id,
  ));
}
add_action('wpmu_delete_user',  'cw_class_remove_all_user_notifications', 10, 1);
add_action('delete_user',       'cw_class_remove_all_user_notifications', 10, 1);

/**
 * Get email templates
 *
 * @since 1.4.0
 *
 * @return array An associative array containing the email type and the email template data.
 */
function cw_class_get_emails()
{
  return apply_filters('cw_class_get_emails', array(
    'cw_class-new-item'       => array(
      'description' => __('A member invited members to a new cw_class', 'cw_class'),
      'term_id'     => 0,
      'post_title'   => __('[{{{site.name}}}] {{organizer.name}} invited you to the next class', 'cw_class'),
      'post_content' => __("{{organizer.name}} is scheduling a new cw_class: {{{rendezvous.content}}}\n\nTo help him fix the date, please log in and visit: <a href=\"{{{rendezvous.url}}}\">{{rendezvous.title}}</a>.", 'cw_class'),
      'post_excerpt' => __("{{organizer.name}} is scheduling a new cw_class: {{rendezvous.content}}\n\nTo help him fix the date, please log in and visit: \n\n{{{rendezvous.url}}}.", 'cw_class'),
    ),
    'cw_class-item-edited'    => array(
      'description' => __('A member updated a cw_class', 'cw_class'),
      'term_id'     => 0,
      'post_title'   => __('[{{{site.name}}}] {{organizer.name}} updated a cw_class', 'cw_class'),
      'post_content' => __("{{organizer.name}} {{rendezvous.action}} for the cw_class: {{{rendezvous.content}}}\n\nTo view details, log in and visit: <a href=\"{{{rendezvous.url}}}\">{{rendezvous.title}}</a>.", 'cw_class'),
      'post_excerpt' => __("{{organizer.name}} {{rendezvous.action}} for the cw_class: {{rendezvous.content}}\n\nTo view details, log in and visit: \n\n{{{rendezvous.url}}}.", 'cw_class'),
    ),
    'cw_class-preference-set' => array(
      'description' => __('A member selected date(s) for a cw_class', 'cw_class'),
      'term_id'     => 0,
      'post_title'   => __('[{{{site.name}}}] {{attendee.name}} selected date(s) for a cw_class', 'cw_class'),
      'post_content' => __("{{attendee.name}} set their preferences for the cw_class: {{{rendezvous.content}}}\n\nTo view details, log in and visit: <a href=\"{{{rendezvous.url}}}\">{{rendezvous.title}}</a>.", 'cw_class'),
      'post_excerpt' => __("{{attendee.name}} set their preferences for the cw_class: {{rendezvous.content}}\n\nTo view details, log in and visit: \n\n{{{rendezvous.url}}}.", 'cw_class'),
    ),
  ));
}

/**
 * Install/Reinstall email templates for the plugin's notifications
 *
 * @since 1.4.0
 */
function cw_class_install_emails()
{
  $switched = false;

  // Switch to the root blog, where the email posts live.
  if (!bp_is_root_blog()) {
    switch_to_blog(bp_get_root_blog_id());
    $switched = true;
  }

  // Get Emails
  $email_types = cw_class_get_emails();

  // Set email types
  foreach ($email_types as $email_term => $term_args) {
    if (term_exists($email_term, bp_get_email_tax_type())) {
      $email_type = get_term_by('slug', $email_term, bp_get_email_tax_type());

      $email_types[$email_term]['term_id'] = $email_type->term_id;
    } else {
      $term = wp_insert_term($email_term, bp_get_email_tax_type(), array(
        'description' => $term_args['description'],
      ));

      $email_types[$email_term]['term_id'] = $term['term_id'];
    }

    // Insert Email templates if needed
    if (!empty($email_types[$email_term]['term_id']) && !is_a(bp_get_email($email_term), 'BP_Email')) {
      wp_insert_post(array(
        'post_status'  => 'publish',
        'post_type'    => bp_get_email_post_type(),
        'post_title'   => $email_types[$email_term]['post_title'],
        'post_content' => $email_types[$email_term]['post_content'],
        'post_excerpt' => $email_types[$email_term]['post_excerpt'],
        'tax_input'    => array(
          bp_get_email_tax_type() => array($email_types[$email_term]['term_id'])
        ),
      ));
    }
  }

  if ($switched) {
    restore_current_blog();
  }
}
add_action('bp_core_install_emails', 'cw_class_install_emails');
