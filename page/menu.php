<?php


class page_menu extends Page {

	public $title = "Menu Management";

	function init(){
		parent::init();
		
		$category_model = $this->add('Model_MenuCategory')
							->addCondition('is_active',true);
		$tabs = $this->add('Tabs');
		foreach ($category_model as $m){
        	$t = $tabs->addTab($m['name']);

        	$item = $this->add('Model_MenuItem');
        	$item->addCondition('menu_category_id',$m->id);
        	$item->addCondition('is_active',true);
        	$t->add('Grid')->setModel($item);
		}

	}
}