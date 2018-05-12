<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use think\Request;
use think\Db;
use app\admin\model\CodeGroup as CodeGroupModel; //分组表模型
use app\admin\model\AdminGroup as AdminGroupModel; //用户分组表模型
use app\admin\model\AdminGroupPrivilege as AdminGroupPrivilegeModel; //权限分组表模型
use app\admin\model\CodePrivilege as CodePrivilegeModel;
/**
 *  分组控制器
 */
class Group extends common{
	//分组列表
	public function index() {
		$codeGroupModel = new CodeGroupModel();
		//分页
		$list  = $codeGroupModel->order('code','asc')->paginate(8); //分页，每页5条数据
		$count = $codeGroupModel->count(); //总记录数
		$page  = $list->render();          //页码数据
		$currentPage = $list->currentPage(); //当前的页码
		return $this->fetch('index',['list'=>$list ,'page'=>$page,'count'=>$count,'currentPage'=>$currentPage]);
	}

	//添加分组
	public function add(Request $request) {
		if($request->isPost()) {
			//过滤数据
			$data['name']     = addslashes(strip_tags(trim(input('post.name'))));
			$data['add_time'] =  date('Y-m-d H:i:s',time());
			$data['is_valid'] =  input('post.is_valid');
			//验证数据
			$rules = [
				'name|分组名称' => ['require','max'=>100,'min'=>2,'unique'=>'code_group'],
			];
			$message = $this->validate($data,$rules);
			if($message!==true) {
				$this->error($message);die;
			}
			//添加数据到数据库
			$codeGroupModel = new CodeGroupModel();
			$status = $codeGroupModel->save($data);
			if($status!==false){
				$this->success('成功添加一个分组！',url('admin/group/index'));die;
			}else{
				$this->error('添加分组失败！');die;
			}
		}
		return $this->fetch();
	}



	//编辑分组
	public function edit(Request $request) {
		if($request->isGet()) {
			//显示编辑页面
			$code_group = input('code');
			$codeGroupModel = new CodeGroupModel();
			$code_group_data_obj = $codeGroupModel->find($code_group);
			$code_group_data = $code_group_data_obj->toArray();
			$this->assign('code_group_data',$code_group_data);
			return $this->fetch();
		}elseif($request->isPost()) {
			//更新当前数据
			//接收过滤数据
			$data['code'] = input('post.code');
			$data['name'] = addslashes(strip_tags(trim(input('post.name'))));
			$data['modify_time'] = date('Y-m-d H:i:s',time());
			$data['is_valid'] = input('post.is_valid');
			//验证数据
			$rules = [
				'name|分组名称' => ['require','max'=>100,'min'=>2,'unique'=>'code_group'],
			];
			$message = $this->validate($data,$rules);
			if($message!==true) {
				$this->error($message);die;
			}
			//更新数据
			$codeGroupModel = new CodeGroupModel();
			$status = $codeGroupModel->update($data);
			if($status!==false) {
				$this->success('修改组名成功！',url('admin/group/index'));die;
			}else{
				$this->error('修改组名失败！');die;
			}
		}
	}

	//删除分组
	public function del() {
		//获取当前分租id
		$group_code = input('code');
		//删除code_group表中的分组信息
		$status = CodeGroupModel::destroy($group_code);
		if($status!==false) {
			//删除分组信息同时删除admin_group表和admin_group_privilege表中关于当前组的信息
			AdminGroupModel::where('group_code',$group_code)->delete();
			AdminGroupPrivilegeModel::where('group_code',$group_code)->delete();
			$this->success('删除分组成功！','admin/user/index',3);
		}else{
			$this->error('删除失败。');die;
		}
	}

}
