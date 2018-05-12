<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use think\Request;
use think\Session;
use app\admin\Model\User as UserModel;
use app\admin\Model\AdminGroup as AdminGroupModel;
use app\admin\model\AdminGroupPrivilege as AdminGroupPrivilegeModel;
/**
 *  后台首页
 */
class Index extends Common{
	//后台首页框架	
    public function index() {
      //输出框架
      return $this->fetch(); 
    }

    // 后台首页[顶部导航]
    public function top() {
 
      $account_name = Session::get('account_name');
      return $this->fetch('top',['account_name'=>$account_name]); 
    }

    // 后台首页[左侧菜单栏]
    public function left() {
      $group_code = Session::get('group_code');
      $privilege_codes = AdminGroupPrivilegeModel::where('group_code',$group_code)->column('privilege_code');
      $this->assign('privilege_codes',$privilege_codes);
      //输出后台左侧菜单栏
      return $this->fetch(); 
    }

    // 后台首页[主要内容窗口]
    public function main() {
      //服务器类型
      $server_type =  $_SERVER['SERVER_SOFTWARE'];
      $this->assign('server_type',$server_type);
      //php 版本
      $php_edition = PHP_VERSION;
      $this->assign('php_edition',$php_edition);
      //客户端ip
      $client_ip = $_SERVER['REMOTE_ADDR'];
      $this->assign('client_ip',$client_ip);
      
      //输出后台内容窗口
      return $this->fetch(); 
    }

    //后台登录页
    public function login(Request $request) {   
      $db = new UserModel;

      //判断是否收到post登录信息
      if($request->isPost()) {
        //首先检测验证码是否正确
        $verify = input('verify');
        if(!captcha_check($verify)) {
          $this->error('验证码错误，请重新输入！');
        }
        
        //接收登录名和密码并核对
        $data = [];
        $data['account_name'] = addslashes(strip_tags(trim(input('post.account_name'))));
        $data['credential'] = trim(input('post.credential'));
        //验证数据，
        $rules = [
          'account_name|用户名称'=> ['require','min'=>3,'max'=>32],
          'credential|密码'=> ['require','min'=>6,'max'=>10,'alphaDash'],
        ];
        $message = $this->validate($data,$rules);
        if($message!==true) {
          $this->error($message);die;
        }

        $data['credential']   = md5($data['credential']);
        //查找出对应名称的信息
        $res = $db->where(['account_name'=>$data['account_name']])->find();
        if($data['credential']==$res['credential']) {
          //根据当前用户id获取它所属分组的id
          $user_id = $res['account_id'];
          $group_code = AdminGroupModel::where('admin_id',$user_id)->value('group_code');

          //开启session,存储用户名和登录状态、id
          Session::set('account_id',$res['account_id']);
          Session::set('account_statu',"Y");
          Session::set('account_name',$res['account_name']); //管理员账号
          Session::set('is_login',1); //登录状态
          Session::set('group_code', $group_code); //分组id
          $this->success('欢迎登录赛金金服后台管理系统！','admin/index/index',3);die;
        }else{
          $this->error('登录失败，请重新登录！');die;
        }
      }
      return $this->fetch();
    }

    //退出登录
    public function loginOut() {
      //清除用户Session信息
      Session::clear();
      //跳回登录页面
      $this->redirect('admin/index/login');die;
    }

    //验证码类
    public function code() {
      
    }

}
