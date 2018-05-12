<?php
namespace app\admin\controller;
use app\admin\controller\Common;
//use think\Request;
use think\Session;
use app\admin\model\About as AboutModel;
/**
 *  关于我们控制器
 */
class About extends Common{
	public function test(){
		$p=input('get.p');
		if($p==1) {
			echo Session::get('id');die;
		}
		$id = 2;
		Session::set('id',$id);
		echo 'ok';die;
		
	}
	//信息列表页
	public function index() {
		$aboutModel = new AboutModel();
		//分页信息
		$list = $aboutModel->order('key','asc')->paginate(12); //分页数据
		$count = $aboutModel->count(); //总记录书
		$page = $list->render(); //页码数据
		$currentPage =$list->currentPage(); //当前页码数值
		return $this->fetch('index',['list'=>$list,'page'=>$page,'count'=>$count,'currentPage'=>$currentPage]);
	}

	//信息添加
	// public function add(Request $request) {
	// 	if( $request->isPost() ) {
	// 		//halt(input('post.'));
	// 		//过滤信息
	// 		$about_data['key'] = addslashes(strip_tags(trim(input('post.key'))));
	// 		$about_data['name'] = addslashes(strip_tags(trim(input('post.name'))));
	// 		$about_data['value'] = addslashes(strip_tags(trim(input('post.value'))));
	// 		$about_data['is_valid'] = input('post.is_valid');
	// 		//***验证
	// 		//验证规则
	// 		$rules = [
	// 			'key|键名' => ['require','alphaDash','max'=>64,'min'=>1,'unique'=>'config'],
	// 			'name|属性名称' => ['require','unique'=>'config','max'=>64,'min'=>1],
	// 			'value|属性值' => ['require','max'=>2000,'min'=>1],
	// 		];
 //            $message = $this->validate($about_data,$rules);
 //            if($message!==true) {
 //                $this->error($message);die;
 //            } 
 //            //保存数据
 //            $aboutModel = new AboutModel();
 //            $status = $aboutModel->save($about_data);
 //            if( $status !== false ) {
 //                $this->success('信息添加成功！',url('admin/about/index'));die;
 //            }else{
 //                $this->error('信息添加失败');
 //            } 
	// 	}

	// 	return $this->fetch();
	// }

    //信息修改
	public function edit(Request $request) {
		if($request->isGet()) {
			$aboutModel = new AboutModel();
			$current_key = input('key');
			$about_data = $aboutModel->get($current_key);
		    return $this->fetch('edit',['about_data'=>$about_data]);
		}elseif($request->isPost()) {
			//过滤信息
			$about_data['key'] = input('post.key');
			$about_data['name'] = addslashes(strip_tags(trim(input('post.name'))));
			$about_data['value'] = addslashes(strip_tags(trim(input('post.value'))));
			$about_data['is_valid'] = input('post.is_valid');
			//验证规则
			$rules = [
				'name|属性名称' => ['require','unique'=>'config','max'=>64,'min'=>1],
				'value|属性值' => ['require','max'=>2000,'min'=>1],
			];
		    $message = $this->validate($about_data,$rules);
            if($message!==true) {
                $this->error($message);die;
            }
            //更新数据
            $aboutModel = new AboutModel();
            $status = $aboutModel->update($about_data);
            if( $status !== false ) {
                $this->success('信息更改成功！',url('admin/about/index'));die;
            }else{
                $this->error('信息更改失败');
            } 

		}

	}

	public function valid() {
		$aboutModel = new AboutModel();
		$current_about_key = input('key');
		$about_data_obj = $aboutModel->where('key',$current_about_key)->field('is_valid')->select();
		$about_data = $about_data_obj->toArray();
		if($about_data[0]['is_valid']=='Y') {
			$aboutModel->where('key',$current_about_key)->update(['is_valid'=>'N']);
		}else{
			$aboutModel->where('key',$current_about_key)->update(['is_valid'=>'Y']);	
		}
		$this->redirect('/admin/about/index');die;
	}

}