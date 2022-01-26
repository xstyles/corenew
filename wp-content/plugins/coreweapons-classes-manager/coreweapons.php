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

// Your code starts here.

// class ScheduleClassForAllStudents
// {

  function initialize()
  {
  echo "test1";
    $filter_key = 'rendez_vous_save_args';
    add_filter('bp_after_' . $filter_key . '_parse_args' , 'addAllPossibleAttendeesForClass', 1, 1);
  }


  function addAllPossibleAttendeesForClass($meeting)
  {
    // echo "test1";
    // $memberType = bp_get_member_type(bp_current_user_id());
    $memberType = bp_get_current_member_type();
    $members = _getAllMembersByType($memberType);

    $meeting->attendees = $members;
    // return 'foobar';
    return $meeting;
  }

  function _getAllMembersByType($memberType)
  {
    $hasMembers = bp_has_members(bp_ajax_querystring($memberType));

    if (!$hasMembers) return [];

    $members = [];

    while (bp_members()) {
      $members[] = bp_the_member()->id;
    }

    return $members;
  }
// }

// new ScheduleClassForAllStudents();
initialize();
