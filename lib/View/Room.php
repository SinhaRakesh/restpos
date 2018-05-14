<?php

class View_Room extends \CompleteLister {
	public $room_model;

	function init(){
		parent::init();

		$tables = $this->add('Model_RoomTable')
				->addCondition('is_active',true)
				->addCondition('room_id',$this->room_model->id)
				;
		$this->setModel($tables);
	}

	function defaultTemplate(){
		return ['view/room'];
	}
}