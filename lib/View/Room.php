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
		$this->template->trySet('room_name',$this->room_model['name']);
	}

	function defaultTemplate(){
		return ['view/room'];
	}
}