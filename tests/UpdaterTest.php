<?php
/**
 * Unit tests for `Updater` class.
 */

namespace Niteo\Kafkai\Plugin\Tests;

use Niteo\Kafkai\Plugin\Admin\Updater;
use PHPUnit\Framework\TestCase;

/**
 * Tests Updater class functions in isolation.
 *
 * @package Niteo\Kafkai\Plugin
 * @coversDefaultClass \Niteo\Kafkai\Plugin\Admin\Updater
 */
class UpdaterTest extends TestCase {

	function setUp() : void {
		\WP_Mock::setUsePatchwork( true );
		\WP_Mock::setUp();
	}

	function tearDown() : void {
		$this->addToAssertionCount(
			\Mockery::getContainer()->mockery_getExpectationCount()
		);

		\WP_Mock::tearDown();
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstructor() {
		$updater = new Updater();

		\WP_Mock::expectActionAdded( 'current_screen', array( $updater, 'check_niches' ) );
		\WP_Mock::expectActionAdded( 'admin_notices', array( $updater, 'add_notification' ) );
		\WP_Mock::expectActionAdded( 'kafkaiwp_settings', array( $updater, 'add_update_niche_button' ) );

		$updater->__construct();
		\WP_Mock::assertHooksAdded();
	}

	/**
	 * @covers ::__construct
	 * @covers ::check_niches
	 * @covers ::is_plugin_page
	 */
	public function testCheckNichesWrongPage() {
		$updater        = new Updater();
		$current_screen = (object) array(
			'id' => 'wrong_page',
		);

		\WP_Mock::userFunction(
			'get_current_screen',
			array(
				'times'  => 1,
				'return' => $current_screen,
			)
		);

		$this->assertEmpty( $updater->check_niches() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::check_niches
	 * @covers ::is_plugin_page
	 */
	public function testCheckNichesNoTransientNoData() {
		$mock = \Mockery::mock( '\Niteo\Kafkai\Plugin\Admin\Updater' )->makePartial();
		$mock->shouldReceive( 'api_call' )->andReturn( false );

		$current_screen = (object) array(
			'id' => 'kafkaiwp_settings',
		);

		\WP_Mock::userFunction(
			'get_current_screen',
			array(
				'times'  => 1,
				'return' => $current_screen,
			)
		);

		\WP_Mock::userFunction(
			'get_transient',
			array(
				'times'  => 1,
				'return' => false,
			)
		);

		$this->assertEmpty( $mock->check_niches() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::check_niches
	 * @covers ::is_plugin_page
	 */
	public function testCheckNichesNoTransientWithData() {
		$mock = \Mockery::mock( '\Niteo\Kafkai\Plugin\Admin\Updater' )->makePartial();
		$mock->shouldReceive( 'api_call' )->andReturn( 'DUMMY_DATA' );
		$mock->shouldReceive( 'yaml_parse_and_check' )->andReturn( true );

		$current_screen = (object) array(
			'id' => 'kafkaiwp_settings',
		);

		\WP_Mock::userFunction(
			'get_current_screen',
			array(
				'times'  => 1,
				'return' => $current_screen,
			)
		);

		\WP_Mock::userFunction(
			'get_transient',
			array(
				'times'  => 1,
				'return' => false,
			)
		);

		$this->assertEmpty( $mock->check_niches() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::check_niches
	 * @covers ::is_plugin_page
	 */
	public function testCheckNichesWithTransient() {
		$mock = \Mockery::mock( '\Niteo\Kafkai\Plugin\Admin\Updater' )->makePartial();
		$mock->shouldReceive( 'yaml_parse_and_check' )->andReturn( true );

		$current_screen = (object) array(
			'id' => 'kafkaiwp_settings',
		);

		\WP_Mock::userFunction(
			'get_current_screen',
			array(
				'times'  => 1,
				'return' => $current_screen,
			)
		);

		\WP_Mock::userFunction(
			'get_transient',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		$this->assertEmpty( $mock->check_niches() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::add_notification
	 * @covers ::is_plugin_page
	 */
	public function testAddNotificationWrongPage() {
		global $current_screen;

		$current_screen = (object) array(
			'id' => 'not_plugin_page',
		);
		$updater        = new Updater();

		$this->assertEmpty( $updater->add_notification() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::add_notification
	 * @covers ::is_plugin_page
	 */
	public function testAddNotificationNoTransient() {
		global $current_screen;

		$current_screen = (object) array(
			'id' => 'kafkaiwp_settings',
		);
		$updater        = new Updater();

		\WP_Mock::userFunction(
			'get_transient',
			array(
				'times'  => 1,
				'return' => false,
			)
		);

		$this->assertEmpty( $updater->add_notification() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::add_notification
	 * @covers ::is_plugin_page
	 */
	public function testAddNotificationSuccess() {
		global $current_screen;

		$current_screen = (object) array(
			'id' => 'kafkaiwp_settings',
		);
		$updater        = new Updater();

		\WP_Mock::userFunction(
			'get_transient',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'self_admin_url',
			array(
				'times'  => 1,
				'return' => 'admin.php?page=kafkaiwp_settings',
			)
		);

		$this->expectOutputString( '<div class="notice notice-info"><p>New niches are available for the plugin. Please go to <a href="admin.php?page=kafkaiwp_settings">Settings page</a> to update.</p></div>' );
		$updater->add_notification();
	}

	/**
	 * @covers ::__construct
	 * @covers ::add_update_niche_button
	 */
	public function testUpdateNicheButton() {
		$updater = new Updater();

		$this->expectOutputString( '&nbsp;<input type="submit" name="kafkaiwp_update_niches" value="Update Niches" class="button button-secondary">' );
		$updater->add_update_niche_button();
	}

}