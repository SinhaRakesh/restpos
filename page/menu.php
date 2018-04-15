<?php


class page_menu extends Page {
	
	public $title = "Menu Management";

	function init(){
		parent::init();

		$m = $this->add('Model_MenuCategory');
		$c = $this->add('CRUD');
		$c->setModel($m);

		$c->grid->add('VirtualPage',['frame_options'=>'des'])
		->addColumn('Item','Menu Items')
		->set(function($page){
			$id = $_GET[$page->short_name.'_id'];
			$m = $page->add('Model_MenuItem');
			$m->addCondition('menu_category_id',$id);
			$crud = $page->add('CRUD');
			$crud->setModel($m);
		});

	}
}