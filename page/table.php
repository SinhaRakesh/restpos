<?php


class page_table extends Page {

	public $title = "Table Management";

	function init(){
		parent::init();


		$room_model = $this->add('Model_Room')->addCondition('is_active',true);

		$ft = $this->add('View')->addClass('flattabs');
		$tab = $ft->add('Tabs');

		foreach ($room_model as $room) {
			$tab_t = $tab->addTab($room['name']);

			$room = $tab_t->add('View_Room',['room_model'=>$room]);

			// $g = $tab_t->add('Grid');
			// $g->setModel($tables,['name','size','shape','member']);
			// $g->add('VirtualPage')
			// 	->addColumn('take_order','Take Order')
			// 	->set(function($page){
			// 		$this->current_table_id = $id = $this->app->stickyGET($page->short_name.'_id');
			// 		$current_table_model = $this->add('Model_RoomTable')->load($this->current_table_id);
			// 		$table_order_model = $current_table_model->getOrder();

			// 		$has_customer = 0;
			// 		if($table_order_model->loaded()){
			// 			$has_customer = $table_order_model['customer_id'];
			// 		}
			// 		if($has_customer){
			// 			$this->app->redirect($this->app->url('takeorder',['tableid'=>$this->current_table_id,'customerid'=>$has_customer]));
			// 		}

			// 		$page->add('View_Info')->set('Create Cutomer');
			// 		$form = $page->add('Form');
			// 		$form->setModel('Customer');
			// 		$form->addField('hidden','table_id')->set($id);

			// 		$submit_button = $form->addSubmit('Save Customer And Take Order')->addClass('atk-swatch-blue');
			// 		$skip_button = $form->addSubmit('Skip Now');
			// 		if($form->isSubmitted()){
			// 			if($form->isClicked($skip_button)){
			// 				$this->app->redirect($this->app->url('takeorder',['tableid'=>$form['table_id']]));
			// 			}

			// 			if($form->isClicked($submit_button)){
			// 				//check mobile number already exist or not
			// 				$customer = $this->add('Model_Customer');
			// 				$customer->addCondition('mobile_no',$form['mobile_no']);
			// 				$customer->tryLoadAny();
			// 				if(!$customer->loaded()){
			// 					$customer = $form->save();
			// 				}

			// 				$this->app->redirect($this->app->url('takeorder',['tableid'=>$form['table_id'],'customerid'=>$customer->id]));
			// 			}
			// 		}

			// 	});
		}
	}
}