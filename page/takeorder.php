<?php

class page_takeorder extends Page {

	public $title = "Order Management";
	public $table_id;
	public $order_id;
	public $order_model;
	public $customer;
	public $customer_id;

	public $category_lister;
	public $item_lister;
	public $running_table_lister;
	public $view_pos;

	function init(){
		parent::init();

		$this->table_id = $this->app->stickyGET('tableid');
		$this->order_id = $this->app->stickyGET('orderid');
		$this->customer_id = $this->app->stickyGET('customerid');
		$this->menu_cat_id = $this->app->stickyGET('menu_cat_id');

		$this->itemid = $this->app->stickyGET('jslisteritemid');
		$this->itemqty = $this->app->stickyGET('jslisteritemqty');

		$this->setMenus();
		$this->setOrder();
		$this->createPos();

		// $this->app->showExecutionTime();
	}

	function setMenus(){

		// running table
		$str = '<ul id="{$_name}" style="{$style}" class="{$class} atk-menu atk-menu-horizontal runningtable-wrapper">
					{rows}{row}
				  <li class="runningtable-single" data-tableid="{$table_id}">
				    <a class="" href="#">
				    	<strong>{$table}</strong><span class="atk-cell"></span>
				    </a>
				  </li>
				  {/}{/}
				</ul>';
		$template = $this->add('GiTemplate');
		$template->loadTemplateFromString($str);
		$order = $this->add('Model_Order');
		$order->addCondition([['status','Running'],['status','Complete']]);
		$order->addCondition('table_id','>',0);
		$this->running_table_lister = $lister = $this->add('CompleteLister',null,'running_table',$template);
		$lister->setModel($order);

		// menu category
		$mc = $this->add('Model_MenuCategory');
		$mc->addCondition('is_active',true);

		$template = '<ul id="{$_name}" class="atk-menu-vertical menucategory-wrapper">
						<li class="atk-padding-small atk-swatch-gray menucategory-single" data-id="0">All</li>
						{rows}{row}	
							<li class="atk-padding-small atk-swatch-gray menucategory-single" data-id="{$id}">{$name}</li>
						{/rows}{/row}
					</ul>';

		$temp = $this->add('GiTemplate');
		$temp->loadTemplateFromString($template);
		$this->category_lister = $lister = $this->add('CompleteLister',null,'menu_category',$temp);
		$lister->setModel($mc);
				
		// menu items
		$mi = $this->add('Model_MenuItem');
		$mi->addCondition('is_active',true);
		$template = '<div id="{$_name}" class="{$class} menuitem-wrapper" style="{$style}">
						<div class="atk-row" style="margin-left: 0px;">
						{rows}{row}
							<div data-id="{$id}" data-catname="{$menu_category}" data-catid="{$menu_category_id}" class="atk-col-6 atk-padding-small menuitem-single" style="border-left: 0px;">
								<div class="atk-box-small atk-align-center" style="position: relative;">
									<div class="overlay" style="color:red;">
					                    <div class="inner">
					                        <div class="content">
					                            {$name}
					                            <input class="orderqty" value="1" type="number">
					                            <button data-id="{$id}" class="atk-button-small addto-order">Add</button>
					                        </div>
					                    </div>
					                </div>

									<image src="{image_url}{/}" style="width:120px; height:70px;" />
									<p class="Heading h4">{$name}-{$code}</p>

								</div>
							</div>
						{/rows}{/row}
						</div>
					</div>';
		// used for backgrounf
		// style="background:url($image);background-size:100%;"
		$temp = $this->add('GiTemplate');
		$temp->loadTemplateFromString($template);
		$this->item_lister = $lister = $this->add('CompleteLister',null,'menu_item',$temp);
		$lister->setModel($mi);
		$lister->addHook('formatRow',function($l){
			if($l->model['image'])
				$l->current_row_html['image_url'] = $l->model['image'];
			else
				$l->current_row_html['image_url'] = "templates/images/restaurants/restaurants/gourmet_0star.png";
		});

	}

	function page_menuitem(){
		$catid = $_GET['catid'];

		$model = $this->add('Model_MenuItem');
		$model->addCondition('menu_category_id',$catid);
		$model->addCondition('is_active',true);

		$grid = $this->add('Grid');
		$grid->setModel($model,['name','code']);
		$grid->template->tryDel('Pannel');

	}

