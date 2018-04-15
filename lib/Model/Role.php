<?php

class Model_Role extends Model_Table{
	public $table = "role";

	function init(){
		parent::init();

		$this->addField('name')->mandatory(true)->caption('Full Name');

		$this->add('dynamic_model/Controller_AutoCreator');
	}
}
