<?php

declare(strict_types=1);

namespace ProudNerds\PnBackendManual\Widgets;

use ProudNerds\PnBackendManual\Manual\ManualLoader;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\View\BackendViewFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\SystemResource\Exception\SystemResourceException;
use TYPO3\CMS\Dashboard\Widgets\AdditionalCssInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetConfigurationInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetContext;
use TYPO3\CMS\Dashboard\Widgets\WidgetRendererInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetResult;

/**
 * One widget per manual, so access is granted per backend group via "available widgets".
 * Options: "file" (EXT: path of the Markdown file), "logo", "numbered", "contentsDepth", "printButton"; see README.
 */
final readonly class ManualWidget implements WidgetRendererInterface, AdditionalCssInterface
{
    private string $file;
    private string $logo;
    private bool $numbered;
    private int $contentsDepth;
    private bool $printButton;

    /**
     * @param array{file?: string, logo?: string, numbered?: bool, contentsDepth?: int, printButton?: bool} $options
     */
    public function __construct(
        private WidgetConfigurationInterface $configuration,
        private BackendViewFactory $backendViewFactory,
        private ManualLoader $manualLoader,
        private UriBuilder $uriBuilder,
        array $options = [],
    ) {
        $this->file = $options['file'] ?? '';
        $this->logo = $options['logo'] ?? '';
        $this->numbered = (bool)($options['numbered'] ?? false);
        $this->contentsDepth = (int)($options['contentsDepth'] ?? 1) >= 2 ? 2 : 1;
        $this->printButton = (bool)($options['printButton'] ?? true);
    }

    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * Widget title from the registration: plain text or an LLL: reference
     */
    public function getTitle(): string
    {
        return $this->configuration->getTitle();
    }

    public function isNumbered(): bool
    {
        return $this->numbered;
    }

    /**
     * 1 = chapters (h2) in the table of contents of the full view, 2 = also their sections (h3)
     */
    public function getContentsDepth(): int
    {
        return $this->contentsDepth;
    }

    public function hasPrintButton(): bool
    {
        return $this->printButton;
    }

    /**
     * A logo that cannot be found is left out rather than breaking the manual
     */
    public function getLogoUrl(ServerRequestInterface $request): string
    {
        if ($this->logo === '') {
            return '';
        }
        try {
            return $this->manualLoader->publicUrl($this->logo, $request);
        } catch (SystemResourceException) {
            return '';
        }
    }

    /**
     * @return list<string>
     */
    public function getCssFiles(): array
    {
        return ['EXT:pn_backend_manual/Resources/Public/Css/manual-widget.css'];
    }

    public function getSettingsDefinitions(): array
    {
        return [];
    }

    public function renderWidget(WidgetContext $context): WidgetResult
    {
        $view = $this->backendViewFactory->create(
            $context->request,
            ['typo3/cms-dashboard', 'proudnerds/pn-backend-manual']
        );
        $view->assignMultiple([
            'manual' => $this->manualLoader->load($this->file, $context->request, $this->getLanguageService(), $this->numbered),
            'logoUrl' => $this->getLogoUrl($context->request),
            'manualUrl' => (string)$this->uriBuilder->buildUriFromRoute(
                'pn_backend_manual',
                ['manual' => $this->configuration->getIdentifier()]
            ),
        ]);

        return new WidgetResult($view->render('Widget/ManualWidget'));
    }

    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
