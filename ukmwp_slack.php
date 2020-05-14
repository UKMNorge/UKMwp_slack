<?php  
/* 
Plugin Name: UKM Slack
Plugin URI: http://www.ukm-norge.no
Description: Slack-hjelpere for arrangørsystemet
Author: UKM Norge / M Mandal
Version: 0.1
Author URI: http://www.ukm-norge.no
*/

use UKMNorge\Wordpress\Modul;

require_once('UKM/Autoloader.php');

class UKMwp_slack extends Modul {
    static $action = 'dashboard';
    static $path_plugin;

    /**
     * Hook inn på wordpress
     *
     * @return void
     */
    public static function hook() {
        add_action(
            'network_admin_menu',
            [ static::class, 'meny'],
            1000
        );
    }

    /**
     * Legg til menyelementer
     *
     * @return void
     */
    public static function meny() {
        $scripts[] = add_submenu_page(
            'UKMsystem_tools',
            'Slack',
            'Slack',
            'superadmin',
            'UKMslack',
            [static::class, 'renderAdmin']
        );

        foreach( $scripts as $scripthook ) {
            add_action( 
                'admin_print_styles-' . $scripthook,
                [static::class, 'scripts_and_styles']
            );
        }
    }

    /**
     * Legg til scripts and styles
     *
     * @return void
     */
    public static function scripts_and_styles() {
        wp_enqueue_style('WPbootstrap3_css');
    }
}

UKMwp_slack::init(__DIR__);
UKMwp_slack::hook();