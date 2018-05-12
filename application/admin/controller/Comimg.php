<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use think\Request;
use think\Session;
use app\admin\model\Comimg as ComimgModel;
/**
 * 公司相册控制器
 */

class Comimg extends Common{
	//相册列表页
	public function index() {
		//删除原图（新上传的原图有则删，没有则跳过）
		$big_logo_path = @Session::get('big_logo_path');
	    if(file_exists($big_logo_path)&&chmod($big_logo_path,0777)) {	
	     	unlink($big_logo_path);
	     	Session::delete('big_logo_path');
	     	unset($big_logo_path);
	    }

		$comimgModel = new ComimgModel();
		//分页
		$list  = $comimgModel->order('id','asc')->paginate(10); 
		$count = $comimgModel->count(); //总记录数
		$page  = $list->render();      //页码数据
		$currentPage = $list->currentPage(); //当前的页码
		return $this->fetch('index',['list'=>$list ,'page'=>$page,'count'=>$count,'currentPage'=>$currentPage]);
	}


	//添加相片
	public function add(Request $request) {

		if($request->isPost()) {
			//文件上传处理
		    $file = request()->file('image');
		    //判断是否有文件上传，没有则给出提示
		    if (empty($file)) { 
		      $this->error('请选择上传文件'); die;
		    }
		    //过滤数据
		    $data['name']     = addslashes(strip_tags(trim(input('post.name'))));
		    $data['add_time'] = date('Y-m-d H:i:s',time());
		    $data['is_valid'] = input('post.is_valid');
			//**验证数据
			$rules = [
				'name|图片名称' =>['require','max'=>200,'min'=>2,'unique'=>'img'],
			];
			$message = $this->validate($data,$rules);
			if($message!==true) {
				$this->error($message);die;
			}
		    //上传文件移动到框架应用根目录/public/uploads/comimg目录下
		    $info = $file->validate(['size'=>8388608,'ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'uploads'.DS.'comimg');
		    //判断上传图片是否成功！
		    if( $info!== false ){
				//调用image类静态方法打开图片
				$big_logo_path = './uploads/comimg/'.$info->getSaveName(); //上传原图的存储路径
		    	$image = \think\Image::open($big_logo_path);
		    	//缩略图片路径
		    	$thumb_logo_path = './thumb/comimg/'.$info->getFileName();
		    	$res = $image->thumb(640,320,\think\Image::THUMB_CENTER)->save($thumb_logo_path );
		    	if($res!==false) {
		    		//图片缩略成功，将缩略图路径存放到数据库中
					$data['image'] = '/thumb/comimg/'.$info->getFileName();
					$comimgModel = new ComimgModel();
				    $status = $comimgModel->save($data);
				    if($status !== false) {
				    	//********删原图*********
				    	//在一个执行周期内删不了原图，则将原图路径保存到session中，跳转到显示列表页中再删除原图
					    $big_logo_path = str_replace('/','\\',$big_logo_path);
					    Session::set('big_logo_path',$big_logo_path);

				    	$this->success('成功添加图片！',url('admin/comimg/index'));die;
				    }else{
				    	$this->error('添加失败！');die;
				    }	
		    	}else{
		    		return $this->error('图片缩略失败！'.$res->getError());die;
		    	}
		    }else{
		    	return $this->error('图片上传失败！'.$file->getError());die;
		    }
		}
		return $this->fetch();
	}


	//更改相片
	public function edit(Request $request) {

		if( $request->isGet() ) {

			$id = input('id');
			//获取当前要更改的图片数据
			$info     = ComimgModel::get($id);
			$comimg_data = $info->toArray();
			$this->assign('comimg_data',$comimg_data);
			return $this->fetch();

		}elseif( $request->isPost() ) {
			//判断是否有文件上传更改
			$file = request()->file('image');
			//分两种情况，有文件上传的数据更新和没有文件上传的数据处理
			//有文件上传的数据处理
			if(!empty($file)) {
				//有文件上传，数据在此判断内做处理
			    //数据过滤
			    $data['id']          = input('post.id');
			    $data['name']        = addslashes(strip_tags(trim(input('post.name'))));
			    $data['add_time']    = input('post.add_time');
			    $data['modify_time'] = date('Y-m-d H:i:s',time());
			    $data['is_valid']    = input('post.is_valid');
				//**验证数据
				$rules = [
					'name|图片名称' =>['require','max'=>200,'min'=>2,'unique'=>'img'],
				];
				$message = $this->validate($data,$rules);
				if($message!==true) {
					$this->error($message);die;
				}
				//将重新上传的图片移动到指定的项目文件夹下
				$info = $file->validate(['size'=>8388608,'ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'uploads'.DS.'comimg');
				//判断上传的图片是否通过文件上传验证
				if($info!==false) {
					//重新上传的图片成功！对图片进行缩略图处理
					$big_logo_path = './uploads/comimg/'.$info->getSaveName();//原图的路径
			    	$image = \think\Image::open($big_logo_path);//打开图片
			    	$thumb_logo_path = './thumb/comimg/'.$info->getFileName(); //定义缩略图的路径
			    	//缩略图片
			    	$res = $image->thumb(640,320,\think\Image::THUMB_CENTER)->save($thumb_logo_path );
			    	//判断缩略图片是否成功
			    	if($res!==false) {
			    		//将新的图片路径保存到数组中，待用于更新数据库路径
						$data['image'] = '/thumb/comimg/'.$info->getFileName();
						$id = input('post.id');
						//取出旧图的路径保存到变量中，用于后面删除文件夹中的旧缩略图片
						$old_info_obj = ComimgModel::get($id);
						$old_info = $old_info_obj->toArray();
						$old_thumb_image_path = '.'.$old_info['image']; //获取旧缩略图片的路径
						//将更改的数据更新到数据库，同时删除之前上传的缩略图片
						$status = $old_info_obj->update($data);
						if($status !== false) {
							//数据更新成功！接着删除旧的缩略图
							if( file_exists($old_thumb_image_path)&&chmod($old_thumb_image_path,0777) ){
					          unlink( $old_thumb_image_path );
					        }
					    	//********删原图(要删除重新上传的原图)*********
					    	//在一个执行周期内删不了原图，则将原图路径保存到session中，跳转到显示列表页中再删除原图
						    $big_logo_path = str_replace('/','\\',$big_logo_path);
						    Session::set('big_logo_path',$big_logo_path);

					    	$this->success('更改图片成功！',url('admin/comimg/index'));die;
						}else{
							$this->error('图片更改失败！');die;
						}
			    	}else{
			    		$this->error('图片缩小失败'.$info->getError());die;
			    	}
				}else{
					$this->error('重新上传的图片失败'.$info->getError());die;
				}
			}
			//如果没有更改上传文件，则不用对上传的数据进行处理，直接更新表单其他数据
			//过滤数据
		    $data['id']          = input('post.id');
		    $data['name']        = addslashes(strip_tags(trim(input('post.name'))));
		    $data['add_time']    = input('post.add_time');
		    $data['modify_time'] = date('Y-m-d H:i:s',time());
		    $data['is_valid']    = input('post.is_valid');   
			//**验证数据
			$rules = [
				'name|图片名称' =>['require','max'=>200,'min'=>2,'unique'=>'img'],
			];
			$message = $this->validate($data,$rules);
			if($message!==true) {
				$this->error($message);die;
			}
			//通过数据的验证后，将数据更新到数据库（图片没有变化）
			if( ComimgModel::update($data) !== false ) {
		    	$this->success('图片信息更改成功！',url('admin/comimg/index'));die;
			}else{
				$this->error('图片信息更改失败！' );die;
			}	
		}
		
	}


	//删除相片
	public function del(Request $request) {
		//接收get传递过来的id
		if($request->isGet()) {
			$comimg_id        = input('id');
			$info 			  = ComimgModel::get($comimg_id); //根据id获取当前图片信息
			$thumb_image_path = '.'.$info->image; //获取存储图片的路径

			$status = ComimgModel::destroy($comimg_id); //删除此图片的数据库信息
			if($status!==false) {
				//删除信息的同时删除在文件夹中的图片
				if( file_exists($thumb_image_path )&&chmod($thumb_image_path,0777)  ) {
					unlink( $thumb_image_path );
				}
			    $this->success('删除图片成功！','admin/comimg/index',3);die;
			}else{
				$this->error('删除失败!');die;
			}
		}
	}

	//是否展示
	public function valid(Request $request) {
		$comimgModel     = new ComimgModel();
		$comimg_id = input('id');
		$comimg_data_obj   = $comimgModel->where('id',$comimg_id)->field('is_valid')->select();
		$comimg_data = $comimg_data_obj->toArray();
		if($comimg_data[0]['is_valid']=='Y') {
			$comimgModel->where('id',$comimg_id)->update(['is_valid'=>'N']);
		}else{
			$comimgModel->where('id',$comimg_id)->update(['is_valid'=>'Y']);	
		}
		$this->redirect('/admin/comimg/index');die;
	}



}