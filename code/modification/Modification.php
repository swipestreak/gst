<?php

class StreakGST_Modification extends Modification {

    private static $db = array(
        'GST' => StreakModule::PriceSchema,
        'Currency' => StreakModule::CurrencySchema
    );

    private static $defaults = array(
        'SubTotalModifier' => true,
        'SortOrder' => 160
    );

    public function onBeforeWrite() {
        parent::onBeforeWrite();
        if (!$this->Currency) {
            $this->Currency = ShopConfig::current_shop_config()->BaseCurrency;
        }
    }

    /**
     * Iterate over the order items and calculate the gst
     * for each depending on StreakGSTModule.config.currency_percentages()
     *
     * @param Order     $order
     * @param null $value
     * @throws ValidationException
     * @throws null
     */
    public function add($order, $value = null) {
        $gst = new Price();
        // TODO copy this line wherever 'NZD' is hardcoded for a currency
        $gst->setCurrency(ShopConfig::current_shop_config()->BaseCurrency);

        $descriptions = array();

        /** @var Price|null $calculated */
        $calculated = null;

        /** @var Item $item */
        foreach ($order->Items() as $item) {
            if ($price = $item->Price()) {
                $currency = $price->getCurrency();

                if ($rate = self::rate_for_currency($currency)) {
                    if (!$calculated) {
                        $calculated = new Price();
                    }
                    $percentage = Zend_Locale_Math::Div($rate, 100.0, 10);

                    $taxed = Zend_Locale_Math::Mul(
                        $price,
                        $percentage,
                        10

                    );

                    $temp = Zend_Locale_Math::Add(
                        $calculated,
                        $taxed,
                        10
                    );
                    $calculated->setAmount($temp);
                    $descriptions[$currency] = static::description($currency);
                }
            }
        }
        if ($calculated) {
            $mod = new StreakGST_Modification();

            $mod->OrderID = $order->ID;
            $mod->Price = 0;
            $mod->Description = implode("<br/>\n", $descriptions);
            $mod->Value = $calculated->getAmount();
            $mod->write();
        }
    }
    /**
     * Add shipping region code dropdown to form.
     * @return FieldList
     */
    public function getFormFields() {
        $fields = new FieldList();

        $field = new StreakGST_ModifierField(
            $this,
            'Including GST of'
        );
        /** @var Price $price */
        $price = Price::create();
        $price->setAmount($this->Value);
        $field->setAmount($price);

        $fields->push($field);
        return $fields;
    }

    public static function description($currency) {
        $rate = self::rate_for_currency($currency);

        return _t(
            'StreakGSTModule.Description',
            "Includes GST at {percentage}%",
            array('percentage' => $rate)
        );
    }

    public static function rate_for_currency($currency) {
        $rates = StreakGSTModule::currency_percentages();

        return isset($rates[$currency]) ? $rates[$currency] : 0;
    }
}