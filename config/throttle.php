<?php

declare(strict_types=1);

return [

    /**
     * RATE LIMIT
     * ----------
     *
     * Maximum number of requests a single client (by IP) can make
     * within the configured window.
     */
    'limit' => 60,

    /**
     * WINDOW
     * ------
     *
     * Duration of the rate-limit window in seconds.
     * After this period, the client's quota resets.
     */
    'window' => 60,

    /**
     * STRATEGY
     * --------
     *
     * The throttling algorithm to use.
     *
     * Supported: "token_bucket", "sliding_window"
     *
     * Token bucket allows controlled bursts — a client that has been
     * idle accumulates capacity. Sliding window is strict — no bursts,
     * smooth enforcement.
     */
    'strategy' => 'token_bucket',

];
