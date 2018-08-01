<?php

class Model_MenuCategory extends Model_Table{
	public $table = "menu_category";

	function init(){
		parent::init();

		$this->addField('name');
		$this->add('filestore\Field_File','image_id');
		$this->addField('is_active')->type('boolean')->defaultValue(true);
		
		$this->add('dynamic_model/Controller_AutoCreator');
	}
}
