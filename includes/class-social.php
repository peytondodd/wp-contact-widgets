<?php

namespace WPCW;

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

final class Social extends Base_Widget {

	/**
	 * Widget constructor
	 */
	public function __construct() {

		$widget_options = [
			'classname'   => 'wpcw-widget-social',
			'description' => __( 'Custom social links', 'contact-widgets' ),
		];

		parent::__construct(
			'wpcw_social',
			__( 'Social', 'contact-widgets' ),
			$widget_options
		);

	}

	/**
	 * Widget form fields
	 *
	 * @param array $instance
	 *
	 * @return string|void
	 */
	public function form( $instance ) {

		parent::form( $instance );

		$fields      = $this->get_fields( $instance );
		$title_field = array_shift( $fields );

		echo '<div class="wpcw-widget wpcw-widget-social">';

		echo '<div class="title">';

		// Title field
		$this->render_form_input( $title_field );

		echo '</div>';

		echo '<div class="icons">';

		foreach ( $fields as $key => $field ) {

			if ( ! isset( $field['social'] ) ) {

				continue;

			}

			printf(
				'<a href="#"
					class="%s"
					title="%s"
					data-key="%s"
					data-value="%s"
					data-select="%s"
					data-name="%s"
					data-id="%s"
					data-label="%s">
					<i class="fa fa-%s"></i>
				</a>',
				empty( $field['value'] ) ? 'inactive' : '',
				esc_attr( $field['label'] ),
				esc_attr( $key ),
				esc_attr( $field['default'] ),
				esc_attr( $field['select'] ),
				esc_attr( $field['name'] ),
				esc_attr( $field['id'] ),
				esc_attr( $field['label'] ),
				esc_attr( $field['icon'] )
			);

		}

		echo '</div>';

		echo '<div class="form">';

		$fields = $this->order_field( $fields );

		foreach ( $fields as $key => $field ) {

			$method = $field['form_callback'];

			if ( is_callable( [ $this, $method ] ) && ! empty( $field['value'] ) ) {

				$this->$method( $field );

			}

		}

		// Workaround customizer refresh @props @westonruter
		echo '<input class="customizer_update" type="hidden" value="">';

		echo '</div>'; // End form

		echo '<div class="default-fields">';

		// Template form for JS use
		$this->render_form_input( $this->field_defaults );

		echo '</div>'; // End default-fields

		echo '</div>'; // End wpcw-widget-social

	}

	/**
	 * Front-end display
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {

		$fields = $this->get_fields( $instance, [], true );

		if ( $this->is_widget_empty( $fields ) ) {

			return;

		}

		$suffix = SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_style( 'font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css', [], '4.5.0' );
		wp_enqueue_style( 'wpcw', \Contact_Widgets::$assets_url . "css/style{$suffix}.css", [], Plugin::$version );

		$this->before_widget( $args, $fields );

		$display_labels = ( 'yes' === $instance['labels']['value'] );

		foreach ( $fields as $field ) {

			if ( empty( $field['value'] ) || ! $field['show_front_end'] ) {

				continue;

			}

			$escape_callback = $field['escaper'];

			printf(
				'<li class="%s"><a href="%s" target="%s" title="%s"><span class="fa fa-2x fa-%s"></span>%s</a></li>',
				$display_labels ? 'has-label' : 'no-label',
				$escape_callback( $field['value'] ),
				esc_attr( $field['target'] ),
				sprintf( esc_attr_x( 'Visit %s on %s', '1. Title of website (e.g. My Cat Blog), 2. Name of social network (e.g. Facebook)', 'contact-widgets' ), get_bloginfo( 'name' ), $field['label'] ),
				esc_attr( $field['icon'] ),
				$display_labels ? esc_html( $field['label'] ) : ''
			);

		}

		$this->after_widget( $args, $fields );

	}

	/**
	 * Initialize fields for use on front-end of forms
	 *
	 * @param  array $instance
	 * @param  array $fields (optional)
	 * @param  bool  $ordered (optional)
	 *
	 * @return array
	 */
	protected function get_fields( array $instance, array $fields = [], $ordered = false ) {

		include 'social-networks.php';

		foreach ( $fields as $key => &$field ) {

			$default = [
				'sanitizer' => 'esc_url_raw',
				'escaper'   => 'esc_url',
				'select'    => '',
				'social'    => true,
				'target'    => '_blank',
			];

			$field = wp_parse_args( $field, $default );

		}

		$title = [
			'title' => [
				'label'       => __( 'Title:', 'contact-widgets' ),
				'description' => __( 'The title of widget. Leave empty for no title.', 'contact-widgets' ),
				'value'       => ! empty( $instance['title'] ) ? $instance['title'] : '',
				'sortable'    => false,
			],
		];

		// Prepend title field to the array
		$fields = $title + $fields;

		$fields['labels'] = [
			'label'          => __( 'Display labels?', 'contact-widgets' ),
			'class'          => '',
			'label_after'    => true,
			'type'           => 'checkbox',
			'sortable'       => false,
			'value'          => 'yes',
			'atts'           => $this->checked( 'yes', isset( $instance['labels']['value'] ) ? $instance['labels']['value'] : 'no' ),
			'show_front_end' => false,
		];

		$fields = parent::get_fields( $instance, $fields, $ordered );

		/**
		 * Filter the social fields
		 *
		 * @since 1.0.0
		 *
		 * @var array
		 */
		return (array) apply_filters( 'wpcw_widget_social_fields', $fields, $instance );

	}

	/**
	 * Print label and wrapper
	 *
	 * @param array $field
	 */
	protected function print_label( array $field ) {

		printf(
			'<label for="%s"><span class="fa fa-%s"></span> <span class="text">%s</span></label>',
			esc_attr( $field['id'] ),
			esc_attr( $field['icon'] ),
			esc_html( $field['label'] )
		);

	}

}
