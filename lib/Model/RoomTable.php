<?php

class Model_RoomTable extends Model_Table{
	public $table = "room_table";

	function init(){
		parent::init();
		
		$this->hasOne('Room','room_id');

		$this->addField('name');
		$this->addField('size')->enum(['Small','Medium','Large','Other']);
		$this->addField('shape')->enum(['Circle','Square','Rectangle','Oval']);
		$this->addField('member')->type('int');
	
		$this->addField('is_active')->type('boolean')->defaultValue(true);
		$this->addExpression('running_order_id')->set(function($m,$q){
			$order = $m->add('Model_Order',['table_alias'=>'orderroomtabel'])
				->addCondition('table_id',$m->getElement('id'))
				->addCondition('status','Running')
				->setLimit(1);
			return $q->expr('[0]',[$order->fieldQuery('id')]);
		});

		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function getOrder(){
		$order = $this->add('Model_Order');
		$order->addCondition('table_id',$this->id);
		$order->addCondition('status','Running');
		$order->setOrder('id','desc');
		$order->setLimit(1);
		return $order;
	}
}
