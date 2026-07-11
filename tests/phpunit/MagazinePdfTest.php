<?php
/**
 * Magazine PDF attachment tests.
 *
 * @package Magazine73
 */

declare(strict_types=1);

namespace Magazine73\Tests;

use Magazine73\Magazine_Pdf;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magazine73\Magazine_Pdf
 */
final class MagazinePdfTest extends TestCase {

	/**
	 * Reset stub state before each test.
	 */
	protected function setUp(): void {
		parent::setUp();
		WordPressStub::reset();
	}

	public function test_is_valid_pdf_attachment_requires_pdf_mime_type(): void {
		WordPressStub::$post_types[501] = 'attachment';
		WordPressStub::$mime_types[501]  = 'application/pdf';
		WordPressStub::$post_types[502]  = 'attachment';
		WordPressStub::$mime_types[502]  = 'image/webp';

		$this->assertTrue( Magazine_Pdf::is_valid_pdf_attachment( 501 ) );
		$this->assertFalse( Magazine_Pdf::is_valid_pdf_attachment( 502 ) );
	}

	public function test_get_safe_filename_sanitizes_unsafe_characters(): void {
		WordPressStub::$attached_files[601] = '/uploads/My Magazine (final).pdf';

		$this->assertSame( 'My_Magazine__final_.pdf', Magazine_Pdf::get_safe_filename( 601 ) );
	}

	public function test_get_attachment_id_returns_zero_for_invalid_pdf(): void {
		WordPressStub::$post_meta[8][ Magazine_Pdf::PDF_ATTACHMENT_META_KEY ] = 999;
		WordPressStub::$post_types[999]                                       = 'attachment';
		WordPressStub::$mime_types[999]                                        = 'image/webp';

		$this->assertSame( 0, Magazine_Pdf::get_attachment_id( 8 ) );
	}
}
