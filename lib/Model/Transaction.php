<?php

class Model_Transaction extends Model_Table{
	public $table = "transaction";

	function init(){
		parent::init();

		$this->hasOne('Order','order_id');
		$this->hasOne('User','user_id')->defaultValue($this->app->auth->model->id)->system(true);
		
		$this->addField('created_at')->type('datetime')->defaultValue($this->app->now)->system(true);
		$this->addField('payment_mode')->setValueList(['Cash'=>'Cash','Cheque'=>'Cheque','Other'=>'Other/ Online'])->defaultValue('Cash');
		$this->addField('cheque_no')->type('Number')->defaultValue(0);
		$this->addField('cheque_date')->type('datetime');
		$this->addField('other_transaction_date')->type('date')->caption('Payment Date');
		$this->addField('amount')->defaultValue(0);
		$this->addField('narration')->type('text');

		$this->addField('discount_amount');
		$this->addField('discount_coupon');

		$this->add('dynamic_model/Controller_AutoCreator');
	}
}
