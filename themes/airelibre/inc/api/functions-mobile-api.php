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

// Feed
function fetch_main_feed($filter = "all", $offset){

	$entries_feed = array();
	if($filter == "all"){
		$filter_nice = "Cronológico";
		$entries = filter_posts_by( "all" );
	}
	if($filter == "most_popular"){
		$filter_nice = "Más populares";
		$entries = filter_posts_by("popular");
	}
	if($filter == "boosted"){
		$filter_nice = "Recomendados";
		$entries = filter_posts_by("boosted");
	}
	if($filter == "ending"){
		$filter_nice = "Por terminar";
		$entries = filter_posts_by("ending");
	}

	foreach ($entries as $index => $entry) {

		$product_price 			= (get_post_meta($entry->ID,'precio_producto', true) != '') ? get_post_meta($entry->ID,'precio_producto', true) : NULL;
		$product_author 		= (get_user_by("id", $entry->post_author)) ? get_user_by("id", $entry->post_author) : NULL;

		$designer_brand			= $product_author->data;
		$trimmed_description 	= ($entry->post_content !== '') ? wp_trim_words( $entry->post_content, $num_words = 15, $more = '...' ) : NULL;
		$post_thumbnail_id = get_post_thumbnail_id($entry->ID);
		$post_thumbnail_url = wp_get_attachment_image_src($post_thumbnail_id,'large');
		$post_thumbnail_url = $post_thumbnail_url[0];
		$foto_user = get_user_meta( $designer_brand->ID, 'foto_user', TRUE );

		if(!$index){
			$entries_feed['featured'][] = array(
									'ID' 					=> $entry->ID,
									'product_title' 		=> $entry->post_title,
									'product_description' 	=> $trimmed_description,
									'price'					=> $product_price,
									'thumb_url'				=> ($post_thumbnail_url) ? $post_thumbnail_url : "",
									'designer_brand'		=> 	array(
																	"ID"   => $designer_brand->ID,
																	"name" => $designer_brand->display_name,
																	"profile_pic" 	=> ($foto_user) ? $foto_user : null,
																),
									'type'					=> $entry->post_type,
									$entry->post_type		=> true,
									'link_base'				=> ($entry->post_type == 'productos') ? 'detail' : 'post',
								);
			$entries_feed['featured'][$index]['designer_brand'] = (!$designer_brand) ? null :  $entries_feed['featured'][$index]['designer_brand'];
		}else{
			$entries_feed['pool'][] = array(
									'ID' 					=> $entry->ID,
									'product_title' 		=> $entry->post_title,
									'product_description' 	=> $trimmed_description,
									'price'					=> $product_price,
									'thumb_url'				=> ($post_thumbnail_url) ? $post_thumbnail_url : "",
									'designer_brand'		=> array(
																	"ID"   => $designer_brand->ID,
																	"name" => $designer_brand->display_name,
																	"profile_pic" 	=> ($foto_user) ? $foto_user : null,
																),
									'type'					=> $entry->post_type,
									$entry->post_type		=> true,
									'link_base'				=> ($entry->post_type == 'productos') ? 'detail' : 'post',
								);
			$entries_feed['pool'][$index-1]['designer_brand'] = (!$designer_brand) ? null :  $entries_feed['pool'][$index-1]['designer_brand'];
		}
		
	}
	$entries_feed['filter_nice'] = $filter_nice;

	return json_encode($entries_feed);
}


	/**
	 * Get entries feed ordered chronologically
	 * @param $offset
	 */
	function get_chronological($offset = 0){
		
		$today = date('Y-m-d');
		$args = array(
				'post_type'   		=> array('post', 'productos'),
				'post_status' 		=> 'publish',
				'paged'				=> $offset+1,
				'posts_per_page' 	=> 20,
				'orderby'   		=> 'date',
			);
		$query = new WP_Query($args);
		return $query->posts;
	}

	/**
	 * Get filtered event feed
	 * @param $filter
	 * @param $offset
	 */
	function filter_posts_by($filter = "all", $offset = 0){
		
		switch ($filter) {
				case 'all':
					return get_chronological($offset);
					break;
				case 'near':
					return get_near_events_array($offset);
					break;
				case 'boosted':
					return get_boosted_events($offset);
					break;
				case 'popular':
					return get_most_popular($offset);
					break;
				case 'visitors':
					return get_most_visited($offset);
					break;
				case 'ending':
					return get_ending_events($offset);
					break;
				
				default:
				return false;
					break;
			}
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

		$search_brand = -1;
		$search_model = -1;
		$user = get_user_by("login", $user_login);
		$userData = get_userdata( $user->ID );

		$assigned_terms = wp_get_object_terms( $user->ID, 'user_category' );
		$foto_user = get_user_meta( $user->ID, 'foto_user', TRUE );
		$first_name = get_user_meta( $user->ID, 'first_name', TRUE );
		$last_name = get_user_meta( $user->ID, 'last_name', TRUE );
		$printer_brand = get_user_meta( $user->ID, 'printer_brand', TRUE );
		$printer_model = get_user_meta( $user->ID, 'printer_model', TRUE );
		$bio = get_user_meta( $user->ID, 'user_3dbio', TRUE );

		$brand_object = get_term_by("id", intval($printer_brand), "printer-model");

		$model_object = get_term_by("id", intval($printer_model), "printer-model");

		$catalogue = file_get_contents(THEMEPATH."inc/pModels.json");
		$catalogue = json_decode($catalogue);
		$catalogue = (array) $catalogue;

		if($brand_object !== FALSE)
			$search_brand = array_search($brand_object->name, array_keys($catalogue), TRUE);

		if($model_object !== FALSE)
			$search_model = intval(array_search($model_object->name, $catalogue[$brand_object->name]));

		$me =   array(
					"ID" 				=> $user->ID,
					"login" 			=> $userData->data->user_login,
					"first_name" 		=> $first_name,
					"last_name" 		=> $last_name,
					"email" 			=> $userData->data->user_email,
					"bio" 				=> $bio,
					"printer_brand" 	=> $printer_brand,
					"printer_model" 	=> $printer_model,
					"cat_printer_brand" => $search_brand,
					"cat_printer_model" => $search_model,
					"display_name" 		=> $userData->data->display_name,
					"profile_pic" 		=> ($foto_user) ? $foto_user : null,
					"role" 				=> $user->roles[0],
					"valid_token"		=> "HFJEUUSNNSO(h@rDc0d3d)DJJEHHAGADMNDHS&$86324",
					"categories" 		=> array(),
				);

		if($printer_model !== -1)
			$me['models_already'] =	$catalogue[$brand_object->name];

		foreach ($assigned_terms as $each_term){
			$me['categories'][] =   array(
										"ID" => $each_term->term_id,
										"name" => $each_term->name,
										"slug" => $each_term->slug
									);
			$me['is_'.$each_term->slug] = true;
		}

		return wp_send_json($me);
		
	}


	/**
	 * Fetch search page composite layout
	 * @param String $user_logged
	 * @return JSON Object
	 */
	function fetch_search_composite($user_logged = NULL){

		$previous = array("pool" => array(), "count" => 0);
		/* Get categories from another endpoint */
		$categories = file_get_contents(site_url('rest/v1/content/enum/categories/'));
		$categories = json_decode($categories);
		/* Fetch 4 featured products */
		$featured 	= fetch_featured_products();
		/* Get previous searches */
		// if($logged){
		// 	// $previous 	= fetch_previous_searches();
		// }
		$composite = array(
									"featured" 		=> $featured,
									"previous" 		=> $previous,
									"categories" 	=> $categories,
							);

		return json_encode($composite);
	}

	/**
	 * Fetch product detail information
	 * @param Int $product_id
	 * @return JSON Object
	 */
	function fetch_product_detail($product_id = NULL){
		if(!$product_id)
			return NULL;
		$post =  get_post($product_id);
		
		$product_author 		= (get_user_by("id", $post->post_author)) ? get_user_by("id", $post->post_author) : NULL;
		$designer_brand			= $product_author->data;

		$trimmed_description 	= ($post->post_content !== '') ? wp_trim_words( $post->post_content, $num_words = 15, $more = '...' ) : NULL;
		$post_thumbnail_id 	= get_post_thumbnail_id($post->ID);
		$post_thumbnail_url = wp_get_attachment_image_src($post_thumbnail_id,'large');
		$post_thumbnail_url = $post_thumbnail_url[0];
		$foto_user 			= get_user_meta( $designer_brand->ID, 'foto_user', TRUE );
		$product_price 		= (get_post_meta($post->ID,'precio_producto', true) != '') ? get_post_meta($post->ID,'precio_producto', true) : NULL;
		$info_tecninca		= get_post_meta($post->ID, 'info_tecninca', TRUE);
		$notas_tecnicas		= wpautop(get_post_meta($post->ID, 'notas_tecnicas', TRUE));
		$printer_type		= get_post_meta($post->ID, 'printer_type', TRUE);
		$supports_rafts		= get_post_meta($post->ID, 'supports_rafts', TRUE);
		$infill				= get_post_meta($post->ID, 'infill', TRUE);
		$resolution			= get_post_meta($post->ID, 'resolution', TRUE);
		$file_for_download 	= get_post_meta($post->ID, 'file_for_download', true);
		$download_count 	= get_post_meta($post->ID, 'download_count', true);
		
		$times_viewed 		= get_post_meta($post->ID, 'times_viewed', true);
		$times_viewed 		= ($times_viewed != "") ? intval($times_viewed) : 0;
		$times_viewed++;
		update_post_meta($post->ID, 'times_viewed', $times_viewed);
		
		$tags = wp_get_post_tags( $post->ID );
		$design_tools = get_the_terms( $post->ID, 'design-tools' );
		
		$final_array = array(
								"ID" 				=> $post->ID,
								"product_title" 	=> $post->post_title,
								"product_description" => $post->post_content,
								"product_price" 	=> $product_price,
								"slug" 				=> $post->post_name,
								"type" 				=> $post->post_type,
								"thumb_url" 		=> ($post_thumbnail_url) ? $post_thumbnail_url : "",
								"has_file" 			=> ($file_for_download) ? TRUE : FALSE,
								"download_count" 	=> ($download_count == '') ? 0 : $download_count,
								"times_viewed" 		=> $times_viewed,
								"gallery" 			=> array(),
								"by_same_maker"		=> array(),
								"tags"				=> array(),
								"design_toos"		=> array(),
								$post->post_type 	=> true,
								"designer_brand" 	=>  array(
															"ID"   => $designer_brand->ID,
															"name" => $designer_brand->display_name,
															"profile_pic" 	=> ($foto_user) ? $foto_user : null,
														),
								"technical"			=> array(
															"info" 	=> wpautop($info_tecninca),
															"notes" => $notas_tecnicas,
														),
								"printer"			=> array(
															"type" 	=> ($printer_type !== '') ? $printer_type : NULL,
															"rafts" => ($supports_rafts !== '') ? $supports_rafts : NULL,
															"infill" => ($infill !== '') ? $infill : NULL,
															"resolution" => ($resolution !== '') ? $resolution : NULL,
														),
							);

		$media = get_attached_media( 'image', $post->ID );
		foreach ($media as $each_image) {
			$medium = wp_get_attachment_image_src($each_image->ID, 'large');
			$final_array['gallery']['pool'][]['url'] = $medium[0];
		}
		$same_maker = get_posts(array(
									"author" 			=> $designer_brand->ID,
									"post_type" 		=> "productos",
									"post_status" 		=> "publish",
									"posts_per_page" 	=> 4,
									"exclude" 			=> $product_id,
									"orderby" 			=> "rand",
								));

		if($same_maker)
			foreach ($same_maker as $each_related) {
				$post_thumbnail_id 	= get_post_thumbnail_id($each_related->ID);
				$post_thumbnail_url = wp_get_attachment_image_src($post_thumbnail_id,'thumbnail');
				$post_thumbnail_url = $post_thumbnail_url[0];
				$final_array['by_same_maker']['pool'][] = array( 
																"ID"			=> $each_related->ID,
																"product_title" => $each_related->post_title,
																"thumb_url"		=> ($post_thumbnail_url) ? $post_thumbnail_url : "",
															);
			}
		if($tags){
			
			foreach ($tags as $each_tag) {
				$final_array['tags']['pool'][] = array( 
																"ID"		=> $each_tag->term_id,
																"name" 		=> $each_tag->name,
																"slug"		=> $each_tag->slug,
															);
			}
			$final_array['tags']['count'] = count($final_array['tags']['pool']);
		}
		if($design_tools){
			
			foreach ($design_tools as $each_dt) {
				$final_array['design_tools']['pool'][] = array( 
																"ID"		=> $each_dt->term_id,
																"name" 		=> $each_dt->name,
																"slug"		=> $each_dt->slug,
															);
			}
			$final_array['design_tools']['count'] = count($final_array['design_tools']['pool']);
		}

		$final_array['gallery']['count'] = count($final_array['gallery']['pool']);
		$final_array['by_same_maker']['count'] = count($final_array['by_same_maker']['pool']);
		return json_encode($final_array);
	} 


	/**
	 * Fetch post detail information
	 * @param Int $product_id
	 * @return JSON Object
	 */
	function fetch_post_detail($post_id = NULL){
		if(!$post_id)
			return NULL;
		$post 					= get_post($post_id);
		$post_author 			= (get_user_by("id", $post->post_author)) ? get_user_by("id", $post->post_author) : NULL;
		$post_editor			= $post_author->data;

		$trimmed_description 	= ($post->post_content !== '') ? wp_trim_words( $post->post_content, $num_words = 15, $more = '...' ) : NULL;
		$post_thumbnail_id 		= get_post_thumbnail_id($post->ID);
		$post_thumbnail_url 	= wp_get_attachment_image_src($post_thumbnail_id,'large');
		$post_thumbnail_url 	= $post_thumbnail_url[0];
		$foto_user 				= get_user_meta( $post_editor->ID, 'foto_user', TRUE );
		$post_date 				= get_the_date('Y-m-d', $post->ID);
		
		
		$final_array = array(
								"post_title" 		=> 	$post->post_title,
								"post_content" 		=> 	$post->post_content,
								"post_date" 		=> 	date( "F j, Y", strtotime($post->post_date) ),
								"slug" 				=> 	$post->post_name,
								"type" 				=> 	$post->post_type,
								"thumb_url" 		=> 	($post_thumbnail_url) ? $post_thumbnail_url : "",
								$post->post_type 	=> 	true,
								"post_editor" 		=>  array(
															"ID"   	=> $post_editor->ID,
															"name" 	=> $post_editor->display_name,
															"profile_pic"	=> ($foto_user) ? $foto_user : "",
														),
							);
		
		return json_encode($final_array);
	} 

	
	/**
	 * Fetch user dashboard
	 * Contains categories and users available to follow marked according status
	 * @param String $user_login
	 * @return JSON Object
	 */
	function fetch_user_dashboard($user_login = NULL){
		$final_array = array();
		/* Get categories and random makers from another endpoint */
		$categories = file_get_contents(site_url('rest/v1/content/enum/categories/-1/14'));
		$categories = json_decode($categories);
		$makers = file_get_contents(site_url("rest/v1/{$user_login}/content/users/maker/10/"));
		$makers = json_decode($makers);
		$user = get_user_by("slug", $user_login);

		if($categories->count)
			foreach ($categories->pool as $index => $each_cat) {
				$final_array['categories']['pool'][] = $each_cat;
				$final_array['categories']['pool'][$index]->followed = is_following_cat($user_login, $each_cat->ID);
			}
		$final_array['categories']['count'] = $categories->count;

		if($makers->count)
			foreach ($makers->pool as $index => $each_user) {
				$final_array['makers']['pool'][] = $each_user;
				$final_array['makers']['pool'][$index]->followed = is_following_user($user->ID, $each_user->ID);
			}
		$final_array['makers']['count'] = $makers->count;

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
						"post_type" 	=>	"productos",
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
		$products = get_posts($args);
		$return_array = array(
								"ID" 	=> $term->term_id,
								"name" 	=> $term->name,
								"slug" 	=> $term->slug,
								"pool" 	=> array(),
								"count" => 0,
							);
		foreach ($products as $each_result) {
			$product_price 			= (get_post_meta($each_result->ID,'precio_producto', true) != '') ? get_post_meta($each_result->ID,'precio_producto', true) : NULL;
			$product_author 		= (get_user_by("id", $each_result->post_author)) ? get_user_by("id", $each_result->post_author) : NULL;

			$designer_brand			= $product_author->data;

			$trimmed_description 	= ($each_result->post_content !== '') ? wp_trim_words( $each_result->post_content, $num_words = 15, $more = '...' ) : NULL;
			$post_thumbnail_id = get_post_thumbnail_id($each_result->ID);
			$post_thumbnail_url = wp_get_attachment_image_src($post_thumbnail_id,'large');
			$post_thumbnail_url = $post_thumbnail_url[0];
			$foto_user = get_user_meta( $designer_brand->ID, 'foto_user', TRUE );
			$return_array['pool'][] = 	array(
												"ID" 					=> $each_result->ID,
												"product_title" 		=> $each_result->post_title,
												"product_description" 	=> $trimmed_description,
												"slug" 					=> $each_result->slug,
												"price"					=> $product_price,
												"thumb_url"				=> ($post_thumbnail_url) ? $post_thumbnail_url : "",
												"designer_brand"		=> array(
																				"ID"   => $designer_brand->ID,
																				"name" => $designer_brand->display_name,
																				"profile_pic" 	=> ($foto_user) ? $foto_user : null,
																			),
												"type"					=> $each_result->post_type,
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

	function ec_mail_name( $email ){
		return '3Dedalo'; // new email name from sender.
	}
	add_filter( 'wp_mail_from_name', 'ec_mail_name', 0 );

	function ec_mail_from ($email ){
		return 'info@3dedalo.org'; // new email address from sender.
	}
	add_filter( 'wp_mail_from', 'ec_mail_from', 0 );

	/**
	 * Set a printer job to another user
	 * @param Integer $user_id
	 * @param Integer $ref_id
	 * @param Integer $printer_id
	 * @return Bool $result
	 */
	function set_printer_job($user_id = NULL, $ref_id = NULL, $printer_id = NULL){
		
		$printer_user = get_user_by("id", $printer_id);
		$download_count = get_post_meta($ref_id, 'download_count', true);
		$download_count++;
		update_post_meta($ref_id, "download_count", $download_count);
		
		$mortal_user = get_user_by("id", $user_id);
		$model_file_url = get_post_meta($ref_id, 'file_for_download', true);

		$headers = array('Content-Type: text/html; charset=UTF-8', 'From: 3Dedalo <info@3dedalo.org>');
		/*** Send mail to printer ***/
		$to = $printer_user->user_email;
		$subject = "You have a pending 3D printing job";
		$message = "<h2>Hello {$printer_user->display_name},</h2><p>{$mortal_user->display_name} has requested a 3D printing job of the following model. <br>Please click the link to download file: <a href='{$model_file_url}'>$model_file_url</a> </p>";
		$result = wp_mail($to, $subject, $message, $headers);

		/*** Send mail to mortal user ***/
		$to = $mortal_user->user_email;
		$subject = "Your recently bought 3D model";
		$message = "<h2>Hello {$mortal_user->display_name},</h2><p>{$printer_user->display_name} has been informed about your 3D printing job, you will receive a notification when the printer sets an estimated time for your piece to be ready. <br>Please click the link to download file: <a href='{$model_file_url}'>$model_file_url</a> </p>";

		$result = wp_mail($to, $subject, $message, $headers);
		return true;
	}


	



// -----------------------------------------------------------------------------------------------


/*
 * Get single event info
 * @param Int $event_id
 * @param String $user_login
 */
function get_event_single($event_id, $user_login){
	$logged_user 	= get_user_by('slug', $user_login);
	if(!$logged_user) 
		return wp_send_json_error('Not a valid user or you don\'t have enough permissions');
	return json_encode(get_event_stdinfo($event_id, TRUE, $logged_user));
}

/*
 * Get gallery attachments for a event
 * @param Int $event_id
 * @param Int $limit
 * @param String $size
 */
function get_event_gallery($event_id, $limit = 5, $size = 'gallery_mobile'){
	$feat_id = get_post_thumbnail_id($event_id);
	$args = array(
	   'post_type' 		=> 'attachment',
	   'post_status' 	=> 'any',
	   'post_parent' 	=> $event_id,
	   'exclude'		=> $feat_id,
	   'posts_per_page' => $limit
	);

	$attachments = get_posts( $args );
	$images = array();
	if ( $attachments ) {
		foreach ( $attachments as $attachment ) {

			$img_url = _get_attachment_url($attachment->ID, $size);
			$this_array = array(
									'src' => $img_url[0],
									'w' => ($size == 'thumbnail') ? 150 : 350,
									'h' => ($size == 'thumbnail') ? 150 : 240,
								);
			$images['items'][]  = $this_array;
		}
	}
	return json_encode($images);
}

/*
 * Get attachments uploaded by a user
 *
 * @param String $user_login
 * @param Int $limit
 * @param String $size
 */
function get_user_gallery($user_login, $limit = 5, $size = 'gallery_mobile'){
	$user_object = get_user_by("slug", $user_login);
	if(!$user_object)
		wp_send_json_error("No such user or not enough permissions");
	$args = array(
	   'post_type' 		=> 'attachment',
	   'post_status' 	=> 'private',
	   'author' 		=> $user_object->ID,
	   'posts_per_page' => $limit
	);

	$attachments = get_posts( $args );
	$images = array();
	if ( $attachments ) {
		foreach ( $attachments as $attachment ) {

			$img_url = _get_attachment_url($attachment->ID, $size);
			$this_array = array(
									'src' => $img_url[0],
									'w' => ($size == 'thumbnail') ? 150 : 350,
									'h' => ($size == 'thumbnail') ? 150 : 240,
								);
			$images['items'][]  = $this_array;
		}
		$images['user_nicename'] = get_the_author_meta( 'display_name', $user_object->ID );
	}
	return json_encode($images);
}



/**
 * Fetch user notifications pool
 * @param String $user_login
 * @param Integer $limit
 * @return JSON encoded Pool/Count Array
 */
function get_notifications_pool($user_login, $limit = 10){

	$user = get_user_by('login', $user_login);
	$data = array();
	$data['count'] = get_count_alertas_user($user);
	$data['pool'] = get_alertas_user($limit, $user);
	foreach ($data['pool'] as $key => $notification) {
		$notification->estatus = intval($notification->estatus);
		$user_object = get_user_by('id',  $notification->user_id );
		if(!$user_object) continue;
		$notification->user_login = $user_object->user_login;
		$notification->event_title = get_the_title($notification->objeto_id);
		if($notification->tipo == 'recomendo') 	$notification->recommend = true;
		if($notification->tipo == 'voto') 		$notification->vote = true;
		if($notification->tipo == 'siguiendo') 	$notification->follow = true;
		if($notification->tipo == 'friend_fb') 	$notification->friend_fb = true;
	}
	$data['user_login'] = $user_login;
	$user_avatar 	= museo_get_profilepic_url($user->ID);
	$data['profile_pic'] = ( isset($user_avatar) AND $user_avatar !== '') ? $user_avatar : null;
	return json_encode( $data );
}

function recomend_event_to_user($user){
	//TO DO: Return falsie value if not done
	ajax_museografo_recomendar($user);
}
/*
 * Update user profile information
 * @param String $user_login
 * @param Object $args
 * @return Boolean
 */
function update_user_profile($user_login, $args){
	$user = get_user_by("slug", $user_login);
	if(!$user)
		wp_send_json_error();

	$userdata = array(
						"ID" 			=> $user->ID,
						"first_name" 	=> $args->user_first_name,
						"last_name" 	=> $args->user_last_name,
						"user_email" 	=> $args->user_email,
				);

	if($user_updated = wp_update_user($userdata)){

		$object_terms = array();
		/*** Updating extra meta information ***/
		update_user_meta( $user_updated, 'user_3dbio', $args->user_bio );
		if($args->become_printer == 'true'){
			$pterm = get_term_by("slug", "printer", "user_category");
			$object_terms[] = $pterm->term_id;
			/*** Saving hardware specifics ***/
			if($args->just_modified){
				$catalogue = file_get_contents(THEMEPATH."inc/pModels.json");
				$catalogue = json_decode($catalogue);
				$catalogue = (array) $catalogue;
				$keys = array_keys($catalogue);

				$brand_index = intval($args->printer_brand);
				$brand_name = $keys[$brand_index];
				$brand_object = get_term_by("name", $brand_name, "printer-model");

				$model_index = $args->printer_model;
				$model_name = $catalogue[$brand_name][$model_index];
				$model_object = get_term_by("name", $model_name, "printer-model");
				
				$object_ptr_terms =  array($brand_object->term_id, $model_object->term_id);
				
				update_user_meta($user->ID, "printer_brand", $brand_object->term_id);
				update_user_meta($user->ID, "printer_model", $model_object->term_id);
			}
		}
		if($args->become_scanner == 'true'){
			$sterm = get_term_by("slug", "scanner", "user_category");
			$object_terms[] = $sterm->term_id;
		}
		wp_set_object_terms( $user->ID, $object_terms, 'user_category', false );
		clean_object_term_cache($user->ID, 'user_category');
		
		wp_send_json_success();
	}
	wp_send_json_error();
}

/* 
 * Get a random categories feed
 * @param Object $user
 * @param Int $number = 4
 * @return JSON success plus data array
 */
function get_rand_categories_feed($user, $number = 4){

	global $wpdb;

	$query_ids = $wpdb->get_results(
			$wpdb->prepare( 
				"SELECT tt.term_id FROM wp_term_taxonomy AS tt
				  INNER JOIN wp_terms AS wpt
					ON tt.term_id = wpt.term_id
						WHERE taxonomy = 'category'
						AND tt.term_id != '1'
						ORDER BY rand() LIMIT %d
					;"
				, $number
			), ARRAY_N
		);
	
	$concat = "";
	foreach ($query_ids as $id) 
		$concat .= $id[0].",";
	$args = array(
		'type'				=> 'post',
		'hide_empty' 		=> 0,
		'include'			=> $concat,
		'exclude'			=> '1'

	);
	$cat_feed = array();
	$categories = get_categories($args);
	$seguidas = museografo_categorias_seguidas($user);
	$parent_name = NULL;
	
	foreach ($categories as $key => $category) {
		$thumb = get_tax_meta($category->term_id, 'mg_imagen_categoria');
		$thumb = ($thumb !== '') ? $thumb : array('id' =>'', 'src' =>'');
		$cat_object = get_term_by('id', $category->term_id, 'category');
		
		$cat_feed['pool'][] = array(
						'ID' 				=> $category->term_id,
						'title' 			=> $category->name,
						'slug' 				=> $category->slug,
						'thumbnail' 		=> $thumb['src'],
						'has_description' 	=> ($cat_object->description) ? TRUE : FALSE,
						'is_following'		=> false
					);
		if(in_array($category->term_id, $seguidas)){
			$cat_feed['pool'][$key]['is_following'] = true;
		}
	}
	if(!empty($cat_feed)) wp_send_json_success($cat_feed);
	wp_send_json_error();
}

/* 
 * Get the categories feed by level
 * @param Object $user
 * @param Int $level The parent level of categories to retrieve
 * @param Mixed $exclude Id(s) to exclude from query
 * @return JSON success plus data array
 */
function get_categories_feed($user, $level, $exclude = NULL, $cached = TRUE){
	$args = array(
		'parent' 		=> $level,
		'type' 			=> 'post',
		'hide_empty' 	=> 0,
		'exclude' 		=> array(1, $exclude)
	);
	$cat_feed = array();
	$cat_feed_try = get_transient( 'cat_feed_tree_'.$level );
	
	if ( !$cached OR false === $cat_feed_try ) {
		$categories = get_categories($args);
		$seguidas = museografo_categorias_seguidas($user);
		$parent_name = NULL;

		if($level != 0) {
			$cat_object = get_term_by('id', $level, 'category');
			if(!$cat_object) wp_send_json_error("No such category id");
			$parent_name = $cat_object->name;
			$parent_thumb = get_tax_meta($cat_object->term_id, 'mg_imagen_categoria');
			$parent_thumb = ($parent_thumb !== '') ? $parent_thumb : array('id' =>'', 'src' =>'');

			$cat_feed['parent'] = $level;
			$cat_feed['parent_name'] = $parent_name;
			$cat_feed['parent_thumbnail'] = $parent_thumb['src'];
			$cat_feed['parent_is_following'] = is_user_following_category($user->ID, $level);
		}
		foreach ($categories as $key => $category) {
			$thumb = get_tax_meta($category->term_id, 'mg_imagen_categoria');
			$thumb = ($thumb !== '') ? $thumb : array('id' =>'', 'src' =>'');
			$cat_object = get_term_by('id', $category->term_id, 'category');

			$cat_feed['pool'][] = array(
							'ID' 				=> $category->term_id,
							'title' 			=> $category->name,
							'slug' 				=> $category->slug,
							'thumbnail' 		=> $thumb['src'],
							'has_description' 	=> ($cat_object->description) ? TRUE : FALSE,
							'is_following'		=> is_user_following_category($user->ID, $category->term_id)
						);

		}
		set_transient( 'cat_feed_tree_'.$level , $cat_feed, 12 * HOUR_IN_SECONDS );
		if(!empty($cat_feed)) wp_send_json_success($cat_feed);
	}
	$cat_feed = $cat_feed_try;
	if(!empty($cat_feed)) wp_send_json_success($cat_feed);
	wp_send_json_error();
}

/*
 * Get explore json content for a category
 * TO DO: Analyze data and explore "campaigns" 
 * @param Int Category ID
 * @param String $logged_user
 * @see EXPLORE methods
 * TO DO: Make Explore stuff a class on it's own
 * TO DO: Keep getting children recursively if has_description is FALSE
 */
function get_category_detail($category_id, $logged_user){
	$user = get_user_by('slug', $logged_user);
	if(!$user)
		wp_send_json_error("Not a valid user or not enough permissions");
	if(!$category_id)
		wp_send_json_error("Not a valid category identifier");
	$seguidas = museografo_categorias_seguidas($user);
	$inarray = in_array($category_id, $seguidas) ? TRUE : FALSE;
	$cat_object = get_term_by('id', $category_id, 'category');
	$thumb = get_tax_meta($category_id, 'mg_imagen_categoria');
	$thumb = ($thumb !== '') ? $thumb : array('id' =>NULL, 'src' =>NULL);

	return array(
					'ID' 				=> $category_id,
					'title' 			=> $cat_object->name,
					'slug' 				=> $cat_object->slug,
					'thumbnail' 		=> $thumb['src'],
					'is_following' 		=> $inarray,
					'description' 		=> $cat_object->description,
					'has_description' 	=> ($cat_object->description) ? TRUE : FALSE,
					'explore_venues' 	=>  explore_get_venues($category_id, $logged_user, 4),
					'explore_artists' 	=>  explore_get_artists($category_id, $logged_user, 4),
					'explore_events' 	=>  explore_get_events($category_id, $logged_user, 2)
				);
}


/*
 * EXPLORE VENUES
 * Get recommended venues based on a category
 * @param Int $category_id
 * @param Obj $logged_user
 * @param Int $number The number of posts to be retrieved
 */
function explore_get_venues($category_id, $logged_user, $limit = 4){
	$args = array(
			'number' => $limit,
			'meta_query' => array(
					'relation' => 'AND',
					array(
						'key' => 'venue_categories',
						'value' => $category_id,
						'compare' => 'LIKE'
					),
					array(
						'key' => 'wp_capabilities',
						'value' => 'venue',
						'compare' => 'LIKE'	
					)
				)
		);

	$venues = get_users($args);
	$return_array = array();
	foreach ($venues as $each_venue){
		$return_array[] = array(
									'ID' => $each_venue->ID,
									'slug' => get_clean_userlogin($each_venue->ID),
									'name' => $each_venue->display_name,
									'thumbnail' => museo_get_profilepic_url($each_venue->ID),
								);
	}
	return $return_array;
}

/*
 * EXPLORE ARTISTS
 * Get recommended artists based on a category
 * @param Int $category_id
 * @param Obj $logged_user
 */
function explore_get_artists($category_id, $logged_user, $limit = 4){
	$args = array(
			'number' => $limit,
			'meta_query' => array(
					'relation' => 'AND',
					array(
						'key' => 'venue_categories',
						'value' => $category_id,
						'compare' => 'LIKE'
					),
					array(
						'key' => 'wp_capabilities',
						'value' => 'artista',
						'compare' => 'LIKE'	
					)
				)
		);

	$artists = get_users($args);
	$return_array = array();
	foreach ($artists as $each_artist){
		$return_array[] = array(
									'ID' 	=> $each_artist->ID,
									'slug' 	=> get_clean_userlogin($each_artist->ID),
									'name' 	=> $each_artist->display_name,
									'thumbnail' => museo_get_profilepic_url($each_artist->ID),
								);
	}
	return $return_array;
}

/*
 * EXPLORE EVENTS
 * Get recommended events based on a category
 * @param Int $category_id
 * @param Obj $logged_user
 * @param Int $numberposts Default is 2
 */
function explore_get_events($category_id, $logged_user, $limit = 2){
	$args = array(
					'post_type' 	=> 'eventos',
					'post_status' 	=> 'publish',
					'posts_per_page' => $limit,
					'category__in' 	=> $category_id,
				);
	$queried_events = get_posts($args);
	
	$event_array = array();
	foreach ($queried_events as $event) {
	
		$event_array[] = get_event_stdinfo($event->ID, FALSE, $logged_user);
	}
	return $event_array;
}

/*
 * Get the standarized JSON for a single event
 * get_event_stdinfo()
 * 
 * @param Int $event_id
 * @param Boolean $complete If set to false only an excerpt of the event is retrieved
 * @param Object $logged_user Logged user object
 * @return Array $event_info
 */
function get_event_stdinfo($event_id, $complete = TRUE, $logged_user){
	$event_object 	= get_post($event_id);
	if(!$event_object OR $event_object->post_type !== 'eventos') 
		return wp_send_json_error('Event doesn\'t exist, check ID');
	$user_obj 	= get_user_by('slug', get_clean_userlogin($logged_user->ID));
	if(!$user_obj) 
		return wp_send_json_error('Not a valid user or you don\'t have enough permissions');
	$info_array = array();
	$ID_venue 		= get_post_meta($event_object->ID ,'mg_venue_id', TRUE);
	$thumb_url 		= _get_attachment_url( get_post_thumbnail_id($event_object->ID), 'gallery_mobile' );
	$type 			= array_values(wp_get_post_terms( $event_object->ID, 'tipo-de-evento' ));
	$event_address  = (get_post_meta($event_object->ID,'mg_evento_direccion', true) !== '') 
							? get_post_meta($event_object->ID,'mg_evento_direccion', true) 
							: get_user_meta($ID_venue, 'direccion', TRUE);
	$event_address = ($event_address == '') ? NULL : $event_address;
	$event_latlong  = (get_post_meta($event_object->ID,'mg_evento_latlong', true) !== '') 
							? get_post_meta($event_object->ID,'mg_evento_latlong', true) 
							: get_user_meta($ID_venue, 'latlong', TRUE);
	$event_latlong = ($event_latlong == '') ? NULL : $event_latlong;
	$info_array = array(
							'ID' 				=> $event_object->ID,
							'event_title' 		=> $event_object->post_title,
							'event_slug' 		=> $event_object->post_name,
							'event_description' => wpautop( wp_trim_words($event_object->post_content), $num_words = 15, $more = '...' ),
							'event_thumbnail' 	=> $thumb_url[0],
							'event_type'	 	=> (!empty($type)) ? $type[0]->name : null,
							'venue_id' 			=> $ID_venue,
							'venue' 			=> get_the_author_meta( 'display_name', $ID_venue ),
							'venue_avatar' 		=> museo_get_profilepic_url($ID_venue),
							'date_start' 		=> fecha_inicio_evento($event_object->ID),
							'date_end' 			=> fecha_fin_evento($event_object->ID),
							'latlong' 			=> $event_latlong,
							'address' 			=> $event_address,
							'scheduled' 		=> ( in_array( $event_object->ID, museografo_eventos_agendados($logged_user) ) ) ? true : false,
							'scheduled_count' 	=> event_get_scheduled_count($event_object->ID),
							'attended' 			=> ( in_array( $event_object->ID, get_attended_events($logged_user) ) ) ? true : false
						);
	if(!$complete) return $info_array;
	
	
	$category_array = wp_get_post_terms( $event_object->ID, 'category' );
	$return_categories = array();
	if(!empty($category_array))
		foreach ($category_array as $each_category)
			$return_categories[] = array(
										'ID'   => $each_category->term_id,
										'name' => $each_category->name,
										'slug' => $each_category->slug,
										'is_last_of_branch' => (!empty($each_category->description)) ? TRUE : FALSE 
									);

	$artists_array  = get_event_artists( $event_object->ID);
	$return_artists = array();

	if(!empty($artists_array))
		foreach ($artists_array as $each_artist)
			$return_artists[] = array(
										'name' => $each_artist->name,
										'slug' => $each_artist->slug
									);
	unset($info_array['event_description']);
	$complementary_array = array(
							'event_uri' 		=> get_permalink($event_object->ID),
							'event_author' 		=> $event_object->post_author,
							'event_description' => wpautop($event_object->post_content),
							'admision' 			=> (get_post_meta($event_object->ID,'mg_costo',true)) ? get_post_meta($event_object->ID,'mg_costo',true) : NULL,
							'artists' 			=> (!empty($return_artists)) ? $return_artists : NULL,
							'categories' 		=> (!empty($return_categories)) ? $return_categories : NULL
					);
	return array_merge($info_array, $complementary_array);
}






function retrieve_user_followers($queried_login, $logged_name = NULL, $type = 'suscriptor'){
	if(!$logged_name){
		global $current_user;
	}else{
		$current_user = get_user_by('slug', $logged_name);
	}
	$queried = get_user_by('slug', $queried_login);
	$follower_ids = get_user_followers($queried->ID, $type);

	$type_label = ($type == 'suscriptor' 
						OR $type == 'subscriber'
						OR $type == 'administrador'
						OR $type == 'administrator') ? 'museografo' : $type;

	$user_list = array();
	foreach ($follower_ids as $each_id) {
		$temp_user = get_user_by('id', $each_id);
		$user_thumb = museo_get_profilepic_url($temp_user->ID);
		$user_role = ($temp_user->roles[0] == 'suscriptor' 
						OR $temp_user->roles[0] == 'subscriber'
						OR $temp_user->roles[0] == 'administrador'
						OR $temp_user->roles[0] == 'administrator') ? 'usuario' : $temp_user->roles[0];
		$role_prefix = $temp_user->roles[0];
		if($role_prefix !== 'venue' AND $role_prefix !== 'artista')
			$role_prefix = 'suscriptor';

		$user_list[] = array(
							'ID' 				=> $temp_user->ID,
							'user_login' 		=> $temp_user->user_login,
							'user_display_name' => $temp_user->display_name,
							'user_role' 		=> $user_role,
							'user_thumbnail' 	=> ( isset($user_thumb) AND $user_thumb !== '') ? $user_thumb : NULL,
							'is_following'		=> ($current_user) ? intval(checa_si_sigue_el_usuario($current_user->ID , $temp_user->ID)) : 'undefined',
							'user_followers'	=> checa_total_followers($temp_user->ID),
							'role_prefix'		=> $role_prefix,
							'is_'.$user_role    => TRUE
						);
	}
	$array_return = array(
							'label' => $type_label.'s',
							'queried_user_display_name' => $queried->display_name,
							'pool'	=> $user_list
						);
	wp_send_json_success($array_return);
}

function retrieve_user_followees($queried_login, $logged_name = NULL, $type = 'suscriptor'){
	if(!$logged_name){
		global $current_user;
	}else{
		$current_user = get_user_by('slug', $logged_name);
	}
	$queried = get_user_by('slug', $queried_login);
	$followees_ids = get_user_followees($queried->ID, $type);
	$type_label = ($type == 'suscriptor' 
						OR $type == 'subscriber'
						OR $type == 'administrador'
						OR $type == 'administrator') ? 'user' : $type;

	$user_list = array();
	foreach ($followees_ids as $each_id) {
		$temp_user = get_user_by('id', $each_id);

		$user_thumb = museo_get_profilepic_url($temp_user->ID);
		$user_role = ($temp_user->roles[0] == 'suscriptor' 
						OR $temp_user->roles[0] == 'subscriber'
						OR $temp_user->roles[0] == 'administrador'
						OR $temp_user->roles[0] == 'administrator') ? 'user' : $temp_user->roles[0];
		$role_prefix = $temp_user->roles[0];
		if($role_prefix !== 'venue' AND $role_prefix !== 'artista')
			$role_prefix = 'suscriptor';
		$user_list[] = array(
							'ID' 				=> $temp_user->ID,
							'user_login' 		=> $temp_user->user_login,
							'user_display_name' => $temp_user->display_name,
							'user_role' 		=> $user_role,
							'user_thumbnail' 	=> ( isset($user_thumb) AND $user_thumb !== '') ? $user_thumb : NULL,
							'is_following'		=> ($current_user) ? intval(checa_si_sigue_el_usuario($current_user->ID , $temp_user->ID)) : 'undefined',
							'user_followers'	=> checa_total_followers($temp_user->ID),
							'role_prefix'		=> $role_prefix,
							'is_'.$user_role    => TRUE
						);
	}
	$array_return = array(
							'label' => ucfirst($type_label).'s',
							'queried_user_display_name' => $queried->display_name,
							'pool'	=> $user_list
						);
	wp_send_json_success($array_return);
}

/*
 * Update user password
 * @param String $user_login The login name of the user
 * @param String $new_password The new password 
 * @return Bool $success
 * TO DO: Encode (client-side) and decode (server-side) password for security
 */
function update_user_password($user_login, $new_password){
	$user = get_user_by('slug', $user_login);
	if(wp_set_password($new_password, $user->ID));
		wp_send_json_success();
	wp_send_json_error();
	exit;
}

/*  
 * Update the categories a user is indexed in
 *
 */
function update_index_categories($logged_name, $values){
	$user 	 = get_user_by('slug', $logged_name);
	$user_id = $user->ID;
	
	if(update_user_meta( $user_id, 'venue_categories', $values['categories'] ))
		return TRUE;
	return FALSE;
}

/**
 * Create new event from artist profile
 * @param String $logged The user that's creating the post
 * @param Array $event_data (via $_POST)
 */
function museo_create_new_event($logged, $event_data){
	
	$author = get_user_by('login', $logged);
	$revision = (!empty($author->roles) AND  $author->roles[0] == 'artista') ? "draft" : "publish" ;
	
	$post = array(
	  'post_title'     => $event_data['event_title'],
	  'post_content'   => $event_data['event_description'],
	  'post_status'    => $revision,
	  'post_type'      => 'eventos',
	  'post_author'    => $author->ID,
	);  
	
	$post_id = wp_insert_post($post);
	
	if(!is_wp_error($post_id)){
		wp_set_object_terms($post_id, array_values(array_map('intval', explode( ',' ,$event_data['cat_array']))), "category", FALSE);
		if(isset($event_data['event_date_start']))	update_post_meta($post_id, 'mg_evento_fecha_inicio', $event_data['event_date_start']);
		if(isset($event_data['event_hour_start'])) 	update_post_meta($post_id, 'mg_evento_hora_inicio', $event_data['event_hour_start']);
		if(isset($event_data['event_date_end'])) 	update_post_meta($post_id, 'mg_evento_fecha_fin', $event_data['event_date_end']);
		if(isset($event_data['event_hour_end'])) 	update_post_meta($post_id, 'mg_evento_hora_fin', $event_data['event_hour_end']);
		if(isset($event_data['event_venue'])) 		update_post_meta($post_id, 'mg_venue_id', $event_data['event_venue']);
		if(isset($event_data['other_venue'])) 		update_post_meta($post_id, 'mg_other_venue', $event_data['other_venue']);
		if(isset($event_data['event_price'])) 		update_post_meta($post_id, 'mg_costo', $event_data['event_price']);
		if(isset($event_data['mg_artist_ids']) AND intval($event_data['mg_artist_ids'])) 	update_post_meta($post_id, 'mg_artist_ids', $event_data['mg_artist_ids']);
		if(isset($event_data['event_address'])) 	update_post_meta($post_id, 'mg_evento_direccion', $event_data['event_address']);
		
		if  (
			 isset( $_POST['event_image_upload_nonce']) 
			 && wp_verify_nonce( $_POST['event_image_upload_nonce'], 'event_image_upload' )
			 && !empty( $_FILES )
			) 
		{
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			require_once( ABSPATH . 'wp-admin/includes/media.php' );
			// Let WordPress handle the upload
			$attachment_id = media_handle_upload('event_image_upload', $user_id );
			if ( is_wp_error( $attachment_id ) ) {
				wp_send_json_error();
			}else {
				add_post_meta($post_id, '_thumbnail_id', $attachment_id);
				$metadata = wp_get_attachment_metadata( $attachment_id );
				$new_imagemeta = array(
									"aperture"=> 0,
									"credit"=> "",
									"camera"=> "",
									"caption"=> "",
									"created_timestamp"=> 0,
									"copyright"=> "",
									"focal_length"=> 0,
									"iso"=> 0,
									"shutter_speed"=> 0,
									"title"=> $event_data['event_title'],
									"orientation"=> 0
								);
				$metadata['image_meta'] = $new_imagemeta;
				wp_update_attachment_metadata($attachment_id, $metadata);
				$message = "El usuario {$author->user_login} ha creado un nuevo evento: \"{$event_data['event_title']}\" que necesita ser revisado y aprobado";
				$headers[] = 'From: Museógrafo Admin<noreply@museografo.com>';
				if($revision == "draft")
					wp_mail( "ivan@marat.mx", "Nuevo evento esperando revisión", $message, $headers );
				wp_send_json_success();
			}

		} else {
			wp_send_json_error();
		}
	}
	wp_send_json_error();
}

/**  
 * Create new project from artist profile
 * @param String $logged The user that's creating the post
 * @param Array $project_data (via $_POST)
 */
function museo_create_new_project($logged, $project_data){
	$author = get_user_by('login', $logged);
	
	$post = array(
	  'post_title'     => $project_data['project_title'],
	  'post_content'   => $project_data['project_description'],
	  'post_status'    => "publish",
	  'post_type'      => 'proyectos-especiales',
	  'post_author'    => $author->ID,
	);  
	
	$post_id = wp_insert_post($post);
	if(!is_wp_error($post_id)){
		wp_set_object_terms($post_id, array_values(array_map('intval', explode( ',' ,$project_data['cat_array']))), "category", FALSE);
		if(isset($project_data['project_date_started']))	update_post_meta($post_id, 'project_date_started', $project_data['project_date_started']);
		if(isset($project_data['project_date_finished'])) update_post_meta($post_id, 'project_date_finished', $project_data['project_date_finished']);
		if(isset($project_data['technique'])) update_post_meta($post_id, 'project_technique', $project_data['technique']);
		if(isset($project_data['collection'])) update_post_meta($post_id, 'project_collection', $project_data['collection']);
		if(isset($project_data['project_links'])) update_post_meta($post_id, 'project_links', $project_data['project_links']);
		if(isset($project_data['url_video'])) update_post_meta($post_id, 'project_video_url', $project_data['url_video']);
		if(isset($project_data['type_of_project'])) update_post_meta($post_id, 'project_type', $project_data['type_of_project']);
		registra_actividad($post_id, $author->ID, 'project', NULL);
		if  (
			 isset( $_POST['artist_image_upload_nonce']) 
			 && wp_verify_nonce( $_POST['artist_image_upload_nonce'], 'artist_image_upload' )
			 && !empty( $_FILES )
			) 
		{
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			require_once( ABSPATH . 'wp-admin/includes/media.php' );
			// Let WordPress handle the upload
			$attachment_id = media_handle_upload('artist_image_upload', $user_id );
			if ( is_wp_error( $attachment_id ) ) {
				wp_send_json_error();
			}else {
				add_post_meta($post_id, '_thumbnail_id', $attachment_id);
				$metadata = wp_get_attachment_metadata( $attachment_id );
				$new_imagemeta = array(
									"aperture"=> 0,
									"credit"=> "",
									"camera"=> "",
									"caption"=> "",
									"created_timestamp"=> 0,
									"copyright"=> "",
									"focal_length"=> 0,
									"iso"=> 0,
									"shutter_speed"=> 0,
									"title"=> $project_data['event_title'],
									"orientation"=> 0
								);
				$metadata['image_meta'] = $new_imagemeta;
				wp_update_attachment_metadata($attachment_id, $metadata);
		
				wp_send_json_success();
			}

		} else {
			wp_send_json_error();
		}
	}
	wp_send_json_error();
}

/* Search usernames to populate autocomplete */
function search_usernames($logged, $s){
	$user = get_user_by('slug', $logged);
	$values = get_user_followees($user->ID);
	
	$stringPlaceholders = array_fill(0, count($values), '%d');
	$in_here = implode(', ', $stringPlaceholders);

	$values[] = "%".$s."%";
	$values[] = "%".$s."%";

	global $wpdb;
	$query = "SELECT user_login
			  FROM wp_users
				WHERE ID IN ($in_here)
				AND ( user_login LIKE %s
					OR display_name LIKE %s)
				LIMIT 5;
			";
			
	$results = $wpdb->get_col( $wpdb->prepare($query , 
													$values));

	wp_send_json_success($results);
}

/*
 * Get content for the login screen
 * @return Array
 */
function get_login_content(){
	$args = array(
					'post_type' => 'eventos',
					'posts_per_page' => 1,
					'orderby' => 'rand'
				);
	$random_event = get_posts($args);
	$random_event = $random_event[0];
	$thumb = _get_attachment_url(get_post_thumbnail_id($random_event->ID), 'gallery_mobile');
	wp_send_json_success( array(
					'login_image' => $thumb[0]
				));
}

/*
 * Get extension from MIME type
 * @param String $mimetype
 */
function get_extension_fromMIMEtype($mimetype){
	switch ($mimetype) {
		case 'image/jpeg':
			$extension = 'jpg';
				break;
		case 'image/png':
			$extension = 'png';
				break;
		case 'image/gif':
			$extension = 'gif';
				break;
		default:
			$extension = 'jpg';
				break;
	}
	return $extension;
}

/*
 * Save event uploads
 * @param $user The user uploading the object
 * @param $ajax Wheather to use as API call (FALSE) or an ajax call
 * @return JSON success plus the image url
 */
function save_event_upload($user_login, $image_temp, $image_name, $event_id) {
	if(!$user_login){
		global $current_user;
	}else{
		$current_user = get_user_by('slug', $user_login);
	}
	global $wpdb;

	$finfo = new finfo(FILEINFO_MIME_TYPE);
		if (false === $ext = array_search(
			$finfo->file($image_temp),
			array(
				'jpg' => 'image/jpeg',
				'png' => 'image/png',
				'gif' => 'image/gif',
			),
			true
		)) {
			throw new RuntimeException('Invalid file format.');
			wp_send_json_error('Invalid file format.');
		}
	
	$wp_upload_dir = wp_upload_dir();
	$extension = get_extension_fromMIMEtype($_FILES['file']['type']);
	$img = $wp_upload_dir['path']."/".md5($image_name).".".$extension;
	if(!move_uploaded_file($image_temp, $img) )
	{
		throw new RuntimeException('Failed to move uploaded file.');
		exit;
	}	

	$attachment = array(
		'post_status'    => 'private',
		'post_mime_type' => "image/{$extension}",
		'post_type'      => 'attachment',
		'post_parent'    => $event_id,
		'post_title'     => $image_name,

	);

	$dir = substr($wp_upload_dir['subdir'], 1);
	
	$attach_id = wp_insert_attachment( $attachment, $img);
	if($attach_id){
		// you must first include the image.php file for the function wp_generate_attachment_metadata() to work
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		$attach_data = wp_generate_attachment_metadata( $attach_id, $img );
		$_POST['attach_id'] = $attach_id;
		wp_update_attachment_metadata( $attach_id, $attach_data );
		set_post_thumbnail( '', $attach_id );
		registra_actividad($event_id, $current_user->ID, 'media', $attach_id);
		$img_url2 = _get_attachment_url($attach_id, 'agenda-feed');

	}
	return $img_url2[0];
	exit;
}

/*
 * Save profile picture from API
 * @param $user_id via $_POST
 * @param $user_name via $_POST
 *
 */
function save_profile_picture_upload($user_login, $image_temp, $image_name) {
	if(!$user_login){
		global $current_user;
	}else{
		$current_user = get_user_by('slug', $user_login);
	}
	global $wpdb;

	$finfo = new finfo(FILEINFO_MIME_TYPE);
		if (false === $ext = array_search(
			$finfo->file($image_temp),
			array(
				'jpg' => 'image/jpeg',
				'png' => 'image/png',
				'gif' => 'image/gif',
			),
			true
		)) {
			throw new RuntimeException('Invalid file format.');
			wp_send_json_error('Invalid file format.');
		}
	
	$wp_upload_dir = wp_upload_dir();
	$extension = get_extension_fromMIMEtype($_FILES['file']['type']);
	$img = $wp_upload_dir['path']."/".md5($image_name).".".$extension;
	if(!move_uploaded_file($image_temp, $img) )
	{
		throw new RuntimeException('Failed to move uploaded file.');
		exit;
	}	

	$attachment = array(
		'post_status'    => 'inherit',
		'post_mime_type' => "image/{$extension}",
		'post_type'      => 'attachment',
		'post_title'     => $image_name,

	);
	$attach_id = wp_insert_attachment( $attachment, $img);

	if($attach_id){
		// you must first include the image.php file for the function wp_generate_attachment_metadata() to work
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		$attach_data = wp_generate_attachment_metadata( $attach_id, $img );
		$_POST['attach_id'] = $attach_id;
		wp_update_attachment_metadata( $attach_id, $attach_data );
		set_post_thumbnail( '', $attach_id );
		$dir = substr($wp_upload_dir['subdir'], 1);
		 $img_url2 = _get_attachment_url($attach_id, 'thumbnail');
		 $pat_img    = pathinfo($img_url2[0]);
		 $img2 = $dir .'/'. $pat_img['basename'];
		 save_image_user($img2, $current_user->ID);
	}
	echo $img_url2[0];
	exit;
}

/*
 * Get asset by name or identifier
 * @param String $asset_name
 * @param json encoded array of arguments
 * @see assets documentation
 *
 * @return json encoded success plus data pool if exists
 */
function museo_get_asset_by_name($asset_name = NULL, $args = array()){
	$args = json_decode(stripslashes($args), JSON_FORCE_OBJECT);
	
	if(!$asset_name) 
		return json_encode(array('success' => FALSE, 'error' =>"No asset name provideed"));
	
	if($asset_name == "provinces"){
		if(!empty($args) AND isset($args['country'])){
			$provinces = get_cities_bycountry($args['country']);
			$provinces_slug = array_map("clean_term_name", $provinces);
			$result_array = array(
								'success' => TRUE, 
								'pool' => array()
							);	
			
			foreach ($provinces as $index => $each_city) {
				$result_array['pool'][] = array("slug" => $provinces_slug[$index], "name" =>$each_city);
			}
			return $result_array;
		}
	return json_encode(array('success' => FALSE, 'error' =>"Not enough arguments"));
	}

	/* If everything fails this guy sends not found */
	return json_encode(array('success' => FALSE, 'error' =>"404 No asset found by that name or identifier"));
}

/*
 * Override for the  _get_attachment_url method
 * This one always gets an absolute path
 *
 * @param Int $attachment_id
 * @param String $size
 * @param Boolean $icon
 * @return Array $image or FALSE if an error ocurred
 */
function _get_attachment_url($attachment_id, $size='thumbnail', $icon = false) {
 
	// get a thumbnail or intermediate image if there is one
	if ( $image = image_downsize($attachment_id, $size) ){
		$broken = explode('/', $image[0]);
		$is_museo_dev = (array_search("museografo.dev", $broken) !== FALSE) ? TRUE : FALSE;
		$relative_start_index = array_search("wp-content", $broken);
		$broken = array_splice($broken, $relative_start_index, count($broken)-1);
		if( (array_search("museografo.com", $broken) == FALSE AND !$is_museo_dev) 
			OR (array_search("museografo.dev", $broken) == FALSE AND $is_museo_dev) ){
			$joint = implode("/", $broken);
			$image[0] = site_url( $joint);
			return $image;
		}
		return $image;
	}
 
	$src = false;
	if ( $icon && $src = wp_mime_type_icon($attachment_id) ) {
	
		/** This filter is documented in wp-includes/post.php */
		$icon_dir = apply_filters( 'icon_dir', ABSPATH . WPINC . '/images/media' );
		$src_file = $icon_dir . '/' . wp_basename($src);
		@list($width, $height) = getimagesize($src_file);
	}
	if ( $src && $width && $height )
		return array( $src, $width, $height );
	return false;
}

/*
 * Get clean url usable version of the user log in name
 * @param Int $ID
 * @return String $nicename
 */
function get_clean_userlogin($user_id){
	return get_the_author_meta('user_nicename', $user_id);
}
