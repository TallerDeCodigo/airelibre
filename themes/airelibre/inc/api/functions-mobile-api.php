<?php

/* Create tokens table on theme switch */
function create_tokenTable(){
	global $wpdb;
	return $wpdb->query(" CREATE TABLE IF NOT EXISTS _api_active_tokens (
							  id int(12) unsigned NOT NULL AUTO_INCREMENT,
							  user_id varchar(12) NOT NULL,
							  token varchar(32) NOT NULL,
							  token_status tinyint(1) NOT NULL DEFAUlT 0,
							  expiration bigint(20) unsigned NOT NULL,
							  token_salt varchar(32),
							  gen_timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
							  PRIMARY KEY (id)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8;
						");
}
add_action('switch_theme', 'create_tokenTable');

function encode_response($response_data = NULL, $encoding = "JSON", $success = TRUE){
	if($encoding == "JSON"){
		$response = array();
	}
}

/* Via POST 
 * Check login data matches, activate token and return user data
 * DISABLING TOKEN RESULTS IN DENIED PROTECTED REQUESTS BUT CAN STILL BE USED AS A PASSIVE TOKEN
 * @param String @user_login (via $_POST) The username
 * @param String @user_password (via $_POST) the password matching the user
 * @return JSON encoded user data to store locally
 * @see get User basic data
 */
function mobile_pseudo_login() {

	if(!isset($_POST['user_email']) && !isset($_POST['user_password'])) 
		return wp_send_json_error(array('error_code' => '401', 'error_message' => 'Data sent to server is not well formatted'));
	
	global $rest;
	extract($_POST);
	$user = get_user_by("email", $user_email);

	$creds = array();
	$creds['user_login'] = $user->data->user_login;
	$creds['user_password'] = $user_password;
	$creds['remember'] = true;
	$SignTry = wp_signon( $creds, false );

	if( !is_wp_error($SignTry)){
		
		$user_id 	= $SignTry->ID;
		$user_login = $SignTry->user_login;
		$role 		= $SignTry->roles[0];
		$user_name 	= $SignTry->display_name;
		
		/* Validate token before sending response */
		if(!$rest->check_token_valid('none', $request_token)){
			$response = $rest->update_tokenStatus($request_token, 'none', 1);
			if($user_id) $rest->settokenUser($request_token, $user_id);
			
			/* Return user info to store client side */
			if($response){
				wp_send_json_success(array(
										'user_id' 		=> $user_id,
										'user_login' 	=> $user_login,
										'user_name' 	=> $user_name,
										'role' 			=> $role
									));
				exit;
			}
			/* Error: Something went wrong */
			return wp_send_json_error();
			exit;
		}
	}
	/* There was an error processing auth request */
	wp_send_json_error(array('error_code' => '400', 'error_message' => 'Couldn\'t sign in using the data provided'));
}

/* Check login data matches, activate token and return user data
 * DISABLING TOKEN RESULTS IN DENIED PROTECTED REQUESTS BUT CAN STILL BE USED AS A PASSIVE TOKEN
 * @param String @user_login (via $_POST) The username
 * @param String @user_password (via $_POST) the password matching the user
 * @return JSON encoded user data to store locally
 * @see get User basic data
 */
function _mobile_pseudo_login($user_login, $user_password, $request_token) {
	
	if(!isset($user_login) && !isset($user_password)) return wp_send_json_error();
	
	global $rest;
	$creds = array();
	$creds['user_login'] = $user_login;
	$creds['user_password'] = $user_password;
	$creds['remember'] = true;
	$SignTry = wp_signon( $creds, false );

	if( !is_wp_error($SignTry)){
		
		$user_id 	= $SignTry->ID;
		$user_login = $SignTry->user_login;
		$role 		= $SignTry->roles[0];
		$user_name 	= $SignTry->display_name;

		/* Validate token before sending response */
		if(!$rest->check_token_valid('none', $request_token)){
			$response = $rest->update_tokenStatus($request_token, 'none', 1);
			if($user_id) $rest->settokenUser($request_token, $user_id);
			
			/* Return user info to store client side */
			if($response){
				wp_send_json_success(array(
										'user_id' 		=> $user_id,
										'user_login' 	=> $user_login,
										'user_name' 	=> $user_name,
										'role' 			=> $role
									));
				exit;
			}
			/* Error: Something went wrong */
			return FALSE;
			exit;
		}
	}
	/* There was an error processing auth request */
	wp_send_json_error("Couldn't sign in using the data provided");
}

/* Disable token in database for the logged user
 * DISABLING TOKEN RESULTS IN DENIED PROTECTED REQUESTS BUT CAN STILL BE USED AS A PASSIVE TOKEN
 * @param String @logged The username
 * @param String @request_token (via $_POST) the active request token for this user
 */
function mobile_pseudo_logout($logged){
	$user = get_user_by('slug', $logged);
	
	if(!isset($_POST['request_token']) || !$user) return wp_send_json_error();

	global $rest;
	/* Validate token before sending response */
	if($rest->check_token_valid($user->ID, $_POST['request_token'])){
		$response = $rest->update_tokenStatus($_POST['request_token'], $user->ID, 0);
		/* Return user info to store client side */
		if($response){
			wp_send_json_success();
			exit;
		}
		/* Error: Something went wrong */
		wp_send_json_error();
	}
	exit;
}

/* DEPRECATED */
/* DEPRECATED */
/* DEPRECATED (if used) PLEASE CHECK LOGIN USING TOKEN with checkToken endpoint */
/* DEPRECATED */
/* DEPRECATED */
function mobile_login_check($user_id, $user_token){
	wp_send_json_success();
}
	
	function fetchRadio(){
		/*** TODO Check time and send playlist with covers ***/
		$catalogue = file_get_contents(THEMEPATH."inc/radioPlist.json");
		$catalogue = json_decode($catalogue);
		$catalogue = (array) $catalogue;
		
		return array(
						"stream" => site_url("wp-content/uploads/radio/1.mp3"),
						"meta"	 => $catalogue
					);
	}


	// Feed
	function fetch_main_feed(){

		$entries = fetch_home();
			
		foreach ($entries as $index => $entry) {


			$trimmed_description 	= ($entry->post_content !== '') ? wp_trim_words( $entry->post_content, $num_words = 15, $more = '...' ) : NULL;
			$post_thumbnail_id 		= get_post_thumbnail_id($entry->ID);
			$post_thumbnail_url 	= wp_get_attachment_image_src($post_thumbnail_id,'medium');
			$post_thumbnail_url 	= $post_thumbnail_url[0];
			$foto_user = get_user_meta( $entry->author, 'foto_user', TRUE );

			$programa =  wp_get_post_terms($entry->ID, "programa");
			$programa = !empty($programa) ? $programa[0]->name : NULL;
			
			$authors_concat =  "";
			$authors =  wp_get_post_terms($entry->ID, "autor");
			if(!empty($authors))
				$authors_concat = (count($authors) == 1) ? "Con: {$authors[0]->name}" :  "Con: {$authors[0]->name} y {$authors[1]->name}";
			
			$entries_feed['pool'][] = array(
									'ID' 					=> $entry->ID,
									'title' 				=> $entry->post_title,
									'excerpt' 				=> $trimmed_description,
									'content' 				=> $entry->post_content,
									'thumb_url'				=> ($post_thumbnail_url) ? $post_thumbnail_url : NULL,
									'type'					=> $entry->post_type,
									'programa'				=> $programa,
									'authors'				=> $authors_concat,
									$entry->post_type		=> true,
								);
			
		}
		$entries_feed['radio'] = fetchRadio();

		return json_encode($entries_feed);
	}

	function fetch_archive_feed($kind = NULL){
		if($kind == "recent")
			$kind = array("columna");
		$args = array(
					"post_type" 		=> $kind,
					"post_status"		=> "publish",
					"orderby"			=> "date",
					"posts_per_page"	=> 10
				);
		$results = get_posts($args);
		$final = array("pool" => array(), "count" => 0);
		foreach ($results as &$each_result) {
			
			$programa =  wp_get_post_terms($each_result->ID, "programa");
			$programa = !empty($programa) ? $programa[0]->name : NULL;
				
			if($programa){
				$thumb_url = get_the_post_thumbnail_url( $each_result->ID, "medium" )
			}else{
				$thumb_url = get_the_post_thumbnail_url( $each_result->ID, "medium" )
			}

			$authors_concat =  "";
			$authors =  wp_get_post_terms($each_result->ID, "autor");
			if(!empty($authors))
				$authors_concat = (count($authors) == 1) ? "Con: {$authors[0]->name}" :  "Con: {$authors[0]->name} y {$authors[1]->name}";

			$final["pool"][] = array(
									"ID"	=> $each_result->ID,
									"title" => $each_result->post_title,
									"slug" 	=> $each_result->post_name,
									"thumb_url" => get_the_post_thumbnail_url( $each_result->ID, "medium" ),
									"excerpt" => $each_result->post_excerpt,
									'programa'				=> $programa,
									'authors'				=> $authors_concat,
									$each_result->post_type => TRUE
								);
				$each_result->{$each_result->post_type} = TRUE;
		}
		$final['count'] = count($final["pool"]);
		return json_encode($final);
	}


	/**
	 * Get entries feed ordered chronologically
	 * @param $offset
	 */
	function fetch_home($offset = 0){
		
		$args = array(
				'post_type'   		=> array('columna'),
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> 1,
				'orderby'   		=> 'date',
			);
		$query = new WP_Query($args);
		return $query->posts;
	}

	/**
	 * Fetch podcast episodes
	 * @param $podcast_id
	 */
	function fetch_podcast($podcast_id = NULL, $playing = NULL){

		$args = array(
					'post_type' => 'podcast',
					'orderby' 	=> 'date',
					'order' 	=> 'ASC',
					'tax_query' => array(
						array(
							'taxonomy' => 'programa',
							'field' => 'id',
							'terms' => $podcast_id
						 )
					  )
					);
		$podcasts = get_posts($args);
		foreach ($podcasts as $each_podcast) {
			$thumb = wp_get_attachment_image_src( get_post_thumbnail_id($each_podcast->ID), 'medium');
			$cover = !empty($thumb) ? $thumb[0] : NULL;
			$stream = get_post_meta($each_podcast->ID, "_file_url_meta", TRUE);

			$array_temp[] = array(
									"ID" 		=> $each_podcast->ID,
									"title" 	=> $each_podcast->post_title,
									"slug" 		=> $each_podcast->post_name,
									"cover"		=> $cover,
									"stream" 	=> $stream,
									"playing" 	=> ($playing == $each_podcast->ID) ? TRUE : FALSE,
									"date" 		=> date("Y-m-d", strtotime($each_podcast->post_date)),
								);
		}
		
		$return_array  = array (
									"pool" => $array_temp,
									"count" => count($array_temp),
								);

		return $return_array;
	}

	/**
	 * Fetch detailed information of podcast episode
	 * @param $episode_id
	 */
	function fetch_episode($episode_id = NULL){

		$podcast =  get_post($episode_id);
		$podcast_term = wp_get_post_terms($episode_id, "programa");
		$podcast_term = !empty($podcast_term) ? $podcast_term[0] : NULL;
		
		$episodes = fetch_podcast($podcast_term->term_id, $episode_id);

		$authors_concat =  "";
		$authors =  wp_get_post_terms($episode_id, "autor");
		if(!empty($authors))
			$authors_concat = (count($authors) == 1) ? "Con: {$authors[0]->name}" :  "Con: {$authors[0]->name} y {$authors[1]->name}";
		
		$thumb = wp_get_attachment_image_src( get_post_thumbnail_id($episode_id), 'medium');
		$cover = !empty($thumb) ? $thumb[0] : NULL;
		
		$return_array  = array (
									"ID" 			=> $podcast->ID,
									"title" 		=> $podcast->post_title,
									"programa" 		=> $podcast_term->name,
									"programa_id" 	=> $podcast_term->term_id,
									"authors" 		=> $authors_concat,
									"cover" 		=> $cover,
									"date" 			=> date("Y-m-d", strtotime($podcast->post_date)),
									"episode_count" => $podcast_term->count,
									"episodes" 		=> $episodes
								);
		return json_encode($return_array);
	}



	/*
	 * Fetch categories for feed
	 * @param Int $level Defaults to 0 (Use -1 for all levels)
	 * @param Int $limit Defaults to 5
	 * @return JSON pool/count Object
	 */
	function fetch_categories($level = 0, $limit = 5, $offset = 0){
		$return_array = array("count" => 0, "pool" => Array());
		$parent_sent = ($level != -1) ? TRUE : NULL;
		
		$args = array(
						'orderby' 		=> 'count',
						'order'   		=> 'DESC',
						'number'  		=> $limit,
						'offset'  		=> $offset,
						'hide_empty'  	=> FALSE,
						'exclude' 		=> 1
					);
		if($parent_sent)
			$args['parent'] = $level;

		$categories = get_categories( $args );

		foreach ($categories as $each_cat) {
			$return_array['pool'][] = 	array(
											"ID" 			=> $each_cat->term_id,
											"name" 			=> $each_cat->name,
											"slug" 			=> $each_cat->slug,
											"product_count" => $each_cat->count
										);
		}
		$return_array['count'] = count($return_array['pool']);
		return json_encode($return_array);
	}

	/*
	 * Fetch categories tree structure
	 * @uses fetch_categories
	 * @return JSON pool/count object
	 */
	function fetch_categories_tree(){

		$parent_cats = json_decode( fetch_categories(0, 0) , true);		
		foreach ($parent_cats['pool'] as $index => $each_parent) {
			$child_categories = json_decode(fetch_categories($each_parent['ID']), TRUE);
			$parent_cats['pool'][$index]['children'] = $child_categories;
		}
		return json_encode($parent_cats);
	}

	/**
	 * Fetch featured products
	 * @param Int $limit
	 * @return Array Pool/Count array
	 */
	function fetch_featured_products($limit = 4){
		$return_array = array("pool" => array(), "count" => 0);
		$args = array(
						"post_type" 		=> "productos",
						"post_status" 		=> "publish",
						'meta_query' 		=> 	array(
													array(
														'key'     => 'file_featured',
														'value'   => 'on'
													),
												),
						"posts_per_page" 	=> $limit,
					);
		$posts = get_posts($args);
		if($posts){
			foreach ($posts as $index => $each_post) {
				$product_price 			= (get_post_meta($each_post->ID,'precio_producto', true) != '') ? get_post_meta($each_post->ID,'precio_producto', true) : NULL;
				$product_author 		= (get_user_by("id", $each_post->post_author)) ? get_user_by("id", $each_post->post_author) : NULL;

				$designer_brand			= $product_author->data;

				$trimmed_description 	= ($each_post->post_content !== '') ? wp_trim_words( $each_post->post_content, $num_words = 15, $more = '...' ) : NULL;
				$post_thumbnail_id = get_post_thumbnail_id($each_post->ID);
				$post_thumbnail_url = wp_get_attachment_image_src($post_thumbnail_id,'large');
				$post_thumbnail_url = $post_thumbnail_url[0];
				$foto_user = get_user_meta( $designer_brand->ID, 'foto_user', TRUE );
				$return_array['pool'][] = 	array(
												"ID" 	=> $each_post->ID,
												"product_title" 		=> $each_post->post_title,
												'product_description' 	=> $trimmed_description,
												"slug" 					=> $each_post->slug,
												'price'					=> $product_price,
												'thumb_url'				=> ($post_thumbnail_url) ? $post_thumbnail_url : "",
												'designer_brand'		=> array(
																				"ID"   => $designer_brand->ID,
																				"name" => $designer_brand->display_name,
																				"profile_pic" 	=> ($foto_user) ? $foto_user : null,
																			),
												'type'					=> $each_post->post_type,
											);
				$return_array['pool'][$index]['designer_brand']= (empty($return_array['pool'][$index]['designer_brand'])) ? null :  $return_array['pool'][$index]['designer_brand'];
			}
			$return_array['count'] = count($return_array['pool']);
		}
		return $return_array;
	}

	/**
	 * Fetch ME information
	 * @param String $user_login
	 * @return JSON Object
	 */
	function fetch_me_information($user_login  = NULL){

		$user = get_user_by("login", $user_login);
		$userData = get_userdata( $user->ID );

		$foto_user = get_user_meta( $user->ID, 'foto_user', TRUE );
		$first_name = get_user_meta( $user->ID, 'first_name', TRUE );
		$last_name = get_user_meta( $user->ID, 'last_name', TRUE );

	
		$me =   array(
					"ID" 				=> $user->ID,
					"login" 			=> $userData->data->user_login,
					"first_name" 		=> $first_name,
					"last_name" 		=> $last_name,
					"email" 			=> $userData->data->user_email,
					"bio" 				=> $userData->data->description,
					"display_name" 		=> $userData->data->display_name,
					"profile_pic" 		=> ($foto_user) ? $foto_user : null,
					"role" 				=> $user->roles[0],
					"valid_token"		=> "HFJEUUSNNSO(h@rDc0d3d)DJJEHHAGADMNDHS&$86324",
					"categories" 		=> array(),
				);

		return wp_send_json($me);
		
	}

	/**
	 * Fetch product detail information
	 * @param Int $product_id
	 * @return JSON Object
	 */
	function fetch_column_detail($column_id = NULL){
		if(!$column_id)
			return NULL;
		$post =  get_post($column_id);

		$author 				= wp_get_post_terms( $column_id, "autor" );

		$trimmed_description 	= ($post->post_content !== '') ? wp_trim_words( $post->post_content, $num_words = 15, $more = '...' ) : NULL;
		$post_thumbnail_id 	= get_post_thumbnail_id($post->ID);
		$post_thumbnail_url = wp_get_attachment_image_src($post_thumbnail_id,'large');
		$post_thumbnail_url = $post_thumbnail_url[0];
		// $foto_user 			= get_user_meta( $designer_brand->ID, 'foto_user', TRUE );

		$final_array = array(
								"ID" 				=> $post->ID,
								"title" 			=> $post->post_title,
								"content" 			=> $post->post_content,
								"author" 			=> $author[0]->name,
								"slug" 				=> $post->post_name,
								"type" 				=> $post->post_type,
								"thumb_url" 		=> ($post_thumbnail_url) ? $post_thumbnail_url : NULL,
								$post->post_type 	=> true
							);

		$media = get_attached_media( 'image', $post->ID );
		foreach ($media as $each_image) {
			$medium = wp_get_attachment_image_src($each_image->ID, 'large');
			$final_array['gallery']['pool'][]['url'] = $medium[0];
		}		
		return json_encode($final_array);
	} 



	/**
	 * Get a number of random users
	 * @param String $role User role to retrieve
	 * @param Integer $number Number of users to retrieve
	 * @param Integer $exclude User ID to exclude
	 * @return JSON encoded pool-count array 
	 */
	function fetch_randomUsers($role = "maker", $number = 5, $exclude = NULL){

		global $wpdb;
		$exclude_query = ($exclude) ?  " AND users.ID != {$exclude}" : "";
		$users = $wpdb->get_results(
					$wpdb->prepare( 
						"SELECT ID , user_login
							FROM wp_users users
							 INNER JOIN wp_usermeta AS wm on user_id = ID
							   AND wm.meta_key = 'wp_capabilities'
							   AND wm.meta_value LIKE %s
							   {$exclude_query}
							ORDER BY rand() LIMIT %d
						;"
						, '%'.$role.'%'
						, $number
					), ARRAY_A
				);
		foreach ($users as &$each_maker) {
			$each_maker['profile_pic'] = NULL;
			$user_profile = get_user_meta( intval($each_maker['ID']), 'foto_user', TRUE);
			
			if($user_profile != '')
				$each_maker['profile_pic'] = $user_profile;
		}
		return json_encode(array("pool" => $users, "count" => count($users)));
	}


	/**
	 * Fetch user profile, requires authentication
	 * @param Integer $queried_user_id
	 * @param String $logged_user
	 * @return Array / Object
	 */
	function fetch_user_profile($queried_user_id = NULL, $logged_user = NULL){
		if(!$queried_user_id) 
			return json_encode(array("success" => FALSE, "error" => "No user queried"));
		if(!$logged_user){
			global $current_user;
		}else{
			$current_user = get_user_by('slug', $logged_user);
		}
		$final_array = array();
		$user_object 	= get_user_by( 'id', $queried_user_id );
		if(!$user_object)
			wp_send_json_error("No such user in the database, check data and try again");
		$user_data = get_userdata( $user_object->ID );

		$user_firstname 	= get_user_meta($user_object->ID, "first_name", TRUE);
		$user_lastname 		= get_user_meta($user_object->ID, "last_name", TRUE);
		$user_description  	= get_user_meta($user_object->ID, "user_3dbio", TRUE);
		$user_profile  		= get_user_meta($user_object->ID, "foto_user", TRUE);

		$role_prefix = $user_object->roles[0];
		$user_data = array(
							'ID' 			=> $user_object->ID,
							'user_display' 	=> $user_object->display_name,
							'user_login' 	=> get_clean_userlogin($user_object->ID),
							'first_name' 	=> ($user_firstname) ? $user_firstname : NULL,
							'last_name' 	=> ($user_lastname) ? $user_lastname : NULL,
							'nickname' 		=> $user_object->nickname,
							'bio' 			=> wpautop($user_description),
							'profile_pic' 	=> $user_profile,
							'is_'.$role_prefix		=> TRUE
						);
		$final_array['profile'] = $user_data;
		$same_maker = get_posts( array(
										"author" 			=> $user_object->ID,
										"post_type" 		=> "productos",
										"post_status" 		=> "publish",
										"posts_per_page" 	=> -1,
										"orderby" 			=> "date",
									));
		$categories = wp_get_object_terms( $user_object->ID, "user_category");
		if($categories){

			foreach ($categories as $each_usercat) {
				$final_array['profile']['categories']['pool'][] = array(
																"ID"	=> $each_usercat->term_id,
																"name" 	=> $each_usercat->name,
																"slug"	=> $each_usercat->slug
															);
			}
			$final_array['profile']['categories']['count'] = count($final_array['profile']['categories']['pool']);
		}
		if($same_maker){
			foreach ($same_maker as $each_related) {
				$post_thumbnail_id 	= get_post_thumbnail_id($each_related->ID);
				$post_thumbnail_url = wp_get_attachment_image_src($post_thumbnail_id,'thumbnail');
				$post_thumbnail_url = $post_thumbnail_url[0];
				$final_array['same_maker']['pool'][] = array( 
																"ID"			=> $each_related->ID,
																"product_title" => $each_related->post_title,
																"thumb_url"		=> ($post_thumbnail_url) ? $post_thumbnail_url : "",
															);
			}
			$final_array['same_maker']['count'] = count($final_array['same_maker']['pool']);
		}
			
		return json_encode($final_array);
	}

	/**
	 * Fetch user profile, requires authentication
	 * Minimal required version for fast loadind
	 * @param Integer $queried_user_id
	 * @param String $logged_user
	 * @return Array / Object
	 */
	function min_fetch_user_profile($queried_user_id = NULL, $logged_user = NULL){
		if(!$queried_user_id) 
			return json_encode(array("success" => FALSE, "error" => "No user queried"));
		if(!$logged_user){
			global $current_user;
		}else{
			$current_user = get_user_by('slug', $logged_user);
		}
		$final_array = array();
		$user_object 	= get_user_by( 'id', $queried_user_id );
		if(!$user_object)
			wp_send_json_error("No such user in the database, check data and try again");
		$user_data = get_userdata( $user_object->ID );

		$user_firstname 	= get_user_meta($user_object->ID, "first_name", TRUE);
		$user_lastname 		= get_user_meta($user_object->ID, "last_name", TRUE);
		$user_description  	= get_user_meta($user_object->ID, "user_3dbio", TRUE);
		$user_profile  		= get_user_meta($user_object->ID, "foto_user", TRUE);

		$role_prefix = $user_object->roles[0];
		$user_data = array(
							'ID' 			=> $user_object->ID,
							'user_display' 	=> $user_object->display_name,
							'user_login' 	=> get_clean_userlogin($user_object->ID),
							'first_name' 	=> ($user_firstname) ? $user_firstname : NULL,
							'last_name' 	=> ($user_lastname) ? $user_lastname : NULL,
							'nickname' 		=> $user_object->nickname,
							'bio' 			=> $user_description,
							'profile_pic' 	=> $user_profile,
							'is_'.$role_prefix		=> TRUE
						);
		$final_array['profile'] = $user_data;
			
		return json_encode($final_array);
	}


	/**
	 * Fetch a taxonomy detailed info and archive of products
	 * @param Integer $tax_id
	 * @param Integer $limit Product pool limit
	 * @return JSON encoded pool/count Array
	 */
	function fetch_taxonomy_archive($tax_id = NULL, $taxonomy = "category", $limit = NULL){
		$return_array = array();
		$term = get_term_by("id", $tax_id, $taxonomy);
		$args = array(
						"post_type" 	=>	array("podcast", "columna"),
						"post_status" 	=>	"publish",
						"posts_per_page" =>	($limit) ? $limit : -1,
						"tax_query" => array(
											array(
												"taxonomy" => $taxonomy,
												"field"    => "id",
												"terms"    => $tax_id,
											),
										),
					);
		$posts = get_posts($args);
		$return_array = array(
								"ID" 	=> $term->term_id,
								"name" 	=> $term->name,
								"slug" 	=> $term->slug,
								"pool" 	=> array(),
								"count" => 0,
							);
		foreach ($posts as $each_result) {
		
			$trimmed_description 	= ($each_result->post_content !== '') ? wp_trim_words( $each_result->post_content, $num_words = 15, $more = '...' ) : NULL;
			$post_thumbnail_id = get_post_thumbnail_id($each_result->ID);
			$post_thumbnail_url = wp_get_attachment_image_src($post_thumbnail_id,'large');
			$post_thumbnail_url = $post_thumbnail_url[0];

			$programa =  wp_get_post_terms($each_result->ID, "programa");
			$programa = !empty($programa) ? $programa[0]->name : NULL;
			
			$authors_concat =  "";
			$authors =  wp_get_post_terms($each_result->ID, "autor");
			if(!empty($authors))
				$authors_concat = (count($authors) == 1) ? "Con: {$authors[0]->name}" :  "Con: {$authors[0]->name} y {$authors[1]->name}";

			$return_array['pool'][] = 	array(
										'ID' 					=> $each_result->ID,
										'title' 				=> $each_result->post_title,
										'excerpt' 				=> $trimmed_description,
										'content' 				=> $each_result->post_content,
										'thumb_url'				=> ($post_thumbnail_url) ? $post_thumbnail_url : NULL,
										'type'					=> $each_result->post_type,
										'programa'				=> $programa,
										'authors'				=> $authors_concat,
										$each_result->post_type	=> true,
									);

		}
		$return_array['count'] = count($return_array['pool']);
		return json_encode($return_array);
	}

	/**
	 * Fetch a set of 3Dedalo users filtered by category
	 * @param String $category
	 * @param Integer $limit
	 * @return JSON encoded pool/count Array
	 */
	function fetch_users_bycategory($logged_user = NULL, $location = NULL, $category = "printer", $limit = 10, $offset = 0){
		global $wpdb;
		$user = get_user_by("slug", $logged_user);

		$latlong_asking 	= explode(",", $location);
		$asking_latitude 	= $latlong_asking[0];
		$asking_longitude 	= $latlong_asking[1];
		$latlong_maker_Obj 	= new LatLng($asking_latitude, $asking_longitude);
		$where_clause 		= ($category !== 'all') ? " WHERE wp_terms.slug = '%s' " : "";

		$filtered_users = 	$wpdb->get_results(
								$wpdb->prepare(" SELECT * FROM wp_users
												 INNER JOIN wp_terms
												 INNER JOIN wp_term_taxonomy
												  ON wp_term_taxonomy.term_id = wp_terms.term_id
												 INNER JOIN wp_term_relationships
												  ON wp_term_relationships.term_taxonomy_id = wp_term_taxonomy.term_taxonomy_id
												  AND wp_term_relationships.object_id = wp_users.ID
												 {$where_clause}
												 AND wp_users.ID != %d
												LIMIT %d, %d
												;", 
													$category,
													$user->ID,
													$offset,
													$limit 
												)
							);
		$final_array = array();
		foreach ($filtered_users as $each_filtered_user) {
			$latlong_maker = get_user_meta( $each_filtered_user->ID, "latlong_maker", TRUE );
			if($latlong_maker !== ''){
				$exploded 	= explode(",", $latlong_maker);
				$latitude 	= $exploded[0];
				$longitude 	= $exploded[1];
				/*** Calculate distance differential ***/
				$differential = SphericalGeometry::computeDistanceBetween(new LatLng($latitude, $longitude), $latlong_maker_Obj);
				$kms_away = round(($differential/1000),1);

				if($kms_away > 10)
					continue;
					$maker_categories = wp_get_object_terms($each_filtered_user->ID, "user_category");
					
					$final_categories = array();
					foreach ($maker_categories as $each_cat) {
						$final_categories[] = array(
													"ID" 	=> $each_cat->term_id,
													"name" 	=> $each_cat->name,
													"slug" 	=> $each_cat->slug,
													);
					}
					$final_array['pool'][] = array(
													"ID" 	=> $each_filtered_user->ID,
													"name" 	=> $each_filtered_user->display_name,
													"user_login" => $each_filtered_user->user_login,
													"distance" 	 => $kms_away,
													"latitude" 	 => $latitude,
													"longitude"  => $longitude,
													"categories" => $final_categories
												);

			}	
			
		}
		$final_array['count'] = count($final_array['pool']);
		
		return json_encode($final_array);
	}


	/**
	 * Fetch search results
	 * @param String $search_term
	 * @param Integer $limit
	 * @return JSON encoded pool/count Array
	 */
	function search_airelibre($search_term = NULL, $offset = 0){

		global $wpdb;
		$final_array = array();


		$query = "	SELECT DISTINCT wpp.*
									  FROM wp_posts AS wpp
									  LEFT JOIN wp_term_relationships wptrel
									   ON wptrel.object_id = wpp.ID
									  LEFT JOIN wp_term_taxonomy wptax
									   ON wptax.term_taxonomy_id = wptrel.term_taxonomy_id
									  LEFT JOIN wp_terms wpt
									   ON wpt.term_id = wptax.term_id
									WHERE( concat( post_title, post_content ) LIKE '%{$search_term}%' 
									 OR wpt.name LIKE '%{$search_term}%' )
									 AND (post_type = 'podcast' OR post_type = 'columna') 
									 AND post_status = 'publish'
									GROUP BY wpp.ID
									ORDER BY post_date DESC LIMIT $offset, 15
									;
									";

		$results = $wpdb->get_results( $query, OBJECT );

		foreach ($results as $each_result) {
			
			$trimmed_description 	= ($each_result->post_content !== '') ? wp_trim_words( $each_result->post_content, 15, '...' ) : NULL;
			$post_thumbnail_id 		= get_post_thumbnail_id($each_result->ID);
			$post_thumbnail_url 	= wp_get_attachment_image_src($post_thumbnail_id,'medium');
			$post_thumbnail_url 	= $post_thumbnail_url[0];
			$foto_user = get_user_meta( $each_result->author, 'foto_user', TRUE );

			$programa =  wp_get_post_terms($each_result->ID, "programa");
			$programa = !empty($programa) ? $programa[0]->name : NULL;
			
			$authors_concat =  "";
			$authors =  wp_get_post_terms($each_result->ID, "autor");
			if(!empty($authors))
				$authors_concat = (count($authors) == 1) ? "Con: {$authors[0]->name}" :  "Con: {$authors[0]->name} y {$authors[1]->name}";
			
			$final_array['pool'][] = array(
										'ID' 					=> $each_result->ID,
										'title' 				=> $each_result->post_title,
										'excerpt' 				=> $trimmed_description,
										'content' 				=> $each_result->post_content,
										'thumb_url'				=> ($post_thumbnail_url) ? $post_thumbnail_url : "",
										'type'					=> $each_result->post_type,
										'programa'				=> $programa,
										'authors'				=> $authors_concat,
										$each_result->post_type	=> true,
									);
		}
		$final_array['count'] = count($final_array['pool']);
		return $final_array;
	}