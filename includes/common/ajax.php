<?php

/**
 * IdeaBoard Common AJAX Functions
 *
 * Common AJAX functions are ones that are used to setup and/or use during
 * IdeaBoard specific, theme-side  AJAX requests.
 *
 * @package IdeaBoard
 * @subpackage Ajax
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Output the URL to use for theme-side IdeaBoard AJAX requests
 *
 * @since IdeaBoard (r4543)
 *
 * @uses ideaboard_get_ajax_url() To get the URL to use for AJAX requests
 */
function ideaboard_ajax_url() {
	echo esc_url( ideaboard_get_ajax_url() );
}
	/**
	 * Return the URL to use for theme-side IdeaBoard AJAX requests
	 *
	 * @since IdeaBoard (r4543)
	 *
	 * @global WP $wp
	 * @return string
	 */
	function ideaboard_get_ajax_url() {
		global $wp;

		$base_url = home_url( trailingslashit( $wp->request ), ( is_ssl() ? 'https' : 'http' ) );
		$ajaxurl  = add_query_arg( array( 'ideaboard-ajax' => 'true' ), $base_url );

		return apply_filters( 'ideaboard_get_ajax_url', $ajaxurl );
	}

/**
 * Is this a IdeaBoard AJAX request?
 *
 * @since IdeaBoard (r4543)
 *
 * @return bool Looking for ideaboard-ajax
 */
function ideaboard_is_ajax() {
	return (bool) ( ( isset( $_GET['ideaboard-ajax'] ) || isset( $_POST['ideaboard-ajax'] ) ) && ! empty( $_REQUEST['action'] ) );
}

/**
 * Hooked to the 'ideaboard_template_redirect' action, this is IdeaBoard's custom
 * theme-side ajax handler.
 *
 * @since IdeaBoard (r4543)
 *
 * @return If not a IdeaBoard ajax request
 */
function ideaboard_do_ajax() {

	// Bail if not an ajax request
	if ( ! ideaboard_is_ajax() )
		return;

	// Set WordPress core ajax constant
	define( 'DOING_AJAX', true );

	// Set the header content type
	@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );

	// Disable content sniffing in browsers that support it
	send_nosniff_header();

	// Perform custom IdeaBoard ajax
	do_action( 'ideaboard_ajax_' . $_REQUEST['action'] );

	// All done
	die( '0' );
}

/**
 * Helper method to return JSON response for the ajax calls
 *
 * @since IdeaBoard (r4542)
 *
 * @param bool $success
 * @param string $content
 * @param array $extras
 */
function ideaboard_ajax_response( $success = false, $content = '', $status = -1, $extras = array() ) {

	// Set status to 200 if setting response as successful
	if ( ( true === $success ) && ( -1 === $status ) )
		$status = 200;

	// Setup the response array
	$response = array(
		'success' => $success,
		'status'  => $status,
		'content' => $content
	);

	// Merge extra response parameters in
	if ( !empty( $extras ) && is_array( $extras ) ) {
		$response = array_merge( $response, $extras );
	}

	// Send back the JSON
	@header( 'Content-type: application/json' );
	echo json_encode( $response );
	die();
}
