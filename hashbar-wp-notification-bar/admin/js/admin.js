(function($) {
  "use strict";

  $( document ).ready(function() {

    $('#_wphash_themes_header_type,#_wphash_notification_transparent_selector, .pro, [name="_wphash_notification_schedule"],#_wphash_notification_schedule_datetime_date,#_wphash_notification_schedule_datetime_time,#_wphash_notification_where_to_show3, #_wphash_notification_where_to_show4').attr("disabled", true);

    // Pro Pop Up Notice
    $( 'span.pro,.cmb-th label span' ).click(function() {
     	$( "#ht_dialog" ).dialog({
     		modal: true,
     		minWidth: 500,
     		buttons: {
                Ok: function() {
                  $( this ).dialog( "close" );
                }
            }
     	});
    });

    var notification_position   = $("input[name='_wphash_notification_position']:checked").val();
    var $previously_set_value   = true;

    set_notification_bar_width(notification_position);

    $('.cmb2-id--wphash-notification-position li input').on('click', function() {
       var $hasba_option_position_value = this.getAttribute('value');
       set_notification_bar_width($hasba_option_position_value);
    });

    $('.cmb2-id--wphash-show-hide-scroll li input').on('click', function() {
      var $hasba_option_clicked_value = this.getAttribute('value');
       disable_on_select_scroll_option($hasba_option_clicked_value);
    });

    function disable_on_select_scroll_option(notification_scrl_value){
      if('show_hide_scroll_enable' == notification_scrl_value){
        $('.cmb2-id--wphash-notification-display').hide();
        $("input[name='_wphash_notification_display']").val('ht-n-close');
      }else{
        $('.cmb2-id--wphash-notification-display').show();
        $("input[name='_wphash_notification_display']").val('ht-n-open');
      }
    }

    function set_notification_bar_width($hasba_option_position_value){
      var $notification_width = $("input[name='_wphash_notification_width']").val();
      if('ht-n_toppromo' == $hasba_option_position_value || 'ht-n_bottompromo' == $hasba_option_position_value){
        if($notification_width == ''){
          $("input[name='_wphash_notification_width']").val('250px');
          $previously_set_value = false;
        }
      }else{
        if(!$previously_set_value){
          $("input[name='_wphash_notification_width']").val('');
        }
      }
    }  
      
  });

})(jQuery);