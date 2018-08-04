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

		$this->addField('discount_coupon');
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


	function applyDiscountCoupon($coupon){
		$result = ['result'=>false,'message'=>'Coupon not found'];
		$coupon = trim($coupon);
		if(!$coupon OR $coupon == " " OR $coupon == null) return $result;

		$dc = $this->add('Model_DiscountCoupon');
		$dc->addCondition('name',$coupon);
		$dc->tryLoadAny();

		if(!$dc->loaded()){
			$result['message'] = "Coupon is not valid";
			return $result;
		}

		if($dc->isExpired()){
			$result['message'] = "Coupon expired on date ".$dc['expire_date'];
			return $result;
		}

		$model_used = $this->add('Model_DiscountCouponUsed');
		$model_used->addCondition('discountcoupon_id',$dc->id);

		$used_count = $model_used->count()->getOne();
		if($used_count >= $dc['max_limit']){
			$result['message'] = "Coupon Max Limit (".$dc['max_limit'].") Reached ";
			return $result;
		}

		if($this['customer_id']){
			$model_used->addCondition('customer_id',$this['customer_id']);
			$cst_used_count = $model_used->count()->getOne();
			if($dc['one_user_how_many_time'] >= $cst_used_count){
				$result['message'] = 'Coupon is already used by customer '.$cst_used_count.' times';
				return $result;
			}
		}


		$discount_amount = $dc['discount_value'];
		if(strpos($dc['discount_value'],'%') !== false){
			$discount_array = explode("%",$dc['discount_value']);
			$discount_percentage = trim($discount_array[0]);
			$discount_amount = round((($this['gross_amount'] * $discount_percentage) / 100.00),2);
		}

		
		$used_model = $this->add('Model_DiscountCouponUsed');
		$used_model->addCondition('order_id',$this->id);
		$used_model->addCondition('discountcoupon_id',$dc->id);
		$used_model->addCondition('customer_id',$this['customer_id']);
		$used_model->tryLoadAny();
		
		$used_model['discount_coupon'] = $dc['name'];
		$used_model['discount_value'] = $dc['discount_value'];
		$used_model['discount_amount'] = $discount_amount;
		$used_model->save();

		$result['result'] = true;
		$result['message'] = 'Coupon Applied Successfully';
		$result['discount_amount'] = $discount_amount;
		return $result;

	}
}
