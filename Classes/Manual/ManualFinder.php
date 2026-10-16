<?php

declare(strict_types=1);

namespace ProudNerds\PnBackendManual\Manual;

use ProudNerds\PnBackendManual\Widgets\ManualWidget;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Dashboard\WidgetRegistry;

/**
 * Finds a manual among the dashboard widgets the backend user may use.
 * The only class that touches WidgetRegistry, which is internal to the dashboard: check it on core updates.
 */
final readonly class ManualFinder
{
    public function __construct(
        private WidgetRegistry $widgetRegistry,
    ) {}

    public function find(ServerRequestInterface $request, string $identifier): ?ManualWidget
    {
        if (!array_key_exists($identifier, $this->widgetRegistry->getAvailableWidgets())) {
            return null;
        }
        $widget = $this->widgetRegistry->getAvailableWidget($request, $identifier);

        return $widget instanceof ManualWidget ? $widget : null;
    }
}
