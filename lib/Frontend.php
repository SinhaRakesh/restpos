<?php
class Frontend extends ApiFrontend {

    public $title = "ISP Log Viewer";

    function init() {
        parent::init();

        $this->dbConnect();
        $this->api->pathfinder
            ->addLocation(array(
                'addons' => array('vendor','shared/atk4-addons'),
                'template'=>array('.')
            ))
            ->setBasePath($this->pathfinder->base_location->getPath());
        
        $this->add('jUI');

        $this->today = date('Y-m-d',strtotime($this->recall('current_date',date('Y-m-d'))));
        $this->now = date('Y-m-d H:i:s',strtotime($this->recall('current_date',date('Y-m-d H:i:s'))));
       
        // If you wish to restrict access to your pages, use BasicAuth class
        $auth=$this->add('BasicAuth');
        $auth->setModel('User','username','password');
            // ->allow('demo','demo')
            // use check() and allowPage for white-list based auth checking
        $auth->check();
      
        $m=$this->add('Menu',null,'Menu');
        $m->addItem('Order','index');
        $m->addItem('Tables','table');
        $m->addItem('Menu','menu');
        $m->addItem('Staff','staff');
        $m->addItem('Reports','report');
        $m->addItem('Master','master');
        $m->addItem('Logout','logout');

        $this->addLayout('UserMenu');
        
        // set current_date for back date entry system
        $this->today = date('Y-m-d',strtotime($this->recall('current_date',date('Y-m-d'))));
        $this->now = date('Y-m-d H:i:s',strtotime($this->recall('current_date',date('Y-m-d H:i:s'))));
        
        if(!($this->company = $this->app->recall('company',false))){
            $this->company = $this->add('Model_Company')->tryLoadAny()->data;
            $this->app->memorize('company',$this->company);
        }
    }

}
