<?php
/**
 * RTL RSS Feed Fixer
 *
 * @package   rtl-feed-fixer
 * @author    Ran Bar-Zik <ran@bar-zik.com>
 * @license   GPL-2.0+
 * @link      http://internet-israel.com
 * @copyright 2014 Ran Bar-Zik
 *
 * @wordpress-plugin
 * Plugin Name:       RTL RSS Feed Fixer
 * Plugin URI:        http://internet-israel.com
 * Description:       Allowing RTL feed support for all RSS readers
 * Version:           1.0.2
 * Author:            Ran Bar-Zik <ran@bar-zik.com>
 * Author URI:        http://internet-israel.com
 * Text Domain:       rtl-feed-fixer
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/barzik/rtl-feed-fixer
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) exit;


add_action( 'plugins_loaded', array( 'RtlFeedFixer', 'get_instance' ) );

class RtlFeedFixer {

    protected $plugin_slug = 'rtl-feed-fixer';

    /**
     * Instance of this class.
     *
     * @since    1.0.0
     *
     * @var      object
     */
    protected static $instance = null;

    /**
     * Initialize the plugin by loading admin scripts & styles and adding a
     * settings page and menu.
     *
     * @since     1.0.0
     */
    private function __construct() {

        // Load plugin text domain
        //add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

        // Activate plugin when new blog is added
        add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );



        /*
         * Define custom functionality.
         *
         * Read more about actions and filters:
         * http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
         */
        add_filter( "the_content_feed", array( $this, 'add_rtl_to_p' ) );
        add_filter( "the_excerpt_rss", array( $this, 'add_rtl_to_p' ) );


    }

    /**
     *
     * Getting HTML string and adding style and dir=rtl to every paragraph. That should do the trick.
     *
     * @since 1.0.0
     * @param $content
     * @return mixed|string
     */


    public function add_rtl_to_p( $content ) {

        if ( class_exists( 'DOMDocument' ) && version_compare( phpversion(), '5.4', '>' ) ) {
            $content = mb_convert_encoding( $content, 'utf-8', mb_detect_encoding( $content ) );
            $content = mb_convert_encoding( $content, 'html-entities', 'utf-8' );
            $dom = new DOMDocument();
            $dom->loadHTML( $content,  LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
            $x = new DOMXPath( $dom );

            foreach( $x->query( "//p" ) as $node ) {
                $node->setAttribute( 'dir', 'rtl' );
                $node->setAttribute( 'style', 'direction: rtl; text-align: right;' );
            }
            $content = trim( $dom->saveHtml() );
        } else {
            $content = str_replace( '<p>', '<p dir="rtl" style="direction: rtl; text-align: right;">', $content );
        }

        return $content;

    }

    /**
     * Return an instance of this class.
     *
     * @since     1.0.0
     *
     * @return    object    A single instance of this class.
     */
    public static function get_instance() {

        // If the single instance hasn't been set, set it now.
        if ( null === self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Return the plugin slug.
     *
     * @since    1.0.0
     *
     * @return    RtlFeedFixer slug variable.
     */
    public function get_plugin_slug() {
        return $this->plugin_slug;
    }


    /**
     * Fired when the plugin is activated.
     *
     * @since    1.0.0
     *
     * @param    boolean    $network_wide    True if WPMU superadmin uses
     *                                       "Network Activate" action, false if
     *                                       WPMU is disabled or plugin is
     *                                       activated on an individual blog.
     */
    public static function activate( $network_wide ) {

        if ( function_exists( 'is_multisite' ) && is_multisite() ) {

            if ( $network_wide  ) {

                // Get all blog ids
                $blog_ids = self::get_blog_ids();

                foreach ( $blog_ids as $blog_id ) {

                    switch_to_blog( $blog_id );

                    restore_current_blog();
                }
            }
        }

    }

    /**
     * Fired when the plugin is deactivated.
     *
     * @since    1.0.0
     *
     * @param    boolean    $network_wide    True if WPMU superadmin uses
     *                                       "Network Deactivate" action, false if
     *                                       WPMU is disabled or plugin is
     *                                       deactivated on an individual blog.
     */
    public static function deactivate( $network_wide ) {

        if ( function_exists( 'is_multisite' ) && is_multisite() ) {

            if ( $network_wide ) {

                // Get all blog ids
                $blog_ids = self::get_blog_ids();

                foreach ( $blog_ids as $blog_id ) {

                    switch_to_blog( $blog_id );
                    self::single_deactivate();

                    restore_current_blog();

                }

            } else {
                self::single_deactivate();
            }

        } else {
            self::single_deactivate();
        }

    }

    /**
     * Fired when a new site is activated with a WPMU environment.
     *
     * @since    1.0.0
     *
     * @param    int    $blog_id    ID of the new blog.
     */
    public function activate_new_site( $blog_id ) {

        if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
            return;
        }

        switch_to_blog( $blog_id );
        restore_current_blog();

    }

    /**
     * Clearing out the data
     */

    private static function single_deactivate() {
        delete_option( self::$plugin_slug );
    }


    /**
     * Get all blog ids of blogs in the current network that are:
     * - not archived
     * - not spam
     * - not deleted
     *
     * @since    1.0.0
     *
     * @return   array|false    The blog ids, false if no matches.
     */
    private static function get_blog_ids() {

        global $wpdb;

        // get an array of blog ids
        $sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

        return $wpdb->get_col( $sql );

    }


    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
//    public function load_plugin_textdomain() {
//
//        $domain = $this->plugin_slug;
//        $locale = apply_filters( 'plugin_locale', get_locale(), $domain );
//
//        load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
//        load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );
//
//    }


}
