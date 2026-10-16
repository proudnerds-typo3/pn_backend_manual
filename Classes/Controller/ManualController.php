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
        $this->pageRenderer->loadJavaScriptModule('@proudnerds/pn-backend-manual/manual-search.js');
        if ($widget->hasPrintButton()) {
            $this->addPrintButton($moduleTemplate, $languageService);
        }
        if ($widget->getFeedback() !== '') {
            $this->addFeedbackButton($moduleTemplate, $languageService, $widget->getFeedback(), $title);
            $this->pageRenderer->loadJavaScriptModule('@proudnerds/pn-backend-manual/manual-feedback.js');
        }
        $moduleTemplate->setTitle($title);
        $moduleTemplate->assignMultiple([
            'title' => $title,
            'manual' => $manual,
            'updated' => $this->formatDate($manual->updated, $languageService),
            'logoUrl' => $widget->getLogoUrl($request),
            'showSections' => $widget->getContentsDepth() > 1,
            'sectionFeedback' => $this->sectionFeedback($widget->getFeedback(), $title, $languageService),
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

    /**
     * Feedback on the manual as a whole; each chapter and section has its own link in the text
     */
    private function addFeedbackButton(ModuleTemplate $moduleTemplate, LanguageService $languageService, string $feedback, string $title): void
    {
        $label = $languageService->sL('pn_backend_manual.messages:manual.feedback');
        $attributes = [];
        if (str_starts_with(strtolower($feedback), 'mailto:')) {
            $subject = sprintf($languageService->sL('pn_backend_manual.messages:manual.feedback.subject'), $title);
            $feedback .= (str_contains($feedback, '?') ? '&' : '?') . 'subject=' . rawurlencode($subject);
            // In the module iframe, the backend's Content Security Policy blocks a mailto: navigation
            $attributes['target'] = '_top';
        } else {
            $attributes['target'] = '_blank';
            $attributes['rel'] = 'noopener noreferrer';
        }
        $moduleTemplate->addButtonToButtonBar(
            $this->componentFactory->createLinkButton()
                ->setHref($feedback)
                ->setTitle($label)
                ->setShowLabelText(true)
                ->setIcon($this->iconFactory->getIcon('actions-message', IconSize::SMALL))
                ->setAttributes($attributes),
            ButtonBar::BUTTON_POSITION_LEFT,
            2
        );
    }

    /**
     * For the JavaScript that puts a feedback link next to each chapter and section
     *
     * @return array{address: string, subject: string, section: string, label: string, icon: string}|null
     */
    private function sectionFeedback(string $feedback, string $title, LanguageService $languageService): ?array
    {
        if ($feedback === '') {
            return null;
        }

        return [
            'address' => $feedback,
            'subject' => sprintf($languageService->sL('pn_backend_manual.messages:manual.feedback.subject'), $title),
            'section' => $languageService->sL('pn_backend_manual.messages:manual.feedback.section'),
            'label' => $languageService->sL('pn_backend_manual.messages:manual.feedback.on'),
            'icon' => $this->iconFactory->getIcon('actions-message', IconSize::SMALL)->render(),
        ];
    }

    private function formatDate(?\DateTimeImmutable $date, LanguageService $languageService): string
    {
        if ($date === null) {
            return '';
        }
        $locale = (string)($languageService->getLocale() ?? 'en');
        $formatter = new \IntlDateFormatter($locale, \IntlDateFormatter::LONG, \IntlDateFormatter::NONE, $date->getTimezone());

        return (string)$formatter->format($date);
    }

    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
