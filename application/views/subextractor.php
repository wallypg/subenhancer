<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>SubExtractor</title>
    
    <link href="https://fonts.googleapis.com/css?family=Bitter:400,400i,700|Catamaran:300,400,500,600,700|Open+Sans:300,300i,400,400i,600,600i,700,700i|Rokkitt:400,700" rel="stylesheet">

    <?=add_style('bootstrap.min')?>
    <?=add_style('jquery-confirm')?>

    <?=add_jscript('jquery-3.1.1.min')?>
    <?=add_jscript('jquery-confirm')?>
    <?=add_jscript('jquery-validate.min')?>
    <?=add_jscript('bootstrap.min')?>
    <?=add_style('font-awesome.min')?>

    <?=add_style('materialize.min')?>
    <?=add_jscript('materialize.min')?>

    <style>
      .col-centered{
          float: none;
          margin: 0 auto;
      }
      #submit-div{
        width: 100%;
        text-align: center;
        padding-top: 6px;
        display: none;
      }
      .vertical-center {
        min-height: 100%;
        min-height: 100vh;

        display: flex;
        align-items: center;
      }
      .floating-submit {
        outline:0;
      }
    </style>

    <script>var baseUrl = '<?=base_url()?>';</script>
    

    <?=add_jscript('dropzone')?>
    <?=add_style('dropzone')?>

    <script>

        

        Dropzone.options.logDropzone = {

          // Prevents Dropzone from uploading dropped files immediately
          autoProcessQueue: false,
          addRemoveLinks: true,
          maxFiles: 1,
          dictDefaultMessage: '<h3>SubExtractor</h3>Suba aquí su archivo <em>.log</em><br><em>(Click o drag n\' drop)</em>',
          dictRemoveFile: 'Remover archivo',
          dictMaxFilesExceeded: 'Puede subir sólo un archivo por vez',

          init: function() {
            var submitButton = document.querySelector("#floating-submit")
                logdropzone = this; // closure

            submitButton.addEventListener("click", function() {
              logdropzone.processQueue(); // Tell Dropzone to process all queued files.
            });

            // You might want to show the submit button only when 
            // files are dropped here:
            this.on("addedfile", function() {
              // Show submit button here and/or inform user to click it.
              $('#submit-div').show();
            });


            this.on("removedfile", function(file) {
              if($('.dz-preview').length == 0) $('#submit-div').hide();
            });

          },
          success: function(file, response){
              window.location = baseUrl+'dropzone/download?file=' + response;

              $.alert({
                animation: 'top',
                title: '<i class="fa fa-download" aria-hidden="true"></i>&nbsp;&nbsp;¡SUBTÍTULO DESCARGADO!',
                type: 'green',
                content: 'Archivo <em>.log</em> convertido a <em>.srt</em> exitosamente.',
                backgroundDismiss: true
              });
          }
        }
        
        // Dropzone.autoDiscover = false;

        // $(document).ready(function(){
        //   $('#floating-submit').on('click',function(){
        //     $('#logdropzone').submit();
        //   });
        // });
        // });
    </script>
  </head>
  <body>
    <div class="vertical-center">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-12 col-centered">
           <form action="<?php echo site_url('/dropzone/upload'); ?>" class="dropzone" id="log-dropzone"></form>
           <div id="submit-div">
            <a href="javascript:void(0)" id="floating-submit" class="btn-floating btn-large waves-effect waves-light green accent-4 tooltipped" data-position="bottom" data-delay="50" data-tooltip="Convertir"><i class="fa fa-check" aria-hidden="true"></i></a>             
           </div>

          </div>
        </div>      
      </div>
    </div>

  </body>
</html>