<?php

declare(strict_types=1);

namespace ProudNerds\PnBackendManual\Controller;

use ProudNerds\PnBackendManual\Manual\ManualFinder;
use ProudNerds\PnBackendManual\Manual\ManualLoader;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\Components\ComponentFactory;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Page\PageRenderer;

/**
 * Full view of a manual. The manual is looked up by widget identifier only, so the request
 * never chooses a file, and the widget permission of the backend user applies.
 */
#[AsController]
final readonly class ManualController
{
    public function __construct(
        private ModuleTemplateFactory $moduleTemplateFactory,
        private ComponentFactory $componentFactory,
        private ManualFinder $manualFinder,
        private ManualLoader $manualLoader,
        private UriBuilder $uriBuilder,
        private PageRenderer $pageRenderer,
        private IconFactory $iconFactory,
        private LoggerInterface $logger,
    ) {}

    public function showAction(ServerRequestInterface $request): ResponseInterface
    {
        $identifier = (string)($request->getQueryParams()['manual'] ?? '');
        $widget = $this->manualFinder->find($request, $identifier);
        $languageService = $this->getLanguageService();

        $moduleTemplate = $this->moduleTemplateFactory->create($request);
        $moduleTemplate->addButtonToButtonBar(
            $this->componentFactory->createBackButton($this->uriBuilder->buildUriFromRoute('dashboard')),
            ButtonBar::BUTTON_POSITION_LEFT
        );

        if ($widget === null) {
            $moduleTemplate->setTitle($languageService->sL('pn_backend_manual.messages:manual.notFound.title'));
            return $moduleTemplate->renderResponse('Manual/NotFound')->withStatus(404);
        }

        try {
            $manual = $this->manualLoader->load($widget->getFile(), $request, $languageService, $widget->isNumbered());
        } catch (\Throwable $exception) {
            $this->logger->error('Manual "{manual}" cannot be shown: {message}', [
                'manual' => $identifier,
                'message' => $exception->getMessage(),
                'exception' => $exception,
            ]);
            $moduleTemplate->setTitle($languageService->sL('pn_backend_manual.messages:manual.error.title'));
            return $moduleTemplate->renderResponse('Manual/Error')->withStatus(500);
        }

        $title = $manual->title !== '' ? $manual->title : $languageService->sL($widget->getTitle());
        $this->pageRenderer->addCssFile('EXT:pn_backend_manual/Resources/Public/Css/manual.css');
        $this->pageRenderer->loadJavaScriptModule('@proudnerds/pn-backend-manual/manual-contents.js');
        if ($widget->hasPrintButton()) {
            $this->addPrintButton($moduleTemplate, $languageService);
        }
        $moduleTemplate->setTitle($title);
        $moduleTemplate->assignMultiple([
            'title' => $title,
            'manual' => $manual,
            'logoUrl' => $widget->getLogoUrl($request),
            'showSections' => $widget->getContentsDepth() > 1,
        ]);

        return $moduleTemplate->renderResponse('Manual/Show');
    }

    /**
     * The browser's print dialog also saves as PDF; the print styles leave out the backend around the manual
     */
    private function addPrintButton(ModuleTemplate $moduleTemplate, LanguageService $languageService): void
    {
        $label = $languageService->sL('pn_backend_manual.messages:manual.print');
        $this->pageRenderer->loadJavaScriptModule('@proudnerds/pn-backend-manual/manual-print.js');
        $moduleTemplate->addButtonToButtonBar(
            $this->componentFactory->createGenericButton()
                ->setTag('button')
                ->setLabel($label)
                ->setTitle($label)
                ->setShowLabelText(true)
                ->setIcon($this->iconFactory->getIcon('actions-file-pdf', IconSize::SMALL))
                ->setAttributes(['data-manual-print' => 'true']),
            ButtonBar::BUTTON_POSITION_LEFT,
            2
        );
    }

    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
