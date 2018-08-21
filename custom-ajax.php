<?php

class dwul_user_register_ajax_call_back {

	private $options;


	/**
	 * Holds the values to be used in the fields callbacks
	 */

	/**
	 * Start up
	 */
	public function __construct() {

		add_action( 'wp_ajax_dwul_action_callback', array( $this, 'dwul_action_callback' ) );
		add_action( 'wp_ajax_nopriv_dwul_action_callback', array( $this, 'dwul_action_callback' ) );
		add_action( 'wp_ajax_dwul_enable_user_email', array( $this, 'dwul_enable_user_email' ) );
		add_action( 'wp_ajax_nopriv_dwul_enable_user_email', array( $this, 'dwul_enable_user_email' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'dwul_ajax_script' ) );
		add_action( 'wp_login', array( $this, 'dwul_disable_user_call_back' ), 10, 2 );
		add_filter( 'login_message', array( $this, 'dwul_disable_user_login_message' ) );
		add_filter( 'user_disable_filter', array( $this, 'filter_remove_users_disable' ), 10, 3 );
		add_filter( 'user_birthday_disable_filter', array( $this, 'filter_remove_users_birthday_disable' ), 10, 3 );
		add_filter( 'user_row_actions', [ $this, 'bp_core_admin_user_row_actions' ], 10, 2 );
	}

	/**
	 * Ajax Action
	 */
	public function dwul_action_callback($user_id=false) {

		global $wpdb;
		global $disable_user_id;
		$exitingarray    = array();
		$disable_user_id = $user_id ?: $_REQUEST['user_id'];
		$table_name      = $wpdb->prefix . 'dwul_disable_user_id';
		$wp_users        = $wpdb->users;

		if ( ! $user = get_userdata( $disable_user_id ) ) {
			$successresponse = "12";
		} elseif ( $user->roles[0] == 'administrator' ) {

			$successresponse = "11";

		} else {
			$query = $wpdb->prepare( "insert $table_name (user_id) select ID from $wp_users as u where ID not in " .
			                         "(select user_id from $table_name) and u.ID = %d;", $disable_user_id );
			if ( $wpdb->query( $query ) ) {
				$successresponse = "1";

			} else {
				$successresponse = "15";
			}

		}

		if ( $user_id ) {
			return $successresponse;
		} else {
			echo $successresponse;
			die();
		}
	}

	public function dwul_ajax_script() {

		wp_enqueue_script( 'user_custom_script', DWUL_PLUGIN_PATH . 'ajax.js' );
	}

	public function dwul_disable_user_call_back( $user_login, $user = null ) {
		global $wpdb;
		$array     = array();
		$usertable = $wpdb->prefix . 'dwul_disable_user_id';
		if ( ! $user ) {
			$user = get_user_by( 'login', $user_login );
		}
		if ( ! $user ) {
			// not logged in - definitely not disabled
			return;
		}

		$query = "SELECT user_id FROM $usertable ";

		$get = $wpdb->get_col( $query );

		foreach ( $get as $user_id ) {

			$result = get_userdata( $user_id );

			$array[] = $result->data->user_login;
		}


		// Is the use logging in disabled?
		if ( in_array( $user_login, $array ) ) {
			// Clear cookies, a.k.a log user out
			wp_clear_auth_cookie();

			// Build login URL and then redirect
			$login_url = site_url( 'login', 'login' );
			$login_url = add_query_arg( 'disabled', '1', $login_url );
			wp_redirect( $login_url );
			exit;
		}
	}

	public function dwul_disable_user_login_message( $message ) {
		// Show the error message if it seems to be a disabled user
		if ( isset( $_GET['disabled'] ) && $_GET['disabled'] == 1 ) {
			$message .= __( 'User Account Disable' );
		}

		return $message;
	}

	public function dwul_enable_user_email() {

		global $wpdb;
		$tblname        = $wpdb->prefix . dwul_disable_user_id;
		$activateuserid = $_REQUEST['activateuserid'];
		$delquery       = $wpdb->query( $wpdb->prepare( "DELETE FROM $tblname WHERE user_id = %d", $activateuserid ) );

		if ( $delquery ) {

			$response = "1";
		} else {

			$response = "20";

		}
		echo $response;
		die();

	}

	public function filter_remove_users_disable( $string, $members ) {
		$array  = $this->get_list_user_disable();
		$output = array();
		foreach ( $members as $member ) {
			if ( ! in_array( $member->ID, $array ) ) {
				$output[] = $member;
			}
		}

		return $output;
	}

	public function get_list_user_disable() {
		if ( $cache = wp_cache_get( 'list_user_disable', 'dwul' ) ) {
			return $cache;
		}

		global $wpdb;
		$array   = array();
		$tblname = $wpdb->prefix . 'dwul_disable_user_id';
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$tblname'" ) == $tblname ) {
			$query = "SELECT user_id FROM $tblname";
			$get   = $wpdb->get_col( $query );
			foreach ( $get as $user_id ) {
				$array[] = $user_id;
			}
		}
		wp_cache_set( 'list_user_disable', $array, 'dwul', WEEK_IN_SECONDS );

		return $array;
	}

	public function filter_remove_users_birthday_disable( $all_birthdays ) {
		$array  = $this->get_list_user_disable();
		$output = array();
		foreach ( $all_birthdays as $user_id => $birthday ) {
			if ( ! in_array( $user_id, $array ) ) {
				$output[ $user_id ] = $birthday;
			}
		}

		return $output;
	}

	/**
	 * @param $actions
	 * @param WP_User $user_object
	 *
	 * @return mixed
	 */
	function bp_core_admin_user_row_actions( $actions, $user_object ) {

		// Setup the $user_id variable from the current user object.
		$user_id = 0;
		if ( ! empty( $user_object->ID ) ) {
			$user_id = absint( $user_object->ID );
		}

		$disable_users = $this->get_list_user_disable();

		// Bail early if user cannot perform this action, or is looking at themselves.
		if ( current_user_can( 'edit_user', $user_id ) && ( bp_loggedin_user_id() !== $user_id ) ) {
			$isDisable = in_array( $user_id, $disable_users );
			$url       = sprintf( "javascript:disableUser_byId(%s,%b)", $user_id, ! $isDisable );
			$action    = sprintf( '<a href="%1$s">%2$s</a>', $url,
				esc_html__( $isDisable ? 'Habilitar' : 'Deshabilitar', 'buddypress' ) );

			$actions['disable_by_id'] = $action . '<div id="disable_by_id_spinner_loading_' . $user_id . '" style="display: none"><i class="fa fa-spinner fa-spin fa-3x fa-fw"></i></div>';


		}


		// Return new actions.
		return $actions;
	}

}

$wpdru_ajax_call_back = new dwul_user_register_ajax_call_back();
