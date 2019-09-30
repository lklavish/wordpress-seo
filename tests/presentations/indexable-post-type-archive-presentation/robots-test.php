<?php

namespace Yoast\WP\Free\Tests\Presentations\Indexable_Post_Type_Archive_Presentation;

use Yoast\WP\Free\Tests\TestCase;

/**
 * Class WPSEO_Schema_FAQ_Questions_Test.
 *
 * @group schema
 *
 * @coversDefaultClass \Yoast\WP\Free\Presentations\Indexable_Post_Type_Archive_Presentation
 *
 * @package Yoast\Tests\Frontend\Schema
 */
class Robots_Test extends TestCase {
	use Presentation_Instance_Builder;

	/**
	 * Sets up the test class.
	 */
	public function setUp() {
		parent::setUp();

		$this->setInstance();

		$this->robots_helper->expects( 'get_base_values' )
			->andReturn( [
				'index'  => 'index',
				'follow' => 'follow',
			] );

		$this->robots_helper
			->expects( 'after_generate' )
			->once()
			->andReturnUsing( function( $robots ) {
				return array_filter( $robots );
			} );
	}

	/**
	 * Tests whether generate_robots calls the right functions of the robot helper.
	 *
	 * @covers ::generate_robots
	 */
	public function test_generate_robots_dont_index_post_type() {
		$this->indexable->object_id       = 1337;
		$this->indexable->object_sub_type = 'post';

		$this->options_helper
			->expects( 'get' )
			->with( 'noindex-ptarchive-post', false )
			->andReturn( true );

		$actual = $this->instance->generate_robots();
		$expected = [
			'index'        => 'noindex',
			'follow'       => 'follow',
		];

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Tests whether generate_robots calls the right functions of the robot helper.
	 *
	 * @covers ::generate_robots
	 */
	public function test_generate_robots_index_post_type() {
		$this->indexable->object_id       = 1337;
		$this->indexable->object_sub_type = 'post';

		$this->options_helper
			->expects( 'get' )
			->with( 'noindex-ptarchive-post', false )
			->andReturn( false );

		$actual = $this->instance->generate_robots();
		$expected = [
			'index'        => 'index',
			'follow'       => 'follow',
		];

		$this->assertEquals( $expected, $actual );
	}
}