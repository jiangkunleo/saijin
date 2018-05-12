<?php
namespace app\admin\controller;
use think\Controller;
use think\Session;
use think\Request;
use app\admin\model\AdminGroupPrivilege as AdminGroupPrivilegeModel;

/**
 *  后台总控制器
 */
class Common extends Controller{
    
    //初始化登录，检测登录是否合法  以及是否拥有权限
    public function __construct(){
      parent::__construct();
      $this->chLogin();//检测是否登录   
      $this->chPrivilege();//检测权限
    }

    //检测登录是否合法
    protected function chLogin( ) {
      $request = Request::instance();
      // 验证用户的登录状态
      $action  = array('index/login');
      $res     = !in_array( strtolower( $request->controller().'/'.$request->action() ), $action );

      if( $res  && Session::get('account_statu') != "Y" ){
        $this->error( '您尚未登录！',url('admin/index/login') );die;
      }       
    }

    //检测当前用户的权限
    protected function chPrivilege() {
      // 先获取当前访问的控制器 
      $request = Request::instance();
      $privilege_contr = $request->controller();

      if($privilege_contr === 'Loan') {
          $privilege_key = 1;  //贷款信息管理
      }elseif($privilege_contr === 'Cateline'){
          $privilege_key = 1;  //贷款金额分类
      }elseif($privilege_contr === 'Cateconfine'){
          $privilege_key = 1;  //贷款期限分类
      }elseif($privilege_contr === 'Category'){
          $privilege_key = 1;  //贷款类型分类
      }elseif($privilege_contr === 'Catework'){
          $privilege_key = 1;  //职业身份分类
      }elseif($privilege_contr === 'News'){
          $privilege_key = 2;  //资讯管理
      }elseif($privilege_contr === 'Banner'){
          $privilege_key = 3;  //轮播图管理
      }elseif($privilege_contr === 'Consultant'){
          $privilege_key = 4;  //顾问信息管理
      }elseif($privilege_contr === 'Apply'){
          $privilege_key = 5;  //贷款申请管理
      }elseif($privilege_contr === 'Answer'){
          $privilege_key = 6;  //客户提问管理
      }elseif($privilege_contr === 'About'){
          $privilege_key = 7;   //关于我们管理
      }elseif($privilege_contr === 'Comimg'){
          $privilege_key = 8;   //公司图片管理-展示关于我们的图片
      }elseif($privilege_contr === 'Frilink'){
          $privilege_key = 9;   //友情链接管理
      }elseif($privilege_contr === 'Modify'){
          $privilege_key = 10;   //密码修改管理
      }elseif($privilege_contr === 'User'){
          $privilege_key = 11;   //用户管理
      }elseif($privilege_contr === 'Privilege'){
          $privilege_key = 12;   //权限管理
      }elseif($privilege_contr === 'Group'){
          $privilege_key = 12;   //分组管理
      }elseif($privilege_contr === 'GroupPrivilege'){
          $privilege_key = 12;   //权限分配组管理
      }
      // 下面数组的方法都不应该验证权限
      $allow = array(
        'index',  // 后台首页控制器
      );
      // 判断当前权限 $auth_key 是否在当前管理员所拥有的权限范围内
      $res = !in_array( strtolower( $request->controller() ), $allow );
      //根据存于session中的用户分组code，查询到用户的权限code
      $group_code      = Session::get('group_code');
      $privilege_codes = AdminGroupPrivilegeModel::where('group_code',$group_code)->column('privilege_code');
      if($res && in_array($privilege_key,$privilege_codes) === false ){
        $this->error('您访问的地址错误！');die;
      }
    }
}