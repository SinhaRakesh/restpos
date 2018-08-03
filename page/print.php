<?php

class page_print extends Page {
	
	function init(){
		parent::init();

		$orderid = $this->app->stickyGET('orderid');
		$format = $this->app->stickyGET('format');

		$this->title = "Print ".$format;
		$this->app->template->trySet('page_title',"Print ".$format);
		
		$config = $this->add('Model_Configuration');
		$config->tryLoadAny();

		$master_layout = $config['kot_master_layout'];
		$detail_layout = $config['kot_detail_layout'];
		if($format == "bill"){
			$master_layout = $config['bill_master_layout'];
			$detail_layout = $config['bill_detail_layout'];
		}
		
		if(!$master_layout OR !$detail_layout){
			echo "First Update print layout in from master section";
			exit;
		}

		$model_order = $this->add('Model_Order')->load($orderid);
		$model_order_detail = $this->add('Model_OrderDetail')->addCondition('order_id',$orderid);

		$master_temp = $this->add('GiTemplate');
		$master_temp->loadTemplateFromString($master_layout);
		$print_view = $this->add('View',null,null,$master_temp);
		$print_view->setModel($model_order);

		$temp = $this->add('GiTemplate');
		$temp->loadTemplateFromString($detail_layout);
		$lister = $print_view->add('CompleteLister',null,'detail',$temp);
		$lister->setModel($model_order_detail);
		$lister->sno = 1;
		$lister->addHook('formatRow',function($l){
			$l->current_row_html['sno'] = $l->sno;
			$l->sno++;
		});
	}
}