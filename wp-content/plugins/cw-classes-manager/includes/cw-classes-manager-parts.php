<?php

/**
 * CW Class Parts.
 *
 * Template parts used in the plugin
 *
 * @package CW Class
 * @subpackage Parts
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Schedule screen title
 *
 * @package CW Class
 * @subpackage Parts
 *
 * @since CW Class (1.0.0)
 */
function cw_class_schedule_title()
{
  esc_html_e('Schedule classes', 'cw_class');
}

/**
 * Schedule screen actions
 *
 * @package CW Class
 * @subpackage Parts
 *
 * @since CW Class (1.0.0)
 */
function cw_class_schedule_actions()
{
?>
  <ul id="cw_class-nav">
    <li><?php cw_class_editor('new-cw_class'); ?></li>
    <li class="last"><?php cw_class_type_filter(); ?></li>
  </ul>
<?php
}

/**
 * Schedule screen content
 *
 * @package CW Class
 * @subpackage Parts
 *
 * @since CW Class (1.0.0)
 */
function cw_class_schedule_content()
{
  cw_class_schedule_actions();
  cw_class_loop();
}

/**
 * Attend screen title
 *
 * @package CW Class
 * @subpackage Parts
 *
 * @since CW Class (1.0.0)
 */
function cw_class_attend_title()
{
  esc_html_e('Attend a class', 'cw_class');
}

/**
 * Attend screen actions
 *
 * @package CW Class
 * @subpackage Parts
 *
 * @since CW Class (1.0.0)
 */
function cw_class_attend_actions()
{
?>
  <ul id="cw_class-nav">
    <li class="last"><?php cw_class_type_filter(); ?></li>
  </ul>
<?php
}

/**
 * Attend screen content
 *
 * @package CW Class
 * @subpackage Parts
 *
 * @since CW Class (1.0.0)
 */
function cw_class_attend_content()
{
  cw_class_attend_actions();
  cw_class_loop();
}

/**
 * Loop part
 *
 * @package CW Class
 * @subpackage Parts
 *
 * @since CW Class (1.0.0)
 */
function cw_class_loop()
{
  $current_action = apply_filters('cw_class_current_action', bp_current_action());
?>
  <div class="cw_class <?php echo esc_attr($current_action); ?>">

    <?php do_action("cw_class_{$current_action}_loop"); ?>

    <?php if (cw_manager_has_classes()) : ?>

      <div id="pag-top" class="pagination no-ajax">

        <div class="pag-count" id="cw_class-<?php echo esc_attr($current_action); ?>-count-top">

          <?php cw_class_pagination_count(); ?>

        </div>

        <div class="pagination-links" id="cw_class-<?php echo esc_attr($current_action); ?>-pag-top">

          <?php cw_class_pagination_links(); ?>

        </div>

      </div>

      <?php do_action("cw_class_before_{$current_action}_list"); ?>

      <ul id="cw_class-list" class="item-list" role="main">

        <?php while (get_inloop_current_classes()) : get_inloop_current_class(); ?>

          <li <?php cw_manager_get_css_classnames(); ?>>
            <div class="item-avatar">
              <a href="<?php cw_class_the_link(); ?>" title="<?php echo esc_attr(cw_class_get_the_title()); ?>"><?php cw_class_avatar(); ?></a>
            </div>

            <div class="item">
              <div class="item-title"><a href="<?php cw_class_the_link(); ?>" title="<?php echo esc_attr(cw_class_get_the_title()); ?>"><?php cw_class_the_title(); ?></a></div>
              <div class="item-meta"><span class="activity"><?php cw_class_last_modified(); ?></span></div>

              <?php if (cw_class_has_description()) : ?>
                <div class="item-desc"><?php cw_class_the_excerpt(); ?></div>
              <?php endif; ?>

              <?php do_action("cw_class_{$current_action}_item"); ?>

              <?php do_action('cw_class_after_item_description'); ?>

            </div>

            <div class="action">

              <?php do_action("cw_class_{$current_action}_actions"); ?>

              <div class="meta">

                <?php cw_class_the_status(); ?>

              </div>

            </div>

            <div class="clear"></div>
          </li>

        <?php endwhile; ?>

      </ul>

      <?php do_action("cw_class_after_{$current_action}_list"); ?>

      <div id="pag-bottom" class="pagination no-ajax">

        <div class="pag-count" id="cw_class-<?php echo esc_attr($current_action); ?>-count-bottom">

          <?php cw_class_pagination_count(); ?>

        </div>

        <div class="pagination-links" id="cw_class-<?php echo esc_attr($current_action); ?>-pag-bottom">

          <?php cw_class_pagination_links(); ?>

        </div>

      </div>

    <?php else : ?>

      <div id="message" class="info">
        <p><?php _e('There were no cw_class found.', 'cw_class'); ?></p>
      </div>

    <?php endif; ?>

    <?php do_action("cw_class_after_{$current_action}_loop"); ?>

  </div>
<?php
}

