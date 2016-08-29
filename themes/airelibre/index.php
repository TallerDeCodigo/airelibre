<?php get_header(); ?>
		<!--[if lt IE 9]>
			<p class="chromeframe">Estás usando una versión <strong>vieja</strong> de tu explorador. Por favor <a href="http://browsehappy.com/" target="_blank"> actualiza tu explorador</a> para tener una experiencia completa.</p>
		<![endif]-->
		<section class="main-page">
			<div class="grid wrapper-inf">
				<div class="grid-sizer"></div>
				<?php
					$args = array(
							'post_type' => 'columna',
							'meta_query' => array(
									array(
										'key' => 'check_destacado',
										'value' => 1,
										'compare' => '=',
									)
								),
							'posts_per_page' => 1,

						);

					$posts = get_posts($args);
					foreach($posts as $post): setup_postdata($post);
					$excludeID = $post->ID; 
					$autores = wp_get_post_terms($post->ID, 'autor');
					foreach($autores as $autor);
					$categories = wp_get_post_terms($post->ID, 'category');
					
					
				?>
				<div class="grid-item destacado grid-item--width2 columna">
					<a class="grid-link" href="<?php the_permalink(); ?>">
						<?php if(has_post_thumbnail($post->ID)){ ?>
							<div class="img-wrapper"><?php the_post_thumbnail('large'); ?></div>
						<?php } ?>
						<div class="art-title"><span><?php the_title(); ?></span></div>
						<div class="art-descr"><?php the_excerpt(); ?></div>
					</a>
					<div class="art-info">
						<span class="art-author"><?php echo $autor->name; ?></span>
						<span class="art-date"><?php echo get_the_date('d/m/Y'); ?></span>
						<?php foreach($categories as $cat){ ?>
						<span class="art-categ">[<?php echo $cat->name; ?>]</span>
						<?php } ?>
					</div>
				</div>

				<?php endforeach; wp_reset_postdata(); ?>

	
				<?php
					$args = array(
							'post_type' => array('columna', 'podcast'),
							'posts_per_page' => -1
						);

					$posts = get_posts($args);
					foreach($posts as $post): setup_postdata($post);
					$posttype = get_post_type();
					$autores = wp_get_post_terms($post->ID, 'autor');
					foreach($autores as $autor);
					$categories = wp_get_post_terms($post->ID, 'category');
					
					if($post->ID == $excludeID){ continue; }
					if($posttype == 'columna'){
				?>

				<div class="grid-item normal columna">
					<a class="grid-link" href="<?php the_permalink(); ?>">
						<?php if(has_post_thumbnail($post->ID)){
							the_post_thumbnail('small');
						}?>
						<div class="art-title"><span><?php the_title(); ?></span></div>
						<div class="art-descr"><?php the_excerpt(); ?></div>
					</a>
					<div class="art-info">
						<span class="art-author"><?php echo $autor->name; ?></span>
						<span class="art-date"><?php echo get_the_date('d/m/Y'); ?></span>
						<?php foreach($categories as $cat){ ?>
						<span class="art-categ">[<?php echo $cat->name; ?>]</span>
						<?php } ?>
					</div>
				</div>

				<?php 
					} elseif($posttype == 'podcast'){ 
					$terms = wp_get_post_terms($post->ID, 'programa');
					foreach($terms as $programa);
					$portada = get_term_meta($programa->term_id,'image_field_id', true);
					
				?>
				
				<div class="grid-item podcast">
					<div class="pod-img">
						<img src="<?php echo $portada['url']; ?>">
						<span><?php echo $programa->name; ?></span>
					</div>
					<div class="pod-title">
						<!-- <span>Ep. 01</span> -->
						<span><?php the_title(); ?></span>
					</div>
					<div class="pod-data">junio 15, 2016</div>
					<a href="#" class="pod-play"><img src="<?php echo THEMEPATH; ?>images/play-blue.svg"></a>
				</div>
			
				<?php } endforeach; wp_reset_postdata(); ?>

			</div>
			<!-- <div class="more-posts">VER MÁS</div> -->
		</section>
		
<?php get_footer(); ?>

