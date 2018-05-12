<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use think\Request;
use think\Db;
use app\admin\Model\User as UserModel;
use app\admin\Model\CodeGroup as CodeGroupModel;
use app\admin\Model\AdminGroup as AdminGroupModel;
/**
 *  用户管理控制器
 */
class User extends Common{
   //用户列表页  
   public function index() {
      //引入模型
       $userModel = new UserModel();
      //分页
      $list = Db::name('user_account')
      ->alias('a')
      ->field('a.account_id ,a.account_name,a.account_code,a.add_time,a.modify_time,a.is_valid,c.name')
      ->join('admin_group b ',' a.account_id = b.admin_id','LEFT')
      ->join('code_group c ',' b.group_code = c.code','LEFT')
      ->order('a.account_id','asc')->paginate(10);
      $count = $userModel->count(); //总记录数
      $page  = $list->render();      //页码数据
      $currentPage = $list->currentPage(); //当前的页码
      return $this->fetch('index',['list'=>$list ,'page'=>$page,'count'=>$count,'currentPage'=>$currentPage]);
      //halt($list);
   }

	//添加用户	
   public function add(Request $request) {
      //引入模型
      $userModel = new UserModel();
      //判断是否有post数据添加过来
      if($request->isPost()) {
      	//$data = input('post.');
        $data['account_name'] = addslashes(strip_tags(trim(input('post.account_name'))));
        $data['credential']   = addslashes(strip_tags(trim(input('post.credential'))));
        $data['is_valid']     = input('post.is_valid');
        //验证数据，
        $rules = [
          'account_name|用户名称'=> ['require','min'=>3,'max'=>32,'unique'=>'user_account'],
          'credential|密码'=> ['require','min'=>6,'max'=>10,'alphaDash'],
        ];
        $message = $this->validate($data,$rules);
        if($message!==true) {
          $this->error($message);die;
        }
        //补充、整理数据
        $data['credential']  = md5(input('credential'));
        $data['add_time']    = date('Y-m-d H:i:s',time());
        $data['account_code']= 1;

      	//数据入库
      	$status = $userModel->save($data);
      	if($status !== false) {
      		//数据新增成功
      		$this->success('添加用户成功！','admin/user/index',3);die;
      	}else{
      		//添加数据失败
      		$this->error('添加用户失败！');die;
      	}
      }

      return $this->fetch(); 
    }


	 //编辑用户	
    public function edit(Request $request) {
      //引入模型
      $userModel = new UserModel();
      if($request->isGet()) {
      	//接收id并查询出此条数据
      	$account_id = $request->param()['account_id'];
      	$info = $userModel->find($account_id);
      	$this->assign('arr',$info);	
        return $this->fetch(); 

      }elseif($request->isPost()) {
        //获取数据
        $info['account_id']   = input('post.account_id');
        $info['account_name'] = addslashes(strip_tags(trim(input('post.account_name'))));
        $info['credential']   = addslashes(strip_tags(trim(input('post.credential'))));
        $info['is_valid']     = input('post.is_valid');
        $info['modify_time']  = date('Y-m-d H:i:s',time());
        //验证数据
        $rules = [
          'account_name|用户名称'=> ['require','min'=>3,'max'=>32,'unique'=>'user_account'],
          'credential|密码'=> ['require','min'=>6,'max'=>10,'alphaDash'],
        ];
        $message = $this->validate($info,$rules);
        if($message!==true) {
          $this->error($message);die;
        }
        //密码加密
        $info['credential'] = md5($info['credential']);
      	$status = $userModel->update($info);
      	//判断用户修改是否成功！
      	if($status!==false) {
      		//修改成功！
      		$this->success('用户信息修改成功！','admin/user/index',3);die;
      	}else{
      		//修改失败！
      		$this->error('修改失败！');die;
      	}
      }
      
    }

	 //删除用户	
    public function del(Request $request) {
        //接收id，并将对应的数据删除
        $account_id =input('account_id');
        //删除数据库数据
        $status = UserModel::destroy($account_id);
        if($status!==false) {
        	$this->success('删除成功！','admin/user/index',3);die;
        }else{
        	$this->error('删除失败。');die;
        }
    }

	 //批量删除用户	
    public function deList() {
      
      echo "批量删除用户" ;
    }

   //用户设置分组
   public function set(Request $request) {
      if($request->isGet()) {
         $user_id       = input('account_id');
         $userModel     = new UserModel();
         $user_data_obj = $userModel->find($user_id);
         $user_data     = $user_data_obj->toArray();
           
         $codeGroupModel      = new CodeGroupModel();
         $code_group_data_obj = $codeGroupModel->where('is_valid','Y')->select();
         $code_group_data     = $code_group_data_obj->toArray();
         $this->assign('code_group_data',$code_group_data);
         $this->assign('user_data',$user_data);
         return $this->fetch(); 

      }elseif($request->isPost()) {
         $adminGroupModel = new AdminGroupModel();
         $admin_id        = input('account_id');
         //插入数据前先判断当前用户id是否有分组
         $admin_group_data_obj = $adminGroupModel->where('admin_id',$admin_id)->find();
         if(empty($admin_group_data_obj)) {
            //为空则新增数据
            $data['admin_id']   = $admin_id;
            $data['group_code'] = input('group_code');
            $data['add_time']   = date('Y-m-d H:i:s',time());
            $data['is_valid']   = 'Y';
            $status = $adminGroupModel->save($data);
            if($status!==false) {
               $this->success('用户分组成功！',url('admin/user/index'),3);die;
            }else{
               $this->error('用户分组失败！');die;
            }
         }else{
            //不为空则更新数据
            //$data['admin_id'] = $admin_id;
            $data['group_code']  = input('group_code');
            $data['modify_time'] = date('Y-m-d H:i:s',time());
            $data['is_valid']    = 'Y';
            $status = $adminGroupModel->where('admin_id',$admin_id)->update($data);
            if($status!==false) {
               $this->success('用户分组更改成功！',url('admin/user/index'),3);die;
            }else{
               $this->error('用户分组更改失败！');die;
            }
         }   
      }
   }



}
