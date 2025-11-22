<?php

namespace Careminate\Routing;

/**
 * Class Segment
 *
 * A robust helper for safely extracting and working with URI path segments.
 * This class normalizes the request URI, strips the application root directory,
 * handles edge cases, and provides segment retrieval utilities.
 *
 * Designed for use inside a routing system.
 */
class Segment
{
    /**
     * Cached normalized URI value.
     *
     * @var string|null
     */
    protected static ?string $cachedUri = null;

    /**
     * Cached URI segments array.
     *
     * @var array|null
     */
    protected static ?array $cachedSegments = null;

    /**
     * Get the normalized request URI path without ROOT_DIR and without query strings.
     *
     * Example:
     *   ROOT_DIR = "/caremi"
     *   Request: /caremi/user/20/edit?x=1
     *   Returns: /user/20/edit
     *
     * Normalization includes:
     *   - Removing query string
     *   - Removing ROOT_DIR prefix
     *   - Ensuring leading slash
     *   - Removing multiple slashes
     *   - URL-decoding
     *
     * @return string
     */
    public static function uri(): string
    {
        if (static::$cachedUri !== null) {
            return static::$cachedUri;
        }

        // Raw path from URL
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

        // Decode URL-encoded characters (e.g., %20)
        $path = urldecode($path);
// dd($path);
        // Normalize ROOT_DIR matching
        if (defined('ROOT_DIR') && ROOT_DIR !== '/' && ROOT_DIR !== '') {
            // Remove only if path actually starts with ROOT_DIR
            if (str_starts_with($path, ROOT_DIR)) {
                $path = substr($path, strlen(ROOT_DIR));
            }
        }

        // Ensure leading slash
        if ($path === '' || $path[0] !== '/') {
            $path = '/' . $path;
        }

        // Remove duplicate slashes (e.g., //user///list)
        $path = preg_replace('#/+#', '/', $path);

        // Remove trailing slash unless it's the root "/"
        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }

        return static::$cachedUri = $path;
    }

    /**
     * Split the normalized URI into clean segments.
     *
     * Example:
     *   URI: /user/10/edit
     *   Returns: ["user", "10", "edit"]
     *
     * Filters:
     *   - Empty segments removed
     *   - No leading empty elements
     *
     * @return array
     */
    public static function all(): array
    {
        if (static::$cachedSegments !== null) {
            return static::$cachedSegments;
        }

        $segments = explode('/', static::uri());
// dd($segments);
        // Remove empty values caused by leading/trailing slashes
        $segments = array_values(array_filter($segments, fn($s) => $s !== ''));

        return static::$cachedSegments = $segments;
    }

    /**
     * Get a URI segment by zero-based index.
     *
     * Example:
     *   URI: /user/10/edit
     *   get(0) => "user"
     *   get(1) => "10"
     *   get(2) => "edit"
     *
     * @param int $index The zero-based index of the segment.
     * @return string|null Returns null if the segment does not exist.
     */
    public static function get(int $index): ?string
    {
        $segments = static::all();
        return $segments[$index] ?? null;
    }

    /**
     * Check whether a given segment exists.
     *
     * @param int $index
     * @return bool
     */
    public static function has(int $index): bool
    {
        return isset(static::all()[$index]);
    }

    /**
     * Get the total number of URI segments.
     *
     * @return int
     */
    public static function count(): int
    {
        return count(static::all());
    }

    /**
     * Reset cached data (useful for testing or CLI environments).
     *
     * @return void
     */
    public static function reset(): void
    {
        static::$cachedUri = null;
        static::$cachedSegments = null;
    }
}
