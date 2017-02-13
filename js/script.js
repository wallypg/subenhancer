$(document).ready(function(){
  // $('#myModal').modal('show');

  // $(".nano").nanoScroller();
  // $(".scroll").niceScroll({
  //               cursorcolor: "#4285b0",
  //               cursoropacitymin: 0.3,
  //               background: "#cedbec",
  //               cursorborder: "0",
  //               autohidemode: false,
  //               cursorminheight: 30
  //   });
    
  //   //Activa el nicescroll cuando esta oculto
    
  //   $(".scroll").getNiceScroll().resize();
  //   $("html").mouseover(function() {
  //       $(".scroll").getNiceScroll().resize();
  //   });


  $('.options-submenu').on('click',function(){
    if($('.options-content').hasClass('collapsed')) {
      $('.options-content').slideDown().removeClass('collapsed');
      $(this).find('.fa').removeClass('fa-chevron-right').addClass('fa-chevron-down');
    } else {
      $('.options-content').slideUp().addClass('collapsed');
      $(this).find('.fa').removeClass('fa-chevron-down').addClass('fa-chevron-right');
    }
  });





  // $(document).ready(function(){
  //   $('.combobox').combobox();
  // });
  
  var clipboard = new Clipboard('.copy-btn');
  clipboard.on('success', function(event) {
    $('.copy-btn').tooltip({trigger: 'click'}).tooltip('show');
  });

  $('.copy-btn').mouseleave(function(){
    $(this).tooltip('destroy');
  });

  $('#info_url').on('input',function(e){
    var info_url = $(this).val();
    var sub_url = $('#sub_url').val();
    if(isValidDataUrl(info_url) || isValidDataUrl2(info_url)) {
      showLoading();
      ajaxRequestInfo(info_url,sub_url);
    }
  });

  $('#sub_url').on('input',function(e){
    var sub_url = $(this).val();
    var info_url = $('#info_url').val();
    if( (isValidDataUrl(info_url) || isValidDataUrl2(info_url)) && isValidSubUrl(sub_url)) {
      showLoading();
      ajaxRequestInfo(info_url,sub_url);
    }
  });

  $('#input-sub-file').on('change',function(e){
    var completeString = $(this).val();
    
    // C:\fakepath\
    if(completeString.substr(0,12) == 'C:\\fakepath\\') tempString = completeString.substr(12);
    else tempString = completeString;

    // Patterns:
    // 1) Blackish - 02x24 - Good-ish Times-Español
    //    Ransom 1x03 - The Box
    // 2) 2.Broke.Girls.S06E01.And.the.Two.Openings.HDTV.x264-LOL
    //    2.Broke.Girls.S06E01E02.And.the.Two.Openings.HDTV.x264-LOL

    // Poner como default si no viene nada HDTV y x264

    var pattern = 0;
    var xPosition = tempString.search(/\s[0-9]{1,2}x[0-9]{2}\s/i);
    if(xPosition > -1) pattern = 1;
    else {
      var xPosition = tempString.search(/\.S\d{2}[E\d]{3,6}\./);
      if(xPosition > -1) pattern = 2;
    }

    var tvShow = '';
    var season = '';
    var episode = '';
    var title = '';

    if(pattern == 1) {
      tvShow = tempString.slice(0,xPosition);
      if(tvShow.substr(tvShow.length - 1) == '-') tvShow = tvShow.slice(0,-1);
      var lastHalf = tempString.slice(tvShow.length+1);
      var episodeSeasonEndPosition = lastHalf.search(/\s-/i);
      var episodeSeason = lastHalf.slice(0,episodeSeasonEndPosition);
      season = parseInt(episodeSeason.slice(0,episodeSeason.indexOf('x')));
      episode = parseInt(episodeSeason.slice(episodeSeason.indexOf('x')+1));
      // if(episode[0]=='0') episode = episode.slice(1);
      
      tempString = lastHalf.slice(episodeSeasonEndPosition+3);
      var spanishPosition = tempString.search(/\s\(Español/);
      if(spanishPosition>0) title = tempString.slice(0,tempString.search(/\s\(Español/));
      else var title = tempString.slice(0,tempString.search(/\.srt/));

      if(title.substr(title.length - 8) == '-Español') title = title.slice(0,-8);

    } else if(pattern == 2) {
      tvShow = (tempString.slice(0,xPosition)).replace(/\./ig,' ');
      var lastHalf = tempString.slice(tvShow.length+1);
      // console.log(lastHalf);
      var nextDotPosition = lastHalf.indexOf('.');
      if(nextDotPosition == 6) {
        // SxxExx
        season = parseInt(lastHalf.slice(1,3));
        episode = parseInt(lastHalf.slice(4,6));
        lastHalf = lastHalf.slice(7);
      }
      var videoInfoPosition = lastHalf.search(/HDTV|DVDRip|WEB-DL|720p|1080p|PROPER|INTERNAL|LIMITED|REPACK/);
      var videoInfo = lastHalf.slice(videoInfoPosition,-4);
      var ripGroup = videoInfo.slice(videoInfo.lastIndexOf('-')+1);
      
      var moreInfo = videoInfo.slice(0,videoInfo.lastIndexOf('-'));

      // HDTV|DVDRip|WEB-DL|WEBRip
      if(moreInfo.search(/HDTV/i) >= 0) $('#format').val('HDTV');
      if(moreInfo.search(/DVDRip/i) >= 0) $('#format').val('DVDRip');
      if(moreInfo.search(/WEB-DL/i) >= 0) $('#format').val('WEB-DL');
      if(moreInfo.search(/WEBRip/i) >= 0) $('#format').val('WEBRip');

      // 720p|1080p
      if(moreInfo.search(/720p/i) >= 0) $('#quality').val('720p');
      if(moreInfo.search(/1080p/i) >= 0) $('#quality').val('1080p');

      // PROPER|INTERNAL|LIMITED|REPACK
      if(moreInfo.search(/INTERNAL/i) >= 0) $('#other').val('INTERNAL');
      if(moreInfo.search(/LIMITED/i) >= 0) $('#other').val('LIMITED');
      if(moreInfo.search(/PROPER/i) >= 0) $('#other').val('PROPER');
      if(moreInfo.search(/REPACK/i) >= 0) $('#other').val('REPACK');
      
      // x264|x265|XviD
      if(moreInfo.search(/x264/i) >= 0) $('#codec').val('x264');
      if(moreInfo.search(/x265/i) >= 0) $('#codec').val('x265');
      if(moreInfo.search(/XviD/i) >= 0) $('#codec').val('XviD');

      title = lastHalf.slice(0,videoInfoPosition).replace(/\./ig,' ');
    }

    
    $('#tv_show').val(tvShow);
    $('#rip_group').val(ripGroup);
    $('#season').val(season);
    $('#episode_number').val(episode);
    $('#episode_title').val(title);
    if(title.search(/^S\d{2}E/i) == 0) $('.title-info').addClass('flashing');
    else $('.title-info').removeClass('flashing');
  });

  $('#enhance').submit(function(event) {

    if(!$('#input-sub-file').val() && $('#sub_url').val() == '' ) {
      $.alert({
          animation: 'top',
          title: '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;&nbsp;NADA QUE OPTIMIZAR',
          type: 'red',
          content: 'Ingresar una URL válida o un archivo para&nbsp;optimizar.',
          backgroundDismiss: true
      });
      return false;
    }

    if($('#input-sub-file').val() && $('#sub_url').val() != '') {
      //  && isValidSubUrl($('#sub_url').val())
      $.alert({
          animation: 'top',
          title: '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;&nbsp;ELEGIR SOLO UNA OPCIÓN',
          type: 'red',
          content: 'Puede elegir optimizar una URL de <em>tusubtitulo.com</em> o un archivo SRT, pero no ambos al mismo tiempo.',
          backgroundDismiss: true
      });
      return false;
    }

    if(!dbg) {
      event.preventDefault();

      var  srtContent;
      var file = document.getElementById("input-sub-file").files[0];
      if (file) {
          NProgress.start();
          var reader = new FileReader();
          reader.readAsText(file, "windows-1252");
          reader.onload = function (evt) {
              // document.getElementById("fileContents").innerHTML = evt.target.result;
              $.ajax({
                method: "POST",
                url: baseUrl+"subenhancer/enhance",
                data: {
                  sub_url: $('[name="sub_url"]').val(),
                  ocr: $('[name="ocr"]').is(':checked'),
                  tv_show: $('[name="tv_show"]').val(),
                  season: $('[name="season"]').val(),
                  episode_number: $('[name="episode_number"]').val(),
                  episode_title: $('[name="episode_title"]').val(),
                  other: $('[name="other"]').val(),
                  quality: $('[name="quality"]').val(),
                  format: $('[name="format"]').val(),
                  codec: $('[name="codec"]').val(),
                  rip_group: $('[name="rip_group"]').val(),
                  editor: $('[name="editor"]').val(),
                  translation: $('[name="translation"]').val(),
                  srtContent: evt.target.result
                }
              }).done(function(data){
                var data = $.parseJSON(data);
                console.log(data);
                NProgress.done();
                if(data.hasOwnProperty('alreadyEnhanced')) alreadyEnhanced();
                if(data.hasOwnProperty('error')) console.log(data);
                else {
                  $('#efficiency').html(data.efficiencyMessage);
                  $('#enhancement').html(data.enhancementMessage);
                  $('#pre-wrap').html(data.threadMessage);
                  $('#ocr-table-container').html(data.ocrCorrections);
                  $('#myModal').modal('show');
                  $('#finalFileName').val(data.filename);
                  $('#finalFileName').attr('data-temp-name', data.tempFilename);
                  // window.location = 'download.php?file='+data.tempFilename+'&name='+data.filename;
                }
              });
          }
          reader.onerror = function (evt) {
              console.log("error reading file");
              // document.getElementById("fileContents").innerHTML = "error reading file";
          }
      } else {
        NProgress.start();
        $.ajax({
          method: "POST",
          url: baseUrl+"subenhancer/enhance",
          data: {
            sub_url: $('[name="sub_url"]').val(),
            ocr: $('[name="ocr"]').is(':checked'),
            tv_show: $('[name="tv_show"]').val(),
            season: $('[name="season"]').val(),
            episode_number: $('[name="episode_number"]').val(),
            episode_title: $('[name="episode_title"]').val(),
            other: $('[name="other"]').val(),
            quality: $('[name="quality"]').val(),
            format: $('[name="format"]').val(),
            codec: $('[name="codec"]').val(),
            rip_group: $('[name="rip_group"]').val(),
            editor: $('[name="editor"]').val(),
            translation: $('[name="translation"]').val()
          }
        }).done(function(data){
          var data = $.parseJSON(data);
          console.log(data);
          NProgress.done();
          if(data.hasOwnProperty('alreadyEnhanced')) alreadyEnhanced();
          if(data.hasOwnProperty('error')) console.log(data);
          else {
            $('#efficiency').html(data.efficiencyMessage);
            $('#enhancement').html(data.enhancementMessage);
            $('#pre-wrap').html(data.threadMessage);
            $('#ocr-table-container').html(data.ocrCorrections);
            $('#myModal').modal('show');
            $('#finalFileName').val(data.filename);
            $('#finalFileName').attr('data-temp-name', data.tempFilename);
            // window.location = 'download.php?file='+data.tempFilename+'&name='+data.filename;
          }
        });
      }

      $('.download-btn').on('click',function(){
        window.location = baseUrl+'subenhancer/download?file='+$('#finalFileName').attr('data-temp-name')+'&name='+$('#finalFileName').val();
      });

    }

  });

  $('i.fa-info-circle.sub-url').on('click',function(){
    $.alert({
          animation: 'top',
          title: '¿Qué link elijo?',
          content: '<img id="info-image" src="images/info.jpg" />',
          backgroundDismiss: true
      });
  });

  $('i.fa-info-circle.info-url').on('click',function(){
    $.alert({
          animation: 'top',
          title: '¿Para qué sirve?',
          content: 'Sólo se usa para completar de forma automática algunos de los campos.',
          backgroundDismiss: true
      });
  });

  $('i.fa-info-circle.title-info').on('click',function(){
    $.alert({
          animation: 'top',
          title: 'Episodio múltiple',
          content: 'Para nombres de episodios que no sigan el formato estándar, como por ejemplo los episodios múltiples del tipo "S06E22E23", ingresar el formato de título deseado en este campo y dejar vacíos los campos de temporada y&nbsp;episodio.',
          backgroundDismiss: true
      });
  });

  $('li.dropdown-item').on('click',function(){
    console.log($(this).parent().parent().attr('data-list'));
    $('#'+$(this).parent().parent().attr('data-list')).val($(this).html());
  });


  document.body.addEventListener('dblclick', function(e){
    var target = e.target || e.srcElement;        
    if (target.className.indexOf("highlight") !== -1 || target.parentNode.className.indexOf("highlight") !== -1){
        var range, selection;

        if (document.body.createTextRange) {
            range = document.body.createTextRange();
            range.moveToElementText(target);
            range.select();
        } else if (window.getSelection) {
            selection = window.getSelection();
            range = document.createRange();
            range.selectNodeContents(target);
            selection.removeAllRanges();
            selection.addRange(range);
        }
         e.stopPropagation();
    }
  });

});

function showLoading() {
  $('#loading-info-overlay').fadeIn();
  $(".meter > span").each(function() {
    $(this)
      .data("origWidth", $(this).width())
      .width(10)
      .animate({
        width: $(this).data("origWidth")
      }, 4200);
  });
};

function ajaxRequestInfo(info_url,sub_url){
  $.post(baseUrl+"subenhancer/subtitleData", { info_url : info_url, sub_url : sub_url }).done(function(data){
    console.log(data);
      try {
        JSON.parse(data);
      } catch (event) {
        $('#loading-info-overlay').fadeOut();
        $.alert({
            animation: 'top',
            title: '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;&nbsp;ERROR DE URL',
            type: 'red',
            content: 'Hubo un problema con el link.',
            backgroundDismiss: true
        });
        return false;
      }
      $('#loading-info-overlay').fadeOut();
      var data = $.parseJSON(data);
      $('#tv_show').val(data.tv_show);
      $('#season').val(data.season);
      $('#episode_number').val(data.episode_number);
      $('#episode_title').val(data.episode_title);
      $('#rip_group').val(data.group);
  });
}

function isValidDataUrl(str) {
  var pattern = new RegExp('^https://www.tusubtitulo.com/serie/[^/]+/[0-9]+/[0-9]+/[0-9]+/$','i');
  return pattern.test(str);
}

function isValidDataUrl2(str) {
  var pattern = new RegExp('^https://www.tusubtitulo.com/episodes/[0-9]+/[^/]+(/)?$','i');
  return pattern.test(str);
}

function isValidSubUrl(str) {
  var pattern = new RegExp('^https://www.tusubtitulo.com/[^/]+/[0-9]+/[0-9]+(/[0-9]+)?$','i');
  return pattern.test(str);
}

function alreadyEnhanced(){
  $.alert({
      animation: 'top',
      title: '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;&nbsp;SUBTÍTULO YA OPTIMIZADO',
      type: 'red',
      content: 'Optimizar un subtítulo ya optimizado daría como resultado tiempos poco confiables.',
      backgroundDismiss: true
  });
}