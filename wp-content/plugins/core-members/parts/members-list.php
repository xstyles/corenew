<?php

/**
 * Get only User IDs which have the administrator role
 * @return array +
 */
function bp_get_only_administrators_ids() {

    $user_ids = get_transient( 'bp_only_administrators_ids' );

    if( false === $user_ids ) {

    	$args = array(
    		'role__in' => 'administrator',
    		'fields' => 'ID'
    	);
        $user_ids = get_users( $args );

        set_transient( 'bp_only_administrators_ids', $user_ids, 12 * HOUR_IN_SECONDS );

    }

    return $user_ids;   
    

}
<?php

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
<?php

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