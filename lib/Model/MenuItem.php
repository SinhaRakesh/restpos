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
		// $this->addField('is_tax_applicable')->type('boolean')->defaultValue(true);
		$this->hasOne('Tax','tax_id');
		// $this->addField('is_tax_applicable')->type('boolean')->defaultValue(true);
		
		$this->addField('is_active')->type('boolean')->defaultValue(true);
		
		$this->add('dynamic_model/Controller_AutoCreator');
	}
}
