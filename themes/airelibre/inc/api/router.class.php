<?php
require_once('User.class.php');
class Router{

	function __construct($token = 'NOTOKEN', $attrs = array()){
		if($token == 'NOTOKEN') return FALSE;
		add_action('slim_mapping', array( &$this,'api_mapping' ));

		/* Set custom expiration if provided, default if not */
		(!empty($attrs) && $attrs['expires'] !== "") ? $this->set_expiration($attrs['expires']) : $this->set_expiration();
		$this->attrs =  array(
							'request_token' => $token,
							'method'		=> '$method',
							'data'			=> '$data'
						);
	}

	function api_mapping($slim){

		$context = $this;
		

		/*** Those damn robots ***/
		$slim->get('/rest/v1/', function() {
			wp_send_json_error('These are not the droids you are looking for, please get a token first');
			exit();
		});

		$slim->get('/rest/v1/robots/', function() {
			wp_send_json_success('These are not the droids you are looking for, please get a token first. If you\'re testing API connection, everything seems to be going smooth ;)');
			exit();
		});

		/*
		 *   _____      _              
		 *  /__   \___ | | _____ _ __  
		 *    / /\/ _ \| |/ / _ \ '_ \ 
		 *   / / | (_) |   <  __/ | | |
		 *   \/   \___/|_|\_\___|_| |_|
		 *                             
		 */

			/* 
			 * Get a passive token
			 * Generates a token, stores it into the database and returns the token as a response
			 * Implement so that tokens are generated only once, then validated and used until valid
			 * @return 	response.success Bool true is request was executed correctly
			 * @return 	response.request_token Generated passive token
			 * @see 	Validate token
			 */
			$slim->get('/rest/v1/auth/getToken/', function () use ($context){
				
				if (method_exists("Router", 'generateToken')){
					$new_token = $context->generateToken(FALSE);
					wp_send_json_success(array('request_token' => $new_token));
				}
				wp_send_json_error('Couldn\'t execute method');
				exit;
			});
			
			/* Check token for validity */
			$slim->post('/rest/v1/auth/user/checkToken/', function () {
				
				if(!isset($_POST['request_token']) OR !isset($_POST['user_id'])) return wp_send_json_error(array("error" => "Please provide a user_id and a request token, or refer to the documentation for further support"));
				$device_info = (isset($_POST['device_info'])) ? $_POST['device_info'] : NULL;
				
				$response = $this->check_token_valid($_POST['user_id'], $_POST['request_token'], $device_info);
				if($response) 
					wp_send_json_success($response);
				wp_send_json_error();
			});

			/*  
			 * Validate Token
			 */
			$slim->post('/rest/v1/user/validateToken/', function () {
				
				$token 		= (isset($_POST['token'])) 		? $_POST['token'] 	: NULL;
				$user_id 	= (isset($_POST['user_id'])) 	? $_POST['user_id'] : NULL;
				$validate_id 	= (isset($_POST['validate_id'])) ? $_POST['validate_id'] : NULL;
				$device_info 	= (isset($_POST['device_info'])) ? $_POST['device_info'] : NULL;
				
				if(!$token OR !$user_id) wp_send_json_error('Error: Not enough data provided, please check documentation');

				/* Validate token and return it as a response */
				if(!$this->check_token_valid($user_id, $token, $device_info)){
					$response = $this->update_tokenStatus($token, $user_id, 1);
					if($validate_id) $this->settokenUser($token, $validate_id, $device_info);
					if($response) wp_send_json_success(array('token' => $token, 'status' => 'valid'));
					/* Error: Something went wrong */
					wp_send_json_error('Can\'t validate token, please check your implementation. Do not send tokens directly to this endpoint');
					exit;
				}
				/* Error: Something went wrong */
				wp_send_json_error('Can\'t validate token or already valid. Please check your implementation or execute auth/user/checkToken/ endpoint to check your current token status. Do not send tokens directly to this endpoint');
				exit;
			});
				
			/*  
			 * ¡VARIATION! 
			 * Validate Token from another endpoint
			 * Differs from the '/user/validateToken/' endpoint in the way it sends a response.
			 * Instead of sending a JSON response, this one just sends a Boolean response to handle inside the other endpoint
			 */
			$slim->post('/rest/v1/auth/validateToken/', function () {

				$token 		= (isset($_POST['token'])) 		? $_POST['token'] 	: NULL;
				$user_id 	= (isset($_POST['user_id'])) 	? $_POST['user_id'] : NULL;
				$validate_id = (isset($_POST['validate_id'])) ? $_POST['validate_id'] : NULL;
				
				if(!$token OR !$user_id) return FALSE;

				/* Validate token and return it as a response */
				if(!$this->check_token_valid($user_id, $token)){
					$response = $this->update_tokenStatus($token, $user_id, 1);
					if($validate_id) $this->settokenUser($token, $validate_id);
					
					if($response) return TRUE;
					return FALSE;
					exit;
				}
				/* Error: Something went wrong */
				return
				exit;
			});


			/* 
			 * Create user new 
			 * @param 	$attr via $_POST {username: 'username',email: 'email',password: 'password',}
			 * @return 	response.success Bool Executed
			 * @return 	response.data User data
			 * @see 	User.class.php
			 */
			$slim->post('/rest/v1/auth/user/', function () {

				extract($_POST);
				if (!isset($email)) wp_send_json_error('Please provide a username');
				
				/* Create user object */
				$User 	= new User();

				$created = $User->create_if__notExists($email, $attrs, FALSE);
				if($created){
					if( isset($attrs['login_redirect']) 
						 AND (!$attrs['login_redirect'] OR $attrs['login_redirect'] == FALSE)
					  ) {
						mobile_pseudo_login();
						wp_send_json_success(get_user_by("id", $created)->user_login);
					}
						
					/* Must provide password to use this method */
					_mobile_pseudo_login($username, $attrs['password'], $attrs['request_token']);
					exit;
				}
				wp_send_json_error('Couldn\'t create user');
				exit;
			});

			/* 
			 * Check if user exists
			 * 
			 */
			$slim->get('/rest/v1/user/exists/', function () {
				$User = new User();
				/* Create user */
				$email = isset($_GET['email']) ? $_GET['email'] : NULL;
				
				if(!$email)
					json_encode(FALSE);
				$username = str_replace("@", "_", $email);
				$username = substr(str_replace(".", "", $username), 0, 14 );
				
				if($User->_username_exists($username)){

					$user = get_user_by("slug", $username);
					$foto_user = get_user_meta( $user->ID, "foto_user", TRUE );

					$json_response = array(
											'user_id' 		=> $User->_username_exists($username), 
											'username' 		=> $username,
											'user_login' 	=> $username,
											'role'			=> $user->roles[0],
											'profile_url'	=> $foto_user,
										);
					wp_send_json_success($json_response);
				}
				echo json_encode(array("success" => FALSE));
				exit;
			});



		/*
		 *   _             _       
		 *  | | ___   __ _(_)_ __  
		 *  | |/ _ \ / _` | | '_ \ 
		 *  | | (_) | (_| | | | | |
		 *  |_|\___/ \__, |_|_| |_|
		 *            |___/         
		 */
		
			/*** Get random event image for login page ***/
			$slim->get('/rest/v1/content/login/', function() {
				return get_login_content();
				exit;
			});
			
			/*** User Login ***/
			/* User Login to API and validate token in database
			 * @return JSON success
			 */
			$slim->post('/rest/v1/auth/login/', function() {
				return mobile_pseudo_login();
				exit;
			});

			/* User Logout from API and invalidate token in database
			 * @param String $logged The username
			 * @param String $request_token (via $_POST) to invalidate in database
			 * @return JSON success
			 */
			$slim->post('/rest/v1/auth/:logged/logout/', function($logged) {

				return mobile_pseudo_logout($logged);
				exit;
			});


		/*     __               _ 
		 *    / _| ___  ___  __| |
		 *   | |_ / _ \/ _ \/ _` |
		 *   |  _|  __/  __/ (_| |
		 *   |_|  \___|\___|\__,_|
		 */      
		
			/**
			 * Get home feed
			 * @param String $user_login The user to retrieve timeline for
			 * @param Int $offset Number of offsetted posts pages for pagination purposes
			 * @param String $filter
			 * @type ANNONYMOUS
			 */
			$slim->get('/rest/v1/feed/home/',function (){
				// TODO Use user information to cure feed
				echo fetch_main_feed();
				exit;
			});
			
			/**
			 * Get radio feed
			 * @param String $user_login The user to retrieve timeline for
			 * @param Int $offset Number of offsetted posts pages for pagination purposes
			 * @param String $filter
			 * @type ANNONYMOUS
			 */
			$slim->get('/rest/v1/feed/radio/',function (){
				// TODO Use user information to cure feed
				echo json_encode(fetchRadio());
				exit;
			});
			
			/**
			 * Get archive feed
			 * @param String $user_login The user to retrieve timeline for
			 * @param Int $offset Number of offsetted posts pages for pagination purposes
			 * @param String $filter
			 * @type ANNONYMOUS
			 */
			$slim->get('/rest/v1/feed/:kind/',function ($kind){
				// TODO Use user information to cure feed
				echo fetch_archive_feed($kind);
				exit;
			});

			/**
			 * Get podcast by episode
			 * @param String $episode_id
			 */
			$slim->get('/rest/v1/podcasts/:episode_id/', function ($episode_id){
				echo fetch_podcast($episode_id);
				exit;
			});

			/**
			 * Get categories feed alphabetized
			 * @param Integer $level where 0 is top level and $parent is expected for deeper levels
			 * @param Integer $limit
			 * @param Integer $offset for pagination
			 * @category GET Endpoint
			 */
			$slim->get('/rest/v1/alphabet/terms/:taxonomy/', function($taxonomy = NULL ){
				echo json_encode(fetch_terms_alphabetized($taxonomy, TRUE));
				exit;
			});





			/**
			 * Get home feed for a logged user
			 * @param String $user_login The user to retrieve timeline for
			 * @param Int $offset Number of offsetted posts pages for pagination purposes
			 * @param String $filter
			 * @type LOGGED
			 */
			$slim->get('/rest/v1/:u_login/feed(/:offset(/:filter))',function ($user_login, $offset = 0, $filter = "all"){
				// TODO Use user information to cure feed
				echo fetch_main_feed($filter, $offset);
				exit;
			});

			

			/**
			 * Get categories tree
			 * @param Integer $level where 0 is top level and $parent is expected for deeper levels
			 * @param Integer $limit
			 * @param Integer $offset for pagination
			 * @category GET Endpoint
			 */
			$slim->get('/rest/v1/content/enum/categories/', function(){
				echo fetch_categories_tree();
				exit;
			});

			/**
			 * Get design tool archive and detailed info
			 * @param String $taxonomy ['autores']
			 * @param Integer $term_id 
			 * @category GET Endpoint
			 */
			$slim->get('/rest/v1/content/taxonomy/:taxonomy/:term_id/', function($taxonomy = 'category', $term_id){
				echo fetch_taxonomy_archive($term_id, $taxonomy);
				exit;
			});

			/**
			 * Fetch column detai
			 * @category GET Endpoint
			 */
			$slim->get('/rest/v1/columns/:column_id/', function($column_id = NULL){
				echo fetch_column_detail($column_id);
				exit;
			});

			/**
			 * Fetch post detail
			 * @category GET Endpoint
			 */
			$slim->get('/rest/v1/content/:post_id/', function($post_id){
				echo fetch_post_detail($post_id);
				exit;
			});
			
			/**
			 * Fetch a limited set of random users
			 * @param String $filter
			 * @param Integer $limit defaults to 5
			 * @category GET Endpoint
			 */
			$slim->get('/rest/v1/content/users/:filter(/:limit)/', function($filter, $limit = 5){
				echo fetch_randomUsers($filter, $limit);
				exit;
			});

			/**
			 * Fetch a limited set of random users excluding logged user
			 * @param String $filter
			 * @param Integer $limit defaults to 5
			 * @category GET Endpoint
			 */
			$slim->get('/rest/v1/:logged_user/content/users/:filter(/:limit)/', function($logged_user, $filter, $limit = 5){
				$user = get_user_by("slug", $logged_user);
				echo fetch_randomUsers($filter, $limit, $user->ID);
				exit;
			});



		/*                          _     
		*  ___  ___  __ _ _ __ ___| |__  
		* / __|/ _ \/ _` | '__/ __| '_ \ 
		* \__ \  __/ (_| | | | (__| | | |
		* |___/\___|\__,_|_|  \___|_| |_|
		*/
			
			/**
			 * Search website
			 * @param String $s
			 * @todo Divide search by: people, tag, events and accept the parameter as a filter
			 */
			$slim->get('/rest/v1/content/search/:s(/:offset)/',function( $s, $offset = 0) {
				echo json_encode(search_airelibre($s, $offset));
				exit;
			});

		
		/*
		 *                     __ _ _           
		 *    _ __  _ __ ___  / _(_) | ___  ___ 
		 *   | '_ \| '__/ _ \| |_| | |/ _ \/ __|
		 *   | |_) | | | (_) |  _| | |  __/\__ \
		 *   | .__/|_|  \___/|_| |_|_|\___||___/
		 *   |_|                                
		 */


			/** 
			 * Get user basic info
			 * User ME
			 * @return JSON formatted user basic info
			 * TO DO: Check data sent by this endpoint and activate it
			 */
			$slim->get('/rest/v1/:logged/me/', function ($logged = NULL) {
				echo fetch_me_information($logged);
				exit;
			});

			/**
			 * Get user dashboard
			 * @param String $logged User requesting the profile
			 * @return JSON formatted dashboard information
			 */
			$slim->get('/rest/v1/:logged/dashboard/', function ($logged){
				echo fetch_user_dashboard($logged);
				exit;
			});
	
		
			/* Get user profile
			 * @param String $logged User requesting the profile
			 * @param String $queried_login User whose profile is requested
			 * @return JSON formatted user profile info
			 */
			$slim->get('/rest/v1/:logged/maker/:queried_id/', function ($logged, $queried_id){
				echo fetch_user_profile($queried_id, $logged);
				exit;
			});

			/* Get user profile min
			 * @param String $logged User requesting the profile
			 * @param String $queried_login User whose profile is requested
			 * @return JSON formatted user profile info
			 */
			$slim->get('/rest/v1/min/:logged/maker/:queried_id/', function ($logged, $queried_id){
				echo min_fetch_user_profile($queried_id, $logged);
				exit;
			});

			/**
			 * Get users by user category
			 * @param String $logged User requesting the profile
			 * @param String $queried_login User whose profile is requested
			 * @param String $@ User location via GET
			 * @return JSON formatted user profile info
			 */
			$slim->get('/rest/v1/around/:logged/makers/:filter(/:limit)', function ($logged, $filter, $limit = 10){
				if(!isset($_GET['@']))
					echo wp_send_json_error();
				$location = $_GET['@'];
				echo fetch_users_bycategory($logged, $location, $filter, $limit);
				exit;
			});


			/**
			 * Update user profile
			 * @param String $ulogin User whose profile is being updated
			 * @see update_user_profile
			 * @see museografo_completar_perfil
			 * @return JSON formatted user profile info
			 * @todo Make this endpoint a PATCH
			 */
			$slim->post('/rest/v1/user/:user_login/', function($user_login){
				// $app = \Slim\Slim::getInstance();
				// $app->add(new \Slim\Middleware\ContentTypes());
				
				// $var_array = json_decode($app->request->getBody());

				$var_array = (object) $_POST;
				return update_user_profile($user_login, $var_array);
				exit;
			});

			/**
			 * Update user password
			 * @param String $ulogin User whose password is being updated
			 * @see PUT endpoint "/rest/v1/user/:ulogin"
			 * @return JSON formatted success response
			 * @todo Make this endpoint a PATCH
			 */
			$slim->post('/rest/v1/user/:ulogin/password/', function($ulogin){
				// $app = \Slim\Slim::getInstance();
				// $app->add(new \Slim\Middleware\ContentTypes());
				// $var_array = array();
				// parse_str($app->request->getBody(), $var_array);
				$var_array = $_POST;
				$new_password = $var_array['password_nuevo'];
				return update_user_password($ulogin, $var_array['password_nuevo']);
				exit;
			});
	

			/*
			 * Get attachments uploaded by user
			 * @param String $user_login
			 * @param Int $limit
			 * @param String optional $Size
			 */
			$slim->get('/rest/v1/user/:user_login/gallery/:limit/(:size)/', function($user_login, $limit, $size = 'gallery_mobile') {	
				echo get_user_gallery($user_login, $limit, $size);
				exit;
			});


			/* Get user followers
			 * @param String $logged The log in name of the logged user making the call
			 * @param String $queried_login The log in name of the queried user
			 * @return JSON object containing the user follower list
			 * @see retrieve_user_followers
			 */
			$slim->get('/rest/v1/:logged/user/:queried_login/followers/:type/', function ($logged, $queried_login, $type) {
				return retrieve_user_followers($queried_login, $logged, $type);
			});

			/* Get user followees
			 * @param String $logged The log in name of the logged user making the call
			 * @param String $queried_login The log in name of the queried user
			 * @return JSON object containing the user followees list
			 * @see retrieve_user_followees
			 */
			$slim->get('/rest/v1/:logged/user/:queried_login/followees/:type/', function ($logged, $queried_login, $type) {
				return retrieve_user_followees($queried_login, $logged, $type);
			});

			


		/*     __       _ _                   
		 *    / _| ___ | | | _____      _____ 
		 *   | |_ / _ \| | |/ _ \ \ /\ / / __|
		 *   |  _| (_) | | | (_) \ V  V /\__ \
		 *   |_|  \___/|_|_|\___/ \_/\_/ |___/
		 *                                    
		 */
		
			/* Follow User
			 * @param Int $who_follows The active logged user
			 * @param Int $who The user ID to follow
			 * @param String $type The type of user who is following
			 * @return JSON success
			 */
			$slim->post('/rest/v1/:who_follows/follow',function($who_follows) {

				$who 	= isset($_POST['user_id']) 	? $_POST['user_id'] :  NULL;
				$user  	= get_user_by('login', $who_follows);
				if( follow_user( $user->ID, $who))
					wp_send_json_success();
				wp_send_json_error();
				exit;
			});

			/* Unfollow User
			 * @param Int $who_follows The active logged user
			 * @param Int $who The user ID to unfollow
			 * @return JSON success
			 */
			$slim->post('/rest/v1/:who_follows/unfollow',function($who_follows) {
				
				$who = isset($_POST['user_id']) ? $_POST['user_id'] : NULL;
				$user = get_user_by('login', $who_follows);
				if( unfollow_user($user->ID, $who))
					wp_send_json_success();
				wp_send_json_error();
				exit;
			});

			/* Follow Category
			 * @param String $user_login Who follows
			 * @param $cat_id via $_POST
			 */
			$slim->post('/rest/v1/:user_login/categories/follow/', function($user_login) {
				$cat_id = (!empty($_POST) AND isset($_POST['cat_id'])) ? $_POST['cat_id'] : NULL ; 
				return (dedalo_follow_category($user_login, $cat_id)) ? wp_send_json_success() : wp_send_json_error("Error following category");
				exit;
			});

			/* Unfollow Cateogry
			 * @param String $user_login Who follows
			 * @param $cat_id via $_POST
			 */
			$slim->post('/rest/v1/:user_login/categories/unfollow/',function($user_login) {
				$cat_id = (!empty($_POST) AND isset($_POST['cat_id'])) ? $_POST['cat_id'] : NULL ; 
				return (dedalo_unfollow_category($user_login, $cat_id)) ? wp_send_json_success() : wp_send_json_error("Error unfollowing category");
				exit();
			});

			/* Check if user is following category
			 * @param String $user_login Who
			 * @param Integer $cat_id Which
			 */
			$slim->get('/rest/v1/:user_login/categories/is_following/:cat_id/',function($user_login, $cat_id) {
				echo (is_following_cat($user_login, $cat_id)) ? "true" : "false";
				exit();
			});

			/* Get categories feed by parent level
			 * @param String $user_login The active logged user
			 * @param Int $level The category level to fetch
			 */
			$slim->get('/rest/v1/:user_login/categories/:level/', function($user_login, $level) {
				$user = get_user_by('login', $user_login);
				echo get_categories_feed($user, $level, NULL, FALSE);
				exit;
			});

			/* Get categories feed by parent level
			 * @param String $user_login The active logged user
			 * @param Int $level The category level to fetch
			 */
			$slim->get('/rest/v1/categories/tree/', function() {
				// echo get_categories_tree();
				exit;
			});
			
			/* Get random categories for discovery page
			 * @param String $user_login The active logged user
			 * @param Int $level The category level to fetch
			 */
			$slim->get('/rest/v1/:user_login/categories/rand/:number/', function($user_login, $number) {
				$user = get_user_by('login', $user_login);
				echo get_rand_categories_feed($user, $number);
				exit;
			});

			/* Get categories feed by parent level
			 * @param String $user_login The active logged user
			 * @param Int $level The category level to fetch
			 */
			$slim->get('/rest/v1/:u_login/categories/:level/:exclude', function($user_login, $level, $exclude) {
				$user = get_user_by('login', $user_login);
				echo get_categories_feed($user, $level, $exclude);
				exit;
			});
			
			/* Get category details (Explore category)
			 * @param String $user_login The active logged user
			 */
			$slim->get('/rest/v1/:user_login/category/:cat_id/', function($user_login, $cat_id) {
				
				wp_send_json_success(get_category_detail($cat_id, $user_login));
				exit;
			});


			/* Get user follower count with user role filter
			 * @param String $user_login The user whose followers are being queried
			 * @param String $filter The type of followers we are looking for DEFAULT is 'museografo'
			 * TO DO: Here we call subscribers 'museografo', later make sure all the api calls respond to this standard
			 * @return Int $follower_count or false if error
			 */
			$slim->get('/rest/v1/user/:user_login/follower_count/:filter/', function($user_login, $filter = 'museografo'){
				$user = get_user_by('slug', $user_login);
				echo json_encode(intval(checa_total_seguidores($user->ID, $filter)));
				exit;
			});

			/* Get user following count with user role filter
			 * @param String $user_login The user whose followees are being queried
			 * @param String $filter The type of followees we are looking for DEFAULT is 'museografo'
			 * TO DO: Here we call subscribers 'museografo', later make sure all the api calls respond to this standard
			 * @return Int $followee_count or false if error
			 */
			$slim->get('/rest/v1/user/:user_login/followee_count/:filter/', function($user_login, $filter = 'museografo'){
				$user = get_user_by('slug', $user_login);
				echo json_encode(intval(checa_total_siguiendo($user->ID, $filter)));
				exit;
			});

		/*                _   _  __ _           _   _                 
		 *    _ __   ___ | |_(_)/ _(_) ___ __ _| |_(_) ___  _ __  ___ 
		 *   | '_ \ / _ \| __| | |_| |/ __/ _` | __| |/ _ \| '_ \/ __|
		 *   | | | | (_) | |_| |  _| | (_| (_| | |_| | (_) | | | \__ \
		 *   |_| |_|\___/ \__|_|_| |_|\___\__,_|\__|_|\___/|_| |_|___/
		 *                                                            
		 */

			/* Get notifications count
			 * @param String $user_login The active logged user_login name
			 * @return JSON
			 */
			$slim->get('/rest/v1/:user_login/notifications/count/',function($user_login) {
				$user = get_user_by('login', $user_login);
				
				echo get_count_alertas_user($user);
				exit();
			});

			/* Get notifications
			 * @param String $user_login The active logged user_login name
			 * Retrieves 10 max notifications, to change this modify the get_notifications_pool second parameter
			 * @return JSON
			 */
			$slim->get('/rest/v1/:user_login/notifications/',function($user_login) {
				
				echo get_notifications_pool($user_login, 10);
				exit();
			});

			/* Mark notification as read
			 * @param String $user_login The active logged user_login name
			 * @param Int $notification_id The ID of the notification
			 * @return JSON success
			 */
			$slim->post('/rest/v1/:user_login/notifications/read/:n_id',function($user_login, $notification_id) {
				$user = get_user_by('login', $user_login);

				if(update_alerta_id($notification_id)) wp_send_json_success();
				exit();
			});
		

		



			
			/*
			 * Get discover screen feed
			 * @param String $user_login The logged in user (to be deprecated soon)
			 * @param Int $offset Number of offsetted posts pages for pagination purposes
			 * @important Timeline gets blocks of 10 activities, offset must be set according to the set of results. Ej. Page 1 is offset 0, page 2 is offset 1
			 * 
			 */
			$slim->get('/rest/v1/:u_login/feeds/discover/',function ($user_login){
				// echo get_discover_feed($user_login);
				echo "Sowwy, this endpoint will be available until next version of the API";
				exit;
			});



		/*
		 * Select printer for my product
		 * @param String $user_login
		 * @param Int $ref_id
		 */
		$slim->post('/rest/v1/:user_login/purchase/:ref_id',function($user_login, $ref_id) {
			extract($_POST);
			$user = get_user_by('login', $user_login);
			echo set_printer_job($user->ID, $ref_id, $printer_id);
			exit;
		});



	   

		/*
		 *    __ _ _                    _                 _ 
		 *   / _(_) | ___   _   _ _ __ | | ___   __ _  __| |
		 *  | |_| | |/ _ \ | | | | '_ \| |/ _ \ / _` |/ _` |
		 *  |  _| | |  __/ | |_| | |_) | | (_) | (_| | (_| |
		 *  |_| |_|_|\___|  \__,_| .__/|_|\___/ \__,_|\__,_|
		 *                       |_|                        
		 */

			/*
			 * Upload event image
			 * @param String $logged
			 * @param File $file via $_POST
			 * @return JSON success
			 * TO DO: Check token validity before actually uploading file
			 * TO DO: Generate extra tokens to upload files, like a nonce
			 */
			$slim->post('/rest/v1/transfers/:logged/event_upload/:event_id/', function($logged, $event_id){
				if( isset($_FILES)){
					wp_send_json_success(save_event_upload($logged, $_FILES['file']['tmp_name'], $_FILES['file']['name'], $event_id));
					exit;
				}
				wp_send_json_error("No files detected");
				exit;
			});
	
			/*
			 * Update user profile pic
			 * @param String $logged
			 * @param File $file via $_POST
			 * @return JSON success
			 * TO DO: Check token validity before actually uploading file
			 * TO DO: Generate extra tokens to upload files, like a nonce
			 */
			$slim->put('/rest/v1/transfers/:logged/profile/', function($logged){
				$app = \Slim\Slim::getInstance();
				$values = array();
				parse_str($app->request->getBody(), $values);
				
				if( update_index_categories($logged, $values) AND isset($values['_redirect']) )
					wp_redirect($values['_redirect']);
				exit;
			});
			
			/*
			 * Update user profile pic
			 * @param String $logged
			 * @param File $file via $_POST
			 * @return JSON success
			 * TO DO: Check token validity before actually uploading file
			 * TO DO: Generate extra tokens to upload files, like a nonce
			 */
			$slim->post('/rest/v1/transfers/:logged/profile/', function($logged){
				if( isset($_FILES)){
					wp_send_json_success(save_profile_picture_upload($logged, $_FILES['file']['tmp_name'], $_FILES['file']['name']));
					exit;
				}
				wp_send_json_error("No files detected");
				exit;
			});


			/*    _                _       
			 *   /_\  ___ ___  ___| |_ ___ 
			 *  //_\\/ __/ __|/ _ \ __/ __|
			 * /  _  \__ \__ \  __/ |_\__ \
			 * \_/ \_/___/___/\___|\__|___/
			 * General data sets for ui controls or some assets, i love the word assets                           
			 */  

			/*
			 * Update user profile pic
			 * @param String $logged
			 * @param File $file via $_POST
			 * @return JSON success
			 * TO DO: Check token validity before actually uploading file
			 * TO DO: Generate extra tokens to upload files, like a nonce
			 */
			$slim->get('/rest/v1/assets/:asset_name/:args/', function($asset_name, $args){
				echo json_encode(museo_get_asset_by_name($asset_name, $args));
				exit;
			});

			/*
			 * Write to log
			 * @param String $log_name
			 * @param Object $pieces via POST
			 * @return success Object including raw log input
			 */
			$slim->post('/rest/v1/assets/logs/:log_name/', function($log_name){
				echo json_encode(museo_write_log($log_name));
				exit;
			});
	
	}
					  

