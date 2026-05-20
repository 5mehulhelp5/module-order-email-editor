<?php

declare(strict_types=1);

namespace ETechFlow\OrderEmailEditor\Model\Performance;

/**
 * Thin profiler-tagging helper for the OrderEmailEditor module's hot paths.
 *
 * Wraps Tideways span calls so traces captured in production are filterable
 * by `ETechFlow_OEE_*` instead of relying on class-name auto-trace.
 * No-op when Tideways isn't installed.
 */
final class Profiler
{
    private static ?bool $tidewaysAvailable = null;

    /**
     * @param string $name
     * @return object|null
     */
    public static function start(string $name): ?object
    {
        if (self::$tidewaysAvailable === null) {
            self::$tidewaysAvailable = class_exists('\\Tideways\\Profiler', false);
        }
        if (!self::$tidewaysAvailable) {
            return null;
        }
        try {
            return \Tideways\Profiler::createSpan($name);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * @param object|null $span
     */
    public static function stop(?object $span): void
    {
        if ($span === null) {
            return;
        }
        try {
            if (method_exists($span, 'stopTimer')) {
                $span->stopTimer();
            }
        } catch (\Throwable $e) {
            // Never let instrumentation surface to the admin.
        }
    }
}
