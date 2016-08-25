<?php


// CUSTOM METABOXES //////////////////////////////////////////////////////////////////



	add_action('add_meta_boxes', function(){

		add_meta_box( "podcast_details", "Detalles de audio", "file_meta_callback", "podcast", "side", "high" );

	});



// CUSTOM METABOXES CALLBACK FUNCTIONS ///////////////////////////////////////////////



	function file_meta_callback($post){
		$file_url = get_post_meta($post->ID, '_file_url_meta', true);
		wp_nonce_field(__FILE__, '_audio_details_meta_nonce');
		echo "<label>Nombre del archivo:</label>";
		echo "<input type='text' class='widefat' id='file_url' name='_file_url_meta' value='$file_url'/>";
	}



// SAVE METABOXES DATA ///////////////////////////////////////////////////////////////



	add_action('save_post', function($post_id){


		if ( ! current_user_can('edit_page', $post_id)) 
			return $post_id;


		if ( defined('DOING_AUTOSAVE') and DOING_AUTOSAVE ) 
			return $post_id;
		
		
		if ( wp_is_post_revision($post_id) OR wp_is_post_autosave($post_id) ) 
			return $post_id;


		if ( isset($_POST['_file_url_meta']) and check_admin_referer(__FILE__, '_audio_details_meta_nonce') ){
			update_post_meta($post_id, '_file_url_meta', $_POST['_file_url_meta']);
		}


		// Guardar correctamente los checkboxes
		/*if ( isset($_POST['_checkbox_meta']) and check_admin_referer(__FILE__, '_checkbox_nonce') ){
			update_post_meta($post_id, '_checkbox_meta', $_POST['_checkbox_meta']);
		} else if ( ! defined('DOING_AJAX') ){
			delete_post_meta($post_id, '_checkbox_meta');
		}*/


	});



// OTHER METABOXES ELEMENTS //////////////////////////////////////////////////////////
