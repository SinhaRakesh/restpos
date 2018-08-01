<?php

class page_index extends Page {
	
	function init(){
		parent::init();


		// $qsp_model->_dsql()->del('fields')->field('max(CAST('.$this->number_field.' AS decimal))')->where('type',$this['type'])->where('serial',$serial)->getOne() + 1 ;
		$btn = $this->add('Button');
		$btn->set('New Order')->addClass('fullwidth atk-button-large');
		if($btn->isClicked()){
			$order = $this->add('Model_Order');
			$order->save();
			$this->app->redirect($this->app->url('takeorder',['orderid'=>$order->id]));
		}

		$this->add('View_Lister_Order');
	}
}