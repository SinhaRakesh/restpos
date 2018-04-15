<?php


class page_index extends Page {
	
	function init(){
		parent::init();

		$this->add('View_Info')->set('Order Management');
		// $filter = $this->app->stickyGET('filter');
		// $this->app->stickyGET('user_name');
		// $this->app->stickyGET('from_date');
		// $this->app->stickyGET('to_date');

		// $form = $this->add('Form');
		// $form->add('Controller_FLC')
		// 	->showLables(true)
		// 	->addContentSpot()
		// 	->makePanelsCoppalsible(true)
		// 	->layout([
		// 			'user_name'=>'Filter~c1~3',
		// 			'from_date'=>'c2~3',
		// 			'to_date'=>'c3~3',
		// 			'FormButtons~&nbsp;'=>'c4~3'
		// 		]);

		// $form->addField('user_name');
		// $form->addField('DateTimePicker','from_date');
		// $form->addField('DateTimePicker','to_date');
		// $form->addSubmit('Filter');

		// $model = $this->add('Model_SystemEvents');
		// if($filter){

		// }else{
		// 	$model->addCondition('id','<',20);
		// }

		// $grid = $this->add('Grid');
		// $grid->setModel($model);
		// $grid->addPaginator(10);

		// if($form->isSubmitted()){
		// 	$grid->js()->reload([
		// 			'filter'=>true,
		// 			'user_name'=>$form['user_name'],
		// 			'from_date'=>$form['from_date'],
		// 			'to_date'=>$form['to_date'],
		// 	])->execute();
		// }

	}	
}