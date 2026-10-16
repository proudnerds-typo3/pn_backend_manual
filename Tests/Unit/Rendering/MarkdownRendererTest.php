<?php

declare(strict_types=1);

namespace ProudNerds\PnBackendManual\Tests\Unit\Rendering;

use PHPUnit\Framework\Attributes\Test;
use ProudNerds\PnBackendManual\Rendering\ImageResolver;
use ProudNerds\PnBackendManual\Rendering\MarkdownRenderer;
use ProudNerds\PnBackendManual\Rendering\RenderedManual;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class MarkdownRendererTest extends UnitTestCase
{
    private const MANUAL = <<<'MD'
        ---
        title: Manual
        ---

        # Manual for editors

        Welcome to the **manual**.

        ## Working in TYPO3

        | You want to | Where |
        |---|---|
        | Manage pages | Content › Layout |

        ### New pages

        Text.

        ## News items

        More text.
        MD;

    private MarkdownRenderer $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new MarkdownRenderer();
    }

    private function render(string $markdown, ?ImageResolver $images = null, bool $numbered = false): RenderedManual
    {
        $images ??= new ImageResolver('/nonexistent', '/nonexistent', static fn(string $path): string => $path);

        return $this->subject->render($markdown, $images, 'opens in a new window', $numbered);
    }

    #[Test]
    public function renderTakesTheFirstH1AsTitleAndLeavesItOutOfTheBody(): void
    {
        $manual = $this->render(self::MANUAL);

        self::assertSame('Manual for editors', $manual->title);
        self::assertStringNotContainsString('<h1', $manual->bodyHtml);
    }

    #[Test]
    public function renderDropsTheFrontMatter(): void
    {
        $manual = $this->render(self::MANUAL);

        self::assertStringNotContainsString('title:', $manual->introHtml . $manual->bodyHtml);
    }

    #[Test]
    public function renderReturnsTheTextBeforeTheFirstH2AsIntro(): void
    {
        $manual = $this->render(self::MANUAL);

        self::assertSame('<p>Welcome to the <strong>manual</strong>.</p>', trim($manual->introHtml));
    }

    #[Test]
    public function renderListsTheH2HeadingsWithTheirH3HeadingsAsTableOfContents(): void
    {
        $manual = $this->render(self::MANUAL);

        self::assertSame(
            [
                [
                    'id' => 'manual-working-in-typo3',
                    'title' => 'Working in TYPO3',
                    'number' => '',
                    'sections' => [
                        ['id' => 'manual-new-pages', 'title' => 'New pages', 'number' => ''],
                    ],
                ],
                ['id' => 'manual-news-items', 'title' => 'News items', 'number' => '', 'sections' => []],
            ],
            $manual->tableOfContents
        );
        self::assertStringContainsString('<h2 id="manual-working-in-typo3">Working in TYPO3</h2>', $manual->bodyHtml);
        self::assertStringContainsString('<h3 id="manual-new-pages">', $manual->bodyHtml);
    }

    #[Test]
    public function renderNumbersChaptersAndSectionsWithoutChangingTheirIds(): void
    {
        $manual = $this->render(self::MANUAL . "\n\n### Fields\n\n## Events\n\n### Fields", null, true);

        self::assertSame(
            ['1.', '1.1', '2.', '2.1', '3.', '3.1'],
            [
                $manual->tableOfContents[0]['number'],
                $manual->tableOfContents[0]['sections'][0]['number'],
                $manual->tableOfContents[1]['number'],
                $manual->tableOfContents[1]['sections'][0]['number'],
                $manual->tableOfContents[2]['number'],
                $manual->tableOfContents[2]['sections'][0]['number'],
            ]
        );
        self::assertSame('Working in TYPO3', $manual->tableOfContents[0]['title']);
        self::assertStringContainsString('<h2 id="manual-working-in-typo3">1. Working in TYPO3</h2>', $manual->bodyHtml);
        self::assertStringContainsString('<h3 id="manual-fields-1">3.1 Fields</h3>', $manual->bodyHtml);
    }

    #[Test]
    public function renderLeavesAnH3BeforeTheFirstChapterUnnumbered(): void
    {
        $manual = $this->render("# T\n\nWelcome.\n\n### Who this is for\n\n## Chapter", null, true);

        self::assertStringContainsString('<h3 id="manual-who-this-is-for">Who this is for</h3>', $manual->bodyHtml);
        self::assertSame([], $manual->tableOfContents[0]['sections']);
    }

    #[Test]
    public function renderLeavesHeadingIdsOutOfTheIntroBecauseItIsAlsoShownOnTheDashboard(): void
    {
        $manual = $this->render("# T\n\nWelcome.\n\n### Who this is for\n\n## Chapter");

        self::assertStringContainsString('<h3>Who this is for</h3>', $manual->introHtml);
        self::assertStringContainsString('<h3 id="manual-who-this-is-for">Who this is for</h3>', $manual->bodyHtml);
    }

    #[Test]
    public function renderLeavesRawHtmlOutOfTheTitleTheTableOfContentsAndTheAltText(): void
    {
        $manual = $this->render(
            "# Title <b>bold</b>\n\n![<img src=x onerror=alert(1)>Screen](missing.png)\n\n## Heading <script>alert(1)</script>"
        );

        self::assertSame('Title bold', $manual->title);
        self::assertSame('Heading alert(1)', $manual->tableOfContents[0]['title']);
        self::assertSame('<p><em>Screen</em></p>', trim($manual->introHtml));
    }

    #[Test]
    public function renderKeepsAccentsInTheIdOfAHeading(): void
    {
        $manual = $this->render("# T\n\n## Über uns");

        self::assertSame('manual-über-uns', $manual->tableOfContents[0]['id']);
    }

    #[Test]
    public function renderReturnsAnEmptyTitleForAManualWithoutH1(): void
    {
        $manual = $this->render("Welcome.\n\n## Chapter");

        self::assertSame('', $manual->title);
        self::assertSame('Chapter', $manual->tableOfContents[0]['title']);
    }

    #[Test]
    public function renderSupportsTables(): void
    {
        $manual = $this->render(self::MANUAL);

        self::assertStringContainsString('<th>You want to</th>', $manual->bodyHtml);
    }

    #[Test]
    public function renderStripsRawHtml(): void
    {
        $manual = $this->render("# T\n\n<script>alert(1)</script>\n\nText <img src=x onerror=alert(1)>");

        self::assertStringNotContainsString('<script', $manual->introHtml);
        self::assertStringNotContainsString('onerror', $manual->introHtml);
    }

    #[Test]
    public function renderRemovesUnsafeLinkTargets(): void
    {
        $manual = $this->render("# T\n\n[click](javascript:alert(1))");

        self::assertStringNotContainsString('javascript:', $manual->introHtml);
    }

    #[Test]
    public function renderOpensExternalLinksInANewWindowWithAHiddenLabel(): void
    {
        $manual = $this->render("# T\n\n[TYPO3](https://typo3.org/) and [below](#manual-news-items)");

        self::assertStringContainsString(
            '<a rel="noopener noreferrer" target="_blank" href="https://typo3.org/">TYPO3<span class="visually-hidden"> (opens in a new window)</span></a>',
            $manual->introHtml
        );
        self::assertStringContainsString('<a href="#manual-news-items">below</a>', $manual->introHtml);
    }

    #[Test]
    public function renderUsesTheResolvedUrlAndKeepsTheAltText(): void
    {
        $resources = __DIR__ . '/../Fixtures/Extension/Resources';
        $images = new ImageResolver(
            $resources . '/Private/Manual',
            $resources . '/Public',
            static fn(string $path): string => '/_assets/abc/' . $path
        );

        $manual = $this->render("# T\n\n![The page tree](../../Public/Manual/screen.png)", $images);

        self::assertStringContainsString('<img src="/_assets/abc/Manual/screen.png" alt="The page tree" />', $manual->introHtml);
    }

    #[Test]
    public function renderReplacesAnImageThatCannotBeShownByItsAltText(): void
    {
        $manual = $this->render("# T\n\n![Screenshot: the page tree](screenshots/missing.png)");

        self::assertSame('<p><em>Screenshot: the page tree</em></p>', trim($manual->introHtml));
    }
}
