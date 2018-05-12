<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use think\Request;
use app\admin\model\Catework  as CateworkModel;

/**
 *  职业身份分类控制器
 */
class Catework extends Common{
	//列表页
	public function index() {
		$cate_work_model = new CateworkModel();
		$list  = $cate_work_model->order('code','asc')->paginate();
		$count = $list->total(); //总记录数
		$page  = $list->render();      //页码数据
		$currentPage = $list->currentPage(); //当前的页码
		return $this->fetch('index',['list'=>$list ,'page'=>$page,'count'=>$count,'currentPage'=>$currentPage]);
	}

	//添加
	public function add(Request $request) {

		if($request->isPost()) {
			//接收过滤数据
			$data['name'] = addslashes(strip_tags(trim(input('post.name'))));
			$data['add_time'] = date('Y-m-s H:i:s',time());
			$data['is_valid'] = input('post.is_valid');
			//**验证数据
			$rules = [
				'name|职业身份名称' =>['require','max'=>100,'min'=>2,'unique'=>'code_work_type'],
			];
			$message = $this->validate($data,$rules);
			if($message!==true) {
				$this->error($message);die;
			}

			$cate_work_model = new CateworkModel();
			$status = $cate_work_model->save($data);
			if($status != false) {
				$this->success('添加成功！','admin/catework/index');die;
			}else{
				$this->error('添加失败！');die;
			}
		}
		return $this->fetch();
	}

	//修改
	public function edit(Request $request) {
		if($request->isGet()) {
			$code = input('code');
			$cate_work_data = CateworkModel::get($code);
			$this->assign('cate_work_data',$cate_work_data);
			return $this->fetch();
		}elseif($request->isPost()) {
			//接收过滤数据
			$data['code'] = input('post.code');
			$data['name'] = addslashes(strip_tags(trim(input('post.name'))));
			$data['add_time'] = input('post.add_time');
			$data['modify_time'] = date('Y-m-s H:i:s',time());
			$data['is_valid'] = input('post.is_valid');
			//**验证数据
			$rules = [
				'name|职业身份名称' =>['require','max'=>100,'min'=>2,'unique'=>'code_work_type'],
			];
			$message = $this->validate($data,$rules);
			if($message!==true) {
				$this->error($message);die;
			}
			//修改数据表数据
			$cate_work_model = new CateworkModel();
			$status = $cate_work_model->update($data);
			if($status!=false) {
				$this->success('修改成功！','admin/catework/index');die;
			}else{
				$this->error('修改失败');die;
			}
		}
	}

	//删除
	public function del(Request $request) {
		if($request->isGet()) {
			$code = input('code');
			$status = CateworkModel::destroy($code); 
			if($status!=false) {
				$this->success('删除成功！','admin/catework/index');die;
			}else{
				$this->error('删除失败');die;
			}
		}
	}

	//是否有效，切换
	public function valid(Request $request) {
		$cate_work_model = new CateworkModel();
		$code = input('code');
		$work_data_obj   = $cate_work_model->where('code',$code)->field('is_valid')->select();
		$work_data = $work_data_obj->toArray();

		if($work_data[0]['is_valid']=='Y') {
			$cate_work_model->where('code',$code)->update(['is_valid'=>'N']);
		}else{
			$cate_work_model->where('code',$code)->update(['is_valid'=>'Y']);	
		}
		$this->redirect('/admin/catework/index');die;
	}


}