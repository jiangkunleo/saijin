<?php
//配置文件
return [
	//定义目录常量
	'view_replace_str' => [   
		//前台目录常量
	    '__home__'=>think\Url::build('/').'home',
	],

	//时间自动转换
	'datetime_format'=>true,

     //模板布局
    'template' =>[
        'layout_on' => true,
        'layout_name' => 'layout',
    ],

];