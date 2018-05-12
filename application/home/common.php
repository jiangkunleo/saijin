<?php

//截取中文字符串
function subtext($text,$length) {
	if(mb_strlen($text,'utf8') > $length) {
		return mb_substr($text,0,$length,'utf8').'....';
	}
	return $text;
}

// //删除某字符串中的指定字符
// function sub_s($str) {
// 	str_replace(array("&nbsp;"),"",$str);
// }

//数据分类展示
function comb($list, $cate_pid=0,$level=0) {
	//定义静态数组用于存放格式化之后分类列表
	static $cate_list = array();
	//遍历数据
	foreach($list as $row) {
		if($row['cate_pid'] == $cate_pid) {
			$row['level'] = $level;
			$cate_list[]  = $row;
			//递归点
			comb($list,$row['cate_id'],$level+1);
		}
	}
	//返回重新真理排序数据
	return $cate_list;
}

