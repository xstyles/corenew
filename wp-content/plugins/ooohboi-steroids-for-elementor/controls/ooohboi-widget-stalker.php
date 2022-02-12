<?php
use Elementor\Controls_Manager;
use Elementor\Controls_Stack;
use Elementor\Element_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main OoohBoi_Kontrolz class
 *
 * The main class that initiates and runs the plugin.
 *
 * @since 1.6.0
 */
class OoohBoi_Widget_Stalker {

	/**
	 * Initialize 
	 *
	 * @since 1.6.0
	 *
	 * @access public
	 */
	public static function init() {

		add_action( 'elementor/element/common/_section_background/after_section_end',  [ __CLASS__, 'ooohboi_widget_stalker_controls' ] );
        add_action( 'elementor/element/after_add_attributes',  [ __CLASS__, 'ob_widget_stalker_add_attributes' ] ); 

    }

    public static function ob_widget_stalker_add_attributes( Element_Base $element ) {

        if( in_array( $element->get_name(), [ 'section', 'column' ] ) ) return;

        if( \Elementor\Plugin::instance()->editor->is_edit_mode() ) return;

		$settings = $element->get_settings_for_display();

		$use_widget_stalker = isset( $settings[ '_ob_widget_stalker_use' ] ) ? $settings[ '_ob_widget_stalker_use' ] : '';

        if ( 'yes' === $use_widget_stalker ) {
            $element->add_render_attribute( '_wrapper', 'class', 'ob-got-stalker' );
        }

    }

	public static function ooohboi_widget_stalker_controls( Element_Base $element ) {

		$element->start_controls_section(
			'_ob_widget_stalker',
			[
				'label' => 'W I D G E T - S T A L K E R', 
				'tab' => Controls_Manager::TAB_ADVANCED,  
			]
        );

        // ------------------------------------------------------------------------- CONTROL: Use Stalker
		$element->add_control(
			'_ob_widget_stalker_use',
			[
                'label' => __( 'Enable Widget Stalker?', 'ooohboi-steroids' ), 
				'description' => __( 'NOTE: Position of this widget is controlled by the parent Column. See the Layout tab, Layout panel > Breaking Bad, Widget Stalker settings.', 'ooohboi-steroids' ), 
				'separator' => 'before', 
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'ooohboi-steroids' ),
				'label_off' => __( 'No', 'ooohboi-steroids' ),
				'return_value' => 'yes',
				'default' => 'no',
				'frontend_available' => true,
			]
        );