/**
 * Edit screen title
 *
 * @package CW Class
 * @subpackage Parts
 *
 * @since CW Class (1.0.0)
 */
function cw_class_edit_title()
{
  esc_html_e('Editing: ', 'cw_class');
  cw_class_single_the_title();

  if (cw_class_single_is_published()) {
    bp_button(array(
      'id'                => 'view-cw_class',
      'component'         => 'cw_class',
      'must_be_logged_in' => true,
      'block_self'        => false,
      'wrapper_id'        => 'cw_class-view-btn',
      'wrapper_class'     => 'right',
      'link_class'        => 'view-cw_class',
      'link_href'         => cw_class_single_get_permalink(),
      'link_title'        => __('View', 'cw_class'),
      'link_text'         => __('View', 'cw_class')
    ));
  }
}

/**
 * Edit screen content
 *
 * @package CW Class
 * @subpackage Parts
 *
 * @since CW Class (1.0.0)
 */
function cw_class_edit_content()
{
?>
  <form action="<?php echo esc_url(cw_class_single_the_form_action()); ?>" method="post" id="cw_class-edit-form" class="standard-form">
    <p>
      <label for="cw_class-edit-title"><?php esc_html_e('Title', 'cw_class'); ?></label>
      <input type="text" name="_cw_class_edit[title]" id="cw_class-edit-title" value="<?php cw_class_single_the_title(); ?>" />
    </p>
    <p>
      <label for="cw_class-edit-description"><?php esc_html_e('Description', 'cw_class'); ?></label>
      <textarea name="_cw_class_edit[description]" id="cw_class-edit-description"><?php cw_class_single_the_description(); ?></textarea>
    </p>
    <p>
      <label for="cw_class-edit-venue"><?php esc_html_e('Venue', 'cw_class'); ?></label>
      <input type="text" name="_cw_class_edit[venue]" id="cw_class-edit-venue" value="<?php cw_class_single_the_venue(); ?>" />
    </p>

    <?php if (cw_class_has_types()) : ?>

      <label for="cw_class-single-type"><?php esc_html_e('Type', 'cw_class'); ?></label>
      <div id="cw_class-single-type">

        <?php cw_class_single_edit_the_type(); ?>

      </div>

    <?php endif; ?>

    <p>
      <label for="cw_class-edit-duration"><?php esc_html_e('Duration', 'cw_class'); ?></label>
      <input type="text" placeholder="00:00" name="_cw_class_edit[duration]" id="cw_class-edit-duration" value="<?php cw_class_single_the_duration(); ?>" class="cls-duree" />
    </p>
    <p>
      <label for="cw_class-edit-status"><?php esc_html_e('Restrict this cw_class to the selected attendees', 'cw_class'); ?>
        <input type="checkbox" name="_cw_class_edit[privacy]" id="cw_class-edit-privacy" <?php cw_class_single_the_privacy(); ?> value="1">
      </label>
    </p>

    <?php do_action('cw_class_edit_form_before_dates'); ?>

    <hr />

    <h4><?php esc_html_e('Attendees', 'cw_class'); ?></h4>

    <?php cw_class_single_the_dates('edit'); ?>

    <?php do_action('cw_class_edit_form_after_dates'); ?>

    <?php if (cw_class_single_can_report()) : ?>

      <p>
        <label for="cw_class-edit-report"><?php esc_html_e('Notes / Report', 'cw_class'); ?></label>
      <div class="cw_class-report-wrapper">
        <?php cw_class_single_edit_report(); ?>
      </div>
      </p>

    <?php endif; ?>

    <hr />

    <p>
      <label for="cw_class-custom-message"><?php esc_html_e('Send a custom message to attendees (restricted to once per day).', 'cw_class'); ?></label>
      <textarea name="_cw_class_edit[message]" id="cw_class-custom-message"></textarea>
    </p>

    <input type="hidden" value="<?php cw_class_single_the_id(); ?>" name="_cw_class_edit[id]" />
    <input type="hidden" value="<?php cw_class_single_the_action('edit'); ?>" name="_cw_class_edit[action]" />
    <?php wp_nonce_field('cw_class_update'); ?>

    <?php cw_class_single_the_submit('edit'); ?>
  </form>
<?php
}

