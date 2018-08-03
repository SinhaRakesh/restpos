<?php

class Model_Order extends Model_Table{

	public $table = "order";

	function init(){
		parent::init();

		$this->hasOne('Customer','customer_id');
		$this->hasOne('User','user_id');
		$this->hasOne('Room','room_id');
		$this->hasOne('RoomTable','table_id');

		$this->hasOne('User','created_by_id')->defaultValue(@$this->app->auth->model->id);
		$this->hasOne('User','paid_by_id')->defaultValue(@$this->app->auth->model->id);
		$this->hasOne('User','void_by_id')->defaultValue(@$this->app->auth->model->id);

		$this->addField('serial');
		$this->addField('name');
		$this->addField('created_at')->type('datetime');

		$this->addField('status')->enum(['Running','Complete','Paid','Void'])->defaultValue('Running');
		$this->addField('paid_at')->type('datetime')->system(true);
		$this->addField('void_at')->type('datetime')->system(true);

		$this->addField('discount_voucher');
		$this->addField('discount_amount')->defaultValue(0);

		$this->addExpression('created_date_only','DATE(created_at)');
		$this->addExpression('created_time_only','TIME(created_at)');

		$this->addExpression('gross_amount')->set(function($m,$q){
			$details = $m->refSQL('OrderDetail');
			return $q->expr("round([0],2)", [$details->sum('amount')]);
		});

		$this->addExpression('net_amount')->set(function($m,$q){
			return $q->expr("round(([0]-IFNULL([1],0)),2)", [$m->getElement('gross_amount'),$m->getElement('discount_amount')]);
		});


		$this->hasMany('OrderDetail','order_id',null,'OrderDetail');
		
		$this->addHook('beforeSave',$this);
		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeSave(){

		if(!$this['name']){
			$this['name'] = $this->id;
		// 	$this['name'] = $this->_dsql()->del('fields')->field('max(CAST(name AS decimal))')->where('status','Paid')->getOne() + 1;
		}
		
		if(!$this['created_at']){
			$this['created_at'] = $this->app->now;
		}
		if($this['status'] == "Paid"){
			$this['paid_at'] = $this->app->now;
		}
		if($this['status'] == "Void"){
			$this['void_at'] = $this->app->now;
		}
	}

}
