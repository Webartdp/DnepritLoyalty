<?php

declare(strict_types=1);

$file =
    dirname(__DIR__) .
    '/core/components/dnepritloyalty/index.class.php';

$source = file_get_contents(
    $file
);

if (
    strpos(
        $source,
        'class DnepritloyaltyIndexManagerController'
    ) === false
) {
    fwrite(
        STDERR,
        "Root manager controller class is incorrect.\n"
    );

    exit(1);
}

if (
    strpos(
        $source,
        'public static function getDefaultController()'
    ) === false
) {
    fwrite(
        STDERR,
        "getDefaultController must be static for MODX 2.8.1.\n"
    );

    exit(1);
}

echo
    "Manager controller checks passed.\n";
