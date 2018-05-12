<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use think\Request;
use think\Db;
use app\admin\model\Category  as CategoryModel;


/**
 *  贷款类型-分类控制器
 */
class Category extends Common{
	//列表
	public function index() {
		$list_data = Db::table('code_category')
							->alias('a')
							->field('a.*,b.name as p_name')
							->join('code_category b','a.pid = b.id','left')
							->select();
		$list = comb($list_data, $pid=0,$level=0);
		return $this->fetch('index',['list'=>$list]);
	}

	//添加
	public function add(Request $request) {
		if($request->isGet()) {
			$category_model = new CategoryModel();
			$category_data_obj = $category_model->field('id,pid,name')->where('is_valid','Y')->select();
			$category_data = $category_data_obj->toArray();
			$category_data = comb($category_data, $pid=0,$level=0);
			$this->assign('category_data',$category_data);
			return $this->fetch();
		}elseif($request->isPost()) {
			//接收并过滤数据
			$data['name'] = addslashes(strip_tags(trim(input('post.name'))));
			$data['pid']  = input('post.pid');
			$data['add_time']  = date('Y-m-d H:i:s',time());
			$data['is_valid']  = input('post.is_valid');
			//**验证数据
			$rules = [
				'name|贷款分类名称' =>['require','max'=>100,'min'=>2,'unique'=>'code_category'],
			];
			$message = $this->validate($data,$rules);
			if($message!==true) {
				$this->error($message);die;
			}
			$category_model = new CategoryModel();
			$status = $category_model->save($data);
			if($status !=false) {
				$this->success('添加成功！','admin/category/index');die;
			}else{
				$this->error('添加失败');die;
			}
		}

	}

	//修改
	public function edit(Request $request) {
		if($request->isGet()) {
			//查询出下拉列表的信息
			$category_model = new CategoryModel();
			$category_list_obj = $category_model->field('id,pid,name')->where('is_valid','Y')->select();
			$category_list = $category_list_obj->toArray();
			$category_list = comb($category_list, $pid=0,$level=0);
			$this->assign('category_list',$category_list);

			//查询出当前分类的信息
			$id = input('id');
			$category_data_obj = CategoryModel::get($id);
			$category_data = $category_data_obj->toArray();
			$this->assign('category_data',$category_data);
			return $this->fetch();

		}elseif($request->isPost()) {
		    //接收并过滤数据
		    $data['id'] = input('post.id');
		    $data['name'] = addslashes(strip_tags(trim(input('post.name'))));
		    $data['pid'] = input('post.pid');
		    $data['add_time'] = input('post.add_time');
		    $data['modify_time'] = date("Y-m-d H:i:s",time());
		    $data['is_valid'] = input('post.is_valid');
			//**验证数据
			$rules = [
				'name|贷款分类名称' =>['require','max'=>100,'min'=>2,'unique'=>'code_category'],
			];
			$message = $this->validate($data,$rules);
			if($message!==true) {
				$this->error($message);die;
			}

			$category_model = new CategoryModel();
			$status = $category_model->update($data);
			if($status !=false) {
				$this->success('修改成功！','admin/category/index');die;
			}else{
				$this->error('修改失败');die;
			}

		}
	}

	//删除
	public function del() {
		//删除分类前先判断是否有子分类，如果有子分类则不能删除，需删除子分类才能删
		$id = input('id');
		$category_model = new CategoryModel();
		//以当前id值查询表中pid列是否有相等的值，如果有则说明当前分类下有子类！
		$data_obj = $category_model->field('id')->where('pid',$id)->select();
		$data = $data_obj->toArray();
		if(empty($data)) {
			//为空则删除
			$status = CategoryModel::destroy($id);
			if($status!=false) {
				$this->success('删除成功！','admin/category/index');die;
			}else{
				$this->error('删除失败！');die;
			}
		}else{
			//不为空则不能删除，给出提示！
			$this->error('此分类下有子分类，如需删除则需先删除子分类！');die;
		}
	}

		//是否有效，切换
	public function valid(Request $request) {
		$category_model = new CategoryModel();
		$id = input('id');
		$category_obj   = $category_model->where('id',$id)->field('is_valid')->select();
		$category = $category_obj->toArray();

		if($category[0]['is_valid']=='Y') {
			$category_model->where('id',$id)->update(['is_valid'=>'N']);
		}else{
			$category_model->where('id',$id)->update(['is_valid'=>'Y']);	
		}
		$this->redirect('/admin/category/index');die;
	}




}