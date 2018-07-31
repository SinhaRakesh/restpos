<?php
class Frontend extends ApiFrontend {

    public $title = "Restaurant POS System";

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
        // $this->addLayout('UserMenu');
        // $this->add('Layout_Admin');

        $this->today = date('Y-m-d',strtotime($this->recall('current_date',date('Y-m-d'))));
        $this->now = date('Y-m-d H:i:s',strtotime($this->recall('current_date',date('Y-m-d H:i:s'))));
       
        // If you wish to restrict access to your pages, use BasicAuth class
        $auth=$this->add('BasicAuth');
        $auth->setModel('User','username','password');
            // ->allow('demo','demo')
            // use check() and allowPage for white-list based auth checking

        if(!$this->app->auth->model->id)
            $this->add('Layout_Admin');
        
        $auth->check();
        
        $m = $this->add('Menu',null,'Menu');
        $menu_list = [
                'Order'=>'index',
                'Tables'=>'table',
                'Menu'=>'menu',
                'Staff'=>'staff',
                'Reports'=>'report',
                'Master'=>'master',
                'Logout'=>'logout',
            ];
        foreach ($menu_list as $name => $page_name) {
            $menu = $m->addItem($name,$page_name);
            // if($this->app->page == $page_name){
                // $menu->addClass('menu-active');
            // }
        }
    
        
        // $b = $this->add("Button")->set(rand(1,100));
        // $b->js('click')->effect('shake',['times'=>3],60,$b->js()->_enclose()->reload());
        // if($b->isClicked()){
        //     $b->js()->animate(['left'=>'+='.rand(10,100).'px'])->execute();
        // }

        // set current_date for back date entry system
        $this->today = date('Y-m-d',strtotime($this->recall('current_date',date('Y-m-d'))));
        $this->now = date('Y-m-d H:i:s',strtotime($this->recall('current_date',date('Y-m-d H:i:s'))));
        
        if(!($this->company = $this->app->recall('company',false))){
            $this->company = $this->add('Model_Company')->tryLoadAny()->data;
            $this->app->memorize('company',$this->company);
        }
    }

}
