<?php

/**
 * To completely control the profile page layout by member type
 */
// add_filter( 'bp_template_hierarchy_members_single_item', function ( $templates ) {
// 	$member_types = bp_get_member_type( bp_displayed_user_id(), false );
// 	foreach ( $member_types as $member_type ) {
// 		array_unshift( $templates, "members/single/index-{$member_type}.php" );
// 	}
// 	// echo var_dump($templates);

// 	return $templates;
// } );

// add_action('wp_logout','ps_redirect_after_logout');
// function ps_redirect_after_logout(){
//          wp_redirect( site_url() );
//          exit();
// }

// add_filter( 'bp_login_redirect', 'bpdev_redirect_to_profile', 11, 3 );

// function bpdev_redirect_to_profile( $redirect_to_calculated, $redirect_url_specified, $user )
// {

//   if( empty( $redirect_to_calculated ) )
//     $redirect_to_calculated = admin_url();

//     //if the user is not site admin,redirect to his/her profile

// if( isset( $user->ID) && ! is_super_admin( $user->ID ) )
//     return bp_core_get_user_domain( $user->ID );
// else
//     return $redirect_to_calculated; /*if site admin or not logged in,do not do anything much*/

// }
 
/**
 * Removes tab items from Member Single
 */
//  function bpcodex_remove_member_notifications_tab() {
// // 	bp_core_remove_nav_item( 'home' );
// // 	bp_core_remove_nav_item( 'activity' );
// // 	bp_core_remove_nav_item( 'friends' );
//   bp_core_remove_nav_item( 'groups' );
// //	bp_core_remove_nav_item( 'foobar' );
//  }
// $member_types = bp_get_member_type( bp_displayed_user_id(), false );
// if($member_types == 'admin'){
//  add_action( 'bp_actions', 'bpcodex_remove_member_notifications_tab' );
// }

