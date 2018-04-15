<?php


class page_table extends Page {
	
	public $title = "Table Management";

	function init(){
		parent::init();

		$m = $this->add('Model_Room');
		$c = $this->add('CRUD');
		$c->setModel($m);

		$c->grid->add('VirtualPage')
		->addColumn('Table','Room Table')
		->set(function($page){
			$id = $_GET[$page->short_name.'_id'];
			$m = $page->add('Model_RoomTable');
			$m->addCondition('room_id',$id);
			$crud = $page->add('CRUD');
			$crud->setModel($m);
		});
	}
}