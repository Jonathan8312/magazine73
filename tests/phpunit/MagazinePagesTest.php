<?php
/**
 * Magazine page metadata tests.
 *
 * @package Magazine73
 */

declare(strict_types=1);

namespace Magazine73\Tests;

use Magazine73\Magazine_Pages;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magazine73\Magazine_Pages
 */
final class MagazinePagesTest extends TestCase {

	/**
	 * Reset stub state before each test.
	 */
	protected function setUp(): void {
		parent::setUp();
		WordPressStub::reset();
	}

	public function test_sort_attachment_ids_by_filename_uses_natural_order(): void {
		WordPressStub::$attached_files[10] = '/uploads/2026/10-page.webp';
		WordPressStub::$attached_files[11] = '/uploads/2026/2-page.webp';
		WordPressStub::$attached_files[12] = '/uploads/2026/1-page.webp';

		$sorted = Magazine_Pages::sort_attachment_ids_by_filename( array( 10, 11, 12 ) );

		$this->assertSame( array( 12, 11, 10 ), $sorted );
	}

	public function test_is_valid_webp_attachment_requires_webp_mime_type(): void {
		WordPressStub::$post_types[101]  = 'attachment';
		WordPressStub::$mime_types[101]   = 'image/webp';
		WordPressStub::$post_types[102]    = 'attachment';
		WordPressStub::$mime_types[102]    = 'image/jpeg';

		$this->assertTrue( Magazine_Pages::is_valid_webp_attachment( 101 ) );
		$this->assertFalse( Magazine_Pages::is_valid_webp_attachment( 102 ) );
	}

	public function test_get_content_hash_changes_when_page_set_changes(): void {
		$this->register_webp_attachment( 10, '/uploads/01-cover.webp' );
		$this->register_webp_attachment( 20, '/uploads/02-page.webp' );
		WordPressStub::$post_meta[5][ Magazine_Pages::PAGE_IDS_META_KEY ] = array( 10, 20 );

		$first_hash = Magazine_Pages::get_content_hash( 5 );

		$this->register_webp_attachment( 30, '/uploads/03-page.webp' );
		WordPressStub::$post_meta[5][ Magazine_Pages::PAGE_IDS_META_KEY ] = array( 10, 20, 30 );

		$second_hash = Magazine_Pages::get_content_hash( 5 );

		$this->assertNotSame( $first_hash, $second_hash );
	}

	public function test_get_viewer_pages_adds_blank_page_for_odd_counts(): void {
		$this->register_webp_attachment( 201, '/uploads/cover.webp' );
		WordPressStub::$post_meta[9][ Magazine_Pages::PAGE_IDS_META_KEY ] = array( 201 );

		$pages = Magazine_Pages::get_viewer_pages( 9 );

		$this->assertCount( 2, $pages );
		$this->assertTrue( ! empty( $pages[1]['blank'] ) );
	}

	/**
	 * Register a valid WebP attachment in the stub store.
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param string $file_path   File path.
	 */
	private function register_webp_attachment( int $attachment_id, string $file_path ): void {
		WordPressStub::$post_types[ $attachment_id ]    = 'attachment';
		WordPressStub::$mime_types[ $attachment_id ]      = 'image/webp';
		WordPressStub::$attached_files[ $attachment_id ] = $file_path;
	}
}
