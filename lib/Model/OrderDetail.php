<?php

class Model_OrderDetail extends Model_Table{

	public $table = "order_detail";

	function init(){
		parent::init();

		$this->hasOne('Order','order_id');
		$this->hasOne('MenuItem','menu_item_id');

		$this->addField('name')->system(true);
		$this->addField('qty')->type('number');
		$this->addField('price')->type('number');

		$this->addExpression('amount')->set(function($m,$q){
			return $q->expr('([0]*[1])',[$m->getElement('qty'),$m->getElement('price')]);
		})->type('number');

		$this->addField('narration')->type('text');
		$this->addField('tax_percentage');

		$this->addField('created_at')->type('datetime')->set($this->app->now)->system(true);

		$this->add('dynamic_model/Controller_AutoCreator');
	}
	
}
