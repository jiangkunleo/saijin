<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use think\Request;
use think\Session;
use app\admin\model\User as UserModel;

/**
 *  修改密码控制器
 */
class Modify extends Common{
	//更改密码
	public function edit(Request $request) {
		if($request->isGet()) {
			$account_name = Session::get('account_name');
			$account_data_obj = UserModel::where('account_name',$account_name)->find();
			$this->assign('account_data',$account_data_obj);
			return $this->fetch();
		}elseif($request->isPost()) {
			//接收并过滤数据
			$data['account_id']   = input('post.account_id');
			$data['account_name'] = addslashes(strip_tags(trim(input('post.account_name'))));
			$data['credential']   = addslashes(strip_tags(trim(input('post.credential'))));
			$data['add_time']     = input('post.add_time');
			$data['modify_time']  = date('Y-m-d H:i:s',time());
	        //验证数据，
	        $rules = [
	          'account_name|用户名称'=> ['require','min'=>3,'max'=>32,'unique'=>'user_account'],
	          'credential|密码'=> ['require','min'=>6,'max'=>10,'alphaDash'],
	        ];
	        $message = $this->validate($data,$rules);
	        if($message!==true) {
	          $this->error($message);die;
	        }
	        //密码加密
	        $data['credential'] = md5($data['credential']);
	      	//数据入库
	      	$userModel = new UserModel();
	      	$status = $userModel->update($data);
	      	if( $status!==false ) {
	      		$this->success('修改密码成功！','admin/index/main',3);die;
	      	}else{
	      		$this->error('密码修改失败！');die;
	      	}
		}


	}

}
