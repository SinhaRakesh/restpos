<?php


class page_master extends Page {
	
	public $title = "Master Management";

	function page_index(){
		// parent::init();


		$tab = $this->add('Tabs');
		$tab->addTabUrl('./company','Company');
		$tab->addTabUrl('./table','Table');
        $tab->addTabUrl('./menu','Menu');
        $tab->addTabUrl('./staff','Staff');
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

	function page_menu(){
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

	function page_staff(){
		$m = $this->add('Model_User');
		$c = $this->add('CRUD',['entity_name'=>'Staff']);
		$c->setModel($m);
	}


}