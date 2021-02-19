<?php // (C) Copyright Bobbing Wide 2017, 2019

class Tests_translate extends BW_UnitTestCase {

	/**
	 * set up logic
	 *
	 * - ensure any database updates are rolled back
	 */
	function setUp(): void {
		parent::setUp();
		//bobbcomp::bw_get_option( "fred" );
		//oik_require_lib( "class-BW-" );
		//oik_require_lib( "bobbfunc" );

	}

	/**
	 * We want to test in en_GB since we want translations to be performed
	 * The trouble is, in en_GB null translates to "0" ?
	 * I've raised #41257 against this problem.
	 */

	function test_locale() {
		$locale = is_admin() ? get_user_locale() : get_locale();
		$this->assertEquals( false, is_admin() );
		$this->assertEquals( "en_GB", $locale );
	}

	/**
	 * Confirms that "0" is not in the default domain
	 *
	 * Should continue to pass even when #41257 is fixed
	 */
	function test_translate_null_default() {
		$actual = translate( null, "default" );
		$expected = "";
		$this->assertEquals( $expected, $actual );
	}

	/**
	 * For the test for issue #41257 see oik/tests/test-bw-translate.php
	 *
	 * This test will pass if the oik text domain doesn't have "0" as a translatable string.
	 * If it does have a translation for "0" then we'll get whatever the translation is.
	 * Which is unlikely to be blank.
	 *
	 */
	function test_translate_null_not_default() {
		$actual = translate( null, "oik" );
		$expected = "";
		$this->assertEquals( $expected, $actual );
	}

	/**
	 * This test is also dependent upon there being a "0" translatable string in the oik text domain
	 * Again see the test for #41257.
	 */
	function test_translate_null_string_not_default() {
		$actual = translate( "", "oik" );
		$expected = "";
		$this->assertEquals( $expected, $actual );
	}

	function test_translate_blank_string_not_default() {
		$actual = translate( " ", "oik" );
		$expected = " ";
		$this->assertEquals( $expected, $actual );
	}

	function test_translate_zero_not_default() {
		$actual = translate( 0, "oik" );
		$expected = "0";
		$this->assertEquals( $expected, $actual );
	}


	function test_translate_one_not_default() {
		$actual = translate( 1, "oik" );
		$expected = "1";
		$this->assertEquals( $expected, $actual );
	}

	/**
	 * I used this to see what was in l10n
	 * Confirming that the "oik" domain contained a translation for "0"
	 *
	 * @TODO Should be converted into a proper test

	function test_l10n() {
		global $l10n;
		//print_r( $l10n );
		$this->assertNot
	}
	*/




}
