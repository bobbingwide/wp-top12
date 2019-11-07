<?php


/**
 * @copyright (C) Copyright Bobbing Wide 2019
 * @package wp-top12
 */

/**
 * Implement [wp-top12] shortcode 
 *
 * @param array $atts shortcode parameters
 * @param string $content - not implemented
 * @param string $tag 
 * @return string - the result of the shortcode expansion  
 */
function wp_top12_sc( $atts=null, $content=null, $tag=null ) {
	$limit = bw_array_get_from( $atts, 'limit,0', null );
	$includes = bw_array_get( $atts, 'includes', null );
	$excludes = bw_array_get( $atts, 'excludes', null );
	$form = bw_array_get( $atts, 'form', 'N' );
	$both = bw_array_get( $atts, 'both', 'N' );

	$form = bw_validate_torf( $form );
	$both = bw_validate_torf( $both );
	if ( $form ) {
		$result = _wp_top12_form( $atts );
	} else {
		$result = _wp_top12_static( $atts );
	}
	return $result;
}

/**
 * Display the original text and the wp-top12'ed text
 * 
 * @param string $text - the text to be "bboinged"
 * @param bool $both - whether or not to display the original text and the result
 * @return string - the result
 */
function _wp_top12_static( $atts ) {
    oik_require( 'class-wp-org-plugins.php', 'wp-top12');
    $top12 = new WP_org_plugins();
    return bw_ret();
}




/** 
 * Return the text to be wp-top12'ed with some sanitization
 * 
 * Nonce verification is also performed. 
 * 
 * @param string $text - default text
 * @return string - sanitized text
 */
function _wp_top12_get( $text=null ) {
  $verified = bw_verify_nonce( "_wp_top12_form", "_wp_top12_nonce" );
  if ( $verified ) {
    $new_text = bw_array_get( $_REQUEST, "_wp_top12_text", $text );
    $new_text = str_replace( "\n", "&#8288;", $new_text );
    $new_text = strip_tags( $new_text );
    $new_text = stripslashes( $new_text ); 
  } else {
    $new_text = $text;
  }    
  return( $new_text );
}     
 
/**
 * Display the  wp-top12 form
 * 
 *  *
 * @param string $text 
 * @param array $atts 
 * @return string generated HTML 
 */
function _wp_top12_form( $text=null, $atts=null ) {
	bw_context( "textdomain", "wp-top12" );
	gob();
  oik_require( "bobbforms.inc" );
  $text = _wp_top12_get( $text );  
  $cols = bw_array_get( $atts, "cols", 80 );
  bw_form();
  p( "Type the text you want to obfuscate then click on 'wp-top12 it'" );
  bw_textarea( "_wp_top12_text", $cols, null, $text );
  br();
  e( wp_nonce_field( "_wp_top12_form", "_wp_top12_nonce", false, false ) );
  e( isubmit( "_wp_top12_ok", __( "wp-top12 it", "wp-top12" ) ) );
  p("This is the result of <i>wp-top12</i>ing the text." ); 
  bw_textarea( "_wp-top12ed", $cols, null ,   wp-top12( $text ) );
  p( "If you're not happy with the result just click on 'wp-top12 it'!" );
  etag( "form" );
	
	bw_context( "textdomain", false );
  return( bw_ret());
}

/** 
 * wp-top12 some text
 * 
 * Note: In its first version this routine would transform "http://www.bobbingwide.com" to something pretty nasty
 * similarly it could ruin any HTML tags or anything with %1$s
 * To overcome this we're going to improve the bboing() function... replacing it with the new function bboing2()
 * 
 * @param string - the text to be wp-top12'ed
 * @return string - the wp-top12'ed text
 */
function wp_top12( $limit, $includes, $excludes ) {
	e( $limit );
	e( $includes );
	e( $excludes );
}




/** 
 * Help for wp-top12
 */
function wp_top12__help( ) {
 return( __( "Display top 12 plugins matching selection", "wp-top12") );
} 

/**
 * Syntax for wp-top12
 * 
 */
function wp_top12__syntax( ) {
  $syntax = array( 'limit' => bw_skv( null, 'integer', 'Maximum results to display'),
  	                'includes' => bw_skv( null, 'word,word2', 'Words to search for' ),
  	                'excludes' => bw_skv( null, 'word,word2', 'Words to exclude'),
  	                "form" => bw_skv( "N", "Y", "display form to allow changes" )
                 , "both" => bw_skv( "N", "Y", "display both original and output text" )
                 );
  return( $syntax );
}

/**
 * Example for the wp-top12 shortcode
 *  
 * [wp-top12 text="and this is where it shows the before and after" both='Y']
 * 
 */
function wp_top12__example( ) {
  //oik_require( "/shortcodes/wp-top12.php", "wp-top12" );
  _wp_top12_static( null );

}             


 

