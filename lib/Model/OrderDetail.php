<?php

class Model_OrderDetail extends Model_Table{

	public $table = "order_detail";

	function init(){
		parent::init();

		$this->hasOne('Order','order_id');
		$this->hasOne('MenuItem','menu_item_id');

		$this->addField('name')->system(true);
		$this->addField('qty')->type('number')->defaultValue(1);
		$this->addField('price')->type('number');

		$this->addExpression('amount')->set(function($m,$q){
			return $q->expr('([0]*[1])',[$m->getElement('qty'),$m->getElement('price')]);
		})->type('number');
		
		$this->addField('narration')->type('text');

		$this->hasOne('Tax','tax_id');

		$this->addField('created_at')->type('datetime')->set($this->app->now)->system(true);

		$this->add('dynamic_model/Controller_AutoCreator');

		// $this->is([
		// 	'menu_item_id|required',
		// 	'order_id|required',
		// 	'qty|required',
		// 	'price|to_trim|required'
		// ]);
	}


	function updateFromItem($save_model=false){
		if(!$this['menu_item_id'])
			throw $this->exception('please select any menu item', 'ValidityCheck')
				->setField('menu_item_id')
				->addMoreInfo('Field', 'menu_item_id');

		$menu_item = $this->add('Model_MenuItem')->load($this['menu_item_id']);
		$this['price'] = $menu_item['price'];
		$this['tax_id'] = $menu_item['tax_id'];
		if($save_model){
			$this->save();
		}
	}
}
