<?php

/**
 * Functions of IdeaBoard's Default theme
 *
 * @package IdeaBoard
 * @subpackage IdeaBoard_Theme_Compat
 * @since IdeaBoard (r3732)
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/** Theme Setup ***************************************************************/

if ( !class_exists( 'IdeaBoard_Default' ) ) :

/**
 * Loads IdeaBoard Default Theme functionality
 *
 * This is not a real theme by WordPress standards, and is instead used as the
 * fallback for any WordPress theme that does not have IdeaBoard templates in it.
 *
 * To make your custom theme IdeaBoard compatible and customize the templates, you
 * can copy these files into your theme without needing to merge anything
 * together; IdeaBoard should safely handle the rest.
 *
 * See @link IdeaBoard_Theme_Compat() for more.
 *
 * @since IdeaBoard (r3732)
 *
 * @package IdeaBoard
 * @subpackage IdeaBoard_Theme_Compat
 */
class IdeaBoard_Default extends IdeaBoard_Theme_Compat {

	/** Functions *************************************************************/

	/**
	 * The main IdeaBoard (Default) Loader
	 *
	 * @since IdeaBoard (r3732)
	 *
	 * @uses IdeaBoard_Default::setup_globals()
	 * @uses IdeaBoard_Default::setup_actions()
	 */
	public function __construct( $properties = array() ) {

		parent::__construct( ideaboard_parse_args( $properties, array(
			'id'      => 'default',
			'name'    => __( 'IdeaBoard Default', 'ideaboard' ),
			'version' => ideaboard_get_version(),
			'dir'     => trailingslashit( ideaboard()->themes_dir . 'default' ),
			'url'     => trailingslashit( ideaboard()->themes_url . 'default' ),
		), 'default_theme' ) );

		$this->setup_actions();
	}

	/**
	 * Setup the theme hooks
	 *
	 * @since IdeaBoard (r3732)
	 * @access private
	 *
	 * @uses add_filter() To add various filters
	 * @uses add_action() To add various actions
	 */
	private function setup_actions() {

		/** Scripts ***********************************************************/

		add_action( 'ideaboard_enqueue_scripts',         array( $this, 'enqueue_styles'          ) ); // Enqueue theme CSS
		add_action( 'ideaboard_enqueue_scripts',         array( $this, 'enqueue_scripts'         ) ); // Enqueue theme JS
		add_filter( 'ideaboard_enqueue_scripts',         array( $this, 'localize_topic_script'   ) ); // Enqueue theme script localization
		add_action( 'ideaboard_ajax_favorite',           array( $this, 'ajax_favorite'           ) ); // Handles the topic ajax favorite/unfavorite
		add_action( 'ideaboard_ajax_subscription',       array( $this, 'ajax_subscription'       ) ); // Handles the topic ajax subscribe/unsubscribe
		add_action( 'ideaboard_ajax_forum_subscription', array( $this, 'ajax_forum_subscription' ) ); // Handles the forum ajax subscribe/unsubscribe

		/** Template Wrappers *************************************************/

		add_action( 'ideaboard_before_main_content',  array( $this, 'before_main_content'   ) ); // Top wrapper HTML
		add_action( 'ideaboard_after_main_content',   array( $this, 'after_main_content'    ) ); // Bottom wrapper HTML

		/** Override **********************************************************/

		do_action_ref_array( 'ideaboard_theme_compat_actions', array( &$this ) );
	}

	/**
	 * Inserts HTML at the top of the main content area to be compatible with
	 * the Twenty Twelve theme.
	 *
	 * @since IdeaBoard (r3732)
	 */
	public function before_main_content() {
	?>

		<div id="ideaboard-container">
			<div id="ideaboard-content" role="main">

	<?php
	}

	/**
	 * Inserts HTML at the bottom of the main content area to be compatible with
	 * the Twenty Twelve theme.
	 *
	 * @since IdeaBoard (r3732)
	 */
	public function after_main_content() {
	?>

			</div><!-- #ideaboard-content -->
		</div><!-- #ideaboard-container -->

	<?php
	}

