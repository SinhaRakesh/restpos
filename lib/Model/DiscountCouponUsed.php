<?php

class Model_DiscountCouponUsed extends \Model_Table{
	public $table='discount_coupon_used';

	function init(){
		parent::init();
		
		$this->hasOne('Order','order_id');
		$this->hasOne('DiscountCoupon','discountcoupon_id');
		$this->hasOne('Customer','customer_id');
		$this->hasOne('User','applied_by_id')->defaultValue($this->app->auth->model->id);
		$this->addField('created_at')->type('datetime')->defaultValue($this->app->now);

		$this->addField('discount_coupon');
		$this->addField('discount_value');
		$this->addField('discount_amount');

		$this->add('dynamic_model/Controller_AutoCreator');
	}
}
