<?php 
	get_header(); 
	the_post(); 
	$autores = wp_get_post_terms($post->ID, 'autor');
	foreach($autores as $autor);
	$categories = wp_get_post_terms($post->ID, 'category');
	$excludeID = $post->ID;
?>

		<section class="single">
			<?php if(has_post_thumbnail($post->ID)){
				the_post_thumbnail('full', array('class' => 'big-img'));
			}?>
			<div class="wrapper-666">
				<h2><?php the_title(); ?></h2>
				<div class="info-left">
					<span class="left-author"><?php echo $autor->name; ?></span>
					<span class="left-date"><?php echo get_the_date('d/m/Y'); ?></span>
					<?php foreach($categories as $cat){ ?>
					<span class="art-categ"><a class="inlink" href="<?php echo site_url(); ?>/category/<?php echo $cat->slug; ?>">[<?php echo $cat->name; ?>]</a></span>
					<?php } ?>
					<!-- <a class="info-action" href="#">GUARDAR</a>
					<a class="info-action" href="#">COMPARTIR</a> -->
				</div>
				<!-- <div class="quote">Una película es como un campo de batalla: tiene amor, odio, acción, violencia y muerte. En una palabra: emociones.<span>–Samuel Fuller, en <i>Pierrot el loco</i></span></div> -->
				<?php the_content(); ?>
			</div>
			<!-- <img class="big-img" src="<?php echo THEMEPATH; ?>images/mad.png" /> -->
			<!-- <div class="img-foot"><span>Wikimedia Commons photograph of a restaurant in Jimma, Ethiopia.</span> Photo by Rod Waddington. Some rights reserved.</div> -->
			<div class="more-author"><a class="inlink" href="<?php echo site_url();?>/autores/<?php echo $autor->slug; ?>/">VER MÁS DE <?php echo $autor->name; ?></a></div>
		</section>
		<?php wp_reset_query(); wp_reset_postdata(); ?>
		<section class="bottom-single">
			<div class="related-title">ARTÍCULOS RELACIONADOS</div>
			<div class="grid wrapper-inf">
				<div class="grid-sizer"></div>
				<?php
					$args = array(
							'post_type' => array('columna'),
							'posts_per_page' => 3,
							'orderby' => 'rand'
						);

					$posts = get_posts($args);
					foreach($posts as $post): setup_postdata($post);
					$posttype = get_post_type();
					$autores = wp_get_post_terms($post->ID, 'autor');
					foreach($autores as $autor);
					$categories = wp_get_post_terms($post->ID, 'category');
					if($post->ID == $excludeID){ continue; }
					
				?>

				<div class="grid-item normal columna">
					<a class="grid-link inlink" href="<?php the_permalink(); ?>">
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
						<span class="art-categ"><a href="<?php echo site_url(); ?>/category/<?php echo $cat->slug; ?>">[<?php echo $cat->name; ?>]</a></span>
						<?php } ?>
					</div>
				</div>

				<?php endforeach; wp_reset_postdata(); ?>
			</div>
			<!-- <div class="more-posts">VER MÁS</div> -->
		</section>
<?php get_footer(); ?>