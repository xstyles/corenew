<?php
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main OoohBoi SearchCop
 *
 * The main class that initiates and runs the plugin.
 *
 * @since 1.3.3
 */
class OoohBoi_SearchCop {

	/**
	 * Initialize 
	 *
	 * @since 1.3.3
	 *
	 * @access public
	 */
	public static function init() {

		add_action( 'elementor/element/search-form/search_content/before_section_end',  [ __CLASS__, 'ooohboi_searchcop_get_controls' ], 10, 2 );

    }
    
	public static function ooohboi_searchcop_get_controls( $element, $args ) {

		$element->add_control(
			'_ob_searchcop_plugin_title',
			[
				'label' => 'S E A R C H - C O P', 
				'type' => Controls_Manager::HEADING,
				'separator' => 'before', 
			]
        );

        // ------------------------------------------------------------------------- CONTROL: Yes 4 Search Cop !
		$element->add_control(
			'_ob_searchcop_srch_options',
			[
				'label' => __( 'Search Target', 'ooohboi-steroids' ),
				'type' => Controls_Manager::SELECT,
                'default' => 'all', 
                'frontend_available' => true,
				'options' => [
					'post' => __( 'Search Posts', 'ooohboi-steroids' ),
					'page' => __( 'Search Pages', 'ooohboi-steroids' ), 
					'product' => __( 'Search Products', 'ooohboi-steroids' ), 
					'all' => __( 'Search All', 'ooohboi-steroids' ), 
				],
			]
		);

	}

}