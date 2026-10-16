<?php

declare(strict_types=1);

namespace ProudNerds\PnBackendManual\Rendering;

final readonly class RenderedManual
{
    /**
     * @param list<array{id: string, title: string, number: string, sections: list<array{id: string, title: string, number: string}>}> $tableOfContents
     *        the h2 headings with their h3 headings; "number" is empty when the manual is not numbered
     * @param string $version from the front matter, empty when not given
     * @param ?\DateTimeImmutable $updated the "date" from the front matter
     */
    public function __construct(
        public string $title,
        public string $introHtml,
        public string $bodyHtml,
        public array $tableOfContents,
        public string $version = '',
        public ?\DateTimeImmutable $updated = null,
    ) {}
}
