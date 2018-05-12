<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use think\Request;
use app\admin\model\Cateconfine as CateconfineModel;

/**
 *  贷款期限-分类控制器
 */
class Cateconfine extends Common{
	//列表
	public function index() {
		$cateconfile_model = new CateconfineModel();
		$cateconfine_data_obj = $cateconfile_model->order('months','asc')->select();
		$cateconfine_data = $cateconfine_data_obj->toArray();
		$this->assign('cateconfine_data',$cateconfine_data);
		return $this->fetch();
	}

	//添加
	public function add(Request $request) {
		if($request->isPost()) {
			//接收并过滤数据
			$data['months'] = addslashes(strip_tags(trim(input('post.months'))));
			$data['is_valid'] = input('post.is_valid');
			$data['add_time'] = date('Y-m-d H:i:s',time());
			//**验证数据
			$rules = [
				'months|贷款期限' =>['require','integer','elt:2400','gt:0','unique'=>'code_confine'],
			];
			$message = $this->validate($data,$rules);
			if($message!==true) {
				$this->error($message);die;
			}
			$cateconfile_model = new CateconfineModel();
			$status = $cateconfile_model->save($data);
			if($status !=false) {
				$this->success('添加成功！','admin/cateconfine/index');die;
			}else{
				$this->error('添加失败');die;
			}
		}
		return $this->fetch();
	}

	//修改
	public function edit(Request $request) {
		if($request->isGet()) {
			$id = input('id');
			$confine_data_obj = CateconfineModel::get($id);
			$confine_data = $confine_data_obj->toArray();
			$this->assign('confine_data',$confine_data);
			return $this->fetch();
		}else if($request->isPost()) {
			//接收并过滤数据
			$data['id'] = input('post.id');
			$data['months'] = addslashes(strip_tags(trim(input('post.months'))));
			$data['is_valid'] = input('post.is_valid');
			$data['add_time'] = input('post.add_time');
			$data['modify_time'] = date('Y-m-d H:i:s',time());
			//**验证数据
			$rules = [
				'months|贷款期限' =>['require','integer','elt:2400','gt:0','unique'=>'code_confine'],
			];
			$message = $this->validate($data,$rules);
			if($message!==true) {
				$this->error($message);die;
			}
			$cateconfile_model = new CateconfineModel();
			$status = $cateconfile_model->update($data);
			if($status !=false) {
				$this->success('修改成功！','admin/cateconfine/index');die;
			}else{
				$this->error('修改失败');die;
			}
		}
	}

	//删除
	public function del() {
		$id = input('id');
		$status = CateconfineModel::destroy($id);
		if($status!=false) {
			$this->success('删除成功！','admin/cateconfine/index');die;
		}else{
			$this->error('删除失败！');
		}

	}

		//是否有效，切换
	public function valid(Request $request) {
		$cateconfine_model = new CateconfineModel();
		$id = input('id');
		$cateconfine_obj   = $cateconfine_model->where('id',$id)->field('is_valid')->select();
		$cateconfine = $cateconfine_obj->toArray();

		if($cateconfine[0]['is_valid']=='Y') {
			$cateconfine_model->where('id',$id)->update(['is_valid'=>'N']);
		}else{
			$cateconfine_model->where('id',$id)->update(['is_valid'=>'Y']);	
		}
		$this->redirect('/admin/cateconfine/index');die;
	}


}