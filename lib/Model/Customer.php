<?php

class Model_Customer extends Model_Table{
	public $table = "customer";

	function init(){
		parent::init();

		$this->addField('name')->mandatory(true)->caption('Full Name');
		$this->addField('email_id');
		$this->addField('mobile_no');
		$this->addField('address')->type('text');
		$this->addField('city');
		$this->addField('state');
		$this->addField('country');
		$this->addField('is_active')->type('boolean')->defaultValue(true);
		$this->addField('birthday')->type('date');
		$this->addField('anniversary')->type('date');
		
		$this->add('dynamic_model/Controller_AutoCreator');

	}
}
