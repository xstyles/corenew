<?php
add_filter( 'bp_template_hierarchy_members_single_item', function ( $templates ) {

$member_types = bp_get_member_type( bp_displayed_user_id(), false );
foreach ( $member_types as $member_type ) {
    array_unshift( $templates, "members/single/index-{$member_type}.php" );
}

return $templates;
} );

?>