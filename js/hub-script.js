$(document).ready(function(){

	$('form').validate({
    onKeyup : true,
    eachValidField : function() {
      $(this).closest('div').removeClass('error');
      $(this).parent().next('div').find('span').hide();
    },
    eachInvalidField : function() {
      $(this).closest('div').addClass('error');
      $(this).parent().next('div').find('span').show();
    },
    valid : function(event) {
      event.preventDefault();
      $.ajax({
        method: 'POST',
        url: baseUrl+'auth',
        data: { username : $('#login__username').val(), password : $('#login__password').val() },
        success: function(data) {
          console.log(data);
          if(data == 'false') {
            $('.unauth').slideDown('fast');
          } else if (data == 'true') {
          	$('.unauth').slideUp('fast');
            $('form').unbind('submit').submit();
          } else {
          	console.log(data);
          }
        }
      });

    }
    ,
    description : {
      errorLoginUser : {
        required : '<span class="popup-text">Campo obligatorio</span>'
      },
      errorLoginPass : {
        required : '<span class="popup-text">Campo obligatorio</span>'
      }
    }
  });

});