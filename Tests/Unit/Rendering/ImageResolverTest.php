<?php

declare(strict_types=1);

namespace ProudNerds\PnBackendManual\Tests\Unit\Rendering;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use ProudNerds\PnBackendManual\Rendering\ImageResolver;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class ImageResolverTest extends UnitTestCase
{
    private ImageResolver $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $resources = __DIR__ . '/../Fixtures/Extension/Resources';
        $this->subject = new ImageResolver(
            $resources . '/Private/Manual',
            $resources . '/Public',
            static fn(string $path): string => '/_assets/abc/' . $path
        );
    }

    #[Test]
    public function resolveReturnsThePublicUrlOfAnImageInsideThePublicDirectory(): void
    {
        self::assertSame('/_assets/abc/Manual/screen.png', $this->subject->resolve('../../Public/Manual/screen.png'));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function rejectedSourcesDataProvider(): array
    {
        return [
            'outside the public directory' => ['../secret.png'],
            'traversal out of and back into public' => ['../../Public/../Private/secret.png'],
            'url-encoded traversal' => ['..%2F..%2FPublic%2F..%2FPrivate%2Fsecret.png'],
            'directory next to public with the same prefix' => ['../../PublicX/leak.png'],
            'symlink in public to a file outside' => ['../../Public/Manual/link-to-secret.png'],
            'symlink in public to a directory outside' => ['../../Public/Manual/link-to-private/secret.png'],
            'null byte' => ['../../Public/Manual/screen.png%00.txt'],
            'file name that would become a url fragment' => ['../../Public/Manual/screen%231.png'],
            'missing file' => ['../../Public/Manual/missing.png'],
            'directory' => ['../../Public/Manual'],
            'absolute path' => ['/etc/passwd'],
            'external url' => ['https://example.org/screen.png'],
            'data uri' => ['data:image/png;base64,AAAA'],
            'empty' => [''],
        ];
    }

    #[Test]
    #[DataProvider('rejectedSourcesDataProvider')]
    public function resolveReturnsNullForASourceThatMayNotBeShown(string $source): void
    {
        self::assertNull($this->subject->resolve($source));
    }
}
