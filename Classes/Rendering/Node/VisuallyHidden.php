<?php

declare(strict_types=1);

namespace ProudNerds\PnBackendManual\Rendering\Node;

use League\CommonMark\Node\Inline\AbstractInline;

final class VisuallyHidden extends AbstractInline
{
    public function __construct(public readonly string $text)
    {
        parent::__construct();
    }
}
