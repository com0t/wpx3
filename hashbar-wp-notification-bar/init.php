<?php
/**
 * Plugin Name: HashBar - WordPress Notification Bar
 * Plugin URI:  http://demo.wphash.com/hashbar/
 * Description: Notification Bar plugin for WordPress
 * Version:     1.2.3
 * Author:      HasThemes
 * Author URI:  https://hasthemes.com
 * Text Domain: hashbar
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/


// define path
define( 'HASHBAR_WPNB_ROOT', __FILE__ );
define( 'HASHBAR_WPNB_URI', plugins_url('',HASHBAR_WPNB_ROOT) );
define( 'HASHBAR_WPNB_DIR', dirname(HASHBAR_WPNB_ROOT ) );

$wordpress_version = (int)get_bloginfo( 'version' );
$hashbar_gutenberg_enable = $wordpress_version < 5 ? false : true;

// include all files
if ( ! function_exists('is_plugin_active') ){ include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); }
include_once( HASHBAR_WPNB_DIR. '/inc/custom-posts.php');
include_once( HASHBAR_WPNB_DIR. '/admin/cmb2/init.php');

if(is_admin()){
    include_once( HASHBAR_WPNB_DIR. '/inc/recomendation/Class_Recommended_Plugins.php');
    include_once( HASHBAR_WPNB_DIR. '/inc/recomendation/hashbar-recomendation.php');
}

if(!is_plugin_active( 'hashbar-pro/init.php' )){
    include_once( HASHBAR_WPNB_DIR. '/inc/shortcode.php');
    if( true === $hashbar_gutenberg_enable ){
        include_once( HASHBAR_WPNB_DIR. '/inc/block-init.php');
    }
	include_once( HASHBAR_WPNB_DIR. '/admin/plugin-options.php');
	add_action( 'cmb2_admin_init', 'hashbar_wpnb_add_metabox' );
	add_action( 'admin_enqueue_scripts','hashbar_wpnb_admin_enqueue_scripts');
}

function hashbar_wpnb_add_metabox(){
    include_once( HASHBAR_WPNB_DIR. '/inc/metabox-multiple-input.php');
    include_once( HASHBAR_WPNB_DIR. '/inc/metabox.php');
}

// deactivate the pro version 
register_activation_hook( HASHBAR_WPNB_ROOT, 'hashbar_deactivate_pro_version' );
function hashbar_deactivate_pro_version(){
    if( is_plugin_active('hashbar-pro/init.php') ){
        deactivate_plugins('hashbar-pro/init.php');
    }
}

//add settings in plugin action
add_filter('plugin_action_links_'.plugin_basename(__FILE__),function($links){

    $link = sprintf("<a href='%s'>%s</a>",esc_url(admin_url('edit.php?post_type=wphash_ntf_bar')),__('Settings','hashbar'));

    array_unshift($links,$link);

    return $links;

});

// define text domain path
function hashbar_wpnb_textdomain() {
    load_plugin_textdomain( 'hashbar', false, basename(HASHBAR_WPNB_URI) . '/languages/' );
}
add_action( 'init', 'hashbar_wpnb_textdomain' );

// enqueue scripts
add_action( 'wp_enqueue_scripts','hashbar_wpnb_enqueue_scripts');
function  hashbar_wpnb_enqueue_scripts(){
    // enqueue styles
    wp_enqueue_style( 'hashbar-notification-bar', HASHBAR_WPNB_URI.'/css/notification-bar.css');

    // enqueue js
     wp_enqueue_script( 'hashbar-main-js', HASHBAR_WPNB_URI.'/js/main.js', array('jquery'), '', false);
}

// admin enqueue scripts
function  hashbar_wpnb_admin_enqueue_scripts(){
    // enqueue styles
    wp_enqueue_style( 'hashbar-admin', HASHBAR_WPNB_URI.'/admin/css/admin.css');
    wp_enqueue_style( 'wp-jquery-ui-dialog');

    // enqueue js
    wp_enqueue_script( 'jquery-ui-dialog');
    wp_enqueue_script( 'hashbar-admin', HASHBAR_WPNB_URI.'/admin/js/admin.js', array('jquery', 'jquery-ui-dialog'), '', false);

    wp_enqueue_script( 'hashbar-metabox-condition', HASHBAR_WPNB_URI .'/admin/js/metabox-conditionals.js', array( 'jquery', 'cmb2-scripts' ), '1.0.0', true );
}

add_action('admin_footer', 'hashbar_wpnb_upgrade_popup');
function hashbar_wpnb_upgrade_popup(){
	?>
	<div id="ht_dialog" title="<?php echo esc_attr__( 'Go Premium!', 'hashbar' ); ?>" class="ht_dialog" style="display: none;">
		<div class="dashicons-before dashicons-warning"></div>
		<h3><?php esc_html_e( 'Purchase our', 'hashbar' ); ?> <a target="_blank" href="https://hasthemes.com/0lx0"><?php esc_html_e( 'Premium', 'hashbar' ); ?></a> <?php esc_html_e( 'version to unlock this feature!', 'hashbar' ); ?></h3>
	</div>
	<?php
}

add_action( 'wp_footer', 'hashbar_wpnb_load_notification_to_footer' );
function hashbar_wpnb_load_notification_to_footer(){
    $args = array('post_type' => 'wphash_ntf_bar');

    $ntf_query = new WP_Query($args);

    while($ntf_query->have_posts()){
        $ntf_query->the_post();

        $post_id = get_the_id();

        $where_to_show = get_post_meta( $post_id , '_wphash_notification_where_to_show', true );

        if($where_to_show  == 'custom'){
            $where_to_show_custom =  get_post_meta( $post_id , '_wphash_notification_where_to_show_custom', true );

            if(!empty($where_to_show_custom)){
                foreach( $where_to_show_custom as $item){
                    if(is_front_page() && $item == 'home'){
                       hashbar_wpnb_output($post_id);
                    }

                    if(is_single() && $item == 'posts'){
                        hashbar_wpnb_output($post_id);
                    }

                    if(is_page() && $item == 'page' ){
                       hashbar_wpnb_output($post_id);
                    }
                }
            }

        } elseif ($where_to_show  == 'everywhere' ){
        	
            hashbar_wpnb_output($post_id);

        } elseif( $where_to_show == 'url_param' ){
			$page_url_param = get_post_meta( $post_id, '_wphash_url_param', true );
			$url_param = isset($_GET['param'])  && $_GET['param'] ? $_GET['param'] : '';

			if($page_url_param == $url_param){
				hashbar_wpnb_output($post_id);
			}
        }
    }
    wp_reset_query(); wp_reset_postdata();
}

//notification bar output
function hashbar_wpnb_output($post_id){

    if(is_admin()){
        return;
    }

    $positon = get_post_meta( $post_id , '_wphash_notification_position', true );
    $positon = !empty($positon) ? $positon : 'ht-n-top';
    $where_to_show = get_post_meta( $post_id , '_wphash_show_hide_scroll', true );

    // width
    $width = get_post_meta( $post_id , '_wphash_notification_width', true );

    $on_desktop = get_post_meta( $post_id, '_wphash_notification_on_desktop', true );
    $on_mobile = get_post_meta( $post_id, '_wphash_notification_on_mobile', true );
    $display = get_post_meta( $post_id , '_wphash_notification_display', true );
    $display = !empty($display) ? $display : 'ht-n-open';

    $content_width = get_post_meta( $post_id, '_wphash_notification_content_width', true );

    $content_color = get_post_meta( $post_id, '_wphash_notification_content_text_color', true );
    $content_bg_color = get_post_meta( $post_id, '_wphash_notification_content_bg_color', true );
    $content_bg_image = get_post_meta( $post_id, '_wphash_notification_content_bg_image', true );
    $content_bg_opacity = get_post_meta( $post_id, '_wphash_notification_content_bg_opcacity', true );

    //margin and padding
    $margin = get_post_meta($post_id,'_wphash_notification_content_margin');
    $padding = get_post_meta($post_id,'_wphash_notification_content_padding');

    //button options
    $close_button = get_post_meta( $post_id, '_wphash_notification_close_button', true );
    $button_text = get_post_meta( $post_id, '_wphash_notification_close_button_text', true );
    $button_text = !empty($button_text) ? $button_text : esc_html__( 'Close', 'hashbar' );

    $open_button_text = get_post_meta( $post_id, '_wphash_notification_open_button_text', true );

    $close_button_bg_color = get_post_meta( $post_id, '_wphash_notification_close_button_bg_color', true );
    $close_button_color = get_post_meta( $post_id, '_wphash_notification_close_button_color', true );
    $close_button_hover_color = get_post_meta( $post_id, '_wphash_notification_close_button_hover_color', true );
    $close_button_hover_bg_color = get_post_meta( $post_id, '_wphash_notification_close_button_hover_bg_color', true );

    $arrow_color = get_post_meta( $post_id, '_wphash_notification_arrow_color', true );
    $arrow_bg_color = get_post_meta( $post_id, '_wphash_notification_arrow_bg_color', true );
    $arrow_hover_color = get_post_meta( $post_id, '_wphash_notification_arrow_hover_color', true );
    $arrow_hover_bg_color = get_post_meta( $post_id, '_wphash_notification_arrow_hover_bg_color', true );
    $prb_margin = get_post_meta($post_id,'_wphash_prb_margin');

    $css_style = '';
    if(!empty($content_color)){
        $css_style .= "#notification-$post_id .ht-notification-text,#notification-$post_id .ht-notification-text p{color:$content_color}";
    }

    if(!empty($content_bg_color)){
        $css_style .= "#notification-$post_id::before{background-color:$content_bg_color}";
    }

    if(!empty($content_bg_image)){
        $css_style .= "#notification-$post_id::before{background-image:url($content_bg_image)}";
    }

    if(!empty($content_bg_opacity)){
        $css_style .= "#notification-$post_id::before{opacity:$content_bg_opacity}";
    }

    if($margin && is_array($margin[0])){
        $css_style .= "#notification-$post_id .ht-notification-text{margin:".$margin[0]['margin_top']." ".$margin[0]['margin_right']." ".$margin[0]['margin_bottom']." ".$margin[0]['margin_left']."}";
    }

    if($padding && is_array($padding[0])){
        $css_style .= "#notification-$post_id .ht-notification-text{padding:".$padding[0]['padding_top']." ".$padding[0]['padding_right']." ".$padding[0]['padding_bottom']." ".$padding[0]['padding_left']."}";
    }


    $prb_margin_top    = $prb_margin && is_array($prb_margin[0]) && !empty($prb_margin[0]['margin_top']) ? $prb_margin[0]['margin_top'] : '';
    $prb_margin_right  = $prb_margin && is_array($prb_margin[0]) && !empty($prb_margin[0]['margin_right']) ? $prb_margin[0]['margin_right'] : '';
    $prb_margin_bottom = $prb_margin && is_array($prb_margin[0]) && !empty($prb_margin[0]['margin_bottom']) ? $prb_margin[0]['margin_bottom'] : '';
    $prb_margin_left   = $prb_margin && is_array($prb_margin[0]) && !empty($prb_margin[0]['margin_left']) ? $prb_margin[0]['margin_left'] : '';




    
    if($width){
        if( 'ht-n_bottompromo' == $positon || 'ht-n_toppromo' == $positon ){
            $css_style .= "#notification-$post_id .ht-notification-text .ht-promo-banner{width:$width}";
            $css_style .= "#notification-$post_id .ht-notification-text .ht-promo-banner-image a img{width:$width !important}";
        }else{
            $css_style .= "#notification-$post_id{width:$width}";
        }
    }
    if($close_button_bg_color) $css_style .= "#notification-$post_id .ht-n-close-toggle{background-color:$close_button_bg_color}";
    if($close_button_color) $css_style .= "#notification-$post_id .ht-n-close-toggle,#notification-$post_id .ht-n-close-toggle i{color:$close_button_color}";
    if($close_button_hover_bg_color) $css_style .= "#notification-$post_id .ht-n-close-toggle:hover{background-color:$close_button_hover_bg_color}";
    if($close_button_hover_color) $css_style .= "#notification-$post_id .ht-n-close-toggle:hover{color:$close_button_hover_color}";
    if($close_button_hover_color) $css_style .= "#notification-$post_id .ht-n-close-toggle:hover i{color:$close_button_hover_color}";

    if($arrow_bg_color) $css_style .= "#notification-$post_id .ht-n-open-toggle{background-color:$arrow_bg_color}";
    if($arrow_color) $css_style .= "#notification-$post_id .ht-n-open-toggle{color:$arrow_color}";

    if($arrow_hover_color) $css_style .= "#notification-$post_id .ht-n-open-toggle:hover i{color:$arrow_hover_color}";
    if($arrow_hover_bg_color) $css_style .= "#notification-$post_id .ht-n-open-toggle:hover{background-color:$arrow_hover_bg_color}";

    // mobile device breakpoint
	$hashbar_wpnbp_opt = get_option( 'hashbar_wpnbp_opt');
	$mobile_device_width = isset($hashbar_wpnbp_opt['mobile_device_breakpoint']) ? $hashbar_wpnbp_opt['mobile_device_breakpoint'] : '';
	$mobile_device_width = empty($mobile_device_width) ? 768 : $mobile_device_width; 
	$desktop_device_width = $mobile_device_width + 1;

    $responsive_style = '';
    if($on_mobile == 'off'){
        $padding_top = '';
        $padding_bottom = '';
        if($positon == 'ht-n-top'){
            $padding_top = 'padding-top:0 !important;';
        } elseif( $positon == 'ht-n-bottom' ){
            $padding_bottom = 'padding-bottom:0 !important;';
        }

        $responsive_style = "@media (max-width: ".$mobile_device_width."px){#notification-$post_id{display:none} body.htnotification-mobile{ $padding_top $padding_bottom } }";
    }
    if($on_desktop == 'off'){
        $responsive_style = "@media (min-width: ". $desktop_device_width ."px){#notification-$post_id{display:none}}";
    }

    switch ($positon) {
        case 'ht-n-left':
            $arrow_class = HASHBAR_WPNB_URI.'/images/arrow-right.svg';
            break;

        case 'ht-n-right':
            $arrow_class = HASHBAR_WPNB_URI.'/images/arrow-left.svg';
            break;

        case 'ht-n-bottom':
            $arrow_class = HASHBAR_WPNB_URI.'/images/arrow-up.svg';
            break;
        
        default:
            $arrow_class = HASHBAR_WPNB_URI.'/images/arrow-down.svg';
            break;
    }

    
    // get the number input of how many time this notifcation will show
    // make a unique meta key for this item
    // add post meta for this unique item
    // get view count of this item
    $count_input = get_post_meta($post_id, '_wphash_notification_how_many_times_to_show', true);
    $count_key = 'post_'. $post_id .'_views_count';
    $post_view_count = get_post_meta($post_id, $count_key, true);

    // if user iput is any value which is less than 1
    // then delete post meta
    // otherwise update the post meta increment by 1
    if($count_input < 1){
        delete_post_meta($post_id, $count_key);
    } else {
        $post_view_count = $post_view_count + 1;
        update_post_meta($post_id, $count_key, $post_view_count);
    }

    // dont load the notification when view count over than user input
    if($count_input == '' || $count_input >= $post_view_count):
        if( 'ht-n_bottompromo' == $positon ){
            $positon = 'ht-n-bottom ht-n_bottompromo';
        }

        if( 'ht-n_toppromo' == $positon ){
            $positon = 'ht-n-top ht-n_toppromo';
        }

    ?>

    <!--Notification Section-->
    <div id="notification-<?php echo esc_attr( $post_id ); ?>" class="ht-notification-section <?php echo esc_attr($content_width); ?> <?php echo esc_attr($positon); ?> <?php echo esc_attr($display); ?> <?php echo 'show_hide_scroll_enable' == $where_to_show ? 'ht-n-scroll' : ''; ?>">

        <!--Notification Open Buttons-->
        <?php if(empty($open_button_text)): ?>
            <span class="ht-n-open-toggle"><img src="<?php echo esc_url($arrow_class); ?>" alt="open" style="height: 20px; width: 20px;"></span>
        <?php else: ?>
             <span class="ht-n-open-toggle has_text"><span><?php echo esc_html($open_button_text); ?></span></span>
        <?php endif; ?>

        <div class="ht-notification-wrap">
            <div class="<?php echo $content_width == 'ht-n-full-width' ? esc_attr( 'ht-n-container_full_width' ) : esc_attr('ht-n-container'); ?>">

                <?php if( $close_button != 'off' ): ?>
                <!--Notification Buttons-->
                <div class="ht-notification-buttons">
                    <button class="ht-n-close-toggle" data-text="<?php echo esc_html( $button_text ); ?>"><img src="<?php echo esc_url(HASHBAR_WPNB_URI.'/images/close.svg'); ?>" alt="close" style=" height: 20px; width: 20px; "></button>
                </div>
                <?php endif; ?>

                <!--Notification Text-->
                <div class="ht-notification-text">
                    <?php the_content(); ?>
                </div>

            </div>
        </div>

    </div>


    <style type="text/css">
        <?php echo esc_html($css_style.$responsive_style); ?>
    </style>
    <?php if('show_hide_scroll_enable' == $where_to_show): ?>
        <script>
        (function($) {
        "use strict";
            //show hide notification on scroll
            var $window = $(window);
            var $notificationSectionId = '#notification-'+<?php echo esc_attr( $post_id ); ?>;
            var $notificationSection   = $($notificationSectionId);
            var $notiBottomHeight      = $($notificationSectionId+'.ht-notification-section.ht-n-bottom').height();
            var $notiTopHeight         = $($notificationSectionId+'.ht-notification-section.ht-n-top').height();
            var $bannerSelector        = $notificationSectionId+' .ht-notification-wrap';
            var $scrl_show_position    = '<?php echo  get_post_meta( $post_id, '_wphash_show_scroll_position', true ); ?>';
            var $scrl_hide_position    = '<?php echo  get_post_meta( $post_id, '_wphash_hide_scroll_position', true ); ?>';
            var $promo_bottom_positon  = '<?php echo  get_post_meta( $post_id, '_wphash_promo_banner_bottom_display', true );?>';
            var $promo_top_positon     = '<?php echo  get_post_meta( $post_id, '_wphash_promo_banner_top_display', true );?>';
            var $window_inner_height   = $window.height();
            var $page_height           = $('body').height();

            if($notificationSection.hasClass('ht-n_bottompromo')){
                if( 'promo-bottom-left' == $promo_bottom_positon){
                    $($notificationSectionId+" .ht-notification-text").css({
                                                "left"  : "<?php echo !empty($prb_margin_left) ? $prb_margin_left :'5%'; ?>",
                                                "bottom": "<?php echo !empty($prb_margin_bottom)? $prb_margin_bottom : '100px'; ?>"
                                            });
                }

                if( 'promo-bottom-right' == $promo_bottom_positon){
                    $($notificationSectionId+" .ht-notification-text").css({
                                                "right" :  "<?php echo !empty($prb_margin_right) ? $prb_margin_right : '5%'; ?>",
                                                "bottom":  "<?php echo !empty($prb_margin_bottom) ? $prb_margin_bottom : '100px'; ?>"
                                            });
                }
            }

            if($notificationSection.hasClass('ht-n_toppromo')){
                if( 'promo-top-left' == $promo_top_positon){
                    $($notificationSectionId+" .ht-notification-text").css({
                                                "left" :  "<?php echo !empty($prb_margin_left) ? $prb_margin_left : '5%'; ?>",
                                                "top"  :  "<?php echo !empty($prb_margin_top) ? $prb_margin_top : '150px'; ?>"
                                            });
                }

                if( 'promo-top-right' == $promo_top_positon){
                    $($notificationSectionId+" .ht-notification-text").css({
                                                "right": "<?php echo !empty($prb_margin_right) ? $prb_margin_right : '5%'; ?>",
                                                "top"  : "<?php echo !empty($prb_margin_top) ? $prb_margin_top : '150px'; ?>"
                                            });
                }
            }

            if( $notificationSection.is('.ht-n-scroll.ht-n_bottompromo') || $notificationSection.is('.ht-n-scroll.ht-n_toppromo')){
                $bannerSelector  = $notificationSectionId+' .ht-notification-text';
                if( !$scrl_show_position || '0%' ==  $scrl_show_position || '0' == $scrl_show_position){
                    $($bannerSelector).css({'display' : 'unset'});
                }else{
                    $($bannerSelector).css({'display' : 'none'});
                }
            }

            if( !$scrl_show_position || '0%' ==  $scrl_show_position || '0' == $scrl_show_position){
                $scrl_show_position = '0%';
                $notificationSection.removeClass('ht-n-close').addClass('ht-n-open');
            }

            if( !$scrl_hide_position || '0%' ==  $scrl_hide_position || '0' == $scrl_hide_position){
                $scrl_hide_position = '0%';
            }

            $scrl_show_position = scroll_value_process($scrl_show_position);
            $scrl_hide_position = scroll_value_process($scrl_hide_position);

            $window.on('scroll', function(e){

                var $scroll_top    = $window.scrollTop();
                var $scroll_bottom = $page_height - $window_inner_height;
                var $scroll        = $scroll_top / $scroll_bottom * 100;

                //show notificaiton on scrolling position
                if($notificationSection.is('.ht-n-scroll.ht-n-bottom')){
                    var $bottomSection = $($notificationSectionId+'.ht-n-bottom');
                    show_hide_ntf_process($bottomSection,$notiBottomHeight,$scroll,'padding-bottom');
                }

                if($notificationSection.is('.ht-n-scroll.ht-n-top')){
                    var $topSection = $($notificationSectionId+'.ht-n-top');
                    show_hide_ntf_process($topSection,$notiTopHeight,$scroll,'padding-bottom');
                }

            });

            function scroll_value_process($scroll_value){
                if($scroll_value.includes('%')){
                    var $percent_value = $scroll_value.replace('%', '');
                    var $scroll_value  = parseInt($percent_value);
                    return $scroll_value;
                }else{
                    return parseInt($scroll_value);
                }
            }

            function show_hide_ntf_process($section,$sectionHeight,$scroll,$element){
                if ( $scroll >= $scrl_show_position && $scroll <= $scrl_hide_position ) {
                    show_scrl_notification($section);
                }else if(0 < $scrl_show_position && 0 == $scrl_hide_position){
                    if ( $scroll >= $scrl_show_position ){
                       show_scrl_notification($section);
                    }else{
                       hide_scrl_notification($section);
                    }
                }else if( 0 == $scrl_show_position && 0 < $scrl_hide_position ){
                    if ( $scroll >= $scrl_hide_position ){
                        hide_scrl_notification($section);
                    }
                }else{
                    hide_scrl_notification($section);
                }
            }

            function show_scrl_notification($section){
                $section.find('.ht-n-open-toggle').removeClass('ht-n-active');
                $section.removeClass('ht-n-close').addClass('ht-n-open');
                $($bannerSelector).slideDown();
            }

            function hide_scrl_notification($section){
                $section.find('.ht-n-open-toggle').addClass('ht-n-active');
                $section.removeClass('ht-n-open').addClass('ht-n-close');
                $($bannerSelector).slideUp();
            }
        })(jQuery);
        </script>
    <?php endif; ?>
    <?php
    endif;
}


// page builder king composer and visual composer
add_action( 'init', 'hashbar_wpnb_page_builder_support' );
function hashbar_wpnb_page_builder_support(){
    //king composer support
    global $kc;

    if($kc){
        $kc->add_content_type( 'wphash_ntf_bar' );
    }

    //vc support
    if( class_exists( 'VC_Manager' ) ){
    	$default_post_types = vc_default_editor_post_types();

    	if(!in_array('wphash_ntf_bar', $default_post_types)){
    		$default_post_types[] = 'wphash_ntf_bar';
    	}
        
        vc_set_default_editor_post_types( $default_post_types );
    }
}


// set post view to 0 when update notification
// define the updated_post_meta callback
add_action( 'save_post', 'hashbar_wpnp_update_meta', 10, 3 );
function hashbar_wpnp_update_meta( $post_id, $post, $update ) {
    if($post->post_type == 'wphash_ntf_bar'){
        $count_key = 'post_'. $post_id .'_views_count';
        update_post_meta( $post_id, $count_key, 0 );
    }
};