<?php

declare(strict_types=1);

use ProudNerds\PnBackendManual\Controller\ManualController;

return [
    'pn_backend_manual' => [
        'path' => '/manual',
        'target' => ManualController::class . '::showAction',
        'methods' => ['GET'],
    ],
];
