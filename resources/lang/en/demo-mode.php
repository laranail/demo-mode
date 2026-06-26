<?php

declare(strict_types=1);

return [
    'banner' => [
        'message' => 'This is a live demo. Some actions are disabled and data resets periodically.',
        'reset_in' => 'Resets in :time',
        'dismiss' => 'Dismiss',
    ],

    'blocked' => [
        'title' => 'Action disabled',
        'message' => 'This action is disabled in demo mode.',
        'feature' => 'The :feature feature is disabled in demo mode.',
        'write' => 'Changes cannot be saved in demo mode.',
    ],

    'reset' => [
        'started' => 'Demo reset started.',
        'complete' => 'The demo has been reset.',
        'refused' => 'Demo reset refused: :reason.',
    ],
];
