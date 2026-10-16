<?php

declare(strict_types=1);

namespace ProudNerds\PnBackendManual\Rendering;

final readonly class RenderedManual
{
    /**
     * @param list<array{id: string, title: string, number: string, sections: list<array{id: string, title: string, number: string}>}> $tableOfContents
     *        the h2 headings with their h3 headings; "number" is empty when the manual is not numbered
     */
    public function __construct(
        public string $title,
        public string $introHtml,
        public string $bodyHtml,
        public array $tableOfContents,
    ) {}
}