	function setOrder(){
		$this->order_model = $this->add('Model_Order');

		if($this->order_id){
			$this->order_model->load($this->order_id);
		}elseif($this->table_id){
			$this->order_model->addCondition('table_id',$this->table_id);
			$this->order_model->addCondition([['status','Running'],['status','Complete']]);

		}elseif(!$this->order_model->loaded()){

			$this->order_model['created_at'] = $this->app->now;
			$this->order_model['status'] = "Running";
		}
		if($this->customer_id)
			$this->order_model['customer_id'] = $this->customer_id;

		$this->order_model->save();
	}

	function createPos(){
		if(!$this->order_model->loaded()) throw new \Exception("Order not found, something wrong");
		
		$this->view_pos = $view_pos = $this->add('View',null,'pos');

		if($this->itemid){
			$new_od_model = $this->add('Model_OrderDetail');
			$new_od_model->addHook('beforeSave',[$new_od_model,'updateFromItem']);
			$new_od_model['order_id'] = $this->order_id;
			$new_od_model['menu_item_id'] = $this->itemid;
			$new_od_model['qty'] = $this->itemqty?:1;
			$new_od_model->save();
			$this->app->stickyForget('jslisteritemid');
			$this->app->stickyForget('jslisteritemqty');
			$this->order_model->reload();
		}

		$this->detail_model = $detail_model = $this->add('Model_OrderDetail');
		$detail_model->addCondition('order_id',$this->order_model->id);
		$detail_model->addHook('beforeSave',[$detail_model,'updateFromItem']);


		// col2 detail
		if($detail_model->count()->getOne()){
			$set = $view_pos->add('ButtonSet');
			$checkout_btn = $set->add('Button');

			$checkout_btn->set('Checkout')->addClass('atk-swatch-green')->setIcon('money');
			$checkout_btn->add('VirtualPage')
				->bindEvent('Payment','click')
				->set(function($page){
					
				});
			$set->add('Button')->set('Print KOT')->addClass('atk-swatch-red')->setIcon('print')->js('click')->univ()->newWindow($this->app->url('print',['format'=>'kot','oid'=>$this->order_model->id]),'kot'.$this->order_model->id);
			$set->add('Button')->set('Print Bill')->addClass('atk-swatch-blue')->setIcon('print')->js('click')->univ()->newWindow($this->app->url('print',['format'=>'bill','oid'=>$this->order_model->id]).'bill'.$this->order_model->id);
			// $crud->grid->addTotals(['qty','price']);
		}

		$crud = $view_pos->add('CRUD',['entity_name'=>'Menu Item','allow_add'=>false]);
		$crud->setModel($detail_model,['qty','narration'],['menu_item','qty','narration','amount']);
		$crud->grid->addSno('S.No.');
		$crud->grid->addHook('formatRow',function($g){

			$g->current_row_html['menu_item'] = $g->model['menu_item']."<br/>".$g->model['narration'];
		});
		$crud->grid->addFormatter('menu_item','wrap');
		$crud->grid->removeColumn('narration');
		// $crud->grid->addTotals(['amount']);

		$view_total = $crud->grid->add('View');
		$view_total->addClass('fullwidth atk-align-right atk-text-bold atk-padding-small atk-swatch-yellow');
		$view_total->setHtml('Total:&nbsp;<i class="icon-rupee"></i>'.$this->order_model['amount']);

		// $crud->grid->js('reload',$view_total->js()->reload());
		// $view_pos->add('View')->set(rand(199,9999)." = item ".$this->itemid);

	}

	function page_checkout(){

	}

	function recursiveRender(){

		// on item add to order
		$this->item_lister->js('click',$this->view_pos->js()->reload(
				[
					'jslisteritemid'=>$this->js()->_selectorThis()->closest(".menuitem-single")->data('id'),
					'jslisteritemqty'=>$this->js()->_selectorThis()->closest(".menuitem-single")->find('.orderqty')->val(),
			]))->_selector('.menuitem-single .addto-order')->univ()->successMessage('added to order');
		// ->ajaxec(
		// 	array($this->api->url('update'),
		// 	'markreadyid'=>$this->js()->_selectorThis()->data('id'),
		// 	'cut_page'=>1
		// ));

		//  filter item based on category
		// $this->category_lister->js('click',[
		// 	$this->item_lister->js()->find('.menuitem-single')->hide(),
		// 	$this->item_lister->js()->find('.menuitem-single')->show()
		// ])->_selector('.menucategory-single');

		parent::recursiveRender();
	}

	function defaultTemplate(){
	return ['page\takeorder'];
	}
}