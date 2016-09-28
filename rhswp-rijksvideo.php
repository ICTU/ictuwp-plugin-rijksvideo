<?php
/*
 * Rijksvideo. Slideshow plugin for WordPress.
 *
 * Plugin Name: Rijksvideo
 * Plugin URI:  https://www.metaslider.com
 * Description: Easy to use slideshow plugin. Create SEO optimised responsive slideshows with Nivo Slider, Flex Slider, Coin Slider and Responsive Slides.
 * Version:     1.0.0
 * Author:      Matcha Labs
 * Author URI:  https://www.metaslider.com
 * License:     GPL-2.0+
 * Copyright:   2014 Matcha Labs LTD
 *
 * Text Domain: rhswp-rijksvideo
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // disable direct access
}

if ( ! class_exists( 'RijksvideoPlugin_v1' ) ) :

/**
 * Register the plugin.
 *
 * Display the administration panel, insert JavaScript etc.
 */
class RijksvideoPlugin_v1 {

    /**
     * @var string
     */
    public $version = '1.0.0';


    /**
     * @var MetaSlider
     */
    public $slider = null;


    /**
     * Init
     */
    public static function init() {

        $metaslider = new self();

    }


    /**
     * Constructor
     */
    public function __construct() {

        $this->define_constants();
        $this->includes();
        $this->setup_actions();
        $this->setup_filters();
        $this->setup_shortcode();
        $this->register_slide_types();
        $this->append_comboboxes();


    }


    /**
     * Define Rijksvideo constants
     */
    private function define_constants() {

        define( 'RIJKSVIDEO_VERSION',    $this->version );
        define( 'RIJKSVIDEO_BASE_URL',   trailingslashit( plugins_url( "rhswp-rijksvideo" ) ) );
        define( 'RIJKSVIDEO_ASSETS_URL', trailingslashit( RIJKSVIDEO_BASE_URL . 'assets' ) );
        define( 'RIJKSVIDEO_PATH',       plugin_dir_path( __FILE__ ) );
        define( 'RIJKSVIDEO_CPT',        "rijksvideo_old" );
        define( 'RIJKSVIDEO_CT',         "rijksvideo_custom_taxonomy" );
        
        define( 'RHSWP_CPT_RIJKSVIDEO', 'rijksvideo_old' ); // slug for custom post type 'rijksvideo'
        define( 'RHSWP_CPT_VIDEO_PREFIX', RHSWP_CPT_RIJKSVIDEO . '_xx_' ); // prefix for rijksvideo metadata fields
        define( 'RHSWP_CPT_RIJKSVIDEO_SHOW_DEBUG', false );
        define( 'RHSWP_CPT_RIJKSVIDEO_USE_CMB2', true ); 
        

    }


    /**
     * All Rijksvideo classes
     */
    private function plugin_classes() {

        return array(
            'rijksvideo'             => RIJKSVIDEO_PATH . 'inc/slider/metaslider.class.php',
//            'metacoinslider'         => RIJKSVIDEO_PATH . 'inc/slider/metaslider.coin.class.php',
//            'metaflexslider'         => RIJKSVIDEO_PATH . 'inc/slider/metaslider.flex.class.php',
//            'metanivoslider'         => RIJKSVIDEO_PATH . 'inc/slider/metaslider.nivo.class.php',
//            'metaresponsiveslider'   => RIJKSVIDEO_PATH . 'inc/slider/metaslider.responsive.class.php',
//            'metaslide'              => RIJKSVIDEO_PATH . 'inc/slide/metaslide.class.php',
//            'metaimageslide'         => RIJKSVIDEO_PATH . 'inc/slide/metaslide.image.class.php',
//            'metasliderimagehelper'  => RIJKSVIDEO_PATH . 'inc/metaslider.imagehelper.class.php',
//            'metaslidersystemcheck'  => RIJKSVIDEO_PATH . 'inc/metaslider.systemcheck.class.php',
//            'metaslider_widget'      => RIJKSVIDEO_PATH . 'inc/metaslider.widget.class.php',
//            'simple_html_dom'        => RIJKSVIDEO_PATH . 'inc/simple_html_dom.php'
        );

    }


    /**
     * Load required classes
     */
    private function includes() {

        $autoload_is_disabled = defined( 'RIJKSVIDEO_AUTOLOAD_CLASSES' ) && RIJKSVIDEO_AUTOLOAD_CLASSES === false;

        if ( function_exists( "spl_autoload_register" ) && ! ( $autoload_is_disabled ) ) {

            // >= PHP 5.2 - Use auto loading
            if ( function_exists( "__autoload" ) ) {
                spl_autoload_register( "__autoload" );
            }

            spl_autoload_register( array( $this, 'autoload' ) );

        } else {

            // < PHP5.2 - Require all classes
            foreach ( $this->plugin_classes() as $id => $path ) {
                if ( is_readable( $path ) && ! class_exists( $id ) ) {
                    require_once( $path );
                }
            }

        }

    }



//* Customize the entry meta in the entry footer (requires HTML5 theme support)
//add_action( 'genesis_entry_content', 'rhswp_append_video_details' );

/*
function rhswp_append_video_details($post_meta) {
  
  global $post;

  if ( is_single() ) {
    if ( RHSWP_CPT_RIJKSVIDEO    == get_post_type() ) {
      
      $postid = $post->ID;
      rhswp_makevideo($postid);
      
            
      
    }
  }
}
*/

    /**
     * Autoload Rijksvideo classes to reduce memory consumption
     */
    public function autoload( $class ) {

        $classes = $this->plugin_classes();

        $class_name = strtolower( $class );

        if ( isset( $classes[$class_name] ) && is_readable( $classes[$class_name] ) ) {
            require_once( $classes[$class_name] );
        }

    }


    /**
     * Register the [metaslider] shortcode.
     */
    private function setup_shortcode() {

        add_shortcode( 'rijksvideo', array( $this, 'register_shortcode' ) );
        add_shortcode( RIJKSVIDEO_CPT, array( $this, 'register_shortcode' ) ); // backwards compatibility

    }


    /**
     * Hook Rijksvideo into WordPress
     */
    private function setup_actions() {

        add_action( 'init', array( $this, 'register_post_type' ) );
//        add_action( 'init', array( $this, 'register_taxonomy' ) );
        add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
        add_action( 'admin_footer', array( $this, 'admin_footer' ), 11 );

//        add_action( 'admin_post_metaslider_preview', array( $this, 'do_preview' ) );
//        add_action( 'admin_post_metaslider_switch_view', array( $this, 'switch_view' ) );
//        add_action( 'admin_post_metaslider_delete_slide', array( $this, 'delete_slide' ) );
//        add_action( 'admin_post_metaslider_delete_slider', array( $this, 'delete_slider' ) );
//        add_action( 'admin_post_metaslider_create_slider', array( $this, 'create_slider' ) );
//       add_action( 'admin_post_metaslider_update_slider', array( $this, 'update_slider' ) );

    }


    /**
     * Hook Rijksvideo into WordPress
     */
    private function setup_filters() {

        add_filter( 'media_upload_tabs', array( $this, 'custom_media_upload_tab_name' ), 998 );
        add_filter( 'media_view_strings', array( $this, 'custom_media_uploader_tabs' ), 5 );
        add_filter( 'media_buttons_context', array( $this, 'insert_metaslider_button' ) );

        // html5 compatibility for stylesheets enqueued within <body>
        add_filter( 'style_loader_tag', array( $this, 'add_property_attribute_to_stylesheet_links' ), 11, 2 );

    }



    /**
     * Register post type
     */
    public function register_post_type() {

    	$labels = array(
    		"name"                  => "Rijksvideo's",
    		"singular_name"         => "Rijksvideo",
    		"menu_name"             => "Rijksvideo's",
    		"all_items"             => "Alle rijksvideo's",
    		"add_new"               => "Nieuwe toevoegen",
    		"add_new_item"          => "Nieuwe rijksvideo toevoegen",
    		"edit"                  => "Bewerken?",
    		"edit_item"             => "Bewerk rijksvideo",
    		"new_item"              => "Nieuwe rijksvideo's",
    		"view"                  => "Toon",
    		"view_item"             => "Bekijk rijksvideo",
    		"search_items"          => "Zoek rijksvideo",
    		"not_found"             => "Niet gevonden",
    		"not_found_in_trash"    => "Geen rijksvideo's gevonden in de prullenbak",
    		"parent"                => "Hoofd",
    		);
    
    	$args = array(
    		"labels"                => $labels,
        "label"                 => __( 'Rijksvideo', '' ),
        "labels"                => $labels,
        "description"           => "",
        "public"                => true,
        "publicly_queryable"    => true,
        "show_ui"               => true,
        "show_in_rest"          => false,
        "rest_base"             => "",
        "has_archive"           => false,
        "show_in_menu"          => true,
        "exclude_from_search"   => false,
        "capability_type"       => "post",
        "map_meta_cap"          => true,
        "hierarchical"          => false,
        "rewrite"               => array( "slug" => RIJKSVIDEO_CPT, "with_front" => true ),
        "query_var"             => true,
    		"supports"              => array( "title", "editor", "thumbnail" ),					
    		);
    	register_post_type( RIJKSVIDEO_CPT, $args );

    }