	/*
	 * Set expiration for the Request token
	 * @param $exp Int (Expiration time in miliseconds)
	 * Default value is 86,400,000 (24 hours)
	 * Use 0 for no expiration token
	 * TO DO: Invalidate token after expiration
	 */
	private function set_expiration($exp = 86400000){
		$this->attrs['timestamp'] 	= microtime(FALSE);
		$this->attrs['expires'] 	= $exp;
		return $this;
	}

	/*
	 * Generate token
	 * @param $active Bool FALSE is default for passive tokens
	 * PLEASE DO NOT ACTIVATE TOKENS DIRECTLY, USE AUTH-ACTIVATE TOKEN INSTEAD
	 */
	private function generateToken($active = FALSE){
		$token = strtoupper(md5(uniqid(rand(), true)));
		$this->setToken($token, $active);
		$this->set_expiration();
		$this->saveToken_toDB();
		return $this->getToken();
	}

	/*
	 * Save Token to DB
	 * @param $user_id String Default is "none"
	 */
	private function saveToken_toDB($user_id = 'none'){
		global $wpdb;
		$result = $wpdb->query( $wpdb->prepare(" INSERT INTO _api_active_tokens
												  (user_id, token, token_status, expiration)
												  VALUES(%s, %s, %d, %d);
											   "
												 ,$user_id
												 ,$this->attrs['request_token']
												 ,0
												 ,$this->attrs['expires'] ));
		return ($result == 1) ? TRUE : FALSE; 
	}

