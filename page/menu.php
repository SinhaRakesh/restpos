<?php


class page_menu extends Page {

	public $title = "Menu Management";

	function init(){
		parent::init();
		
		$category_model = $this->add('Model_MenuCategory')
							->addCondition('is_active',true);
		$ft = $this->add('View')->addClass('flattabs');
		$tabs = $ft->add('Tabs');
		foreach ($category_model as $m){
        	$t = $tabs->addTab($m['name']);

        	$item = $this->add('Model_MenuItem');
        	$item->addCondition('menu_category_id',$m->id);
        	$item->addCondition('is_active',true);
        	$grid = $t->add('Grid');
        	$grid->setModel($item,['image','name','code','price','unit']);
        	// $grid->addFormatter('image','image');

        	$grid->addHook('formatRow',function($g){
        		$g->current_row_html['image'] = '<img style="width:120px;" src="'.$g->model['image'].'"/>';
        	});
        	$grid->addSno('sno');

		}

	}
}