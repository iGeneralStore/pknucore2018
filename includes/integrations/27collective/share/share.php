<?php

namespace CASE27;

class Sharer {

	protected static $_instance = null;

	public static function instance()
	{
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	public function __construct() {
		add_action( 'wp_head', [ $this, 'add_opengraph_tags' ], 5 );
	}

	public function add_opengraph_tags() {
    	global $post;

    	if ( is_singular( 'job_listing' ) && $listing = new \CASE27\Classes\Listing( $post ) ) {
    		$tags = [];

    		$tags['og:title'] = $listing->get_name();
    		$tags['og:url'] = $listing->get_link();
    		$tags['og:site_name'] = get_bloginfo();
    		$tags['og:type'] = 'profile';

    		if ( $tagline = $listing->get_field( 'job_tagline' ) ) {
    			$tags['og:description'] = $tagline;
    		} else {
    			$tags['og:description'] = $listing->get_field('job_description');
    		}

    		if ( $listing_logo = job_manager_get_resized_image( $listing->get_field( 'job_logo' ), 'large') ) {
    			$tags['og:image'] = esc_url( $listing_logo );
    		}

    		foreach ( $tags as $property => $content ) {
    			printf( "<meta property=\"%s\" content=\"%s\" />\n", esc_attr( $property ), esc_attr( $content ) );
    		}
		}
	}

	public function get_links( $options = [] )
	{
		$options = c27()->merge_options([
			'title' => false,
			'image' => false,
			'permalink' => false,
			'description' => false,
			], $options);

		return [
			$this->facebook($options),
			$this->twitter($options),
			$this->pinterest($options),
			$this->google_plus($options),
			$this->linkedin($options),
			$this->tumblr($options),
			$this->mail($options),
		];
	}

	public function facebook($options) {
		if (!$options['title'] || !$options['permalink']) return '';

		$url = 'http://www.facebook.com/share.php';
		$url .= '?u=' . urlencode($options['permalink']);
		$url .= '&title=' . urlencode($options['title']);

		if ($options['description']) $url .= '&description=' . urlencode($options['description']);
		if ($options['image']) $url .= '&picture=' . urlencode($options['image']);

		return "<a class=\"c27-open-popup-window\" href=\"{$url}\">" . __( 'Facebook', 'my-listing' ) . "</a>";
	}

	public function twitter($options) {
		if (!$options['title'] || !$options['permalink']) return '';

		$url = 'http://twitter.com/home';
		$url .= '?status=' . urlencode($options['title']);
		$url .= '+' . urlencode($options['permalink']);

		return "<a class=\"c27-open-popup-window\" href=\"{$url}\">" . __( 'Twitter', 'my-listing' ) . "</a>";
	}

	public function pinterest($options) {
		if (!$options['title'] || !$options['permalink'] || !$options['image']) return '';

		$url = 'https://pinterest.com/pin/create/button/';
		$url .= '?url=' . urlencode($options['permalink']);
		$url .= '&media=' . urlencode($options['image']);
		$url .= '&description=' . urlencode($options['title']);

		return "<a class=\"c27-open-popup-window\" href=\"{$url}\">" . __( 'Pinterest', 'my-listing' ) . "</a>";
	}

	public function google_plus($options) {
		if (!$options['permalink']) return '';

		$url = 'https://plus.google.com/share';
		$url .= '?url=' . urlencode($options['permalink']);

		return "<a class=\"c27-open-popup-window\" href=\"{$url}\">" . __( 'Google Plus', 'my-listing' ) . "</a>";
	}

	public function linkedin($options) {
		if (!$options['title'] || !$options['permalink']) return '';

		$url = 'http://www.linkedin.com/shareArticle?mini=true';
		$url .= '&url=' . urlencode($options['permalink']);
		$url .= '&title=' . urlencode($options['title']);

		return "<a class=\"c27-open-popup-window\" href=\"{$url}\">" . __( 'LinkedIn', 'my-listing' ) . "</a>";
	}

	public function tumblr($options) {
		if (!$options['title'] || !$options['permalink']) return '';

		$url = 'http://www.tumblr.com/share?v=3';
		$url .= '&u=' . urlencode($options['permalink']);
		$url .= '&t=' . urlencode($options['title']);

		return "<a class=\"c27-open-popup-window\" href=\"{$url}\">" . __( 'Tumblr', 'my-listing' ) . "</a>";
	}

	public function mail($options) {
		if (!$options['title'] || !$options['permalink']) return '';

		$url = 'mailto:';
		$url .= '?subject=' . urlencode($options['permalink']);
		$url .= '&body=' . $options['title'] . ' - ' . urlencode($options['permalink']);

		return "<a href=\"{$url}\">" . __( 'Mail', 'my-listing' ) . "</a>";
	}

	public function print_link( $link )
	{
		echo wp_kses( $link, [
			'a' => [
				'href' => [],
				'title' => [],
				'class' => [],
			]]);
	}
}

mylisting()->register( 'sharer', Sharer::instance() );
