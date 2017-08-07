<?php
define('QUERY_DOMAIN', 'xinyue.com');

return array_merge(
	array(
		'debug' => 1,
		'SlowTimeout' => 3,
		'LogCutSuffix' => '-' . date('Ymd'),
		'timezone' => 'Etc/GMT-8',
		'cookieparams' => array('domain' => '.' . QUERY_DOMAIN),
		'db' => array(
			'class' => 'pdo',
			'host' => '127.0.0.1',
			'user' => 'root',
			'password' => 'root',
			'charset' => 'utf8',
			'prefix' => '',
			'autoconnect' => false,
			'dbname' => 'phone',
			'slowtimeout' => 3,
		),
		'RenderDataTransmit' => true, //开启模板渲染参数传递
		'import' => array(
			'app.model.*',
			'app.util.*',
			'app.controller.MobileBaseController',
			'app.controller.BaseController',
			'app.controller.GlobalController',
		),
		'url' => array(
			'format' => 'path',
			'showscriptname' => false,
			'rules' => array(
			),
		),
	)
);