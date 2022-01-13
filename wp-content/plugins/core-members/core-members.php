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

    bp_core_new_nav_item(array(
        'name' => __('foobar', 'core-members'),
        'slug' => 'recent-posts',
        'position' => 100,
        'screen_function' => 'corewpns_budypress_recent_posts',
        'show_for_displayed_user' => true,
        'default_subnav_slug' => 'sub-nav',
        'item_css_id' => 'corewpns_budypress_recent_posts'
    ));

    bp_core_new_subnav_item(array(
        'name' => __('new sub tab', 'core-members'),
        'slug' => 'sub-nav',
        'parent_slug' => 'recent-posts',
        'parent_url' => getUrlWithSlug('recent-posts'),
        'position' => 10,
        'screen_function' => 'corewpns_sub_nav',
        'show_for_displayed_user' => true,
        'item_css_id' => 'corewpns_budypress_subnav'
    ));

    bp_core_new_subnav_item(array(
        'name' => __('new sub tab2', 'core-members'),
        'slug' => 'sub-nav2',
        'parent_slug' => 'recent-posts',
        'parent_url' => getUrlWithSlug('recent-posts'),
        'position' => 10,
        'screen_function' => 'corewpns_sub_nav',
        'show_for_displayed_user' => true,
        'item_css_id' => 'corewpns_budypress_subnav'
    ));
}

add_action('bp_setup_nav', 'corewpns_buddypress_tab', 1000);

add_action('bp_setup_sub-nav', 'corewpns_buddypress_tab', 1000);

function corewpns_sub_nav(){
    // echo 'heelo';
    
    add_action('bp_template_title', 'corewpns_buddypress_subnav_title');
    add_action('bp_template_content', 'corewpns_buddypress_subnav_content');
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

function corewpns_buddypress_subnav_content()
{
    echo 'subnav1_changed content';
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

