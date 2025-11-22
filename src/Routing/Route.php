<?php 
namespace Careminate\Routing;

use Careminate\Routing\Traits\Methods;

/**
 * Route
 *
 * Acts as a convenient facade over the core Router class.
 * 
 * The Route class extends Router and mixes in the "Methods" trait,
 * which provides expressive route definition shortcuts such as:
 * 
 *     Route::get('users', UserController::class, 'index');
 *     Route::post('users', UserController::class, 'store');
 *
 * Since Route extends Router, all static route registrations are
 * written directly into the Router's internal route table.
 *
 * This design mirrors common framework styles (e.g., Laravel),
 * providing a clean, fluent API while leaving routing logic inside
 * the Router class.
 */
class Route extends Router
{
    // Import HTTP verb helper methods: get(), post(), put(), patch(), delete()
    use Methods;
}
