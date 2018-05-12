<?php
//截取中文字符串
function subtext($text,$length) {
	if(mb_strlen($text,'utf8') > $length) {
		return mb_substr($text,0,$length,'utf8').'....';
	}
	return $text;
}

//数据分类展示
function comb($list, $pid=0,$level=0) {
	//定义静态数组用于存放格式化之后分类列表
	static $cate_list = array();
	//遍历数据
	foreach($list as $row) {
		if($row['pid'] == $pid) {
			$row['level'] = $level;
			$cate_list[]  = $row;
			//递归点
			comb($list,$row['id'],$level+1);
		}
	}
	//返回重新真理排序数据
	return $cate_list;
}