        // ------------------------------------------------------------------------- CONTROL: Size Method
        $element->add_responsive_control(
            '_ob_ws_width_method',
            [
                'label' => __( 'Size Method', 'ooohboi-steroids' ),
                'description' => __( 'Use Flex or Units?', 'ooohboi-steroids' ),
                'type' => Controls_Manager::SELECT,
                'default' => 'flex',
                'options' => [
                    'flex' => __( 'Flex', 'ooohboi-steroids' ), 
                    'units' => __( 'Units', 'ooohboi-steroids' ), 
                ],
                'condition' => [
                    '_ob_widget_stalker_use' => 'yes', 
                ],
            ]
        );
		// --------------------------------------------------------------------------------------------- CONTROL Flex size
		$element->add_responsive_control(
			'_ob_ws_flex',
			[
				'label' => __( 'Flex', 'ooohboi-steroids' ),
				'type' => Controls_Manager::NUMBER, 
				'separator' => 'before', 
                'default' => 'unset', 
				'min' => 1,
				'selectors' => [
					'{{WRAPPER}}.elementor-widget.ob-got-stalker' => 'flex: {{VALUE}}; width: unset; min-width: 1px;', 
				],
                'device_args' => [
					Controls_Stack::RESPONSIVE_TABLET => [
                        'selectors' => [
                            '{{WRAPPER}}.elementor-widget.ob-got-stalker' => 'flex: {{VALUE}}; width: unset; min-width: 1px;', 
                        ],
						'condition' => [
                            '_ob_widget_stalker_use' => 'yes', 
							'_ob_ws_width_method_tablet' => 'flex', 
						],
					],
					Controls_Stack::RESPONSIVE_MOBILE => [
                        'selectors' => [
                            '{{WRAPPER}}.elementor-widget.ob-got-stalker' => 'flex: {{VALUE}}; width: unset; min-width: 1px;', 
                        ],
						'condition' => [
                            '_ob_widget_stalker_use' => 'yes', 
							'_ob_ws_width_method_mobile' => 'flex', 
						],
					],
				],
                'condition' => [
                    '_ob_widget_stalker_use' => 'yes', 
                    '_ob_ws_width_method' => 'flex', 
                ],
			]
		);
        // --------------------------------------------------------------------------------------------- CONTROL width
		$element->add_responsive_control(
            '_ob_ws_width',
            [
                'label' => __( 'Widget width', 'ooohboi-steroids' ),
                'type' => Controls_Manager::TEXT,
                'separator' => 'before',
                'label_block' => true,
				'description' => __( 'You can enter any acceptable CSS value, for example: 50em, 300px, 100%, calc(100% - 300px).', 'ooohboi-steroids' ), 
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget.ob-got-stalker' => 'width: {{VALUE}}; flex: unset;',
				],
                'device_args' => [
					Controls_Stack::RESPONSIVE_TABLET => [
                        'selectors' => [
                            '{{WRAPPER}}.elementor-widget.ob-got-stalker' => 'width: {{VALUE}}; flex: unset;',
                        ],
						'condition' => [
                            '_ob_widget_stalker_use' => 'yes', 
							'_ob_ws_width_method_tablet' => 'units', 
						],
					],
					Controls_Stack::RESPONSIVE_MOBILE => [
                        'selectors' => [
                            '{{WRAPPER}}.elementor-widget.ob-got-stalker' => 'width: {{VALUE}}; flex: unset;',
                        ],
						'condition' => [
                            '_ob_widget_stalker_use' => 'yes', 
							'_ob_ws_width_method_mobile' => 'units', 
						],
					],
				],
                'condition' => [
                    '_ob_widget_stalker_use' => 'yes', 
                    '_ob_ws_width_method' => 'units', 
                ],
            ]
		);
        // --------------------------------------------------------------------------------------------- CONTROL max width
		$element->add_responsive_control(
            '_ob_ws_max_width',
            [
                'label' => __( 'Max Width', 'ooohboi-steroids' ),
                'type' => Controls_Manager::TEXT,
                'separator' => 'before',
                'label_block' => true,
				'description' => __( 'You can enter any acceptable CSS value, for example: 50em, 300px, 100%, calc(100% - 300px).', 'ooohboi-steroids' ), 
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget.ob-got-stalker' => 'max-width: {{VALUE}};',
				],
                'condition' => [
                    '_ob_widget_stalker_use' => 'yes', 
                ],
            ]
		);
        // ------------------------------------------------------------------------- CONTROL: align self
        $element->add_responsive_control(
            '_ob_ws_align_self',
            [
                'label' => __( 'Align self', 'ooohboi-steroids' ),
                'description' => __( 'Align this widget vertically', 'ooohboi-steroids' ),
                'type' => Controls_Manager::SELECT,
                'default' => 'auto',
                'options' => [
                    'auto' => __( 'Auto', 'ooohboi-steroids' ), 
                    'baseline' => __( 'Baseline', 'ooohboi-steroids' ), 
                    'center' => __( 'Center', 'ooohboi-steroids' ), 
                    'end' => __( 'End', 'ooohboi-steroids' ), 
                ],
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget.ob-got-stalker' => 'align-self: {{VALUE}};',
				],
                'condition' => [
                    '_ob_widget_stalker_use' => 'yes', 
                ],
            ]
        );
        // --------------------------------------------------------------------------------------------- 1.6.3 CONTROL Widget order
		$element->add_responsive_control(
            '_ob_ws_widget_order',
            [
				'label' => __( 'Widget Order', 'ooohboi-steroids' ), 
				'description' => sprintf(
                    __( 'More info at %sMozilla%s.', 'ooohboi-steroids' ),
                    '<a 
href="https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_Flexible_Box_Layout/Ordering_Flex_Items#The_order_property" target="_blank">',
                    '</a>'
                ),
				'type' => Controls_Manager::NUMBER, 
				'style_transfer' => true, 
				'selectors' => [
					'{{WRAPPER}}.elementor-widget.ob-got-stalker' => '-webkit-box-ordinal-group: calc({{VALUE}} + 1 ); -ms-flex-order:{{VALUE}}; order: {{VALUE}};', 
                ],
                'condition' => [
                    '_ob_widget_stalker_use' => 'yes', 
                ],
			]
		);

        $element->end_controls_section(); // END SECTION / PANEL

    }

}