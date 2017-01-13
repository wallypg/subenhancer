<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>SubEnhancer Data Editor</title>
  
  <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <!-- <link rel="stylesheet" href="css/bootstrap.min.css"> -->
  <?=add_style('bootstrap.min')?>
  <!-- <link rel="stylesheet" href="css/jquery-confirm.css"> -->
  <?=add_style('jquery-confirm')?>
  <!-- <link rel="stylesheet" href="css/bootstrap-theme.min.css"> -->
  <?=add_style('bootstrap-theme.min')?>
  <!-- <link type="text/css" rel="stylesheet" href="css/materialize.min.css"  media="screen,projection"/> -->
  <?=add_style('materialize.min')?>

  <!-- <script src="js/jquery-3.1.1.min.js"></script> -->
  <?=add_jscript('jquery-3.1.1.min')?>
  <!-- <script src="js/jquery-confirm.js"></script> -->
  <?=add_jscript('jquery-confirm')?>
  <!-- <script src="js/bootstrap.min.js"></script> -->
  <?=add_jscript('bootstrap.min')?>

  <!-- <script src="js/medium-editor.min.js"></script> -->
  <?=add_jscript('medium-editor.min')?>
  <!-- <script type="text/javascript" src="js/materialize.min.js"></script> -->
  <?=add_jscript('materialize.min')?>
  <!-- <link rel="stylesheet" href="css/medium-editor.min.css" type="text/css" media="screen" charset="utf-8"> -->
  <?=add_style('medium-editor.min')?>
  
  <!-- <script src="js/script.js"></script> -->
  
  <!-- <link href="https://fonts.googleapis.com/css?family=Bitter:400,400i,700|Catamaran:300,400,500,600,700|Open+Sans:300,300i,400,400i,600,600i,700,700i|Rokkitt:400,700" rel="stylesheet"> -->
  <!-- <link rel="stylesheet" href="css/font-awesome.min.css"> -->
  <?=add_style('font-awesome.min')?>
  <!-- <link rel="stylesheet" href="css/style.css"> -->

  <style>
    pre {
      border: 0!important;
      background-color: #fff;
      /*font-family: auto!important;*/
      font-size:13px!important;
    }
    *, *:focus {
      outline:none!important;
    }
    kbd{
      border:1px solid gray;
      font-size:1.2em;
      box-shadow:1px 0 1px 0 #eee, 0 2px 0 2px #ccc, 0 2px 0 3px #444;
      -webkit-border-radius:3px;
      -moz-border-radius:3px;
      border-radius:3px;
      margin:2px 3px;
      padding:1px 5px;
    }
    .jconfirm .jconfirm-box div.jconfirm-content-pane {
      overflow: visible!important;
    }
    .jconfirm-content {
      font-size: 17px;
      line-height: 34px!important;
    }
    p {
      margin: 0px!important;
    }
    .jconfirm-title-c {
      /*color: #5dade3;*/
      /*text-align: center;*/
    }
    .fa-info-circle {
      color: #5dade3;
    }
    .fa-floppy-o {
      color: #2ecc71;
    }
    .fa-exclamation-circle {
      color: #e74c3c;
    }
    .red.darken-4 {
      position:fixed;
      top: 8px;
      right: 8px;
    }
    .green.accent-4 {
      position:fixed;
      top: 78px;
      right: 8px;
    }
    @media screen and (min-width:992px) {
      .jc-bs3-row .col-md-offset-4 {
        margin-left: 25%!important;
      }
      .jconfirm-box {
        min-width: 380px;
      }
    }
    @media screen and (min-width:768px) and (max-width:991px) {
      .jc-bs3-row .col-md-offset-4 {
        margin-left: 22%!important;
      }
      .jconfirm-box {
        min-width: 380px;
      } 
    }
  </style>
</head>
<body>
  
  <?php if (isset($json)) echo '<pre id="sub-data" class="editable">'.$json.'</pre>'; ?>

  <a href="<?=base_url()?>subenhancer" class="btn-floating btn-large waves-effect waves-light red darken-4 tooltipped" data-position="left" data-delay="50" data-tooltip="Volver al SubEnhancer"><i class="material-icons">exit_to_app</i></a>
  
  <a href="javascript:void(0)" id="floating-save" class="btn-floating btn-large waves-effect waves-light green accent-4 tooltipped" data-position="left" data-delay="50" data-tooltip="Guardar"><i class="fa fa-floppy-o" aria-hidden="true"></i></a>
  <script>
    var editor = new MediumEditor('.editable',{
      spellcheck: false,
      toolbar: false
    });


    $(document).ready(function(){
      $.alert({
          title: '<i class="fa fa-info-circle" aria-hidden="true"></i>&nbsp;&nbsp;<b>¡LEER!</b>',
          type: 'blue',
          buttons: {
              ok: {
                  keys: [
                      'enter'
                  ]
              }
          },
          backgroundDismiss: true,
          content: '<i class="fa fa-caret-right" aria-hidden="true"></i> ¡No editar las categorías!<br /><i class="fa fa-caret-right" aria-hidden="true"></i> Respetar la sintaxis.<br /><i class="fa fa-caret-right" aria-hidden="true"></i> Presionar <kbd>Ctrl</kbd> + <kbd>s</kbd> para guardar.',
          animation: 'zoom',
          closeAnimation: 'zoom',
      });

      $('#floating-save').on('click',function(){
        ajaxRequest($('pre#sub-data').html());
      });



    });

    $(window).bind('keydown', function(event) {
        if (event.ctrlKey || event.metaKey) {
            switch (String.fromCharCode(event.which).toLowerCase()) {
            case 's':
                event.preventDefault();
                var updatedJson = $('pre#sub-data').html();
                ajaxRequest(updatedJson);
                break;
            }
        }
    });

    function ajaxRequest(updatedJson){
      $.post("<?=base_url()?>subenhancer/save", { updatedJson : updatedJson }).done(function(data){
          
          try {
            JSON.parse(data);
          } catch (event) {
            $.alert({
                title: '<i class="fa fa-exclamation-circle" aria-hidden="true"></i>&nbsp;&nbsp;Error al guardar',
                type: 'red',
                buttons: {
                    ok: {
                        keys: [
                            'enter'
                        ]
                    }
                },
                content: 'Revisa la sintaxis: <pre>[\n&nbsp;&nbsp;"Línea intermedia termina en coma",\n&nbsp;&nbsp;"Última línea no"\n]',
                animation: 'zoom',
                closeAnimation: 'zoom',
            });
            return false;
          }
          $.alert({
              title: '<i class="fa fa-floppy-o" aria-hidden="true"></i>&nbsp;&nbsp;<b>Guardado</b>',
              type: 'green',
              buttons: {
                  ok: {
                      keys: [
                          'enter'
                      ]
                  },
                  exit: {
                    text: 'Volver al SubEnhancer',
                    btnClass: 'btn-success',
                    action: function () {
                        window.location.href = "<?=base_url()?>subenhancer";
                    }
                  }
              },
              backgroundDismiss: true,
              content: '¡Datos actualizados con éxito!',
              animation: 'zoom',
              closeAnimation: 'zoom',
          });
      });
    }

    function stripHTML(dirtyString) {
      var container = document.createElement('div');
      var text = document.createTextNode(dirtyString);
      container.appendChild(text);
      return container.innerHTML;
    }
  </script>
</body>