<?php
use Elementor\Controls_Manager;
use Elementor\Controls_Stack;
use Elementor\Element_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main OoohBoi Hover Animator Class
 *
 * The main class that initiates and runs the plugin.
 *
 * @since 1.5.6
 */
class OoohBoi_Hover_Animator {

	/**
	 * Initialize 
	 *
	 * @since 1.5.6
	 *
	 * @access public
	 */
	public static function init() {

		add_action( 'elementor/element/common/_section_background/after_section_end',  [ __CLASS__, 'add_section' ] );
        add_action( 'elementor/element/after_add_attributes', [ __CLASS__, 'add_attributes' ] ); 
        add_action( 'elementor/element/column/section_advanced/before_section_end',  [ __CLASS__, 'manage_column_option' ], 10, 2 );
        /* allow hoveranimator for the column ? */
        add_action( 'elementor/frontend/column/before_render', function( Element_Base $element ) {

			if ( \Elementor\Plugin::instance()->editor->is_edit_mode() ) return;
			$settings = $element->get_settings_for_display();

			if ( isset( $settings[ '_ob_column_hoveranimator' ] ) && 'yes' === $settings[ '_ob_column_hoveranimator' ] ) {

				$element->add_render_attribute( '_wrapper', [
					'class' => 'ob-is-hoveranimator'
				] );

			}

		} );

    }

