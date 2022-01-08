<?php

/**
 * Core Weapons - Admin Dashboard
 *
 * @since   1.0.0
 * @version 3.0.0
 */
get_header();
?>

<?php

$user_id = bp_displayed_user_id();
$current_user = wp_get_current_user();
?>
<div class="top-page-navbar">

    <section class="top-page-menu clearfix">

        <!-- <h3 class="menu-title float-left">Profile</h3> -->
        <?php 
        // if (is_user_logged_in() && ($user_id == $current_user)) : return $current_user;
        ?>
        <!-- <h4>User: <?php // $current_user_id ?> -->

        <?php
        bp_get_template_part('members/single/header');
        bp_get_template_part('members/single/parts/item-nav');
        bp_get_template_part('members/single/parts/item-subnav');
        ?>
    </section>
</div>
<!-- <h3 id="edit-athlete-profile-button" class="menu-title float-right">Edit Profile</h3> -->

<?php // endif; ?>


<?php
get_footer();
