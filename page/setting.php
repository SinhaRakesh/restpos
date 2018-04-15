<?php


class page_setting extends Page {
	
	public $title = "Configuration Management";

	function init(){
		parent::init();

		$model = $this->add('Model_Company');
		$form = $this->add('Form');
		$form->setModel($model);
		$form->addSubmit('Update');
		if($form->isSubmitted()){
			$form->save();
			$form->js()->univ()->successMessage('Saved')->execute();
		}

	}
}