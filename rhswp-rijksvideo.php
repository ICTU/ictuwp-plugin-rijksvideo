<?php
/*
 * Rijksvideo. 
 *
 * Plugin Name: Rijksvideo
 * Plugin URI:  https://wbvb.nl/plugins/rhswp-rijksvideo/
 * Description: De mogelijkheid om video's in te voegen met diverse media-formats en ondertitels
 * Version:     1.0.0
 * Author:      Paul van Buuren
 * Author URI:  https://wbvb.nl
 * License:     GPL-2.0+
 *
 * Text Domain: rijksvideo-translate
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
     * @var Rijksvideo
     */
    public $rijksvideo = null;


    /**
     * Init
     */
    public static function init() {

        $rijksvideo_this = new self();

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
        $this->append_comboboxes();


    }


    /**
     * Define Rijksvideo constants
     */
    private function define_constants() {

    $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,strpos( $_SERVER["SERVER_PROTOCOL"],'/'))).'://';

      define( 'RIJKSVIDEO_VERSION',    $this->version );
      define( 'RIJKSVIDEO_FOLDER',     'rhswp-rijksvideo' );
      define( 'RIJKSVIDEO_BASE_URL',   trailingslashit( plugins_url( RIJKSVIDEO_FOLDER ) ) );
      define( 'RIJKSVIDEO_ASSETS_URL', trailingslashit( RIJKSVIDEO_BASE_URL . 'assets' ) );
      define( 'RIJKSVIDEO_PATH',       plugin_dir_path( __FILE__ ) );
      define( 'RIJKSVIDEO_CPT',        "rijksvideo_old" );
      define( 'RIJKSVIDEO_CT',         "rijksvideo_custom_taxonomy" );
      
      define( 'RHSWP_CPT_RIJKSVIDEO', 'rijksvideo_old' ); // slug for custom post type 'rijksvideo'
      define( 'RHSWP_CPT_VIDEO_PREFIX', RHSWP_CPT_RIJKSVIDEO . '_xx_' ); // prefix for rijksvideo metadata fields
      define( 'RHSWP_CPT_RIJKSVIDEO_SHOW_DEBUG', true );
      define( 'RHSWP_CPT_RIJKSVIDEO_USE_CMB2', true ); 
      define( 'RHSWP_DEFAULT_EXAMPLES', RIJKSVIDEO_ASSETS_URL . 'examples/' ); // slug for custom post type 'rijksvideo'

    }


    /**
     * All Rijksvideo classes
     */
    private function plugin_classes() {

        return array(
            'RijksvideoSystemCheck'  => RIJKSVIDEO_PATH . 'inc/rijksvideo.systemcheck.class.php',
        );

    }


    /**
     * Load required classes
     */
    private function includes() {
    
      // load CMB2 functionality
      if ( ! defined( 'CMB2_LOADED' ) ) {
        // cmb2 NOT loaded
        if ( file_exists( dirname( __FILE__ ) . '/cmb2/init.php' ) ) {
          require_once dirname( __FILE__ ) . '/cmb2/init.php';
        }
        elseif ( file_exists( dirname( __FILE__ ) . '/CMB2/init.php' ) ) {
          require_once dirname( __FILE__ ) . '/CMB2/init.php';
        }
      }
      
      
      
      $autoload_is_disabled = defined( 'RIJKSVIDEO_AUTOLOAD_CLASSES' ) && RIJKSVIDEO_AUTOLOAD_CLASSES === false;
      
      if ( function_exists( "spl_autoload_register" ) && ! ( $autoload_is_disabled ) ) {
        
        // >= PHP 5.2 - Use auto loading
        if ( function_exists( "__autoload" ) ) {
          spl_autoload_register( "__autoload" );
        }
        spl_autoload_register( array( $this, 'autoload' ) );
        
      } 
      else {
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
     * Register the [rijksvideo] shortcode.
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
        add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
        add_action( 'admin_footer', array( $this, 'admin_footer' ), 11 );

        add_action( 'wp_enqueue_scripts', 'rhswp_frontend_css' );

    }


    /**
     * Hook Rijksvideo into WordPress
     */
    private function setup_filters() {

        add_filter( 'media_buttons_context', array( $this, 'insert_rijksvideo_button' ) );

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
     * Shortcode used to display video
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
        $rijksvideo = get_post( $id );

        // check the slider is published and the ID is correct
        if ( ! $rijksvideo || $rijksvideo->post_status != 'publish' || $rijksvideo->post_type != RIJKSVIDEO_CPT ) {
            return "<!-- meta slider {$atts['id']} not found -->";
        }

        // lets go
        //$this->set_video_attributes( $id, $atts );
        //$this->enqueue_scripts();
        //$this->render_video_html( );
        
        return $this->rhswp_makevideo($id);

    }


    /**
     * Initialise translations
     */
    public function load_plugin_textdomain() {

        load_plugin_textdomain( "rijksvideo-translate", false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

    }


    /**
     * Add the help tab to the screen.
     */
    public function help_tab() {

        $screen = get_current_screen();

        // documentation tab
        $screen->add_help_tab( array(
                'id'      => 'documentation',
                'title'   => __( 'Documentatie', "rijksvideo-translate" ),
                'content' => "<p><a href='https://wbvb.nl/plugins/rhswp-rijksvideo/documentation/' target='blank'>" . __( 'Rijksvideo documentatie', "rijksvideo-translate" ) . "</a></p>",
            )
        );

    }


    /**
     * Register frontend styles
     */
    public function rhswp_frontend_css() {
      wp_enqueue_style( 'rhswp-frontend', RIJKSVIDEO_ASSETS_URL . 'css/rijksvideo.css', array(), RIJKSVIDEO_VERSION );
    }
    




    /**
     * Register admin-side styles
     */
    public function register_admin_styles() {

        wp_enqueue_style( 'rijksvideo-admin-styles', RIJKSVIDEO_ASSETS_URL . 'css/admin.css', false, RIJKSVIDEO_VERSION );

        do_action( 'rijksvideo_register_admin_styles' );

    }


    /**
     * Register admin JavaScript
     */
    public function register_admin_scripts() {

        // media library dependencies
        wp_enqueue_media();

        // plugin dependencies
        wp_enqueue_script( 'jquery-ui-core', array( 'jquery' ) );
//        wp_enqueue_script( 'jquery-ui-sortable', array( 'jquery', 'jquery-ui-core' ) );

        wp_dequeue_script( 'link' ); // WP Posts Filter Fix (Advanced Settings not toggling)
        wp_dequeue_script( 'ai1ec_requirejs' ); // All In One Events Calendar Fix (Advanced Settings not toggling)

        $this->localize_admin_scripts();

        do_action( 'rijksvideo_register_admin_scripts' );

    }


    /**
     * Localise admin script
     */
    public function localize_admin_scripts() {

        wp_localize_script( 'rijksvideo-admin-script', 'rijksvideo', array(
                'url'               => __( "URL", "rijksvideo-translate" ),
                'caption'           => __( "Caption", "rijksvideo-translate" ),
                'new_window'        => __( "New Window", "rijksvideo-translate" ),
                'confirm'           => __( "Weet je het zeker?", "rijksvideo-translate" ),
                'ajaxurl'           => admin_url( 'admin-ajax.php' ),
                'resize_nonce'      => wp_create_nonce( 'rijksvideo_resize' ),
                'addslide_nonce'    => wp_create_nonce( 'rijksvideo_addslide' ),
                'changeslide_nonce' => wp_create_nonce( 'rijksvideo_changeslide' ),
                'iframeurl'         => admin_url( 'admin-post.php?action=rijksvideo_preview' ),
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
          
          $videoplayer_title              = _x( 'Video Player', 'Rijksvideo', "rijksvideo-translate" );
          $videoplayer_video_txt          = _x( 'Video', 'Rijksvideo', "rijksvideo-translate" );
          $videoplayer_audio_txt          = _x( 'Audio', 'Rijksvideo', "rijksvideo-translate" );
          $videoplayer_subtitle_txt       = _x( 'Caption', 'Rijksvideo', "rijksvideo-translate" );
          $videoplayer_subtitles          = _x( 'Ondertitels', 'Rijksvideo', "rijksvideo-translate" );
          $videoplayer_subtitles_language = _x( 'nl', 'Rijksvideo: voor welke taal heb je ondertitels geupload?', "rijksvideo-translate" );
          
          
          $videoplayer_play               = _x( 'Afspelen', 'Rijksvideo', "rijksvideo-translate" );
          $videoplayer_mute_label         = _x( 'Geluid uit', 'Rijksvideo', "rijksvideo-translate" );
          $videoplayer_fullscreen_open    = _x( 'Schermvullende weergave openen', 'Rijksvideo', "rijksvideo-translate" );
          $videoplayer_fullscreen_close   = _x( 'Schermvullende weergave sluiten', 'Rijksvideo', "rijksvideo-translate" );
          $videoplayer_volume_label       = _x( 'Gebruik op- of neer-pijltjestoetsen om het volume harder of zachter te zetten', 'Rijksvideo', "rijksvideo-translate" );
          $videoplayer_subtitle_on        = _x( 'Ondertiteling aan', 'Rijksvideo', "rijksvideo-translate" );
          $videoplayer_download_label     = _x( 'Download deze video', 'Rijksvideo', "rijksvideo-translate" );
          $videoplayer_noplugin_label     = _x( 'Helaas kan deze video niet worden afgespeeld. Een mogelijk oplossing is de meest recente versie van uw browser te installeren.', 'Rijksvideo', "rijksvideo-translate" );
          
          $videoplayer_quicktime_label    = _x( 'Video voor Apple Quicktime Player', 'Rijksvideo', "rijksvideo-translate" );
          $videoplayer_quicktime_abbr     = _x( 'mp4', 'Rijksvideo', "rijksvideo-translate" );
          $videoplayer_quicktime_hr       = _x( ' (hoge resolutie)', 'Rijksvideo', "rijksvideo-translate" );
          
          $videoplayer_wmv_label          = _x( 'Video voor Windows Media Player', 'Rijksvideo', "rijksvideo-translate" );
          $videoplayer_wmv_abbr           = _x( 'wmv', 'Rijksvideo', "rijksvideo-translate" );
          
          $videoplayer_mobileformat_label = _x( 'Video voor mobiel gebruik', 'Rijksvideo', "rijksvideo-translate" );
          $videoplayer_mobileformat_abbr  = _x( '3gp', 'Rijksvideo', "rijksvideo-translate" );
          
          $videoplayer_audioformat_label  = _x( 'Audiospoor', 'Rijksvideo', "rijksvideo-translate" );
          $videoplayer_audioformat_abbr   = _x( 'mp3', 'Rijksvideo', "rijksvideo-translate" );
          
          $videoplayer_subtitle_label     = _x( 'Ondertitelingsbestand', 'Rijksvideo', "rijksvideo-translate" );
          $videoplayer_subtitle_abbr      = _x( 'srt', 'Rijksvideo', "rijksvideo-translate" );
          
    
    
          $rhswp_video_duur               = $this->get_stored_values( $postid, RHSWP_CPT_VIDEO_PREFIX . 'video_time', '-' );
          $rhswp_video_url_video_thumb    = $this->get_stored_values( $postid, RHSWP_CPT_VIDEO_PREFIX . 'url_video_thumb', RHSWP_DEFAULT_EXAMPLES . 'examples.jpg' );
          $rhswp_video_url_transcript     = $this->get_stored_values( $postid, RHSWP_CPT_VIDEO_PREFIX . 'url_transcript_file', RHSWP_DEFAULT_EXAMPLES . 'examples.srt' );
          $rhswp_video_transcriptvlak     = $this->get_stored_values( $postid, RHSWP_CPT_VIDEO_PREFIX . 'transcriptvlak', '' );
          $rhswp_video_audio_url          = $this->get_stored_values( $postid, RHSWP_CPT_VIDEO_PREFIX . 'audio_url', RHSWP_DEFAULT_EXAMPLES . 'examples.mp3' );
          $rhswp_video_flv_url            = $this->get_stored_values( $postid, RHSWP_CPT_VIDEO_PREFIX . 'flv_url', RHSWP_DEFAULT_EXAMPLES . 'examples.flv' );
          $rhswp_video_flv_filesize       = $this->get_stored_values( $postid, RHSWP_CPT_VIDEO_PREFIX . 'flv_filesize', '-' );
          $rhswp_video_wmv_url            = $this->get_stored_values( $postid, RHSWP_CPT_VIDEO_PREFIX . 'wmv_url', RHSWP_DEFAULT_EXAMPLES . 'examples.wmv' );
          $rhswp_video_filesize_wmv       = $this->get_stored_values( $postid, RHSWP_CPT_VIDEO_PREFIX . 'filesize_wmv', '-' );
          $rhswp_video_mp4_url            = $this->get_stored_values( $postid, RHSWP_CPT_VIDEO_PREFIX . 'mp4_url', RHSWP_DEFAULT_EXAMPLES . 'examples.mp4' );
          $rhswp_video_mp4_filesize       = $this->get_stored_values( $postid, RHSWP_CPT_VIDEO_PREFIX . 'mp4_filesize', '-' );
          $rhswp_video_mp4_hr_url         = $this->get_stored_values( $postid, RHSWP_CPT_VIDEO_PREFIX . 'mp4_url_hires', RHSWP_DEFAULT_EXAMPLES . 'examples_hd.mp4' );
          $rhswp_video_mp4_hr_filesize    = $this->get_stored_values( $postid, RHSWP_CPT_VIDEO_PREFIX . 'mp4_filesize_hires', '-' );
          $rhswp_video_3gp_url            = $this->get_stored_values( $postid, RHSWP_CPT_VIDEO_PREFIX . '3gp_url', RHSWP_DEFAULT_EXAMPLES . 'examples.3gp' );
          $rhswp_video_3gp_filesize       = $this->get_stored_values( $postid, RHSWP_CPT_VIDEO_PREFIX . '3gp_filesize', '-' );
          
          
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
          
    
          $returnstring .= '<div class="block-audio-video" id="block-' . $video_id . '" style="border: 1px solid red;">
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

    private function get_stored_values( $postid, $postkey, $defaultvalue = '' ) {

      $returnstring = $defaultvalue;

      $temp = get_post_meta( $postid, $postkey, true );
      if ( $temp ) {
        $returnstring = $temp;
      }
      
      return $returnstring;
    }

    //========================================================================================================
    
    
    
    public function append_comboboxes() {
    
    if ( RHSWP_CPT_RIJKSVIDEO_USE_CMB2 ) {
      

      if ( ! defined( 'CMB2_LOADED' ) ) {
        // cmb2 NOT loaded
        return false;
      }
    
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
      		'title'         => __( 'Metadata voor video', "rijksvideo-translate" ),
      		'object_types'  => array( RHSWP_CPT_RIJKSVIDEO ), // Post type
      	) );
    
      	$cmb_demo->add_field( array(
      		'name' => __( 'Lengte van de video', "rijksvideo-translate" ),
      		'desc' => __( 'formaat: uu:mm:ss', "rijksvideo-translate" ),
      		'id'   => RHSWP_CPT_VIDEO_PREFIX . 'video_time',
      		'type' => 'text_small',
      	) );
    
      	$cmb_demo->add_field( array(
      		'name' => __( 'URL van thumbnail', "rijksvideo-translate" ),
      		'desc' => __( 'URL van het bijbehorende plaatje', "rijksvideo-translate" ),
      		'id'   => RHSWP_CPT_VIDEO_PREFIX . 'url_video_thumb',
      		'type' => 'text_url',
      		'protocols' => array('http', 'https', '//'), // Array of allowed protocols
      	) );
    
      	$cmb_demo->add_field( array(
      		'name' => __( 'URL van audio-track', "rijksvideo-translate" ),
      		'desc' => __( 'Dit is meestal een bestand dat eindigt op .mp3', "rijksvideo-translate" ),
      		'id'   => RHSWP_CPT_VIDEO_PREFIX . 'audio_url',
      		'type' => 'text_url',
      		'protocols' => array('http', 'https', '//'), // Array of allowed protocols
      	) );
      
      	$cmb_demo->add_field( array(
      		'name' => __( 'URL van ondertitel', "rijksvideo-translate" ),
      		'desc' => __( 'Dit is meestal een bestand dat eindigt op .srt', "rijksvideo-translate" ),
      		'id'   => RHSWP_CPT_VIDEO_PREFIX . 'url_transcript_file',
      		'type' => 'text_url',
      		'protocols' => array('http', 'https', '//'), // Array of allowed protocols
      	) );
      
      	$cmb_demo->add_field( array(
      		'name' => __( 'Volledige transcriptie', "rijksvideo-translate" ),
      		'id'   => RHSWP_CPT_VIDEO_PREFIX . 'transcriptvlak',
      		'type' => 'textarea',
      	) );
    
      	$cmb_demo->add_field( array(
      		'name' => __( 'URL van FLV-bestand', "rijksvideo-translate" ),
      		'desc' => __( 'Eindigt op .flv', "rijksvideo-translate" ),
      		'id'   => RHSWP_CPT_VIDEO_PREFIX . 'flv_url',
      		'type' => 'text_url',
      		'protocols' => array('http', 'https', '//'), // Array of allowed protocols
      	) );
      
      	$cmb_demo->add_field( array(
      		'name' => __( 'Bestandsgrootte van FLV-bestand', "rijksvideo-translate" ),
      		'id'   => RHSWP_CPT_VIDEO_PREFIX . 'flv_filesize',
      		'type' => 'text_small',
      	) );
    
      	$cmb_demo->add_field( array(
      		'name' => __( 'URL van WMV-bestand', "rijksvideo-translate" ),
      		'desc' => __( 'Windows Media File. Eindigt vaak op .wmv', "rijksvideo-translate" ),
      		'id'   => RHSWP_CPT_VIDEO_PREFIX . 'wmv_url',
      		'type' => 'text_url',
      		'protocols' => array('http', 'https', '//'), // Array of allowed protocols
      	) );
      
      	$cmb_demo->add_field( array(
      		'name' => __( 'Bestandsgrootte van WMV-bestand', "rijksvideo-translate" ),
      		'id'   => RHSWP_CPT_VIDEO_PREFIX . 'filesize_wmv',
      		'type' => 'text_small',
      	) );
    
      	$cmb_demo->add_field( array(
      		'name' => __( 'URL van MP4-bestand', "rijksvideo-translate" ),
      		'desc' => __( 'Apple Quicktime-bestand. Eindigt vaak op .mp4', "rijksvideo-translate" ),
      		'id'   => RHSWP_CPT_VIDEO_PREFIX . 'mp4_url',
      		'type' => 'text_url',
      		'protocols' => array('http', 'https', '//'), // Array of allowed protocols
      	) );
      
      	$cmb_demo->add_field( array(
      		'name' => __( 'Bestandsgrootte van MP4-bestand', "rijksvideo-translate" ),
      		'id'   => RHSWP_CPT_VIDEO_PREFIX . 'mp4_filesize',
      		'type' => 'text_small',
      	) );
    
      	$cmb_demo->add_field( array(
      		'name' => __( 'URL van Hi-res MP4-bestand', "rijksvideo-translate" ),
      		'desc' => __( 'Versie van Quicktime-bestand in hoge resolutie.', "rijksvideo-translate" ),
      		'id'   => RHSWP_CPT_VIDEO_PREFIX . 'mp4_url_hires',
      		'type' => 'text_url',
      		'protocols' => array('http', 'https', '//'), // Array of allowed protocols
      	) );
      
      	$cmb_demo->add_field( array(
      		'name' => __( 'Bestandsgrootte van hi-res MP4-bestand', "rijksvideo-translate" ),
      		'id'   => RHSWP_CPT_VIDEO_PREFIX . 'mp4_filesize_hires',
      		'type' => 'text_small',
      	) );
    
      	$cmb_demo->add_field( array(
      		'name' => __( 'Url voor 3GP formaat', "rijksvideo-translate" ),
      		'id'   => RHSWP_CPT_VIDEO_PREFIX . '3gp_url',
      		'type' => 'text_url',
      		'protocols' => array('http', 'https', '//'), // Array of allowed protocols
      	) );
      
      	$cmb_demo->add_field( array(
      		'name' => __( 'Bestandsgrootte van 3GP-bestand', "rijksvideo-translate" ),
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
     * Check our WordPress installation is compatible with Rijksvideo
     */
    public function do_system_check() {

        $systemCheck = new RijksvideoSystemCheck();
        $systemCheck->check();

    }


    /**
     * Set the current slider
     */
    public function set_video_attributes( $id, $shortcode_settings = array() ) {

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
    public function update_video() {

        check_admin_referer( "rijksvideo_update_video" );

        $capability = apply_filters( 'rijksvideo_capability', 'edit_others_posts' );

        if ( ! current_user_can( $capability ) ) {
            return;
        }

        $rijksvideo_id = absint( $_POST['slider_id'] );

        if ( ! $rijksvideo_id ) {
            return;
        }

        // update settings
        if ( isset( $_POST['settings'] ) ) {

            $new_settings = $_POST['settings'];

            $old_settings = get_post_meta( $rijksvideo_id, 'rijksvideo_settings', true );

            // convert submitted checkbox values from 'on' or 'off' to boolean values
            $checkboxes = apply_filters( "rijksvideo_checkbox_settings", array( 'noConflict', 'fullWidth', 'hoverPause', 'links', 'reverse', 'random', 'printCss', 'printJs', 'smoothHeight', 'center', 'carouselMode', 'autoPlay' ) );

            foreach ( $checkboxes as $checkbox ) {
                if ( isset( $new_settings[$checkbox] ) && $new_settings[$checkbox] == 'on' ) {
                    $new_settings[$checkbox] = "true";
                } else {
                    $new_settings[$checkbox] = "false";
                }
            }

            $settings = array_merge( (array)$old_settings, $new_settings );

            // update the slider settings
            update_post_meta( $rijksvideo_id, 'rijksvideo_settings', $settings );

        }

        // update video title
        if ( isset( $_POST['title'] ) ) {

            $slide = array(
                'ID' => $rijksvideo_id,
                'post_title' => esc_html( $_POST['title'] )
            );

            wp_update_post( $slide );

        }

        // update individual slides
        if ( isset( $_POST['attachment'] ) ) {

            foreach ( $_POST['attachment'] as $slide_id => $fields ) {
                do_action( "rijksvideo_save_{$fields['type']}_slide", $slide_id, $rijksvideo_id, $fields );
            }

        }

    }




    /**
     * Get sliders. Returns a nicely formatted array of currently
     * published sliders.
     *
     * @param string $sort_key
     * @return array all published sliders
     */
    public function get_all_videos( $sort_key = 'date' ) {

        $rijksvideos = array();

        // list the tabs
        $args = array(
            'post_type'         => RIJKSVIDEO_CPT,
            'post_status'       => 'publish',
            'orderby'           => $sort_key,
            'suppress_filters'  => 1, // wpml, ignore language filter
            'order'             => 'ASC',
            'posts_per_page'    => -1
        );

        $args = apply_filters( 'rijksvideo_get_all_videos_args', $args );

        // WP_Query causes issues with other plugins using admin_footer to insert scripts
        // use get_posts instead
        $videos = get_posts( $args );

        foreach( $videos as $video ) {

            $active = $this->slider && ( $this->slider->id == $video->ID ) ? true : false;

            $rijksvideos[] = array(
                'active'  => $active,
                'title'   => $video->post_title,
                'id'      => $video->ID
            );

        }

        return $rijksvideos;

    }




    


    /**
     * Append the 'Add video' button to selected admin pages
     */
    public function insert_rijksvideo_button( $context ) {

        $capability = apply_filters( 'rijksvideo_capability', 'edit_others_posts' );

        if ( ! current_user_can( $capability ) ) {
            return $context;
        }

        global $pagenow;
        $posttype = get_post_type( $_GET['post'] );

        if ( ( in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) ) && ( in_array( $posttype, array( 'post',  'page' ) ) ) ) {
            $context .= '<a href="#TB_inline?&inlineId=choose-meta-slider" class="thickbox button" title="' .
                __( "Selecteer een rijksvideo om in dit bericht in te voegen.", "rijksvideo-translate" ) .
                '"><span class="wp-media-buttons-icon" style="background: url(' . RIJKSVIDEO_ASSETS_URL . 'images/icon-video.png); background-repeat: no-repeat; background-size: 16px 16px; background-position: center center;"></span> ' .
                __( "Voeg rijksvideo in", "rijksvideo-translate" ) . ' (' . $posttype . ')</a>';
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
            $rijksvideos = $this->get_all_videos( 'title' );
            ?>

            <script type="text/javascript">
                jQuery(document).ready(function() {
                  jQuery('#insertMetaSlider').on('click', function() {
                    var id = jQuery('#rijksvideo-select option:selected').val();
                    window.send_to_editor('[<?php echo RIJKSVIDEO_CPT ?> id=' + id + ']');
                    tb_remove();
                  })
                });
            </script>

            <div id="choose-meta-slider" style="display: none;">
                <div class="wrap">
                    <?php

                        if ( count( $rijksvideos ) ) {
                            echo "<h3>" . __( "Choose a video to insert into your post / page.", "rijksvideo-translate" ) . "</h3>";
                            echo "<select id='rijksvideo-select'>";
                            echo "<option disabled=disabled>" . __( "Choose video", "rijksvideo-translate" ) . "</option>";
                            foreach ( $rijksvideos as $rijksvideo ) {
                                echo "<option value='{$rijksvideo['id']}'>{$rijksvideo['title']}</option>";
                            }
                            echo "</select>";
                            echo "<button class='button primary' id='insertMetaSlider'>" . __( "Select and insert video", "rijksvideo-translate" ) . "</button>";
                        } else {
                            _e( "No videos found", "rijksvideo-translate" );
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