    /**
     * Register taxonomy to store slider => slides relationship
    public function register_taxonomy() {

        register_taxonomy( RIJKSVIDEO_CT, 'attachment', array(
                'hierarchical' => true,
                'public' => false,
                'query_var' => false,
                'rewrite' => false
            )
        );

    }
     */


    /**
     * Register our slide types
     */
    private function register_slide_types() {

//        $image = new MetaImageSlide();

    }




    /**
     * Shortcode used to display slideshow
     *
     * @return string HTML output of the shortcode
     */
    public function register_shortcode( $atts ) {

        extract( shortcode_atts( array(
            'id' => false,
            'restrict_to' => false
        ), $atts, 'rijksvideo' ) );


        if ( ! $id ) {
            return false;
        }

        // handle [metaslider id=123 restrict_to=home]
        if ($restrict_to && $restrict_to == 'home' && ! is_front_page()) {
            return;
        }

        if ($restrict_to && $restrict_to != 'home' && ! is_page( $restrict_to ) ) {
            return;
        }

        // we have an ID to work with
        $slider = get_post( $id );

        // check the slider is published and the ID is correct
        if ( ! $slider || $slider->post_status != 'publish' || $slider->post_type != RIJKSVIDEO_CPT ) {
            return "<!-- meta slider {$atts['id']} not found -->";
        }

        // lets go
        //$this->set_slider( $id, $atts );
        //$this->slider->enqueue_scripts();
        
        return 'hier komt dan een video met id: ' . $id . '<br>' . $this->rhswp_makevideo($id);
        

//        return $this->slider->render_public_slides();

    }


