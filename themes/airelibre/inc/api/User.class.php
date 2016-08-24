<?php

class User{

	private $wpdb, $table_prefix;
	public $attrs;

	/*
	 * @param $attrs Array
	 * params
	 */
	public function __construct($attrs = array()){

		global $wpdb, $table_prefix;
		$this->wpdb = &$wpdb;
		$this->attrs = array();
		
	}

	/*
	 * Create new user
	 * TO DO: Create user object from login and fill attributes
	 */
	public function create($nombre, $password, $email, $attrs = array()){

		$response =  wp_create_user(
			$nombre,
			$password,
			$email
		);

		$first_name = (isset($attrs['name'])) 		? $attrs['name'] 	  : "" ;
		$last_name 	= (isset($attrs['last_name'])) 	? $attrs['last_name'] : "" ;

		$display_name = ($first_name == "" AND $last_name == "") ? $nombre : $first_name." ".$last_name;
		
		if(isset($attrs['fbId']))
			update_user_meta($response, 'fbId', $attrs['fbId']);
		if(isset($attrs['gpId']))
			update_user_meta($response, 'gpId', $attrs['gpId']);
		if(isset($attrs['avatar']))
			update_user_meta( $response, 'foto_user', $attrs['avatar'] );
		if(isset($attrs['bio']))
			update_user_meta( $response, 'user_3dbio', $attrs['bio'] );
		
		$userdata = array(
							"ID" 			=> $response,
							"first_name" 	=> $first_name,
							"last_name" 	=> $last_name,
							"display_name" 	=> $display_name,
							"nickname"		=> $display_name,
							"role" 			=> "dedalo_user"
						);

		$update = wp_update_user( $userdata );

		return $response;
	}

	/*
	 * Get username
	 */
	public function _username_exists($username){
		$user = get_user_by( 'login', $username );
		if(!$user) return FALSE;
		if($user_id 	= 	username_exists( $username )) return $user_id;
	}

	
	/*
	 * Check if user exists by mail or twitter account
	 */
	public function check_if_user_exists($username, $email) {

		//Check for twitter meta, email or username
		$user = get_user_by( 'login', $username );
		if(!$user) return FALSE;
		$found_google	= 	get_user_meta($user->ID, 'gpId', TRUE);
		$found_facebook = 	get_user_meta($user->ID, 'fbId', TRUE);
		$found_email 	=	get_user_by( 'email', $email );
		
		if($user_id 	= 	username_exists( $username )) return $user_id;
		if($found_google) 	return $found_google;
		if($found_facebook) return $found_facebook;
		if($found_email) 	return $found_email;
		return FALSE;
	}

	/*
	 * Create user account if the username isn't taken
	 * @param $username
	 * @param $email
	 * @param $attrs (IMPORTANT: If account is created the 'native' way the password will be sent here as an attribute named password)
	 * @param $autologin Bool
	 * @return $success JSON encoded success, if user was created response contains also an object with the user info
	 * NOTE: If no password is passed the account may become inaccessible if created outside social login methods
	 */
	public function create_if__notExists($username, $email, $attrs = array(), $autologin = TRUE){
		
		$found = $this->check_if_user_exists($username, $email);
		
		if(!$found){
			$password = isset($attrs['password']) ? $attrs['password'] : wp_generate_password();
			$user_id = $this->create($username,  $password, $email, $attrs);
			$data_created = array('username' => $username, 'user_id' => $user_id);

			if($autologin){
				$this->login_if__Exists($username);
				return;
			}
			return (!is_wp_error($user_id)) ? $data_created : FALSE;
		}
		// if($autologin){
		// 	$this->login_if__Exists($username);
		// 	return;
		// }else{
		// 	wp_send_json_success($data_created);
		// }
		wp_send_json_error(array('error_message' => 'Couldn\'t create user', 'found_id' => $found));
		exit;
	}

	public function login_if__Exists($username){
		
		$user = get_user_by('login', $username);
		$this->pseudo_login($user->ID);
	}

	/*
	 * Set the username as metadata if provider is Twitter
	 * 
	 */
	private function set_tw_username($user_id, $twitter_username){
		
		$this->attrs['twitter_username'] = $twitter_username;
		return update_user_meta($user_id, 'twitter_username', $twitter_username);
	}

	/*
	 * Set the username as metadata if provider is Google+
	 * 
	 */
	private function set_gp_username($user_id, $gp_username){

		$this->attrs['gp_username'] = $gp_username;
		return update_user_meta($user_id, 'gp_username', $gp_username);
	}

	/*
	 * Pseudo LogIn 
	 * TO DO: Send user login and token for mobile app
	 */
	public function pseudo_login($user_id = NULL){

		if( $user_id ){
			wp_set_auth_cookie( $user_id, 0, 0 );
			wp_set_current_user( $user_id );
			global $current_user;
		}
		return wp_send_json_success();
	}

}
