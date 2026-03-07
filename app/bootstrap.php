<?php

declare(strict_types=1);
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

/**
 * Environment initialization
 */
error_reporting(E_ALL);
if (in_array('phar', \stream_get_wrappers())) {
    stream_wrapper_unregister('phar');
}

require_once __DIR__ . '/autoload.php';
// Sets default autoload mappings, may be overridden in Bootstrap::create
\Magento\Framework\App\Bootstrap::populateAutoloader(BP, []);

/* Custom umask value may be provided in optional mage_umask file in root */
$umaskFile = BP . '/magento_umask';
$mask = file_exists($umaskFile) ? octdec(file_get_contents($umaskFile)) : 002;
umask($mask);

if (
    (!empty($_SERVER['MAGE_PROFILER']) || file_exists(BP . '/var/profiler.flag'))
    && isset($_SERVER['HTTP_ACCEPT'])
    && str_contains((string) $_SERVER['HTTP_ACCEPT'], 'text/html')
) {
    $profilerConfig = isset($_SERVER['MAGE_PROFILER']) && strlen($_SERVER['MAGE_PROFILER'])
        ? $_SERVER['MAGE_PROFILER']
        : trim(file_get_contents(BP . '/var/profiler.flag'));

    if ($profilerConfig) {
        $profilerConfig = json_decode((string) $profilerConfig, true) ?: $profilerConfig;
    }

    Magento\Framework\Profiler::applyConfig(
        $profilerConfig,
        BP,
        !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'
    );
}

date_default_timezone_set('UTC');

/*  For data consistency between displaying (printing) and serialization a float number */
ini_set('precision', 14);
ini_set('serialize_precision', 14);
