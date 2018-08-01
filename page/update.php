<?php

class page_update extends Page {
	
	function init(){
		parent::init();

		$id = $_GET['markreadyid'];
		if(!$id) return 0;

		$ods = $this->add('Model_OrderDetail')
			->addCondition('order_id',$id);

		foreach ($ods as $od) {
			$od->set('status','Delivered')->save();
		}

		$this->add('Model_Order')->load($id)->set('status','Complete')->save();
		return $this->app->js()->univ()->redirect($this->app->url('index'))->execute();
		exit;
	}
}