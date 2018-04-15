<?php

class Model_MenuCategory extends Model_Table{
	public $table = "menu_category";

	function init(){
		parent::init();

		$this->addField('name');

		$this->add('dynamic_model/Controller_AutoCreator');
	}
}
