<?php 
	get_header(); 
	the_post();
	$autores = wp_get_post_terms($post->ID, 'autor');
	$porgramas =  wp_get_post_terms($post->ID, 'programa');
	foreach($porgramas as $programa);
	$portada = get_term_meta($programa->term_id,'image_field_id', true);
	$excludeID = $post->ID;
?>
		<section class="podcast">
			<div class="wrapper-769">
				<div class="mob-pod-head">
					<div class="pod-img">
						<img src="<?php echo $portada['url']; ?>">
						<span><?php echo $programa->name; ?></span>
					</div>
					<span class="cast-tab">PODCAST</span>
					<span class="cast-title desk-cast"><?php the_title(); ?></span>
					<p>por: <?php foreach($autores as $autor){ echo $autor->name.' '; }?></p>
					<!-- <a class="suscribe done" href="#">SUSCRITO</a> -->
				</div>
				<div class="cast-full">
					<div class="pod-img">
						<img src="<?php echo $portada['url']; ?>">
						<span><?php echo $programa->name; ?></span>
					</div>
					<span class="cast-title mob-cast"><?php the_title(); ?></span>
					<p><?php echo $programa->description; ?></p>
				</div>
			</div>
		</section>
		<section class="playlist">
			<div class="wrapper-769">
				<ul>
					<li class="pl-item">
						<div class="pl-number">1</div>
						<div class="pl-add"><img src="<?php echo THEMEPATH; ?>images/play-blue.svg"></div>
						<div class="pl-descr">
							<span class="pl-itm-tt">Let me be your friend · junio 31</span>
							<span>Donec id elit non mi porta gravida at eget metus. Donec ullamcorper nulla non metus auctor fringilla.</span>
						</div>
						<div class="pl-time">23 min</div>
					</li>
					<li class="pl-item selected">
						<div class="pl-number">2</div>
						<div class="pl-add"><img src="<?php echo THEMEPATH; ?>images/play-blue.svg"></div>
						<div class="pl-descr">
							<span class="pl-itm-tt">Let me be your friend · junio 31</span>
							<span>Donec id elit non mi porta gravida at eget metus. Donec ullamcorper nulla non metus auctor fringilla.</span>
						</div>
						<div class="pl-time">23 min</div>
					</li>
					<li class="pl-item">
						<div class="pl-number">3</div>
						<div class="pl-add"><img src="<?php echo THEMEPATH; ?>images/play-blue.svg"></div>
						<div class="pl-descr">
							<span class="pl-itm-tt">Let me be your friend · junio 31</span>
							<span>Donec id elit non mi porta gravida at eget metus. Donec ullamcorper nulla non metus auctor fringilla.</span>
						</div>
						<div class="pl-time">23 min</div>
					</li>
					<li class="pl-item">
						<div class="pl-number">4</div>
						<div class="pl-add"><img src="<?php echo THEMEPATH; ?>images/play-blue.svg"></div>
						<div class="pl-descr">
							<span class="pl-itm-tt">Let me be your friend · junio 31</span>
							<span>Donec id elit non mi porta gravida at eget metus. Donec ullamcorper nulla non metus auctor fringilla.</span>
						</div>
						<div class="pl-time">23 min</div>
					</li>
					<li class="pl-item">
						<div class="pl-number">5</div>
						<div class="pl-add"><img src="<?php echo THEMEPATH; ?>images/play-blue.svg"></div>
						<div class="pl-descr">
							<span class="pl-itm-tt">Let me be your friend · junio 31</span>
							<span>Donec id elit non mi porta gravida at eget metus. Donec ullamcorper nulla non metus auctor fringilla.</span>
						</div>
						<div class="pl-time">23 min</div>
					</li>
					<li class="pl-item">
						<div class="pl-number">6</div>
						<div class="pl-add"><img src="<?php echo THEMEPATH; ?>images/play-blue.svg"></div>
						<div class="pl-descr">
							<span class="pl-itm-tt">Let me be your friend · junio 31</span>
							<span>Donec id elit non mi porta gravida at eget metus. Donec ullamcorper nulla non metus auctor fringilla.</span>
						</div>
						<div class="pl-time">23 min</div>
					</li>
				</ul>
			</div>	
		</section>
		<?php wp_reset_postdata(); ?>
		<section class="bottom-single">
			<div class="related-title closer">PODCASTS SIMILARES</div>
			<div class="grid wrapper-inf">
				<div class="grid-sizer"></div>
				<?php
					$args = array(
							'post_type' => array('podcast'),
							'posts_per_page' => 3
						);

					$posts = get_posts($args);
					foreach($posts as $post): setup_postdata($post);
					$posttype = get_post_type();
					$autores = wp_get_post_terms($post->ID, 'autor');
					foreach($autores as $autor);
					$categories = wp_get_post_terms($post->ID, 'category');
					if($post->ID == $excludeID){ continue; }
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
				<?php endforeach; ?>
				
			</div>
			<!-- <div class="more-posts">VER MÁS</div> -->
		</section>
<?php get_footer(); ?>
