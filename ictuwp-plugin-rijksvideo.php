<?php
/*
// * Rijksvideo.
// *
// * Plugin Name:         ICTU / Rijksvideo digitaleoverheid.nl
// * Plugin URI:          https://github.com/ICTU/digitale-overheid-wordpress-plugin-rijksvideoplugin/
// * Description:         De mogelijkheid om video's in te voegen met diverse media-formats en ondertitels
// * Version:             1.0.11
// * Version description: HTML check.
// * Author:              Paul van Buuren
// * Author URI:          https://wbvb.nl
// * License:             GPL-2.0+
// *
// * Text Domain:         rijksvideo-translate
// * Domain Path:         /languages
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
		public $version = '1.0.11';


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

			$protocol = strtolower( substr( $_SERVER["SERVER_PROTOCOL"], 0, strpos( $_SERVER["SERVER_PROTOCOL"], '/' ) ) ) . '://';

			define( 'RIJKSVIDEO_VERSION', $this->version );
			define( 'RIJKSVIDEO_FOLDER', 'ictuwp-plugin-rijksvideo' );
			define( 'RIJKSVIDEO_BASE_URL', trailingslashit( plugins_url( RIJKSVIDEO_FOLDER ) ) );
			define( 'RIJKSVIDEO_ASSETS_URL', trailingslashit( RIJKSVIDEO_BASE_URL . 'assets' ) );
			define( 'RIJKSVIDEO_MEDIAELEMENT_URL', trailingslashit( RIJKSVIDEO_BASE_URL . 'mediaelement' ) );
			define( 'RIJKSVIDEO_PATH', plugin_dir_path( __FILE__ ) );
			define( 'RHSWP_CPT_RIJKSVIDEO', "rijksvideo" );
			define( 'RIJKSVIDEO_CT', "rijksvideo_custom_taxonomy" );

			define( 'RHSWP_CPT_VIDEO_PREFIX', RHSWP_CPT_RIJKSVIDEO . '_pf_' ); // prefix for rijksvideo metadata fields
//      define( 'RHSWP_RV_DO_DEBUG',      true );
			define( 'RHSWP_RV_DO_DEBUG', false );
			define( 'RHSWP_RV_USE_CMB2', true );
			define( 'RHSWP_DEFAULT_EXAMPLES', RIJKSVIDEO_ASSETS_URL . 'examples/' ); // slug for custom post type 'rijksvideo'

		}


		/**
		 * All Rijksvideo classes
		 */
		private function plugin_classes() {

			return array(
				'RijksvideoSystemCheck' => RIJKSVIDEO_PATH . 'inc/rijksvideo.systemcheck.class.php',
			);

		}


		/**
		 * Load required classes
		 */
		private function includes() {

			if ( RHSWP_RV_USE_CMB2 ) {
				// load CMB2 functionality
				if ( ! defined( 'CMB2_LOADED' ) ) {
					// cmb2 NOT loaded
					if ( file_exists( dirname( __FILE__ ) . '/cmb2/init.php' ) ) {
						require_once dirname( __FILE__ ) . '/cmb2/init.php';
					} elseif ( file_exists( dirname( __FILE__ ) . '/CMB2/init.php' ) ) {
						require_once dirname( __FILE__ ) . '/CMB2/init.php';
					}
				}
			}


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


		/**
		 * filter for when the CPT is previewed
		 */
		public function content_filter_for_preview( $content = '' ) {
			global $post;

			if ( ( RHSWP_CPT_RIJKSVIDEO == get_post_type() ) && ( is_single() ) ) {

				// lets go
				$this->register_frontend_style_script();

				return $content . $this->rhswp_makevideo( $post->ID );
			} else {
				return $content;
			}

		}


		/**
		 * Autoload Rijksvideo classes to reduce memory consumption
		 */
		public function autoload( $class ) {

			$classes = $this->plugin_classes();

			$class_name = strtolower( $class );

			if ( isset( $classes[ $class_name ] ) && is_readable( $classes[ $class_name ] ) ) {
				require_once( $classes[ $class_name ] );
			}

		}


		/**
		 * Register the [rijksvideo] shortcode.
		 */
		private function setup_shortcode() {

			add_shortcode( 'rijksvideo', array( $this, 'register_shortcode' ) );
			add_shortcode( RHSWP_CPT_RIJKSVIDEO, array( $this, 'register_shortcode' ) ); // backwards compatibility


		}


		/**
		 * Hook Rijksvideo into WordPress
		 */
		private function setup_actions() {

			add_action( 'init', array( $this, 'register_post_type' ) );
			add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
			add_action( 'admin_footer', array( $this, 'admin_footer' ), 11 );


		}


		/**
		 * Hook Rijksvideo into WordPress
		 */
		private function setup_filters() {

			add_filter( 'media_buttons_context', array( $this, 'admin_insert_rijksvideo_button' ) );

			// content filter
			add_filter( 'the_content', array( $this, 'content_filter_for_preview' ) );


		}


		/**
		 * Register post type
		 */
		public function register_post_type() {

			$labels = array(
				"name"               => "Rijksvideo's",
				"singular_name"      => "Rijksvideo",
				"menu_name"          => "Rijksvideo's",
				"all_items"          => "Alle rijksvideo's",
				"add_new"            => "Nieuwe toevoegen",
				"add_new_item"       => "Nieuwe rijksvideo toevoegen",
				"edit"               => "Bewerken?",
				"edit_item"          => "Bewerk rijksvideo",
				"new_item"           => "Nieuwe rijksvideo's",
				"view"               => "Toon",
				"view_item"          => "Bekijk rijksvideo",
				"search_items"       => "Zoek rijksvideo",
				"not_found"          => "Niet gevonden",
				"not_found_in_trash" => "Geen rijksvideo's gevonden in de prullenbak",
				"parent"             => "Hoofd",
			);

			$args = array(
				"labels"              => $labels,
				"label"               => __( 'Rijksvideo', '' ),
				"labels"              => $labels,
				"description"         => "",
				"public"              => true,
				"publicly_queryable"  => true,
				"show_ui"             => true,
				"show_in_rest"        => false,
				"rest_base"           => "",
				"has_archive"         => false,
				"show_in_menu"        => true,
				"exclude_from_search" => false,
				"capability_type"     => "post",
				"map_meta_cap"        => true,
				"hierarchical"        => false,
				"rewrite"             => array( "slug" => RHSWP_CPT_RIJKSVIDEO, "with_front" => true ),
				"query_var"           => true,
				"supports"            => array( "title", "editor", "thumbnail", "excerpt" ),
			);
			register_post_type( RHSWP_CPT_RIJKSVIDEO, $args );

			flush_rewrite_rules();

		}


		/**
		 * Shortcode used to display video
		 *
		 * @return string HTML output of the shortcode
		 */
		public function register_shortcode( $atts ) {

			extract( shortcode_atts( array(
				'id'          => false,
				'restrict_to' => false
			), $atts, 'rijksvideo' ) );


			if ( ! $id ) {
				return false;
			}

			// we have an ID to work with
			$rijksvideo = get_post( $id );

			// check the video is published and the ID is correct
			if ( ! $rijksvideo || $rijksvideo->post_status != 'publish' || $rijksvideo->post_type != RHSWP_CPT_RIJKSVIDEO ) {
				return "<!-- video {$atts['id']} not found -->";
			}

			// lets go
			$this->register_frontend_style_script();

			return $this->rhswp_makevideo( $id );

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
					'content' => "<p><a href='https://github.com/ICTU/digitale-overheid-wordpress-plugin-rijksvideoplugin/documentation/' target='blank'>" . __( 'Rijksvideo documentatie', "rijksvideo-translate" ) . "</a></p>",
				)
			);
		}


		/**
		 * Register frontend styles
		 */
		public function register_frontend_style_script() {

			if ( ! is_admin() ) {


				$infooter = false;

				// don't add to any admin pages
				wp_enqueue_script( 'rhswp_video_js', RIJKSVIDEO_MEDIAELEMENT_URL . 'build/mediaelement-and-player.js', array( 'jquery' ), RIJKSVIDEO_VERSION, $infooter );
				wp_enqueue_script( 'rhswp_video_action_js', RIJKSVIDEO_ASSETS_URL . 'js/createPlayer.js', array( 'jquery' ), RIJKSVIDEO_VERSION, $infooter );
				wp_enqueue_style( 'rhswp-mediaelementplayer', RIJKSVIDEO_MEDIAELEMENT_URL . 'build/mediaelementplayer.css', array(), RIJKSVIDEO_VERSION, $infooter );

				$theme_options = get_option( 'gc2020_theme_options' );
				if ( $theme_options ) {
					// dit is blijkbaar de een of andere GC-site
					wp_enqueue_style( 'rhswp-frontend', RIJKSVIDEO_ASSETS_URL . 'css/video-gebruiker-centraal.css', array(), RIJKSVIDEO_VERSION, $infooter );
					wp_enqueue_script( 'rhswp_video_collapsible', RIJKSVIDEO_ASSETS_URL . 'js/gc-collapsible-min.js', '', RIJKSVIDEO_VERSION, $infooter );

				} else {
					wp_enqueue_style( 'rhswp-frontend', RIJKSVIDEO_ASSETS_URL . 'css/rijksvideo.css', array(), RIJKSVIDEO_VERSION, $infooter );
				}

			}

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
					'url'                => __( "URL", "rijksvideo-translate" ),
					'caption'            => __( "Caption", "rijksvideo-translate" ),
					'new_window'         => __( "New Window", "rijksvideo-translate" ),
					'confirm'            => __( "Weet je het zeker?", "rijksvideo-translate" ),
					'ajaxurl'            => admin_url( 'admin-ajax.php' ),
					'resize_nonce'       => wp_create_nonce( 'rijksvideo_resize' ),
					'add_video_nonce'    => wp_create_nonce( 'rijksvideo_add_video' ),
					'change_video_nonce' => wp_create_nonce( 'rijksvideo_change_video' ),
					'iframeurl'          => admin_url( 'admin-post.php?action=rijksvideo_preview' ),
				)
			);

		}

		//========================================================================================================

		/**
		 * Output the HTML
		 */
		public function rhswp_makevideo( $postid ) {

			$videotitle = get_the_title( $postid );

			$theme_options = get_option( 'gc2020_theme_options' );
			if ( $theme_options ) {
				// voor GC-sites
				$videoplayer_width  = '760';
				$videoplayer_height = '428';
			} else {
				// anders dan GC-sites
				$videoplayer_width  = '500';
				$videoplayer_height = '412';
			}
			$video_id = 'movie-' . $postid;

			$videoplayer_aria_id = 'mep_7';
			$videoplayer_date    = get_the_date();

			$videoplayer_title              = _x( 'Video Player', 'Rijksvideo', "rijksvideo-translate" );
			$videoplayer_video_txt          = _x( 'Video', 'Rijksvideo', "rijksvideo-translate" );
			$videoplayer_audio_txt          = _x( 'Audio', 'Rijksvideo', "rijksvideo-translate" );
			$videoplayer_subtitle_txt       = _x( 'Caption', 'Rijksvideo', "rijksvideo-translate" );
			$videoplayer_subtitles          = _x( 'Ondertitels', 'Rijksvideo', "rijksvideo-translate" );
			$videoplayer_subtitles_language = _x( 'nl', 'Rijksvideo: voor welke taal heb je ondertitels geupload?', "rijksvideo-translate" );


			$videoplayer_play             = _x( 'Afspelen', 'Rijksvideo', "rijksvideo-translate" );
			$videoplayer_mute_label       = _x( 'Geluid uit', 'Rijksvideo', "rijksvideo-translate" );
			$videoplayer_fullscreen_open  = _x( 'Schermvullende weergave openen', 'Rijksvideo', "rijksvideo-translate" );
			$videoplayer_fullscreen_close = _x( 'Schermvullende weergave sluiten', 'Rijksvideo', "rijksvideo-translate" );
			$videoplayer_volume_label     = _x( 'Gebruik op- of neer-pijltjestoetsen om het volume harder of zachter te zetten', 'Rijksvideo', "rijksvideo-translate" );
			$videoplayer_subtitle_on      = _x( 'Ondertiteling aan', 'Rijksvideo', "rijksvideo-translate" );
			$videoplayer_download_label   = sprintf( _x( 'Downloads %s bij %s %s', 'Titel boven downloads', "rijksvideo-translate" ), '<span class="visuallyhidden">', $videotitle, '</span>' );


			$videoplayer_noplugin_label = _x( 'Helaas kan deze video niet worden afgespeeld. Een mogelijk oplossing is de meest recente versie van uw browser te installeren.', 'Rijksvideo', "rijksvideo-translate" );

			$videoplayer_quicktime_label = _x( 'Video voor Apple Quicktime Player', 'Rijksvideo', "rijksvideo-translate" );
			$videoplayer_quicktime_abbr  = _x( 'mp4', 'Rijksvideo', "rijksvideo-translate" );
			$videoplayer_quicktime_hr    = _x( ' (hoge resolutie)', 'Rijksvideo', "rijksvideo-translate" );

			$videoplayer_wmv_label = _x( 'Video voor Windows Media Player', 'Rijksvideo', "rijksvideo-translate" );
			$videoplayer_wmv_abbr  = _x( 'wmv', 'Rijksvideo', "rijksvideo-translate" );

			$videoplayer_mobileformat_label = _x( 'Video voor mobiel gebruik', 'Rijksvideo', "rijksvideo-translate" );
			$videoplayer_mobileformat_abbr  = _x( '3gp', 'Rijksvideo', "rijksvideo-translate" );

			$videoplayer_audioformat_label = _x( 'Audiospoor', 'Rijksvideo', "rijksvideo-translate" );
			$videoplayer_audioformat_abbr  = _x( 'mp3', 'Rijksvideo', "rijksvideo-translate" );

			$videoplayer_subtitle_label = _x( 'Ondertitelingsbestand', 'Rijksvideo', "rijksvideo-translate" );
			$videoplayer_subtitle_abbr  = _x( 'srt', 'Rijksvideo', "rijksvideo-translate" );


			$uniqueid = $this->getuniqueid( $postid );

			$rhswp_video_duur = $this->get_stored_values( $postid, RHSWP_CPT_VIDEO_PREFIX . 'video_time', '-' );

			// to do: check featured image
			if ( has_post_thumbnail( $postid ) ) {
				$rhswp_video_url_video_thumb_arr = wp_get_attachment_image_src( get_post_thumbnail_id( $postid ), "large" );
				$rhswp_video_url_video_thumb     = $rhswp_video_url_video_thumb_arr[0];
			} else {
				$rhswp_video_url_video_thumb = $this->get_stored_values( $postid, RHSWP_CPT_VIDEO_PREFIX . 'url_video_thumb', '' );
			}

			$rhswp_video_url_video_thumb = rovidcheck( $rhswp_video_url_video_thumb );

			$rhswp_video_url_transcript  = rovidcheck( $this->get_stored_values( $postid, RHSWP_CPT_VIDEO_PREFIX . 'url_transcript_file', '' ) );
			$rhswp_video_transcriptvlak  = $this->get_stored_values( $postid, RHSWP_CPT_VIDEO_PREFIX . 'transcriptvlak', '' );
			$rhswp_video_url_flv         = $this->get_stored_values( $postid, RHSWP_CPT_VIDEO_PREFIX . 'flv_url', '' );
			$rhswp_video_flv_filesize    = $this->get_stored_values( $postid, RHSWP_CPT_VIDEO_PREFIX . 'flv_filesize', '-' );
			$rhswp_video_url_wmv         = $this->get_stored_values( $postid, RHSWP_CPT_VIDEO_PREFIX . 'wmv_url', '' );
			$rhswp_video_filesize_wmv    = $this->get_stored_values( $postid, RHSWP_CPT_VIDEO_PREFIX . 'filesize_wmv', '-' );
			$rhswp_video_url_mp4         = rovidcheck( $this->get_stored_values( $postid, RHSWP_CPT_VIDEO_PREFIX . 'mp4_url', RHSWP_DEFAULT_EXAMPLES . 'examples.mp4' ) );
			$rhswp_video_mp4_filesize    = $this->get_stored_values( $postid, RHSWP_CPT_VIDEO_PREFIX . 'mp4_filesize', '-' );
			$rhswp_video_mp4_hr_url      = rovidcheck( $this->get_stored_values( $postid, RHSWP_CPT_VIDEO_PREFIX . 'mp4_url_hires', RHSWP_DEFAULT_EXAMPLES . 'examples_hd.mp4' ) );
			$rhswp_video_mp4_hr_filesize = $this->get_stored_values( $postid, RHSWP_CPT_VIDEO_PREFIX . 'mp4_filesize_hires', '-' );
			$rhswp_video_3gp_url         = rovidcheck( $this->get_stored_values( $postid, RHSWP_CPT_VIDEO_PREFIX . '3gp_url', RHSWP_DEFAULT_EXAMPLES . 'examples.3gp' ) );
			$rhswp_video_3gp_filesize    = $this->get_stored_values( $postid, RHSWP_CPT_VIDEO_PREFIX . '3gp_filesize', '-' );
			$rhswp_video_audio_url       = rovidcheck( $this->get_stored_values( $postid, RHSWP_CPT_VIDEO_PREFIX . 'audio_url', '' ) );
			$rhswp_video_mp3_filesize    = $this->get_stored_values( $postid, RHSWP_CPT_VIDEO_PREFIX . 'mp3_filesize', '' );

			$returnstring = '';

			if ( RHSWP_RV_DO_DEBUG && WP_DEBUG ) {

				$returnstring .= 'RHSWP_RV_USE_CMB2: "' . RHSWP_RV_USE_CMB2 . '"<br>';
				$returnstring .= '$rhswp_video_duur: "' . $rhswp_video_duur . '"<br>';
				$returnstring .= '$rhswp_video_url_video_thumb: "' . $rhswp_video_url_video_thumb . '"<br>';
				$returnstring .= '$rhswp_video_url_transcript: "' . $rhswp_video_url_transcript . '"<br>';
				$returnstring .= '$rhswp_video_transcriptvlak: "' . $rhswp_video_transcriptvlak . '"<br>';
				$returnstring .= '$rhswp_video_audio_url: "' . $rhswp_video_audio_url . '"<br>';
				$returnstring .= '$rhswp_video_audio_filesize: "' . $rhswp_video_mp3_filesize . '"<br>';

				$returnstring .= '$rhswp_video_url_flv: "' . $rhswp_video_url_flv . '"<br>';
				$returnstring .= '$rhswp_video_flv_filesize: "' . $rhswp_video_flv_filesize . '"<br>';
				$returnstring .= '$rhswp_video_url_wmv: "' . $rhswp_video_url_wmv . '"<br>';
				$returnstring .= '$rhswp_video_filesize_wmv: "' . $rhswp_video_filesize_wmv . '"<br>';
				$returnstring .= '$rhswp_video_url_mp4: "' . $rhswp_video_url_mp4 . '"<br>';
				$returnstring .= '$rhswp_video_mp4_filesize: "' . $rhswp_video_mp4_filesize . '"<br>';
				$returnstring .= '$rhswp_video_mp4_hr_url: "' . $rhswp_video_mp4_hr_url . '"<br>';
				$returnstring .= '$rhswp_video_mp4_hr_filesize: "' . $rhswp_video_mp4_hr_filesize . '"<br>';
				$returnstring .= '$rhswp_video_3gp_url: "' . $rhswp_video_3gp_url . '"<br>';
				$returnstring .= '$rhswp_video_3gp_filesize: "' . $rhswp_video_3gp_filesize . '"<br>';

			}

			$returnstring .= "\n\n\n";

			if ( $rhswp_video_url_video_thumb && ( ( $rhswp_video_url_mp4 ) || ( $rhswp_video_url_wmv ) || ( $rhswp_video_url_flv ) ) ) {
				// wel of geen arialabel, that is the question
				// omdat de HTML-validator erover klaagt, vanaf nu geen aria-label meer.
				// $arialabel = ' aria-label="' . wp_strip_all_tags( sprintf( _x( 'Video getiteld: \'%s\'', 'Rijksvideo', "rijksvideo-translate" ), $videotitle ) ) . '"';
				$arialabel = '';


				$returnstring .= '<div class="block-audio-video" id="block-' . $video_id . '"' . $arialabel . '>';

				$returnstring .= '<video id="' . $video_id . '" width="' . $videoplayer_width . '" height="' . $videoplayer_height . '" poster="' . esc_url( $rhswp_video_url_video_thumb ) . '" data-noplugintxt="' . $videoplayer_noplugin_label . '">';

				if ( $rhswp_video_url_mp4 ) {
					$returnstring .= '<source type="video/mp4" src="' . esc_url( $rhswp_video_url_mp4 ) . '">';
				}
				if ( $rhswp_video_url_wmv ) {
					$returnstring .= '<source type="video/wmv" src="' . esc_url( $rhswp_video_url_wmv ) . '">';
				}
				if ( $rhswp_video_url_flv ) {
					$returnstring .= '<source type="video/x-flv" src="' . esc_url( $rhswp_video_url_flv ) . '">';
				}
				if ( $rhswp_video_url_transcript ) {
					$returnstring .= '<track kind="subtitles" src="' . esc_url( $rhswp_video_url_transcript ) . '" label="' . $videoplayer_subtitles . '" srclang="' . $videoplayer_subtitles_language . '">';
				}

				$returnstring .= '</video>' . "\n";


				$returnstring .= '<div class="downloads">

			<h2 id="videoplayer_download_label' . $uniqueid . '" class="collapsetoggle"><button aria-expanded="false">' . $videoplayer_download_label . '</button></h2>
			<div class="collapsible"  hidden>
			<ul aria-labelledby="videoplayer_download_label' . $uniqueid . '">';

				if ( $rhswp_video_url_mp4 ) {
					$returnstring .= '<li class="download"><a href="' . $rhswp_video_url_mp4 . '">' . $videoplayer_quicktime_label . '<span class="meta mp4">' . $videoplayer_video_txt . ', ' . $videoplayer_date . ', ' . $rhswp_video_duur . ' ' . $videoplayer_quicktime_abbr . ', ' . $rhswp_video_mp4_filesize . '</span></a></li>';
				}
				if ( $rhswp_video_mp4_hr_url ) {
					$returnstring .= '<li class="download"><a href="' . $rhswp_video_mp4_hr_url . '">' . $videoplayer_quicktime_label . '<span class="meta mp4">' . $videoplayer_video_txt . ', ' . $videoplayer_date . ', ' . $rhswp_video_duur . ' ' . $videoplayer_quicktime_abbr . $videoplayer_quicktime_hr . ', ' . $rhswp_video_mp4_hr_filesize . '</span></a></li>';
				}
				if ( $rhswp_video_url_wmv ) {
					$returnstring .= '<li class="download"><a href="' . $rhswp_video_url_wmv . '">' . $videoplayer_wmv_label . '<span class="meta wmv">' . $videoplayer_video_txt . ', ' . $videoplayer_date . ', ' . $rhswp_video_duur . ' ' . $videoplayer_wmv_abbr . ', ' . $rhswp_video_filesize_wmv . '</span></a></li>';
				}
				if ( $rhswp_video_3gp_url ) {
					$returnstring .= '<li class="download"><a href="' . $rhswp_video_3gp_url . '">' . $videoplayer_mobileformat_label . '<span class="meta 3gp">' . $videoplayer_video_txt . ', ' . $videoplayer_date . ', ' . $rhswp_video_duur . ' ' . $videoplayer_mobileformat_abbr . ', ' . $rhswp_video_3gp_filesize . '</span></a></li>';
				}
				if ( $rhswp_video_audio_url ) {
					$returnstring .= '<li class="download"><a href="' . $rhswp_video_audio_url . '">' . $videoplayer_audioformat_label . '<span class="meta mp3">' . $videoplayer_video_txt . ', ' . $videoplayer_date . ', ' . $rhswp_video_duur . ' ' . $videoplayer_audioformat_abbr . ', ' . $rhswp_video_mp3_filesize . ' </span></a></li>';
				}
				if ( $rhswp_video_url_transcript ) {
					$returnstring .= '<li class="download"><a href="' . $rhswp_video_url_transcript . '">' . $videoplayer_subtitle_label . '<span class="meta srt">' . $videoplayer_video_txt . ', ' . $videoplayer_date . ', ' . $rhswp_video_duur . ' ' . $videoplayer_subtitle_abbr . ' </span></a></li>';
				}

				$returnstring .= '
			</ul>
			</div><!-- div class="collapsible"  hidden> -->' .
				                 '</div><!-- .downloads -->' . "\n"; // .downloads


				if ( $rhswp_video_transcriptvlak ) {
					$rhswp_video_transcriptvlak = wpautop( $rhswp_video_transcriptvlak, 'br' );

					$uitgeschreventekst_label = sprintf( _x( 'Uitgeschreven tekst %s bij %s %s', 'Titel boven uitgeschreven tekst', "rijksvideo-translate" ), '<span class="visuallyhidden">', $videotitle, '</span>' );


					$returnstring .= '
				<h2 id="videoplayer_captions' . $uniqueid . '" class="collapsetoggle">
				<button aria-expanded="false">' . $uitgeschreventekst_label . '</button>
				</h2>
				<div class="collapsible"  hidden>
					<div aria-labelledby="videoplayer_captions' . $uniqueid . '">' . $rhswp_video_transcriptvlak . '</div>
				</div>' . "\n";
				}


				$returnstring .= '</div><!-- .block-audio-video -->' . "\n"; // class="block-audio-video"


			} else {

				if ( ! $rhswp_video_url_video_thumb ) {
					$returnstring .= '<p>' . _x( 'Er is geen plaatje gevonden voor deze video. Je kunt een uitgelichte afbeelding gebruiken of een extern beeld-bestand.', 'Rijksvideo', "rijksvideo-translate" ) . '</p>';
				} elseif ( ( ! $rhswp_video_url_mp4 ) && ( ! $rhswp_video_url_wmv ) || ( ! $rhswp_video_url_flv ) ) {
					$returnstring .= '<p>' . _x( 'Er is geen video-stream gevonden voor deze video.', 'Rijksvideo', "rijksvideo-translate" ) . '</p>';
				} else {
					$returnstring .= '<p>' . _x( 'Er is onvoldoende ingevoerd om deze video goed weer te geven.', 'Rijksvideo', "rijksvideo-translate" ) . '</p>';
				}
			}

			return $returnstring;
		}

		//========================================================================================================

		private function get_stored_values( $postid, $postkey, $defaultvalue = '' ) {

			if ( RHSWP_RV_DO_DEBUG ) {
				$returnstring = $defaultvalue;
			} else {
				$returnstring = '';
			}

			$temp = get_post_meta( $postid, $postkey, true );
			if ( $temp ) {
				$returnstring = $temp;
			}

			return $returnstring;
		}

		//========================================================================================================

		public function getuniqueid( $video_id ) {

			global $post;

			return '_video' . $video_id . '_post' . $post->ID;

		}

		//========================================================================================================

		public function append_comboboxes() {

			if ( RHSWP_RV_USE_CMB2 ) {


				if ( ! defined( 'CMB2_LOADED' ) ) {
					return false;
					die( ' CMB2_LOADED not loaded ' );
					// cmb2 NOT loaded
				} else {
				}

				add_action( 'cmb2_admin_init', 'rhswp_register_metabox_rijksvideo' );

				/**
				 * Hook in and add a demo metabox. Can only happen on the 'cmb2_admin_init' or 'cmb2_init' hook.
				 */
				function rhswp_register_metabox_rijksvideo() {

					/**
					 * Metabox with fields for the video
					 */
					$cmb2_metafields = new_cmb2_box( array(
						'id'           => RHSWP_CPT_VIDEO_PREFIX . 'metabox',
						'title'        => __( 'Metadata voor video', "rijksvideo-translate" ),
						'object_types' => array( RHSWP_CPT_RIJKSVIDEO ), // Post type
					) );

					/**
					 * The fields
					 */

					$cmb2_metafields->add_field( array(
						'name'       => __( 'URL van thumbnail', "rijksvideo-translate" ),
						'desc'       => __( 'Als caption van de video wordt eerst gekeken of je een uitgelichte afbeelding hebt toegevoegd aan deze video. Als die er niet is, kun je hier de URL van het bijbehorende plaatje invoeren.', "rijksvideo-translate" ),
						'id'         => RHSWP_CPT_VIDEO_PREFIX . 'url_video_thumb',
						'type'       => 'text_url',
						'protocols'  => array( 'http', 'https', '//' ), // Array of allowed protocols
						'attributes' => array(
							'data-validation' => 'required',
							'required'        => 'required'
						),

					) );

					$cmb2_metafields->add_field( array(
						'name'       => __( 'Lengte (*)', "rijksvideo-translate" ),
						'desc'       => __( '(verplicht) formaat: uu:mm:ss', "rijksvideo-translate" ),
						'id'         => RHSWP_CPT_VIDEO_PREFIX . 'video_time',
						'type'       => 'text_small',
						'attributes' => array(
							'data-validation' => 'required',
							'required'        => 'required'
						),
					) );

					$cmb2_metafields->add_field( array(
						'name'      => __( 'URL van MP4-bestand', "rijksvideo-translate" ),
						'desc'      => __( 'Apple Quicktime-bestand. Eindigt vaak op .mp4', "rijksvideo-translate" ),
						'id'        => RHSWP_CPT_VIDEO_PREFIX . 'mp4_url',
						'type'      => 'text_url',
						'protocols' => array( 'http', 'https', '//' ), // Array of allowed protocols
					) );

					$cmb2_metafields->add_field( array(
						'name' => __( 'Bestandsgrootte van MP4-bestand', "rijksvideo-translate" ),
						'id'   => RHSWP_CPT_VIDEO_PREFIX . 'mp4_filesize',
						'type' => 'text_small',
					) );

					$cmb2_metafields->add_field( array(
						'name'      => __( 'URL van Hi-res MP4-bestand', "rijksvideo-translate" ),
						'desc'      => __( 'Versie van Quicktime-bestand in hoge resolutie.', "rijksvideo-translate" ),
						'id'        => RHSWP_CPT_VIDEO_PREFIX . 'mp4_url_hires',
						'type'      => 'text_url',
						'protocols' => array( 'http', 'https', '//' ), // Array of allowed protocols
					) );

					$cmb2_metafields->add_field( array(
						'name' => __( 'Bestandsgrootte van hi-res MP4-bestand', "rijksvideo-translate" ),
						'id'   => RHSWP_CPT_VIDEO_PREFIX . 'mp4_filesize_hires',
						'type' => 'text_small',
					) );

					$cmb2_metafields->add_field( array(
						'name'      => __( 'Url voor 3GP formaat', "rijksvideo-translate" ),
						'id'        => RHSWP_CPT_VIDEO_PREFIX . '3gp_url',
						'type'      => 'text_url',
						'protocols' => array( 'http', 'https', '//' ), // Array of allowed protocols
					) );

					$cmb2_metafields->add_field( array(
						'name' => __( 'Bestandsgrootte van 3GP-bestand', "rijksvideo-translate" ),
						'id'   => RHSWP_CPT_VIDEO_PREFIX . '3gp_filesize',
						'type' => 'text_small',
					) );

					$cmb2_metafields->add_field( array(
						'name'      => __( 'URL van ondertitel', "rijksvideo-translate" ),
						'desc'      => __( 'Dit is meestal een bestand dat eindigt op .srt', "rijksvideo-translate" ),
						'id'        => RHSWP_CPT_VIDEO_PREFIX . 'url_transcript_file',
						'type'      => 'text_url',
						'protocols' => array( 'http', 'https', '//' ), // Array of allowed protocols
					) );

					$cmb2_metafields->add_field( array(
						'name' => __( 'Volledige transcriptie', "rijksvideo-translate" ),
						'id'   => RHSWP_CPT_VIDEO_PREFIX . 'transcriptvlak',
						'type' => 'textarea',
					) );

					$cmb2_metafields->add_field( array(
						'name'      => __( 'URL van audio-track', "rijksvideo-translate" ),
						'desc'      => __( 'Dit is meestal een bestand dat eindigt op .mp3', "rijksvideo-translate" ),
						'id'        => RHSWP_CPT_VIDEO_PREFIX . 'audio_url',
						'type'      => 'text_url',
						'protocols' => array( 'http', 'https', '//' ), // Array of allowed protocols
					) );

					$cmb2_metafields->add_field( array(
						'name' => __( 'Bestandsgrootte van audio-track', "rijksvideo-translate" ),
						'id'   => RHSWP_CPT_VIDEO_PREFIX . 'mp3_filesize',
						'type' => 'text_small',
					) );


					$cmb2_metafields->add_field( array(
						'name'      => __( 'URL van FLV-bestand', "rijksvideo-translate" ),
						'desc'      => __( 'Eindigt op .flv', "rijksvideo-translate" ),
						'id'        => RHSWP_CPT_VIDEO_PREFIX . 'flv_url',
						'type'      => 'text_url',
						'protocols' => array( 'http', 'https', '//' ), // Array of allowed protocols
					) );

					$cmb2_metafields->add_field( array(
						'name' => __( 'Bestandsgrootte van FLV-bestand', "rijksvideo-translate" ),
						'id'   => RHSWP_CPT_VIDEO_PREFIX . 'flv_filesize',
						'type' => 'text_small',
					) );

					$cmb2_metafields->add_field( array(
						'name'      => __( 'URL van WMV-bestand', "rijksvideo-translate" ),
						'desc'      => __( 'Windows Media File. Eindigt vaak op .wmv', "rijksvideo-translate" ),
						'id'        => RHSWP_CPT_VIDEO_PREFIX . 'wmv_url',
						'type'      => 'text_url',
						'protocols' => array( 'http', 'https', '//' ), // Array of allowed protocols
					) );

					$cmb2_metafields->add_field( array(
						'name' => __( 'Bestandsgrootte van WMV-bestand', "rijksvideo-translate" ),
						'id'   => RHSWP_CPT_VIDEO_PREFIX . 'filesize_wmv',
						'type' => 'text_small',
					) );

					require_once dirname( __FILE__ ) . '/inc/cmb2-check-required-fields.php';


				}


			}  // RHSWP_RV_USE_CMB2
			else {
				if ( function_exists( 'acf_add_local_field_group' ) ):

					acf_add_local_field_group( array(
						'key'                   => 'group_57ea177ac9849',
						'title'                 => 'Metadata voor ' . RHSWP_CPT_RIJKSVIDEO,
						'fields'                => array(
							array(
								'key'               => 'field_57ea1788c6162',
								'label'             => 'Lengte van de video',
								'name'              => RHSWP_CPT_VIDEO_PREFIX . 'video_time',
								'type'              => 'text',
								'instructions'      => '',
								'required'          => 0,
								'conditional_logic' => 0,
								'wrapper'           => array(
									'width' => '',
									'class' => '',
									'id'    => '',
								),
								'default_value'     => '',
								'placeholder'       => '',
								'prepend'           => '',
								'append'            => '',
								'maxlength'         => '',
							),
							array(
								'key'               => 'field_57ea17bd1c807',
								'label'             => 'Thumbnail URL',
								'name'              => RHSWP_CPT_VIDEO_PREFIX . 'url_video_thumb',
								'type'              => 'url',
								'instructions'      => '',
								'required'          => 0,
								'conditional_logic' => 0,
								'wrapper'           => array(
									'width' => '',
									'class' => '',
									'id'    => '',
								),
								'default_value'     => '',
								'placeholder'       => '',
							),
							array(
								'key'               => 'field_57ea17e81c808',
								'label'             => 'Ondertitel URL',
								'name'              => RHSWP_CPT_VIDEO_PREFIX . 'url_transcript_file',
								'type'              => 'url',
								'instructions'      => '',
								'required'          => 0,
								'conditional_logic' => 0,
								'wrapper'           => array(
									'width' => '',
									'class' => '',
									'id'    => '',
								),
								'default_value'     => '',
								'placeholder'       => '',
							),
							array(
								'key'               => 'field_57ea18001c809',
								'label'             => 'Transcript',
								'name'              => RHSWP_CPT_VIDEO_PREFIX . 'transcriptvlak',
								'type'              => 'textarea',
								'instructions'      => '',
								'required'          => 0,
								'conditional_logic' => 0,
								'wrapper'           => array(
									'width' => '',
									'class' => '',
									'id'    => '',
								),
								'default_value'     => '',
								'placeholder'       => '',
								'maxlength'         => '',
								'rows'              => '',
								'new_lines'         => 'wpautop',
							),
							array(
								'key'               => 'field_57ea18101c80a',
								'label'             => 'Audio URL',
								'name'              => RHSWP_CPT_VIDEO_PREFIX . 'audio_url',
								'type'              => 'url',
								'instructions'      => '',
								'required'          => 0,
								'conditional_logic' => 0,
								'wrapper'           => array(
									'width' => '',
									'class' => '',
									'id'    => '',
								),
								'default_value'     => '',
								'placeholder'       => '',
							),
							array(
								'key'               => 'field_57ea18211c80b',
								'label'             => 'Video (FLV) URL',
								'name'              => RHSWP_CPT_VIDEO_PREFIX . 'flv_url',
								'type'              => 'url',
								'instructions'      => '',
								'required'          => 0,
								'conditional_logic' => 0,
								'wrapper'           => array(
									'width' => '',
									'class' => '',
									'id'    => '',
								),
								'default_value'     => '',
								'placeholder'       => '',
							),
							array(
								'key'               => 'field_57ea18411c80c',
								'label'             => 'Bestandsgrootte (FLV)',
								'name'              => RHSWP_CPT_VIDEO_PREFIX . 'flv_filesize',
								'type'              => 'text',
								'instructions'      => '',
								'required'          => 0,
								'conditional_logic' => 0,
								'wrapper'           => array(
									'width' => '',
									'class' => '',
									'id'    => '',
								),
								'default_value'     => '',
								'placeholder'       => '',
								'prepend'           => '',
								'append'            => '',
								'maxlength'         => '',
							),
							array(
								'key'               => 'field_57ea18521c80d',
								'label'             => 'Video (WMV) URL',
								'name'              => RHSWP_CPT_VIDEO_PREFIX . 'wmv_url',
								'type'              => 'text',
								'instructions'      => '',
								'required'          => 0,
								'conditional_logic' => 0,
								'wrapper'           => array(
									'width' => '',
									'class' => '',
									'id'    => '',
								),
								'default_value'     => '',
								'placeholder'       => '',
								'prepend'           => '',
								'append'            => '',
								'maxlength'         => '',
							),
							array(
								'key'               => 'field_57ea189c1c80e',
								'label'             => 'Bestandsgrootte (WMV)',
								'name'              => RHSWP_CPT_VIDEO_PREFIX . 'filesize_wmv',
								'type'              => 'text',
								'instructions'      => '',
								'required'          => 0,
								'conditional_logic' => 0,
								'wrapper'           => array(
									'width' => '',
									'class' => '',
									'id'    => '',
								),
								'default_value'     => '',
								'placeholder'       => '',
								'prepend'           => '',
								'append'            => '',
								'maxlength'         => '',
							),
							array(
								'key'               => 'field_57ea18b11c80f',
								'label'             => 'Video (MP4) URL',
								'name'              => RHSWP_CPT_VIDEO_PREFIX . 'mp4_url',
								'type'              => 'url',
								'instructions'      => '',
								'required'          => 0,
								'conditional_logic' => 0,
								'wrapper'           => array(
									'width' => '',
									'class' => '',
									'id'    => '',
								),
								'default_value'     => '',
								'placeholder'       => '',
							),
							array(
								'key'               => 'field_57ea18d01c810',
								'label'             => 'Bestandsgrootte (MP4)',
								'name'              => RHSWP_CPT_VIDEO_PREFIX . 'mp4_filesize',
								'type'              => 'text',
								'instructions'      => '',
								'required'          => 0,
								'conditional_logic' => 0,
								'wrapper'           => array(
									'width' => '',
									'class' => '',
									'id'    => '',
								),
								'default_value'     => '',
								'placeholder'       => '',
								'prepend'           => '',
								'append'            => '',
								'maxlength'         => '',
							),
							array(
								'key'               => 'field_57ea18f793b47',
								'label'             => 'Video (MP4 High Resolution) URL',
								'name'              => RHSWP_CPT_VIDEO_PREFIX . 'mp4_url_hires',
								'type'              => 'url',
								'instructions'      => '',
								'required'          => 0,
								'conditional_logic' => 0,
								'wrapper'           => array(
									'width' => '',
									'class' => '',
									'id'    => '',
								),
								'default_value'     => '',
								'placeholder'       => '',
							),
							array(
								'key'               => 'field_57ea191793b48',
								'label'             => 'Bestandsgroote (MP4 High Resolution)',
								'name'              => RHSWP_CPT_VIDEO_PREFIX . 'mp4_filesize_hires',
								'type'              => 'text',
								'instructions'      => '',
								'required'          => 0,
								'conditional_logic' => 0,
								'wrapper'           => array(
									'width' => '',
									'class' => '',
									'id'    => '',
								),
								'default_value'     => '',
								'placeholder'       => '',
								'prepend'           => '',
								'append'            => '',
								'maxlength'         => '',
							),
							array(
								'key'               => 'field_57ea193193b49',
								'label'             => 'Video (3GP) URL',
								'name'              => RHSWP_CPT_VIDEO_PREFIX . '3gp_url',
								'type'              => 'url',
								'instructions'      => '',
								'required'          => 0,
								'conditional_logic' => 0,
								'wrapper'           => array(
									'width' => '',
									'class' => '',
									'id'    => '',
								),
								'default_value'     => '',
								'placeholder'       => '',
							),
							array(
								'key'               => 'field_57ea194493b4a',
								'label'             => 'Bestandsgrootte (3GP)',
								'name'              => RHSWP_CPT_VIDEO_PREFIX . '3gp_filesize',
								'type'              => 'text',
								'instructions'      => '',
								'required'          => 0,
								'conditional_logic' => 0,
								'wrapper'           => array(
									'width' => '',
									'class' => '',
									'id'    => '',
								),
								'default_value'     => '',
								'placeholder'       => '',
								'prepend'           => '',
								'append'            => '',
								'maxlength'         => '',
							),
						),
						'location'              => array(
							array(
								array(
									'param'    => 'post_type',
									'operator' => '==',
									'value'    => RHSWP_CPT_RIJKSVIDEO,
								),
							),
						),
						'menu_order'            => 0,
						'position'              => 'normal',
						'style'                 => 'default',
						'label_placement'       => 'top',
						'instruction_placement' => 'label',
						'hide_on_screen'        => '',
						'active'                => 1,
						'description'           => '',
					) );

				endif;

			}  // else RHSWP_RV_USE_CMB2
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
		 *
		 */
		public function update_video() {

			check_admin_referer( "rijksvideo_update_video" );

			$capability = apply_filters( 'rijksvideo_capability', 'edit_others_posts' );

			if ( ! current_user_can( $capability ) ) {
				return;
			}

			$rijksvideo_id = absint( $_POST['rijksvideo_id'] );

			if ( ! $rijksvideo_id ) {
				return;
			}

			// update settings
			if ( isset( $_POST['settings'] ) ) {

				$new_settings = $_POST['settings'];

				$old_settings = get_post_meta( $rijksvideo_id, 'rijksvideo_settings', true );

				// convert submitted checkbox values from 'on' or 'off' to boolean values
				$checkboxes = apply_filters( "rijksvideo_checkbox_settings", array(
					'noConflict',
					'fullWidth',
					'hoverPause',
					'links',
					'reverse',
					'random',
					'printCss',
					'printJs',
					'smoothHeight',
					'center',
					'carouselMode',
					'autoPlay'
				) );

				foreach ( $checkboxes as $checkbox ) {
					if ( isset( $new_settings[ $checkbox ] ) && $new_settings[ $checkbox ] == 'on' ) {
						$new_settings[ $checkbox ] = "true";
					} else {
						$new_settings[ $checkbox ] = "false";
					}
				}

				$settings = array_merge( (array) $old_settings, $new_settings );

				// update the video settings
				update_post_meta( $rijksvideo_id, 'rijksvideo_settings', $settings );

			}

			// update video title
			if ( isset( $_POST['title'] ) ) {

				$video = array(
					'ID'         => $rijksvideo_id,
					'post_title' => esc_html( $_POST['title'] )
				);

				wp_update_post( $video );

			}

			// update individual video
			if ( isset( $_POST['attachment'] ) ) {

				foreach ( $_POST['attachment'] as $video_id => $fields ) {
					do_action( "rijksvideo_save_{$fields['type']}_video", $video_id, $rijksvideo_id, $fields );
				}

			}

		}


		/**
		 * Get all videos. Returns an array of
		 * published videos.
		 *
		 * @param string $sort_key
		 *
		 * @return an array of published videos
		 */
		public function get_all_videos( $sort_key = 'date' ) {

			$rijksvideos = array();

			// list the tabs
			$args = array(
				'post_type'        => RHSWP_CPT_RIJKSVIDEO,
				'post_status'      => 'publish',
				'orderby'          => $sort_key,
				'suppress_filters' => 1, // wpml, ignore language filter
				'order'            => 'ASC',
				'posts_per_page'   => - 1
			);

			$args = apply_filters( 'rijksvideo_get_all_videos_args', $args );

			// WP_Query causes issues with other plugins using admin_footer to insert scripts
			// use get_posts instead
			$videos = get_posts( $args );

			foreach ( $videos as $video ) {

				$rijksvideos[] = array(
//                'active'  => $active,
					'title' => $video->post_title,
					'id'    => $video->ID
				);

			}

			return $rijksvideos;

		}


		/**
		 * Append the 'Add video' button to selected admin pages
		 */
		public function admin_insert_rijksvideo_button( $context ) {

			$capability = apply_filters( 'rijksvideo_capability', 'edit_others_posts' );

			if ( ! current_user_can( $capability ) ) {
				return $context;
			}

			global $pagenow;

			$posttype = 'post';

			if ( isset( $_GET['post'] ) ) {

				$posttype = get_post_type( $_GET['post'] );

			}
			if ( isset( $_GET['post_type'] ) ) {

				$posttype = $_GET['post_type'];

			}

			$available_post_types = get_post_types();

			$allowed_post_types = array();

			// to do: possibility to exclude some post types to allow for the insert video button
			foreach ( $available_post_types as $available_post_type ) {
				if ( defined( 'RHSWP_CPT_TIMELINE' ) && $available_post_type === RHSWP_CPT_TIMELINE ) {
					continue;
				}
				if ( $available_post_type !== RHSWP_CPT_RIJKSVIDEO ) {
					array_push( $allowed_post_types, $available_post_type );
				}
			}

			if ( ( in_array( $pagenow, array(
					'post.php',
					'page.php',
					'post-new.php',
					'post-edit.php'
				) ) ) && ( in_array( $posttype, $allowed_post_types ) ) ) {
				$context .= '<a href="#TB_inline?&inlineId=choose-video-selector-screen" class="thickbox button" title="' .
				            __( "Selecteer een rijksvideo om in dit bericht in te voegen.", "rijksvideo-translate" ) .
				            '"><span class="wp-media-buttons-icon" style="background: url(' . RIJKSVIDEO_ASSETS_URL . 'images/icon-video.png); background-repeat: no-repeat; background-size: 16px 16px; background-position: center center;"></span> ' .
				            __( "Voeg rijksvideo in", "rijksvideo-translate" ) . '</a>';
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
                    jQuery(document).ready(function () {
                        jQuery('#insert_video').on('click', function () {
                            var id = jQuery('#rijksvideo-select option:selected').val();
                            window.send_to_editor('[<?php echo RHSWP_CPT_RIJKSVIDEO ?> id=' + id + ']');
                            tb_remove();
                        })
                    });
                </script>

                <div id="choose-video-selector-screen" style="display: none;">
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
							echo "<button class='button primary' id='insert_video'>" . __( "Select and insert video", "rijksvideo-translate" ) . "</button>";
						} else {
							_e( "No videos found", "rijksvideo-translate" );
						}
						?>
                    </div>
                </div>

				<?php
			}
		}
	}


endif;

add_action( 'plugins_loaded', array( 'RijksvideoPlugin_v1', 'init' ), 10 );


function rovidcheck( $inputstring = '' ) {
	$inputstring = preg_replace( "|^http://www.rovid.nl|i", "https://www.rovid.nl", $inputstring );

	return $inputstring;
}