/**
 * Single screen title
 *
 * @package CW Class
 * @subpackage Parts
 *
 * @since CW Class (1.0.0)
 */
function cw_class_single_title()
{
  cw_class_single_the_title();

  if (current_user_can('edit_cw_class', cw_class_single_get_the_id())) {
    bp_button(array(
      'id'                => 'edit-cw_class',
      'component'         => 'cw_class',
      'must_be_logged_in' => true,
      'block_self'        => false,
      'wrapper_id'        => 'cw_class-edit-btn',
      'wrapper_class'     => 'right',
      'link_class'        => 'edit-cw_class',
      'link_href'         => cw_class_single_get_edit_link(),
      'link_title'        => __('Edit', 'cw_class'),
      'link_text'         => __('Edit', 'cw_class')
    ));
  }
}

/**
 * Single screen content
 *
 * @package CW Class
 * @subpackage Parts
 *
 * @since CW Class (1.0.0)
 */
function cw_class_single_content()
{
  // Make sure embed url are processed
  add_filter('embed_post_id', 'cw_class_single_get_the_id');

?>
  <form action="<?php echo esc_url(cw_class_single_the_form_action()); ?>" method="post" id="cw_class-single-form" class="standard-form">

    <label for="cw_class-single-description"><?php esc_html_e('Description', 'cw_class'); ?></label>
    <div id="cw_class-single-description"><?php cw_class_single_the_description(); ?></div>

    <label for="cw_class-single-venue"><?php esc_html_e('Venue', 'cw_class'); ?></label>
    <div id="cw_class-single-venue"><?php cw_class_single_the_venue(); ?></div>

    <?php if (cw_class_single_has_type()) : ?>

      <label for="cw_class-single-type"><?php esc_html_e('Type', 'cw_class'); ?></label>
      <div id="cw_class-single-type"><?php cw_class_single_the_type(); ?></div>

    <?php endif; ?>

    <?php if (cw_class_single_date_set()) : ?>

      <label for="cw_class-single-date"><?php esc_html_e('Fixed to:', 'cw_class'); ?></label>
      <div id="cw_class-single-date"><?php cw_class_single_the_date(); ?></div>

    <?php endif; ?>

    <label for="cw_class-single-duration"><?php esc_html_e('Duration (hours)', 'cw_class'); ?></label>
    <div id="cw_class-single-duration"><?php cw_class_single_the_duration(); ?></div>

    <hr />

    <h4><?php esc_html_e('Attendees', 'cw_class'); ?></h4>

    <?php cw_class_single_the_dates('single'); ?>

    <?php if (cw_class_single_has_report()) : ?>

      <hr />

      <label for="cw_class-single-report"><?php esc_html_e('Notes/Report', 'cw_class'); ?></label>
      <div id="cw_class-single-report"><?php cw_class_single_the_report(); ?></div>

    <?php endif; ?>

    <input type="hidden" value="<?php cw_class_single_the_id(); ?>" name="_cw_class_prefs[id]" />
    <input type="hidden" value="<?php cw_class_single_the_action('single'); ?>" name="_cw_class_prefs[action]" />
    <?php wp_nonce_field('cw_class_prefs'); ?>

    <?php if (!cw_class_single_date_set()) cw_class_single_the_submit('single'); ?>
  </form>
<?php

  // Stop processing embeds
  remove_filter('embed_post_id', 'cw_class_single_get_the_id');
}
