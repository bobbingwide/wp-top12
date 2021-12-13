<?php

/**
 * Reimplements themes_api prior to version 5.0
 *
 * @param $action
 * @param array $args
 * @return mixed|void
 *
 */
function themes_api_v10( $action, $args = array() ) {
	// Include an unmodified $wp_version.
	require ABSPATH . WPINC . '/version.php';

	if ( is_array( $args ) ) {
		$args = (object) $args;
	}

	if ( 'query_themes' === $action ) {
		if ( ! isset( $args->per_page ) ) {
			$args->per_page = 24;
		}
	}

	if ( ! isset( $args->locale ) ) {
		$args->locale = get_user_locale();
	}

	if ( ! isset( $args->wp_version ) ) {
		$args->wp_version = substr( $wp_version, 0, 3 ); // x.y
	}

	/**
	 * Filters arguments used to query for installer pages from the WordPress.org Themes API.
	 *
	 * Important: An object MUST be returned to this filter.
	 *
	 * @since 2.8.0
	 *
	 * @param object $args   Arguments used to query for installer pages from the WordPress.org Themes API.
	 * @param string $action Requested action. Likely values are 'theme_information',
	 *                       'feature_list', or 'query_themes'.
	 */
	//$args = apply_filters( 'themes_api_args', $args, $action );

	/**
	 * Filters whether to override the WordPress.org Themes API.
	 *
	 * Passing a non-false value will effectively short-circuit the WordPress.org API request.
	 *
	 * If `$action` is 'query_themes', 'theme_information', or 'feature_list', an object MUST
	 * be passed. If `$action` is 'hot_tags', an array should be passed.
	 *
	 * @since 2.8.0
	 *
	 * @param false|object|array $override Whether to override the WordPress.org Themes API. Default false.
	 * @param string             $action   Requested action. Likely values are 'theme_information',
	 *                                    'feature_list', or 'query_themes'.
	 * @param object             $args     Arguments used to query for installer pages from the Themes API.
	 */
	$res = apply_filters( 'themes_api', false, $action, $args );

	if ( ! $res ) {
		$url = 'http://api.wordpress.org/themes/info/1.2/';
		$url = add_query_arg(
			array(
				'action'  => $action,
				'request' => $args,
			),
			$url
		);

		$http_url = $url;
		$ssl      = wp_http_supports( array( 'ssl' ) );
		if ( $ssl ) {
			$url = set_url_scheme( $url, 'https' );
		}

		$http_args = array(
			'user-agent' => 'WordPress/' . $wp_version . '; ' . home_url( '/' ),
		);
		$request   = wp_remote_get( $url, $http_args );

		if ( $ssl && is_wp_error( $request ) ) {
			if ( ! wp_doing_ajax() ) {
				trigger_error(
					sprintf(
					/* translators: %s: Support forums URL. */
						__( 'An unexpected error occurred. Something may be wrong with WordPress.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="%s">support forums</a>.' ),
						__( 'https://wordpress.org/support/forums/' )
					) . ' ' . __( '(WordPress could not establish a secure connection to WordPress.org. Please contact your server administrator.)' ),
					headers_sent() || WP_DEBUG ? E_USER_WARNING : E_USER_NOTICE
				);
			}
			$request = wp_remote_get( $http_url, $http_args );
		}

		if ( is_wp_error( $request ) ) {
			$res = new WP_Error(
				'themes_api_failed',
				sprintf(
				/* translators: %s: Support forums URL. */
					__( 'An unexpected error occurred. Something may be wrong with WordPress.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="%s">support forums</a>.' ),
					__( 'https://wordpress.org/support/forums/' )
				),
				$request->get_error_message()
			);
		} else {
			$res = json_decode( wp_remote_retrieve_body( $request ), true );
			if ( is_array( $res ) ) {
				// Object casting is required in order to match the info/1.0 format.
				$res = (object) $res;
			} elseif ( null === $res ) {
				$res = new WP_Error(
					'themes_api_failed',
					sprintf(
					/* translators: %s: Support forums URL. */
						__( 'An unexpected error occurred. Something may be wrong with WordPress.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="%s">support forums</a>.' ),
						__( 'https://wordpress.org/support/forums/' )
					),
					wp_remote_retrieve_body( $request )
				);
			}

			if ( isset( $res->error ) ) {
				$res = new WP_Error( 'themes_api_failed', $res->error );
			}
		}

		// Back-compat for info/1.2 API, upgrade the theme objects in query_themes to objects.
		if ( 'query_themes' === $action ) {
			foreach ( $res->themes as $i => $theme ) {
				$res->themes[ $i ] = (object) $theme;
			}
		}
		// Back-compat for info/1.2 API, downgrade the feature_list result back to an array.
		if ( 'feature_list' === $action ) {
			$res = (array) $res;
		}
	}

	/**
	 * Filters the returned WordPress.org Themes API response.
	 *
	 * @since 2.8.0
	 *
	 * @param array|object|WP_Error $res    WordPress.org Themes API response.
	 * @param string                $action Requested action. Likely values are 'theme_information',
	 *                                      'feature_list', or 'query_themes'.
	 * @param object                $args   Arguments used to query for installer pages from the WordPress.org Themes API.
	 */
	//return apply_filters( 'themes_api_result', $res, $action, $args );
	return $res;
}

