<?php

class Model_Company extends Model_Table{
	public $table="company";

	function init(){
		parent::init();

		$this->addField('plan')->system(true);

		// restaurant detail
		$this->addField('name')->mandatory(true);
		$this->addField('description')->type('text');
		$this->addField('website');
		$this->addField('email');
		$this->addField('contact_no')->hint('comma (,) seperated multiple values');
		$this->addField('address');
		$this->addField('landmark');
		$this->addField('country');
		$this->addField('state');
		$this->addField('city');
		$this->add('filestore\Field_File','logo_id');

		$this->addField('start_time');
		$this->addField('end_time');
		$this->addField('currency');
		$this->addField('payment_type')->type('text')->hint('comma seperated multiple values');
		
		$this->addField('service_charge_name');
		$this->addField('service_charge')->hint('if fixed amount 10, or 2%');
		// $this->addField('tax_name');
		// $this->addField('tax_value')->hint('if fixed amount then 10 else in percentage 2%');
		
		$this->addHook('beforeSave',$this);
		$this->addHook('afterSave',$this);
		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeSave(){
		if($this['name']=='admin') $this['is_super']=true;
	}

	function afterSave(){
        $this->app->memorize('company',$this->data);
	}
}
