<?php
class StreakGSTModule extends Object {
    // map of currency to GST rate e.g. ['NZD' => 15.0]
    private static $currency_percentages = array();

    public static function currency_percentages() {
        return static::config()->get('currency_percentages');
    }

}