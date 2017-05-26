<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>SubEnhancer</title>
  
  <?=add_style('bootstrap.min')?>
  <?=add_style('fileinput.min')?>
  <?=add_style('jquery-confirm')?>

  <?=add_jscript('jquery-3.1.1.min')?>
  <?=add_jscript('fileinput.min')?>
  <?=add_jscript('jquery-confirm')?>
  <?=add_jscript('bootstrap.min')?>
  <?=add_jscript('typeahead.bundle.min')?>

  <!-- <script src="js/dropzone.js"></script> -->
  <?php echo "<script>var dbg = ".(isset($dbg) ? "1" : "0")."</script>\n"; ?>
  <script>
    var baseUrl = '<?=base_url()?>';
    var tv_show_array = <?php echo json_encode($dataArray['tv_show'] );?>;
    var codec_array = <?php echo json_encode($dataArray['codec'] );?>;
    var format_array = <?php echo json_encode($dataArray['format'] );?>;
    var quality_array = <?php echo json_encode($dataArray['quality'] );?>;
    var rip_group_array = <?php echo json_encode($dataArray['rip_group'] );?>;
    var other_array = <?php echo json_encode($dataArray['other'] );?>;
    var task_array = <?php echo json_encode($dataArray['task'] );?>;
    var subtitler_array = <?php echo json_encode($dataArray['subtitler'] );?>;
  </script>

  <?=add_jscript('script',true)?>
  
  <link href="https://fonts.googleapis.com/css?family=Bitter:400,400i,700|Catamaran:300,400,500,600,700|Open+Sans:300,300i,400,400i,600,600i,700,700i|Rokkitt:400,700" rel="stylesheet">

  <!-- <link rel="stylesheet" href="css/font-awesome.min.css"> -->
  <?=add_style('font-awesome.min')?>
  <!-- <link rel="stylesheet" href="css/style.css"> -->
  <?=add_style('style',true)?>
  <!-- <link rel='stylesheet' href='css/nprogress.css'/> -->
  <?=add_style('nprogress')?>

  <?=add_style('perfect-scrollbar')?>
  <?=add_style('jquery-ui.min')?>
  <?=add_style('magicsuggest-min')?>
  <?=add_style('material-design-icons')?>
  
