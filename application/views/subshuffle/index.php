<!DOCTYPE html>
<html lang="en" class="no-js">
	<head>
		<meta charset="UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> 
		<meta name="viewport" content="width=device-width, initial-scale=1.0"> 
		<title>Subshuffle</title>
		<link rel="shortcut icon" href="<?=img_url('subshuffle-favicon-green.ico?adas')?>" type="image/x-icon">
		<!-- <?=add_style('materialize.min')?> -->
		<?=add_style('bootstrap.min')?>
		<!-- <?=add_style('nanoscroller')?> -->
		<?=add_style('font-awesome.min')?>
		<?=add_style('jquery-confirm')?>
		<?=add_style('subshuffle', true)?>
		
		<?=add_jscript('jquery-3.1.1.min')?>
		<?=add_jscript('materialize.min')?>
		<?=add_jscript('bootstrap.min')?>
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
							<!-- <li>
								<a class="first-level icon icon-random" href="#">Modo Aleatorio</a>
							</li> -->

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
												<li><a><em>[Sólo secuencias sin traducir]</em></a></li>
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
										<li><a><em>[Sólo funcionan cuando el cursor se&nbsp;encuentra en el campo de traducción]</em></a></li>
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

							<li>
								<a class="first-level icon icon-legal rules <?=($firstLogIn) ? 'rules-flashing' : ''?>" href="#">Consideraciones</a>
							</li>
							<li>
								<a class="first-level icon icon-bug report-bug" href="#">Reportar bug</a>
							</li>
							<li>
								<a class="first-level icon icon-logout" href="<?=base_url()?>logout">Salir</a>
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
					            <span class="hamb-top <?=($firstLogIn) ? 'flashing' : ''?>"></span>
					            <span class="hamb-middle <?=($firstLogIn) ? 'flashing' : ''?>"></span>
					            <span class="hamb-bottom <?=($firstLogIn) ? 'flashing' : ''?>"></span>
								<div class="subshuffle">subshuffle</div>
								<div class="path">#<span>beta</span></div>
								<!-- <div class="path">#<span>ModoAleatorio</span></div> -->
								<!-- <div class="path">#<span>MisTraducciones</span></div> -->
								<!-- <div class="path">@<span>SubAdictos</span></div> -->
								<!-- <div class="path">#<span>Subtítulos</span></div> -->
								<!-- <div class="path">#<span>FlowerShopMystery</span></div> -->
								<!-- <div class="path">#<span>Black-ish</span></div> -->
								<!-- <div class="path">#<span>Black-ish - 03x04 - Who is Afraid of the Big Black Man</span></div> -->
								<!-- Si subtitula no conduzca -->
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
											<!-- <div class="col-xs-1"> -->
												<!-- <i class="ic ic-eye">view</i> -->
												<!-- <i class="fa fa-list"></i> -->
											<!-- </div> -->
											<div class="col-xs-9 title-info"></div>
											<div class="col-xs-3 text-right sleeper-tooltip" tooltip="Número de secuencia" flow="right">#&nbsp;<span class="sequence-number"></span></div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6 col-md-offset-3">
										<div class="row">
											<div class="col-xs-4 sleeper-tooltip" tooltip="Secuencia anterior" flow="down">
												<i id="prev" class="btn green fa fa-caret-left prev neighbour-seq"></i>
											</div>
											<div class="col-xs-4 sleeper-tooltip" tooltip="Secuencia aleatoria" flow="down">
												<i id="random" class="btn green fa fa-random randomize"></i>
											</div>
											<div class="col-xs-4 sleeper-tooltip" tooltip="Secuencia siguiente" flow="down">
												<i id="next" class="btn green fa fa-caret-right next neighbour-seq"></i>
											</div>
										</div>
										 <!-- data-toggle="modal" data-target="#contextModal" data-backdrop="false" -->

										<div class="row">
											<div class="col-md-12 sleeper-tooltip" tooltip="Texto a traducir" flow="left">
												<div class="from-textarea">
													<label>From:</label>
													<p></p>
												</div>
											</div>
										</div>
	      								
										<div class="row">
											<div class="col-md-12 sleeper-tooltip" tooltip="Traducción" flow="left">
										        <div class="container-textarea">
											        <div class="form-group-textarea textarea-alert" tooltip="Máximo: 42 caracteres por línea, 2 líneas">
											        	<textarea id="to-textarea" required="required" cols="42" rows="2" wrap="hard"></textarea>
		      											<label class="control-label" for="to-textarea">To:</label><i class="bar"></i>
		      										</div>
		      									</div>
											</div>
										</div>

										<div class="row subtitle-data">	
											<div class="col-xs-6 text-left sleeper-tooltip" tooltip="Caracteres por línea" flow="left">
												<span class="chars first-line-chars">0</span>&nbsp;<span>/</span>&nbsp;<span class="chars second-line-chars">0</span>&nbsp;&nbsp;<span class="cps-label">CPL</span>
											</div>
											<div class="col-xs-6 text-right sleeper-tooltip" tooltip="Caracteres por segundo" flow="right">
												<span class="cps">0</span>&nbsp;&nbsp;<span class="cps-label">CPS</span>
											</div>
	      								</div>

										<div class="row">
											<div class="col-md-12 sleeper-tooltip" tooltip="¡Guardar!">
												<i id="save" class="btn green fa fa-check save"></i>
											</div>
										</div>

										<div class="row">
											<div class="col-md-12 footer">
												<span class="adictito sleeper-tooltip" tooltip="test" flow="down">
													Made with <i class="fa fa-heart pulse"></i> by&nbsp;&nbsp;<a href="http://www.subadictos.net/" target="_blank">SubAdictos.Net</a><br />
													Powered By <a href="http://wiki-adictos.subadictos.net/" target="_blank">Wiki-Adictos</a>
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
		<!-- Context -->
		<div class="modal right fade" id="contextModal" role="dialog" aria-labelledby="contextLabel">
			<div class="modal-dialog">
				<div id="floating-modal-close" data-dismiss="modal"><span class="close-context thick"></span></div>
				<div class="modal-content custom-scrollbar">

					<!-- <div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="contextLabel">Right Sidebar</h4>
					</div> -->

					<div class="modal-body">
						<ul class="tabs clearfix" data-tabgroup="first-tab-group">
						  <li><a href="#tab1" class="active">English</a></li>
						  <li><a href="#tab2">Español</a></li>
						</ul>
						<section id="first-tab-group" class="tabgroup">
						  <div id="tab1">
						    <h2>Heading 1</h2>
						    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nulla deserunt consectetur ratione id tempore laborum laudantium facilis reprehenderit beatae dolores ipsum nesciunt alias iusto dicta eius itaque blanditiis modi velit.</p>
						  </div>
						  <div id="tab2">
						    <h2>Heading 2</h2>
						    <p>Adipisci autem obcaecati velit natus quos beatae explicabo at tempora minima voluptates deserunt eum consectetur reiciendis placeat dolorem repellat in nam asperiores impedit voluptas iure repellendus unde eveniet accusamus ex.</p>
						  </div>
						</section>
					</div>

				</div>
			</div>
		</div>

		


	</div><!-- /container -->
	
	<div id="saved">
		<i class="fa fa-check-circle fa-5x"></i>
		<div>¡Guardado!</div>	
	</div>

	<div id="bug-sent">
		<i class="fa fa-bug fa-5x"></i>
		<div>¡Bug reportado, muchas gracias!<br />En breve lo estaremos investigando...</div>	
	</div>

