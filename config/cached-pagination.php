<?php

return [

    /**
     * The default Time To Live (TTL) for cached paginations in seconds.
     * You can use an integer for seconds or a \DateInterval object.
     * The default is 1 hour (3600 seconds).
     */
    'ttl' => 3600,

    /**
     * Determine whether the pagination cache should be automatically
     * cleared when a model is updated.
     */
    'clear_on_update' => true,

    /**
     * Determine whether the pagination cache should be automatically
     * cleared when a model is created.
     */
    'clear_on_create' => true,

    /**
     * Determine whether the pagination cache should be automatically
     * cleared when a model is deleted.
     */
    'clear_on_delete' => true,

];