</head>
<body>
  <form action="subenhancer/enhance" method="POST" enctype="multipart/form-data" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" id="enhance">
    <div class="container-fluid">
        <div class="logout">
          <a href="<?=base_url()?>logout" title="Cerrar sesión">
            <span class="fa-stack">
              <i class="fa fa-circle fa-stack-1x"></i>
              <i class="fa fa-times-circle fa-stack-1x"></i>
            </span>
          </a>
        </div>
        <img src="<?=img_url('adictito.png')?>" alt="" class="adictito">
        <div class="row centered-form">
        <div class="col-md-12 main-body">
          <div class="panel">
            <div class="panel-heading">
              <h3 class="panel-title text-center"><span>SubEnhancer</span> <small>by SubAdictos.Net</small></h3>
            </div>
            <div class="panel-body">
                <div class="row">
                  <div class="col-xs-12 col-sm-6 col-md-6">
                    <div class="form-group">
                      <label for="sub_url">URL del subtítulo&nbsp;&nbsp;<i class="fa fa-info-circle sub-url" aria-hidden="true"></i></label>
                      <input type="text" name="sub_url" id="sub_url" class="form-control input-sm" placeholder="Ej: https://www.tusubtitulo.com/updated/5/20739/0">
                    </div>
                  </div>
                  <label class="loose-text hidden-xs">o</label>
                  <div class="col-xs-12 col-sm-6 col-md-6">
                    <div class="form-group">
                      <label class="control-label" for="sub_url">Archivo del subtítulo</label>
                      <!-- <form action="" class="dropzone"> -->
                      <input id="input-sub-file" name="uploaded_file" type="file" class="file" data-show-preview="false" data-show-remove="true" data-show-upload="false" multiple>
                      <!-- </form> -->
                    </div>
                  </div>
                </div>

                <hr class="divider">

                <div class="row">
                  <div class="col-xs-12 col-sm-12 col-md-12">
                    <div class="form-group">
                      <label for="info_url">URL de la página del subtítulo (opcional)&nbsp;&nbsp;<i class="fa fa-info-circle info-url" aria-hidden="true"></i></label>
                      <input type="text" name="info_url" id="info_url" class="form-control input-sm" placeholder="Ej: https://www.tusubtitulo.com/serie/community/3/4/367/">
                    </div>
                  </div>
                  <!--  -->                  

                  <div class="col-xs-6 col-sm-8 col-md-8">
                    <div class="form-group">
                      <label for="tv_show">Serie</label>
                      <div class="input-group" id="tvshow-suggest">
                        <input type="text" name="tv_show" id="tv_show" class="form-control input-sm typeahead">
                        <div class="input-group-btn">
                          <button type="button" class="btn dropdown-toggle more-options" data-toggle="dropdown" tabindex="-1" aria-haspopup="true" aria-expanded="false"><i class="fa fa-caret-down" aria-hidden="true"></i></button>
                          <ul class="dropdown-menu dropdown-menu-right container-scrollable" data-list="tv_show">
                            <div class="content-scrollable">
                              
                              <?php foreach($dataArray['tv_show'] as $show) { ?>
                                <li class="dropdown-item"><?=$show?></li>
                              <?php } ?>
                            </div>
                          </ul>                          
                        </div>
                      </div>
                    </div>
                  </div>

                  <!--  -->
                  <div class="col-xs-3 col-sm-2 col-md-2">
                    <div class="form-group small-size-input">
                      <label class="align-center" for="season">Temporada</label>
                      <input name="season" id="season" class="form-control input-sm spinner" min="1">
                      <!-- <input name="season" id="season" class="form-control input-sm spinner" min="1"> -->
                    </div>
                  </div>
                  <div class="col-xs-3 col-sm-2 col-md-2">
                    <div class="form-group small-size-input">
                      <label for="episode_number">Episodio</label>
                      <input name="episode_number" id="episode_number" class="form-control input-sm spinner" min="1">
                    </div>
                  </div>
                  <!--  -->
                  <div class="col-xs-12 col-sm-12 col-md-12">
                    <div class="form-group">
                      <label for="episode_title">Nombre del episodio&nbsp;&nbsp;<i class="fa fa-info-circle title-info" aria-hidden="true"></i></label>
                      <input type="text" name="episode_title" id="episode_title" class="form-control input-sm">
                    </div>
                  </div>

                  <!-- UNCOMMENT -->
                  <!-- <div class="col-xs-12 col-sm-6 col-md-6">
                    <div class="form-group">
                      <label for="episode_title_spa">Nombre del episodio traducido (para los créditos)</label>
                      <input type="text" name="episode_title_spa" id="episode_title_spa" class="form-control input-sm">
                    </div>
                  </div> -->
                </div>

                <hr class="divider">

                <div class="row">
                  <div class="col-xs-4 col-sm-4 col-md-4">
                    <div class="form-group">
                        <label for="other">Otro</label>
                        <div class="input-group" id="other-suggest">  
                          <input type="text" name="other" id="other" class="form-control input-sm typeahead">
                          <div class="input-group-btn">
                            <button type="button" class="btn dropdown-toggle more-options" data-toggle="dropdown" tabindex="-1" aria-haspopup="true" aria-expanded="false"><i class="fa fa-caret-down" aria-hidden="true"></i></button>
                            <ul class="dropdown-menu dropdown-menu-right container-scrollable" data-list="other">
                              <div class="content-scrollable">
                                <?php foreach($dataArray['other'] as $other) { ?>
                                  <li class="dropdown-item"><?=$other?></li>
                                <?php } ?>
                              </div>
                            </ul>
                          </div>
                        </div>
                    </div>
                  </div>

                  <div class="col-xs-4 col-sm-4 col-md-4">
                    <div class="form-group">
                        <label for="quality">Calidad</label>
                        <div class="input-group" id="quality-suggest">  
                          <input type="text" name="quality" id="quality" class="form-control input-sm typeahead">
                          <div class="input-group-btn">
                            <button type="button" class="btn dropdown-toggle more-options" data-toggle="dropdown" tabindex="-1" aria-haspopup="true" aria-expanded="false"><i class="fa fa-caret-down" aria-hidden="true"></i></button>
                            <ul class="dropdown-menu dropdown-menu-right container-scrollable" data-list="quality">
                              <div class="content-scrollable">
                                <?php foreach($dataArray['quality'] as $quality) { ?>
                                  <li class="dropdown-item"><?=$quality?></li>
                                <?php } ?>
                              </div>
                            </ul>
                          </div>
                        </div>
                    </div>
                  </div>
                  
                  <div class="col-xs-4 col-sm-4 col-md-4">
                    <div class="form-group">
                      <label for="format">Formato</label>
                      <div class="input-group" id="format-suggest">
                        <input type="text" name="format" id="format" class="form-control input-sm typeahead" value="HDTV">
                        <div class="input-group-btn">
                          <button type="button" class="btn dropdown-toggle more-options" data-toggle="dropdown" tabindex="-1" aria-haspopup="true" aria-expanded="false"><i class="fa fa-caret-down" aria-hidden="true"></i></button>
                          <ul class="dropdown-menu dropdown-menu-right container-scrollable" data-list="format">
                            <div class="content-scrollable">
                              <?php foreach($dataArray['format'] as $format) { ?>
                                <li class="dropdown-item"><?=$format?></li>
                              <?php } ?>
                            </div>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                
                  <div class="col-xs-4 col-sm-4 col-md-4">
                    <div class="form-group">
                      <label for="codec">Codec</label>
                        <div class="input-group" id="codec-suggest"> 
                          <input type="text" name="codec" id="codec" class="form-control input-sm typeahead" value="x264">
                          <div class="input-group-btn">
                            <button type="button" class="btn dropdown-toggle more-options" data-toggle="dropdown" tabindex="-1" aria-haspopup="true" aria-expanded="false"><i class="fa fa-caret-down" aria-hidden="true"></i></button>
                            <ul class="dropdown-menu dropdown-menu-right container-scrollable" data-list="codec">
                              <div class="content-scrollable">
                                <?php foreach($dataArray['codec'] as $codec) { ?>
                                  <li class="dropdown-item"><?=$codec?></li>
                                <?php } ?>
                              </div>
                            </ul>
                          </div>
                        </div>
                    </div>
                  </div>

                  <div class="col-xs-4 col-sm-4 col-md-4">
                    <div class="form-group">
                        <label for="rip_group">Grupo</label>
                        <div class="input-group" id="ripgroup-suggest">
                          <input type="text" name="rip_group" id="rip_group" class="form-control input-sm typeahead">
                          <div class="input-group-btn">
                            <button type="button" class="btn dropdown-toggle more-options" data-toggle="dropdown" tabindex="-1" aria-haspopup="true" aria-expanded="false"><i class="fa fa-caret-down" aria-hidden="true"></i></button>
                            <ul class="dropdown-menu dropdown-menu-right container-scrollable" data-list="rip_group">
                              <div class="content-scrollable">
                                <?php foreach($dataArray['rip_group'] as $group) { ?>
                                  <li class="dropdown-item"><?=$group?></li>
                                <?php } ?>
                              </div>
                            </ul>
                          </div>
                        </div>
                    </div>
                  </div>

                  <!-- <div class="col-xs-4 col-sm-4 col-md-4">
                    <div class="form-group">
                        <label for="ocr" class="ocr-label">OCR</label>
                        <div class="ocr">
                          <input type="checkbox" name="ocr" id="ocr" value="true" class="form-control ocr-checkbox" >
                        </div>
                    </div>
                  </div> -->
                  
                  <!--  -->
                  <!-- <div class="col-xs-12 col-sm-12 col-md-12">
                    <div class="form-group">
                        <label for="editor">Correctores</label>
                        <div class="input-group" id="editor-suggest">  
                          <input type="text" name="editor" id="editor" class="form-control input-sm typeahead">
                          <div class="input-group-btn">
                            <button type="button" class="btn dropdown-toggle more-options" data-toggle="dropdown" tabindex="-1" aria-haspopup="true" aria-expanded="false"><i class="fa fa-caret-down" aria-hidden="true"></i></button>
                            <ul class="dropdown-menu dropdown-menu-right container-scrollable" data-list="editor">
                              <div class="content-scrollable">
                                <?php foreach($dataArray['task'] as $task) { ?>
                                  <li class="dropdown-item"><?=$task?></li>
                                <?php } ?>
                              </div>
                            </ul>
                          </div>
                        </div>
                    </div>
                  </div> -->
                  <!--  -->
                  <!-- <div class="col-xs-12 col-sm-12 col-md-12">
                    <div class="form-group">
                      <label for="translation">Traducción original</label>
                      <div class="input-group" id="translation-suggest">
                        <input type="text" name="translation" id="translation" class="form-control input-sm typeahead">
                        <div class="input-group-btn">
                          <button type="button" class="btn dropdown-toggle more-options" data-toggle="dropdown" tabindex="-1" aria-haspopup="true" aria-expanded="false"><i class="fa fa-caret-down" aria-hidden="true"></i></button>
                          <ul class="dropdown-menu dropdown-menu-right container-scrollable" data-list="translation">
                            <div class="content-scrollable">
                              <?php foreach($dataArray['translation'] as $translation) { ?>
                                <li class="dropdown-item"><?=$translation?></li>
                              <?php } ?>
                            </div>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div> -->

                  

                  <!-- UNCOMMENT -->
                  <!-- <div class="col-md-12 task-row">
                  <div class="row">

                  <div class="col-xs-4 col-sm-4 col-md-4">
                    <div class="form-group">
                      <label for="task">Créditos</label>
                      <div class="input-group" id="task-suggest">
                        <input type="text" name="task" id="task" class="form-control input-sm typeahead">
                        <div class="input-group-btn">
                          <button type="button" class="btn dropdown-toggle more-options" data-toggle="dropdown" tabindex="-1" aria-haspopup="true" aria-expanded="false"><i class="fa fa-caret-down" aria-hidden="true"></i></button>
                          <ul class="dropdown-menu dropdown-menu-right container-scrollable" data-list="task">
                            <div class="content-scrollable">
                              <?php foreach($dataArray['task'] as $task) { ?>
                                <li class="dropdown-item"><?=$task?></li>
                              <?php } ?>
                            </div>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-xs-8 col-sm-8 col-md-8">
                    <div class="row">
                      <div class="col-xs-12 col-sm-12 col-md-12">
                        <label class="label-style">Subtitulador&nbsp;&nbsp;<i class="fa fa-info-circle info-subtitler" aria-hidden="true"></i></label>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-xs-10 col-sm-10 col-md-10">
                        <div id="magicsuggest"></div>
                      </div>
                      <div class="col-xs-2 col-sm-2 col-md-2 remove-padding-left">
                        <button class="fab primary mdi-plus" type="button">
                          <div class="ripples black"><span class="ripple"></span></div>
                        </button>
                      </div>
                    </div>
                  </div>

                  </div>
                  </div> -->
                </div>

                
                <!-- UNCOMMENT -->
                <!-- 
                <hr class="divider">
                <div class="row options-row">
                  <div class="col-md-12 options-submenu"><span>OPCIONES&nbsp;&nbsp;<em>(Experimental)</em></span><span></span>
                  <span><i class="fa fa-chevron-right"></i></span>
                  </div>

                  <div class="col-md-12" style="color:#fff;">
                    <div class="options-content collapsed" style="display:none;">
                      <div class="row">
                        <div class="col-md-12 ocr-disclaimer">
                        <div class="well">
                          CLÁUSULA DE AUSENCIA DE GARANTÍA Y LIMITACIÓN DE RESPONSABILIDADES.
                          <br>
                          Opciones en etapa de prueba, use bajo su propio riesgo y por favor reporte cualquier error para ayudarnos a mejorar la herramienta. ¡Muchas gracias!
                          <br>
                          * Para una optimización más eficiente, no activar todos los OCRs, ni OCRs que se solapen en cuanto a su funcionalidad.
                        </div>
                        </div>

                        <div class="col-sm-6">
                          <div class="row">
                            <div class="col-md-12">
                              <label class="switch">
                                <input type="checkbox">
                                <div class="slider round"></div>
                              </label>
                              <label class="switch">
                                <input type="checkbox">
                                <div class="slider round revision"></div>
                              </label>
                              <label class="switch-label">
                                Reduce
                              </label>
                            </div>

                            <div class="col-md-12">
                              <label class="switch">
                                <input type="checkbox">
                                <div class="slider round"></div>
                              </label>
                              <label class="switch">
                                <input type="checkbox">
                                <div class="slider round revision"></div>
                              </label>
                              <label class="switch-label">
                                Reduce@
                              </label>
                            </div>

                            <div class="col-md-12">
                              <label class="switch">
                                <input type="checkbox">
                                <div class="slider round"></div>
                              </label>
                              <label class="switch">
                                <input type="checkbox">
                                <div class="slider round revision"></div>
                              </label>
                              <label class="switch-label">
                                Reduce@@
                              </label>
                            </div>

                            <div class="col-md-12">
                              <label class="switch">
                                <input type="checkbox">
                                <div class="slider round"></div>
                              </label>
                              <label class="switch">
                                <input type="checkbox">
                                <div class="slider round revision"></div>
                              </label>
                              <label class="switch-label">
                                Reduce v2
                              </label>
                            </div>

                            <div class="col-md-12">
                              <label class="switch">
                                <input type="checkbox">
                                <div class="slider round"></div>
                              </label>
                              <label class="switch">
                                <input type="checkbox">
                                <div class="slider round revision"></div>
                              </label>
                              <label class="switch-label">
                                Reduce#
                              </label>
                            </div>

                            <div class="col-md-12">
                              <label class="switch">
                                <input type="checkbox">
                                <div class="slider round"></div>
                              </label>
                              <label class="switch">
                                <input type="checkbox">
                                <div class="slider round revision"></div>
                              </label>
                              <label class="switch-label">
                                Reduce##
                              </label>
                            </div>

                            <div class="col-md-12">
                              <label class="switch">
                                <input type="checkbox">
                                <div class="slider round"></div>
                              </label>
                              <label class="switch">
                                <input type="checkbox">
                                <div class="slider round revision"></div>
                              </label>
                              <label class="switch-label">
                                Asia-team
                              </label>
                            </div>

                            <div class="col-md-12">
                              <label class="switch">
                                <input type="checkbox">
                                <div class="slider round"></div>
                              </label>
                              <label class="switch">
                                <input type="checkbox">
                                <div class="slider round revision"></div>
                              </label>
                              <label class="switch-label">
                                Unidades de sentido
                              </label>
                            </div>
                            
                          </div>
                        </div>
                        <div class="col-sm-6">
                          <div class="row">
                            <div class="col-md-12">
                              <label class="switch">
                                <input type="checkbox">
                                <div class="slider round"></div>
                              </label>
                              <label class="switch-label">
                                Activar todos los OCR
                              </label>
                            </div>

                            <div class="col-md-12">
                              <label class="switch">
                                <input type="checkbox">
                                <div class="slider round revision"></div>
                              </label>
                              <label class="switch-label">
                                Modo "revisión"
                              </label>
                            </div>

                          </div>
                        </div>

                      </div>   
                    </div>
                  </div>
                </div> -->
                
                <hr class="divider">

                <div class="row">
                  <div class="col-xs-3 edit-fields">
                    <i class="fa fa-pencil fa-2" aria-hidden="true"></i>&nbsp;&nbsp;<a href="<?php echo base_url() ?>subenhancer/editor"><span class="hidden-xxs">Editar&nbsp;datos</span><span class="hidden-xs">&nbsp;guardados</span><span class="hidden show-xxs">Datos</span></a>
                    <br>
                    <i class="fa fa-comment fa-2" aria-hidden="true"></i>&nbsp;&nbsp;<a href="<?php echo base_url() ?>subenhancer/ocrEditor"><span class="hidden-xxs">Editor </span>OCR</a>
                    <br>
                    <i class="fa fa-bug fa-2" aria-hidden="true"></i>&nbsp;&nbsp;<a href="<?php echo base_url() ?>subenhancer/bug"><span class="hidden-xxs">Reportar&nbsp;bug</span><span class="hidden show-xxs">Bug</span></a>
                  </div>
                  <div class="col-xs-3 reset-col">
                    <div class="form-group align-center">
                      <input type="reset" id="reset-button" value="LIMPIAR" class="btn btn-info reset-button">
                    </div>
                  </div>
                  <div class="col-xs-3 optimize-col">
                    <div class="form-group align-center">
                      <input type="submit" id="optimize-button" value="OPTIMIZAR" class="btn btn-info optimize-button">
                    </div>
                  </div>
                  <div class="col-xs-3 version">
                    <?=isset($version) ? $version : 'v0c0' ?>
                  </div>
                </div>

                
                <?php if(isset($dbg)) echo "<input type=\"hidden\" name=\"dbg\" >\n"; ?>
                <!-- <div class="row">
                  <div class="col-xs-4"></div>
                  <div class="col-xs-4">
                    <div class="form-group align-center">
                      <input type="reset" id="reset-button" value="LIMPIAR CAMPOS" class="btn btn-info optimize-button">
                    </div>
                  </div>
                  <div class="col-xs-4 version"></div>
                </div> -->
              
            </div>
          </div>
        </div>
      </div>
  <!-- Modal -->
  <div id="myModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">¡Subtítulo optimizado!</h4>
        </div>
        <div class="modal-body">
          <div id="efficiency"></div>
          <div id="enhancement"></div>
          <p>
            <label>Nombre del archivo:</label>
            <input type="text" id="finalFileName" class="form-control" />
          </p>
          <!-- <div class="panel-group">
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title">
                  <a class="pull-left" data-toggle="collapse" href="#collapse1">Correcciones de OCR</a>
                  <div class="clearfix"></div>
                </h4>
                
              </div>
              <div id="collapse1" class="panel-collapse collapse">
                <div class="panel-body">Panel Body</div>
                <div class="panel-footer">Panel Footer</div>
              </div>
            </div>
          </div> -->

    <div class="list-group panel panel-no-border">
        <a href="#category-0" class="list-group-item active-toggle collapsed ocr-corrections" data-toggle="collapse" aria-expanded="false">Correcciones de OCR <span class="pull-right"><i class="fa fa-caret-down" aria-hidden="true"></i></span></a>
        <div class="collapse" id="category-0" aria-expanded="false" style="height: 0px;">
            <div id="ocr-table-container">
              
            </div>
            <!-- <li class="sub-item list-group-item">Agua mineral</li>
            <li class="sub-item list-group-item">Gaseosa</li>
            <li class="sub-item list-group-item">Leche</li>
            <li class="sub-item list-group-item">Vodka</li> -->
        </div>
        <!-- <a href="#category-1" class="list-group-item active-toggle collapsed" data-toggle="collapse" data-parent="#MainMenu" aria-expanded="false">Frutas/Verduras&nbsp;<i class="fa fa-fw fa-caret-down"></i></a>
        <div class="collapse" id="category-1" aria-expanded="false" style="height: 0px;">
            <a href="product.php?id=6" class="sub-item list-group-item">Manzana</a>
            <a href="product.php?id=5" class="sub-item list-group-item">Tomate</a>
        </div>
        <a href="#category-2" class="list-group-item active-toggle collapsed" data-toggle="collapse" data-parent="#MainMenu" aria-expanded="false">Alimentos&nbsp;<i class="fa fa-fw fa-caret-down"></i></a>
        <div class="collapse" id="category-2" aria-expanded="false" style="height: 0px;">
            <a href="product.php?id=8" class="sub-item list-group-item">Arroz</a>
            <a href="product.php?id=54" class="sub-item list-group-item">Galletitas</a>
            <a href="product.php?id=55" class="sub-item list-group-item">Galletitas</a>
            <a href="product.php?id=7" class="sub-item list-group-item">Hamburguesas</a>
            <a href="product.php?id=1" class="sub-item list-group-item">Pan Lactal de Salvado</a>
            <a href="product.php?id=48" class="sub-item list-group-item">Pepitos</a>
            <a href="product.php?id=61" class="sub-item list-group-item">Serenito</a>
        </div> -->
    </div>


          <p><pre id="pre-wrap" class="highlight"></pre></p>
        </div>
        <div class="modal-footer">
          <div class="pull-left">
            <button type="button" class="btn btn-default download-btn pull-left" data-dismiss="modal"><i class="fa fa-arrow-circle-down" aria-hidden="true"></i>&nbsp;Descargar</button>
          </div>
          <div>
            <button type="button" class="btn copy-btn" data-clipboard-target="#pre-wrap" data-toggle="tooltip" data-placement="left" title="¡Copiado!"><i class="fa fa-clipboard" aria-hidden="true"></i>&nbsp;Copiar</button>
          </div>
          <div>
            <button type="button" class="btn btn-default close-btn" data-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>

    </div>
  </div>
    </div>
  </form>


  

  <div id="loading-info-overlay">
    <div>
      <div class="loading-animation">
        <div class="meter animate">
          <span style="width: 100%"><span></span></span>
        </div>
      </div>
    </div>
  </div>

  <!-- <script src="js/clipboard.min.js"></script> -->
  <?=add_jscript('clipboard.min')?>
  <!-- <script src='js/nprogress.js'></script> -->
  <?=add_jscript('nprogress')?>
  
  <?=add_jscript('perfect-scrollbar.jquery')?>
  <?=add_jscript('jquery-ui.min')?>

  <?=add_jscript('jquery-1.11.0.min')?>
  <script type="text/javascript">
    var jQueryL = $.noConflict(true);
  </script>
  <?=add_jscript('magicsuggest')?>

  <script>
    $('.container-scrollable').perfectScrollbar();
  
    $( function() {
      var spinner = $( ".spinner" ).spinner();
    });

    var $ripples = $('.ripples');
    $ripples.on('click.Ripples', function(e) {
        var $this = $(this);
        var $offset = $this.parent().offset();
        var $circle = $this.find('.ripple');

        var x = e.pageX - $offset.left;
        var y = e.pageY - $offset.top;
        $circle.css({
            top: y + 'px',
            left: x + 'px'
        });
        $this.addClass('is-active');
    });

    $ripples.on('animationend webkitAnimationEnd mozAnimationEnd oanimationend MSAnimationEnd', function(e) {
        $(this).removeClass('is-active');
    });

    jQueryL(function() {
      jQueryL('#magicsuggest').magicSuggest({
        data: subtitler_array,
        maxSelection: 5,
        name: 'subtitlers'
      });
    });

    $(document).ready(function(){
      $('#magicsuggest').addClass('dropup');

      $('button.fab.mdi-plus').on('click',function(){

        var rowCount = $('.extra-row').length;

        if(rowCount<2){

            // var foundId = false;
            var rowId = 0;
            for(var j=1; j<3 || !rowId; j++) {

            //   console.log('here');
              if($('.extra-row-'+j).length == 0) {
            //     console.log('there');
            //     foundId = true;
                rowId = j;
              }
            }

            var tasks = '';
            for(var i=0; i<task_array.length; i++) {
              tasks += '<li class="dropdown-item">'+task_array[i]+'</li>';
            }


        
            var row = '<div class="row extra-row extra-row-'+rowId+'">\
            <div class="col-xs-4 col-sm-4 col-md-4">\
              <div class="form-group">\
                <div class="input-group" id="task-suggest'+rowId+'">\
                  <input type="text" name="task'+rowId+'" id="task'+rowId+'" class="form-control input-sm typeahead">\
                  <div class="input-group-btn">\
                    <button type="button" class="btn dropdown-toggle more-options" data-toggle="dropdown" tabindex="-1" aria-haspopup="true" aria-expanded="false"><i class="fa fa-caret-down" aria-hidden="true"></i></button>\
                    <ul class="dropdown-menu dropdown-menu-right container-scrollable" data-list="task'+rowId+'">\
                      <div class="content-scrollable">'+tasks+'</div>\
                    </ul>\
                  </div>\
                </div>\
              </div>\
            </div>\
            <div class="col-xs-8 col-sm-8 col-md-8">\
              <div class="row">\
                <div class="col-xs-10 col-sm-10 col-md-10">\
                  <div id="magicsuggest'+rowId+'"></div>\
                </div>\
                <div class="col-xs-2 col-sm-2 col-md-2 remove-padding-left">\
                  <button class="fab primary mdi-minus" type="button">\
                    <div class="ripples black"><span class="ripple"></span></div>\
                  </button>\
                </div>\
              </div>\
            </div>\
            </div>';

            $('.task-row').append(row);

            jQueryL(function() {
              jQueryL('#magicsuggest'+rowId).magicSuggest({
                data: subtitler_array,
                maxSelection: 5,
                name: 'subtitlers'+rowId
              });
            });

            $('#magicsuggest'+rowId).addClass('dropup');

            var substringMatcher = function(strs) {
              return function findMatches(q, cb) {
                // if(q=='') q = '/./';
                // console.log(q);
                var matches, substringRegex;

                // an array that will be populated with substring matches
                matches = [];

                // regex used to determine if a string contains the substring `q`
                substrRegex = new RegExp(q, 'i');

                // iterate through the pool of strings and for any string that
                // contains the substring `q`, add it to the `matches` array
                $.each(strs, function(i, str) {
                  if (substrRegex.test(str)) {
                    matches.push(str);
                  }
                });

                cb(matches);
              };
            };

            $('#task-suggest'+rowId+' .typeahead').typeahead({
              hint: true,
              highlight: true,
              limit: 10
            },
            {
              name: 'task_array'+rowId,
              source: substringMatcher(task_array)
            });
          
        }

      });

      $(document).on('click','button.fab.mdi-minus',function(){
        $(this).closest('.extra-row').remove();
      });
    });


  </script>
  
</body>
</html>