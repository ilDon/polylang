<?php

class Flags_Test extends PLL_UnitTestCase {

	/**
	 * Instance of the Polylang context for these tests.
	 *
	 * @var PLL_Frontend
	 */
	private $pll_env;

	/**
	 * Language properties from {@see PLL_Settings::get_predefined_languages()} to be added as a new language.
	 *
	 * @var array
	 */
	private static $new_language;

	/**
	 * Path to a custom flag image.
	 *
	 * @var string
	 */
	private static $flag_de_ch_informal = WP_CONTENT_DIR . '/polylang/de_CH_informal.png';

	static function wpSetUpBeforeClass() {
		parent::wpSetUpBeforeClass();

		self::create_language( 'en_US' );
		self::create_language( 'fr_FR' );

		@mkdir( WP_CONTENT_DIR . '/polylang' );
		copy( dirname( __FILE__ ) . '/../data/fr_FR.png', WP_CONTENT_DIR . '/polylang/fr_FR.png' );

		self::$new_language = PLL_Settings::get_predefined_languages()['de_CH'];
		copy( dirname( __FILE__ ) . '/../data/de_CH.png', WP_CONTENT_DIR . '/polylang/de_CH.png' );
	}

	static function wpTearDownAfterClass() {
		parent::wpTearDownAfterClass();

		unlink( WP_CONTENT_DIR . '/polylang/fr_FR.png' );
		unlink( WP_CONTENT_DIR . '/polylang/de_CH.png' );
		if ( file_exists( self::$flag_de_ch_informal ) ) {
			unlink( self::$flag_de_ch_informal );
		}
		rmdir( WP_CONTENT_DIR . '/polylang' );
	}

	function setUp() {
		parent::setUp();

		$options       = array_merge( PLL_Install::get_default_options(), array( 'default_lang' => 'en_US' ) );
		$model         = new PLL_Model( $options );
		$links_model   = new PLL_Links_Default( $model );
		$this->pll_env = new PLL_Frontend( $links_model );
		$this->pll_env->init();
		$this->pll_env->model->cache->clean();
	}

	function test_default_flag() {
		$lang = $this->pll_env->model->get_language( 'en' );
		$this->assertEquals( plugins_url( '/flags/us.png', POLYLANG_FILE ), $lang->get_display_flag_url() ); // Bug fixed in 2.8.1.
		$this->assertEquals( 1, preg_match( '#<img src="data:image\/png;base64,(.+)" title="English" alt="English" width="16" height="11" style="(.+)" \/>#', $lang->get_display_flag() ) );
	}

	function test_custom_flag() {
		$lang = $this->pll_env->model->get_language( 'fr' );
		$this->assertEquals( content_url( '/polylang/fr_FR.png' ), $lang->get_display_flag_url() );
		$this->assertEquals( '<img src="/wp-content/polylang/fr_FR.png" title="Français" alt="Français" />', $lang->get_display_flag() );
	}

	/*
	 * bug fixed in 1.8
	 */
	function test_default_flag_ssl() {
		$_SERVER['HTTPS'] = 'on';

		$lang = $this->pll_env->model->get_language( 'en' );
		$this->assertContains( 'https', $lang->get_display_flag_url() );

		unset( $_SERVER['HTTPS'] );
	}

	function test_custom_flag_ssl() {
		$_SERVER['HTTPS'] = 'on';

		$lang = $this->pll_env->model->get_language( 'fr' );
		$this->assertEquals( content_url( '/polylang/fr_FR.png' ), $lang->get_display_flag_url() );
		$this->assertContains( 'https', $lang->get_display_flag_url() );

		unset( $_SERVER['HTTPS'] );
	}

	function test_remove_flag_inline_style_in_saved_language() {
		copy( dirname( __FILE__ ) . '/../data/de_CH.png', self::$flag_de_ch_informal );
		self::create_language( 'de_CH_informal' );
		$language = $this->pll_env->model->get_language( 'de_CH_informal' );

		$this->assertNotContains( 'style', $language->get_display_flag() );
		$this->assertNotContains( 'width', $language->get_display_flag() );
		$this->assertNotContains( 'height', $language->get_display_flag() );
	}

	function test_remove_flag_inline_style_in_new_language() {
		$language = PLL_Language::create( self::$new_language );

		$this->assertNotContains( 'style', $language->get_display_flag() );
		$this->assertNotContains( 'width', $language->get_display_flag() );
		$this->assertNotContains( 'height', $language->get_display_flag() );
	}
}