    public static function manage_column_option( $element, $args ) {

        //  create panel section
		$element->add_control(
			'_ob_hoveranimator_section_title',
			[
				'label' => 'H O V E R A N I M A T O R', 
				'type' => Controls_Manager::HEADING,
				'separator' => 'before', 
			]
        );

        // --------------------------------------------------------------------------------------------- CONTROL enable HOVERANIMATOR
        $element->add_control(
            '_ob_column_hoveranimator',
            [
                'label' => __( 'Enable HOVERANIMATOR?', 'ooohboi-steroids' ), 
                'separator' => 'before', 
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __( 'Yes', 'ooohboi-steroids' ),
                'label_off' => __( 'No', 'ooohboi-steroids' ),
                'return_value' => 'yes',
                'default' => 'no',
                'frontend_available' => true,
            ]
        );
        // --------------------------------------------------------------------------------------------- CONTROL Description - Faker !!!!!
        $element->add_control(
            '_ob_hoveranimator_column_fake_description',
            [
                'type' => Controls_Manager::RAW_HTML, 
                'raw' => __( 'With Hoveranimator you can animate any widget in this column on mouse-over event. Animation panel is available under the Advanced tab, per widget!', 'ooohboi-steroids' ), 
                'content_classes' => 'elementor-control-field-description', 
            ]
        );
        // ------------------------------------------------------------------------- CONTROL: Visibility
		$element->add_control(
			'_ob_hoveranimator_visibility',
			[
				'label' => __( 'Content Overflow', 'ooohboi-steroids' ),
				'description' => __( 'VISIBLE - makes visible all the elements outside this Column boundaries, HIDDEN - hides everything outside this Column boundaries.', 'ooohboi-steroids' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'hidden',
				'separator' => 'before', 
				'options' => [
					'visible' => __( 'Visible', 'ooohboi-steroids' ), 
					'hidden' => __( 'Hidden', 'ooohboi-steroids' ), 
				],
				'selectors' => [
					'{{WRAPPER}}.ob-is-hoveranimator' => 'overflow: {{VALUE}};',
				],
				'condition' => [
					'_ob_column_hoveranimator' => 'yes', 
				],
			]
		);

    }

	public static function add_attributes( Element_Base $element ) {

        if ( in_array( $element->get_name(), [ 'section', 'column' ] ) ) {
            return;
        }

        if ( \Elementor\Plugin::instance()->editor->is_edit_mode() ) {
            return;
        }

        $settings = $element->get_settings_for_display();

		$allow_hoveranimator  = isset( $settings[ '_ob_allow_hoveranimator' ] ) ? $settings[ '_ob_allow_hoveranimator' ] : '';

        if ( 'yes' === $allow_hoveranimator ) {
            $element->add_render_attribute( '_wrapper', 'class', 'ob-is-hoveranimal' ); 
        }

    }

    public static function add_section( Element_Base $element ) {

		$element->start_controls_section(
            '_ob_hoveranimator_panel',
            [
                'label' => 'H O V E R A N I M A T O R',
				'tab' => Controls_Manager::TAB_ADVANCED, 
            ]
        );
        
        // ------------------------------------------------------------------------- CONTROL: Yes 4 Hoveranimator !
		$element->add_control(
			'_ob_allow_hoveranimator',
			[
                'label' => __( 'Enable Hoveranimator', 'ooohboi-steroids' ), 
                'description' => __( 'That will allow you to animate this widget on mouse-over event of the parent column.', 'ooohboi-steroids' ), 
				'type' => Controls_Manager::SWITCHER, 
				'label_on' => __( 'Yes', 'ooohboi-steroids' ),
				'label_off' => __( 'No', 'ooohboi-steroids' ),
				'return_value' => 'yes',
                'default' => 'no', 
                'frontend_available' => true, 
			]
        );

        // --------------------------------------------------------------------------------------------- CONTROL OPACITY
		$element->add_control(
			'_ob_hoveranimator_opacity_popover',
			[
				'label' => __( 'Opacity', 'ooohboi-steroids' ), 
                'type' => Controls_Manager::POPOVER_TOGGLE, 
                'return_value' => 'yes', 
				'condition' => [
					'_ob_allow_hoveranimator' => 'yes',
                ],
			]
		);

        $element->start_popover();
		
		// --------------------------------------------------------------------------------------------- CONTROL OPACITY _normal
        $element->add_control(
            '_ob_hoveranimator_opacity',
            [
                'label' => __( 'Opacity Normal', 'ooohboi-steroids' ),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 1,
                ],
                'range' => [
                    'px' => [
                        'max' => 1,
                        'step' => 0.01,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}.ob-is-hoveranimal' => 'opacity: {{SIZE}};',
                ],
                'condition' => [
					'_ob_allow_hoveranimator' => 'yes',
				],
            ]
        );
        // --------------------------------------------------------------------------------------------- CONTROL OPACITY _hover 
        $element->add_control(
            '_ob_hoveranimator_opacity_hover',
            [
                'label' => __( 'Opacity Hover', 'ooohboi-steroids' ),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 1,
                ],
                'range' => [
                    'px' => [
                        'max' => 1,
                        'step' => 0.01,
                    ],
                ],
                'condition' => [
                    '_ob_allow_hoveranimator' => 'yes',
                ], 
                'frontend_available' => true, 
            ]
        );
        // --------------------------------------------------------------------------------------------- CONTROL OPACITY duration 
        $element->add_control(
            '_ob_hoveranimator_opacity_duration',
            [
				'label' => __( 'Duration', 'ooohboi-steroids' ),
				'separator' => 'before', 
				'type' => Controls_Manager::SLIDER,
				'render_type' => 'template', 
				'default' => [
					'size' => 250,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
					],
                ],
                'selectors' => [
                    '{{WRAPPER}}.ob-is-hoveranimal' => 'transition-duration: {{SIZE}}ms, {{_ob_hoveranimator_y_duration.SIZE}}ms, {{_ob_hoveranimator_x_duration.SIZE}}ms, {{_ob_hoveranimator_transform_duration.SIZE}}ms, {{_ob_hoveranimator_blur_duration.SIZE}}ms; 
                    transition-timing-function: {{_ob_hoveranimator_opacity_easing.VALUE}}, {{_ob_hoveranimator_y_easing.VALUE}}, {{_ob_hoveranimator_x_easing.VALUE}}, {{_ob_hoveranimator_transform_easing.VALUE}}, {{_ob_hoveranimator_blur_easing.VALUE}}; 
                    transition-delay: {{_ob_hoveranimator_opacity_delay.SIZE}}ms, {{_ob_hoveranimator_y_delay.SIZE}}ms, {{_ob_hoveranimator_x_delay.SIZE}}ms, {{_ob_hoveranimator_transform_delay.SIZE}}ms, {{_ob_hoveranimator_blur_delay.SIZE}}ms;', 
                ], 
				'condition' => [
                    '_ob_allow_hoveranimator' => 'yes',
                ], 
			]
        );
        // --------------------------------------------------------------------------------------------- CONTROL OPACITY delay 
        $element->add_control(
            '_ob_hoveranimator_opacity_delay',
            [
				'label' => __( 'Delay', 'ooohboi-steroids' ),
				'separator' => 'before', 
				'type' => Controls_Manager::SLIDER,
				'render_type' => 'template', 
				'default' => [
					'size' => 0,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
					],
                ],
                'selectors' => [
                    '{{WRAPPER}}.ob-is-hoveranimal' => 'transition-duration: {{_ob_hoveranimator_opacity_duration.SIZE}}ms, {{_ob_hoveranimator_y_duration.SIZE}}ms, {{_ob_hoveranimator_x_duration.SIZE}}ms, {{_ob_hoveranimator_transform_duration.SIZE}}ms, {{_ob_hoveranimator_blur_duration.SIZE}}ms; 
                    transition-timing-function: {{_ob_hoveranimator_opacity_easing.VALUE}}, {{_ob_hoveranimator_y_easing.VALUE}}, {{_ob_hoveranimator_x_easing.VALUE}}, {{_ob_hoveranimator_transform_easing.VALUE}}, {{_ob_hoveranimator_blur_easing.VALUE}}; 
                    transition-delay: {{SIZE}}ms, {{_ob_hoveranimator_y_delay.SIZE}}ms;, {{_ob_hoveranimator_x_delay.SIZE}}ms, {{_ob_hoveranimator_transform_delay.SIZE}}ms, {{_ob_hoveranimator_blur_delay.SIZE}}ms;', 
                ], 
				'condition' => [
                    '_ob_allow_hoveranimator' => 'yes',
                ], 
			]
        );
        // ------------------------------------------------------------------------- CONTROL: OPACITY easing
        $element->add_control(
			'_ob_hoveranimator_opacity_easing',
			[
				'label' => __( 'Easing', 'ooohboi-steroids' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'ease',
				'frontend_available' => true, 
                'separator' => 'before', 
				'options' => [
					'ease' => __( 'Default', 'ooohboi-steroids' ), 
					'ease-in' => __( 'Ease-in', 'ooohboi-steroids' ), 
                    'ease-out' => __( 'Ease-out', 'ooohboi-steroids' ), 
                    'ease-in-out' => __( 'Ease-in-out', 'ooohboi-steroids' ), 
                ],
                'selectors' => [
                    '{{WRAPPER}}.ob-is-hoveranimal' => 'transition-duration: {{_ob_hoveranimator_opacity_duration.SIZE}}ms, {{_ob_hoveranimator_y_duration.SIZE}}ms, {{_ob_hoveranimator_x_duration.SIZE}}ms, {{_ob_hoveranimator_transform_duration.SIZE}}ms, {{_ob_hoveranimator_blur_duration.SIZE}}ms; 
                    transition-timing-function: {{VALUE}}, {{_ob_hoveranimator_y_easing.VALUE}}, {{_ob_hoveranimator_x_easing.VALUE}}, {{_ob_hoveranimator_transform_easing.VALUE}}, {{_ob_hoveranimator_blur_easing.VALUE}}; 
                    transition-delay: {{_ob_hoveranimator_opacity_delay.SIZE}}ms, {{_ob_hoveranimator_y_delay.SIZE}}ms, {{_ob_hoveranimator_x_delay.SIZE}}ms, {{_ob_hoveranimator_transform_delay.SIZE}}ms, {{_ob_hoveranimator_blur_delay.SIZE}}ms;', 
                ], 
                'condition' => [
                    '_ob_allow_hoveranimator' => 'yes',
                ], 
			]
        );
        
        $element->end_popover(); // end opacity popover -----

        // --------------------------------------------------------------------------------------------- CONTROL Offset Top popover
		$element->add_control(
			'_ob_hoveranimator_y_popover',
			[
				'label' => __( 'Offset Top', 'ooohboi-steroids' ), 
				'type' => Controls_Manager::POPOVER_TOGGLE,
                'return_value' => 'yes', 
				'condition' => [
					'_ob_allow_hoveranimator' => 'yes',
                ],
			]
		);

        $element->start_popover();

        // --------------------------------------------------------------------------------------------- CONTROL OFFSET TOP
		$element->add_control(
			'_ob_hoveranimator_y',
			[
				'label' => __( 'Offset Top Normal', 'ooohboi-steroids' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => -500,
						'max' => 500,
					],
					'%' => [
						'min' => -500,
						'max' => 500,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 0,
				],
				'selectors' => [
					'{{WRAPPER}}.ob-is-hoveranimal' => 'top: {{SIZE}}{{UNIT}};', 
				],
				'condition' => [
                    '_ob_hoveranimator_y_alt' => '', 
                    '_ob_allow_hoveranimator' => 'yes',
                ],
			]
        );
        // --------------------------------------------------------------------------------------------- CONTROL CALC OFFSET TOP
        $element->add_control(
            '_ob_hoveranimator_y_alt',
            [
                'label' => __( 'Calc Offset Top Normal', 'ooohboi-steroids' ),
                'description' => __( 'Enter CSS calc value only! Like: 100% - 50px or 100% + 2em', 'ooohboi-steroids' ),
                'type' => Controls_Manager::TEXT,
                'selectors' => [
                    '{{WRAPPER}}.ob-is-hoveranimal' => 'top: calc({{VALUE}});',
                ],
                'condition' => [
                    '_ob_allow_hoveranimator' => 'yes',
                ],
            ]
        );
        // --------------------------------------------------------------------------------------------- CONTROL OFFSET TOP HOVER
        $element->add_control(
            '_ob_hoveranimator_y_hover',
            [
                'label' => __( 'Offset Top Hover', 'ooohboi-steroids' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range' => [
                    'px' => [
                        'min' => -500,
                        'max' => 500,
                    ],
                    '%' => [
                        'min' => -500,
                        'max' => 500,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 0,
                ],
                'condition' => [
                    '_ob_hoveranimator_y_hover_alt' => '', 
                    '_ob_allow_hoveranimator' => 'yes', 
                ], 
                'frontend_available' => true, 
            ]
        );
        // --------------------------------------------------------------------------------------------- CONTROL CALC OFFSET TOP HOVER
        $element->add_control(
            '_ob_hoveranimator_y_hover_alt',
            [
                'label' => __( 'Calc Offset Top Hover', 'ooohboi-steroids' ),
                'description' => __( 'Enter CSS calc value only! Like: 100% - 50px or 100% + 2em', 'ooohboi-steroids' ),
                'type' => Controls_Manager::TEXT,
                'condition' => [
                    '_ob_allow_hoveranimator' => 'yes',
                ],
                'frontend_available' => true, 
            ]
        );
        // --------------------------------------------------------------------------------------------- CONTROL Offset Top duration 
        $element->add_control(
            '_ob_hoveranimator_y_duration',
            [
                'label' => __( 'Duration', 'ooohboi-steroids' ),
                'separator' => 'before', 
                'type' => Controls_Manager::SLIDER,
                'render_type' => 'template', 
                'default' => [
                    'size' => 250,
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}.ob-is-hoveranimal' => 'transition-duration: {{_ob_hoveranimator_opacity_duration.SIZE}}ms, {{SIZE}}ms, {{_ob_hoveranimator_x_duration.SIZE}}ms, {{_ob_hoveranimator_transform_duration.SIZE}}ms, {{_ob_hoveranimator_blur_duration.SIZE}}ms; 
                    transition-timing-function: {{_ob_hoveranimator_opacity_easing.VALUE}}, {{_ob_hoveranimator_y_easing.VALUE}}, {{_ob_hoveranimator_x_easing.VALUE}}, {{_ob_hoveranimator_transform_easing.VALUE}}, {{_ob_hoveranimator_blur_easing.VALUE}}; 
                    transition-delay: {{_ob_hoveranimator_opacity_delay.SIZE}}ms, {{_ob_hoveranimator_y_delay.SIZE}}ms, {{_ob_hoveranimator_x_delay.SIZE}}ms, {{_ob_hoveranimator_transform_delay.SIZE}}ms, {{_ob_hoveranimator_blur_delay.SIZE}}ms;', 
                ],
                'condition' => [
                    '_ob_allow_hoveranimator' => 'yes',
                ], 
            ]
        );
        // --------------------------------------------------------------------------------------------- CONTROL Offset Top delay 
        $element->add_control(
            '_ob_hoveranimator_y_delay',
            [
                'label' => __( 'Delay', 'ooohboi-steroids' ),
                'separator' => 'before', 
                'type' => Controls_Manager::SLIDER,
                'render_type' => 'template', 
                'default' => [
                    'size' => 0,
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}.ob-is-hoveranimal' => 'transition-duration: {{_ob_hoveranimator_opacity_duration.SIZE}}ms, {{_ob_hoveranimator_y_duration.SIZE}}ms, {{_ob_hoveranimator_x_duration.SIZE}}ms, {{_ob_hoveranimator_transform_duration.SIZE}}ms, {{_ob_hoveranimator_blur_duration.SIZE}}ms; 
                    transition-timing-function: {{_ob_hoveranimator_opacity_easing.VALUE}}, {{_ob_hoveranimator_y_easing.VALUE}}, {{_ob_hoveranimator_x_easing.VALUE}}, {{_ob_hoveranimator_transform_easing.VALUE}}, {{_ob_hoveranimator_blur_easing.VALUE}}; 
                    transition-delay: {{_ob_hoveranimator_opacity_delay.SIZE}}ms, {{SIZE}}ms, {{_ob_hoveranimator_x_delay.SIZE}}ms, {{_ob_hoveranimator_transform_delay.SIZE}}ms, {{_ob_hoveranimator_blur_delay.SIZE}}ms;', 
                ],
                'condition' => [
                    '_ob_allow_hoveranimator' => 'yes',
                ], 
            ]
        );
        // ------------------------------------------------------------------------- CONTROL: Offset Top easing
        $element->add_control(
            '_ob_hoveranimator_y_easing',
            [
                'label' => __( 'Easing', 'ooohboi-steroids' ),
                'type' => Controls_Manager::SELECT,
                'default' => 'ease',
                'separator' => 'before', 
                'options' => [
                    'ease' => __( 'Default', 'ooohboi-steroids' ), 
                    'ease-in' => __( 'Ease-in', 'ooohboi-steroids' ), 
                    'ease-out' => __( 'Ease-out', 'ooohboi-steroids' ), 
                    'ease-in-out' => __( 'Ease-in-out', 'ooohboi-steroids' ), 
                ],
                'selectors' => [
                    '{{WRAPPER}}.ob-is-hoveranimal' => 'transition-duration: {{_ob_hoveranimator_opacity_duration.SIZE}}ms, {{_ob_hoveranimator_y_duration.SIZE}}ms, {{_ob_hoveranimator_x_duration.SIZE}}ms, {{_ob_hoveranimator_transform_duration.SIZE}}ms, {{_ob_hoveranimator_blur_duration.SIZE}}ms; 
                    transition-timing-function: {{_ob_hoveranimator_opacity_easing.VALUE}}, {{VALUE}}, {{_ob_hoveranimator_x_easing.VALUE}}ms, {{_ob_hoveranimator_transform_easing.VALUE}}, {{_ob_hoveranimator_blur_easing.VALUE}}; 
                    transition-delay: {{_ob_hoveranimator_opacity_delay.SIZE}}ms, {{_ob_hoveranimator_y_delay.SIZE}}ms, {{_ob_hoveranimator_x_delay.SIZE}}ms, {{_ob_hoveranimator_transform_delay.SIZE}}ms, {{_ob_hoveranimator_blur_delay.SIZE}}ms;', 
                ],
                'condition' => [
                    '_ob_allow_hoveranimator' => 'yes',
                ], 
            ]
        );

        $element->end_popover(); // end Offset Top popover -----

        // --------------------------------------------------------------------------------------------- CONTROL Offset Left popover
        $element->add_control(
            '_ob_hoveranimator_x_popover',
            [
                'label' => __( 'Offset Left', 'ooohboi-steroids' ), 
                'type' => Controls_Manager::POPOVER_TOGGLE,
                'return_value' => 'yes', 
                'condition' => [
                    '_ob_allow_hoveranimator' => 'yes',
                ],
            ]
        );

        $element->start_popover();
		
		// --------------------------------------------------------------------------------------------- CONTROL OFFSET LEFT
		$element->add_control(
			'_ob_hoveranimator_x',
			[
				'label' => __( 'Offset Left Normal', 'ooohboi-steroids' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => -500,
						'max' => 500,
					],
					'%' => [
						'min' => -500,
						'max' => 500,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 0,
				],
				'selectors' => [
					'{{WRAPPER}}.ob-is-hoveranimal' => 'left: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
                    '_ob_hoveranimator_x_alt' => '', 
                    '_ob_allow_hoveranimator' => 'yes',
                ],
			]
        );
        // --------------------------------------------------------------------------------------------- CONTROL CALC OFFSET TOP
        $element->add_control(
            '_ob_hoveranimator_x_alt',
            [
                'label' => __( 'Calc Offset Left Normal', 'ooohboi-steroids' ),
                'description' => __( 'Enter CSS calc value only! Like: 100% - 50px or 100% + 2em', 'ooohboi-steroids' ),
                'type' => Controls_Manager::TEXT,
                'selectors' => [
                    '{{WRAPPER}}.ob-is-hoveranimal' => 'left: calc({{VALUE}});',
                ],
                'condition' => [
                    '_ob_allow_hoveranimator' => 'yes',
                ],
            ]
        );
        // --------------------------------------------------------------------------------------------- CONTROL OFFSET LEFT _hover
        $element->add_control(
            '_ob_hoveranimator_x_hover',
            [
                'label' => __( 'Offset Left Hover', 'ooohboi-steroids' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range' => [
                    'px' => [
                        'min' => -500,
                        'max' => 500,
                    ],
                    '%' => [
                        'min' => -500,
                        'max' => 500,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 0,
                ],
                'condition' => [
                    '_ob_hoveranimator_x_hover_alt' => '', 
                    '_ob_allow_hoveranimator' => 'yes',
                ],
                'frontend_available' => true, 
            ]
        );
        // --------------------------------------------------------------------------------------------- CONTROL CALC OFFSET TOP HOVER
        $element->add_control(
            '_ob_hoveranimator_x_hover_alt',
            [
                'label' => __( 'Calc Offset Left Hover', 'ooohboi-steroids' ),
                'description' => __( 'Enter CSS calc value only! Like: 100% - 50px or 100% + 2em', 'ooohboi-steroids' ),
                'type' => Controls_Manager::TEXT,
                'condition' => [
                    '_ob_allow_hoveranimator' => 'yes',
                ],
                'frontend_available' => true, 
            ]
        );
        // --------------------------------------------------------------------------------------------- CONTROL Offset Left duration 
        $element->add_control(
            '_ob_hoveranimator_x_duration',
            [
                'label' => __( 'Duration', 'ooohboi-steroids' ),
                'separator' => 'before', 
                'type' => Controls_Manager::SLIDER,
                'render_type' => 'template', 
                'default' => [
                    'size' => 250,
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}.ob-is-hoveranimal' => 'transition-duration: {{_ob_hoveranimator_opacity_duration.SIZE}}ms, {{_ob_hoveranimator_y_duration.SIZE}}ms, {{SIZE}}ms, {{_ob_hoveranimator_transform_duration.SIZE}}ms, {{_ob_hoveranimator_blur_duration.SIZE}}ms; 
                    transition-timing-function: {{_ob_hoveranimator_opacity_easing.VALUE}}, {{_ob_hoveranimator_y_easing.VALUE}}, {{_ob_hoveranimator_x_easing.VALUE}}, {{_ob_hoveranimator_transform_easing.VALUE}}, {{_ob_hoveranimator_blur_easing.VALUE}}; 
                    transition-delay: {{_ob_hoveranimator_opacity_delay.SIZE}}ms, {{_ob_hoveranimator_y_delay.SIZE}}ms, {{_ob_hoveranimator_x_delay.SIZE}}ms, {{_ob_hoveranimator_transform_delay.SIZE}}ms, {{_ob_hoveranimator_blur_delay.SIZE}}ms;', 
                ],
                'condition' => [
                    '_ob_allow_hoveranimator' => 'yes',
                ], 
            ]
        );
        // --------------------------------------------------------------------------------------------- CONTROL Offset Left delay 
        $element->add_control(
            '_ob_hoveranimator_x_delay',
            [
                'label' => __( 'Delay', 'ooohboi-steroids' ),
                'separator' => 'before', 
                'type' => Controls_Manager::SLIDER,
                'render_type' => 'template', 
                'default' => [
                    'size' => 0,
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}.ob-is-hoveranimal' => 'transition-duration: {{_ob_hoveranimator_opacity_duration.SIZE}}ms, {{_ob_hoveranimator_y_duration.SIZE}}ms, {{_ob_hoveranimator_x_duration.SIZE}}ms, {{_ob_hoveranimator_transform_duration.SIZE}}ms, {{_ob_hoveranimator_blur_duration.SIZE}}ms; 
                    transition-timing-function: {{_ob_hoveranimator_opacity_easing.VALUE}}, {{_ob_hoveranimator_y_easing.VALUE}}, {{_ob_hoveranimator_x_easing.VALUE}}, {{_ob_hoveranimator_transform_easing.VALUE}}, {{_ob_hoveranimator_blur_easing.VALUE}}; 
                    transition-delay: {{_ob_hoveranimator_opacity_delay.SIZE}}ms, {{_ob_hoveranimator_y_delay.SIZE}}ms, {{SIZE}}ms, {{_ob_hoveranimator_transform_delay.SIZE}}ms, {{_ob_hoveranimator_blur_delay.SIZE}}ms;', 
                ],
                'condition' => [
                    '_ob_allow_hoveranimator' => 'yes',
                ], 
            ]
        );
        // ------------------------------------------------------------------------- CONTROL: Offset Left easing
        $element->add_control(
            '_ob_hoveranimator_x_easing',
            [
                'label' => __( 'Easing', 'ooohboi-steroids' ),
                'type' => Controls_Manager::SELECT,
                'default' => 'ease',
                'separator' => 'before', 
                'options' => [
                    'ease' => __( 'Default', 'ooohboi-steroids' ), 
                    'ease-in' => __( 'Ease-in', 'ooohboi-steroids' ), 
                    'ease-out' => __( 'Ease-out', 'ooohboi-steroids' ), 
                    'ease-in-out' => __( 'Ease-in-out', 'ooohboi-steroids' ), 
                ],
                'selectors' => [
                    '{{WRAPPER}}.ob-is-hoveranimal' => 'transition-duration: {{_ob_hoveranimator_opacity_duration.SIZE}}ms, {{_ob_hoveranimator_y_duration.SIZE}}ms, {{_ob_hoveranimator_x_duration.SIZE}}ms, {{_ob_hoveranimator_transform_duration.SIZE}}ms, {{_ob_hoveranimator_blur_duration.SIZE}}ms; 
                    transition-timing-function: {{_ob_hoveranimator_opacity_easing.VALUE}}, {{_ob_hoveranimator_y_easing.VALUE}}, {{VALUE}}, {{_ob_hoveranimator_transform_easing.VALUE}}, {{_ob_hoveranimator_blur_easing.VALUE}}; 
                    transition-delay: {{_ob_hoveranimator_opacity_delay.SIZE}}ms, {{_ob_hoveranimator_y_delay.SIZE}}ms, {{_ob_hoveranimator_x_delay.SIZE}}ms, {{_ob_hoveranimator_transform_delay.SIZE}}ms, {{_ob_hoveranimator_blur_delay.SIZE}}ms;', 
                ],
                'condition' => [
                    '_ob_allow_hoveranimator' => 'yes',
                ], 
            ]
        );

        $element->end_popover(); // end Offset Left popover -----

        // --------------------------------------------------------------------------------------------- CONTROL transforms popover
        $element->add_control(
            '_ob_hoveranimator_transforms_popover',
            [
                'label' => __( 'Transforms', 'ooohboi-steroids' ), 
                'type' => Controls_Manager::POPOVER_TOGGLE,
                'return_value' => 'yes', 
                'condition' => [
                    '_ob_allow_hoveranimator' => 'yes',
                ],
            ]
        );

        $element->start_popover();

        // --------------------------------------------------------------------------------------------- CONTROL SCALE X
        $element->add_control(
            '_ob_hoveranimator_scalex',
            [
                'label' => __( 'ScaleX Normal', 'ooohboi-steroids' ),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 10,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'size' => 1,
                ],
                'selectors' => [
                    '{{WRAPPER}}.ob-is-hoveranimal' => 'transform: scaleX({{SIZE}}) scaleY({{_ob_hoveranimator_scaley.SIZE}}) rotate({{_ob_hoveranimator_rot.SIZE}}deg);',
                ],
                'condition' => [
                    '_ob_allow_hoveranimator' => 'yes',
                ],
            ]
        );
        // --------------------------------------------------------------------------------------------- CONTROL SCALE X _hover
        $element->add_control(
            '_ob_hoveranimator_scalex_hover',
            [
                'label' => __( 'ScaleX Hover', 'ooohboi-steroids' ),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 10,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'size' => 1,
                ],
                'condition' => [
                    '_ob_allow_hoveranimator' => 'yes',
                ],
                'frontend_available' => true, 
            ]
        );
        // --------------------------------------------------------------------------------------------- CONTROL SCALE Y
        $element->add_control(
            '_ob_hoveranimator_scaley',
            [
                'label' => __( 'ScaleY Normal', 'ooohboi-steroids' ),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 10,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'size' => 1,
                ],
                'selectors' => [
                    '{{WRAPPER}}.ob-is-hoveranimal' => 'transform: scaleX({{_ob_hoveranimator_scalex.SIZE}}) scaleY({{SIZE}}) rotate({{_ob_hoveranimator_rot.SIZE}}deg);',
                ],
                'condition' => [
                    '_ob_allow_hoveranimator' => 'yes',
                ],
            ]
        );
        // --------------------------------------------------------------------------------------------- CONTROL SCALE Y _hover
        $element->add_control(
            '_ob_hoveranimator_scaley_hover',
            [
                'label' => __( 'ScaleY Hover', 'ooohboi-steroids' ),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 10,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'size' => 1,
                ],
                'condition' => [
                    '_ob_allow_hoveranimator' => 'yes',
                ],
                'frontend_available' => true, 
            ]
        );
        // --------------------------------------------------------------------------------------------- CONTROL ROTATION
        $element->add_control(
            '_ob_hoveranimator_rot',
            [
                'label' => __( 'Rotation Normal', 'ooohboi-steroids' ),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 360,
                        'step' => 5,
                    ],
                ],
                'default' => [
                    'size' => 0,
                ],
                'selectors' => [
                    '{{WRAPPER}}.ob-is-hoveranimal' => 'transform: scaleX({{_ob_hoveranimator_scalex.SIZE}}) scaleY({{_ob_hoveranimator_scaley.SIZE}}) rotate({{SIZE}}deg);',
                ],
                'condition' => [
                    '_ob_allow_hoveranimator' => 'yes',
                ],
            ]
        );
        // --------------------------------------------------------------------------------------------- CONTROL ROTATION _hover
        $element->add_control(
            '_ob_hoveranimator_rot_hover',
            [
                'label' => __( 'Rotation Hover', 'ooohboi-steroids' ),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 360,
                        'step' => 5,
                    ],
                ],
                'default' => [
                    'size' => 0,
                ],
                'condition' => [
                    '_ob_allow_hoveranimator' => 'yes',
                ],
                'frontend_available' => true, 
            ]
        );

        // --------------------------------------------------------------------------------------------- CONTROL Transforms duration 
        $element->add_control(
            '_ob_hoveranimator_transform_duration',
            [
                'label' => __( 'Duration', 'ooohboi-steroids' ),
                'separator' => 'before', 
                'type' => Controls_Manager::SLIDER,
                'render_type' => 'template', 
                'default' => [
                    'size' => 250,
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}.ob-is-hoveranimal' => 'transition-duration: {{_ob_hoveranimator_opacity_duration.SIZE}}ms, {{_ob_hoveranimator_y_duration.SIZE}}ms, {{_ob_hoveranimator_x_duration.SIZE}}ms, {{SIZE}}ms, {{_ob_hoveranimator_blur_duration.SIZE}}ms; 
                    transition-timing-function: {{_ob_hoveranimator_opacity_easing.VALUE}}, {{_ob_hoveranimator_y_easing.VALUE}}, {{_ob_hoveranimator_x_easing.VALUE}}, {{_ob_hoveranimator_transform_easing.VALUE}}, {{_ob_hoveranimator_blur_easing.VALUE}}; 
                    transition-delay: {{_ob_hoveranimator_opacity_delay.SIZE}}ms, {{_ob_hoveranimator_y_delay.SIZE}}ms, {{_ob_hoveranimator_x_delay.SIZE}}ms, {{_ob_hoveranimator_transform_delay.SIZE}}ms, {{_ob_hoveranimator_blur_delay.SIZE}}ms;', 
                ],
                'condition' => [
                    '_ob_allow_hoveranimator' => 'yes',
                ], 
            ]
        );
        // --------------------------------------------------------------------------------------------- CONTROL Transforms delay   
        $element->add_control(
            '_ob_hoveranimator_transform_delay',
            [
                'label' => __( 'Delay', 'ooohboi-steroids' ),
                'separator' => 'before', 
                'type' => Controls_Manager::SLIDER,
                'render_type' => 'template', 
                'default' => [
                    'size' => 0,
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}.ob-is-hoveranimal' => 'transition-duration: {{_ob_hoveranimator_opacity_duration.SIZE}}ms, {{_ob_hoveranimator_y_duration.SIZE}}ms, {{_ob_hoveranimator_x_duration.SIZE}}ms, {{_ob_hoveranimator_transform_duration.SIZE}}ms, {{_ob_hoveranimator_blur_duration.SIZE}}ms; 
                    transition-timing-function: {{_ob_hoveranimator_opacity_easing.VALUE}}, {{_ob_hoveranimator_y_easing.VALUE}}, {{_ob_hoveranimator_x_easing.VALUE}}, {{_ob_hoveranimator_transform_easing.VALUE}}, {{_ob_hoveranimator_blur_easing.VALUE}}; 
                    transition-delay: {{_ob_hoveranimator_opacity_delay.SIZE}}ms, {{_ob_hoveranimator_y_delay.SIZE}}ms, {{_ob_hoveranimator_x_delay.SIZE}}ms, {{SIZE}}ms, {{_ob_hoveranimator_blur_delay.SIZE}}ms;', 
                ],
                'condition' => [
                    '_ob_allow_hoveranimator' => 'yes',
                ], 
            ]
        );
        // ------------------------------------------------------------------------- CONTROL: Transforms easing
        $element->add_control(
            '_ob_hoveranimator_transform_easing',
            [
                'label' => __( 'Easing', 'ooohboi-steroids' ),
                'type' => Controls_Manager::SELECT,
                'default' => 'ease',
                'separator' => 'before', 
                'options' => [
                    'ease' => __( 'Default', 'ooohboi-steroids' ), 
                    'ease-in' => __( 'Ease-in', 'ooohboi-steroids' ), 
                    'ease-out' => __( 'Ease-out', 'ooohboi-steroids' ), 
                    'ease-in-out' => __( 'Ease-in-out', 'ooohboi-steroids' ), 
                ],
                'selectors' => [
                    '{{WRAPPER}}.ob-is-hoveranimal' => 'transition-duration: {{_ob_hoveranimator_opacity_duration.SIZE}}ms, {{_ob_hoveranimator_y_duration.SIZE}}ms, {{_ob_hoveranimator_x_duration.SIZE}}ms, {{_ob_hoveranimator_transform_duration.SIZE}}ms, {{_ob_hoveranimator_blur_duration.SIZE}}ms; 
                    transition-timing-function: {{_ob_hoveranimator_opacity_easing.VALUE}}, {{_ob_hoveranimator_y_easing.VALUE}}, {{_ob_hoveranimator_x_easing.VALUE}}, {{VALUE}}, {{_ob_hoveranimator_blur_easing.VALUE}}; 
                    transition-delay: {{_ob_hoveranimator_opacity_delay.SIZE}}ms, {{_ob_hoveranimator_y_delay.SIZE}}ms, {{_ob_hoveranimator_x_delay.SIZE}}ms, {{_ob_hoveranimator_transform_delay.SIZE}}ms, {{_ob_hoveranimator_blur_delay.SIZE}}ms;', 
                ],
                'condition' => [
                    '_ob_allow_hoveranimator' => 'yes',
                ], 
            ]
        );

        $element->end_popover(); // end transforms popover -----

        // --------------------------------------------------------------------------------------------- CONTROL Blur popover
        $element->add_control(
            '_ob_hoveranimator_blur_popover',
            [
                'label' => __( 'Blur', 'ooohboi-steroids' ), 
                'type' => Controls_Manager::POPOVER_TOGGLE,
                'return_value' => 'yes', 
                'condition' => [
                    '_ob_allow_hoveranimator' => 'yes',
                ],
            ]
        );

        $element->start_popover();
        
        // --------------------------------------------------------------------------------------------- CONTROL blur
        $element->add_control(
            '_ob_hoveranimator_blur',
            [
                'label' => __( 'Blur Normal', 'ooohboi-steroids' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 10,
                        'step' => 1,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 5,
                        'step' => 0.1, 
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 0,
                ],
                'selectors' => [
                    '{{WRAPPER}}.ob-is-hoveranimal' => 'filter: blur({{SIZE}}{{UNIT}});',
                ],
                'condition' => [
                    '_ob_allow_hoveranimator' => 'yes',
                ],
            ]
        );
        // --------------------------------------------------------------------------------------------- CONTROL blur _hover
        $element->add_control(
            '_ob_hoveranimator_blur_hover',
            [
                'label' => __( 'Blur Hover', 'ooohboi-steroids' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 10, 
                        'step' => 1,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 5, 
                        'step' => 0.1, 
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 0,
                ],
                'condition' => [
                    '_ob_allow_hoveranimator' => 'yes',
                ],
                'frontend_available' => true, 
            ]
        );
        // --------------------------------------------------------------------------------------------- CONTROL Blur duration 
        $element->add_control(
            '_ob_hoveranimator_blur_duration',
            [
                'label' => __( 'Duration', 'ooohboi-steroids' ),
                'separator' => 'before', 
                'type' => Controls_Manager::SLIDER,
                'render_type' => 'template', 
                'default' => [
                    'size' => 250,
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}.ob-is-hoveranimal' => 'transition-duration: {{_ob_hoveranimator_opacity_duration.SIZE}}ms, {{_ob_hoveranimator_y_duration.SIZE}}ms, {{_ob_hoveranimator_x_duration.SIZE}}ms, {{_ob_hoveranimator_transform_duration.SIZE}}ms, {{SIZE}}ms; 
                    transition-timing-function: {{_ob_hoveranimator_opacity_easing.VALUE}}, {{_ob_hoveranimator_y_easing.VALUE}}, {{_ob_hoveranimator_x_easing.VALUE}}, {{_ob_hoveranimator_transform_easing.VALUE}}, {{_ob_hoveranimator_blur_easing.VALUE}}; 
                    transition-delay: {{_ob_hoveranimator_opacity_delay.SIZE}}ms, {{_ob_hoveranimator_y_delay.SIZE}}ms, {{_ob_hoveranimator_x_delay.SIZE}}ms, {{_ob_hoveranimator_transform_delay.SIZE}}ms, {{_ob_hoveranimator_blur_delay.SIZE}}ms;', 
                ],
                'condition' => [
                    '_ob_allow_hoveranimator' => 'yes',
                ], 
            ]
        );
        // --------------------------------------------------------------------------------------------- CONTROL Blur delay 
        $element->add_control(
            '_ob_hoveranimator_blur_delay',
            [
                'label' => __( 'Delay', 'ooohboi-steroids' ),
                'separator' => 'before', 
                'type' => Controls_Manager::SLIDER,
                'render_type' => 'template', 
                'default' => [
                    'size' => 0,
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}.ob-is-hoveranimal' => 'transition-duration: {{_ob_hoveranimator_opacity_duration.SIZE}}ms, {{_ob_hoveranimator_y_duration.SIZE}}ms, {{_ob_hoveranimator_x_duration.SIZE}}ms, {{_ob_hoveranimator_transform_duration.SIZE}}ms, {{_ob_hoveranimator_blur_duration.SIZE}}ms; 
                    transition-timing-function: {{_ob_hoveranimator_opacity_easing.VALUE}}, {{_ob_hoveranimator_y_easing.VALUE}}, {{_ob_hoveranimator_x_easing.VALUE}}, {{_ob_hoveranimator_transform_easing.VALUE}}, {{_ob_hoveranimator_blur_easing.VALUE}}; 
                    transition-delay: {{_ob_hoveranimator_opacity_delay.SIZE}}ms, {{_ob_hoveranimator_y_delay.SIZE}}ms, {{_ob_hoveranimator_x_delay.SIZE}}ms, {{_ob_hoveranimator_transform_delay.SIZE}}ms, {{SIZE}}ms;', 
                ],
                'condition' => [
                    '_ob_allow_hoveranimator' => 'yes',
                ], 
            ]
        );
        // ------------------------------------------------------------------------- CONTROL: Blur easing
        $element->add_control(
            '_ob_hoveranimator_blur_easing',
            [
                'label' => __( 'Easing', 'ooohboi-steroids' ),
                'type' => Controls_Manager::SELECT,
                'default' => 'ease',
                'separator' => 'before', 
                'options' => [
                    'ease' => __( 'Default', 'ooohboi-steroids' ), 
                    'ease-in' => __( 'Ease-in', 'ooohboi-steroids' ), 
                    'ease-out' => __( 'Ease-out', 'ooohboi-steroids' ), 
                    'ease-in-out' => __( 'Ease-in-out', 'ooohboi-steroids' ), 
                ],
                'selectors' => [
                    '{{WRAPPER}}.ob-is-hoveranimal' => 'transition-duration: {{_ob_hoveranimator_opacity_duration.SIZE}}ms, {{_ob_hoveranimator_y_duration.SIZE}}ms, {{_ob_hoveranimator_x_duration.SIZE}}ms, {{_ob_hoveranimator_transform_duration.SIZE}}ms, {{_ob_hoveranimator_blur_duration.SIZE}}ms; 
                    transition-timing-function: {{_ob_hoveranimator_opacity_easing.VALUE}}, {{_ob_hoveranimator_y_easing.VALUE}}, {{_ob_hoveranimator_x_easing.VALUE}}, {{_ob_hoveranimator_transform_easing.VALUE}}, {{VALUE}}; 
                    transition-delay: {{_ob_hoveranimator_opacity_delay.SIZE}}ms, {{_ob_hoveranimator_y_delay.SIZE}}ms, {{_ob_hoveranimator_x_delay.SIZE}}ms, {{_ob_hoveranimator_transform_delay.SIZE}}ms, {{_ob_hoveranimator_blur_delay.SIZE}}ms;', 
                ],

                'condition' => [
                    '_ob_allow_hoveranimator' => 'yes',
                ], 
            ]
        );

        $element->end_popover(); // end Blur popover -----

		$element->end_controls_section(); // END SECTION / PANEL

	}

}