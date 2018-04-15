<?php
$config['url_prefix']='?page=';
$config['url_postfix']='';
$config['xepan-mysql-host']='127.0.0.1';

$config['locale']['date_js'] = 'dd/mm/yyyy';

// $config['js']['versions']['jqueryui']='1.11.master';

$config['tmail']['transport'] = 'Echo';


$config['developer_mode'] = true;
$config['all_rights_to_superuser'] = true;
$config['status_icon'] = [];

$config['filestore']['chmod'] = 0755;
$config['paymentgateways'] = ['Instamojo','CCAvenue'];