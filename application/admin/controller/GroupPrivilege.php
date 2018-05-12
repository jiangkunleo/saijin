<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use think\Request;
use think\Db;
use app\admin\model\CodeGroup as CodeGroupModel; //分组表模型
use app\admin\model\AdminGroup as AdminGroupModel; //用户分组表模型
use app\admin\model\AdminGroupPrivilege as AdminGroupPrivilegeModel; //权限分组表模型
use app\admin\model\CodePrivilege as CodePrivilegeModel;

class GroupPrivilege extends common{
	//分组-权限 列表
	public function index() {
		$list = Db::name('code_group')
		->alias('b')
		->field('b.code group_code , b.name group_name , group_concat(c.name) pri_name,group_concat(c.code) pri_code,max(a.add_time) add_time , max(a.modify_time) modify_time')
		->join('admin_group_privilege a ','a.group_code = b.code','left')
		->join('code_privilege c ','a.privilege_code = c.code','left')
		->group('b.code,b.name')
		->paginate(10);
		$codeGroupModel = new CodeGroupModel();
      	$count = $codeGroupModel->count(); //总记录数
      	$page  = $list->render();      //页码数据
      	$currentPage = $list->currentPage(); //当前的页码
      	return $this->fetch('index',['list'=>$list ,'page'=>$page,'count'=>$count,'currentPage'=>$currentPage]);
	}

	//组分配权限设置
	public function set(Request $request) {
		//在给组分配权限前先判断是否是否已经分配，如果已经分配则提示点击更改或者删除！后面的代码不能执行！
		$adminGroupPrivilegeModel = new AdminGroupPrivilegeModel();
		$group_code =  input('group_code');
		$data_obj = $adminGroupPrivilegeModel->where('group_code',$group_code)->select();
		$data = $data_obj->toArray();
		if(!empty($data)) {
			$this->error('权限已经分配到组，如需更改，请点击更改!');die;
		}
		//没有分配权限的，就继续执行如下设置权限代码！
		if($request->isGet()) {
			//获取当前分组id
			$group_code =  input('group_code');
			$group_name =  input('group_name');

			//获取所有权限数据
			$CodePrivilegeModel      = new CodePrivilegeModel();
			$code_privilege_data_obj = $CodePrivilegeModel->where('is_valid','Y')->select();
			$code_privilege_data     = $code_privilege_data_obj->toArray();
			$this->assign('group_code',$group_code);
			$this->assign('group_name',$group_name);
			$this->assign('code_privilege_data',$code_privilege_data);
			return $this->fetch();
		}elseif($request->isPost()) {
			$group_code 	 = input('post.code_group');//当前组id
			$privilege_codes = input('privilege_codes/a');//当前被选中的权限id
			if(empty($privilege_codes)) {
				$this->error('请选择权限，再提交！');
			}
			foreach($privilege_codes as $v) {
				//在循环中将数据存入admin_group_privilege表中
				$data = [];
				$data['group_code']       = $group_code;
				$data['privilege_code']   = intval($v);
				$data['add_time']         = date('Y-m-d H:i:s',time());
				$tata['is_valid']         = 'Y';
				$adminGroupPrivilegeModel = new AdminGroupPrivilegeModel();
				$adminGroupPrivilegeModel->save($data);
			}
			$this->redirect('admin/group_privilege/index');die;			
		}

	}

	//组分配权限修改
	public function edit(Request $request) {
		if($request->isGet()) {
			//在给组更改权限前先判断是否是否已经分配，如果没有添加过权限，给出提示需要先添加了权限才能更改！
			$adminGroupPrivilegeModel = new AdminGroupPrivilegeModel();
			$group_code =  input('group_code');
			$data_obj 	= $adminGroupPrivilegeModel->where('group_code',$group_code)->select();
			$data       = $data_obj->toArray();
			if(empty($data)) {
				$this->error('此分组还没有设置权限，设置了权限才能进行更改!');die;
			}

			//***显示更改权限页面
			//获取当前分组id,和name值
			$group_code =  input('group_code');
			$group_name =  input('group_name');
			$this->assign('group_code',$group_code);
			$this->assign('group_name',$group_name);

			//获取所有权限数据
			$CodePrivilegeModel = new CodePrivilegeModel();
			$code_privilege_data_obj = $CodePrivilegeModel->where('is_valid','Y')->select();
			$code_privilege_data     = $code_privilege_data_obj->toArray();
			$this->assign('code_privilege_data',$code_privilege_data);
			//获取已选权限的code 
			$checked_privilege_code_str = input('pri_code');
			$this->assign('checked_privilege_code_str',$checked_privilege_code_str);
			return $this->fetch();
		}elseif($request->isPost()) {
			//***更新分组权限设置（对表1行或者多行进行操作，所以先删除之前的对应的所有数据，然后插入新数据）
			//1.删除admin_group_privilege中当前组所有的权限数据
			$old_group_code = input('code_group'); //当前组的组code
			AdminGroupPrivilegeModel::where('group_code',$old_group_code)->delete();
			//2.重新新增现在所选的权限数据到admin_group_privilege中

			$privilege_codes = input('privilege_codes/a');//当前被选中的权限id
			if(empty($privilege_codes)) {
				$this->error('请选择权限，再提交！');
			}			
			foreach($privilege_codes as $v) {
				//在循环中将数据存入admin_group_privilege表中
				$data = [];
				$data['group_code']     = $old_group_code;
				$data['privilege_code'] = intval($v);
				$data['modify_time']    = date('Y-m-d H:i:s',time());
				$tata['is_valid']       = 'Y';
				$adminGroupPrivilegeModel = new AdminGroupPrivilegeModel();
				$adminGroupPrivilegeModel->save($data);
			}
			$this->redirect('admin/group_privilege/index');die;	
		}

	}

	//组分配权限删除
	public function del() {
		//删除权限前做判断，有数据则删，我米有数据则提示报错！
		$adminGroupPrivilegeModel = new AdminGroupPrivilegeModel();
		$group_code =  input('group_code');
		$data_obj   = $adminGroupPrivilegeModel->where('group_code',$group_code)->select();
		$data = $data_obj->toArray();
		if(empty($data)) {
			$this->error('此分组还没有设置权限，没有数据可删!');die;
		}

		$group_code = input('group_code'); //当前组的组code
		$status     = AdminGroupPrivilegeModel::where('group_code',$group_code)->delete();
		if($status!== false) {
			$this->success('成功删除本组权限！','admin/group_privilege/index');die;
		}else{
			$this->error('删除本组权限失败！');die;
		}
	}
}