<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use think\Request;
use think\Db;
use app\admin\Model\CodeGroup as CodeGroupModel;
use app\admin\Model\CodePrivilege as CodePrivilegeModel;
use app\admin\Model\AdminGroupPrivilege as AdminGroupPrivilegeModel;

/**
 *  权限控制器
 */
class Privilege extends common{
	//权限列表
	public function index() {
		$codePrivilegeModel = new CodePrivilegeModel();
		//分页
		$list  = $codePrivilegeModel->order('code','asc')->paginate(12); //分页，每页5条数据
		$count = $codePrivilegeModel->count(); //总记录数
		$page  = $list->render();      //页码数据
		$currentPage = $list->currentPage(); //当前的页码
		return $this->fetch('index',['list'=>$list ,'page'=>$page,'count'=>$count,'currentPage'=>$currentPage]);

	}


	//添加权限
	public function add(Request $request) {
		if($request->isPost()) {
			//过滤数据
			$data['name']     = addslashes(strip_tags(trim(input('post.name'))));
			$data['add_time'] =  date('Y-m-d H:i:s',time());
			$data['is_valid'] =  input('post.is_valid');
			//验证数据
			$rules = [
				'name|权限名称' => ['require','max'=>100,'min'=>2,'unique'=>'code_privilege'],
			];
			$message = $this->validate($data,$rules);
			if($message!==true) {
				$this->error($message);die;
			}
			//添加数据到数据库
			$codePrivilegeModel = new CodePrivilegeModel();
			$status = $codePrivilegeModel->save($data);
			if($status!==false){
				$this->success('成功添加一个权限！',url('admin/privilege/index'));die;
			}else{
				$this->error('添加权限失败！');die;
			}			
		}
		return $this->fetch();
	}

	//修改权限
	public function edit(Request $request) {
		if($request->isGet()) {
			//显示编辑页面
			$code_privilege          = input('code');
			$codePrivilegeModel      = new CodePrivilegeModel();
			$code_privilege_data_obj = $codePrivilegeModel->find($code_privilege);
			$code_privilege_data     = $code_privilege_data_obj->toArray();
			$this->assign('code_privilege_data',$code_privilege_data);
			return $this->fetch();
		}elseif($request->isPost()) {
			//更新当前数据
			//接收过滤数据
			$data['code']        = input('post.code');
			$data['name']        = addslashes(strip_tags(trim(input('post.name'))));
			$data['modify_time'] = date('Y-m-d H:i:s',time());
			$data['is_valid']    = input('post.is_valid');
			//验证数据
			$rules = [
				'name|权限名称' => ['require','max'=>100,'min'=>2,'unique'=>'code_privilege'],
			];
			$message = $this->validate($data,$rules);
			if($message!==true) {
				$this->error($message);die;
			}
			//更新数据
			$codePrivilegeModel = new CodePrivilegeModel();
			$status = $codePrivilegeModel->update($data);
			if($status!==false) {
				$this->success('修改权限成功！',url('admin/privilege/index'));die;
			}else{
				$this->error('修改权限失败！');die;
			}
		}
	}

	//删除权限(删除权限表信息，同时删除权限分组表的信息)
	public function del() {
		//接收当前组的ID，删除本组信息
		$code_privilege = input('code');
		$status         = CodePrivilegeModel::destroy($code_privilege);
		if($status!==false) {
			//同时删除admin_group_privilege 和 admin_group表中和当前组相关的信息
			AdminGroupPrivilegeModel::where('privilege_code',$code_privilege)->delete();
			$this->success('删除权限成功！',url('admin/privilege/index'));die;
		}else{
			$this->error('删除权限失败！');die;
		}		
	}


}
