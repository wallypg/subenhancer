<!DOCTYPE html>
<html lang="en" class="no-js">
	<head>
		<meta charset="UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> 
		<meta name="viewport" content="width=device-width, initial-scale=1.0"> 
		<title>Subshuffle</title>
		<?=add_style('bootstrap.min')?>
		<!-- <?=add_style('materialize.min')?> -->
		<!-- <?=add_style('nanoscroller')?> -->
		<?=add_style('font-awesome.min')?>
		<?=add_style('jquery-confirm')?>
		<?=add_style('subshuffle', true)?>


		<?=add_jscript('jquery-3.1.1.min')?>
		<?=add_jscript('bootstrap.min')?>
		<?=add_jscript('materialize.min')?>
	</head>
	<body class="custom-scrollbar">

		<div class="demo-container">
			<!-- Push Wrapper -->
			<div class="mp-pusher" id="mp-pusher">

				<!-- mp-menu -->
				<nav id="mp-menu" class="mp-menu">
					<div class="mp-level">
						<h2 class="icon icon-globe">Menú</h2>
						<ul>
							<li>
								<a class="first-level icon icon-random" href="#">Modo Aleatorio</a>
							</li>

							<li class="icon icon-arrow-left">
								<a id="my-translations" class="first-level icon icon-translations" href="#">Mis Traducciones</a>
								<div class="mp-level">
									<h2 class="icon icon-translations">Mis Traducciones</h2>
									<a class="mp-back" href="#">Atrás</a>
									<div class="nano-container custom-scrollbar">
										<div class="nano">
											<ul id="my-translations-container" class="nano-content load-loader">
												
											</ul>
										</div>
									</div>
								</div>
							</li>

							<li class="icon icon-arrow-left">
								<!-- <a class="first-level icon icon-film" href="#">Subtítulos</a> -->
								<a id="subtitles" class="first-level icon icon-film" href="#">Subtítulos</a>
								<div class="mp-level">
									<h2 class="icon icon-film">Subtítulos</h2>
									<a class="mp-back" href="#">Atrás</a>
									<div class="nano-container custom-scrollbar">
										<div class="nano">
											<ul id="subtitles-container" class="nano-content">

												<?php foreach ($subtitles as $key => $subtitle) { ?>
													<li class="icon icon-arrow-left list-item">
														<a class="subtitle-item" sub-id="<?=$subtitle->subId;?>"><?=$subtitle->title;?></a>
														<div class="mp-level">
															<h2 class="subtitle-title"><?=$subtitle->title;?></h2>
															<a class="mp-back" href="#">Atrás</a>
															<div class="nano-container custom-scrollbar">
																<div class="nano">
																	<ul id="sub-<?=$subtitle->subId;?>-container" sub-id="<?=$subtitle->subId;?>" class="nano-content load-loader"></ul>
																</div>
															</div>
														</div>
													</li>
												<?php } ?>

											</ul>
										</div>
									</div>
								</div> 
							</li>

							<li class="icon icon-arrow-left">
								<a class="first-level icon icon-keyboard" href="#">Atajos</a>
								<div class="mp-level">
									<h2 class="icon icon-keyboard">Atajos</h2>
									<a class="mp-back" href="#">Atrás</a>
									<div class="nano">										
									<ul class="nano-content shortcuts">
										<li>
											<a>
												Guardar: <span><kbd>Ctrl</kbd> + <kbd>s</kbd></span>
											</a>
										</li>
										<li>
											<a>
												Anterior: <span><kbd>Ctrl</kbd> + <kbd>i</kbd></span>
											</a>
										</li>
										<li>
											<a>
												Aleatorio: <span><kbd>Ctrl</kbd> + <kbd>o</kbd></span>
											</a>
										</li>
										<li>
											<a>
												Próximo: <span><kbd>Ctrl</kbd> + <kbd>p</kbd></span>
											</a>
										</li>
									</ul>
									</div>
								</div>
							</li>

						</ul>
							
					</div>
				</nav>
				<!-- /mp-menu -->

				<div class="scroller custom-scrollbar"><!-- this is for emulating position fixed of the nav -->
					<div class="scroller-inner">
						<!-- Top Navigation -->
						<div class="codrops-top clearfix">
							<button type="button" class="hamburger is-closed" id="trigger">
					            <span class="hamb-top"></span>
					            <span class="hamb-middle"></span>
					            <span class="hamb-bottom"></span>
								<div class="subshuffle">subshuffle</div>
								<div class="path">#<span>ModoAleatorio</span></div>
								<!-- <div class="path">#<span>MisTraducciones</span></div> -->
								<!-- <div class="path">@<span>SubAdictos</span></div> -->
								<!-- <div class="path">#<span>Subtítulos</span></div> -->
								<!-- <div class="path">#<span>FlowerShopMystery</span></div> -->
								<!-- <div class="path">#<span>Black-ish</span></div> -->
								<!-- <div class="path">#<span>Black-ish - 03x04 - Who is Afraid of the Big Black Man</span></div> -->
					        </button>
					        <div class="path">
					        </div>
					        <div class="info-tooltips-btn">

					        	<i class="fa fa-lightbulb-o glow"></i>
					        </div>
							<!-- <p><a href="#" id="trigger" class="menu-trigger">Open/Close Menu</a></p> -->
							<!-- <span class="right"></span> -->
						</div>
						<!-- <header class="codrops-header">
							<h1>Multi-Level Push Menu <span>Off-screen navigation with multiple levels</span></h1>
						</header> -->
						<div class="content clearfix">
							<div class="container">
								<div class="row">
									<div class="col-md-6 col-md-offset-3">
										<div class="row sequence-info sleeper-tooltip" tooltip="Nombre del subtítulo">	
											<div class="col-xs-9 title-info">Black-ish - 03x04 - Who is Afraid of the Big Black Man</div>
											<div class="col-xs-3 text-right sleeper-tooltip" tooltip="Número de secuencia" flow="right">#&nbsp;<span class="sequence-number">435</span></div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6 col-md-offset-3">
										<div class="row">
											<div class="col-xs-4 sleeper-tooltip" tooltip="Secuencia anterior" flow="down">
												<i class="btn green fa fa-caret-left prev"></i>
											</div>
											<div class="col-xs-4 sleeper-tooltip" tooltip="Secuencia aleatoria" flow="down">
												<i class="btn green fa fa-random randomize"></i>
											</div>
											<div class="col-xs-4 sleeper-tooltip" tooltip="Secuencia siguiente" flow="down">
												<i class="btn green fa fa-caret-right next"></i>
											</div>
										</div>
										
										<div class="row">
											<div class="col-md-12 sleeper-tooltip" tooltip="Texto a traducir" flow="left">
												<div class="from-textarea">
													<label>From:</label>
													<p>I'm so glad you brought<br>They Call Me Johan</p>
												</div>
											</div>
										</div>
	      								
										<div class="row">
											<div class="col-md-12 sleeper-tooltip" tooltip="Traducción" flow="left">
										        <div class="container-textarea">
											        <div class="form-group-textarea">
											        	<textarea id="to-textarea" required="required" cols="42" rows="2" wrap="hard"></textarea>
		      											<label class="control-label" for="to-textarea">To:</label><i class="bar"></i>
		      										</div>
		      									</div>
											</div>
										</div>

										<div class="row subtitle-data">	
											<div class="col-xs-6 text-left sleeper-tooltip" tooltip="Caracteres por línea" flow="left">
												<span class="chars first-line-chars">10</span>
												<span>/</span>
												<span class="chars second-line-chars">13</span>
												&nbsp;<span class="cps-label">CPL</span>
											</div>
											<div class="col-xs-6 text-right sleeper-tooltip" tooltip="Caracteres por segundo" flow="right">
												<span class="cps">25,1</span>&nbsp;<span class="cps-label">CPS</span>
											</div>
	      								</div>

										<div class="row">
											<div class="col-md-12 sleeper-tooltip" tooltip="¡Guardar!">
												<i class="btn green fa fa-check save"></i>
											</div>
										</div>

										<div class="row">
											<div class="col-md-12 footer">
												<span>
													Made with <i class="fa fa-heart pulse"></i> by&nbsp;&nbsp;<a href="http://www.subadictos.net/" target="_blank">SubAdictos.Net</a>
												</span>
											</div>
										</div>
										
										<!-- <textarea class="form-control" rows="3"></textarea> -->
									</div>
								</div>
							</div>

						</div>
					</div><!-- /scroller-inner -->
					<!-- <div class="footer">
						<span>
							Made with <i class="fa fa-heart pulse"></i> by&nbsp;&nbsp;<a href="http://www.subadictos.net/" target="_blank">SubAdictos.Net</a>
						</span>
					</div> -->
				</div><!-- /scroller -->

			</div><!-- /pusher -->
		</div><!-- /container -->








		<!-- MustacheTemplates >>>> -->

		<script type="template/mustache" id="my-translations-template">
			{{#items}}
				<li class="list-item" seq-id="{{entryID}}">
					<a href="#">
						<strong>#{{sequence}}</strong> - {{title}}
					</a>
				</li>
			{{/items}}
			<li class="load-more">
				<a id="my-translations-more">VER MÁS<br /><i class="fa fa-chevron-down"></i></a>
			</li>
		</script>

		<script type="template/mustache" id="subtitles-template">
			{{#items}}
				<li class="icon icon-arrow-left list-item">
					<a class="subtitle-item" sub-id="{{subId}}">{{title}}</a>
					<div class="mp-level">
						<h2 class="subtitle-title">{{title}}</h2>
						<a class="mp-back" href="#">Atrás</a>
						<div class="nano-container custom-scrollbar">
							<div class="nano">
								<ul id="subtitle-sequences-container" class="nano-content load-loader"></ul>
							</div>
						</div>
					</div>
				</li>
			{{/items}}

		</script>

		<script type="template/mustache" id="subtitle-sequences-template">
			{{#items}}
				<li class="list-item" seq-id="{{entryID}}">
					<a href="#">
						<strong>#{{sequence}}</strong> - {{text}}
					</a>
				</li>
			{{/items}}
			<li class="load-more">
				<a class="more-sequences">VER MÁS<br /><i class="fa fa-chevron-down"></i></a>
			</li>
		</script>

		<script type="template/mustache" id="loader-template">
			<div class="loader">
			  <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
			     width="24px" height="30px" viewBox="0 0 24 30" style="enable-background:new 0 0 50 50;" xml:space="preserve">
			    <rect x="0" y="13" width="4" height="5" fill="#333">
			      <animate attributeName="height" attributeType="XML"
			        values="5;21;5" 
			        begin="0s" dur="0.6s" repeatCount="indefinite" />
			      <animate attributeName="y" attributeType="XML"
			        values="13; 5; 13"
			        begin="0s" dur="0.6s" repeatCount="indefinite" />
			    </rect>
			    <rect x="10" y="13" width="4" height="5" fill="#333">
			      <animate attributeName="height" attributeType="XML"
			        values="5;21;5" 
			        begin="0.15s" dur="0.6s" repeatCount="indefinite" />
			      <animate attributeName="y" attributeType="XML"
			        values="13; 5; 13"
			        begin="0.15s" dur="0.6s" repeatCount="indefinite" />
			    </rect>
			    <rect x="20" y="13" width="4" height="5" fill="#333">
			      <animate attributeName="height" attributeType="XML"
			        values="5;21;5" 
			        begin="0.3s" dur="0.6s" repeatCount="indefinite" />
			      <animate attributeName="y" attributeType="XML"
			        values="13; 5; 13"
			        begin="0.3s" dur="0.6s" repeatCount="indefinite" />
			    </rect>
			  </svg>
			</div>
		</script>

		<script type="template/mustache" id="jconfirm-template">
		  <div class="row">
		  	<div class="col-xs-6">
		  		<i class="btn green fa fa-caret-left"></i>
		  		<div class="jconfirm-label"><span class="two-line">Secuencia<br />anterior</span></div>
		  		<div class="clearfix"></div>
		  	</div>
		  	
		  	<div class="col-xs-6">
		  		<i class="btn green fa fa-caret-right"></i>
		  		<div class="jconfirm-label"><span class="two-line">Secuencia<br />siguiente</span></div>
		  		<div class="clearfix"></div>
		  	</div>
		  	
		  </div>

		  <div class="row">
		  	<div class="col-xs-6">
		  		<i class="btn green fa fa-random"></i>
		  		<div class="jconfirm-label"><span class="two-line">Secuencia<br />aleatoria</span></div>
		  		<div class="clearfix"></div>
		  	</div>

		  	<div class="col-xs-6">
		  		<i class="btn green fa fa-check"></i>
		  		<div class="jconfirm-label"><span class="one-line">Guardar</span></div>
		  		<div class="clearfix"></div>
		  	</div>
		  </div>

		  <div class="row bottom-margin-20">
		  	<div class="col-xs-6">
		  		<div class="jconfirm-label label-text"><span>From:</span><br />Texto a traducir</div>
		  		<div class="clearfix"></div>
		  	</div>

		  	<div class="col-xs-6">
		  		<div class="jconfirm-label label-text"><span>To:</span><br />Traducción</div>
		  		<div class="clearfix"></div>
		  	</div>
		  </div>

		  <div class="row">
		  	<div class="col-xs-6">
		  		<div class="jconfirm-label label-text"><span>CPL:</span><br />Caracteres por&nbsp;línea</div>
		  		<div class="clearfix"></div>
		  	</div>

		  	<div class="col-xs-6">
		  		<div class="jconfirm-label label-text"><span>CPS:</span><br />Caracteres por&nbsp;segundo</div>
		  		<div class="clearfix"></div>
		  	</div>
		  </div>
		</script>

		<!-- <<<< MustacheTemplates -->
		
		<?=add_jscript('jquery.nanoscroller.min')?>
		<?=add_jscript('mustache.min')?>
		<?=add_jscript('subshuffle', true)?>
		<?=add_jscript('modernizr.custom')?>

		<script>
			// $(".nano").nanoScroller();
			new mlPushMenu( document.getElementById( 'mp-menu' ), document.getElementById( 'trigger' ) );

			(function() {
				document.getElementById("to-textarea").focus();
			})();

	  $(document).ready(function () {

	  //         var trigger = $('.hamburger'),
	  //           isClosed = false;

   	  //            trigger.click(function () {
      //              hamburger_cross();      
   	  //            });

	  // 		function hamburger_cross() {
	  //         if (isClosed == true) {          
	  //           trigger.removeClass('is-open');
	  //           trigger.addClass('is-closed');
	  //           isClosed = false;
	  //         } else {   
	  //           trigger.removeClass('is-closed');
	  //           trigger.addClass('is-open');
	  //           isClosed = true;
	  //         }
	  // 		}
	          
	  //         $('[data-toggle="offcanvas"]').click(function () {
	  //               $('#wrapper').toggleClass('toggled');
	  // });  

	 

	  	// PARA LOS ATAJOS
	 	// var btnClick = $("#next");
		// btnClick.toggleClass("active-btn");
		// setTimeout(function () {
		//       btnClick.toggleClass("active-btn");
		// }, 300);
		
		$(".info-tooltips-btn").on("click", function(){
			if (window.matchMedia('(min-width: 992px)').matches) {
		        
		        $(".sleeper-tooltip").each(function(){
					$(this).addClass("activate-tooltip");
				});

				setTimeout(function () {
				    $(".sleeper-tooltip").each(function(){
						$(this).removeClass("activate-tooltip");
					});
				}, 5000);

		    } else {
		        $.alert({
		          animation: 'top',
		          type: 'yellow',
		          columnClass: 'col-lg-4 col-lg-offset-4 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 col-xs-8 col-xs-offset-2',
		          title: '<i class="fa fa-lightbulb-o"></i>&nbsp;&nbsp;Watt?',
		          content: Mustache.render($("#jconfirm-template").html()),
		          backgroundDismiss: true
		      });
		    }
		});

	  });



	var limit = 2;
	var textarea = document.getElementById("to-textarea");
	var spaces = textarea.getAttribute('cols');
	// console.log(spaces);

	textarea.onkeydown = function(event) {

		if (event.ctrlKey || event.metaKey) {
			var shortcut = false;
            switch (String.fromCharCode(event.which).toLowerCase()) {
            case 's':
                event.preventDefault();
                shortcut = true;
                var btnClick = $(".save");
                break;
            case 'i':
                event.preventDefault();
                shortcut = true;
                var btnClick = $(".prev");
                break;
            case 'o':
                event.preventDefault();
                shortcut = true;
                var btnClick = $(".randomize");
                break;
            case 'p':
                event.preventDefault();
                shortcut = true;
                var btnClick = $(".next");
                break;

            }

            if(shortcut) {
            	btnClick.toggleClass("active-btn");
				setTimeout(function () {
				      btnClick.toggleClass("active-btn");
				}, 300);
            }
        }


		var lines = textarea.value.split("\n");
		if(event.keyCode == 10 || event.keyCode == 13) {
			if(lines.length >= limit) {
				textarea.style.color = '#D42D25';
		        setTimeout(function(){
		            textarea.style.color = '';
		        },500);
				return false;
			}
		} else {

		  //  for (var i = 0; i < lines.length; i++) 
		  //  {
		  //        if (lines[i].length <= spaces) continue;
		  //        var j = 0;
		         
		  //       var space = spaces;
		        
		  //       while (j++ <= spaces) 
		  //       {
		  //          if (lines[i].charAt(j) === " ") space = j;  
		  //       }
		  //   lines[i + 1] = lines[i].substring(space + 1) + (lines[i + 1] || "");
		  //   lines[i] = lines[i].substring(0, space);
		  // }
		  //   if(lines.length>limit)
		  //   {
		  //       textarea.style.color = 'red';
		  //       setTimeout(function(){
		  //           textarea.style.color = '';
		  //       },500);
		  //   }    
		  //  textarea.value = lines.slice(0, limit).join("\n");

		}
		    

	};



		</script>
	</body>	
	<?=add_jscript('jquery-confirm')?>
</html>