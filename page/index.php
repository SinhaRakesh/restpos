<?php


class page_index extends Page {
	
	function init(){
		parent::init();

		$order = $this->add('Model_Order');
		$order->addCondition('status','Draft');
		$order->addCondition('created_date_only',$this->app->today);

		$crud = $this->add('CRUD');
		$crud->setModel($order);

		$crud->grid->add('VirtualPage')
		->addColumn('Table','Items')
		->set(function($page){
			$id = $_GET[$page->short_name.'_id'];

			$m = $page->add('Model_OrderDetail');
			$m->addCondition('order_id',$id);
			$crud = $page->add('CRUD');
			$crud->setModel($m);
		});

	}	
}