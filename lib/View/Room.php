<?php

class View_Room extends \CompleteLister {
	public $room_model;

	function init(){
		parent::init();

		$this->vp = $this->add('VirtualPage');
		$this->vp->set([$this,'customerinfo']);

		$tables = $this->add('Model_RoomTable')
				->addCondition('is_active',true)
				->addCondition('room_id',$this->room_model->id)
				;

		$this->setModel($tables);
		$this->template->trySet('room_name',$this->room_model['name']);

	}

	function formatRow(){
		parent::formatRow();

		if($this->model['running_order_id']){
			$this->current_row_html['table_class'] = "has-running-order";
			$url = $this->app->url('takeorder',['orderid'=>$this->model['running_order_id']]);
			$this->current_row_html['order_detail_url'] = $url;
		}else{
			$take_order_class =  "take-order-".$this->model['id'];
			$this->current_row_html['table_class'] = "has-no-running-order ".$take_order_class;
			$this->current_row_html['order_detail_url'] = "#";

			$this->js('click')->_selector('.'.$take_order_class)
				->univ()->frameURL('Customer Info',
					$this->app->url(
							$this->vp->getURL(),
							['ordertable_id'=>$this->model->id]
						)
				);
		}

		if($this->model['running_order_id'])
			$this->current_row_html['running_order_id'] = $this->model['running_order_id'];
		else
			$this->current_row_html['running_order'] = "<small class='atk-text-dimmed'>No Running Order</small>";

	}

	function recursiveRender(){
		parent::recursiveRender();
	}

	function defaultTemplate(){
		return ['view/room'];
	}

	function customerinfo($page){

		$table_id = $this->app->stickyGET('ordertable_id');

		$table_model = $this->add('Model_RoomTable')->load($table_id);
		$form = $page->add('Form');
		$form->add('Controller_FLC')
			->showLables(true)
			->addContentSpot()
			->makePanelsCoppalsible(true)
			->layout([
					'name~Full Name'=>'c1~4',
					'email_id'=>'c2~4',
					'mobile_no'=>'c3~4',
					'address'=>'c4~3',
					'city'=>'c5~3',
					'state'=>'c6~3',
					'country'=>'c7~3',
					'FormButtons~&nbsp;'=>'c8~12'
			]);
		
		$form->setModel('Customer',['name','email_id','mobile_no','address','city','state','country']);
		$form->addField('hidden','table_id')->set($table_id);

		$submit_button = $form->addSubmit('Save Customer And Take Order')->addClass('atk-swatch-blue');
		$skip_button = $form->addSubmit('Skip Now and Take Order');
		if($form->isSubmitted()){

			$order_model = $this->add('Model_Order');
			$order_model['table_id'] = $form['table_id'];
			$order_model['created_at'] = $this->app->now;
			$order_model['status'] = "Running";
			$order_model->save();

			if($form->isClicked($skip_button)){
				$this->app->redirect($this->app->url('takeorder',['orderid'=>$order_model->id]));
			}
			
			if($form->isClicked($submit_button)){
				//check mobile number already exist or not
				if(trim($form['mobile_no'])){
					$customer = $this->add('Model_Customer');
					$customer->addCondition('mobile_no',$form['mobile_no']);
					$customer->tryLoadAny();
					if(!$customer->loaded()){
						$customer['is_active'] = true;
						$customer = $form->save();
					}
				}
				
				$this->app->redirect($this->app->url('takeorder',['orderid'=>$order_model->id]));
			}
		}
	}
}