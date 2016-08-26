<?php get_header(); ?>
		<!--[if lt IE 9]>
			<p class="chromeframe">Estás usando una versión <strong>vieja</strong> de tu explorador. Por favor <a href="http://browsehappy.com/" target="_blank"> actualiza tu explorador</a> para tener una experiencia completa.</p>
		<![endif]-->
		<section class="main-page">
			<div class="grid wrapper-inf">
				<div class="grid-sizer"></div>
				<div class="grid-item destacado grid-item--width2 columna">
					<a class="grid-link" href="#">
						<div class="img-wrapper"><img src="<?php echo THEMEPATH; ?>images/1.png"></div>
						<div class="art-title"><span>LA MUERTE DE MOHAMED ALI</span></div>
						<div class="art-descr">La leyenda del boxeo, Mohamed Ali, murió este viernes a los 74 años, informó su familia, mientras estaba en el hospital por problemas...</div>
					</a>
					<div class="art-info">
						<span class="art-author">Martha Cristiana</span>
						<span class="art-date">junio 18, 2016</span>
						<span class="art-categ">[DEPORTES]</span>
					</div>
				</div>

				<?php
					$args = array(
							'post_type' => array('columna', 'podcast'),
							'posts_per_page' => -1
						);

					$posts = get_posts($args);
					foreach($posts as $post): setup_postdata($post);
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
						<span class="art-author">Martha Cristiana</span>
						<span class="art-date">junio 18, 2016</span>
						<span class="art-categ">[DEPORTES]</span>
					</div>
				</div>

				<?php } elseif($posttype == 'podcast'){ ?>
				
				<div class="grid-item podcast">
					<div class="pod-img">
						<img src="<?php echo THEMEPATH; ?>images/5.png">
						<span>EL PODCAST DE JORDI SOLER</span>
					</div>
					<div class="pod-title">
						<span>Ep. 01</span>
						<span>¿Cómo fue que Jordi Soler conoció a David Bowie?</span>
					</div>
					<div class="pod-data">18 min | junio 15, 2016</div>
					<a href="#" class="pod-play"><img src="<?php echo THEMEPATH; ?>images/play-blue.svg"></a>
				</div>
			
				<?php } endforeach; wp_reset_postdata(); ?>

			</div>
			<div class="more-posts">VER MÁS</div>
		</section>
		
<?php get_footer(); ?>

