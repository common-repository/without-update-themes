<?php
/*
Plugin Name: Without Update Themes
Plugin URI: 
Description: This plugin does not update the theme.
Author: 8suzuran8
Author URI: https://profiles.wordpress.org/8suzuran8/
Version: 1.0.2
Text Domain: without-update-themes
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if ( is_admin() ) {
	function without_update_themes_deactivation() {
		delete_option( 'without_update_themes_option_name' );
	}

	register_deactivation_hook( __FILE__, 'without_update_themes_deactivation');

	add_action( 'admin_menu', 'without_update_themes_add_plugin_page' );
	add_action( 'admin_init', 'without_update_themes_page_init' );

	function without_update_themes_add_plugin_page() {
		add_plugins_page(
			__( 'Without Update Themes 設定', 'without-update-themes' ),
			__( 'Without Update Themes 設定', 'without-update-themes' ),
			'manage_options',
			'without-update-themes-setting-admin',
			'without_update_themes_create_admin_page'
		);
	}

	function without_update_themes_create_admin_page() {
		?>
		<style>
			#without-update-themes th,
			#without-update-themes td {
				padding: .5em 0;
			}
		</style>
		<div id="without-update-themes">
		<form method="post" action="options.php">
		<?php settings_fields( 'without_update_themes_option_group' ); ?>
		<?php do_settings_sections( 'without-update-themes-setting-admin' ); ?>
		<p><?php _e( '反映に時間がかかる場合があります。', 'without-update-themes' ); ?></p>
		<?php submit_button(); ?>
		</form>
		</div>
		<?php
	}

	function without_update_themes_page_init() {
		register_setting(
			'without_update_themes_option_group', // Option group
			'without_update_themes_option_name' // Option name
		);

		add_settings_section(
			'without_update_themes_setting_section_id', // ID
			__( 'Without Update Themes 設定', 'without-update-themes' ), // Title
			'without_update_themes_print_section_info', // Callback
			'without-update-themes-setting-admin' // Page
		);

		$installed_themes = array_keys( wp_get_themes() );

		foreach ( $installed_themes as $installed_theme ) {
			add_settings_field(
				$installed_theme, // ID
				$installed_theme, // Title
				'without_update_themes_callback', // Callback
				'without-update-themes-setting-admin', // Page
				'without_update_themes_setting_section_id', // Section
				$installed_theme
			);
		}
	}

	function without_update_themes_print_section_info() {
		_e( '更新しないテーマを選択してください', 'without-update-themes' );
	}

	function without_update_themes_callback( $args ) {
		$without_update_themes = get_option( 'without_update_themes_option_name' );

		if ( !is_array( $without_update_themes ) || !array_key_exists( $args, $without_update_themes ) ) {
			echo '<input type="checkbox" name="without_update_themes_option_name[' . $args . ']" value="1" />';
		} else {
			echo '<input type="checkbox" name="without_update_themes_option_name[' . $args . ']" value="1"' . checked( $without_update_themes[ $args ], 1, false ) . ' />';
		}
	}

	// select themes without update
	function graftee_site_transient_update_theme( $value ) {
		$without_update_themes = get_option( 'without_update_themes_option_name' );
		if ( !is_array( $without_update_themes ) ) {
			$without_update_themes = array();
		}

		foreach ( $without_update_themes as $without_update_theme_name => $without_update_theme_value ) {
			if ( array_key_exists( $without_update_theme_name, $value->response )
			&& $without_update_theme_value == 1 ) {
				unset( $value->response[ $without_update_theme_name ] );
			} else {
				unset( $value->checked[ $without_update_theme_name ] );
			}
		}

		$value->checked = array();

		return $value;
	}

	add_filter( 'pre_set_site_transient_update_themes', 'graftee_site_transient_update_theme' );
}