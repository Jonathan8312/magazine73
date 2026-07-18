<?php
/**
 * Publish guard tests.
 *
 * @package Magazine73
 */

declare(strict_types=1);

namespace Magazine73\Tests;

use Magazine73\Magazine_Pages;
use Magazine73\Publish_Guard;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magazine73\Publish_Guard
 */
final class PublishGuardTest extends TestCase {

	/**
	 * Reset stub state before each test.
	 */
	protected function setUp(): void {
		parent::setUp();
		WordPressStub::reset();
	}

	public function test_resolve_status_keeps_non_publish_statuses(): void {
		$this->assertSame( 'draft', Publish_Guard::resolve_status_for_save( 'draft', 0 ) );
		$this->assertSame( 'pending', Publish_Guard::resolve_status_for_save( 'pending', 0 ) );
	}

	public function test_resolve_status_blocks_publish_without_pages(): void {
		$this->assertSame( 'draft', Publish_Guard::resolve_status_for_save( 'publish', 0 ) );
		$this->assertSame( 'draft', Publish_Guard::resolve_status_for_save( 'publish', 10, array() ) );
	}

	public function test_resolve_status_allows_publish_with_valid_webp_pages(): void {
		WordPressStub::$post_types[21] = 'attachment';
		WordPressStub::$mime_types[21] = 'image/webp';
		WordPressStub::$attached_files[21] = '/tmp/01-cover.webp';

		$this->assertSame(
			'publish',
			Publish_Guard::resolve_status_for_save( 'publish', 0, array( 21 ) )
		);
	}

	public function test_has_publishable_pages_reads_stored_meta(): void {
		WordPressStub::$post_types[21] = 'attachment';
		WordPressStub::$mime_types[21] = 'image/webp';
		WordPressStub::$attached_files[21] = '/tmp/01-cover.webp';
		WordPressStub::$post_meta[15][ Magazine_Pages::PAGE_IDS_META_KEY ] = array( 21 );

		$this->assertTrue( Publish_Guard::has_publishable_pages( 15 ) );
		$this->assertFalse( Publish_Guard::has_publishable_pages( 16 ) );
	}
}
