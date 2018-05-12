<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use think\Request;
use think\Session;
use app\admin\model\News as NewsModel;
use app\admin\model\Frilink as FrilinkModel;
/**
 *  友情链接控制器
 */
class Frilink extends Common{

	//友情链接列表页
	public function index(){
		$frilinkModel = new FrilinkModel();
		$list  = $frilinkModel->order('id','asc')->paginate(10); //分页，每页10条数据
		$count = $frilinkModel->count(); //总记录数
		$page  = $list->render();      //页码数据
		$currentPage = $list->currentPage(); //当前的页码
		return $this->fetch('index',['list'=>$list ,'page'=>$page,'count'=>$count,'currentPage'=>$currentPage]);
	}


	//友情链接添加
	public function add(Request $request){
		if($request->isPost()){
			//过滤数据
			$data['name']     = addslashes(strip_tags(trim(input('post.name'))));
			$data['url']      = addslashes(strip_tags(trim(input('post.url'))));
			$data['add_time'] = date('Y-m-d H:i:s',time());
			$data['is_valid'] =  input('post.is_valid');
			//**验证数据
			$rules = [
				'name|链接名称' =>['require','max'=>32,'min'=>2,'unique'=>'link'],
				'url|链接地址'    =>['require','url','unique'=>'link'],
			];
			$message = $this->validate($data,$rules);
			if($message!==true) {
				$this->error($message);die;
			}
			//添加数据到数据表中
			$frilinkModel = new FrilinkModel();
			$status = $frilinkModel->save($data);
			if($status!==false) {
				$this->success('添加链接成功！',url('admin/frilink/index'));die;
			}else{
				$this->error('添加链接失败！');die;
			}
		}
		return $this->fetch();
	}


	//友情链接修改
	public function edit(Request $request){
		if($request->isGet()) {
			$id = input('id');
			$info = FrilinkModel::get($id);
			$link_data = $info->toArray();
			return $this->fetch('edit',['link_data'=>$link_data]);

		}elseif($request->isPost()) {
			//过滤数据
			$data['id']          = input('post.id');
			$data['name']        = addslashes(strip_tags(trim(input('post.name'))));
			$data['url']         = addslashes(strip_tags(trim(input('post.url'))));
			$data['add_time']    = input('post.add_time');
			$data['is_valid']    = input('post.is_valid');
			$data['modify_time'] = date('Y-m-d H:i:s',time());
			//**验证数据
			$rules = [
				'name|链接名称' =>['require','max'=>32,'min'=>2,'unique'=>'link'],
				'url|链接地址'    =>['require','url','unique'=>'link'],
			];
			$message = $this->validate($data,$rules);
			if($message!==true) {
				$this->error($message);die;
			}
			//更新数据到数据库中
			$frilinkModel = new FrilinkModel();
			$status = $frilinkModel->update($data);
			if($status!==false){
				$this->success('修改友情链接成功！',url('admin/frilink/index'));die;
			}else{
				$this->error('修改失败！');die;
			}
		}
	}


	//友情链接删除
	public function del(){
		//接收ID
		$id = input('id');
        //删除数据库数据
        $status = FrilinkModel::destroy($id);
        if($status!==false) {
        	$this->success('删除成功！','admin/frilink/index',3);die;
        }else{
        	$this->error('删除失败。');die;
        }

	}

}