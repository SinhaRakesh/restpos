<?php

class View_Menu extends \View {

	function init(){
		parent::init();

		$this->tab = $this->add('Tabs',['position'=>'left']);
	}

	function recursiveRender(){

		$mc = $this->add('Model_MenuCategory');
		$mc->addCondition('is_active',true);
		foreach ($mc as $m) {
			$tab = $this->tab->addTab($m['name']);
			
		}

		parent::recursiveRender();

	}
}