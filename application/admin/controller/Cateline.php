<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use think\Request;
use app\admin\model\Cateline as CatelineModel;

/**
 *  贷款金额-分类控制器
 */
class Cateline extends Common{
	//列表
	public function index() {
		$cateline_model = new CatelineModel();
		$cateline_data_obj = $cateline_model->order('line_code','asc')->select();
		$cateline_data = $cateline_data_obj->toArray();
		$this->assign('cateline_data',$cateline_data);
		return $this->fetch();
	}

	//添加
	public function add(Request $request) {
		if( $request->isPost() ) {
			//接收并过滤数据 
			$data['line_code'] = addslashes(strip_tags(trim(input('post.line_code'))));
			$data['is_valid'] = input('post.is_valid');
			$data['add_time'] = date('Y-m-d H:i:s',time());
			//halt($data['line_code']);
			//**验证数据
			$rules = [
				'line_code|贷款金额' =>['require','integer','elt:10000','gt:0','unique'=>'code_line'],
			];
			$message = $this->validate($data,$rules);
			if($message!==true) {
				$this->error($message);die;
			}
			$cateline_model = new CatelineModel();
			$status = $cateline_model->save($data);
			if($status !=false) {
				$this->success('添加成功！','admin/cateline/index');die;
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
			$line_data_obj = CatelineModel::get($id);
			$line_data = $line_data_obj->toArray();
			$this->assign('line_data',$line_data);
			return $this->fetch();
		}else if($request->isPost()) {
			//接收并过滤数据
			$data['id'] = input('post.id');
			$data['line_code'] = addslashes(strip_tags(trim(input('post.line_code'))));
			$data['is_valid'] = input('post.is_valid');
			$data['add_time'] = input('post.add_time');
			$data['modify_time'] = date('Y-m-d H:i:s',time());
			//**验证数据
			$rules = [
				'line_code|贷款期限' =>['require','integer','elt:10000','gt:0','unique'=>'code_line'],
			];
			$message = $this->validate($data,$rules);
			if($message!==true) {
				$this->error($message);die;
			}
			$cateline_model = new CatelineModel();
			$status = $cateline_model->update($data);
			if($status !=false) {
				$this->success('修改成功！','admin/cateline/index');die;
			}else{
				$this->error('修改失败');die;
			}
		}
	}

	//删除
	public function del() {
		$id = input('id');
		$status = CatelineModel::destroy($id);
		if($status!=false) {
			$this->success('删除成功！','admin/cateline/index');die;
		}else{
			$this->error('删除失败！');
		}
	}

		//是否有效，切换
	public function valid(Request $request) {
		$cateline_model = new CatelineModel();
		$id = input('id');
		$cateline_obj   = $cateline_model->where('id',$id)->field('is_valid')->select();
		$cateline = $cateline_obj->toArray();

		if($cateline[0]['is_valid']=='Y') {
			$cateline_model->where('id',$id)->update(['is_valid'=>'N']);
		}else{
			$cateline_model->where('id',$id)->update(['is_valid'=>'Y']);	
		}
		$this->redirect('/admin/cateline/index');die;
	}


}