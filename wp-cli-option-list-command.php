<?php

/**
 * List options based on a varios factors.
 *
 */
class WP_CLI_Option_List extends WP_CLI_Command {

	/**
	 * List options.
	 *
	 * [--search=<sql-like-pattern>]
	 * : SQL pattern matching enables you to use "_" to match any single character and "%" to match an arbitrary number of characters.
	 *
	 * [--fields=<fields>]
	 * : Limit the output to specific object fields.
	 *
	 * [--autoload]
	 * : Match only autoload options.
	 *
	 * [--total]
	 * : Display only the total size of matching options.
	 *
	 * [--format=<format>]
	 * : The serialization format for the value. Default is table.
	 *
	 * ## AVAILABLE FIELDS
	 *
	 * This field will be displayed by default for each matching option:
	 *
	 * * option_name
	 *
	 * These fields are optionally available:
	 *
	 * * option_name
	 * * autoload
	 * * size
	 *
	 * @synopsis [--search=<sql-like-pattern>] [--total] [--autoload] [--fields=<fields>] [--format=<format>]
	 */
	public function __invoke( $args, $assoc_args ) {

		global $wpdb;
		$size_query = "LENGTH(option_value) AS size";
		$autoload_query = '';

		if ( isset( $assoc_args['search'] ) ) {
			$pattern = $assoc_args['search'];
		} else {
			$pattern = '%';
		}

		if ( isset( $assoc_args['fields'] ) ) {
			$fields = explode( ',', $assoc_args['fields'] );
		} else {
			$fields = array( 'option_name' );
		}

		if ( isset( $assoc_args['total'] ) ) {
			$fields = array( 'size' );
			$size_query = "SUM(LENGTH(option_value)) AS size";
		}

		if ( isset( $assoc_args['autoload'] ) ) {
			$autoload_query = " AND autoload='yes'";
		}

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name,option_value,autoload," . $size_query
					. " FROM $wpdb->options WHERE option_name LIKE %s" . $autoload_query,
				$pattern
			)
		);

		$formatter = new \WP_CLI\Formatter(
			$assoc_args,
			$fields
		);
		$formatter->display_items( $results );
	}

}

WP_CLI::add_command( 'option list', 'WP_CLI_Option_List' );
