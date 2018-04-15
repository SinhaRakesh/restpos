<?php

class Model_Room extends Model_Table{
	public $table = "room";

	function init(){
		parent::init();
		
		$this->addField('name');

		$this->addField('is_active')->type('boolean')->defaultValue(true);
		
		$this->add('dynamic_model/Controller_AutoCreator');
	}
}
