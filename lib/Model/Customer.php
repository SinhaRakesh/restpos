<?php

class Model_Customer extends Model_Table{
	public $table = "customer";

	function init(){
		parent::init();

		$this->addField('name')->mandatory(true)->caption('Full Name');
		$this->addField('email_id');
		$this->addField('mobile_no');
		$this->addField('address');
		$this->addField('city');
		$this->addField('state');
		$this->addField('country');
		$this->addField('is_active')->type('boolean')->defaultValue(true);
		
		$this->add('dynamic_model/Controller_AutoCreator');
	}
}