	/**
	 * Load the theme CSS
	 *
	 * @since IdeaBoard (r3732)
	 *
	 * @uses wp_enqueue_style() To enqueue the styles
	 */
	public function enqueue_styles() {

		// Setup styles array
		$styles = array();

		// LTR
		$styles['ideaboard-default'] = array(
			'file'         => 'css/ideaboard.css',
			'dependencies' => array()
		);

		// RTL helpers
		if ( is_rtl() ) {
			$styles['ideaboard-default-rtl'] = array(
				'file'         => 'css/ideaboard-rtl.css',
				'dependencies' => array( 'ideaboard-default' )
			);
		}

		// Filter the scripts
		$styles = apply_filters( 'ideaboard_default_styles', $styles );

		// Enqueue the styles
		foreach ( $styles as $handle => $attributes ) {
			ideaboard_enqueue_style( $handle, $attributes['file'], $attributes['dependencies'], $this->version, 'screen' );
		}
	}

	/**
	 * Enqueue the required Javascript files
	 *
	 * @since IdeaBoard (r3732)
	 *
	 * @uses ideaboard_is_single_forum() To check if it's the forum page
	 * @uses ideaboard_is_single_topic() To check if it's the topic page
	 * @uses ideaboard_thread_replies() To check if threaded replies are enabled
	 * @uses ideaboard_is_single_user_edit() To check if it's the profile edit page
	 * @uses wp_enqueue_script() To enqueue the scripts
	 */
	public function enqueue_scripts() {

		// Setup scripts array
		$scripts = array();

		// Always pull in jQuery for TinyMCE shortcode usage
		if ( ideaboard_use_wp_editor() ) {
			$scripts['ideaboard-editor'] = array(
				'file'         => 'js/editor.js',
				'dependencies' => array( 'jquery' )
			);
		}

		// Forum-specific scripts
		if ( ideaboard_is_single_forum() ) {
			$scripts['ideaboard-forum'] = array(
				'file'         => 'js/forum.js',
				'dependencies' => array( 'jquery' )
			);
		}

		// Topic-specific scripts
		if ( ideaboard_is_single_topic() ) {

			// Topic favorite/unsubscribe
			$scripts['ideaboard-topic'] = array(
				'file'         => 'js/topic.js',
				'dependencies' => array( 'jquery' )
			);

			// Hierarchical replies
			if ( ideaboard_thread_replies() ) {
				$scripts['ideaboard-reply'] = array(
					'file'         => 'js/reply.js',
					'dependencies' => array( 'jquery' )
				);
			}
		}

		// User Profile edit
		if ( ideaboard_is_single_user_edit() ) {
			$scripts['ideaboard-user'] = array(
				'file'         => 'js/user.js',
				'dependencies' => array( 'user-query' )
			);
		}

		// Filter the scripts
		$scripts = apply_filters( 'ideaboard_default_scripts', $scripts );

		// Enqueue the scripts
		foreach ( $scripts as $handle => $attributes ) {
			ideaboard_enqueue_script( $handle, $attributes['file'], $attributes['dependencies'], $this->version, 'screen' );
		}
	}

	/**
	 * Load localizations for topic script
	 *
	 * These localizations require information that may not be loaded even by init.
	 *
	 * @since IdeaBoard (r3732)
	 *
	 * @uses ideaboard_is_single_forum() To check if it's the forum page
	 * @uses ideaboard_is_single_topic() To check if it's the topic page
	 * @uses is_user_logged_in() To check if user is logged in
	 * @uses ideaboard_get_current_user_id() To get the current user id
	 * @uses ideaboard_get_forum_id() To get the forum id
	 * @uses ideaboard_get_topic_id() To get the topic id
	 * @uses ideaboard_get_favorites_permalink() To get the favorites permalink
	 * @uses ideaboard_is_user_favorite() To check if the topic is in user's favorites
	 * @uses ideaboard_is_subscriptions_active() To check if the subscriptions are active
	 * @uses ideaboard_is_user_subscribed() To check if the user is subscribed to topic
	 * @uses ideaboard_get_topic_permalink() To get the topic permalink
	 * @uses wp_localize_script() To localize the script
	 */
	public function localize_topic_script() {

		// Single forum
		if ( ideaboard_is_single_forum() ) {
			wp_localize_script( 'ideaboard-forum', 'ideaboardForumJS', array(
				'ideaboard_ajaxurl'        => ideaboard_get_ajax_url(),
				'generic_ajax_error' => __( 'Something went wrong. Refresh your browser and try again.', 'ideaboard' ),
				'is_user_logged_in'  => is_user_logged_in(),
				'subs_nonce'         => wp_create_nonce( 'toggle-subscription_' . get_the_ID() )
			) );

		// Single topic
		} elseif ( ideaboard_is_single_topic() ) {
			wp_localize_script( 'ideaboard-topic', 'ideaboardTopicJS', array(
				'ideaboard_ajaxurl'        => ideaboard_get_ajax_url(),
				'generic_ajax_error' => __( 'Something went wrong. Refresh your browser and try again.', 'ideaboard' ),
				'is_user_logged_in'  => is_user_logged_in(),
				'fav_nonce'          => wp_create_nonce( 'toggle-favorite_' .     get_the_ID() ),
				'subs_nonce'         => wp_create_nonce( 'toggle-subscription_' . get_the_ID() )
			) );
		}
	}

