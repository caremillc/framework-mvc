<?php
namespace Careminate\Routing;

/**
 * RouteCompiler
 *
 * Converts route patterns into regular expressions and produces a
 * token list describing each parameter (name, optional, wildcard, pattern).
 *
 * Supports Laravel-style patterns:
 *  - {id}
 *  - {id?}
 *  - {path*}  (greedy multi-segment wildcard)
 *  - {id:\d+} (custom regex)
 */
class RouteCompiler
{
    /**
     * Compile a route pattern to a regex and an ordered list of parameter tokens.
     *
     * @param string $route Route pattern e.g. "user/{id}/{slug?}/{path*}"
     * @return array ['regex' => <string>, 'tokens' => [['name'=>..,'optional'=>..,'wildcard'=>..], ...]]
     */
    public static function compile(string $route): array
    {
        $route = trim($route, '/');
        if ($route === '') {
            return ['regex' => '#^/?$#', 'tokens' => []];
        }

        $tokens = [];

        // Escape regex special chars except parameter braces
        $escaped = preg_replace_callback(
            '/\{[^}]+\}|[^{}]+/',
            function ($m) use (&$tokens) {
                $part = $m[0];

                // if it's a parameter
                if ($part[0] === '{' && substr($part, -1) === '}') {
                    $inside = substr($part, 1, -1);

                    // wildcard multi segment: name*
                    if (str_ends_with($inside, '*')) {
                        $name = rtrim($inside, '*');
                        $tokens[] = ['name' => $name, 'optional' => true, 'wildcard' => true, 'pattern' => '.*'];
                        // greedy match including slashes, allow empty
                        return '(?P<' . $name . '>.*)';
                    }

                    // optional marker ?
                    $optional = false;
                    if (str_ends_with($inside, '?')) {
                        $optional = true;
                        $inside = rtrim($inside, '?');
                    }

                    // custom regex constraint?
                    $name = $inside;
                    $pattern = '[^/]+';
                    if (strpos($inside, ':') !== false) {
                        [$name, $custom] = explode(':', $inside, 2);
                        $pattern = $custom;
                    }

                    $tokens[] = ['name' => $name, 'optional' => $optional, 'wildcard' => false, 'pattern' => $pattern];

                    // For optional params we make the preceding slash optional as well using (?:/pattern)?
                    $segment = '(?P<' . $name . '>' . $pattern . ')';
                    return $optional ? '(?:/' . $segment . ')?' : '/' . $segment;
                }

                // static text - escape regex chars
                return preg_quote($part, '#');
            },
            $route
        );

        // The escaping above will produce leading literal slashes for param pieces. Ensure regex anchors and optional root
        $regex = '#^' . $escaped . '/?$#';

        return ['regex' => $regex, 'tokens' => $tokens];
    }
}
