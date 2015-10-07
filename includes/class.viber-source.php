<?php

if( !function_exists('add_action') ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}
class jeviber_Share_Viber extends Sharing_Source {
	var $shortname = 'viber';

	function __construct( $id, array $settings ) {
		parent::__construct( $id, $settings );

		if( 'official' == $this->button_style )
			$this->smart = true;
		else
			$this->smart = false;
	}

	function get_name() {
		return __( 'Viber', 'jetpack-viber' );
	}

	function has_custom_button_style() {
		return $this->smart;
	}

	private function guess_locale_from_lang( $lang ) {
		if( strpos( $lang, 'ja' ) === 0 )
			return 'ja';

		if( strpos( $lang, 'zh' ) === 0 )
			return 'zh-hant';

		return 'en';
	}

	function get_display( $post ) {
		$locale = $this->guess_locale_from_lang( get_locale() );
			if( $this->smart )
				return sprintf(
					'<div class="viber_button"><a href="viber://forward?text=%s: %s - %s%s" class="share-viber %s" title="%s"></a></div>',
					__('Look at this','jetpack-viber'),
					rawurlencode( $this->get_share_title( $post->ID ) ),
					rawurlencode( $this->get_share_url( $post->ID ) ),
					rawurlencode( '?utm_source=jetpack-sharing&utm_medium=viber&utm_campaign=mobile' ),
					esc_attr( $locale ),
					esc_attr__( 'Viber it!', 'jetpack-viber' )
				);
			else
				return $this->get_link( get_permalink( $post->ID ), _x( 'Viber', 'share to', 'jetpack-viber' ), __( 'Click to share on Viber', 'jetpack-viber' ), 'share=viber' );
	}

	function display_header() {
	}

	function display_footer() {
		$this->js_dialog( $this->shortname );
	}

	function process_request( $post, array $post_data ) {

		$url = add_query_arg( array(
		    'utm_source' => 'jetpack-sharing',
		    'utm_medium' => 'viber',
		    'utm_campaign' => 'mobile'
		), $this->get_share_url( $post->ID ) );

		$params = array(
		    'text' => __( 'Look at this', 'jetpack-viber' ) . ': ' . $this->get_share_title( $post->ID ) .' - ' . $url,
		);

		$viber_url = 'viber://forward?' . http_build_query( $params );

		// Record stats
		parent::process_request( $post, $post_data );

		// Redirect to Viber
		wp_redirect( $viber_url );
		die();
	}
}
