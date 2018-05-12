<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use think\Request;
use think\Session;
use think\Db;
use app\admin\model\Loan as LoanModel;
use app\admin\model\Category as CategoryModel;
use app\admin\model\Catework as CateworkModel;
use app\admin\model\Cateconfine as CateconfineModel;
use app\admin\model\Cateline as CatelineModel;

/**
 *  贷款信息控制器
 */
class Loan extends Common{
	//贷款信息列表
	public function index() {
		//删除原图（新上传的原图有则删，没有则跳过）
		$big_logo_path = @Session::get('big_logo_path');
	    if(file_exists($big_logo_path)&&chmod($big_logo_path,0777)) {	
	     	unlink($big_logo_path);
	     	Session::delete('big_logo_path');
	     	unset($big_logo_path);
	    }

		$loanModel   = new LoanModel();
		//分页
		$list  = Db::table('loan')
					->alias('a')
					->field('a.id,a.bank,a.desc,a.image,a.interest,a.get_time,a.return_type,a.conditions,a.req_file,a.add_time,a.modify_time,a.is_valid,b.name as cate_name,c.name as cate_son_name,d.name as work_type_name,e.line_code as lines_min_name,f.line_code as lines_max_name,g.months as confine_min_name,h.months as confine_max_name')
					->join('code_category b','a.cate = b.id','left')
					->join('code_category c','a.cate_son = c.id','left')
					->join('code_work_type d','a.work_type = d.code','left')
					->join('code_line e','a.lines_min = e.id','left')
					->join('code_line f','a.lines_max = f.id','left')
					->join('code_confine g','a.confine_min = g.id','left')
					->join('code_confine h','a.confine_max = h.id','left')
					->order('a.id','asc')
					->paginate(12); //分页，每页12条数据
					//halt($list);
		$count = $list->total(); //总记录数
		$page  = $list->render();      //页码数据
		$currentPage = $list->currentPage(); //当前的页码
		return $this->fetch('index',['list'=>$list ,'page'=>$page,'count'=>$count,'currentPage'=>$currentPage]);
	}

