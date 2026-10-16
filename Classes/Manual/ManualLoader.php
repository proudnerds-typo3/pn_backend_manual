<?php

declare(strict_types=1);

namespace ProudNerds\PnBackendManual\Manual;

use ProudNerds\PnBackendManual\Rendering\ImageResolver;
use ProudNerds\PnBackendManual\Rendering\MarkdownRenderer;
use ProudNerds\PnBackendManual\Rendering\RenderedManual;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\SystemResource\Publishing\SystemResourcePublisherInterface;
use TYPO3\CMS\Core\SystemResource\SystemResourceFactory;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * Loads a manual from an EXT: path; images must live in Resources/Public of that extension.
 */
final readonly class ManualLoader
{
    public function __construct(
        private MarkdownRenderer $renderer,
        private SystemResourceFactory $systemResourceFactory,
        private SystemResourcePublisherInterface $systemResourcePublisher,
    ) {}

    public function load(
        string $file,
        ServerRequestInterface $request,
        LanguageService $languageService,
        bool $numbered = false,
    ): RenderedManual {
        if (!PathUtility::isExtensionPath($file)) {
            throw new \InvalidArgumentException('A manual must be an EXT: path, got "' . $file . '"', 1791980002);
        }
        $absoluteFile = GeneralUtility::getFileAbsFileName($file);
        $markdown = is_file($absoluteFile) ? file_get_contents($absoluteFile) : false;
        if ($markdown === false) {
            throw new \RuntimeException('Manual "' . $file . '" cannot be read', 1791980003);
        }

        $extensionKey = explode('/', substr($file, 4), 2)[0];
        $images = new ImageResolver(
            dirname($absoluteFile),
            ExtensionManagementUtility::extPath($extensionKey) . 'Resources/Public',
            fn(string $path): string => $this->publicUrl('EXT:' . $extensionKey . '/Resources/Public/' . $path, $request),
        );

        return $this->renderer->render(
            $markdown,
            $images,
            $languageService->sL('pn_backend_manual.messages:link.newWindow'),
            $numbered,
        );
    }

    /**
     * URL of a file in Resources/Public of an extension, with cache busting
     */
    public function publicUrl(string $resource, ServerRequestInterface $request): string
    {
        return (string)$this->systemResourcePublisher->generateUri(
            $this->systemResourceFactory->createPublicResource($resource),
            $request
        );
    }
}
