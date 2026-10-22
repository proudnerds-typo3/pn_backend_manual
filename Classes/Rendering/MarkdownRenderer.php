<?php

declare(strict_types=1);

namespace ProudNerds\PnBackendManual\Rendering;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\HtmlBlock;
use League\CommonMark\Extension\CommonMark\Node\Inline\Emphasis;
use League\CommonMark\Extension\CommonMark\Node\Inline\HtmlInline;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\DefaultAttributes\DefaultAttributesExtension;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\Table\Table;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Node\Node;
use League\CommonMark\Node\StringContainerHelper;
use League\CommonMark\Parser\MarkdownParser;
use League\CommonMark\Renderer\HtmlRenderer;
use ProudNerds\PnBackendManual\Rendering\Node\VisuallyHidden;
use ProudNerds\PnBackendManual\Rendering\Node\VisuallyHiddenRenderer;

/**
 * Renders a manual: the first h1 is the title, everything before the first h2 the intro.
 * Raw HTML in the Markdown is stripped; the manual is shown inside the backend.
 * Numbers are added after parsing, so the heading anchors do not depend on them.
 */
final class MarkdownRenderer
{
    public function render(string $markdown, ImageResolver $images, string $newWindowLabel, bool $numbered = false): RenderedManual
    {
        $environment = new Environment([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'heading_permalink' => [
                'insert' => 'none',
                'apply_id_to_heading' => true,
                'id_prefix' => 'manual',
                'fragment_prefix' => 'manual',
                'min_heading_level' => 2,
                'max_heading_level' => 3,
            ],
            'external_link' => [
                'internal_hosts' => [],
                'open_in_new_window' => true,
            ],
            // The table style of the core's backend lists; table-fit-wrap lets text wrap in the cells
            'table' => [
                'wrap' => [
                    'enabled' => true,
                    'tag' => 'div',
                    'attributes' => ['class' => 'table-fit table-fit-wrap'],
                ],
            ],
            'default_attributes' => [
                Table::class => ['class' => 'table table-striped'],
            ],
        ]);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());
        $environment->addExtension(new FrontMatterExtension());
        $environment->addExtension(new HeadingPermalinkExtension());
        $environment->addExtension(new ExternalLinkExtension());
        $environment->addExtension(new DefaultAttributesExtension());
        $environment->addRenderer(VisuallyHidden::class, new VisuallyHiddenRenderer());
        // Runs after the external link processor, which sets the target
        $environment->addEventListener(
            DocumentParsedEvent::class,
            function (DocumentParsedEvent $event) use ($images, $newWindowLabel): void {
                $this->resolveImages($event->getDocument(), $images);
                $this->labelNewWindowLinks($event->getDocument(), $newWindowLabel);
            },
            -1000
        );

        $document = (new MarkdownParser($environment))->parse($markdown);
        $renderer = new HtmlRenderer($environment);

        $title = '';
        $intro = [];
        $body = [];
        $tableOfContents = [];
        $inIntro = true;
        $chapter = 0;
        $section = 0;
        foreach ($document->children() as $node) {
            if ($title === '' && $node instanceof Heading && $node->getLevel() === 1) {
                $title = $this->plainText($node);
                continue;
            }
            if ($node instanceof Heading && $node->getLevel() === 2) {
                $inIntro = false;
                $section = 0;
                $number = ++$chapter . '.';
                $tableOfContents[] = $this->contentsEntry($node, $numbered ? $number : '') + ['sections' => []];
                if ($numbered) {
                    $this->prependNumber($node, $number);
                }
            } elseif ($node instanceof Heading && $node->getLevel() === 3 && $tableOfContents !== []) {
                $number = $chapter . '.' . ++$section;
                $tableOfContents[array_key_last($tableOfContents)]['sections'][]
                    = $this->contentsEntry($node, $numbered ? $number : '');
                if ($numbered) {
                    $this->prependNumber($node, $number);
                }
            }
            if ($inIntro) {
                $intro[] = $node;
            }
            $body[] = $node;
        }

        $bodyHtml = $renderer->renderNodes($body);
        // The intro is also shown on the dashboard, where heading ids could clash with another manual
        foreach ($intro as $node) {
            if ($node instanceof Heading) {
                // @extensionScannerIgnoreLine Node::$data of league/commonmark
                $node->data->remove('attributes/id');
            }
        }

        return new RenderedManual(
            $title,
            $renderer->renderNodes($intro),
            $bodyHtml,
            $tableOfContents,
            // @extensionScannerIgnoreLine Node::$data of league/commonmark
            ...$this->versionAndDate($document->data->get('front_matter', null)),
        );
    }

    /**
     * "version" and "date" from the front matter. YAML turns an unquoted date into a timestamp of
     * midnight UTC, so it is read back as a date in UTC to stay on the same day in every timezone.
     *
     * @return array{version: string, updated: ?\DateTimeImmutable}
     */
    private function versionAndDate(mixed $frontMatter): array
    {
        $version = is_array($frontMatter) && is_scalar($frontMatter['version'] ?? null) ? (string)$frontMatter['version'] : '';
        $date = is_array($frontMatter) ? ($frontMatter['date'] ?? null) : null;
        if (is_int($date)) {
            $date = gmdate('Y-m-d', $date);
        }
        $updated = is_string($date) ? (\DateTimeImmutable::createFromFormat('!Y-m-d', $date) ?: null) : null;

        return ['version' => $version, 'updated' => $updated];
    }

    /**
     * @return array{id: string, title: string, number: string}
     */
    private function contentsEntry(Heading $heading, string $number): array
    {
        return [
            // @extensionScannerIgnoreLine Node::$data of league/commonmark
            'id' => (string)$heading->data->get('attributes/id', ''),
            'title' => $this->plainText($heading),
            'number' => $number,
        ];
    }

    private function prependNumber(Heading $heading, string $number): void
    {
        $heading->prependChild(new Text($number . ' '));
    }

    /**
     * Raw HTML is stripped when rendering, but not from the literal text of a node.
     */
    private function plainText(Node $node): string
    {
        return StringContainerHelper::getChildText($node, [HtmlInline::class, HtmlBlock::class]);
    }

    /**
     * An image that may not be shown becomes its alt text, as a browser does with a broken image.
     */
    private function resolveImages(Document $document, ImageResolver $images): void
    {
        $unresolved = [];
        foreach ($document->iterator() as $node) {
            if (!$node instanceof Image) {
                continue;
            }
            $url = $images->resolve($node->getUrl());
            if ($url === null) {
                $unresolved[] = $node;
            } else {
                $node->setUrl($url);
            }
        }

        foreach ($unresolved as $image) {
            $altText = new Emphasis();
            $altText->appendChild(new Text($this->plainText($image)));
            $image->replaceWith($altText);
        }
    }

    private function labelNewWindowLinks(Document $document, string $label): void
    {
        $links = [];
        foreach ($document->iterator() as $node) {
            // @extensionScannerIgnoreLine Node::$data of league/commonmark
            if ($node instanceof Link && $node->data->get('attributes/target', '') === '_blank') {
                $links[] = $node;
            }
        }

        foreach ($links as $link) {
            $link->appendChild(new VisuallyHidden(' (' . $label . ')'));
        }
    }
}
