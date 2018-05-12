<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use think\Request;
use app\admin\model\Banner as BannerModel;
/**
 *  轮播图控制器
 */
class Banner extends Common{
	//后台轮播图列表页
	public function index() {
		//获取所有轮播图数据
		$bannerModel = new BannerModel();
		$list  = $bannerModel->order('id','asc')->paginate(15); //分页，每页5条数据
		$count = $bannerModel->count(); //总记录数
		$page  = $list->render();       //页码数据
		$currentPage = $list->currentPage(); //当前的页码
		return $this->fetch('index',['list'=>$list ,'page'=>$page,'count'=>$count,'currentPage'=>$currentPage]);
	}

	//新增轮播图
	public function add(Request $request) {
		$bannerModel = new BannerModel();

		//获取所有轮播图数据
		if($request->isPost()) {

			//文件上传处理
		    $file = request()->file('image');
		    //判断是否有文件上传，没有则给出提示
		    if (empty($file)) { 
		      $this->error('请选择上传文件'); die;
		    }

			//接收表单数据 
			$banner_data = input('post.');
			//**验证数据
			$rules = [
				'name|广告名称' => ['require','max'=>100,'min'=>3,'unique'=>'banner'],
				'link_url|跳转网址' =>['url'],
				'order|轮播顺序' => ['require','integer','unique'=>'banner','gt:0'],
			];
			$message = $this->validate($banner_data,$rules);
			if($message!==true) {
				$this->error($message);die;
			}
			//上传图片处理
			$info = $file->validate(['size'=>1313280,'ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'uploads'.DS.'banner');
			//判断是否上传图片成功！
			if($info !== false) {
				//上传成功，获取图片保存路径，
				$banner_path = '/uploads/banner/'.$info->getSaveName(); //上传原图的存储路径
				$banner_path = str_replace('\\','/',$banner_path);
				$banner_data['image'] = $banner_path;
				//halt($banner_data);
				$banner_data['add_time'] = date('Y-m-d H:i:s',time());
				$statu = $bannerModel->save($banner_data);
				if($statu) {
					$this->success('成功添加轮播图！',url('admin/banner/index'));die;
				}else{
					$this->error('轮播图添加失败！'.$statu->gerError(),3);
				}
			}else{
				return $this->error('图片上传失败！'.$file->getError());die;
			}
		}
		return $this->fetch();
	}

	//修改轮播图
	public function edit(Request $request) {
		$bannerModel = new BannerModel(); 

		if( $request->isGet() ) {
			$banner_id = input('id');
			$banner_info = $bannerModel->find($banner_id);
			$banner_info = $banner_info->toArray();
			return $this->fetch('edit',['banner_info'=>$banner_info]);
		}elseif( $request->isPost() ) {
			//先判断是否有上传图片
			$file = $request->file('image');
			if(!empty($file)) {
				//有文件上传
				//接收post表单数据
				$banner_data = input('post.');
				//**验证数据
				$rules = [
					'name|广告名称' => ['require','max'=>100,'min'=>3],
					'link_url|跳转网址' =>['url'],
					'order|轮播顺序' => ['require','integer','gt:0'],
				];
				$message = $this->validate($banner_data,$rules);
				if($message!==true) {
					$this->error($message);die;
				}
				//上传图片处理
				$info = $file->validate(['size'=>1313280,'ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'uploads'.DS.'banner');
				//判断是否上传图片成功！
				if($info !== false) {
					//上传成功，获取旧图片保存路径，
					$old_banner_obj = $bannerModel->get(input('post.id'));
					$old_banner_data = $old_banner_obj->toArray();
					$old_banner_path = '.'.$old_banner_data['image'];
					//上传成功，获取新图片保存路径，
					$banner_path = '/uploads/banner/'.$info->getSaveName(); //上传原图的存储路径
					$banner_path = str_replace('\\','/',$banner_path);
					$banner_data['image'] = $banner_path;
					$statu = $bannerModel->update($banner_data);
					if($statu) {
						//删除旧图
						if( file_exists($old_banner_path)&&chmod($old_banner_path,0777) ){
				          unlink( $old_banner_path );
				        }

						$this->success('成功更改轮播图！',url('admin/banner/index'));die;
					}else{
						$this->error('轮播图更改失败！'.$statu->gerError(),3);
					}
				}else{
					return $this->error('图片更改失败！'.$file->getError());die;
				}

			}else{
				//没有文件上传,只做除图片以外的数据更新！
				//接收post表单数据
				$banner_data = input('post.');
				//**验证数据
				$rules = [
					'name|广告名称' => ['require','max'=>100,'min'=>3],
					'link_url|跳转网址' =>['url'],
					'order|轮播顺序' => ['require','integer','gt:0'],
				];
				$message = $this->validate($banner_data,$rules);
				if($message!==true) {
					$this->error($message);die;
				}
				//更新数据库数据
				$statu = $bannerModel->update($banner_data);
				if($statu) {
					$this->success('成功更改轮播图信息！',url('admin/banner/index'));die;
				}else{
					$this->error('轮播图信息更改失败！'.$statu->gerError(),3);
				}
			}
		}
	}

	//删除轮播图
	public function del(Request $request) {
		if($request->isGet()) {
			//接收轮播图对应id
			$banner_id = input('id');
			$info 			  = BannerModel::get($banner_id); //根据id获取当前轮播图信息
			$old_image_path = '.'.$info->image; //获取存储旧图图片的路径
			$statu = BannerModel::destroy($banner_id); //删除此轮播图的数据库信息
			if($statu!==false) {
				//删除数据库信息后，也要删除项目文件夹中的旧图片
				if( file_exists($old_image_path )&&chmod($old_image_path,0777)  ) {
					unlink( $old_image_path );
				}
				$this->success('轮播图删除成功！','admin/banner/index',3);die;
			}else{
				$this->error('轮播图删除失败！');die;
			}
		}
	}

	//是否有效，切换
	public function valid(Request $request) {
		$bannerModel = new BannerModel();
		$current_banner_id = input('id');
		$banner_data_obj = $bannerModel->where('id',$current_banner_id)->field('is_valid')->select();
		$banner_data = $banner_data_obj->toArray();
		if($banner_data[0]['is_valid']=='Y') {
			$bannerModel->where('id',$current_banner_id)->update(['is_valid'=>'N']);
		}else{
			$bannerModel->where('id',$current_banner_id)->update(['is_valid'=>'Y']);	
		}
		$this->redirect('/admin/banner/index');die;
	}

}
