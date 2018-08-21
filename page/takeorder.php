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

		$this->cstvp = $this->add('VirtualPage');
		$this->cstvp->set([$this,'customerinfo']);

		$this->table_id = $this->app->stickyGET('tableid');
		$this->order_id = $this->app->stickyGET('orderid');
		$this->customer_id = $this->app->stickyGET('customerid');
		$this->menu_cat_id = $this->app->stickyGET('menu_cat_id');

		$this->itemid = $this->app->stickyGET('jslisteritemid');
		$this->itemqty = $this->app->stickyGET('jslisteritemqty');

		if(!$this->order_id){
			throw new \Exception("some thing wrong, order is not created", 1);
		}

		$this->setOrder();
		$this->createPos();
		$this->setMenus();

		// $this->app->showExecutionTime();
	}

	function setMenus(){

		$form = $this->add("Form",null,'add_form');
		$form->add('Controller_FLC')
			->showLables(true)
			->addContentSpot()
			->makePanelsCoppalsible(true)
			->layout([
					'item'=>'c1~6',
					'qty'=>'c2~3',
					'FormButtons~&nbsp;'=>'c3~3'
			]);	
		$item_model = $this->add('Model_MenuItem',['title_field'=>'name_type'])->addCondition('is_active',true);
		$item_model->addExpression('name_type')->set(function($m,$q){
			return $q->expr('CONCAT(IFNULL([0],"")," : ",IFNULL([1],""))',[$m->getElement('name'),$m->getElement('code')]);
		});
		$item_field = $form->addField('DropDown','item');
		$item_field->setModel($item_model);
		$item_field->setEmptyText('Please Select');
		$form->addField('qty')->set(1);
		$form->addSubmit('Add');
		
		if($form->isSubmitted()){
			$js_array = [
					$form->js()->reload(),
					$this->view_pos->js()->reload([
						'jslisteritemid'=>$form['item'],
						'jslisteritemqty'=>$form['qty']
					]),
				];
			$form->js(null,$js_array)->univ()->successMessage('added to order')->execute();
		}

		// running table
		$str = '<ul id="{$_name}" style="{$style}" class="{$class} atk-menu atk-menu-horizontal runningtable-wrapper">
					{rows}{row}
				  <li class="runningtable-single" data-tableid="{$table_id}" data-relatedorderid="{$id}">
				    <a class="" href="?page=takeorder&orderid={$id}">
				    	<strong>{$table_space}-{$table}</strong><span class="atk-cell"></span>
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
							<div data-id="{$id}" data-catname="{$menu_category}" data-catid="{$menu_category_id}" class="atk-col-4 menuitem-single" style="border-left: 0px;height:130px;padding:5px;">
								<div class="atk-box-small atk-align-center" style="position: relative;height:120px;">
									<div class="overlay" style="color:red;">
					                    <div class="inner">
					                        <div class="content">
					                            <span style="font-size:12px">{$name}</span>
					                            <input class="orderqty" value="1" type="number">
					                            <button data-id="{$id}" class="atk-button-small addto-order">Add</button>
					                        </div>
					                    </div>
					                </div>

									<image src="{image_url}{/}" style="width:80px; height:50px;" />
									<p class="" style="font-size:10px;">{$name}-{$code}</p>

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

		$type_set = $view_pos->add('ButtonSet');
		$dine_in_btn = $type_set->add('Button')->set('DINE IN')->addClass("btn-type-dinein ".strtolower($this->order_model['type']));
		if($dine_in_btn->isClicked()){
			$this->order_model['type'] = "dine-in";
			$this->order_model->save();
			$this->order_model->reload();
			// $b->js('click')->effect('shake',['times'=>3],60,$b->js()->_enclose()->reload());
			$type_set->js()->reload()->execute();
			// $dine_in_btn->js()->addClass('btn-type-dinein dine-in')->execute();
		}
		$take_away_btn = $type_set->add('Button')->set('TAKE AWAY')->addClass("btn-type-take ".strtolower($this->order_model['type']));
		if($take_away_btn->isClicked()){
			$this->order_model['type'] = "take-away";
			$this->order_model->save();
			$this->order_model->reload();
			$type_set->js()->reload()->execute();
			// $take_away_btn->js()->addClass('btn-type-take take-away')->execute();
		}
		$delivery_away_btn = $type_set->add('Button')->set('DELIVERY')->addClass("btn-type-delivery ".strtolower($this->order_model['type']));
		if($delivery_away_btn->isClicked()){
			$this->order_model['type'] = "delivery";
			$this->order_model->save();
			$this->order_model->reload();
			$type_set->js()->reload()->execute();
			// $delivery_away_btn->js()->addClass('btn-type-delivery delivery')->execute();
		}
		$customer_btn = $type_set->add('Button')->set('Customer: '.$this->order_model['customer']." ".$this->order_model['customer_mobile'])->setIcon('user')->addClass('atk-swatch-green customer-info-btn');
		$customer_btn->js('click')->univ()->frameURL('Customer Info',
				$this->app->url(
						$this->cstvp->getURL(),
						['customerorderid'=>$this->order_model->id]
					)
			);

		if($this->itemid){
			$new_od_model = $this->add('Model_OrderDetail');
			$new_od_model->addHook('beforeSave',[$new_od_model,'updateFromItem']);
			$new_od_model['order_id'] = $this->order_id;
			$new_od_model['menu_item_id'] = $this->itemid;
			$new_od_model['qty'] = $this->itemqty?:1;
			$new_od_model->save();
			$this->app->stickyForget('jslisteritemid');
			$this->app->stickyForget('jslisteritemqty');

			if($this->order_model['status'] != "Running"){
				$this->order_model['status'] = "Running";
				$this->order_model->save();
			}
			$this->order_model->reload();
		}

		$this->detail_model = $detail_model = $this->add('Model_OrderDetail');
		$detail_model->addCondition('order_id',$this->order_model->id);
		$detail_model->addHook('beforeSave',[$detail_model,'updateFromItem']);


		// col2 detail
		// if($this->order_model['customer_id']){
		// }
		$crud = $view_pos->add('CRUD',['entity_name'=>'Menu Item','allow_add'=>false]);
		$crud->setModel($detail_model,['qty','narration'],['menu_item','qty','narration','amount']);
		$crud->grid->addSno('S.No.');
		$crud->grid->addHook('formatRow',function($g){

			$g->current_row_html['menu_item'] = $g->model['menu_item']."<br/>".$g->model['narration'];
		});
		$crud->grid->addFormatter('menu_item','wrap');
		$crud->grid->removeColumn('narration');
		// $crud->grid->addTotals(['amount']);

		// discount form
		// $form_str = '<div class="atk-form-row" data-shortname="{$field_name}">Discount Coupon {$discount_coupon}</div> OR </br> <div class="atk-form-row" data-shortname="{$field_name}">Discount Amount:{$discount_amount}</div><br/>';
		// $form_gitemp = $this->add('GiTemplate');
		// $form_gitemp->loadTemplateFromString($form_str);
		// $discount_form = $crud->grid->add('Form',null,null,['form/empty']);
		// $form_layout = $discount_form->add('View', null, null,$form_gitemp);
		// $form_layout->addField('line','discount_coupon')->set($this->order_model['discount_coupon']);
		// $form_layout->addField('line','discount_amount')->set(($this->order_model['discount_coupon']?0:$this->order_model['discount_amount']));
		// $apply_discount = $discount_form->addSubmit('Apply Discount')->addClass('atk-swatch-green');
		// $clear_discount = $discount_form->addSubmit('Clear Discount')->addClass('atk-swatch-red');


		if($this->order_model['status'] != "Paid"){
			$dform = $crud->grid->add('Form',null,null,['form/horizontal']);
			$field_dv = $dform->addField("Line",'discount_coupon')->set($this->order_model['discount_coupon']);
			$dform->add("View")->setHtml('<div style="margin-top:30px;"><b>OR</b></div>');
			$field_d = $dform->addField("Line",'discount','Discount Amount / %')->set($this->order_model['discount_value']);

			$field_d->js('change',$dform->js()->submit());
			$field_dv->js('change',$dform->js()->submit());

			if($dform->isSubmitted()){
				if($dform['discount_coupon'] AND $dform['discount'])
					$dform->js(null,$dform->js()->reload())->univ()->errorMessage('Discount Coupon is applied first')->execute();

				$discount_amount = 0;
				if($dform['discount_coupon']){
					$data = $this->order_model->applyDiscountCoupon($dform['discount_coupon']);
					if(!$data['result']){
						$dform->displayError('discount_coupon',$data['message']);
					}
					$discount_amount = $data['discount_amount'];
					$dform['discount'] = null;
				}elseif($dform['discount']){
					$discount_amount = $dform['discount'];
					if(strpos($dform['discount'],'%') !== false){
						$discount_array = explode("%",$dform['discount']);
						$discount_percentage = trim($discount_array[0]);
						$discount_amount = round((($this->order_model['gross_amount'] * $discount_percentage) / 100.00),2);
					}
					$dform['discount_coupon'] = null;
				}

				
				$this->order_model['discount_coupon'] = $dform['discount_coupon'];
				$this->order_model['discount_amount'] = $discount_amount;
				$this->order_model['discount_value'] = $dform['discount'];
				$this->order_model->save();
				$crud->js()->reload()->execute();
			}
		}

			$view_total = $crud->grid->add('View');
			$view_total->addClass('fullwidth atk-align-right atk-text-bold atk-padding-small atk-swatch-yellow');
			$view_total->setHtml(
					'<table style="width:100%;">'.
					'<tr style="border-bottom:1px solid #f3f3f3;"><td> Gross Amount: </td> <td align="right">'.$this->order_model['gross_amount']."</td></tr>".
					'<tr style="border-bottom:1px solid #f3f3f3;"><td> Discount Amount:</td> <td align="right">'.($this->order_model['discount_amount']?:0)."</td></tr>".
					'<tr><td> Net Amount: </td> <td align="right" class="atk-size-kilo atk-effect-danger"> '.$this->order_model['net_amount']."</td></tr>".
					'</table>'
				);

		// 	$view_total = $crud->grid->add('View');
		// 	$view_total->addClass('fullwidth atk-align-right atk-text-bold atk-padding-small atk-swatch-yellow');
		// 	$view_total->setHtml(
		// 		'Net Amount:&nbsp;<i class="icon-rupee"></i>'.($this->order_model['net_amount']?:0)
		// 	);
		// }

		if($detail_model->count()->getOne()){
			$set = $view_pos->add('ButtonSet');
			$checkout_btn = $set->add('Button');

			$checkout_btn->set('Checkout')->addClass('atk-swatch-green')->setIcon('money');
			$checkout_btn->js('click')->univ()->frameURL('Payment of Order - '.$this->order_model['name']." ".$this->order_model['type'],$this->app->url('checkout',['orderid'=>$this->order_model->id]));

			$set->add('Button')->set('Print KOT')->addClass('atk-swatch-red')->setIcon('print')->js('click')->univ()->newWindow($this->app->url('print',['format'=>'kot','orderid'=>$this->order_model->id,'cut_page'=>1]),'kot'.$this->order_model->id);
			$set->add('Button')->set('Print Bill')->addClass('atk-swatch-blue ')->setIcon('print')->js('click')->univ()->newWindow($this->app->url('print',['format'=>'bill','orderid'=>$this->order_model->id,'cut_page'=>1]),'bill'.$this->order_model->id);
			// $crud->grid->addTotals(['qty','price']);
		}

		$crud->grid->add('View',null,'grid_buttons')->set('Order Number : '.$this->order_model['name']);
		$crud->grid->add('View',null,'right_panel')->set(" Status : ".$this->order_model['status']);

		// $crud->grid->js('reload',$view_total->js()->reload());
		// $view_pos->add('View')->set(rand(199,9999)." = item ".$this->itemid);

	}

	function recursiveRender(){

		// on item add to order
		$this->item_lister->js('click',$this->view_pos->js()->reload(
				[
					'jslisteritemid'=>$this->js()->_selectorThis()->closest(".menuitem-single")->data('id'),
					'jslisteritemqty'=>$this->js()->_selectorThis()->closest(".menuitem-single")->find('.orderqty')->val(),
			]))->_selector('.menuitem-single .addto-order')->univ()->successMessage('added to order');

		if($this->order_model['table_id']){
			$this->js(true)->_selector('.runningtable-single[data-tableid="'.$this->order_model['table_id'].'"')->addClass('table-has-order');
		}
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



	function customerinfo($page){
				
		$orderid = $this->app->stickyGET('customerorderid');
		$order_model = $this->add('Model_Order');
		$order_model->load($orderid);

		$form = $page->add('Form');
		$form->add('Controller_FLC')
			->showLables(true)
			->addContentSpot()
			->makePanelsCoppalsible(true)
			->layout([
					'mobile_no'=>'c1~4',
					'email_id'=>'c2~4',
					'name~Name'=>'c3~4',
					'country'=>'c4~4',
					'state'=>'c5~4',
					'city'=>'c6~4',
					'birthday'=>'c7~4',
					'anniversary'=>'c8~4',
					'address'=>'c9~4',
					'FormButtons~&nbsp;'=>'c10~12'
			]);
		
		$customer_model = $this->add('Model_Customer');
		if($order_model['customer_id']) $customer_model->load($order_model['customer_id']);

		$form->setModel($customer_model,['name','email_id','mobile_no','address','city','state','country','birthday','anniversary']);
		$submit_button = $form->addSubmit('Save Customer')->addClass('atk-swatch-blue ak-push');
		if($form->isSubmitted()){
			$form->save();
			$order_model['customer_id'] = $form->model->id;
			$order_model->save();
				
			$js_array = [
						$form->js()->univ()->successMessage('Customer Info Updated')
					];
			$form->js(null,$js_array)->closest('.dialog')->dialog('close')->execute();
		}
	}

	
}