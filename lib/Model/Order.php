<?php

class Model_Order extends Model_Table{

	public $table = "order";

	function init(){
		parent::init();

		$this->hasOne('Customer','customer_id');
		$this->hasOne('User','user_id');
		$this->hasOne('Room','room_id');
		$this->hasOne('RoomTable','table_id');

		$this->addField('name')->system(true);
		$this->addField('created_at')->type('datetime')->system(true);

		$this->addExpression('created_date_only','DATE(created_at)');
		// $this->addExpresion('created_time_only','TIME(created_at)');
		$this->addField('updated_at')->type('datetime')->set($this->app->now)->system(true);

		$this->addField('status')->enum(['Draft','Paid','Void']);

		$this->addExpression('amount')->set(function($m,$q){
			$details = $m->refSQL('OrderDetail');
			return $q->expr("round([0],2)", [$details->sum('amount')]);
		});

		$this->hasMany('OrderDetail','order_id',null,'OrderDetail');
		$this->addHook('beforeSave',$this);
		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeSave(){
		$this['name'] = $this->id;
		if(!$this['created_at']){
			$this['created_at'] = $this->app->now;
		}
	}
}
