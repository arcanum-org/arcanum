<?php

declare(strict_types=1);

return [

    /**
     * DEFAULT FORMAT
     * --------------
     *
     * The fallback format when no file extension is present in the URL.
     * Convention routes (e.g., /health) will use this format.
     */
    'default' => 'json',

    /**
     * FORMATS
     * -------
     *
     * Each entry maps a file extension to a content type and renderer class.
     * Add your own formats or override built-in ones here.
     *
     * To disable a format, remove it from this array.
     */
    'formats' => [
        'json' => [
            'content_type' => 'application/json',
            'renderer' => \Arcanum\Shodo\JsonRenderer::class,
        ],
    ],

];