	/*
	 * Update Token status
	 * @param $token String
	 * @param $user_id Int
	 * @param $status String
	 */
	public function update_tokenStatus( $token, $user_id = 'none', $status = 0){
		global $wpdb;
		$result = $wpdb->query( $wpdb->prepare(" UPDATE _api_active_tokens
												  SET token_status = %d
												  WHERE user_id = %s
												  AND token = %s;
											   "
												 ,$status
												 ,$user_id
												 ,$token));
		return ($result == 1) ? $token : FALSE; 
	}

	/*
	 * Update Token expiration time 
	 * @param $token String
	 * @param $user_id Int
	 * @param $new_expiration Int (milliseconds) Default is 86400000
	 */
	private function update_tokenExp( $token, $user_id = 'none', $new_expiration = 86400000){
		global $wpdb;
		$result = $wpdb->query( $wpdb->prepare(" UPDATE _api_active_tokens
												  SET expiration = %d
												  WHERE user_id = %s
												  AND token = %s;
											   "
												 ,$status
												 ,$user_id
												 ,$token));
		return ($result == 1) ? $token : FALSE; 
	}

	/*
	 * Update Token expiration time 
	 * @param $token String
	 * @param $user_id Int
	 * @param $new_timestamp Unix timestamp
	 */
	private function update_tokenTimestamp( $token, $user_id = 'none', $new_timestamp){
		global $wpdb;
		$result = $wpdb->query( $wpdb->prepare(" UPDATE _api_active_tokens
												  SET ge_timestamp = FROM_UNIXTIME(%d)
												  WHERE user_id = %s
												  AND token = %s;
											   "
												 ,$status
												 ,$user_id
												 ,$new_timestamp));
		return ($result == 1) ? $token : FALSE; 
	}

