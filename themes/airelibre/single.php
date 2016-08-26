<?php get_header(); the_post(); ?>

		<section class="single">
			<?php if(has_post_thumbnail($post->ID)){
				the_post_thumbnail('full', array('class' => 'big-img'));
			}?>
			<div class="wrapper-666">
				<h2><?php the_title(); ?></h2>
				<div class="info-left">
					<span class="left-author">Juan Patricio Riveroll</span>
					<span class="left-date">18 de junio, 2016</span>
					<span class="left-tag">[CINE] [POLITICA]</span>
					<a href="#">GUARDAR</a>
					<a href="#">COMPARTIR</a>
				</div>
				<!-- <div class="quote">Una película es como un campo de batalla: tiene amor, odio, acción, violencia y muerte. En una palabra: emociones.<span>–Samuel Fuller, en <i>Pierrot el loco</i></span></div> -->
				<?php the_content(); ?>
			</div>
			<!-- <img class="big-img" src="<?php echo THEMEPATH; ?>images/mad.png" /> -->
			<!-- <div class="img-foot"><span>Wikimedia Commons photograph of a restaurant in Jimma, Ethiopia.</span> Photo by Rod Waddington. Some rights reserved.</div> -->
			<div class="more-author">VER MÁS DE JUAN PATRICIO RIVEROLL</div>
		</section>
		<section class="bottom-single">
			<div class="related-title">ARTÍCULOS RELACIONADOS</div>
			<div class="grid wrapper-inf">
				<div class="grid-sizer"></div>
				<div class="grid-item normal">
					<img src="images/3.png">
					<div class="art-title"><span>WITKIN</span></div>
					<div class="art-descr">La leyenda del boxeo, Mohamed Ali, murió este viernes a los 74 años, informó su familia, mientras estaba en el hospital por problemas...</div>
					<div class="art-info">
						<span class="art-author">Martha Cristiana</span>
						<span class="art-date">junio 18, 2016</span>
						<span class="art-categ">[DEPORTES]</span>
					</div>
				</div>
				<div class="grid-item normal">
					<img src="images/3.png">
					<div class="art-title"><span>WITKIN</span></div>
					<div class="art-descr">La leyenda del boxeo, Mohamed Ali, murió este viernes a los 74 años, informó su familia, mientras estaba en el hospital por problemas...</div>
					<div class="art-info">
						<span class="art-author">Martha Cristiana</span>
						<span class="art-date">junio 18, 2016</span>
						<span class="art-categ">[DEPORTES]</span>
					</div>
				</div>
				<div class="grid-item normal">
					<img src="images/3.png">
					<div class="art-title"><span>WITKIN</span></div>
					<div class="art-descr">La leyenda del boxeo, Mohamed Ali, murió este viernes a los 74 años, informó su familia, mientras estaba en el hospital por problemas...</div>
					<div class="art-info">
						<span class="art-author">Martha Cristiana</span>
						<span class="art-date">junio 18, 2016</span>
						<span class="art-categ">[DEPORTES]</span>
					</div>
				</div>
				<div class="grid-item normal hidden-1280">
					<img src="images/3.png">
					<div class="art-title"><span>WITKIN</span></div>
					<div class="art-descr">La leyenda del boxeo, Mohamed Ali, murió este viernes a los 74 años, informó su familia, mientras estaba en el hospital por problemas...</div>
					<div class="art-info">
						<span class="art-author">Martha Cristiana</span>
						<span class="art-date">junio 18, 2016</span>
						<span class="art-categ">[DEPORTES]</span>
					</div>
				</div>
			</div>
			<div class="more-posts">VER MÁS</div>
		</section>
<?php get_footer(); ?>