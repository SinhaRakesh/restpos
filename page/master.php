<?php


class page_master extends Page {
	
	public $title = "Master Management";

	function page_index(){
		// parent::init();

		$ft = $this->add('View')->addClass('flattabs');
		$tab = $ft->add('Tabs');
		$tab->addTabUrl('./company','Company');
		$tab->addTabUrl('./table','Table');
        $tab->addTabUrl('./menu','Menu');
        $tab->addTabUrl('./staff','Staff');
        $tab->addTabUrl('./customer','Customer');
        $tab->addTabUrl('./tax','Tax');
        $tab->addTabUrl('./order','Order');
        $tab->addTabUrl('./printlayout','Print Layout');
	}


	function page_company(){
		$model = $this->add('Model_Company');
		$form = $this->add('Form');
		$form->setModel($model);
		$form->addSubmit('Update');
		if($form->isSubmitted()){
			$form->save();
			$form->js()->univ()->successMessage('Saved')->execute();
		}
	}

	function page_table(){
		$m = $this->add('Model_Space');
		$m->setOrder('name','asc');
		$c = $this->add('CRUD');
		$c->setModel($m);
		$c->grid->addPaginator(10);

		$c->grid->add('VirtualPage')
		->addColumn('Table',"Table")
		->set(function($page){
			$id = $_GET[$page->short_name.'_id'];
			$m = $page->add('Model_RoomTable');
			$m->addCondition('room_id',$id);
			$crud = $page->add('CRUD',['entity_name'=>'Table']);
			$crud->setModel($m);

		});
	}

	function page_menu(){
		$m = $this->add('Model_MenuCategory');
		$c = $this->add('CRUD',['entity_name'=>'Menu Category']);
		$c->setModel($m);
		$c->grid->addPaginator(15);

		$c->grid->add('VirtualPage',['frame_options'=>'des'])
		->addColumn('Item','Menu Items')
		->set(function($page){
			$id = $_GET[$page->short_name.'_id'];

			$cat_model = $this->add('Model_MenuCategory')->load($id);

			$m = $page->add('Model_MenuItem');
			$m->addCondition('menu_category_id',$id);
			$crud = $page->add('CRUD',['entity_name'=>'Menu Items']);
			$crud->setModel($m);

			// $crud->grid->add("View_Box",null,'right_panel')->set("List of ".$cat_model['name']);
		});
	}

	function page_staff(){
		$m = $this->add('Model_User');
		$c = $this->add('CRUD',['entity_name'=>'Staff']);
		$c->setModel($m);
	}

	function page_tax(){
		$m = $this->add('Model_Tax');
		$c = $this->add('CRUD');
		$c->setModel($m);
	}

	function page_customer(){
		$m = $this->add('Model_Customer');
		$c = $this->add('CRUD');
		$c->setModel($m);
	}

	function page_order(){
		$order = $this->add('Model_Order');
		// $order->addCondition('status','Draft');
		// $order->addCondition('created_date_only',$this->app->today);

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

	function page_printlayout(){
		$tab = $this->add('Tabs');
		$bill_tab = $tab->addTab('Bill');
		$kot_tab = $tab->addTab('KOT');
		
		$config = $this->add('Model_Configuration');
		$config->tryLoadAny();

		$form = $bill_tab->add('Form');
		$form->setModel($config,['bill_master_layout','bill_detail_layout']);
		$form->addSubmit();
		if($form->isSubmitted()){
			$form->save();
			$form->js(null,$form->js()->reload())->univ()->successMessage('Bill format updated')->execute();
		}

		$form = $kot_tab->add('Form');
		$form->setModel($config,['kot_master_layout','kot_detail_layout']);
		$form->addSubmit();
		if($form->isSubmitted()){
			$form->save();
			$form->js(null,$form->js()->reload())->univ()->successMessage('KOT format updated')->execute();
		}
	}
}