	/*
	 * Update Token user
	 * @param $token String
	 * @param $user_id Int The user that will be associated with the token
	 */
	public function settokenUser( $token, $user_id = 'none', $device_info = NULL){
		global $wpdb;
		$result = $wpdb->query( $wpdb->prepare(" UPDATE _api_active_tokens
												  SET user_id = %d
												  WHERE token = %s;
											   "
												 ,$user_id
												 ,$token));
		$pieces = array();
		if($result == 1 AND $device_info)
			$pieces = array(
							'data' => $device_info,
							'message' => "Token {$token} successfully assigned to the user {$user_id} connected from mobile device."
						);
		$pieces = array(
						'user_id' => $user_id,
						'error' => "Couldn't get device info"
					);
		// museo_write_log('connections', $pieces);
		return ($result == 1) ? TRUE : FALSE; 
	}

	/*  
	 * Check token validity
	 * @param user_id String (string for internal purposes, int id's only)
	 * @param token String 
	 * @param Array $device_info contains device info to write in the log 
	 */
	public function check_token_valid($user_id, $token, $device_info = array()){
		global $wpdb;
		
		$result = $wpdb->get_var( $wpdb->prepare(" SELECT token_status
													FROM _api_active_tokens
													  WHERE user_id = %s
													  AND token = %s;
												   "
												 ,$user_id
												 ,$token));
		
		$pieces = array();
		if(intval($result) == 1 ){
			$pieces = array(
							'request_token' => $token,
							'user_id' => $user_id,
							'data' => $device_info,
							'message' => "Token {$token} checked for validation connected from mobile device."
						);
			// museo_write_log('connections', $pieces);
			return ($result == 1) ? TRUE : FALSE; 
		}
		$pieces = array(
						'user_id' => $user_id,
						'error' => "Couldn't get device info"
					);
		// museo_write_log('connections', $pieces);
		return ($result == 1) ? TRUE : FALSE; 
	}

	/*
	 * Token setter
	 * @param $token String
	 * @param $active Bool Default is FALSE
	 * Please DO NOT activate tokens directly, 
	 *  follow authentication process to do so
	 */
	private function setToken($token, $active = FALSE){
		$this->attrs['request_token'] = $token;
		return $this;
	}
	
	/*
	 * Token getter
	 * @return (String) Object token 
	 */
	private function getToken(){
		return $this->attrs['request_token'];
	}

}