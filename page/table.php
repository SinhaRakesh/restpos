<?php


class page_table extends Page {
	
	public $title = "Table Management";

	function init(){
		parent::init();

		$model = $this->add('Model_Room')
				->addCondition('is_active',true);
		$tabs = $this->add('Tabs');
		foreach ($model as $m){
        	$t = $tabs->addTab($m['name']);

        	$item = $this->add('Model_RoomTable');
        	$item->addCondition('room_id',$m->id);
        	$item->addCondition('is_active',true);
        	
        	$t->add('Grid')->setModel($item);
		}

	}
}