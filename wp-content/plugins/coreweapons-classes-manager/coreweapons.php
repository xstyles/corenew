<?php

/**
 * Plugin Name:     Coreweapons Classes Manager
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     Schedule and attend online education classes via video-conference (Google Meet)
 * Author:          VGo Solutions
 * Author URI:      https://vgosolutions.com
 * Text Domain:     coreweapons
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Coreweapons
 */

class ScheduleClassForAllStudents
{
  /**
   * The single instance of this class
   * @var ScheduleClassForAllStudents
   */
  private static $instance = null;

  /**
   * Returns the single instance of the main plugin class.
   *
   * @return WCS_Gifting_Product_Restrictions
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
    $filter_key = 'rendez_vous_save_args';
    add_filter('bp_after_' . $filter_key . '_parse_args', [$this, 'addAllPossibleAttendeesForClass'], 10, 1);
  }


  function addAllPossibleAttendeesForClass($meeting)
  {
    $attendees = $this->_getAllMembersByType(['student', 'individual']);

    $meeting['attendees'] = $attendees;

    return $meeting;
  }

  /**
   * Private functions
   */

  private function _getAllMembersByType($memberType)
  {
    $members = [];

    if (bp_has_members(['member_type' => $memberType])) {
      while (bp_members()) : bp_the_member();
        $members[] = bp_get_member_user_id();
      endwhile;
    }

    return $members;
  }
}

/**
 * Transformers, roll out!
 */
add_action('plugins_loaded', array('ScheduleClassForAllStudents', 'instance'));
