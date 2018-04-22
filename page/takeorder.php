<?php

class page_takeorder extends Page {

	public $title = "Order Management";
	public $table_id;
	public $order_id;
	public $order_model;
	public $customer;
	public $customer_id;

	function init(){
		parent::init();
		
		$this->table_id = $this->app->stickyGET('tableid');
		$this->order_id = $this->app->stickyGET('orderid');
		$this->customer_id = $this->app->stickyGET('customerid');
		$this->menu_cat_id = $this->app->stickyGET('menu_cat_id');

		$this->order_model = $this->add('Model_Order');

		$this->setOrder();
		$this->createPos();
	}

	function setOrder(){
		if($this->table_id){
			$this->order_model->addCondition('table_id',$this->table_id);
			$this->order_model->addCondition('status','Running');
			$this->order_model->setOrder('id','desc');
			// $this->order_model->setLimit(1);
		}

		if($this->order_id){
			$this->order_model->load('id',$this->order_id);
		}else{
			$this->order_model->tryLoadAny();
			if(!$this->order_model->loaded()){
				$this->order_model['created_at'] = $this->app->now;
				$this->order_model['status'] = "Running";
			}
		}

		if($this->customer_id)
			$this->order_model['customer_id'] = $this->customer_id;

		$this->order_model->save();
	}

	function createPos(){
		$this->detail_model = $detail_model = $this->add('Model_OrderDetail');
		$detail_model->addCondition('order_id',$this->order_model->id);
		$detail_model->addHook('beforeSave',[$detail_model,'updateFromItem']);

		$col = $this->add('Columns');
		$col1 = $col->addColumn(6);
		$col2 = $col->addColumn(6);

		$form = $col1->add('Form');
		$cat_field = $form->addField('DropDown','menu_category');
		$cat_field->setModel('MenuCategory')->addCondition('is_active',true);
		$cat_field->setEmptyText('Select Category To Filter Menu Items');
		$form->setModel($detail_model,['menu_item_id','qty','narration']);

		$item_field = $form->getElement('menu_item_id');
		$item_field->Validate('required');
		if($this->menu_cat_id){
			$item_field->getModel()->addCondition('menu_category_id',$this->menu_cat_id);
		}

		$form->getElement('qty')->Validate('required');

		$cat_field->js('change',$form->js()->atk4_form('reloadField','menu_item_id',[$this->app->url(null,['cut_object'=>$item_field->name]),'menu_cat_id'=>$cat_field->js()->val()]));

		$form->addSubmit('Add');
		if($form->isSubmitted()){
			$form->save();
			$this->order_model['status'] = "Running";
			$form->js(null,[$form->js()->reload(['menu_cat_id'=>$form['menu_category']]),$col2->js()->reload()])->univ()->successMessage('Menu Item Added')->execute();
		}

		// col2 detail

		if($detail_model->count()->getOne()){
			$set = $col2->add('ButtonSet');
			$set->add('Button')->set('Total: '.$this->order_model['amount']." ".$this->app->company['currency'])->addClass('atk-swatch-yellow');
			$checkout_btn = $set->add('Button');
			$checkout_btn->set('Checkout')->addClass('atk-swatch-green')->setIcon('money');
			$checkout_btn->add('VirtualPage')
				->bindEvent('Payment','click')
				->set(function($page){
					
				});

			$set->add('Button')->set('Print KOT')->addClass('atk-swatch-red')->setIcon('print');
			$set->add('Button')->set('Print Bill')->addClass('atk-swatch-blue')->setIcon('print');
			// $crud->grid->addTotals(['qty','price']);
		}
		$crud = $col2->add('CRUD',['entity_name'=>'Menu Item','allow_add'=>false]);
		$crud->setModel($detail_model,['menu_item_id','qty','narration'],['menu_item','qty','price','narration']);
		$crud->grid->addSno('S.No.');
		// if($crud->grid){
		// 	$crud->grid->addFormatter('qty','grid/inline');
		// }
	}

	function page_checkout(){

	}
}