	/**
	 * AJAX handler to Subscribe/Unsubscribe a user from a forum
	 *
	 * @since IdeaBoard (r5155)
	 *
	 * @uses ideaboard_is_subscriptions_active() To check if the subscriptions are active
	 * @uses ideaboard_is_user_logged_in() To check if user is logged in
	 * @uses ideaboard_get_current_user_id() To get the current user id
	 * @uses current_user_can() To check if the current user can edit the user
	 * @uses ideaboard_get_forum() To get the forum
	 * @uses wp_verify_nonce() To verify the nonce
	 * @uses ideaboard_is_user_subscribed() To check if the forum is in user's subscriptions
	 * @uses ideaboard_remove_user_subscriptions() To remove the forum from user's subscriptions
	 * @uses ideaboard_add_user_subscriptions() To add the forum from user's subscriptions
	 * @uses ideaboard_ajax_response() To return JSON
	 */
	public function ajax_forum_subscription() {

		// Bail if subscriptions are not active
		if ( ! ideaboard_is_subscriptions_active() ) {
			ideaboard_ajax_response( false, __( 'Subscriptions are no longer active.', 'ideaboard' ), 300 );
		}

		// Bail if user is not logged in
		if ( ! is_user_logged_in() ) {
			ideaboard_ajax_response( false, __( 'Please login to subscribe to this forum.', 'ideaboard' ), 301 );
		}

		// Get user and forum data
		$user_id = ideaboard_get_current_user_id();
		$id      = intval( $_POST['id'] );

		// Bail if user cannot add favorites for this user
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			ideaboard_ajax_response( false, __( 'You do not have permission to do this.', 'ideaboard' ), 302 );
		}

		// Get the forum
		$forum = ideaboard_get_forum( $id );

		// Bail if forum cannot be found
		if ( empty( $forum ) ) {
			ideaboard_ajax_response( false, __( 'The forum could not be found.', 'ideaboard' ), 303 );
		}

