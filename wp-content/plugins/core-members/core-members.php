<?php

/**
 * Plugin Name:     Core Members
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          YOUR NAME HERE
 * Author URI:      YOUR SITE HERE
 * Text Domain:     core-members
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Core_Members
 */

function corewpns_buddypress_tab()
{
    global $bp;
    $nav_item_name='';
    $sub_nav1_name='';
    $sub_nav2_name='';
    $member_type = bp_get_member_type( bp_displayed_user_id(), false );
    if($member_type[0] == 'admin'){
        $nav_item_name = 'Members';
        $sub_nav1_name = 'Parents';
        $sub_nav2_name = 'Students';
    } else{
        $nav_item_name = 'Foobar';
    }
    bp_core_new_nav_item(array(
        'name' => __($nav_item_name, 'core-members'),
        'slug' => 'recent-posts',
        'position' => 100,
        'screen_function' => 'corewpns_budypress_recent_posts',
        'show_for_displayed_user' => true,
        'default_subnav_slug' => 'sub-nav',
        'item_css_id' => 'corewpns_budypress_recent_posts'
    ));

    bp_core_new_subnav_item(array(
        'name' => __($sub_nav1_name, 'core-members'),
        'slug' => 'sub-nav',
        'parent_slug' => 'recent-posts',
        'parent_url' => getUrlWithSlug('recent-posts'),
        'position' => 10,
        'screen_function' => 'corewpns_sub_nav',
        'show_for_displayed_user' => true,
        'item_css_id' => 'corewpns_budypress_subnav'
    ));

    bp_core_new_subnav_item(array(
        'name' => __($sub_nav2_name, 'core-members'),
        'slug' => 'sub-nav2',
        'parent_slug' => 'recent-posts',
        'parent_url' => getUrlWithSlug('recent-posts'),
        'position' => 20,
        'screen_function' => 'corewpns_sub_nav',
        'show_for_displayed_user' => true,
        'item_css_id' => 'corewpns_budypress_subnav'
    ));
    bp_core_new_subnav_item(array(
        'name' => __('sub nav 3', 'core-members'),
        'slug' => 'sub-nav3',
        'parent_slug' => 'recent-posts',
        'parent_url' => getUrlWithSlug('recent-posts'),
        'position' => 30,
        'screen_function' => 'sub_nav_3_content',
        'show_for_displayed_user' => true,
        'item_css_id' => 'corewpns_budypress_subnav'
    ));
}

add_action('bp_setup_nav', 'corewpns_buddypress_tab', 1000);

add_action('bp_setup_sub-nav', 'corewpns_buddypress_tab', 1000);

function sub_nav_3_content(){
    

/**
 * Get only User IDs which have the administrator role
 * @return array +
 */
// function _bp_get_only_administrators_ids() {

//     $user_ids = get_transient( 'bp_only_administrators_ids' );

//     if( false === $user_ids ) {

//     	$args = array(
//     		'role__in' => 'administrator',
//     		'fields' => 'ID'
//     	);
//         $user_ids = get_users( $args );

//         set_transient( 'bp_only_administrators_ids', $user_ids, 12 * HOUR_IN_SECONDS );

//     }

//     return $user_ids;   
    

// }


/**
 * Add the Presenters tab to the Members Directory
 * @return void 
 */
function bp_my_administrators_tab() {
 
  
    $button_args = array(
        'id'         => 'administrators',
        'component'  => 'members',
        'link_text'  => sprintf( __( 'Administrators %s', 'buddypress' ), '<span>' . count( bp_get_only_administrators_ids() ) .'</span>' ),
        'link_title' => __( 'Administrators', 'buddypress' ),
        'link_class' => 'administrators no-ajax',
        'link_href'  => bp_get_members_directory_permalink() . '/?show=administrators',
        'wrapper'    => false,
        'block_self' => false,
        'must_be_logged_in' => false
    );  
     
    ?>
    <li <?php if( isset( $_GET['show'] ) && $_GET['show'] == 'administrators' ) { ?> class="current" <?php } ?>><?php echo bp_get_button( $button_args ); ?></li>
    <?php
}
add_action( 'bp_members_directory_member_types', 'bp_my_administrators_tab' );


add_filter( 'bp_after_core_get_users_parse_args', 'bp_filtering_only_administrators' );

/**
 * Showing only administrators
 * @return array
 */
function bp_filtering_only_administrators( $r ) {

    if( isset( $_GET['show'] ) && $_GET['show'] == 'administrators' ) {

        $user_ids = bp_get_only_administrators_ids();

        if( $user_ids ) {
             $r['include'] = $user_ids;
        }
       
    }  

    return $r;
} 
    bp_get_template_part('parts/index.php');
    add_action('bp_template_title', 'corewpns_buddypress_subnav_title');
    add_action('bp_template_content', 'sub_nav_3_content');
    bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
}
function corewpns_sub_nav(){
    // echo 'heelo';
    $member_type = bp_get_member_type( bp_displayed_user_id(), false );
    $subnav_slug = '';
    if ($member_type[0] == 'admin'){
        $subnav_slug = 'corewpns_buddypress_parents_subnav_content';
        // return $subnav_slug;
    } else{
        $subnav_slug = 'corewpns_buddypress_students_subnav_content';
        // return $subnav_slug;
    }
   
    add_action('bp_template_title', 'corewpns_buddypress_subnav_title');
    add_action('bp_template_content', $subnav_slug);
    bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
    
}

function corewpns_budypress_recent_posts()
{
    // add_action('bp_template_title', 'corewpns_buddypress_recent_posts_title');
    // add_action('bp_template_content', 'corewpns_buddypress_recent_posts_content');
    // bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
}

function corewpns_buddypress_recent_posts_title()
{
    echo 'foobarbaz';
}

function corewpns_buddypress_recent_posts_content()
{
    echo 'changed content';
}

function corewpns_buddypress_subnav_title()
{
    echo 'subnav1_foobarbaz';
}

function corewpns_buddypress_parents_subnav_content()
{
    echo 'subnav1_changed content for parents sub-tab';
}

function corewpns_buddypress_students_subnav_content()
{
    echo 'subnav_content for students sub-tab';
}

function getUrlWithSlug ($slug) {
    // Determine user to use
    if ( bp_displayed_user_domain() ) {
        $user_domain = bp_displayed_user_domain();
    } elseif ( bp_loggedin_user_domain() ) {
        $user_domain = bp_loggedin_user_domain();
    } else {
        return $slug;
    }

    return trailingslashit( $user_domain . $slug );
}