    /**
     * Initialise translations
     */
    public function load_plugin_textdomain() {

        load_plugin_textdomain( 'rhswp-rijksvideo', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

    }


    /**
     * Add the help tab to the screen.
     */
    public function help_tab() {

        $screen = get_current_screen();

        // documentation tab
        $screen->add_help_tab( array(
                'id'    => 'documentation',
                'title' => __( 'Documentation', "rhswp-rijksvideo" ),
                'content'   => "<p><a href='http://www.metaslider.com/documentation/' target='blank'>Rijksvideo Documentation</a></p>",
            )
        );

    }


    /**
     * Rehister admin styles
     */
    public function register_admin_styles() {

        wp_enqueue_style( 'metaslider-admin-styles', RIJKSVIDEO_ASSETS_URL . 'metaslider/admin.css', false, RIJKSVIDEO_VERSION );
        wp_enqueue_style( 'metaslider-colorbox-styles', RIJKSVIDEO_ASSETS_URL . 'colorbox/colorbox.css', false, RIJKSVIDEO_VERSION );
        wp_enqueue_style( 'metaslider-tipsy-styles', RIJKSVIDEO_ASSETS_URL . 'tipsy/tipsy.css', false, RIJKSVIDEO_VERSION );

        do_action( 'metaslider_register_admin_styles' );

    }


    /**
     * Register admin JavaScript
     */
    public function register_admin_scripts() {

        // media library dependencies
        wp_enqueue_media();

        // plugin dependencies
        wp_enqueue_script( 'jquery-ui-core', array( 'jquery' ) );
        wp_enqueue_script( 'jquery-ui-sortable', array( 'jquery', 'jquery-ui-core' ) );
        wp_enqueue_script( 'metaslider-colorbox', RIJKSVIDEO_ASSETS_URL . 'colorbox/jquery.colorbox-min.js', array( 'jquery' ), RIJKSVIDEO_VERSION );
        wp_enqueue_script( 'metaslider-tipsy', RIJKSVIDEO_ASSETS_URL . 'tipsy/jquery.tipsy.js', array( 'jquery' ), RIJKSVIDEO_VERSION );
        wp_enqueue_script( 'metaslider-admin-script', RIJKSVIDEO_ASSETS_URL . 'metaslider/admin.js', array( 'jquery', 'metaslider-tipsy', 'media-upload' ), RIJKSVIDEO_VERSION );

        wp_dequeue_script( 'link' ); // WP Posts Filter Fix (Advanced Settings not toggling)
        wp_dequeue_script( 'ai1ec_requirejs' ); // All In One Events Calendar Fix (Advanced Settings not toggling)

        $this->localize_admin_scripts();

        do_action( 'metaslider_register_admin_scripts' );

    }


    /**
     * Localise admin script
     */
    public function localize_admin_scripts() {

        wp_localize_script( 'metaslider-admin-script', 'rijksvideo', array(
                'url' => __( "URL", "rhswp-rijksvideo" ),
                'caption' => __( "Caption", "rhswp-rijksvideo" ),
                'new_window' => __( "New Window", "rhswp-rijksvideo" ),
                'confirm' => __( "Are you sure?", "rhswp-rijksvideo" ),
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'change_image' => __( "Select replacement image", "ml-slider"),
                'resize_nonce' => wp_create_nonce( 'metaslider_resize' ),
                'addslide_nonce' => wp_create_nonce( 'metaslider_addslide' ),
                'changeslide_nonce' => wp_create_nonce( 'metaslider_changeslide' ),
                'iframeurl' => admin_url( 'admin-post.php?action=metaslider_preview' ),
                'useWithCaution' => __( "Caution: This setting is for advanced developers only. If you're unsure, leave it checked.", "rhswp-rijksvideo" )
            )
        );

    }

    //========================================================================================================
    /**
     * Output the HTML
     */
    public function rhswp_makevideo($postid) {
          $videoplayer_width              = '500';
          $videoplayer_height             = '412';
          $video_id                       = 'movie-' . $postid;
          
          $videoplayer_aria_id            = 'mep_7';
          $videoplayer_date               = 'DATUM';
          
          $videoplayer_title              = _x( 'Video Player', 'Rijksvideo', 'wp-rijkshuisstijl' );
          $videoplayer_video_txt          = _x( 'Video', 'Rijksvideo', 'wp-rijkshuisstijl' );
          $videoplayer_audio_txt          = _x( 'Audio', 'Rijksvideo', 'wp-rijkshuisstijl' );
          $videoplayer_subtitle_txt       = _x( 'Caption', 'Rijksvideo', 'wp-rijkshuisstijl' );
          $videoplayer_subtitles          = _x( 'Ondertitels', 'Rijksvideo', 'wp-rijkshuisstijl' );
          $videoplayer_subtitles_language = _x( 'nl', 'Rijksvideo: voor welke taal heb je ondertitels geupload?', 'wp-rijkshuisstijl' );
          
          
          $videoplayer_play               = _x( 'Afspelen', 'Rijksvideo', 'wp-rijkshuisstijl' );
          $videoplayer_mute_label         = _x( 'Geluid uit', 'Rijksvideo', 'wp-rijkshuisstijl' );
          $videoplayer_fullscreen_open    = _x( 'Schermvullende weergave openen', 'Rijksvideo', 'wp-rijkshuisstijl' );
          $videoplayer_fullscreen_close   = _x( 'Schermvullende weergave sluiten', 'Rijksvideo', 'wp-rijkshuisstijl' );
          $videoplayer_volume_label       = _x( 'Gebruik op- of neer-pijltjestoetsen om het volume harder of zachter te zetten', 'Rijksvideo', 'wp-rijkshuisstijl' );
          $videoplayer_subtitle_on        = _x( 'Ondertiteling aan', 'Rijksvideo', 'wp-rijkshuisstijl' );
          $videoplayer_download_label     = _x( 'Download deze video', 'Rijksvideo', 'wp-rijkshuisstijl' );
          $videoplayer_noplugin_label     = _x( 'Helaas kan deze video niet worden afgespeeld. Een mogelijk oplossing is de meest recente versie van uw browser te installeren.', 'Rijksvideo', 'wp-rijkshuisstijl' );
          
          $videoplayer_quicktime_label    = _x( 'Video voor Apple Quicktime Player', 'Rijksvideo', 'wp-rijkshuisstijl' );
          $videoplayer_quicktime_abbr     = _x( 'mp4', 'Rijksvideo', 'wp-rijkshuisstijl' );
          $videoplayer_quicktime_hr       = _x( ' (hoge resolutie)', 'Rijksvideo', 'wp-rijkshuisstijl' );
          
          $videoplayer_wmv_label          = _x( 'Video voor Windows Media Player', 'Rijksvideo', 'wp-rijkshuisstijl' );
          $videoplayer_wmv_abbr           = _x( 'wmv', 'Rijksvideo', 'wp-rijkshuisstijl' );
          
          $videoplayer_mobileformat_label = _x( 'Video voor mobiel gebruik', 'Rijksvideo', 'wp-rijkshuisstijl' );
          $videoplayer_mobileformat_abbr  = _x( '3gp', 'Rijksvideo', 'wp-rijkshuisstijl' );
          
          $videoplayer_audioformat_label  = _x( 'Audiospoor', 'Rijksvideo', 'wp-rijkshuisstijl' );
          $videoplayer_audioformat_abbr   = _x( 'mp3', 'Rijksvideo', 'wp-rijkshuisstijl' );
          
          $videoplayer_subtitle_label     = _x( 'Ondertitelingsbestand', 'Rijksvideo', 'wp-rijkshuisstijl' );
          $videoplayer_subtitle_abbr      = _x( 'srt', 'Rijksvideo', 'wp-rijkshuisstijl' );
          
    
    
          $rhswp_video_duur               = get_post_meta( $postid, RHSWP_CPT_VIDEO_PREFIX . 'video_time', true );
          $rhswp_video_url_video_thumb    = get_post_meta( $postid, RHSWP_CPT_VIDEO_PREFIX . 'url_video_thumb', true );
          $rhswp_video_url_transcript     = get_post_meta( $postid, RHSWP_CPT_VIDEO_PREFIX . 'url_transcript_file', true );
          $rhswp_video_transcriptvlak     = get_post_meta( $postid, RHSWP_CPT_VIDEO_PREFIX . 'transcriptvlak', true );
          $rhswp_video_audio_url          = get_post_meta( $postid, RHSWP_CPT_VIDEO_PREFIX . 'audio_url', true );
          $rhswp_video_flv_url            = get_post_meta( $postid, RHSWP_CPT_VIDEO_PREFIX . 'flv_url', true );
          $rhswp_video_flv_filesize       = get_post_meta( $postid, RHSWP_CPT_VIDEO_PREFIX . 'flv_filesize', true );
          $rhswp_video_wmv_url            = get_post_meta( $postid, RHSWP_CPT_VIDEO_PREFIX . 'wmv_url', true );
          $rhswp_video_filesize_wmv       = get_post_meta( $postid, RHSWP_CPT_VIDEO_PREFIX . 'filesize_wmv', true );
          $rhswp_video_mp4_url            = get_post_meta( $postid, RHSWP_CPT_VIDEO_PREFIX . 'mp4_url', true );
          $rhswp_video_mp4_filesize       = get_post_meta( $postid, RHSWP_CPT_VIDEO_PREFIX . 'mp4_filesize', true );
          $rhswp_video_mp4_hr_url         = get_post_meta( $postid, RHSWP_CPT_VIDEO_PREFIX . 'mp4_url_hires', true );
          $rhswp_video_mp4_hr_filesize    = get_post_meta( $postid, RHSWP_CPT_VIDEO_PREFIX . 'mp4_filesize_hires', true );
          $rhswp_video_3gp_url            = get_post_meta( $postid, RHSWP_CPT_VIDEO_PREFIX . '3gp_url', true );
          $rhswp_video_3gp_filesize       = get_post_meta( $postid, RHSWP_CPT_VIDEO_PREFIX . '3gp_filesize', true );
          
          
          $returnstring = '';
          
          if ( RHSWP_CPT_RIJKSVIDEO_SHOW_DEBUG && WP_DEBUG ) {
    
    
            $returnstring .= 'RHSWP_CPT_RIJKSVIDEO_USE_CMB2: "' . RHSWP_CPT_RIJKSVIDEO_USE_CMB2 . '"<br>';
            $returnstring .= '$rhswp_video_duur: "' . $rhswp_video_duur . '"<br>';
            $returnstring .= '$rhswp_video_url_video_thumb: "' . $rhswp_video_url_video_thumb . '"<br>';
            $returnstring .= '$rhswp_video_url_transcript: "' . $rhswp_video_url_transcript . '"<br>';
            $returnstring .= '$rhswp_video_transcriptvlak: "' . $rhswp_video_transcriptvlak . '"<br>';
            $returnstring .= '$rhswp_video_audio_url: "' . $rhswp_video_audio_url . '"<br>';
            $returnstring .= '$rhswp_video_flv_url: "' . $rhswp_video_flv_url . '"<br>';
            $returnstring .= '$rhswp_video_flv_filesize: "' . $rhswp_video_flv_filesize . '"<br>';
            $returnstring .= '$rhswp_video_wmv_url: "' . $rhswp_video_wmv_url . '"<br>';
            $returnstring .= '$rhswp_video_filesize_wmv: "' . $rhswp_video_filesize_wmv . '"<br>';
            $returnstring .= '$rhswp_video_mp4_url: "' . $rhswp_video_mp4_url . '"<br>';
            $returnstring .= '$rhswp_video_mp4_filesize: "' . $rhswp_video_mp4_filesize . '"<br>';
            $returnstring .= '$rhswp_video_mp4_hr_url: "' . $rhswp_video_mp4_hr_url . '"<br>';
            $returnstring .= '$rhswp_video_mp4_hr_filesize: "' . $rhswp_video_mp4_hr_filesize . '"<br>';
            $returnstring .= '$rhswp_video_3gp_url: "' . $rhswp_video_3gp_url . '"<br>';
            $returnstring .= '$rhswp_video_3gp_filesize: "' . $rhswp_video_3gp_filesize . '"<br>';
            
          }
    
    //      $returnstring .= '<div class="block-audio-video" id="block-' . $video_id . '"><video id="' . $video_id . '" width="' . $videoplayer_width . '" height="' . $videoplayer_height . '" poster="' . $rhswp_video_url_video_thumb . '" preload="metadata" data-playtxt="Play" data-pauzetxt="Pauzeren" data-enablead="Audio descriptie afspelen" data-disablead="Audio descriptie stoppen" data-enablevolume="Geluid aan" data-disablevolume="Geluid uit" data-enablecc="Ondertiteling aan" data-disablecc="Ondertiteling uit" data-enablefullscreen="Schermvullende weergave openen" data-disablefullscreen="Schervullende weergave sluiten" data-noplugintxt="' . $videoplayer_noplugin_label . '">';
          
    
          $returnstring .= '<div class="block-audio-video" id="block-' . $video_id . '">
          <video id="' . $video_id . '" width="' . $videoplayer_width . '" height="' . $videoplayer_height . '" poster="' . $rhswp_video_url_video_thumb . '" data-noplugintxt="' . $videoplayer_noplugin_label . '">';
          
    
          if ( $rhswp_video_mp4_url ) {
            $returnstring .= '<source type="video/mp4" src="' . $rhswp_video_mp4_url . '">';
          }
          if ( $rhswp_video_wmv_url ) {
            $returnstring .= '<source type="video/wmv" src="' . $rhswp_video_wmv_url . '">';
          }
          if ( $rhswp_video_flv_url ) {
            $returnstring .= '<source type="video/x-flv" src="' . $rhswp_video_flv_url . '">';
          }
          if ( $rhswp_video_url_transcript ) {
            $returnstring .= '<track kind="subtitles" src="' . $rhswp_video_url_transcript . '" label="' . $videoplayer_subtitles . '" srclang="' . $videoplayer_subtitles_language . '"></track>';
          }
          
          $returnstring .= '</video>';
    
          $returnstring .= '<ul><li class="toggle downloads close">
          <h2><a href="#">' . $videoplayer_download_label . '</a></h2><ul>';
          if ( $rhswp_video_mp4_url ) {
            $returnstring .= '<li class="download"><a href="' . $rhswp_video_mp4_url . '">' . $videoplayer_quicktime_label . '<span class="meta mp4">' . $videoplayer_video_txt . ', ' . $videoplayer_date . ', ' . $rhswp_video_duur . ' ' . $videoplayer_quicktime_abbr . ', ' . $rhswp_video_mp4_filesize . '</span></a></li>';
          }
          if ( $rhswp_video_mp4_hr_url ) {
            $returnstring .= '<li class="download"><a href="' . $rhswp_video_mp4_hr_url . '">' . $videoplayer_quicktime_label . '<span class="meta mp4">' . $videoplayer_video_txt . ', ' . $videoplayer_date . ', ' . $rhswp_video_duur . ' ' . $videoplayer_quicktime_abbr . $videoplayer_quicktime_hr . ', ' . $rhswp_video_mp4_hr_filesize . '</span></a></li>';
          }
          if ( $rhswp_video_wmv_url ) {
            $returnstring .= '<li class="download"><a href="' . $rhswp_video_wmv_url . '">' . $videoplayer_wmv_label . '<span class="meta wmv">' . $videoplayer_video_txt . ', ' . $videoplayer_date . ', ' . $rhswp_video_duur . ' ' . $videoplayer_wmv_abbr . ', ' . $rhswp_video_filesize_wmv . '</span></a></li>';
          }
          if ( $rhswp_video_3gp_url ) {
            $returnstring .= '<li class="download"><a href="' . $rhswp_video_3gp_url . '">' . $videoplayer_mobileformat_label . '<span class="meta 3gp">' . $videoplayer_video_txt . ', ' . $videoplayer_date . ', ' . $rhswp_video_duur . ' ' . $videoplayer_mobileformat_abbr . ', ' . $rhswp_video_3gp_filesize . '</span></a></li>';
          }
          if ( $rhswp_video_audio_url ) {
            $returnstring .= '<li class="download"><a href="' . $rhswp_video_audio_url . '">' . $videoplayer_audioformat_label . '<span class="meta mp3">' . $videoplayer_video_txt . ', ' . $videoplayer_date . ', ' . $rhswp_video_duur . ' ' . $videoplayer_audioformat_abbr . ' </span></a></li>';
          }
          if ( $rhswp_video_url_transcript ) {
            $returnstring .= '<li class="download"><a href="' . $rhswp_video_url_transcript . '">' . $videoplayer_subtitle_label . '<span class="meta srt">' . $videoplayer_video_txt . ', ' . $videoplayer_date . ', ' . $rhswp_video_duur . ' ' . $videoplayer_subtitle_abbr . ' </span></a></li>';
          }
          $returnstring .= '</ul></li>';
    
          if ( $rhswp_video_transcriptvlak ) {
            $returnstring .= '<li class="toggle transcription"><h2><a href="#">Uitgeschreven tekst</a></h2><div><h3>' . get_the_title() . '</h3>' . $rhswp_video_transcriptvlak . '</div></li>';
          }
          else {
            $returnstring .= '<li class="toggle transcription"><h2><a href="#">Hehu</a></h2</li>';
          }
          $returnstring .= '</ul></div>';
          
    ?>
    <script  type="text/javascript">
    
    function appendMediaEvents($node, media) {
    	var 
    		mediaEventNames = 'loadstart progress suspend abort error emptied stalled play pause loadedmetadata loadeddata waiting playing canplay canplaythrough seeking seeked timeupdate ended ratechange durationchange volumechange'.split(' ');
    		mediaEventTable = jQuery('<table class="media-events"><caption>Media Events</caption><tbody></tbody></table>').appendTo($node).find('tbody'),
    		tr = null,
    		th = null,
    		td = null,
    		eventName = null,
    		il = 0,				
    		i=0;
    		
    	for (il = mediaEventNames.length;i<il;i++) {
    		eventName = mediaEventNames[i];
    		th = jQuery('<th>' + eventName + '</th>');
    		td = jQuery('<td id="e_' + media.id + '_' + eventName + '" class="not-fired">0</td>');
    					
    		if (tr == null) 
    			tr = jQuery("<tr/>");
    			
    		tr.append(th);
    		tr.append(td);
    
    		if ((i+1) % 5 == 0) {
    			mediaEventTable.append(tr);
    			tr = null;
    		}		
    		
    		// listen for event
    		media.addEventListener(eventName, function(e) {
    			
    			var notice = jQuery('#e_' + media.id + '_' + e.type),
    				number = parseInt(notice.html(), 10);
    			
    			notice
    				.html(number+1)
    				.attr('class','fired');
    		}, true);
    	}	
    	
    	mediaEventTable.append(tr);
    }
    
    function appendMediaProperties($node, media) {
    	var /* src currentSrc  */
    		mediaPropertyNames = 'error currentSrc networkState preload buffered bufferedBytes bufferedTime readyState seeking currentTime initialTime duration startOffsetTime paused defaultPlaybackRate playbackRate played seekable ended autoplay loop controls volume muted'.split(' '),
    		mediaPropertyTable = jQuery('<table class="media-properties"><caption>Media Properties</caption><tbody></tbody></table>').appendTo($node).find('tbody'),
    		tr = null,
    		th = null,
    		td = null,
    		propName = null,	
    		il = 0,		
    		i=0;
    		
    	for (il = mediaPropertyNames.length; i<il; i++) {
    		propName = mediaPropertyNames[i];
    		th = jQuery('<th>' + propName + '</th>');
    		td = jQuery('<td id="p_' + media.id + '_' + propName + '" class=""></td>');
    					
    		if (tr == null) 
    			tr = jQuery("<tr/>");
    			
    		tr.append(th);
    		tr.append(td);
    
    		if ((i+1) % 3 == 0) {
    			mediaPropertyTable.append(tr);
    			tr = null;
    		}
    	}	
    	
    	setInterval(function() {
    		var 
    			propName = '',
    			val = null,
    			td = null;
    		
    		for (i = 0, il = mediaPropertyNames.length; i<il; i++) {
    			propName = mediaPropertyNames[i];
    			td = jQuery('#p_' + media.id + '_' + propName);
    			val = media[propName];
    			val = 
    				(typeof val == 'undefined') ? 
    				'undefined' : (val == null) ? 'null' : val.toString();
    			td.html(val);
    		}
    	}, 500);	
    	
    }
    
      
    jQuery('audio, video').bind('error', function(e) {
    
    	console.log('error',this, e, this.src, this.error.code);
    });
    
    jQuery(document).ready(function() {
    	jQuery('audio, video').mediaelementplayer({
    		//mode: 'shim',
    	
    		pluginPath:'../build/', 
    		enablePluginSmoothing:true,
    		//duration: 489,
    		//startVolume: 0.4,
    		enablePluginDebug: true,
    		//iPadUseNativeControls: true,
    		//mode: 'shim',
    		//forcePluginFullScreen: true,
    		//usePluginFullScreen: true,
    		//mode: 'native',
    		//plugins: ['silverlight'],
    		//features: ['playpause','progress','volume','speed','fullscreen'],
    		success: function(me,node) {
    			// report type
    			var tagName = node.tagName.toLowerCase();
    			jQuery('#' + tagName + '-mode').html( me.pluginType  + ': success' + ', touch: ' + mejs.MediaFeatures.hasTouch);
    
    			
    			if (tagName == 'audio') {
    
    				me.addEventListener('progress',function(e) {
    					console.log(e);
    				}, false);
    			
    			}
    			
    			me.addEventListener('progress',function(e) {
    				console.log(e);
    			}, false);
    	
    			
    			// add events
    			if (tagName == 'video' && node.id == 'player1') {
    				appendMediaProperties(jQuery('#' + tagName + '-props'), me);
    				appendMediaEvents(jQuery('#' + tagName + '-events'), me);
    				
    			}
    		}		
    	});
    	
    
    
    });
    
    </script>
    
    <?php
      return $returnstring;
    }


    //========================================================================================================
    
    
    
    public function append_comboboxes() {
    
    if ( RHSWP_CPT_RIJKSVIDEO_USE_CMB2 ) {
    
      add_action( 'cmb2_admin_init', 'rhswp_register_metabox_rijksvideo' );
    
      /**
       * Hook in and add a demo metabox. Can only happen on the 'cmb2_admin_init' or 'cmb2_init' hook.
       */
      function rhswp_register_metabox_rijksvideo() {
      
      	/**
      	 * Sample metabox to demonstrate each field type included
      	 */
      	$cmb_demo = new_cmb2_box( array(
      		'id'            => RHSWP_CPT_VIDEO_PREFIX . 'metabox',
      		'title'         => __( 'Metadata voor video', 'wp-rijkshuisstijl' ),
      		'object_types'  => array( RHSWP_CPT_RIJKSVIDEO ), // Post type
      	) );
    
      	$cmb_demo->add_field( array(
      		'name' => __( 'Lengte van de video', 'wp-rijkshuisstijl' ),
      		'desc' => __( 'formaat: uu:mm:ss', 'wp-rijkshuisstijl' ),
      		'id'   => RHSWP_CPT_VIDEO_PREFIX . 'video_time',
      		'type' => 'text_small',
      	) );
    
      	$cmb_demo->add_field( array(
      		'name' => __( 'URL van thumbnail', 'wp-rijkshuisstijl' ),
      		'desc' => __( 'URL van het bijbehorende plaatje', 'wp-rijkshuisstijl' ),
      		'id'   => RHSWP_CPT_VIDEO_PREFIX . 'url_video_thumb',
      		'type' => 'text_url',
      		'protocols' => array('http', 'https', '//'), // Array of allowed protocols
      	) );
    
      	$cmb_demo->add_field( array(
      		'name' => __( 'URL van audio-track', 'wp-rijkshuisstijl' ),
      		'desc' => __( 'Dit is meestal een bestand dat eindigt op .mp3', 'wp-rijkshuisstijl' ),
      		'id'   => RHSWP_CPT_VIDEO_PREFIX . 'audio_url',
      		'type' => 'text_url',
      		'protocols' => array('http', 'https', '//'), // Array of allowed protocols
      	) );
      
      	$cmb_demo->add_field( array(
      		'name' => __( 'URL van ondertitel', 'wp-rijkshuisstijl' ),
      		'desc' => __( 'Dit is meestal een bestand dat eindigt op .srt', 'wp-rijkshuisstijl' ),
      		'id'   => RHSWP_CPT_VIDEO_PREFIX . 'url_transcript_file',
      		'type' => 'text_url',
      		'protocols' => array('http', 'https', '//'), // Array of allowed protocols
      	) );
      
      	$cmb_demo->add_field( array(
      		'name' => __( 'Volledige transcriptie', 'wp-rijkshuisstijl' ),
      		'id'   => RHSWP_CPT_VIDEO_PREFIX . 'transcriptvlak',
      		'type' => 'textarea',
      	) );
    
      	$cmb_demo->add_field( array(
      		'name' => __( 'URL van FLV-bestand', 'wp-rijkshuisstijl' ),
      		'desc' => __( 'Eindigt op .flv', 'wp-rijkshuisstijl' ),
      		'id'   => RHSWP_CPT_VIDEO_PREFIX . 'flv_url',
      		'type' => 'text_url',
      		'protocols' => array('http', 'https', '//'), // Array of allowed protocols
      	) );
      
      	$cmb_demo->add_field( array(
      		'name' => __( 'Bestandsgrootte van FLV-bestand', 'wp-rijkshuisstijl' ),
      		'id'   => RHSWP_CPT_VIDEO_PREFIX . 'flv_filesize',
      		'type' => 'text_small',
      	) );
    
      	$cmb_demo->add_field( array(
      		'name' => __( 'URL van WMV-bestand', 'wp-rijkshuisstijl' ),
      		'desc' => __( 'Windows Media File. Eindigt vaak op .wmv', 'wp-rijkshuisstijl' ),
      		'id'   => RHSWP_CPT_VIDEO_PREFIX . 'wmv_url',
      		'type' => 'text_url',
      		'protocols' => array('http', 'https', '//'), // Array of allowed protocols
      	) );
      
      	$cmb_demo->add_field( array(
      		'name' => __( 'Bestandsgrootte van WMV-bestand', 'wp-rijkshuisstijl' ),
      		'id'   => RHSWP_CPT_VIDEO_PREFIX . 'filesize_wmv',
      		'type' => 'text_small',
      	) );
    
      	$cmb_demo->add_field( array(
      		'name' => __( 'URL van MP4-bestand', 'wp-rijkshuisstijl' ),
      		'desc' => __( 'Apple Quicktime-bestand. Eindigt vaak op .mp4', 'wp-rijkshuisstijl' ),
      		'id'   => RHSWP_CPT_VIDEO_PREFIX . 'mp4_url',
      		'type' => 'text_url',
      		'protocols' => array('http', 'https', '//'), // Array of allowed protocols
      	) );
      
      	$cmb_demo->add_field( array(
      		'name' => __( 'Bestandsgrootte van MP4-bestand', 'wp-rijkshuisstijl' ),
      		'id'   => RHSWP_CPT_VIDEO_PREFIX . 'mp4_filesize',
      		'type' => 'text_small',
      	) );
    
      	$cmb_demo->add_field( array(
      		'name' => __( 'URL van Hi-res MP4-bestand', 'wp-rijkshuisstijl' ),
      		'desc' => __( 'Versie van Quicktime-bestand in hoge resolutie.', 'wp-rijkshuisstijl' ),
      		'id'   => RHSWP_CPT_VIDEO_PREFIX . 'mp4_url_hires',
      		'type' => 'text_url',
      		'protocols' => array('http', 'https', '//'), // Array of allowed protocols
      	) );
      
      	$cmb_demo->add_field( array(
      		'name' => __( 'Bestandsgrootte van hi-res MP4-bestand', 'wp-rijkshuisstijl' ),
      		'id'   => RHSWP_CPT_VIDEO_PREFIX . 'mp4_filesize_hires',
      		'type' => 'text_small',
      	) );
    
      	$cmb_demo->add_field( array(
      		'name' => __( 'Url voor 3GP formaat', 'wp-rijkshuisstijl' ),
      		'id'   => RHSWP_CPT_VIDEO_PREFIX . '3gp_url',
      		'type' => 'text_url',
      		'protocols' => array('http', 'https', '//'), // Array of allowed protocols
      	) );
      
      	$cmb_demo->add_field( array(
      		'name' => __( 'Bestandsgrootte van 3GP-bestand', 'wp-rijkshuisstijl' ),
      		'id'   => RHSWP_CPT_VIDEO_PREFIX . '3gp_filesize',
      		'type' => 'text_small',
      	) );
      }
    
    
    }  // RHSWP_CPT_RIJKSVIDEO_USE_CMB2
    else {
      if( function_exists('acf_add_local_field_group') ):
      
      acf_add_local_field_group(array (
      	'key' => 'group_57ea177ac9849',
      	'title' => 'Metadata voor ' . RHSWP_CPT_RIJKSVIDEO,
      	'fields' => array (
      		array (
      			'key' => 'field_57ea1788c6162',
      			'label' => 'Lengte van de video',
      			'name' => RHSWP_CPT_VIDEO_PREFIX . 'video_time',
      			'type' => 'text',
      			'instructions' => '',
      			'required' => 0,
      			'conditional_logic' => 0,
      			'wrapper' => array (
      				'width' => '',
      				'class' => '',
      				'id' => '',
      			),
      			'default_value' => '',
      			'placeholder' => '',
      			'prepend' => '',
      			'append' => '',
      			'maxlength' => '',
      		),
      		array (
      			'key' => 'field_57ea17bd1c807',
      			'label' => 'Thumbnail URL',
      			'name' => RHSWP_CPT_VIDEO_PREFIX . 'url_video_thumb',
      			'type' => 'url',
      			'instructions' => '',
      			'required' => 0,
      			'conditional_logic' => 0,
      			'wrapper' => array (
      				'width' => '',
      				'class' => '',
      				'id' => '',
      			),
      			'default_value' => '',
      			'placeholder' => '',
      		),
      		array (
      			'key' => 'field_57ea17e81c808',
      			'label' => 'Ondertitel URL',
      			'name' => RHSWP_CPT_VIDEO_PREFIX . 'url_transcript_file',
      			'type' => 'url',
      			'instructions' => '',
      			'required' => 0,
      			'conditional_logic' => 0,
      			'wrapper' => array (
      				'width' => '',
      				'class' => '',
      				'id' => '',
      			),
      			'default_value' => '',
      			'placeholder' => '',
      		),
      		array (
      			'key' => 'field_57ea18001c809',
      			'label' => 'Transcript',
      			'name' => RHSWP_CPT_VIDEO_PREFIX . 'transcriptvlak',
      			'type' => 'textarea',
      			'instructions' => '',
      			'required' => 0,
      			'conditional_logic' => 0,
      			'wrapper' => array (
      				'width' => '',
      				'class' => '',
      				'id' => '',
      			),
      			'default_value' => '',
      			'placeholder' => '',
      			'maxlength' => '',
      			'rows' => '',
      			'new_lines' => 'wpautop',
      		),
      		array (
      			'key' => 'field_57ea18101c80a',
      			'label' => 'Audio URL',
      			'name' => RHSWP_CPT_VIDEO_PREFIX . 'audio_url',
      			'type' => 'url',
      			'instructions' => '',
      			'required' => 0,
      			'conditional_logic' => 0,
      			'wrapper' => array (
      				'width' => '',
      				'class' => '',
      				'id' => '',
      			),
      			'default_value' => '',
      			'placeholder' => '',
      		),
      		array (
      			'key' => 'field_57ea18211c80b',
      			'label' => 'Video (FLV) URL',
      			'name' => RHSWP_CPT_VIDEO_PREFIX . 'flv_url',
      			'type' => 'url',
      			'instructions' => '',
      			'required' => 0,
      			'conditional_logic' => 0,
      			'wrapper' => array (
      				'width' => '',
      				'class' => '',
      				'id' => '',
      			),
      			'default_value' => '',
      			'placeholder' => '',
      		),
      		array (
      			'key' => 'field_57ea18411c80c',
      			'label' => 'Bestandsgrootte (FLV)',
      			'name' => RHSWP_CPT_VIDEO_PREFIX . 'flv_filesize',
      			'type' => 'text',
      			'instructions' => '',
      			'required' => 0,
      			'conditional_logic' => 0,
      			'wrapper' => array (
      				'width' => '',
      				'class' => '',
      				'id' => '',
      			),
      			'default_value' => '',
      			'placeholder' => '',
      			'prepend' => '',
      			'append' => '',
      			'maxlength' => '',
      		),
      		array (
      			'key' => 'field_57ea18521c80d',
      			'label' => 'Video (WMV) URL',
      			'name' => RHSWP_CPT_VIDEO_PREFIX . 'wmv_url',
      			'type' => 'text',
      			'instructions' => '',
      			'required' => 0,
      			'conditional_logic' => 0,
      			'wrapper' => array (
      				'width' => '',
      				'class' => '',
      				'id' => '',
      			),
      			'default_value' => '',
      			'placeholder' => '',
      			'prepend' => '',
      			'append' => '',
      			'maxlength' => '',
      		),
      		array (
      			'key' => 'field_57ea189c1c80e',
      			'label' => 'Bestandsgrootte (WMV)',
      			'name' => RHSWP_CPT_VIDEO_PREFIX . 'filesize_wmv',
      			'type' => 'text',
      			'instructions' => '',
      			'required' => 0,
      			'conditional_logic' => 0,
      			'wrapper' => array (
      				'width' => '',
      				'class' => '',
      				'id' => '',
      			),
      			'default_value' => '',
      			'placeholder' => '',
      			'prepend' => '',
      			'append' => '',
      			'maxlength' => '',
      		),
      		array (
      			'key' => 'field_57ea18b11c80f',
      			'label' => 'Video (MP4) URL',
      			'name' => RHSWP_CPT_VIDEO_PREFIX . 'mp4_url',
      			'type' => 'url',
      			'instructions' => '',
      			'required' => 0,
      			'conditional_logic' => 0,
      			'wrapper' => array (
      				'width' => '',
      				'class' => '',
      				'id' => '',
      			),
      			'default_value' => '',
      			'placeholder' => '',
      		),
      		array (
      			'key' => 'field_57ea18d01c810',
      			'label' => 'Bestandsgrootte (MP4)',
      			'name' => RHSWP_CPT_VIDEO_PREFIX . 'mp4_filesize',
      			'type' => 'text',
      			'instructions' => '',
      			'required' => 0,
      			'conditional_logic' => 0,
      			'wrapper' => array (
      				'width' => '',
      				'class' => '',
      				'id' => '',
      			),
      			'default_value' => '',
      			'placeholder' => '',
      			'prepend' => '',
      			'append' => '',
      			'maxlength' => '',
      		),
      		array (
      			'key' => 'field_57ea18f793b47',
      			'label' => 'Video (MP4 High Resolution) URL',
      			'name' => RHSWP_CPT_VIDEO_PREFIX . 'mp4_url_hires',
      			'type' => 'url',
      			'instructions' => '',
      			'required' => 0,
      			'conditional_logic' => 0,
      			'wrapper' => array (
      				'width' => '',
      				'class' => '',
      				'id' => '',
      			),
      			'default_value' => '',
      			'placeholder' => '',
      		),
      		array (
      			'key' => 'field_57ea191793b48',
      			'label' => 'Bestandsgroote (MP4 High Resolution)',
      			'name' => RHSWP_CPT_VIDEO_PREFIX . 'mp4_filesize_hires',
      			'type' => 'text',
      			'instructions' => '',
      			'required' => 0,
      			'conditional_logic' => 0,
      			'wrapper' => array (
      				'width' => '',
      				'class' => '',
      				'id' => '',
      			),
      			'default_value' => '',
      			'placeholder' => '',
      			'prepend' => '',
      			'append' => '',
      			'maxlength' => '',
      		),
      		array (
      			'key' => 'field_57ea193193b49',
      			'label' => 'Video (3GP) URL',
      			'name' => RHSWP_CPT_VIDEO_PREFIX . '3gp_url',
      			'type' => 'url',
      			'instructions' => '',
      			'required' => 0,
      			'conditional_logic' => 0,
      			'wrapper' => array (
      				'width' => '',
      				'class' => '',
      				'id' => '',
      			),
      			'default_value' => '',
      			'placeholder' => '',
      		),
      		array (
      			'key' => 'field_57ea194493b4a',
      			'label' => 'Bestandsgrootte (3GP)',
      			'name' => RHSWP_CPT_VIDEO_PREFIX . '3gp_filesize',
      			'type' => 'text',
      			'instructions' => '',
      			'required' => 0,
      			'conditional_logic' => 0,
      			'wrapper' => array (
      				'width' => '',
      				'class' => '',
      				'id' => '',
      			),
      			'default_value' => '',
      			'placeholder' => '',
      			'prepend' => '',
      			'append' => '',
      			'maxlength' => '',
      		),
      	),
      	'location' => array (
      		array (
      			array (
      				'param' => 'post_type',
      				'operator' => '==',
      				'value' => RHSWP_CPT_RIJKSVIDEO,
      			),
      		),
      	),
      	'menu_order' => 0,
      	'position' => 'normal',
      	'style' => 'default',
      	'label_placement' => 'top',
      	'instruction_placement' => 'label',
      	'hide_on_screen' => '',
      	'active' => 1,
      	'description' => '',
      ));
      
      endif;  
    
    }  // else RHSWP_CPT_RIJKSVIDEO_USE_CMB2
}    

    //========================================================================================================
    

    /**
     * Outputs a blank page containing a slideshow preview (for use in the 'Preview' iFrame)
     */
    public function do_preview() {

        remove_action('wp_footer', 'wp_admin_bar_render', 1000);

        if ( isset( $_GET['slider_id'] ) && absint( $_GET['slider_id'] ) > 0 ) {
            $id = absint( $_GET['slider_id'] );

            ?>
            <!DOCTYPE html>
            <html>
                <head>
                    <style type='text/css'>
                        body, html {
                            overflow: hidden;
                            margin: 0;
                            padding: 0;
                        }
                    </style>
                    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
                    <meta http-equiv="Pragma" content="no-cache" />
                    <meta http-equiv="Expires" content="0" />
                </head>
                <body>
                    <?php echo do_shortcode("[" . RIJKSVIDEO_CPT . " id={$id}]"); ?>
                    <?php wp_footer(); ?>
                </body>
            </html>
            <?php
        }

        die();

    }


    /**
     * Check our WordPress installation is compatible with Rijksvideo
     */
    public function do_system_check() {

        $systemCheck = new MetaSliderSystemCheck();
        $systemCheck->check();

    }


    /**
     * Update the tab options in the media manager
     */
    public function custom_media_uploader_tabs( $strings ) {

        //update strings
        if ( ( isset( $_GET['page'] ) && $_GET['page'] == 'rijksvideo' ) ) {
            $strings['insertMediaTitle'] = __( "Image", "rhswp-rijksvideo" );
            $strings['insertIntoPost'] = __( "Add to slider", "rhswp-rijksvideo" );
            // remove options

            $strings_to_remove = array(
                'createVideoPlaylistTitle',
                'createGalleryTitle',
                'insertFromUrlTitle',
                'createPlaylistTitle'
            );

            foreach ($strings_to_remove as $string) {
                if (isset($strings[$string])) {
                    unset($strings[$string]);
                }
            }
        }

        return $strings;

    }


    /**
     * Add extra tabs to the default wordpress Media Manager iframe
     *
     * @var array existing media manager tabs
     */
    public function custom_media_upload_tab_name( $tabs ) {

        // restrict our tab changes to the meta slider plugin page
        if ( isset( $_GET['page'] ) && $_GET['page'] == 'rijksvideo' ) {

            if ( isset( $tabs['nextgen'] ) ) {
                unset( $tabs['nextgen'] );
            }

        }

        return $tabs;

    }


    /**
     * Set the current slider
     */
    public function set_slider( $id, $shortcode_settings = array() ) {

        $type = 'flex';

        if ( isset( $shortcode_settings['type'] ) ) {
            $type = $shortcode_settings['type'];
        } else if ( $settings = get_post_meta( $id, 'rijksvideo_settings', true ) ) {
            if ( is_array( $settings ) && isset( $settings['type'] ) ) {
                $type = $settings['type'];
            }
        }

        if ( ! in_array( $type, array( 'flex', 'coin', 'nivo', 'responsive' ) ) ) {
            $type = 'flex';
        }

        $this->slider = $this->load_slider( $type, $id, $shortcode_settings );

    }


    /**
     * Create a new slider based on the sliders type setting
     */
    private function load_slider( $type, $id, $shortcode_settings ) {

        switch ( $type ) {
            case( 'coin' ):
                return new MetaCoinSlider( $id, $shortcode_settings );
            case( 'flex' ):
                return new MetaFlexSlider( $id, $shortcode_settings );
            case( 'nivo' ):
                return new MetaNivoSlider( $id, $shortcode_settings );
            case( 'responsive' ):
                return new MetaResponsiveSlider( $id, $shortcode_settings );
            default:
                return new MetaFlexSlider( $id, $shortcode_settings );

        }
    }


    /**
     *
     */
    public function update_slider() {

        check_admin_referer( "metaslider_update_slider" );

        $capability = apply_filters( 'metaslider_capability', 'edit_others_posts' );

        if ( ! current_user_can( $capability ) ) {
            return;
        }

        $slider_id = absint( $_POST['slider_id'] );

        if ( ! $slider_id ) {
            return;
        }

        // update settings
        if ( isset( $_POST['settings'] ) ) {

            $new_settings = $_POST['settings'];

            $old_settings = get_post_meta( $slider_id, 'rijksvideo_settings', true );

            // convert submitted checkbox values from 'on' or 'off' to boolean values
            $checkboxes = apply_filters( "metaslider_checkbox_settings", array( 'noConflict', 'fullWidth', 'hoverPause', 'links', 'reverse', 'random', 'printCss', 'printJs', 'smoothHeight', 'center', 'carouselMode', 'autoPlay' ) );

            foreach ( $checkboxes as $checkbox ) {
                if ( isset( $new_settings[$checkbox] ) && $new_settings[$checkbox] == 'on' ) {
                    $new_settings[$checkbox] = "true";
                } else {
                    $new_settings[$checkbox] = "false";
                }
            }

            $settings = array_merge( (array)$old_settings, $new_settings );

            // update the slider settings
            update_post_meta( $slider_id, 'rijksvideo_settings', $settings );

        }

        // update slideshow title
        if ( isset( $_POST['title'] ) ) {

            $slide = array(
                'ID' => $slider_id,
                'post_title' => esc_html( $_POST['title'] )
            );

            wp_update_post( $slide );

        }

        // update individual slides
        if ( isset( $_POST['attachment'] ) ) {

            foreach ( $_POST['attachment'] as $slide_id => $fields ) {
                do_action( "metaslider_save_{$fields['type']}_slide", $slide_id, $slider_id, $fields );
            }

        }

    }


    /**
     * Delete a slide. This doesn't actually remove the slide from WordPress, simply untags
     * it from the slide taxonomy.
     */
    public function delete_slide() {

        // check nonce
        check_admin_referer( "metaslider_delete_slide" );

        $capability = apply_filters( 'metaslider_capability', 'edit_others_posts' );

        if ( ! current_user_can( $capability ) ) {
            return;
        }

        $slide_id = absint( $_GET['slide_id'] );
        $slider_id = absint( $_GET['slider_id'] );

        // Get the existing terms and only keep the ones we don't want removed
        $new_terms = array();
        $current_terms = wp_get_object_terms( $slide_id, RIJKSVIDEO_CT, array( 'fields' => 'ids' ) );
        $term = get_term_by( 'name', $slider_id, RIJKSVIDEO_CT );

        foreach ( $current_terms as $current_term ) {
            if ( $current_term != $term->term_id ) {
                $new_terms[] = absint( $current_term );
            }
        }

        wp_set_object_terms( $slide_id, $new_terms, "rhswp-rijksvideo" );

        wp_redirect( admin_url( "admin.php?page=metaslider&id={$slider_id}" ) );

    }


    /**
     * Delete a slider (send it to trash)
     */
    public function delete_slider() {

        // check nonce
        check_admin_referer( "metaslider_delete_slider" );

        $capability = apply_filters( 'metaslider_capability', 'edit_others_posts' );

        if ( ! current_user_can( $capability ) ) {
            return;
        }

        $slider_id = absint( $_GET['slider_id'] );

        // send the post to trash
        $id = wp_update_post( array(
                'ID' => $slider_id,
                'post_status' => 'trash'
            )
        );

        $slider_id = $this->find_slider( 'modified', 'DESC' );

        wp_redirect( admin_url( "admin.php?page=metaslider&id={$slider_id}" ) );

    }


    /**
     *
     */
    public function switch_view() {
        global $user_ID;

        $view = $_GET['view'];

        $allowed_views = array('tabs', 'dropdown');

        if ( ! in_array( $view, $allowed_views ) ) {
            return;
        }

        delete_user_meta( $user_ID, "metaslider_view" );

        if ( $view == 'dropdown' ) {
            add_user_meta( $user_ID, "metaslider_view", "dropdown");
        }

        wp_redirect( admin_url( "admin.php?page=metaslider" ) );

    }


    /**
     * Create a new slider
     */
    public function create_slider() {

        // check nonce
        check_admin_referer( "metaslider_create_slider" );

        $capability = apply_filters( 'metaslider_capability', 'edit_others_posts' );

        if ( ! current_user_can( $capability ) ) {
            return;
        }

        $defaults = array();

        // if possible, take a copy of the last edited slider settings in place of default settings
        if ( $last_modified = $this->find_slider( 'modified', 'DESC' ) ) {
            $defaults = get_post_meta( $last_modified, 'rijksvideo_settings', true );
        }

        // insert the post
        $id = wp_insert_post( array(
                'post_title' => __( "New Slider", "rhswp-rijksvideo" ),
                'post_status' => 'publish',
                'post_type' => RIJKSVIDEO_CPT
            )
        );

        // use the default settings if we can't find anything more suitable.
        if ( empty( $defaults ) ) {
            $slider = new MetaSlider( $id, array() );
            $defaults = $slider->get_default_parameters();
        }

        // insert the post meta
        add_post_meta( $id, 'rijksvideo_settings', $defaults, true );

        // create the taxonomy term, the term is the ID of the slider itself
        wp_insert_term( $id, RIJKSVIDEO_CT );

        wp_redirect( admin_url( "admin.php?page=metaslider&id={$id}" ) );

    }



    /**
     * Find a single slider ID. For example, last edited, or first published.
     *
     * @param string $orderby field to order.
     * @param string $order direction (ASC or DESC).
     * @return int slider ID.
     */
    private function find_slider( $orderby, $order ) {

        $args = array(
            'force_no_custom_order' => true,
            'post_type' => RIJKSVIDEO_CPT,
            'num_posts' => 1,
            'post_status' => 'publish',
            'suppress_filters' => 1, // wpml, ignore language filter
            'orderby' => $orderby,
            'order' => $order
        );

        $the_query = new WP_Query( $args );

        while ( $the_query->have_posts() ) {
            $the_query->the_post();
            return $the_query->post->ID;
        }

        wp_reset_query();

        return false;

    }


    /**
     * Get sliders. Returns a nicely formatted array of currently
     * published sliders.
     *
     * @param string $sort_key
     * @return array all published sliders
     */
    public function all_meta_sliders( $sort_key = 'date' ) {

echo 'all_meta_sliders<br>';

        $sliders = array();

        // list the tabs
        $args = array(
            'post_type'         => RIJKSVIDEO_CPT,
            'post_status'       => 'publish',
            'orderby'           => $sort_key,
            'suppress_filters'  => 1, // wpml, ignore language filter
            'order'             => 'ASC',
            'posts_per_page'    => -1
        );

        // list the tabs
        $args = array(
            'post_type'         => RIJKSVIDEO_CPT,
            'post_status'       => 'publish',
            'order'             => 'ASC',
            'posts_per_page'    => -1
        );

        $args = apply_filters( 'metaslider_all_meta_sliders_args', $args );

        // WP_Query causes issues with other plugins using admin_footer to insert scripts
        // use get_posts instead
        $all_sliders = get_posts( $args );

echo '$all_sliders: ' . count( $all_sliders ) . '<br>';


        foreach( $all_sliders as $slideshow ) {

echo 'sliders: ' . $slideshow->ID . '<br>';

            $active = $this->slider && ( $this->slider->id == $slideshow->ID ) ? true : false;

            $sliders[] = array(
                'active' => $active,
                'title' => $slideshow->post_title,
                'id' => $slideshow->ID
            );

        }

        return $sliders;

    }


    /**
     * Compare array values
     *
     * @param array $elem1
     * @param array $elem2
     * @return bool
     */
    private function compare_elems( $elem1, $elem2 ) {

        return $elem1['priority'] > $elem2['priority'];

    }


    /**
     *
     * @param array $aFields - array of field to render
     * @return string
     */
    public function build_settings_rows( $aFields ) {

        // order the fields by priority
        uasort( $aFields, array( $this, "compare_elems" ) );

        $return = "";

        // loop through the array and build the settings HTML
        foreach ( $aFields as $id => $row ) {
            // checkbox input type
            if ( $row['type'] == 'checkbox' ) {
                $return .= "<tr><td class='tipsy-tooltip' title=\"{$row['helptext']}\">{$row['label']}</td><td><input class='option {$row['class']} {$id}' type='checkbox' name='settings[{$id}]' {$row['checked']} />";

                if ( isset( $row['after'] ) ) {
                    $return .= "<span class='after'>{$row['after']}</span>";
                }

                $return .= "</td></tr>";
            }

            // navigation row
            if ( $row['type'] == 'navigation' ) {
                $navigation_row = "<tr class='{$row['type']}'><td class='tipsy-tooltip' title=\"{$row['helptext']}\">{$row['label']}</td><td><ul>";

                foreach ( $row['options'] as $k => $v ) {

                    if ( $row['value'] === true && $k === 'true' ) {
                        $checked = checked( true, true, false );
                    } else if ( $row['value'] === false && $k === 'false' ) {
                        $checked = checked( true, true, false );
                    } else {
                        $checked = checked( $k, $row['value'], false );
                    }

                    $disabled = $k == 'thumbnails' ? 'disabled' : '';
                    $navigation_row .= "<li><label><input type='radio' name='settings[{$id}]' value='{$k}' {$checked} {$disabled}/>{$v['label']}</label></li>";
                }

                $navigation_row .= "</ul></td></tr>";

                $return .= apply_filters( 'metaslider_navigation_options', $navigation_row, $this->slider );
            }

            // navigation row
            if ( $row['type'] == 'radio' ) {
                $navigation_row = "<tr class='{$row['type']}'><td class='tipsy-tooltip' title=\"{$row['helptext']}\">{$row['label']}</td><td><ul>";

                foreach ( $row['options'] as $k => $v ) {
                    $checked = checked( $k, $row['value'], false );
                    $class = isset( $v['class'] ) ? $v['class'] : "";
                    $navigation_row .= "<li><label><input type='radio' name='settings[{$id}]' value='{$k}' {$checked} class='radio {$class}'/>{$v['label']}</label></li>";
                }

                $navigation_row .= "</ul></td></tr>";

                $return .= apply_filters( 'metaslider_navigation_options', $navigation_row, $this->slider );
            }

            // header/divider row
            if ( $row['type'] == 'divider' ) {
                $return .= "<tr class='{$row['type']}'><td colspan='2' class='divider'><b>{$row['value']}</b></td></tr>";
            }

            // slideshow select row
            if ( $row['type'] == 'slider-lib' ) {
                $return .= "<tr class='{$row['type']}'><td colspan='2' class='slider-lib-row'>";

                foreach ( $row['options'] as $k => $v ) {
                    $checked = checked( $k, $row['value'], false );
                    $return .= "<input class='select-slider' id='{$k}' rel='{$k}' type='radio' name='settings[type]' value='{$k}' {$checked} />
                    <label for='{$k}'>{$v['label']}</label>";
                }

                $return .= "</td></tr>";
            }

            // number input type
            if ( $row['type'] == 'number' ) {
                $return .= "<tr class='{$row['type']}'><td class='tipsy-tooltip' title=\"{$row['helptext']}\">{$row['label']}</td><td><input class='option {$row['class']} {$id}' type='number' min='{$row['min']}' max='{$row['max']}' step='{$row['step']}' name='settings[{$id}]' value='" . absint( $row['value'] ) . "' /><span class='after'>{$row['after']}</span></td></tr>";
            }

            // select drop down
            if ( $row['type'] == 'select' ) {
                $return .= "<tr class='{$row['type']}'><td class='tipsy-tooltip' title=\"{$row['helptext']}\">{$row['label']}</td><td><select class='option {$row['class']} {$id}' name='settings[{$id}]'>";
                foreach ( $row['options'] as $k => $v ) {
                    $selected = selected( $k, $row['value'], false );
                    $return .= "<option class='{$v['class']}' value='{$k}' {$selected}>{$v['label']}</option>";
                }
                $return .= "</select></td></tr>";
            }

            // theme drop down
            if ( $row['type'] == 'theme' ) {
                $return .= "<tr class='{$row['type']}'><td class='tipsy-tooltip' title=\"{$row['helptext']}\">{$row['label']}</td><td><select class='option {$row['class']} {$id}' name='settings[{$id}]'>";
                $themes = "";

                foreach ( $row['options'] as $k => $v ) {
                    $selected = selected( $k, $row['value'], false );
                    $themes .= "<option class='{$v['class']}' value='{$k}' {$selected}>{$v['label']}</option>";
                }

                $return .= apply_filters( 'metaslider_get_available_themes', $themes, $this->slider->get_setting( 'theme' ) );

                $return .= "</select></td></tr>";
            }

            // text input type
            if ( $row['type'] == 'text' ) {
                $return .= "<tr class='{$row['type']}'><td class='tipsy-tooltip' title=\"{$row['helptext']}\">{$row['label']}</td><td><input class='option {$row['class']} {$id}' type='text' name='settings[{$id}]' value='" . esc_attr( $row['value'] ) . "' /></td></tr>";
            }

            // text input type
            if ( $row['type'] == 'textarea' ) {
                $return .= "<tr class='{$row['type']}'><td class='tipsy-tooltip' title=\"{$row['helptext']}\" colspan='2'>{$row['label']}</td></tr><tr><td colspan='2'><textarea class='option {$row['class']} {$id}' name='settings[{$id}]' />{$row['value']}</textarea></td></tr>";
            }

            // text input type
            if ( $row['type'] == 'title' ) {
                $return .= "<tr class='{$row['type']}'><td class='tipsy-tooltip' title=\"{$row['helptext']}\">{$row['label']}</td><td><input class='option {$row['class']} {$id}' type='text' name='{$id}' value='" . esc_attr( $row['value'] ) . "' /></td></tr>";
            }
        }

        return $return;

    }


    /**
     * Return an indexed array of all easing options
     *
     * @return array
     */
    private function get_easing_options() {

        $options = array(
            'linear', 'swing', 'jswing', 'easeInQuad', 'easeOutQuad', 'easeInOutQuad',
            'easeInCubic', 'easeOutCubic', 'easeInOutCubic', 'easeInQuart',
            'easeOutQuart', 'easeInOutQuart', 'easeInQuint', 'easeOutQuint',
            'easeInOutQuint', 'easeInSine', 'easeOutSine', 'easeInOutSine',
            'easeInExpo', 'easeOutExpo', 'easeInOutExpo', 'easeInCirc', 'easeOutCirc',
            'easeInOutCirc', 'easeInElastic', 'easeOutElastic', 'easeInOutElastic',
            'easeInBack', 'easeOutBack', 'easeInOutBack', 'easeInBounce', 'easeOutBounce',
            'easeInOutBounce'
        );

        foreach ( $options as $option ) {
            $return[$option] = array(
                'label' => ucfirst( preg_replace( '/(\w+)([A-Z])/U', '\\1 \\2', $option ) ),
                'class' => ''
            );
        }

        return $return;

    }

    /**
     * Output the slideshow selector.
     *
     * Show tabs or a dropdown list depending on the users saved preference.
     */
    public function print_slideshow_selector() {
        global $user_ID;

        $add_url = wp_nonce_url( admin_url( "admin-post.php?action=metaslider_create_slider" ), "metaslider_create_slider" );

        if ( $tabs = $this->all_meta_sliders() ) {

            if ( $this->get_view() == 'tabs' ) {

                echo "<div style='display: none;' id='screen-options-switch-view-wrap'>
                <a class='switchview dashicons-before dashicons-randomize tipsy-tooltip' title='" . __("Switch to Dropdown view", "ml-slider") . "' href='" . admin_url( "admin-post.php?action=metaslider_switch_view&view=dropdown") . "'>" . __("Dropdown", "ml-slider") . "</a></div>";

                echo "<h3 class='nav-tab-wrapper'>";

                foreach ( $tabs as $tab ) {

                    if ( $tab['active'] ) {
                        echo "<div class='nav-tab nav-tab-active'><input type='text' name='title'  value='" . esc_attr( $tab['title'] ) . "' onfocus='this.style.width = ((this.value.length + 1) * 9) + \"px\"' /></div>";
                    } else {
                        echo "<a href='?page=metaslider&amp;id={$tab['id']}' class='nav-tab'>" . esc_html( $tab['title'] ) . "</a>";
                    }

                }

                echo "<a href='{$add_url}' id='create_new_tab' class='nav-tab'>+</a>";
                echo "</h3>";

            } else {

                if ( isset( $_GET['add'] ) && $_GET['add'] == 'true' ) {

                    echo "<div id='message' class='updated'><p>" . __( "New slideshow created. Click 'Add Slide' to get started!", "rhswp-rijksvideo" ) . "</p></div>";

                }

                echo "<div style='display: none;' id='screen-options-switch-view-wrap'><a class='switchview dashicons-before dashicons-randomize tipsy-tooltip' title='" . __("Switch to Tab view", "ml-slider") . "' href='" . admin_url( "admin-post.php?action=metaslider_switch_view&view=tabs") . "'>" . __("Tabs", "ml-slider") . "</a></div>";

                echo "<div class='dropdown_container'><label for='select-slider'>" . __("Select Slider", "ml-slider") . ": </label>";
                echo "<select name='select-slider' onchange='if (this.value) window.location.href=this.value'>";

                $tabs = $this->all_meta_sliders( 'title' );

                foreach ( $tabs as $tab ) {

                    $selected = $tab['active'] ? " selected" : "";

                    if ( $tab['active'] ) {

                        $title = $tab['title'];

                    }

                    echo "<option value='?page=metaslider&amp;id={$tab['id']}'{$selected}>{$tab['title']}</option>";

                }

                echo "</select> " . __( 'or', "rhswp-rijksvideo" ) . " ";
                echo "<a href='{$add_url}'>" . __( 'Add New Slideshow', "rhswp-rijksvideo" ) . "</a></div>";

            }
        } else {
            echo "<h3 class='nav-tab-wrapper'>";
            echo "<a href='{$add_url}' id='create_new_tab' class='nav-tab'>+</a>";
            echo "<div class='bubble'>" . __( "Create your first slideshow", "rhswp-rijksvideo" ) . "</div>";
            echo "</h3>";
        }
    }


    /**
     * Return the users saved view preference.
     */
    public function get_view() {
        global $user_ID;

        if ( get_user_meta( $user_ID, "metaslider_view", true ) ) {
            return get_user_meta( $user_ID, "metaslider_view", true );
        }

        return 'tabs';
    }


    


    /**
     * Append the 'Add video' button to selected admin pages
     */
    public function insert_metaslider_button( $context ) {

        $capability = apply_filters( 'metaslider_capability', 'edit_others_posts' );

        if ( ! current_user_can( $capability ) ) {
            return $context;
        }

        global $pagenow;
        $posttype = get_post_type( $_GET['post'] );

        if ( ( in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) ) && ( in_array( $posttype, array( 'post',  'page' ) ) ) ) {
            $context .= '<a href="#TB_inline?&inlineId=choose-meta-slider" class="thickbox button" title="' .
                __( "Selecteer een rijksvideo om in dit bericht in te voegen.", "rhswp-rijksvideo" ) .
                '"><span class="wp-media-buttons-icon" style="background: url(' . RIJKSVIDEO_ASSETS_URL . 'images/icon-video.png); background-repeat: no-repeat; background-size: 16px 16px; background-position: center center;"></span> ' .
                __( "Voeg rijksvideo in", "rhswp-rijksvideo" ) . '</a>';
        }

        return $context;

    }


