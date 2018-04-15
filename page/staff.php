<?php


class page_staff extends Page {
	
	public $title = "Staff Management";

	function init(){
		parent::init();

		$m = $this->add('Model_User');
		$c = $this->add('CRUD',['entity_name'=>'Staff']);
		$c->setModel($m);
	}
}