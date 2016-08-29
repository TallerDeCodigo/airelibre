<?php


// CUSTOM POST TYPES /////////////////////////////////////////////////////////////////


	add_action('init', function(){


		// Podcasts
		$labels = array(
			'name'          => 'Podcasts',
			'singular_name' => 'Podcast',
			'add_new'       => 'Nuevo Podcast',
			'add_new_item'  => 'Nuevo Podcast',
			'edit_item'     => 'Editar Podcast',
			'new_item'      => 'Nuevo Podcast',
			'all_items'     => 'Todos',
			'view_item'     => 'Ver Podcast',
			'search_items'  => 'Buscar Podcast',
			'not_found'     => 'No se encontró',
			'menu_name'     => 'Podcasts'
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'podcasts' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 6,
			'taxonomies'         => array( 'category' ),
			'supports'           => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail' )
		);
		register_post_type( 'podcast', $args );

		// Columnas
		$labels = array(
			'name'          => 'Columnas',
			'singular_name' => 'Columnas',
			'add_new'       => 'Nueva Columna',
			'add_new_item'  => 'Nueva Columna',
			'edit_item'     => 'Editar Columna',
			'new_item'      => 'Nueva Columna',
			'all_items'     => 'Todos',
			'view_item'     => 'Ver Columna',
			'search_items'  => 'Buscar Columnas',
			'not_found'     => 'No se encontró',
			'menu_name'     => 'Columnas'
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'columnas' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 6,
			'taxonomies'         => array( 'category' ),
			'supports'           => array( 'title', 'editor', 'author', 'thumbnail' )
		);
		register_post_type( 'columna', $args );

	});