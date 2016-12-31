$(document).ready(function(){
  
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
    tempString = completeString.substr(12);
    // C:\fakepath\
    // The Great Indoors 1x08 - Office Romance.srt
    var xPosition = tempString.search(/\s[0-9]{1,2}x[0-9]{2}\s/i);
    var tvShow = tempString.slice(0,xPosition);
    var lastHalf = tempString.slice(tvShow.length+1);
    var episodeSeasonEndPosition = lastHalf.search(/\s-/i);
    var episodeSeason = lastHalf.slice(0,episodeSeasonEndPosition);
    var season = episodeSeason.slice(0,episodeSeason.indexOf('x'));
    var episode = episodeSeason.slice(episodeSeason.indexOf('x')+1);
    if(episode[0]=='0') episode = episode.slice(1);
    
    tempString = lastHalf.slice(episodeSeasonEndPosition+3);
    var spanishPosition = tempString.search(/\s\(Español/);
    if(spanishPosition>0) var title = tempString.slice(0,tempString.search(/\s\(Español/));
    else var title = tempString.slice(0,tempString.search(/\.srt/));
    
    $('#tv_show').val(tvShow);
    $('#season').val(season);
    $('#episode_number').val(episode);
    $('#episode_title').val(title);
  });

  $('#enhance').submit(function() {
    if(!$('#input-sub-file').val() && !isValidSubUrl($('#sub_url').val())) {
      $.alert({
          animation: 'top',
          title: '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;&nbsp;NADA QUE OPTIMIZAR',
          type: 'red',
          content: 'Ingresar una URL válida o un archivo para&nbsp;optimizar.',
          backgroundDismiss: true
      });
      return false;
    }
    if($('#input-sub-file').val() && isValidSubUrl($('#sub_url').val())) {
      $.alert({
          animation: 'top',
          title: '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;&nbsp;ELEGIR SOLO UNA OPCIÓN',
          type: 'red',
          content: 'Puede elegir optimizar una URL de <em>tusubtitulo.com</em> o un archivo SRT, pero no ambos al mismo tiempo.',
          backgroundDismiss: true
      });
      return false;
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

  $('li.dropdown-item').on('click',function(){
    $('#'+$(this).parent().attr('data-list')).val($(this).html());
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
  $.post("subtitleData.php", { info_url : info_url, sub_url : sub_url }).done(function(data){
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
  var pattern = new RegExp('^^https://www.tusubtitulo.com/episodes/[0-9]+/[^/]+(/)?$','i');
  return pattern.test(str);
}

function isValidSubUrl(str) {
  var pattern = new RegExp('^https://www.tusubtitulo.com/[^/]+/[0-9]+/[0-9]+(/[0-9]+)?$','i');
  return pattern.test(str);
}