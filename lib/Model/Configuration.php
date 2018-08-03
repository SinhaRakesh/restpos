<?php

class Model_Configuration extends Model_Table{
	public $table = "configuration";

	function init(){
		parent::init();

		$this->addField('bill_master_layout')->type('text');
		$this->addField('bill_detail_layout')->type('text');

		$this->addField('kot_master_layout')->type('text');
		$this->addField('kot_detail_layout')->type('text');
		
		$this->add('dynamic_model/Controller_AutoCreator');
	}
}
