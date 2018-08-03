<?php

class View_Lister_Order extends \CompleteLister {

	public $number = [1=>'First',2=>'Second',3=>'Third',4=>'Fourth',5=>'Fifth',6=>'Six'];
	function init(){
		parent::init();

		$markorderid = $this->app->stickyGET('markorderid');
		if($markorderid){
			throw new \Exception("Error Processing Request", 1);
		}

		$order = $this->add('Model_Order');
		$order->addCondition([['status','Running'],['status','Complete']]);

		if($order->count()->getOne()){
			$this->template->tryDel('not_found');
		}

		$this->setModel($order);

	}

	function setModel($model){
		parent::setModel($model);
	}

	function formatRow(){
		$ods = $this->add('Model_OrderDetail');
		$ods->addCondition('order_id',$this->model->id);
		$ods->setOrder('course','asc');

		$row_html = "<table style='width:100%;'>";
		$last_course = 0;
		foreach ($ods as $od) {
			if($last_course != $od['course']){
				$row_html .= "<tr><td colspan='3' align='center' style='border-bottom:1px solid #f0f0f0;'>".$od['course']." Course</td></tr>";
				$last_course = $od['course'];
			}
			$icon = "";
			$class = "no-delivered";
			if($od['status'] == "Delivered"){
				$icon = "icon-ok";
				$class = "delivered";
			}
			$row_html .= "<tr class='".$class."'><td>".$od['qty']."</td><td> ".$od['menu_item']."</td><td><i class='".$icon."'></i></td></tr>";
		}

		$row_html .= "<tr><td colspan='3' style='border-top:1px solid #f3f3f3;' align='right'><h4><i class='icon-rupee'></i>".($this->model['net_amount']?:0)."</h4></td></tr>";
		$row_html .= "</table>";
		
		$this->current_row_html['order_detail'] = $row_html;

		if($this->model['status'] == "Running"){
			$this->current_row_html['action_btn'] = '<button data-id="'.$this->model->id.'" class=" do-mark-complete fullwidth atk-button-small atk-button atk-swatch-blue">Mark Ready</button>';
		}elseif($this->model['status'] == "Complete"){
			$this->current_row_html['action_btn'] = '<a href="?page=takeorder&orderid='.$this->model->id.'" data-id="{$id}" class=" do-payment fullwidth atk-button-small atk-button atk-swatch-green">Payment</a>';
		}

		parent::formatRow();
	}


	function recursiveRender(){
		$this->js('click')->_selector('.do-mark-complete')->univ()->ajaxec(
			array($this->api->url('update'),
			'markreadyid'=>$this->js()->_selectorThis()->data('id'),
			'cut_page'=>1
		));

		// $this->js('click')->_selector('.do-payment')->univ()->redirect(
		// 		$this->app->url('takeorder')]
			// );
		parent::recursiveRender();
	}

	function defaultTemplate(){
		return ['view/lister/order'];
	}
}