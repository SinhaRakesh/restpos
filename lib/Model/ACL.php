<?php

class Model_ACL extends Model_Table{
	
	public $table="staff_acl";

	function init(){
		parent::init();

		$this->hasOne('Staff','staff_id');
		$this->hasOne('Client','gschedule_of_client_id');
		$this->hasOne('Bill','details_of_bill_id');
		
		$this->addField('allow_add')->type('boolean')->defaultValue(true);
		$this->addField('allow_edit')->type('boolean')->defaultValue(true);
		$this->addField('allow_del')->type('boolean')->defaultValue(true);

		$this->add('dynamic_model/Controller_AutoCreator');

	}

	function forGSchedule($client_id){
		$this->addCondition('gschedule_of_client_id',$client_id);
		$this->addCondition('staff_id',$this->app->auth->model->id);
		$this->tryLoadAny();
		
		if($this->app->auth->model['is_super']) return null;
		
		if(!$this->loaded())
			return ['allow_add'=>false,'allow_edit'=>false,'allow_del'=>false];
		else
			return ['allow_add'=>$this['allow_add'],'allow_edit'=>$this['allow_edit'],'allow_del'=>$this['allow_del']];
	}

	function forBillDetail($bill_id){
		$this->addCondition('details_of_bill_id',$bill_id);
		$this->addCondition('staff_id',$this->app->auth->model->id);
		$this->tryLoadAny();
		
		if($this->app->auth->model['is_super']) return null;
		
		if(!$this->loaded())
			return ['allow_add'=>false,'allow_edit'=>false,'allow_del'=>false];
		else
			return ['allow_add'=>$this['allow_add'],'allow_edit'=>$this['allow_edit'],'allow_del'=>$this['allow_del']];
	}

	function setNoneForOthers(){
		if($this->app->auth->model['is_super']) return null;

		return ['allow_add'=>false,'allow_del'=>false,'allow_edit'=>false];
	}
}
