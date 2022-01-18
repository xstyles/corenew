<?php
/**
 * BuddyPress Single Members item Navigation
 *
 * @since 3.0.0
 * @version 3.1.0
 */
?>

<nav class="<?php bp_nouveau_single_item_nav_classes(); ?>" id="object-nav" role="navigation" aria-label="<?php esc_attr_e( 'Member menu', 'buddypress' ); ?>">

	<?php if ( bp_nouveau_has_nav( array( 'type' => 'primary' ) ) ) : ?>

		<ul>

			<?php
			while ( bp_nouveau_nav_items() ) :
				bp_nouveau_nav_item();
			?>

			<?php
				$slug = bp_nouveau()->current_nav_item->slug;
				$member_type = bp_get_member_type( bp_displayed_user_id(), false );

				// echo var_dump($member_type);
			?>
				<?php if ( $slug == 'groups') : ?>
					<?php if ($member_type[0] !== 'student') : ?>
					<li id="<?php bp_nouveau_nav_id(); ?>" class="<?php bp_nouveau_nav_classes(); ?>">
						<a href="<?php bp_nouveau_nav_link(); ?>" id="<?php bp_nouveau_nav_link_id(); ?>">
							<?php bp_nouveau_nav_link_text(); ?>

							<?php if ( bp_nouveau_nav_has_count() ) : ?>
								<span class="count"><?php bp_nouveau_nav_count(); ?></span>
							<?php endif; ?>
						</a>
					</li>
					<?php endif; ?>
				<?php else : ?>
				<li id="<?php bp_nouveau_nav_id(); ?>" class="<?php bp_nouveau_nav_classes(); ?>">
					<a href="<?php bp_nouveau_nav_link(); ?>" id="<?php bp_nouveau_nav_link_id(); ?>">
						<?php bp_nouveau_nav_link_text(); ?>

						<?php if ( bp_nouveau_nav_has_count() ) : ?>
							<span class="count"><?php bp_nouveau_nav_count(); ?></span>
						<?php endif; ?>
					</a>
				</li>
				<?php endif; ?>

			<?php endwhile; ?>

			<?php bp_nouveau_member_hook( '', 'options_nav' ); ?>

		</ul>

	<?php endif; ?>

</nav>
