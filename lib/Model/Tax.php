<?php

class Model_Tax extends Model_Table{
	public $table = "tax";

	function init(){
		parent::init();

		$this->addField('name')->mandatory(true);
		$this->addField('tax_percentage')->type('number');
		$this->addField('is_active')->type('boolean')->defaultValue(true);

		$this->add('dynamic_model/Controller_AutoCreator');
	}
}
