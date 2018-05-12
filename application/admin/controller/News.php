<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use think\Request;
use think\Session;
use app\admin\model\News as NewsModel;
/**
 *  资讯文章控制器
 */
class News extends Common{
	//文章列表页
	public function index( ) {
		//删除原图（新上传的原图有则删，没有则跳过）
		$big_logo_path = @Session::get('big_logo_path');
	    if(file_exists($big_logo_path)&&chmod($big_logo_path,0777)) {	
	     	unlink($big_logo_path);
	     	Session::delete('big_logo_path');
	     	unset($big_logo_path);
	    }

		$newsModel   = new NewsModel();
		//分页
		$list  = $newsModel->order('id','asc')->paginate(10); //分页，每页10条数据
		$count = $newsModel->count(); //总记录数
		$page  = $list->render();      //页码数据
		$currentPage = $list->currentPage(); //当前的页码
		return $this->fetch('index',['list'=>$list ,'page'=>$page,'count'=>$count,'currentPage'=>$currentPage]);
	}

	//文章添加
	public function add(Request $request ) {
		$newsModel = new NewsModel();
		//判断是否收到post表单提交
		if( $request->isPost() ) {
			//文件上传处理
		    $file = request()->file('image');
		    //判断是否有文件上传，没有则给出提示
		    if (empty($file)) { 
		      $this->error('请选择上传文件'); die;
		    }
			//过滤数据
			$data['title']   = addslashes(strip_tags(trim(input('post.title'))));
			$data['author']  = addslashes(strip_tags(trim(input('post.author'))));
			$data['content'] = htmlspecialchars(input('post.content'));
			$data['add_time']  =  date('Y-m-d H:i:s',time());
			$data['is_valid']  =  input('post.is_valid');
			$data['is_hot']    =  input('post.is_hot');
			$data['cate'] =  input('post.cate');
			//**验证数据
			$rules = [
				'title|文章标题' =>['require','max'=>200,'min'=>3,'unique'=>'news'],
				'author|作者'    =>['require','max'=>30,'min'=>3],
				'content|文章内容'    =>['require','min'=>3],
			];
			$message = $this->validate($data,$rules);
			if($message!==true) {
				$this->error($message);die;
			}
			
		    //上传文件移动到框架应用根目录/public/uploads/news目录下
		    $info = $file->validate(['size'=>8388608,'ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'uploads'.DS.'news');
		    //判断上传图片是否成功！
		    if( $info!== false ){  
				//调用image类静态方法打开图片
				$big_logo_path = './uploads/news/'.$info->getSaveName(); //上传原图的存储路径
		    	$image = \think\Image::open($big_logo_path);
		    	//缩略图片路径
		    	$thumb_logo_path = './thumb/news/'.$info->getFileName();
		    	$res = $image->thumb(600,350,\think\Image::THUMB_CENTER)->save($thumb_logo_path );

		    	if($res!==false) {
		    		//图片缩略成功，将缩略图路径存放到数据库中
					$data['image'] = '/thumb/news/'.$info->getFileName();
				    $status = $newsModel->save($data);
				    if($status !== false) {
				    	//********删原图*********
				    	//在一个执行周期内删不了原图，则将原图路径保存到session中，跳转到显示列表页中再删除原图
					    $big_logo_path = str_replace('/','\\',$big_logo_path);
					    Session::set('big_logo_path',$big_logo_path);

				    	$this->success('成功添加一条资讯！',url('admin/news/index'));die;
				    }else{
				    	$this->error('添加失败！');die;
				    }	
		    		
		    	}else{
		    		return $this->error('图片上传失败！'.$res->getError());die;         
		    	}
		    }else{        
		    	// 上传失败获取错误信息16.
		    	return $this->error('图片上传失败！'.$file->getError());die;         
		    } 
		}
		return $this->fetch();
	}


	//文章修改
	public function edit(Request $request) {
		//判断是否有get请求
		if($request->isGet()) {
			$id   = input('id');
			//数据库提取当前资讯信息
			$info     = NewsModel::get($id);
			$new_data = $info->toArray();
			return $this->fetch('edit',['new_data'=>$new_data]);
			
		}elseif($request->isPost()) {
			//判断是否有文件上传更改
			$file = request()->file('image');
			//分两种情况，有文件上传的数据更新和没有文件上传的数据处理
			//有文件上传的数据处理
			if(!empty($file)) {
				//数据过滤
				$data['id']      = input('post.id');
				$data['title']   = addslashes(strip_tags(trim(input('post.title'))));
				$data['author']  = addslashes(strip_tags(trim(input('post.author'))));
				$data['content'] = htmlspecialchars(input('post.content'));
				$data['is_valid']    =  input('post.is_valid');
				$data['is_hot']    =  input('post.is_hot');
				$data['add_time']    =  input('post.add_time');
				$data['cate']    =  input('post.cate');
				$data['modify_time'] =  date('Y-m-d H:i:s',time());					
				//**验证数据
				$rules = [
					'title|文章标题' =>['require','max'=>200,'min'=>3],
					'author|作者'    =>['require','max'=>30,'min'=>3],
					'content|文章内容'    =>['require','min'=>3],
				];
				$message = $this->validate($data,$rules);
				if($message!==true) {
					$this->error($message);die;
				}

				//存在文件上传,保存更新图片，更新地址，删除之前缩略图
				//移动图片到指定的项目文件夹下
				$info = $file->validate(['size'=>8388608,'ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'uploads'.DS.'news');
				//判断图片上传验证是否通过
				if($info!==false) {
					//验证通过，对图片进行缩略图处理
					$big_logo_path = './uploads/news/'.$info->getSaveName();//原图的路径
			    	$image = \think\Image::open($big_logo_path);//打开图片
			    	$thumb_logo_path = './thumb/news/'.$info->getFileName(); //定义缩略图的路径
			    	//缩略图片
			    	$res = $image->thumb(600,350,\think\Image::THUMB_CENTER)->save($thumb_logo_path );
			    	//判断缩略图片是否成功
			    	if($res!==false) {			    		
			    		//将缩略图路径更新到数据库中
						$data['image'] = '/thumb/news/'.$info->getFileName();
						$id = input('post.id');

						//在更新数据之前，先得到旧缩略图片的路径,
						$old_info_obj = NewsModel::get($id);
						$old_info = $old_info_obj->toArray();
						$old_thumb_image_path = '.'.$old_info['image']; //获取旧缩略图片的路径

						//将更改的数据新增到数据库，同时删除之前上传的缩略图片
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

					    	$this->success('更改资讯成功！',url('admin/news/index'));die;
					    }else{
					    	$this->error('更改资讯失败!');die;
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
			$data['id']      = input('post.id');
			$data['title']   = addslashes(strip_tags(trim(input('post.title'))));
			$data['author']  = addslashes(strip_tags(trim(input('post.author'))));
			$data['content'] = htmlspecialchars(input('post.content'));
			$data['is_valid']    = input('post.is_valid');
			$data['is_hot']      = input('post.is_hot');
			$data['add_time']    = input('post.add_time');				
			$data['cate']        = input('post.cate');				
			$data['modify_time'] = date('Y-m-d H:i:s',time());				
			//**验证数据
			$rules = [
				'title|文章标题' =>['require','max'=>200,'min'=>3],
				'author|作者'    =>['require','max'=>30,'min'=>3],
				'content|文章内容'    =>['require','min'=>3],
			];
			$message = $this->validate($data,$rules);
			if($message!==true) {
				$this->error($message);die;
			}
			if( NewsModel::update($data) !== false ) {
		    	$this->success('更改资讯成功！',url('admin/news/index'));die;
			}else{
				$this->error('更改资讯失败！' );die;
			}	
		}
	} 

	//文章删除（单条）
	public function del(Request $request) {
		//接收get传递过来的id
		if($request->isGet()) {
			$current_new_id   = input('id');
			$info 			  = NewsModel::get($current_new_id); //根据id获取当前资讯信息
			$thumb_image_path = '.'.$info->image; //获取存储图片的路径

			$status = NewsModel::destroy($current_new_id); //删除此文章的数据库信息
			if($status!==false) {
				//删除信息的同时删除图片
				if( file_exists($thumb_image_path )&&chmod($thumb_image_path,0777)  ) {
					unlink( $thumb_image_path );
				}

			    $this->success('资讯删除成功！','admin/news/index',1);die;
			}else{
				$this->error('删除失败!');die;
			}
		}
	}


	//是否有效，切换
	public function valid(Request $request) {
		$newsModel      = new NewsModel();
		$current_new_id = input('id');
		$new_data_obj   = $newsModel->where('id',$current_new_id)->field('is_valid')->select();
		$new_data = $new_data_obj->toArray();
		if($new_data[0]['is_valid']=='Y') {
			$newsModel->where('id',$current_new_id)->update(['is_valid'=>'N']);
		}else{
			$newsModel->where('id',$current_new_id)->update(['is_valid'=>'Y']);	
		}
		$this->redirect('/admin/news/index');die;
	}

	//是否设置热门，切换
	public function hot(Request $request) {
		$newsModel      = new NewsModel();
		$current_new_id = input('id');
		$new_data_obj   = $newsModel->where('id',$current_new_id)->field('is_hot')->select();
		$new_data = $new_data_obj->toArray();
		if($new_data[0]['is_hot']=='Y') {
			$newsModel->where('id',$current_new_id)->update(['is_hot'=>'N']);
		}else{
			$newsModel->where('id',$current_new_id)->update(['is_hot'=>'Y']);	
		}
		$this->redirect('/admin/news/index');die;
	}

		
}
