<?php
	get_header(); 
	$objeto = get_queried_object();
	$imagen = get_term_meta($objeto->term_id, 'image_field_id', true);
	$imagen_mov = get_term_meta($objeto->term_id,'image_field_id_movil', true);
	// echo '<pre>';
	// print_r($objeto);
	// echo '</pre>';
?>
		<section class="person">
			<img class="per-bkg" src="<?php echo $imagen['url']; ?>">
			<div class="wrapper">
				<div class="per-info">
					<span class="per-name"><?php echo $objeto->name; ?></span>
					<!-- <a class="mob-follow" href="#">SEGUIR</a> -->
					<p class="per-desc"><?php echo $objeto->description; ?></p>
					<img class="per-mobile" src="<?php echo $imagen_mov['url']; ?>">
					
				</div>
				<!-- <a class="per-follow" href="#">SEGUIR</a> -->
			</div>
		</section>
		<section class="bottom-single">
			<div class="related-title closer">COLUMNAS Y PODCASTS</div>
			<div class="grid wrapper-inf">
				<div class="grid-sizer"></div>
				<?php
					$args = array(
							'post_type' => array('columna', 'podcast'),
							'tax_query' => array(
							//'relation' => 'AND',
								array(
									'taxonomy' => 'autor',
									'field'    => 'term_id',
									'terms'    => $objeto->term_id,
								)
							),
							'posts_per_page' => -1
						);

					$posts = get_posts($args);
					foreach($posts as $post): setup_postdata($post);
					$categories = wp_get_post_terms($post->ID, 'category');
					$posttype = get_post_type();
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
						<span class="art-author"><?php echo $objeto->name; ?></span>
						<span class="art-date"><?php echo get_the_date('d/m/Y'); ?></span>
						<?php foreach($categories as $cat){ ?>
						<span class="art-categ"><a href="<?php echo site_url(); ?>/category/<?php echo $cat->slug; ?>">[<?php echo $cat->name; ?>]</a></span>
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
					<a class="grid-link" href="<?php the_permalink(); ?>">
						<div class="pod-img">
							<img src="<?php echo $portada['url']; ?>">
							<span><?php echo $programa->name; ?></span>
						</div>
						<div class="pod-title">
							<!-- <span>Ep. 01</span> -->
							<span><?php the_title(); ?></span>
						</div>
						<div class="pod-data">junio 15, 2016</div>
						<!--<a href="#" class="pod-play"><img src="<a class="grid-link" href="<?php the_permalink(); ?>">"></a>-->
					</a>
				</div>
			
				<?php } endforeach; wp_reset_postdata(); ?>
			
			</div>
			<!-- <div class="more-posts">VER MÁS</div> -->
		</section>
<?php get_footer(); ?>