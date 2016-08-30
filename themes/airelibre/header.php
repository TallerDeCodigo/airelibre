<!doctype html>
	<head>
		<meta charset="utf-8">
		<title><?php print_title(); ?></title>
		<link rel="shortcut icon" href="<?php echo THEMEPATH; ?><?php echo THEMEPATH; ?>images/favicon.ico">
		<meta name="description" content="<?php bloginfo('description'); ?>">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta http-equiv="cleartype" content="on">
		<!--[if IE]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
		<?php wp_head(); ?>

		
	</head>

	<body>
		<!--[if lt IE 9]>
			<p class="chromeframe">Estás usando una versión <strong>vieja</strong> de tu explorador. Por favor <a href="http://browsehappy.com/" target="_blank"> actualiza tu explorador</a> para tener una experiencia completa.</p>
		<![endif]-->
		<header class="mobile">
			<a class="mob-logo" href="<?php echo site_url(''); ?>"><img src="<?php echo THEMEPATH; ?>images/mobile.svg"></a>
		</header>
		<div id="nav-icon3"><span></span><span></span><span></span><span></span></div>
		<header class="desktop">
			<div class="wrapper">
				<div class="logo left">
					<a href="<?php echo site_url(); ?>"><img class="logogif" src="<?php echo THEMEPATH; ?>images/logo.gif"></a>
					<a href="<?php echo site_url(); ?>"><img class="logosvg" src="<?php echo THEMEPATH; ?>images/mobile.svg"></a>
				</div>
				<div class="menu right">
					<nav class="social">
						<a href="#" class="nav-item"><img src="<?php echo THEMEPATH; ?>images/social/tw.svg"></a>
						<a href="#" class="nav-item"><img src="<?php echo THEMEPATH; ?>images/social/fb.svg"></a>
						<a href="#" class="nav-item"><img src="<?php echo THEMEPATH; ?>images/social/sp.svg"></a>
						<a href="#" class="nav-item"><img src="<?php echo THEMEPATH; ?>images/social/sc.svg"></a>
						<!-- <div class="nav-item ultima">HOLA, MARIANA</div> -->
					</nav>
					<nav class="botones">
						<!-- <a data-filter="*" class="nav-item">RADIO</a> -->
						<a data-filter=".podcast" class="nav-item">PODCASTS</a>
						<a data-filter=".columna" class="nav-item">COLUMNAS</a>
						<a class="nav-item dropdown">AUTORES <img src="<?php echo THEMEPATH; ?>images/down.svg"></a>
						<div class="nav-item ultima"><input type="text" name="search1" placeholder="Búsqueda"><img id="search1" src="<?php echo THEMEPATH; ?>images/search.svg"></div>
					</nav>
					<div class="filler-right"></div>
				</div>
			</div>
		</header>
		<nav class="mob-menu" style="display:none">
			<div class="mbm-item"><input type="text" name="search2" placeholder="Búsqueda"><img id="search2" src="<?php echo THEMEPATH; ?>images/search2.svg"></div>
			<a href="#" class="mbm-item">RADIO</a>
			<a href="#" class="mbm-item">PODCASTS</a>
			<a href="#" class="mbm-item">COLUMNAS</a>
			<a href="#" class="mbm-item separator">AUTORES</a>
			<a href="#" class="mbm-item1">TWITTER</a>
			<a href="#" class="mbm-item1">FACEBOOK</a>
			<a href="#" class="mbm-item1">SNAPCHAT</a>
			<a href="#" class="mbm-item1">NEWSLETTER</a>
			<a href="#" class="mbm-item1">ACERCA</a>
		</nav>
		<nav class="submenu" style="display:none">
			<div class="arrow-sub"><img src="<?php echo THEMEPATH; ?>images/down.svg"></div>
			<div class="wrapper">
				<?php
					$alphabetized = fetch_terms_alphabetized('autor');
					foreach ($alphabetized as $key => $value): ?>
						<div class="letra">
							<span><?php echo $key; ?></span>
						<?php
							foreach ($alphabetized[$key] as $author_name): 
								$slug = get_term_by('name', $author_name, 'autor');

						?>

								<a href="<?php echo site_url().'/autores/'.$slug->slug; ?>"><?php echo $author_name; ?></a>
						<?php 
							endforeach;
							?>
						</div>
					<?php 
					endforeach;
				?>
			</div>
		</nav>
		<section class="player">
			<div class="wrapper">
				<img class="play" src="<?php echo THEMEPATH; ?>images/play.svg">
				<img class="pause" src="<?php echo THEMEPATH; ?>images/pause.svg">
				<img class="album" src="<?php echo THEMEPATH; ?>images/album.png">
				<div class="audio-title">
					<div class="showname">LA HORA DE LA COMIDA [14:00 – 16:00]</div>
					<div class="breadcrumbs">Hunting » Fishlights » Serpientes EP</div>
				</div>
				<?php 
				$file = site_url().'/wp-content/uploads/radio/1.mp3';
				$mp3file = new MP3File($file);//http://www.npr.org/rss/podcast.php?id=510282
				//$duration1 = $mp3file->getDurationEstimate();//(faster) for CBR only
				$duration2 = $mp3file->getDuration();//(slower) for VBR (or CBR)
				
				//echo MP3File::formatTime($duration2)."\n";
				?>

				<!-- <div class="changer">
					<div class="podc-ch">PODCAST</div>
					<div class="live-ch circle roja">AL AIRE LIBRE</div>
				</div> -->
			</div>
		</section>

	