<?php
/*
Plugin Name: WSUWP Maps
Version: 0.3.2
Plugin URI: https://web.wsu.edu/
Description: A shortcode to display an embedded map from map.wsu.edu.
Author: washingtonstateuniversity, jeremyfelt, jeremybass
Author URI: https://web.wsu.edu/
*/

class WSUWP_Maps {

	/**
	 * @var string Current version of this plugin.
	 */
	var $plugin_version = '0.3.2';

	/**
	 * Setup hooks.
	 */
	public function __construct() {
		add_shortcode( 'wsuwp_map', array( $this, 'display_map' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_map_script' ) );
	}

	/**
	 * Enqueue the mapping scripts and styles when a page with the proper shortcode tag is being displayed.
	 */
	public function enqueue_map_script() {
		if ( ! is_singular() ) {
			return;
		}

		$post = get_post();
		if ( isset( $post->post_content ) && has_shortcode( $post->post_content, 'wsuwp_map' ) ) {
			wp_enqueue_style( 'jquery-ui-smoothness', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/themes/smoothness/jquery-ui.min.css', array(), false );
			wp_enqueue_style( 'wsu-map-style', 'https://map.wsu.edu/content/dis/css/map.view.styles.css', array(), false );
		}
	}

	/**
	 * Handle the supplied shortcode to display a WSU map.
	 */
	public function display_map( $attributes ) {
		$defaults = array(
			'size' => 'medium',
			'id' => '',
			'width' => '',
			'height' => '',
			'map' => '',
		);
		$att = shortcode_atts( $defaults, $attributes );

		// The map attribute is used for newer style maps. ID is used for older style maps.
		if ( ! empty( $att['map'] ) ) {
			$map_path = sanitize_title_with_dashes( $attributes['map'] );

			if ( empty( $map_path ) ) {
				return '';
			}

			$content = '<div id="map-embed-' . $map_path . '"></div>';
			$content .= '<script>var map_view_scripts_block = true; var map_view_id = "map-embed-' . esc_js( $map_path ) .'";</script>';

			wp_enqueue_script( 'wsu-map-embed', esc_url( 'https://map.wsu.edu/embed/' . $map_path ), array( 'jquery' ), false, true );

			return $content;

		} elseif ( ! empty( $att['id'] ) ) {
			$map_url = 'https://map.wsu.edu/t/' . sanitize_key( $att['id'] );

			if ( 'small' === $att['size'] ) {
				$x = 214;
				$y = 161;
			} elseif ( 'medium' === $att['size'] ) {
				$x = 354;
				$y = 266;
			} elseif ( 'large' === $att['size'] ) {
				$x = 495;
				$y = 372;
			} elseif ( 'largest' === $att['size'] ) {
				$x = 731;
				$y = 549;
			} else {
				$x = 354;
				$y = 266;
			}

			if ( '' !== $att['width'] ) {
				$x = absint( $att['width'] );
			}

			if ( '' !== $att['height'] )  {
				$y = absint( $att['height'] );
			}

			$html = '<iframe width="' . $x . '" height="' . $y . '" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="' . esc_url( $map_url ) . '" ></iframe>';

			return $html;
		}

		return '<!-- no valid map parameters -->';
	}
}
new WSUWP_Maps();
