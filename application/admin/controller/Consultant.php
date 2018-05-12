<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use think\Request;
use think\Session;
use app\admin\model\Consultant as ConsultantModel; 

/**
 *  顾问信息控制器
 */
class Consultant extends Common{
	//顾问列表
	public function index() {
		//删除原图（新上传的原图有则删，没有则跳过）
		$big_logo_path = @Session::get('big_logo_path');
	    if(file_exists($big_logo_path)&&chmod($big_logo_path,0777)) {	
	     	unlink($big_logo_path);
	     	Session::delete('big_logo_path');
	     	unset($big_logo_path);
	    }

		$consultantModel = new ConsultantModel();
		//分页
		$list  = $consultantModel->order('id','asc')->paginate(12); //分页，每页12条数据
		$count = $consultantModel->count(); //总记录数
		$page  = $list->render();      //页码数据
		$currentPage = $list->currentPage(); //当前的页码
		return $this->fetch('index',['list'=>$list ,'page'=>$page,'count'=>$count,'currentPage'=>$currentPage]);
	
	}

	//添加顾问信息
	public function add(Request $request) {
		if( $request->isPost() ) {
			//文件上传处理
		    $file = request()->file('image');
		    //判断是否有文件上传，没有则给出提示
		    if (empty($file)) { 
		      $this->error('请选择上传文件'); die;
		    }
		    //过滤数据
		    $data['name']     = addslashes(strip_tags(trim(input('post.name'))));
		    $data['area']     = addslashes(strip_tags(trim(input('post.area'))));
		    $data['tell']     = addslashes(strip_tags(trim(input('post.tell'))));
		    $data['desc']     = htmlspecialchars(input('post.desc'));
		    $data['is_valid'] = input('post.is_valid');
		    $data['add_time'] =  date('Y-m-d H:i:s',time());
			//**验证数据
			$rules = [
				'name|名称' =>['require','max'=>60,'min'=>2],
				'area|区域' =>['require','max'=>30,'min'=>2],
				'tell|电话' =>['require','max'=>30,'min'=>12],
				'desc|描述' =>['require','max'=>120,'min'=>2]
			];
			$message = $this->validate($data,$rules);
			if($message!==true) {
				$this->error($message);die;
			}
		    //上传文件移动到框架应用根目录/public/uploads/consultant目录下
		    $info = $file->validate(['size'=>8388608,'ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'uploads'.DS.'consultant');
		    //判断上传图片是否成功！
		    if( $info!== false ){
				//调用image类静态方法打开图片
				$big_logo_path = './uploads/consultant/'.$info->getSaveName(); //上传原图的存储路径
		    	$image = \think\Image::open($big_logo_path);
		    	//缩略图片路径
		    	$thumb_logo_path = './thumb/consultant/'.$info->getFileName();
		    	$res = $image->thumb(100,120,\think\Image::THUMB_CENTER)->save($thumb_logo_path );

		    	if($res!==false) {
		    		//图片缩略成功，将缩略图路径存放到数据库中
					$data['image'] = '/thumb/consultant/'.$info->getFileName();
					$consultantModel = new ConsultantModel();
				    $status = $consultantModel->save($data);
				    if($status !== false) {
				    	//********删原图*********
				    	//在一个执行周期内删不了原图，则将原图路径保存到session中，跳转到显示列表页中再删除原图
					    $big_logo_path = str_replace('/','\\',$big_logo_path);
					    Session::set('big_logo_path',$big_logo_path);

				    	$this->success('成功添加顾问信息！',url('admin/consultant/index'));die;
				    }else{
				    	$this->error('添加失败！');die;
				    }	
		    	}else{
		    		return $this->error('图片上传失败！'.$res->getError());die;         
		    	}
		    }else{        
		    	// 上传失败获取错误信息
		    	return $this->error('图片上传失败！'.$file->getError());die;         
		    } 
		}
		return $this->fetch();
	}

	//修改顾问信息
	public function edit(Request $request) {
		//判断是否为get请求
		if( $request->isGet() ) {
			$id   = input('id');
			//数据库提取当前新闻信息
			$info     = consultantModel::get($id);
			$consultant_data = $info->toArray();
			return $this->fetch('edit',['consultant_data'=>$consultant_data]);

		}elseif( $request->isPost() ) {
			//判断是否有文件上传更改
			$file = request()->file('image');
			//分两种情况，有文件上传的数据更新和没有文件上传的数据处理
			//有文件上传的数据处理
			if(!empty($file)) {
			    //过滤数据
			    $data['id']       = input('id');
			    $data['name']     = addslashes(strip_tags(trim(input('post.name'))));
			    $data['area']     = addslashes(strip_tags(trim(input('post.area'))));
			    $data['tell']     = addslashes(strip_tags(trim(input('post.tell'))));
			    $data['desc']     = htmlspecialchars(input('post.desc'));
			    $data['is_valid'] = input('post.is_valid');
			    $data['add_time'] = input('post.add_time');
			    $data['modify_time'] =  date('Y-m-d H:i:s',time());
				//**验证数据
				$rules = [
					'name|名称' =>['require','max'=>60,'min'=>2],
					'area|区域' =>['require','max'=>30,'min'=>2],
					'tell|电话' =>['require','max'=>30,'min'=>12],
					'desc|描述' =>['require','max'=>120,'min'=>2]
				];
				$message = $this->validate($data,$rules);
				if($message!==true) {
					$this->error($message);die;
				}
				//存在文件上传,保存更新图片，更新地址，删除之前缩略图
				//移动图片到指定的项目文件夹下
				$info = $file->validate(['size'=>8388608,'ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'uploads'.DS.'consultant');
				//判断图片上传验证是否通过
				if($info!==false) {
					//验证通过，对图片进行缩略图处理
					$big_logo_path = './uploads/consultant/'.$info->getSaveName();//原图的路径
			    	$image = \think\Image::open($big_logo_path);//打开图片
			    	$thumb_logo_path = './thumb/consultant/'.$info->getFileName(); //定义缩略图的路径
			    	//缩略图片,并保存到指定文件夹
			    	$res = $image->thumb(100,120,\think\Image::THUMB_CENTER)->save($thumb_logo_path );
			    	//判断缩略图片是否成功
			    	if($res!==false) {
			    		//将缩略图路径更新到数据库中
						$data['image'] = '/thumb/consultant/'.$info->getFileName();
						$id = input('post.id');
						//在更新数据之前，先得到旧缩略图片的路径,
						$old_info_obj = ConsultantModel::get($id);
						$old_info = $old_info_obj->toArray();
						$old_thumb_image_path = '.'.$old_info['image']; //获取旧缩略图片的路径
						//将更改的数据新增到数据库，成功之后删除之前上传的缩略图片
						$status = $old_info_obj->update($data);
						if($status !== false) {
							//数据更改成功，先删除旧缩略图
							if( file_exists($old_thumb_image_path)&&chmod($old_thumb_image_path,0777) ){
					          unlink( $old_thumb_image_path );
					        }
					    	//********删原图(要删除重新上传的原图)*********
					    	//在一个执行周期内删不了原图，则将原图路径保存到session中，跳转到显示列表页中再删除原图
						    $big_logo_path = str_replace('/','\\',$big_logo_path);
						    Session::set('big_logo_path',$big_logo_path);

					    	$this->success('更改顾问信息成功！',url('admin/consultant/index'));die;
					    }else{
					    	$this->error('更改顾问信息失败!');die;
					    }	

			    	}else{
			    		$this->error('图片缩小失败'.$info->getError());die;
			    	}
				}else{
					//更改的图片上传验证没通过
					$this->error('图片上传失败'.$info->getError());die;
				}
			}
			//如果没有更改上传文件，则不用对上传的数据进行处理，直接更新表单其他数据
		    //过滤数据
		    $data['id']       = input('id');
		    $data['name']     = addslashes(strip_tags(trim(input('post.name'))));
		    $data['area']     = addslashes(strip_tags(trim(input('post.area'))));
		    $data['tell']     = addslashes(strip_tags(trim(input('post.tell'))));
		    $data['desc']     = htmlspecialchars(input('post.desc'));
		    $data['is_valid'] = input('post.is_valid');
		    $data['add_time'] = input('post.add_time');
		    $data['modify_time'] =  date('Y-m-d H:i:s',time());
			//**验证数据
			$rules = [
				'name|名称' =>['require','max'=>60,'min'=>2],
				'area|区域' =>['require','max'=>30,'min'=>2],
				'tell|电话' =>['require','max'=>30,'min'=>12],
				'desc|描述' =>['require','max'=>120,'min'=>2]
			];
			$message = $this->validate($data,$rules);
			if($message!==true) {
				$this->error($message);die;
			}
			if( ConsultantModel::update($data) !== false ) {
		    	$this->success('更改顾问信息成功！',url('admin/consultant/index'));die;
			}else{
				$this->error('更改顾问信息失败！' );die;
			}	
		}
	}





	//删除顾问信息
	public function del(Request $request) {
		//接收get传递过来的id
		if($request->isGet()) {
			$current_consultant_id = input('id');
			$info 			       = ConsultantModel::get($current_consultant_id); //根据id获取当前信息
			$thumb_image_path      = '.'.$info->image; //获取存储图片的路径

			$status = ConsultantModel::destroy($current_consultant_id); //删除此顾问的数据库信息
			if($status!==false) {
				//删除信息的同时删除图片
				if( file_exists($thumb_image_path )&&chmod($thumb_image_path,0777)  ) {
					unlink( $thumb_image_path );
				}
			    $this->success('顾问信息删除成功！','admin/consultant/index',1);die;
			}else{
				$this->error('删除失败!');die;
			}
		}
	}

	//是否有效，切换
	public function valid(Request $request) {
		$consultantModel = new ConsultantModel();
		$current_consultant_id = input('id');
		$consultant_data_obj   = $consultantModel->where('id',$current_consultant_id)->field('is_valid')->select();
		$consultant_data = $consultant_data_obj->toArray();
		if($consultant_data[0]['is_valid']=='Y') {
			$consultantModel->where('id',$current_consultant_id)->update(['is_valid'=>'N']);
		}else{
			$consultantModel->where('id',$current_consultant_id)->update(['is_valid'=>'Y']);	
		}
		$this->redirect('/admin/consultant/index');die;
	}

}