    /**
     * Append the 'Choose Rijksvideo' thickbox content to the bottom of selected admin pages
     */
    public function admin_footer() {

        global $pagenow;

        // Only run in post/page creation and edit screens
        if ( in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) ) {
            $sliders = $this->all_meta_sliders( 'title' );
            ?>

            <script type="text/javascript">
                jQuery(document).ready(function() {
                  jQuery('#insertMetaSlider').on('click', function() {
                    var id = jQuery('#metaslider-select option:selected').val();
                    window.send_to_editor('[<?php echo RIJKSVIDEO_CPT ?> id=' + id + ']');
                    tb_remove();
                  })
                });
            </script>

            <div id="choose-meta-slider" style="display: none;">
                <div class="wrap">
                    <?php

                        if ( count( $sliders ) ) {
                            echo "<h3>" . __( "Insert Rijksvideo", "rhswp-rijksvideo" ) . "</h3>";
                            echo "<select id='metaslider-select'>";
                            echo "<option disabled=disabled>" . __( "Choose slideshow", "rhswp-rijksvideo" ) . "</option>";
                            foreach ( $sliders as $slider ) {
                                echo "<option value='{$slider['id']}'>{$slider['title']}</option>";
                            }
                            echo "</select>";
                            echo "<button class='button primary' id='insertMetaSlider'>" . __( "Insert slideshow", "rhswp-rijksvideo" ) . "</button>";
                        } else {
                            _e( "No videos found", "rhswp-rijksvideo" );
                        }
                    ?>
                </div>
            </div>

            <?php
        }
    }




    /**
     * Add a 'property=stylesheet' attribute to the Rijksvideo CSS links for HTML5 validation
     *
     * @since 3.3.4
     * @param string $tag
     * @param string $handle
     */
    public function add_property_attribute_to_stylesheet_links( $tag, $handle ) {

        if ( strpos( $handle, 'rijksvideo' ) !== FALSE && strpos( $tag, "property='" ) === FALSE ) {
            // we're only filtering tags with metaslider in the handle, and links which don't already have a property attribute
            $tag = str_replace( "/>", "property='stylesheet' />", $tag );
        }

        return $tag;

    }

}

endif;

add_action( 'plugins_loaded', array( 'RijksvideoPlugin_v1', 'init' ), 10 );