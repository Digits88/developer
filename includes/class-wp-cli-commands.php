<?php
/**
 * Developer Plugin commands for WP-CLI.
 * @since 1.2
 */
class Developer_Command extends WP_CLI_Command {

	/**
	 * Sets up Developer Plugin
	 * @subcommand install-plugins
	 * @synopsis --type=<type> [--activate]
	 */
	function install_plugins( $args, $assoc_args ) {
		global $automattic_developer;
		$type = $assoc_args['type'];
		$activate = isset( $assoc_args['activate'] ) && $assoc_args['activate'] == "true";

		$reco_plugins = $automattic_developer->recommended_plugins;
		$installed_plugins = array_keys( get_plugins() );
		$types = array_keys( $automattic_developer->get_project_types() );

		if ( in_array( $type, $types ) ) {
			foreach ( $reco_plugins as $slug => $plugin ) {
				$path = $automattic_developer->get_path_for_recommended_plugin( $slug );
				$activate = $activate && ( 'all' == $plugin['project_type'] || $type == $plugin['project_type'] );

				// Download the plugin if we don't already have it
				if ( ! in_array( $path, $installed_plugins ) )
					WP_CLI::run_command( explode( " ", "plugin install $slug" ) );

				// Install the plugin if --activate and it's the right type
				if ( is_plugin_inactive( $path ) && $activate ) {
					if ( NULL == activate_plugin( $path ) )
						WP_CLI::success( "Activated " . $plugin['name'] );
				}
			}
		} else {
			WP_CLI::error( "Specify a valid type to install: <" . implode( "|", $types ) . ">" );
		}
	}

}

WP_CLI::add_command( 'developer', 'Developer_Command' );
