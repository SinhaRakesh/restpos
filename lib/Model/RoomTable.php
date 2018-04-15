<?php

class Model_RoomTable extends Model_Table{
	public $table = "room_table";

	function init(){
		parent::init();
		
		$this->hasOne('Room','room_id');

		$this->addField('name');
		$this->addField('size')->enum(['Small','Medium','Large','Other']);
		$this->addField('shape')->enum(['Circle','Square','Rectangle','Oval','Other']);
		$this->addField('member')->type('int');
		
		$this->add('dynamic_model/Controller_AutoCreator');
	}
}