	//添加贷款信息
	public function add(Request $request) {
		//贷款分类信息 主类 子类
		$category_model = new CategoryModel();
		$category_data_obj = $category_model->field(['id,name,pid'])->where(['is_valid'=>'Y'])->select();
		$category_data = $category_data_obj->toArray();
		//进行分级整理
		$category_data = comb($category_data);
		$this->assign('category_data',$category_data);

		//职业身份信息
		$catework_model = new CateworkModel();
		$catework_data_obj = $catework_model->field('code,name')->where(['is_valid'=>'Y'])->select();
		$catework_data = $catework_data_obj->toArray();
		$this->assign('catework_data',$catework_data);

		//贷款金额信息
		$cateconfine_model = new CateconfineModel();
		$cateconfine_data_obj = $cateconfine_model->field('id,months')->where(['is_valid'=>'Y'])->select();
		$cateconfine_data = $cateconfine_data_obj->toArray();
		$this->assign('cateconfine_data',$cateconfine_data);

		//贷款期限信息
		$cateline_model = new CatelineModel();
		$cateline_data_obj = $cateline_model->field('id,line_code')->where(['is_valid'=>'Y'])->select();
		$cataline_data = $cateline_data_obj->toArray();
		$this->assign('cataline_data',$cataline_data);

		//当添加页post提交的时候
		if($request->isPost()){
			//先检测是否有文件上传！没有则返回要求上传文件提示！
		    $file = request()->file('image');
		    //判断是否有文件上传，没有则给出提示
		    if (empty($file)) { 
		      $this->error('请选择上传文件'); die;
		    }

			//接收并过滤数据
			$data['bank'] = addslashes(strip_tags(trim(input('post.bank'))));
			$data['desc'] = addslashes(strip_tags(trim(input('post.desc'))));
			$data['cate'] = input('post.cate');
			$data['cate_son'] = input('post.cate_son');
			$data['work_type'] = input('post.work_type');
			$data['lines_min'] = input('post.lines_min');
			$data['lines_max'] = input('post.lines_max');
			$data['interest'] = addslashes(strip_tags(trim(input('post.interest'))));
			$data['get_time'] = addslashes(strip_tags(trim(input('post.get_time'))));
			$data['confine_min'] = input('post.confine_min');
			$data['confine_max'] = input('post.confine_max');
			$data['return_type'] = addslashes(strip_tags(trim(input('post.return_type'))));
			$data['conditions'] = htmlspecialchars(input('post.conditions'));
			$data['req_file'] = htmlspecialchars(input('post.req_file'));
			$data['add_time'] = date('Y-m-d H:i:s',time());
			$data['is_valid'] = input('post.is_valid');
			//halt($data);
			//**验证数据
			$rules = [
				'bank|放贷行' =>['require','max'=>90,'min'=>3],
				'desc|简略描述' =>['require','max'=>90,'min'=>3],
				'interest|利息' =>['require','max'=>60,'min'=>3],
				'get_time|放款时间' =>['require','max'=>60,'min'=>3],
				'return_type|还款方式' =>['require','max'=>60,'min'=>3],
				'conditions|申请条件' =>['require','min'=>3],
				'req_file|所需材料' =>['require','min'=>3],
			];
			$message = $this->validate($data,$rules);
			if($message!==true) {
				$this->error($message);die;
			}
		    //上传文件移动到框架应用根目录/public/uploads/news目录下
		    $info = $file->validate(['size'=>8388608,'ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'uploads'.DS.'loan');
		    //判断上传图片是否成功！
		    if( $info!== false ){
				//调用image类静态方法打开图片
				$big_logo_path = './uploads/loan/'.$info->getSaveName(); //上传原图的存储路径
		    	$image = \think\Image::open($big_logo_path);
		    	//缩略图片路径
		    	$thumb_logo_path = './thumb/loan/'.$info->getFileName();
		    	$res = $image->thumb(50,50,\think\Image::THUMB_CENTER)->save($thumb_logo_path );
		    	if($res!==false) {
		    		//图片缩略成功，将缩略图路径存放到数据库中
					$data['image'] = '/thumb/loan/'.$info->getFileName();
					$loanModel = new LoanModel();
				    $status = $loanModel->save($data);
				    if($status !== false) {
				    	//********删原图*********
				    	//在一个执行周期内删不了原图，则将原图路径保存到session中，跳转到显示列表页中再删除原图
					    $big_logo_path = str_replace('/','\\',$big_logo_path);
					    Session::set('big_logo_path',$big_logo_path);

				    	$this->success('成功添加一条贷款信息！',url('admin/loan/index'));die;
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



	//修改贷款信息
	public function edit(Request $request) {
		//判断是否有get请求
		if($request->isGet()) {
		//贷款分类信息 主类 子类
		$category_model = new CategoryModel();
		$category_data_obj = $category_model->field(['id,name,pid'])->where(['is_valid'=>'Y'])->select();
		$category_data = $category_data_obj->toArray();
		//进行分级整理
		$category_data = comb($category_data);
		$this->assign('category_data',$category_data);

		//职业身份信息
		$catework_model = new CateworkModel();
		$catework_data_obj = $catework_model->field('code,name')->where(['is_valid'=>'Y'])->select();
		$catework_data = $catework_data_obj->toArray();
		$this->assign('catework_data',$catework_data);

		//贷款金额信息
		$cateconfine_model = new CateconfineModel();
		$cateconfine_data_obj = $cateconfine_model->field('id,months')->where(['is_valid'=>'Y'])->select();
		$cateconfine_data = $cateconfine_data_obj->toArray();
		$this->assign('cateconfine_data',$cateconfine_data);

		//贷款期限信息
		$cateline_model = new CatelineModel();
		$cateline_data_obj = $cateline_model->field('id,line_code')->where(['is_valid'=>'Y'])->select();
		$cataline_data = $cateline_data_obj->toArray();
		$this->assign('cataline_data',$cataline_data);



			$id   = input('id');
			//数据库提取当前新闻信息
			$info      = LoanModel::get($id);
			$loan_data = $info->toArray();
			return $this->fetch('edit',['loan_data'=>$loan_data]);
		}elseif($request->isPost()) {
			//判断是否有文件上传更改
			$file = request()->file('image');
			//分两种情况，有文件上传的数据更新和没有文件上传的数据处理
			//有文件上传的数据处理
			if(!empty($file)) {
				//接收并过滤数据
				$data['id']   = input('post.id');
				$data['bank'] = addslashes(strip_tags(trim(input('post.bank'))));
				$data['desc'] = addslashes(strip_tags(trim(input('post.desc'))));
				$data['cate'] = input('post.cate');
				$data['cate_son'] = input('post.cate_son');
				$data['work_type'] = input('post.work_type');
				$data['lines_min'] = input('post.lines_min');
				$data['lines_max'] = input('post.lines_max');
				$data['interest'] = addslashes(strip_tags(trim(input('post.interest'))));
				$data['get_time'] = addslashes(strip_tags(trim(input('post.get_time'))));
				$data['confine_min'] = input('post.confine_min');
				$data['confine_max'] = input('post.confine_max');
				$data['return_type'] = addslashes(strip_tags(trim(input('post.return_type'))));
				$data['conditions'] = htmlspecialchars(input('post.conditions'));
				$data['req_file'] = htmlspecialchars(input('post.req_file'));
				$data['add_time'] = input('post.add_time');
				$data['modify_time'] = date('Y-m-d H:i:s',time());
				$data['is_valid'] = input('post.is_valid');
				//halt($data);
				//**验证数据
				$rules = [
					'bank|放贷行' =>['require','max'=>90,'min'=>3],
					'desc|简略描述' =>['require','max'=>90,'min'=>3],
					'interest|利息' =>['require','max'=>60,'min'=>3],
					'get_time|放款时间' =>['require','max'=>60,'min'=>3],
					'return_type|还款方式' =>['require','max'=>60,'min'=>3],
					'conditions|申请条件' =>['require','min'=>3],
					'req_file|所需材料' =>['require','min'=>3],
				];
				$message = $this->validate($data,$rules);
				if($message!==true) {
					$this->error($message);die;
				}
				//存在文件上传,保存更新图片，更新地址，删除之前缩略图
				//移动图片到指定的项目文件夹下
				$info = $file->validate(['size'=>8388608,'ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'uploads'.DS.'loan');
				//判断图片上传验证是否通过
				if($info!==false) {
					//验证通过，对图片进行缩略图处理
					$big_logo_path = './uploads/loan/'.$info->getSaveName();//原图的路径
			    	$image = \think\Image::open($big_logo_path);//打开图片
			    	$thumb_logo_path = './thumb/loan/'.$info->getFileName(); //定义缩略图的路径
			    	//缩略图片
			    	$res = $image->thumb(50,50,\think\Image::THUMB_CENTER)->save($thumb_logo_path );
			    	//判断缩略图片是否成功
			    	if($res!==false) {
			    		//将缩略图路径更新到数据库中
						$data['image'] = '/thumb/loan/'.$info->getFileName();
						$id = input('post.id');

						//在更新数据之前，先得到旧缩略图片的路径,
						$old_info_obj = LoanModel::get($id);
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

					    	$this->success('更改贷款信息成功！',url('admin/loan/index'));die;
					    }else{
					    	$this->error('更改贷款信息失败!');die;
					    }	
			    	}else{
			    		$this->error('图片缩小失败'.$info->getError());die;
			    	}
				}else{
					//更改的图片上传验证没通过
					$this->error('图片重新上传失败'.$info->getError());die;
				}
			}
			//如果没有更改上传文件，则不用对上传的数据进行处理，直接更新表单其他数据
			//过滤数据
			$data['id']   = input('post.id');
			$data['bank'] = addslashes(strip_tags(trim(input('post.bank'))));
			$data['desc'] = addslashes(strip_tags(trim(input('post.desc'))));
			$data['cate'] = input('post.cate');
			$data['cate_son'] = input('post.cate_son');
			$data['work_type'] = input('post.work_type');
			$data['lines_min'] = input('post.lines_min');
			$data['lines_max'] = input('post.lines_max');
			$data['interest'] = addslashes(strip_tags(trim(input('post.interest'))));
			$data['get_time'] = addslashes(strip_tags(trim(input('post.get_time'))));
			$data['confine_min'] = input('post.confine_min');
			$data['confine_max'] = input('post.confine_max');
			$data['return_type'] = addslashes(strip_tags(trim(input('post.return_type'))));
			$data['conditions'] = htmlspecialchars(input('post.conditions'));
			$data['req_file'] = htmlspecialchars(input('post.req_file'));
			$data['add_time'] = input('post.add_time');
			$data['modify_time'] = date('Y-m-d H:i:s',time());
			$data['is_valid'] = input('post.is_valid');
			//halt($data);
			//**验证数据
			$rules = [
				'bank|放贷行' =>['require','max'=>90,'min'=>3],
				'desc|简略描述' =>['require','max'=>90,'min'=>3],
				'interest|利息' =>['require','max'=>60,'min'=>3],
				'get_time|放款时间' =>['require','max'=>60,'min'=>3],
				'return_type|还款方式' =>['require','max'=>60,'min'=>3],
				'conditions|申请条件' =>['require','min'=>3],
				'req_file|所需材料' =>['require','min'=>3],
			];
			$message = $this->validate($data,$rules);
			if($message!==true) {
				$this->error($message);die;
			}
			if( LoanModel::update($data) !== false ) {
		    	$this->success('更改贷款信息成功！',url('admin/loan/index'));die;
			}else{
				$this->error('更改贷款信息失败！' );die;
			}	
		}
	}


	//删除贷款信息
	public function del(Request $request) {
		//接收get传递过来的id
		if($request->isGet()) {
			$current_loan_id   = input('id');
			$info 			  = LoanModel::get($current_loan_id); //根据id获取当前新闻信息
			$thumb_image_path = '.'.$info->image; //获取存储图片的路径

			$status = LoanModel::destroy($current_loan_id); //删除此文章的数据库信息
			if($status!==false) {
				//删除信息的同时删除图片
				if( file_exists($thumb_image_path )&&chmod($thumb_image_path,0777)  ) {
					unlink( $thumb_image_path );
				}

			    $this->success('贷款信息删除成功！','admin/loan/index',1);die;
			}else{
				$this->error('删除失败!');die;
			}
		}
	}


	//是否有效，切换
	public function valid(Request $request) {
		$loanModel      = new LoanModel();
		$current_loan_id = input('id');
		$loan_data_obj   = $loanModel->where('id',$current_loan_id)->field('is_valid')->select();
		$loan_data = $loan_data_obj->toArray();
		if($loan_data[0]['is_valid']=='Y') {
			$loanModel->where('id',$current_loan_id)->update(['is_valid'=>'N']);
		}else{
			$loanModel->where('id',$current_loan_id)->update(['is_valid'=>'Y']);	
		}
		$this->redirect('/admin/loan/index');die;
	}

}
