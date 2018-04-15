<?php

class Model_MenuItem extends Model_Table{
	public $table = "menu_item";

	function init(){
		parent::init();

		$this->hasOne('MenuCategory','menu_category_id');
		$this->addField('name');
		$this->addField('code')->type('number');
		$this->addField('price')->type('number')->hint('unit wise');
		$this->addField('unit');
		
		$this->add('dynamic_model/Controller_AutoCreator');
	}
}
