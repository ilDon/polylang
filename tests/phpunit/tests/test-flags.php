<?php

class Flags_Test extends PLL_UnitTestCase {

	static function wpSetUpBeforeClass() {
		parent::wpSetUpBeforeClass();

		self::create_language( 'en_US' );
		self::create_language( 'fr_FR' );

		@mkdir( WP_CONTENT_DIR . '/polylang' );
		copy( dirname( __FILE__ ) . '/../data/fr_FR.png', WP_CONTENT_DIR . '/polylang/fr_FR.png' );
	}

	static function wpTearDownAfterClass() {
		parent::wpTearDownAfterClass();

		unlink( WP_CONTENT_DIR . '/polylang/fr_FR.png' );
		rmdir( WP_CONTENT_DIR . '/polylang' );
	}

	function setUp() {
		$options = PLL_Install::get_default_options();
		$model = new PLL_Model( $options );
		$links_model = new PLL_Links_Default( $model );
		$this->pll_env = $this->getMockBuilder( PLL_Frontend::class )
			->disableOriginalconstructor()
			->getMock();
		$this->pll_env->links_model = $links_model;
		$this->pll_env->model = $model;
		$this->pll_env->options = $options;
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
}