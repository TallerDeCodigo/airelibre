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
						<!-- <span><?php echo $programa->name; ?></span> -->
					</div>
					<span class="cast-tab"><?php echo $programa->name; ?></span>
					<span class="cast-title desk-cast"><?php the_title(); ?></span>
					<p>por: <?php foreach($autores as $autor){ echo $autor->name.' '; }?></p>
					<!-- <a class="suscribe done" href="#">SUSCRITO</a> -->
				</div>
				<div class="cast-full">
					<div class="pod-img">
						<img src="<?php echo $portada['url']; ?>">
						<!-- <span><?php echo $programa->name; ?></span> -->
					</div>
					<span class="cast-title mob-cast"><?php the_title(); ?></span>
					<p><?php echo $programa->description; ?></p>
				</div>
			</div>
		</section>
		<?php wp_reset_postdata(); ?>
		<section class="playlist">
			<div class="wrapper-769">
				<ul>
				<?php
					$args = array(
							'post_type' => 'podcast',
							'tax_query' => array(
									array(
										'taxonomy' => 'programa',
										'field'    => 'term_id',
										'terms'    => $programa->term_id,
									),
								),
							'posts_per_page' => -1,
							'order' => 'ASC'
						);

					$episodios = get_posts($args);
					$count = 0;
					foreach($episodios as $post): setup_postdata($post);
					$count ++;
					// $file = get_post_meta($post->ID, '_file_url_meta', true);
					// $mp3file1 = new MP3File($file);//http://www.npr.org/rss/podcast.php?id=510282
					// //$duration1 = $mp3file->getDurationEstimate();//(faster) for CBR only
					// $duration2 = $mp3file1->getDuration();//(slower) for VBR (or CBR)
				
					

				?>
					
					<li class="pl-item">
						<div class="pl-number"><?php echo $count; ?></div>
						<div class="pl-add"><img class="play_podcast" src="<?php echo THEMEPATH; ?>images/play-blue.svg" data-audio="<?php echo get_post_meta($post->ID, '_file_url_meta', true); ?>"></div>
						<div class="pl-descr">
							<span class="pl-itm-tt"><?php the_title(); ?>· <?php echo get_the_date('d/m/Y'); ?></span>
							<span><?php the_excerpt(); ?></span>
						</div>
						<div class="pl-time"><?php //echo MP3File::formatTime($duration2)."\n"; ?></div>
					</li>
					<?php endforeach; wp_reset_postdata(); ?>
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
