<?php

declare(strict_types=1);

namespace ProudNerds\PnBackendManual\Tests\Unit\Widgets;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use ProudNerds\PnBackendManual\Manual\ManualLoader;
use ProudNerds\PnBackendManual\Rendering\MarkdownRenderer;
use ProudNerds\PnBackendManual\Widgets\ManualWidget;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\View\BackendViewFactory;
use TYPO3\CMS\Core\SystemResource\Publishing\SystemResourcePublisherInterface;
use TYPO3\CMS\Core\SystemResource\SystemResourceFactory;
use TYPO3\CMS\Dashboard\Widgets\WidgetConfigurationInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class ManualWidgetTest extends UnitTestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function feedbackDataProvider(): array
    {
        return [
            'mail address' => ['mailto:web@example.org', 'mailto:web@example.org'],
            'web address' => ['https://example.org/feedback', 'https://example.org/feedback'],
            'surrounding spaces' => [' mailto:web@example.org ', 'mailto:web@example.org'],
            'javascript' => ['javascript:alert(1)', ''],
            'relative path' => ['/feedback', ''],
            'empty' => ['', ''],
        ];
    }

    #[Test]
    #[DataProvider('feedbackDataProvider')]
    public function getFeedbackAllowsOnlyMailAndWebAddresses(string $option, string $expected): void
    {
        self::assertSame($expected, $this->createWidget(['feedback' => $option])->getFeedback());
    }

    #[Test]
    public function optionsHaveTheDocumentedDefaults(): void
    {
        $widget = $this->createWidget([]);

        self::assertSame(
            [false, 1, true, ''],
            [$widget->isNumbered(), $widget->getContentsDepth(), $widget->hasPrintButton(), $widget->getFeedback()]
        );
    }

    /**
     * @param array<string, mixed> $options
     */
    private function createWidget(array $options): ManualWidget
    {
        // Final classes that these getters never call
        $viewFactory = (new \ReflectionClass(BackendViewFactory::class))->newInstanceWithoutConstructor();
        $loader = new ManualLoader(
            new MarkdownRenderer(),
            self::createStub(SystemResourceFactory::class),
            self::createStub(SystemResourcePublisherInterface::class),
        );

        return new ManualWidget(
            self::createStub(WidgetConfigurationInterface::class),
            $viewFactory,
            $loader,
            self::createStub(UriBuilder::class),
            $options,
        );
    }
}
