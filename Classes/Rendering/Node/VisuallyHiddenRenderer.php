<?php

declare(strict_types=1);

namespace ProudNerds\PnBackendManual\Rendering\Node;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use League\CommonMark\Util\Xml;

final class VisuallyHiddenRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): HtmlElement
    {
        if (!$node instanceof VisuallyHidden) {
            throw new \InvalidArgumentException('Incompatible node type: ' . $node::class, 1791980001);
        }

        return new HtmlElement('span', ['class' => 'visually-hidden'], Xml::escape($node->text));
    }
}
