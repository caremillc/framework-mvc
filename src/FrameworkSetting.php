<?php
namespace Careminate;

use Careminate\Sessions\Session;

class FrameworkSetting
{
    /**
     * Set the default timezone for the framework.
     *
     * @return void
     */
    public static function setTimeZone(): void
    {
        $timezone = config('app.timezone', 'UTC');
        date_default_timezone_set($timezone);
    }

    /**
     * Get the current default timezone.
     *
     * @return string
     */
    public static function getTimeZone(): string
    {
        return date_default_timezone_get();
    }

    /**
     * get current locale lang
     * @return string
     */
    public static function getLocale()
    {
        return Session::has('locale') && ! empty(Session::get('locale')) ? Session::get('locale') : config('app.locale');
    }

    /**
     * change locale lang
     * @param string $locale
     *
     * @return string
     */
    public static function setLocale(string $locale): string
    {

        Session::make('locale', $locale);

        return Session::get('locale');
    }

}
