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
  // private $productIds = [65, 66, 67];

  // private $bothProduct = 67;

  // private $classTypes = [
  //   'Math' => 49,
  //   'ELA' => 50,
  // ];

  private $productIds = [410, 412, 414];

  private $bothProduct = 414;

  private $classTypes = [
    'Math' => 43,
    'ELA' => 44,
  ];

  /**
   * The single instance of this class
   * @var ScheduleClassForAllStudents
   */
  private static $instance = null;

  /**
   * Returns the single instance of the main plugin class.
   *
   * @return ScheduleClassForAllStudents
   */
  public static function instance()
  {
    if (is_null(self::$instance)) {
      self::$instance = new self;
    }

    return self::$instance;
  }


  public function __construct()
  {
    $filter_key = 'rendez_vous_save_args';
    add_filter('bp_after_' . $filter_key . '_parse_args', [$this, 'addAllPossibleAttendeesForClass'], 10, 1);
  }


  function addAllPossibleAttendeesForClass($meeting)
  {
    $attendees = $this->_getAllAttendeesByMemberType(['student', 'individual'], '41');

    $memberIds = [];

    foreach ($attendees as $attendee) {
      $memberIds[] = $attendee->id;
    }

    $meeting['attendees'] = $memberIds;

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
        // $members[] = bp_get_member_user_id();
        $members[] = bp_the_member();
      endwhile;
    }

    return $members;
  }

  private function _getAllAttendeesByMemberType($productId, $memberType)
  {
    $members = $this->_getAllMembersByType($memberType);

    $attendees = [];

    foreach ($members as $member) {
      $subscriptions = WCSG_Recipient_Management::get_recipient_subscriptions($member->get('ID'), 0, [
        'product_id' => $productId,
      ]);

      if (count($subscriptions)) {
        $attendees[] = $member;
      }
    }

    return $attendees;
  }
}

// add_action('plugins_loaded', array('ScheduleClassForAllStudents', 'instance'));
new ScheduleClassForAllStudents();
