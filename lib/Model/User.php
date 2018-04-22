<?php

class Model_User extends Model_Table{
	public $table = "users";

	function init(){
		parent::init();

		$this->addField('name')->mandatory(true)->caption('Full Name');
		$this->addField('username');
		$this->addField('password')->type('password');
		$this->addField('email_id');
		$this->addField('mobile_no');

		$this->hasOne('Role','role_id');

		$this->addField('is_active')->type('boolean')->defaultValue(false);
		$this->addField('is_super')->type('boolean')->defaultValue(false)->system(true);
		// $this->addHook('beforeSave',$this);
		$this->addHook('beforeDelete',$this);
		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeDelete(){
		if($this['is_super'])
			throw new \Exception("cannot delete admin user");
	}

	function beforeSave(){
		// if($this['name']=='admin') $this['is_super']=true;
	}
}
