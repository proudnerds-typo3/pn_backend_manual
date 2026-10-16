<?php

// Metadata for the TER, which reads category and state from here. Keep 'version' in sync with
// composer.json: `typo3/tailor set-version` updates both. The TER rejects strict_types here.
$EM_CONF[$_EXTKEY] = [
    'title' => 'Backend Manual',
    'description' => 'Editor manuals in Markdown, registered by a sitepackage or extension and shown as dashboard widgets with a full view in the TYPO3 backend. Composer installations only.',
    'category' => 'be',
    'author' => 'Jacco van der Post',
    'author_email' => 'extensions@proudnerds.com',
    'author_company' => 'ProudNerds',
    'state' => 'stable',
    'version' => '1.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '14.3.0-14.3.99',
            'php' => '8.3.0-8.5.99',
            'dashboard' => '14.3.0-14.3.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