		// Bail if user did not take this action
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'toggle-subscription_' . $forum->ID ) ) {
			ideaboard_ajax_response( false, __( 'Are you sure you meant to do that?', 'ideaboard' ), 304 );
		}

		// Take action
		$status = ideaboard_is_user_subscribed( $user_id, $forum->ID ) ? ideaboard_remove_user_subscription( $user_id, $forum->ID ) : ideaboard_add_user_subscription( $user_id, $forum->ID );

		// Bail if action failed
		if ( empty( $status ) ) {
			ideaboard_ajax_response( false, __( 'The request was unsuccessful. Please try again.', 'ideaboard' ), 305 );
		}

		// Put subscription attributes in convenient array
		$attrs = array(
			'forum_id' => $forum->ID,
			'user_id'  => $user_id
		);

		// Action succeeded
		ideaboard_ajax_response( true, ideaboard_get_forum_subscription_link( $attrs, $user_id, false ), 200 );
	}

	/**
	 * AJAX handler to add or remove a topic from a user's favorites
	 *
	 * @since IdeaBoard (r3732)
	 *
	 * @uses ideaboard_is_favorites_active() To check if favorites are active
	 * @uses ideaboard_is_user_logged_in() To check if user is logged in
	 * @uses ideaboard_get_current_user_id() To get the current user id
	 * @uses current_user_can() To check if the current user can edit the user
	 * @uses ideaboard_get_topic() To get the topic
	 * @uses wp_verify_nonce() To verify the nonce & check the referer
	 * @uses ideaboard_is_user_favorite() To check if the topic is user's favorite
	 * @uses ideaboard_remove_user_favorite() To remove the topic from user's favorites
	 * @uses ideaboard_add_user_favorite() To add the topic from user's favorites
	 * @uses ideaboard_ajax_response() To return JSON
	 */
	public function ajax_favorite() {

		// Bail if favorites are not active
		if ( ! ideaboard_is_favorites_active() ) {
			ideaboard_ajax_response( false, __( 'Favorites are no longer active.', 'ideaboard' ), 300 );
		}

		// Bail if user is not logged in
		if ( ! is_user_logged_in() ) {
			ideaboard_ajax_response( false, __( 'Please login to make this topic a favorite.', 'ideaboard' ), 301 );
		}

		// Get user and topic data
		$user_id = ideaboard_get_current_user_id();
		$id      = !empty( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

		// Bail if user cannot add favorites for this user
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			ideaboard_ajax_response( false, __( 'You do not have permission to do this.', 'ideaboard' ), 302 );
		}

		// Get the topic
		$topic = ideaboard_get_topic( $id );

		// Bail if topic cannot be found
		if ( empty( $topic ) ) {
			ideaboard_ajax_response( false, __( 'The topic could not be found.', 'ideaboard' ), 303 );
		}

		// Bail if user did not take this action
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'toggle-favorite_' . $topic->ID ) ) {
			ideaboard_ajax_response( false, __( 'Are you sure you meant to do that?', 'ideaboard' ), 304 );
		}

		// Take action
		$status = ideaboard_is_user_favorite( $user_id, $topic->ID ) ? ideaboard_remove_user_favorite( $user_id, $topic->ID ) : ideaboard_add_user_favorite( $user_id, $topic->ID );

		// Bail if action failed
		if ( empty( $status ) ) {
			ideaboard_ajax_response( false, __( 'The request was unsuccessful. Please try again.', 'ideaboard' ), 305 );
		}

		// Put subscription attributes in convenient array
		$attrs = array(
			'topic_id' => $topic->ID,
			'user_id'  => $user_id
		);

		// Action succeeded
		ideaboard_ajax_response( true, ideaboard_get_user_favorites_link( $attrs, $user_id, false ), 200 );
	}

	/**
	 * AJAX handler to Subscribe/Unsubscribe a user from a topic
	 *
	 * @since IdeaBoard (r3732)
	 *
	 * @uses ideaboard_is_subscriptions_active() To check if the subscriptions are active
	 * @uses ideaboard_is_user_logged_in() To check if user is logged in
	 * @uses ideaboard_get_current_user_id() To get the current user id
	 * @uses current_user_can() To check if the current user can edit the user
	 * @uses ideaboard_get_topic() To get the topic
	 * @uses wp_verify_nonce() To verify the nonce
	 * @uses ideaboard_is_user_subscribed() To check if the topic is in user's subscriptions
	 * @uses ideaboard_remove_user_subscriptions() To remove the topic from user's subscriptions
	 * @uses ideaboard_add_user_subscriptions() To add the topic from user's subscriptions
	 * @uses ideaboard_ajax_response() To return JSON
	 */
	public function ajax_subscription() {

		// Bail if subscriptions are not active
		if ( ! ideaboard_is_subscriptions_active() ) {
			ideaboard_ajax_response( false, __( 'Subscriptions are no longer active.', 'ideaboard' ), 300 );
		}

		// Bail if user is not logged in
		if ( ! is_user_logged_in() ) {
			ideaboard_ajax_response( false, __( 'Please login to subscribe to this topic.', 'ideaboard' ), 301 );
		}

		// Get user and topic data
		$user_id = ideaboard_get_current_user_id();
		$id      = intval( $_POST['id'] );

		// Bail if user cannot add favorites for this user
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			ideaboard_ajax_response( false, __( 'You do not have permission to do this.', 'ideaboard' ), 302 );
		}

		// Get the topic
		$topic = ideaboard_get_topic( $id );

		// Bail if topic cannot be found
		if ( empty( $topic ) ) {
			ideaboard_ajax_response( false, __( 'The topic could not be found.', 'ideaboard' ), 303 );
		}

		// Bail if user did not take this action
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'toggle-subscription_' . $topic->ID ) ) {
			ideaboard_ajax_response( false, __( 'Are you sure you meant to do that?', 'ideaboard' ), 304 );
		}

		// Take action
		$status = ideaboard_is_user_subscribed( $user_id, $topic->ID ) ? ideaboard_remove_user_subscription( $user_id, $topic->ID ) : ideaboard_add_user_subscription( $user_id, $topic->ID );

		// Bail if action failed
		if ( empty( $status ) ) {
			ideaboard_ajax_response( false, __( 'The request was unsuccessful. Please try again.', 'ideaboard' ), 305 );
		}

		// Put subscription attributes in convenient array
		$attrs = array(
			'topic_id' => $topic->ID,
			'user_id'  => $user_id
		);

		// Action succeeded
		ideaboard_ajax_response( true, ideaboard_get_user_subscribe_link( $attrs, $user_id, false ), 200 );
	}
}
new IdeaBoard_Default();
endif;
