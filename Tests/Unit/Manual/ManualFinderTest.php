<?php

declare(strict_types=1);

namespace ProudNerds\PnBackendManual\Tests\Unit\Manual;

use PHPUnit\Framework\Attributes\Test;
use ProudNerds\PnBackendManual\Manual\ManualFinder;
use ProudNerds\PnBackendManual\Manual\ManualLoader;
use ProudNerds\PnBackendManual\Rendering\MarkdownRenderer;
use ProudNerds\PnBackendManual\Widgets\ManualWidget;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\View\BackendViewFactory;
use TYPO3\CMS\Core\SystemResource\Publishing\SystemResourcePublisherInterface;
use TYPO3\CMS\Core\SystemResource\SystemResourceFactory;
use TYPO3\CMS\Dashboard\WidgetRegistry;
use TYPO3\CMS\Dashboard\Widgets\WidgetConfigurationInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetRendererInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class ManualFinderTest extends UnitTestCase
{
    private ServerRequestInterface $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = self::createStub(ServerRequestInterface::class);
    }

    #[Test]
    public function findReturnsNullWithoutLoadingAWidgetTheUserMayNotUse(): void
    {
        $registry = $this->createMock(WidgetRegistry::class);
        $registry->method('getAvailableWidgets')->willReturn([]);
        $registry->expects(self::never())->method('getAvailableWidget');

        self::assertNull((new ManualFinder($registry))->find($this->request, 'otherSiteManual'));
    }

    #[Test]
    public function findReturnsNullForAnAvailableWidgetThatIsNotAManual(): void
    {
        $registry = self::createStub(WidgetRegistry::class);
        $registry->method('getAvailableWidgets')->willReturn(['rssWidget' => self::createStub(WidgetConfigurationInterface::class)]);
        $registry->method('getAvailableWidget')->willReturn(self::createStub(WidgetRendererInterface::class));

        self::assertNull((new ManualFinder($registry))->find($this->request, 'rssWidget'));
    }

    #[Test]
    public function findReturnsTheManualWidgetTheUserMayUse(): void
    {
        $manual = $this->createManualWidget();
        $registry = self::createStub(WidgetRegistry::class);
        $registry->method('getAvailableWidgets')->willReturn(['siteManual' => self::createStub(WidgetConfigurationInterface::class)]);
        $registry->method('getAvailableWidget')->willReturn($manual);

        self::assertSame($manual, (new ManualFinder($registry))->find($this->request, 'siteManual'));
    }

    private function createManualWidget(): ManualWidget
    {
        // Final classes that the finder never calls
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
            ['file' => 'EXT:my_sitepackage/Resources/Private/Manual/EditorManual.md'],
        );
    }
}
