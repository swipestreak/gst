<?php
/**
 * Form field that represents {@link RegionalshippingRate}s in the Checkout form.
 */
class StreakGST_ModifierField extends ModificationField_Hidden {

	/**
	 * The amount this field represents e.g: 15% * order subtotal
	 *
	 * @var Money
	 */
	protected $amount;

	/**
	 * Render field with the appropriate template.
	 *
	 * @see FormField::FieldHolder()
	 * @return String
	 */
	public function FieldHolder($properties = array()) {
		return $this->renderWith($this->template);
	}

	/**
	 * Set the amount that this field represents.
	 *
	 * @param Money $amount
	 */
	public function setAmount(Money $amount) {
		$this->amount = $amount;
		return $this;
	}

	/**
	 * Return the amount for this tax rate for displaying in the {@link CheckoutForm}
	 *
	 * @return String
	 */
	public function Description() {
		return $this->amount->Nice();
	}
}

class StreakGSTModifierField_Extension extends Extension {


}