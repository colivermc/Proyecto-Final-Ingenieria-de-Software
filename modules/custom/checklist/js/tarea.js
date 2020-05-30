
(function ($) {

  $(document).ready(function () {
    if ($('#campo_slack_mensaje').length > 0){
      $('#campo_img_mensaje_slack').show();
    }else{
      $('#campo_img_mensaje_slack').hide();
    }
  });

  $(document).ajaxComplete(function() {
    if ($('#campo_slack_mensaje').length > 0){
      $('#campo_img_mensaje_slack').show();
    }else{
      $('#campo_img_mensaje_slack').hide();
    }
  });


})(jQuery);
