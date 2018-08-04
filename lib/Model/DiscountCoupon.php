<?php

class Model_DiscountCoupon extends Model_Table{
	public $table = "discount_coupon";

	function init(){
		parent::init();

		$this->hasOne('User','created_by_id')->defaultValue($this->app->auth->model->id)->system(true);
		$this->addField('name')->caption('Discount Coupon');
		$this->addField('start_date')->caption('Starting Date')->type('date')->defaultValue($this->app->today)->mandatory(true);
		$this->addField('expire_date')->type('date');
		$this->addField('max_limit')->type('Number')->defaultValue(1)->hint('coupon applied on how many order ie. 100');
		$this->addField('one_user_how_many_time')->type('Number')->defaultValue(1)->hint('How many time it can be used by one customer ?');
		$this->addField('created_at')->type('date')->defaultValue($this->app->today)->sortable(true)->system(true);
		
		$this->addField('discount_value')->hint('Discount Amount \ Percentage ie. type 100 or 10%');
		$this->addField('status')->enum(['Active','InActive'])->defaultValue('Active');

		$this->hasMany('DiscountCouponUsed','discountcoupon_id');

		
		$this->addExpression('coupon_used_count')->set(function($m,$q){
			$used = $m->add('Model_DiscountCouponUsed');
			$used->addCondition('discountcoupon_id',$m->getElement('id'));
			return $used->count();
		});
		// $this->is([
		// 		'name|to_trim|required',
		// 		'start_date|required',
		// 		'expire_date|required',
		// 		'no_of_person|number|>0',
		// 		'one_user_how_many_time|number|>0',
		// 		]);

		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function isExpired(){
		$current_date = $this->app->now;
		if( strtotime($current_date) > strtotime($this['expire_date']))
			return true;
		else
			return false;
	}
}
