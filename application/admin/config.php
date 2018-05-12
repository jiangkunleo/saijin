<?php
//配置文件
return [
	//定义目录常量
	'view_replace_str' => [
		//后台目录常量
	    '__admin__'=>think\Url::build('/').'admin',

	    //富文本目录常量
	    '__ed__'=>think\Url::build('/').'editor',
	],

	//时间自动转换
	'datetime_format'=>true,
];