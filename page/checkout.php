<?php


class page_checkout extends Page {

	public $title = "Checkout";
	public $order_model;

	function init(){
		parent::init();

		$orderid = $this->app->stickyGET('orderid');
		if(!$orderid){
			$this->add('View_Warning')->set('No Order found .');
			return;
		}

		$this->order_model = $this->add('Model_Order')->load($orderid);

		if($this->order_model['status'] == "Paid"){
			$this->add('View_Error')->set('Order is already paid');
			return;
		}

		$col = $this->add('Columns');
		$left_col = $col->addColumn('3');
		$cen_col = $col->addColumn('4');
		$right_col = $col->addColumn('5');

		// cen column
		$payment_view = $cen_col->add('View');
		$payment_str = "<h3>Order Amount:</h3>";
		$payment_str .= '<table style="width:100%;">';
		$payment_str .= '<tr style="border-bottom:1px solid #f3f3f3;"><td> Gross Amount: </td> <td align="right">'.$this->order_model['gross_amount']."</td></tr>";
		$payment_str .= '<tr style="border-bottom:1px solid #f3f3f3;"><td> Discount Amount:</td> <td align="right">'.($this->order_model['discount_amount']?:0)."</td></tr>";
		$payment_str .= '<tr><td> Net Amount: </td> <td align="right" class="atk-size-kilo atk-effect-danger"> '.$this->order_model['net_amount']."</td></tr>";
		$payment_str .= '</table>';
		$payment_view->setHtml($payment_str);


		// left column
		$left_col->add('View')->set('Have a Discount Coupon ?')->addClass('atk-size-kilo');
		$form_str = '<div class="atk-form-row" data-shortname="{$field_name}">Discount Coupon {$discount_coupon}</div> OR </br> <div class="atk-form-row" data-shortname="{$field_name}">Discount Amount:{$discount_amount}</div><br/>';
		$form_gitemp = $this->add('GiTemplate');
		$form_gitemp->loadTemplateFromString($form_str);

		$discount_form = $left_col->add('Form',null,null,['form/empty']);
		$form_layout = $discount_form->add('View', null, null,$form_gitemp);
		$form_layout->addField('line','discount_coupon')->set($this->order_model['discount_coupon']);
		$form_layout->addField('line','discount_amount')->set(($this->order_model['discount_coupon']?0:$this->order_model['discount_amount']));
		$discount_form->addSubmit('Apply Discount');

		if($discount_form->isSubmitted()){
			if(!$discount_form['discount_coupon'] AND !$discount_form['discount_amount']){
				$discount_form->displayError('discount_coupon','either discount_coupon or discount_amount must not be empty')->execute();
			}

			$discount_amount = $discount_form['discount_amount'];

			if($discount_form['discount_coupon']){
				// todo
			}

			$this->order_model['discount_coupon'] = $discount_form['discount_coupon'];
			$this->order_model['discount_amount'] = $discount_amount;
			$this->order_model->save();

			$this->order_model->reload();
			$discount_form->js(null,[$discount_form->js()->reload(),$payment_view->js()->reload()])->univ()->successMessage('Discount Applied successfully')->execute();
		}

		// right column
		$model_tran = $this->add('Model_Transaction');
		$model_tran->addCondition('order_id',$this->order_model->id);
		$model_tran->getElement('discount_amount')->system(true);
		$model_tran->getElement('discount_coupon')->system(true);

		$form = $right_col->add('Form');
		$form->setModel($model_tran);
		$form->addSubmit('Paid And Print Bill')->addClass('atk-swatch-blue');
		
		$field_payment = $form->getElement('payment_mode');
		$field_payment->js(true)->univ()->bindConditionalShow([
			'Cash'=>['amount','narration'],
			'Cheque'=>['cheque_no','cheque_date','amount','narration'],
			'Other'=>['other_transaction_date','amount','narration']
		],'div.atk-form-row');
		// $grid = $right_col->add('Grid');
		// $grid->setModel($model_tran);

		if($form->isSubmitted()){

			if($form['payment_mode'] == "Cheque"){
				if(!$form['cheque_no']) $form->displayError('cheque_no','cheque_no must not be empty');
				if(!$form['cheque_date']) $form->displayError('cheque_date','cheque_date must not be empty');
			}
			// if(!$form['amount']) $form->displayError('amount','amount must not be empty');
			
			if($form['amount'] < $this->order_model['net_amount'])
				$form->displayError('amount','must be equal to order net amount ( '.$this->order_model['net_amount'].' )');


			$form->save();
			$this->order_model['status'] = "Paid";
			$this->order_model->save();

			$js_array = [
							$form->js()->univ()->newWindow($this->app->url('print',['format'=>'bill','orderid'=>$this->order_model->id,'cut_page'=>1]),'bill'.$this->order_model->id),
							$this->app->redirect($this->app->url('takeorder',['order_id'=>$this->order_model->id]))
						];

			$form->js(null,$js_array)->univ()->successMessage('Order '.$this->order_model['name'].' Payment Received of amount '.$this->order_model['net_amount'])->execute();
		}

	}
}