<?php if($firstLogIn) { ?>
	<div id="welcome">
		<div class="first-message">¡Bienvenido!</div>
		<div class="last-message">
			<div class="fade-in five">¡Happy subtitling!</div>
		</div>
		<div class="second-message">
			<div>				
				<div class="fade-in one">Subadictos presenta...</div>
				<div class="fade-in two"><span>Subshuffle</span>.</div>
				<span class="fade-in three">La herramienta de&nbsp;subtitulación colaborativa que nadie pedía...</span>
				<span class="fade-in four">pero que todos merecemos.</span>
			</div>			
		</div>
	</div>
<?php } ?>


	<div id="log">
		<div class="last-random"></div>
		<div class="last-sequence"></div>
		<div class="last-translations"></div>	
		<div class="last-sequences"></div>
	</div>





		<!-- MustacheTemplates >>>> -->

		<script type="template/mustache" id="my-translations-template">
			{{#items}}
				<li class="list-item" seq-id="{{entryID}}">
					<a href="#" class="final-item" sub-id="{{subID}}" seq-num="{{sequence}}">
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
					<a href="#" class="final-item" sub-id="{{subID}}" seq-num="{{sequence}}">
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


		<script type="template/mustache" id="considerations-template">
		<div class="row">
			<div class="col-xs-12">
			Antes de realizar cualquier traducción, lee el <a href="http://www.subadictos.net/foros/showthread.php?t=4248" target="_blank">Manual de estilo de Traducción SubAdictos</a>. A&nbsp;continuación se enumeran algunas de las consideraciones más importantes:
			</div>
		</div>
		<div class="row">
			<div class="col-xs-1">1.</div>
			<div class="col-xs-11">
				<strong>MUY IMPORTANTE:</strong> No pueden tomarse ni basarse en traducciones de otros subtítulos que ya existen en ESPAÑOL. Tampoco se pueden utilizar traductores automáticos.
			</div>
		</div>

		<div class="row">
			<div class="col-xs-1">2.</div>
			<div class="col-xs-11">
				Cada subtítulo deber tener un máximo de 2 líneas y cada línea un máximo de 40 caracteres. Las líneas largas, si no se pueden reducir, el corrector se encargará de dividirlas.
			</div>
		</div>

		<div class="row">
			<div class="col-xs-1">3.</div>
			<div class="col-xs-11">
				Si hablan dos personas y el guión (-) inicial no está, habrá que agregarlo. Luego del guión inicial (-), debe ir un espacio.
			</div>
		</div>

		<div class="row">
			<div class="col-xs-1">4.</div>
			<div class="col-xs-11">
				Eliminar los wow, ups, oh, ah, hey, ey, etc. que aparezcan, ya que estos NO SE TRADUCEN NI SE DEJAN EN EL SUBTÍTULO.
			</div>
		</div>

		<div class="row">
			<div class="col-xs-1">5.</div>
			<div class="col-xs-11">
				Recuerden siempre poner los signos de apertura de interrogación (¿) y exclamación (¡).
			</div>
		</div>

		<div class="row">
			<div class="col-xs-1">6.</div>
			<div class="col-xs-11">
				Se deben eliminar los <strong>puntos suspensivos</strong> del final de le línea, estos serán agregados en la corrección.
			</div>
		</div>

		<div class="row">
			<div class="col-xs-1">7.</div>
			<div class="col-xs-11">
				Si queda alguna parte con dudas poner al final de la línea <strong>[REVISAR]</strong> (de esa forma), así el subtitle workshop la toma como un warning de subtítulos para sordos y es más fácil para el corrector.
			</div>
		</div>

		<div class="row">
			<div class="col-xs-1">8.</div>
			<div class="col-xs-11">
				Si se debe eliminar alguna línea COMPLETA poner <strong>[ELIMINAR]</strong> (de esa forma), así el subtitle workshop la toma como un warning de subtítulos para sordos y es más fácil para el corrector.
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
			var menu = new mlPushMenu( document.getElementById( 'mp-menu' ), document.getElementById( 'trigger' ) );

			(function() {
				document.getElementById("to-textarea").focus();
			})();

	  $(document).ready(function () {

	  	<?php if($firstLogIn) { ?>
		  	setTimeout(function () {
			    $('#welcome').fadeOut('slow');
			}, 15000);
		<?php } ?>

	  	if (window.matchMedia('(max-width: 540px)').matches) document.getElementById("to-textarea").setAttribute("rows", "4");
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
		          type: 'orange',
		          columnClass: 'col-lg-4 col-lg-offset-4 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 col-xs-8 col-xs-offset-2',
		          title: '<i class="fa fa-lightbulb-o"></i>&nbsp;&nbsp;Watt?',
		          content: Mustache.render($("#jconfirm-template").html()),
		          backgroundDismiss: true,
		          buttons: {
			          okay: {
			          	text: "GOT IT",
			          	keys: ['enter']
			          }		          	
		          }
		      });
		    }
		});

		$(".rules").on("click", function(){
			if($(this).hasClass('rules-flashing')) {
				$(this).removeClass("rules-flashing");
				$.post( "subshuffle/checkedConsiderations");
			}
			

			menu._resetMenu();
			$.alert({
	          animation: 'top',
	          type: 'purple',
	          columnClass: 'col-lg-8 col-lg-offset-2 considerations',
	          title: '<i class="fa fa-gavel"></i>&nbsp;&nbsp;Consideraciones para los traductores',
	          content: Mustache.render($("#considerations-template").html()),
	          backgroundDismiss: false,
	          buttons: {
		          okay: {
		          	text: "¡Leí y acepto los Términos y Condiciones!"
		          }		          	
	          }
	        });		    
		});

		$('.report-bug').on('click',function(){
			menu._resetMenu();
			$.confirm({
			    title: '<i class="fa fa-bug"></i>&nbsp;&nbsp;Reportar bug',
				type: 'red',
				columnClass: 'col-md-6 col-md-offset-3',
			    content: '' +
			    '<form action="" id="reportForm">' +
			    '<div class="form-group bug-reporter">' +
			    '<label>Describe el problema:</label>' +
			    '<textarea rows="6" class="report form-control" name="report" required />' +
			    '</div>' +
			    '</form>',
			    buttons: {
			        formSubmit: {
			            text: 'Enviar',
			            btnClass: 'btn-submit',
			            action: function () {
			                var report = this.$content.find('.report').val();
			                if(!report){
			                    $.alert('Descripción insuficiente.');
			                    return false;
			                } else {
			                	$.post( "subshuffle/reportBug", { report : report }, function(data){
			                		if(data) {
			                			$('#bug-sent').fadeIn();
			                			setTimeout(function(){ $('#bug-sent').fadeOut(); }, 3000);
			                		} else {
			                			$.confirm({
										   title: 'Error inception :/',
										    content: 'Hubo un error reportando el error, mejor envía un mail a "<strong>wallytarantino@gmail.com</strong>". Muchas gracias y perdón por tantos inconvenientes, ¡por suerte es la versión beta!',
										    autoClose: 'cancel|20000',
										    buttons: {
										        cancel: {
										        	text: 'Ya le mando',
										        	btnClass: 'errorInception',
										        	action: function () {
										        	}
										        }
										    }
										});
			                		}
			                	});
			                }
			            }
			        },
			        cancel: {
			        	text: 'Cancelar',
			        	action: function () {}
			        }
			    },
			    onContentReady: function () {
			        // bind to events
			        var jc = this;
			        this.$content.find('form').on('submit', function (e) {
			            // if the user submits the form by pressing enter in the field.
			            e.preventDefault();
			            jc.$$formSubmit.trigger('click'); // reference the button and click it
			        });
			    }
			});
		});


		$('.tabgroup > div').hide();
		$('.tabgroup > div:first-of-type').show();
		$('.tabs a').click(function(e){
		  e.preventDefault();
		    var $this = $(this),
		        tabgroup = '#'+$this.parents('.tabs').data('tabgroup'),
		        others = $this.closest('li').siblings().children('a'),
		        target = $this.attr('href');
		    others.removeClass('active');
		    $this.addClass('active');
		    $(tabgroup).children('div').hide();
		    $(target).show();
		  
		})
		    

	  });



	var limit = 2;
	var textarea = document.getElementById("to-textarea");
	var spaces = textarea.getAttribute('cols');

	textarea.onkeydown = function(event) {

		if (event.ctrlKey || event.metaKey) {
			var shortcut = false;
            switch (String.fromCharCode(event.which).toLowerCase()) {
            case 's':
                event.preventDefault();
                shortcut = true;
                var btnClick = $("#save");
                break;
            case 'i':
                event.preventDefault();
                shortcut = true;
                var btnClick = $("#prev");
                break;
            case 'o':
                event.preventDefault();
                shortcut = true;
                var btnClick = $("#random");
                break;
            case 'p':
                event.preventDefault();
                shortcut = true;
                var btnClick = $("#next");
                break;
            }

            if(shortcut) {
            	btnClick.click();
            	btnClick.toggleClass("active-btn");
				setTimeout(function () {
				      btnClick.toggleClass("active-btn");
				}, 300);
            }
        } else {
        	if(event.keyCode == 10 || event.keyCode == 13) {
        		var lines = textarea.value.split("\n");
				if(lines.length >= limit) {
					textarea.style.color = '#D42D25';
					$(textarea).parent().addClass('activate-tooltip');
			        setTimeout(function(){
			            textarea.style.color = '';
			            $(textarea).parent().removeClass('activate-tooltip');
			        }, 1000);
					return false;
				}
			}
        }
	};

	textarea.onkeyup = function(event) {
		if (event.ctrlKey || event.metaKey) {
			var shortcut = false;
            switch (String.fromCharCode(event.which).toLowerCase()) {
            case 'v':
            	translationControl ();
            	break;
            }

            if(shortcut) {
            	btnClick.click();
            	btnClick.toggleClass("active-btn");
				setTimeout(function () {
				      btnClick.toggleClass("active-btn");
				}, 300);
            }
        } else {
        	if(event.keyCode != 10 && event.keyCode != 13) {
				translationControl ();        	
        	}
        }
	}

	function translationControl () {
		var lines = textarea.value.split("\n");
		// if(event.keyCode == 10 || event.keyCode == 13) {
		// 	if(lines.length >= limit) {
		// 		textarea.style.color = '#D42D25';
		//         setTimeout(function(){
		//             textarea.style.color = '';
		//         },500);
		// 		return false;
		// 	}
		// } else {
		var firstLine = lines[0];
		var secondLine = lines[1];

		if(firstLine.length > spaces) {
			// var output = [firstLine.slice(0, spaces), "\n", firstLine.slice(spaces), secondLine].join('');

			var cutPosition = firstLine.lastIndexOf(" ");
			if(cutPosition < 10 || cutPosition > spaces) cutPosition = spaces;

			secondLine = ([firstLine.slice(cutPosition), secondLine].join('')).trim();
			firstLine = (firstLine.slice(0, cutPosition)).trim();
		}

		var output = firstLine;

		if(secondLine) {
			if(secondLine.length > spaces) {
				secondLine = secondLine.slice(0,spaces);
				textarea.style.color = '#D42D25';
				$(textarea).parent().addClass('activate-tooltip');
		        setTimeout(function(){
					$(textarea).parent().removeClass('activate-tooltip');
		            textarea.style.color = '';
		        },1000);
			}
			output += "\n"+secondLine;
		}

		subshuffle.cpl(output);
		var cps = subshuffle.cps(output, $(textarea).attr('data-duration'));
		$(".cps").text(cps);
		if(cps.replace(",", ".") > 25) $(".cps").addClass('over-cps');
		else $(".cps").removeClass('over-cps');
		textarea.value = output;

		   // for (var i = 0; i < lines.length; i++) 
		   // {
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

		// }
	}



		</script>
	</body>	
	<?=add_jscript('jquery-confirm')?>
</html>