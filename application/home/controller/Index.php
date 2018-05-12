<?php
namespace app\home\controller;
use think\Controller;
use think\Request;
use think\Db;
use app\admin\model\Frilink as FrilinkModel;
use app\home\model\About as AboutModel;
use app\admin\model\Comimg as ComimgModel;
use app\admin\model\Banner as BannerModel;
use app\admin\model\Consultant as ConsultantModel;
use app\home\model\Apply as ApplyModel;
use app\admin\model\Loan as LoanModel;
use app\admin\model\Answer as AnswerModel;
use app\home\model\News as NewsModel;
use app\admin\model\Cateline as CatelineModel;
use app\admin\model\Cateconfine as CateconfineModel;
use app\admin\model\Catework as CateworkModel;
use app\admin\model\Category as CategoryModel;

/**
 *  前台首页
 */
class Index extends Controller{
	//展示首页
  public function index() {
    $this->assign('title','首页'); //首页标题
    $some = $this->some();
    //halt($some);
    $this->assign('some',$some);  //公共信息

        //获取所有轮播图信息
        $bannerModel = new BannerModel();
        $banner_data = $bannerModel->where(['is_valid'=>'Y','type'=>1])->select();
        $this->assign('banner_data',$banner_data);

        //优秀顾问信息
        $consultantModel = new ConsultantModel();
        $consultant_data_obj = $consultantModel->where('is_valid','Y')->limit(3)->select();
        $consultant_data = $consultant_data_obj->toArray();
      $this->assign('consultant_data',$consultant_data);

      //主分类信息
      $main_cate = Db::table('code_category')->field('id,pid,name')->where(['is_valid'=>'Y','pid'=>0])->order('id','asc')->select();
      $this->assign('main_cate',$main_cate);

      //子分类信息
      $son_cate = Db::table('loan')
                    ->alias('a')
                    ->field('c.name as cate_son_name,c.id as cate_son_id,c.pid as cate_son_pid')
                    ->join('code_category b','a.cate = b.id and b.pid = 0','left')
                    ->join('code_category c','a.cate_son = c.id ','left')
                    ->order('a.id','asc')
                    ->group('a.cate_son')
                    ->select();
      $this->assign('son_cate',$son_cate);
      //****贷款信息
      $loan_list = Db::table('loan')
                    ->alias('a')
                    ->field('a.id,a.bank,a.desc,a.cate,a.cate_son,a.image,a.lines_min,a.lines_max,a.confine_min,a.confine_max,b.name as cate_name,c.name as cate_son_name,b.id as cate_id,b.pid as cate_pid,c.id as cate_son_id,c.pid as cate_son_pid,d.name as work_type_name,e.line_code as lines_min_name,f.line_code as lines_max_name,g.months as confine_min_name,h.months as confine_max_name')
                    ->where(['a.is_valid'=>'Y','b.is_valid'=>'Y','c.is_valid'=>'Y','d.is_valid'=>'Y','e.is_valid'=>'Y','f.is_valid'=>'Y','g.is_valid'=>'Y','h.is_valid'=>'Y',])
                    ->join('code_category b','a.cate = b.id','left')
                    ->join('code_category c','a.cate_son = c.id','left')
                    ->join('code_work_type d','a.work_type = d.code','left')
                    ->join('code_line e','a.lines_min = e.id','left')
                    ->join('code_line f','a.lines_max = f.id','left')
                    ->join('code_confine g','a.confine_min = g.id','left')
                    ->join('code_confine h','a.confine_max = h.id','left')
                    ->order('a.id','asc')
                    ->select(); 
      $this->assign('loan_list',$loan_list);


      //新闻资讯
      $newsModel = new NewsModel();
      //新手贷款信息
      $zx1_data_obj = $newsModel->field('title,id')->where(['is_valid'=>'Y','cate'=>'1'])->limit(9)->select();
      $zx1_data = $zx1_data_obj->toArray();
      $this->assign('zx1_data',$zx1_data);
      //无抵押贷款信息
      $zx2_data_obj = $newsModel->field('title,id')->where(['is_valid'=>'Y','cate'=>'2'])->limit(9)->select();
      $zx2_data = $zx2_data_obj->toArray();
      $this->assign('zx2_data',$zx2_data);
      //经营贷款信息
      $zx3_data_obj = $newsModel->field('title,id')->where(['is_valid'=>'Y','cate'=>'3'])->limit(9)->select();
      $zx3_data = $zx3_data_obj->toArray();
      $this->assign('zx3_data',$zx3_data);
      //政策法规信息
      $zx4_data_obj = $newsModel->field('title,id')->where(['is_valid'=>'Y','cate'=>'4'])->limit(9)->select();
      $zx4_data = $zx4_data_obj->toArray();
      $this->assign('zx4_data',$zx4_data);
      //个人贷款信息
      $zx5_data_obj = $newsModel->field('title,id')->where(['is_valid'=>'Y','cate'=>'5'])->limit(9)->select();
      $zx5_data = $zx5_data_obj->toArray();
      $this->assign('zx5_data',$zx5_data);
      //购车贷款信息
      $zx6_data_obj = $newsModel->field('title,id')->where(['is_valid'=>'Y','cate'=>'6'])->limit(9)->select();
      $zx6_data = $zx6_data_obj->toArray();
      $this->assign('zx6_data',$zx6_data);
      //房贷资讯信息
      $zx7_data_obj = $newsModel->field('title,id')->where(['is_valid'=>'Y','cate'=>'7'])->limit(9)->select();
      $zx7_data = $zx7_data_obj->toArray();
      $this->assign('zx7_data',$zx7_data);
      //行业动态信息
      $zx8_data_obj = $newsModel->field('title,id')->where(['is_valid'=>'Y','cate'=>'8'])->limit(9)->select();
      $zx8_data = $zx8_data_obj->toArray();
      $this->assign('zx8_data',$zx8_data);
      //信用卡资讯信息
      $zx9_data_obj = $newsModel->field('title,id')->where(['is_valid'=>'Y','cate'=>'9'])->limit(9)->select();
      $zx9_data = $zx9_data_obj->toArray();
      $this->assign('zx9_data',$zx9_data);

      //贷款提问信息
      $answer_data = Db::table('answer')->field('id,requ_desc')->where(['is_valid'=>'Y','is_hot'=>'Y'])->limit(7)->select();
      $this->assign('answer_data',$answer_data);

      //查询额度分类
      $lines_cate_data = Db::table('code_line')
                            ->field('id,line_code')
                            ->where(['is_valid'=>'Y'])
                            ->order('line_code','asc')
                            ->select();
      //查询职业身份分类
      $work_cate_data = Db::table('code_work_type')
                            ->field('code,name')
                            ->where(['is_valid'=>'Y'])
                            ->select();
      //查询贷款期限分类
      $confine_cate_data = Db::table('code_confine')
                            ->field('id,months')
                            ->where(['is_valid'=>'Y'])
                            ->order('months','asc')
                            ->select();
      $this->assign('lines_cate_data',$lines_cate_data);
      $this->assign('work_cate_data',$work_cate_data);
      $this->assign('confine_cate_data',$confine_cate_data);

      return $this->fetch();
  }



  //前台信用贷页面
  public function credit(Request $request) {
    
    $this->assign('title','信用贷'); //信用贷标题
    $some = $this->some();
    $this->assign('some',$some);  //公共信息

    //查询额度分类
    $lines_cate_data = Db::table('code_line')
                          ->field('id,line_code')
                          ->where(['is_valid'=>'Y'])
                          ->order('line_code','asc')
                          ->select();
    //查询职业身份分类
    $work_cate_data = Db::table('code_work_type')
                          ->field('code,name')
                          ->where(['is_valid'=>'Y'])
                          ->select();
    //查询贷款期限分类
    $confine_cate_data = Db::table('code_confine')
                          ->field('id,months')
                          ->where(['is_valid'=>'Y'])
                          ->order('months','asc')
                          ->select();
    //查询贷款类型分类
    $son_cate_data = Db::table('code_category')
                          ->alias('a')
                          ->field('b.id,b.pid,b.name')
                          ->join('code_category b','a.id = b.pid')
                          ->where(['a.name'=>'信用贷','a.is_valid'=>'Y','b.is_valid'=>'Y'])
                          ->order('b.id','asc')
                          ->select();
    $this->assign('lines_cate_data',$lines_cate_data);
    $this->assign('work_cate_data',$work_cate_data);
    $this->assign('confine_cate_data',$confine_cate_data);
    $this->assign('son_cate_data',$son_cate_data);

    //接收首页get传递的查询参数
    $get_work_type_id   = input('work_type_id');
    $get_work_type_name = input('work_type_name');
    $get_confine_id     = input('confine_id');
    $get_confine_name   = input('confine_name');
    $get_lines_id       = input('lines_id');
    $get_lines_name     = input('lines_name');
    
    $this->assign('get_work_type_id',$get_work_type_id);
    $this->assign('get_work_type_name',$get_work_type_name);
    $this->assign('get_confine_id',$get_confine_id);
    $this->assign('get_confine_name',$get_confine_name);
    $this->assign('get_lines_id',$get_lines_id);
    $this->assign('get_lines_name',$get_lines_name);



    //当页面有ajax传值过来，则返回对应条件的分页数据回去！
    if($request->isAjax()){
      //接收ajax数据
      $lines = input('post.lines');  //贷款金额
      $confine = input('post.confine'); //贷款期限
      $cate_son_data = input('post.cate_son'); //贷款类型
      $work_type_data = input('post.work_type'); //职业身份
      $page_data = input('post.page'); //要跳转的页码值
      
      //拼接查询条件，传过来条件就拼接上去，没有传过来的不拼接
      $where_str ="a.is_valid = 'Y'";
      if($lines!= null) {
        $where_str .="AND a.lines_min <= ".$lines." AND a.lines_max >= ".$lines;
      }
      if($confine!= null) {
        $where_str .=" AND a.confine_min <= ".$confine." AND a.confine_max >= ".$confine;
      }
      if($cate_son_data!=null) {
        $where_str .=" AND `cate_son` = '".$cate_son_data."'";
      }
      if($work_type_data!=null) {
        $where_str .=" AND `work_type` = '".$work_type_data."'";  
      }

      //页面第一次加载时 当页页码 和 要跳转的页码参数都为空 则默认为第一页
      //点击页码时 分点击数值 和点击 箭头 当前页值和要跳转的值都不为空
      if($page_data == null) {
        $page = 1;
      }else{
        $page = $page_data;
      }
      
      //查询数据
      $list_obj = Db::table('loan')
                    ->alias('a')
                    ->field('a.id,a.bank,a.desc,a.image,a.interest,a.get_time,b.line_code as line_min_name,c.line_code as line_max_name,d.months as confine_min_name,e.months as confine_max_name')
                    ->join('code_line b','a.lines_min = b.id','left')
                    ->join('code_line c','a.lines_max = c.id','left')
                    ->join('code_confine d','a.confine_min = d.id','left')
                    ->join('code_confine e','a.confine_max = e.id','left')
                    ->where($where_str)
                    ->paginate(5);
                    
      $pages = $list_obj->render();
      $list = $list_obj->toArray();
      $html = '';
      $html .='<div class="fl-title"><ul><li class="active"><a href="javascript:void(0);">默认排名</a></li><li><a href="javascript:void(0);">贷款额度</a></li><li><a href="javascript:void(0);">可贷周期</a></li><li><a href="javascript:void(0);">月利息</a></li><li><a href="javascript:void(0);">放款速度</a></li></ul></div><div class="loan-sort">';

      $i =1;
      foreach($list['data'] as $v) {
        $html .= '<div class="item-list"><div class="list-logo">';
        $html .= '<img src="'.$v['image'].'">';
        $html .= '</div><div class="list-desc"><div class="desc-top">'; 
        $html .= '<strong>'.$v['bank'].'</strong>';
        $html .= '<span>'.$v['desc'].'</span>';
        $html .= '</div><div class="desc-btm"><p>';   
        $html .= '<strong>'.$v['line_min_name'].'-'.$v['line_max_name'].'</strong>万元';        
        $html .= '<span>额度</span></p><p>';               
        $html .= '<strong>'.$v['confine_min_name'].'-'.$v['confine_max_name'].'</strong>个月';        
        $html .= '<span>期限</span></p><p>' ;             
        $html .= '<strong>'.$v['interest'].'</strong>';        
        $html .= '<span>月息</span></p><p>';       
        $html .= '<strong>'.$v['get_time'].'</strong>';     
        $html .= '<span>放款速度</span></p></div></div>';     
        $html .= '<a href="/id/'.$v['id'].'.html" target="view_frame" class="btn" id="credit-btn'.$i.'">详情</a>'; 
        $html .= '</div>'; 
        $i++;
      }

      $html .= '</div>';
      $html .= '<div class="pag" >'.$pages.'</div>';
      $data = array();

      $data['page']=$page;
      $data['html']= $html;
      echo json_encode($data);exit;
    }
		return $this->fetch();
	}



  //前台房抵贷页面
  public function house() {
    $this->assign('title','房抵贷'); 
    $some = $this->some();
    $this->assign('some',$some);  //公共信息

      //房抵贷信息
      $fangdidai_data = Db::table('loan')
                          ->alias('a')
                          ->field('a.id,a.bank,a.desc,a.image,b.id as fangdidai_id')
                          ->where(['a.is_valid'=>'Y','b.is_valid'=>'Y','b.name'=>'房抵贷'])
                          ->join('code_category b','a.cate_son = b.id','left')
                          ->limit(4)
                          ->order('a.add_time','desc')
                          ->select();
                          //halt($fangdidai_data);
      $this->assign('fangdidai_data',$fangdidai_data);
      //房抵贷id
      $fangdidai_id = $fangdidai_data[0]['fangdidai_id'];
      $this->assign('fangdidai_id',$fangdidai_id);


      //房贷咨询信息
      $newsModel = new NewsModel();
      $zx7_data_obj = $newsModel->field('title,id')->where(['cate'=>'7','is_valid'=>'Y'])->limit(9)->select();
      $zx7_data = $zx7_data_obj->toArray();
      $this->assign('zx7_data',$zx7_data);

    return $this->fetch();
  }



  //前台车抵贷页面
  public function car() {
    $this->assign('title','车抵贷'); 
    $some = $this->some();
    $this->assign('some',$some);  //公共信息

      //车抵贷信息
      $chedidai_data = Db::table('loan')
                          ->alias('a')
                          ->field('a.id,a.bank,a.desc,a.image,b.id as chedidai_id')
                          ->where(['a.is_valid'=>'Y','b.is_valid'=>'Y','b.name'=>'车抵贷'])
                          ->join('code_category b','a.cate_son = b.id','left')
                          ->limit(4)
                          ->order('a.add_time','desc')
                          ->select();
                          //halt($chedidai_data);
      $this->assign('chedidai_data',$chedidai_data);
      //房抵贷id
      $chedidai_id = $chedidai_data[0]['chedidai_id'];
      $this->assign('chedidai_id',$chedidai_id);

      //车贷资讯询信息
      $newsModel = new NewsModel();
      $zx6_data_obj = $newsModel->field('title,id')->where(['cate'=>'6','is_valid'=>'Y'])->limit(9)->select();
      $zx6_data = $zx6_data_obj->toArray();
      $this->assign('zx6_data',$zx6_data);

    return $this->fetch();
  }

  //更多房贷、车贷 贷款信息列表
  public function moreloan() {

    $cate_son = input('cate_son');
    $name_data = Db::table('code_category')->field('name')->where(['is_valid'=>'Y','id'=>$cate_son])->find();
    //halt($name_data);
    $name = $name_data['name'];

    $this->assign('title',$name.'信息列表'); 
    $some = $this->some();
    $this->assign('some',$some);  //公共信息

    $loanModel = new LoanModel();
    $list  = $loanModel->field('id,bank,desc,add_time')->where(['is_valid'=>'Y','cate_son'=>$cate_son])->order('id','asc')->paginate(8); //分页，每页5条数据
    $count = $loanModel->where(['is_valid'=>'Y','cate_son'=>$cate_son])->count(); //总记录数
    $page  = $list->render();       //页码数据
    $currentPage = $list->currentPage(); //当前的页码
    return $this->fetch('moreloan',['list'=>$list ,'page'=>$page,'count'=>$count,'currentPage'=>$currentPage]); 
  }

  //更多购车贷、购房贷 贷款资讯信息列表
  public function morenews() {

    //根据信息分类code 展示对应的分类标题
    $cate = input('cate');
    if($cate==='1') {
      $this->assign('title','新手贷款资讯列表'); 
    }elseif($cate==='2'){
      $this->assign('title','无抵押贷资讯列表'); 
    }elseif($cate==='3'){
      $this->assign('title','经营贷款资讯列表'); 
    }elseif($cate==='4'){
      $this->assign('title','政策法规资讯列表'); 
    }elseif($cate==='5'){
      $this->assign('title','个人贷款资讯列表'); 
    }elseif($cate==='6'){
      $this->assign('title','购车贷款资讯列表'); 
    }elseif($cate==='7'){
      $this->assign('title','房贷资讯列表'); 
    }elseif($cate==='8'){
      $this->assign('title','行业动态资讯列表'); 
    }elseif($cate==='9'){
      $this->assign('title','信用卡资讯列表'); 
    }

    $some = $this->some();
    $this->assign('some',$some);  //公共信息

    $newsModel = new NewsModel();
    $list  = $newsModel->field('id,title,add_time')->where(['is_valid'=>'Y','cate'=>$cate])->order('id','asc')->paginate(8); //分页，每页8条数
    $count = $newsModel->where(['is_valid'=>'Y','cate'=>$cate])->count(); //总记录数
    $page  = $list->render();      //页码数据
    $currentPage = $list->currentPage(); //当前的页码
    return $this->fetch('morenews',['list'=>$list ,'page'=>$page,'count'=>$count,'currentPage'=>$currentPage]); 
  }



  //前台贷款攻略页面
  public function strategy() {
    $this->assign('title','贷款攻略'); 
    $some = $this->some();
    $this->assign('some',$some);  //公共信息

    $newsModel = new NewsModel();
    //获取-新手贷款资讯 1
      $zx1_data_obj = $newsModel->field('title,id')->where(['is_valid'=>'Y','cate'=>'1'])->limit(6)->select();
      $zx1_data = $zx1_data_obj->toArray();

    //获取-无抵押贷资讯 2
      $zx2_data_obj = $newsModel->field('title,id')->where(['is_valid'=>'Y','cate'=>'2'])->limit(6)->select();
      $zx2_data = $zx2_data_obj->toArray();

    //获取-经营贷款资讯 3
      $zx3_data_obj = $newsModel->field('title,id')->where(['is_valid'=>'Y','cate'=>'3'])->limit(6)->select();
      $zx3_data = $zx3_data_obj->toArray();

    //获取-政策法规资讯 4
      $zx4_data_obj = $newsModel->field('title,id')->where(['is_valid'=>'Y','cate'=>'4'])->limit(6)->select();
      $zx4_data = $zx4_data_obj->toArray();

    //获取-个人贷款资讯 5
      $zx5_data_obj = $newsModel->field('title,id')->where(['is_valid'=>'Y','cate'=>'5'])->limit(6)->select();
      $zx5_data = $zx5_data_obj->toArray();

    //获取-购车贷款资讯 6
      $zx6_data_obj = $newsModel->field('title,id')->where(['is_valid'=>'Y','cate'=>'6'])->limit(6)->select();
      $zx6_data = $zx6_data_obj->toArray();

    //获取-房贷资讯资讯 7
      $zx7_data_obj = $newsModel->field('title,id')->where(['is_valid'=>'Y','cate'=>'7'])->limit(6)->select();
      $zx7_data = $zx7_data_obj->toArray();

    //获取-行业动态资讯 8
      $zx8_data_obj = $newsModel->field('title,id')->where(['is_valid'=>'Y','cate'=>'8'])->limit(6)->select();
      $zx8_data = $zx8_data_obj->toArray();

    //获取-信用卡资讯资讯 9
      $zx9_data_obj = $newsModel->field('title,id')->where(['is_valid'=>'Y','cate'=>'9'])->limit(6)->select();
      $zx9_data = $zx9_data_obj->toArray();

      $this->assign('zx1_data',$zx1_data);
      $this->assign('zx2_data',$zx2_data);
      $this->assign('zx3_data',$zx3_data);
      $this->assign('zx4_data',$zx4_data);
      $this->assign('zx5_data',$zx5_data);
      $this->assign('zx6_data',$zx6_data);
      $this->assign('zx7_data',$zx7_data);
      $this->assign('zx8_data',$zx8_data);
      $this->assign('zx9_data',$zx9_data);

      //侧边栏热门资讯信息
      $hot_data_obj = $newsModel->field('title,id')->where(['is_valid'=>'Y','is_hot'=>'Y'])->limit(6)->select();
      $hot_data = $hot_data_obj->toArray();
      $this->assign('hot_data',$hot_data);

    return $this->fetch();
  }



  //贷款资讯详情页
  public function newd() {
    $this->assign('title','资讯详情页'); 
    $some = $this->some();
    $this->assign('some',$some);  //公共信息

    $new_id = input('id');
    $newsModel = new NewsModel();
    $new_data_obj = NewsModel::get($new_id);
    $new_data = $new_data_obj->toArray();
    $this->assign('new_data',$new_data);

    return $this->fetch();
  }



  //前台贷款问答列表页面
  public function answers(Request $request) {
    $this->assign('title','贷款问答'); 
    $some = $this->some();
    $this->assign('some',$some);  //公共信息

    if($request->isAjax()) {

      $typ = input('post.typ');//接收问答类型 热门-1  已答-2  未答-3
      $page_data = input('post.page'); //接收指定页 

      if( $typ ==0 ) {
        $page = $page_data;
        //查询热门问题，问答数据及分页数据
        $answerModel = new AnswerModel();
        $list_obj  = $answerModel->where(['is_valid'=>'Y','is_hot'=>'Y'])->order('id','asc')->paginate(4); 
        $pages = $list_obj->render();
        $list = $list_obj->toArray();
        //拼接字符串
        $html = '';
        $html .= '<div class="wenda-content-list" style="display: block">';
        $i = 1;
        foreach($list['data'] as $vo) {
          $html .= '<div class="item"><div class="item-pic"><img src="/home/images/wenda-item-01.jpg"></div><div class="item-desc"><p>';
          $html .= '<a href="/id/'.$vo['id'].'.html" target="view_frame" id="answerd'.$i.'">'.$vo['requ_desc'].'</a>';
          $html .= '<span>'.$vo['requ_time'].'</span>';
          $html .= '</p><p>答：</p>';   
          $html .= subtext(htmlspecialchars_decode($vo['ans_desc']),80);
          $html .= '<p>1个问答 </p></div></div>';
          $i++;
        }
        $html .= '<div class="pag">'.$pages.'</div></div>';
        //将拼接字符串，以及当前为选项卡哪个选项分类返回去！
        $data = array();
        $data['typ']=0;
        $data['html']= $html;
        echo json_encode($data);exit;

      }elseif( $typ ==1 ) {

        $page = $page_data;
        //已解决问题
        $answerModel = new AnswerModel();
        $list_obj  = $answerModel->where(['is_valid'=>'Y','is_ans'=>'Y'])->order('id','asc')->paginate(4); 
        $pages = $list_obj->render();
        $list = $list_obj->toArray();
        $html = '';
        $html .= '<div class="wenda-content-list" style="display: block">';
        $i = 1;
        foreach($list['data'] as $vo) {
          $html .= '<div class="item"><div class="item-pic"><img src="/home/images/wenda-item-01.jpg"></div><div class="item-desc"><p>';
          $html .= '<a href="/id/'.$vo['id'].'.html" target="view_frame" id="answerd'.$i.'">'.$vo['requ_desc'].'</a>';
          $html .= '<span>'.$vo['requ_time'].'</span>';
          $html .= '</p><p>答：</p>';   
          $html .= subtext(htmlspecialchars_decode($vo['ans_desc']),80);
          $html .= '<p>1个问答 </p></div></div>';
          $i++;
        }
        $html .= '<div class="pag">'.$pages.'</div></div>';

        $data = array();
        $data['typ']=1;
        $data['html']= $html;
        echo json_encode($data);exit;

      }elseif( $typ ==2 ) {

        $page = $page_data;
        //待回答问题
        $answerModel = new AnswerModel();
        $list_obj  = $answerModel->where(['is_valid'=>'Y','is_ans'=>'N'])->order('id','asc')->paginate(4); 
        $pages = $list_obj->render();
        $list = $list_obj->toArray();
        $html = '';
        $html .= '<div class="wenda-content-list" style="display: block">';
        $i = 1;
        foreach($list['data'] as $vo) {
          $html .= '<div class="item"><div class="item-pic"><img src="/home/images/wenda-item-01.jpg"></div><div class="item-desc"><p>';
          $html .= '<a href="/id/'.$vo['id'].'.html" target="view_frame" id="answerd'.$i.'">'.$vo['requ_desc'].'</a>';
          $html .= '<span>'.$vo['requ_time'].'</span>';
          $html .= '</p><p>待回复</p></div></div>';
          $i++;   
        }
        $html .= '<div class="pag">'.$pages.'</div></div>';
        $data = array();
        $data['typ']=2;
        $data['html']= $html;
        echo json_encode($data);exit;
      }
    }

    return $this->fetch(); 
  }


  //前台贷款问答详情页
  public function answerd() {
    $this->assign('title','问答详情'); 
    $some = $this->some();
    $this->assign('some',$some);  //公共信息

    $id = input('id');
    $ans_data_obj = AnswerModel::get($id);
    $ans_data = $ans_data_obj->toArray();
    $this->assign('ans_data',$ans_data);

    return $this->fetch();
  }



  //前台关于赛金页面
  public function about() {
    $this->assign('title','关于赛金');

    $some     = $this->some();
    $this->assign('some',$some);  //公共信息

    $aboutModel  = new AboutModel();
    $about_sj    = $aboutModel->where('is_valid','Y')->find('about_sj');//关于赛金
        $email_obj   = $aboutModel->where('is_valid','Y')->find('email'); 
        $fax_obj     = $aboutModel->where('is_valid','Y')->find('fax'); 
        $qq_obj      = $aboutModel->where('is_valid','Y')->find('qq'); 
        $wechart_obj = $aboutModel->where('is_valid','Y')->find('wechart'); 

    $this->assign('about_sj',$about_sj);
        $this->assign('email',$email_obj);     
        $this->assign('fax',$fax_obj);     
        $this->assign('qq',$qq_obj);     
        $this->assign('wechart',$wechart_obj);     




    $comimgModel= new ComimgModel();
    $company_img= $comimgModel->where('is_valid','Y')->limit(1)->find();//赛金公司图片
    $this->assign('company_img',$company_img);
    
    $business   = $aboutModel->where('is_valid','Y')->find('business');//企业业务
    $this->assign('business',$business);

    return $this->fetch();
  }

  //前台公共页面信息
  private function some() {
    //获取公司电话、服务时间、友情链接、公司名称、地址
        $frilinkModel = new FrilinkModel();
        $frilink_obj  = $frilinkModel->limit(5)->select(); //友情链接
        $aboutModel   = new AboutModel();
        $company_name = $aboutModel->where('is_valid','Y')->find('company_name');//公司名称
        $address      = $aboutModel->where('is_valid','Y')->find('address');     //公司地址发
        $icp          = $aboutModel->where('is_valid','Y')->find('icp');         //备案号
        $tel          = $aboutModel->where('is_valid','Y')->find('tel');     //电话
        $service_time = $aboutModel->where('is_valid','Y')->find('service_time'); //服务时间

        $data['frilink']      = $frilink_obj->toArray();
        $data['company_name'] = $company_name->toArray();
        $data['address']      = $address->toArray();
        $data['icp']          = $icp->toArray();
        $data['tel']          = $tel->toArray();
        $data['service_time'] = $service_time->toArray();

        return $data;
  }

  //快速申请
  public function apply(Request $request) {
    //接收信息
    if($request->isPost()) {
      //是否同意协议，没有同意则提示
      if(input('is_agree')!='on') {
        $this->error('请阅读协议并勾选同意！');
      }
      //接收并过滤数据
      $data['name']  = addslashes(strip_tags(trim(input('post.name'))));
      $data['phone'] = addslashes(strip_tags(trim(input('post.phone'))));
      $data['zone']  = addslashes(strip_tags(trim(input('post.zone'))));
      $data['add_time'] = date('Y-m-d H:i:s',time());
            //验证规则
            $rules = [
                'name|姓名' =>['require','max'=>50,'min'=>2],
                'phone|手机号'=>['require','regex'=>'/^((13[0-9])|(14[5|7])|(15([0-3]|[5-9]))|(18[0,5-9]))\\d{8}$/','unique'=>'apply'],
                'zone|所在地区'  =>['require','max'=>200,'min'=>1]
            ];
            $message = $this->validate($data,$rules);
            if($message!==true) {
                $this->error($message);die;
            }
            $applyModel = new ApplyModel();
            $status = $applyModel->save($data);
            if( $status !== false ) {
              $this->success('您的申请已经提交，我们将会尽快与您联系！',url('home/index/index'));die;
            }else{
              $this->error('提交失败，请稍候再试！');die;
            }
    }
  }


  //贷款信息详情页
  public function detail() {
    $this->assign('title','车抵贷'); 
    $some = $this->some();
    $this->assign('some',$some);  //公共信息

    $id = input('id');
    $current_loan = Db::table('loan')
                            ->alias('a')
                            ->field('a.*,b.line_code as lines_min_name,c.line_code as lines_max_name,d.months as confine_min_name,e.months as confine_max_name')
                            ->join('code_line b','a.lines_min = b.id','left')
                            ->join('code_line c','a.lines_max = c.id','left')
                            ->join('code_confine d','a.confine_min = d.id','left')
                            ->join('code_confine e','a.confine_max = e.id','left')
                            ->where(['a.is_valid'=>'Y','a.id'=>$id])
                            ->find();

    $this->assign('current_loan',$current_loan);

    return $this->fetch();
  }



  //答案搜索
  public function searchs(Request $request) {
    $this->assign('title','搜索问题'); 
    $some = $this->some();
    $this->assign('some',$some);  //公共信息

    if($request->isGet()) {
      //过滤数据
      $data['requ_desc'] = addslashes(strip_tags(trim(input('requ_desc'))));
      //验证数据
      $rules = [
        'requ_desc|问题内容' =>['require','max'=>200,'min'=>2],
      ];
      $message = $this->validate($data,$rules);
      if($message!==true) {
        $this->error($message);die;
      }
      $requ_desc = $data['requ_desc'];

      //通过验证，搜索相关数据
      $answerModel = new AnswerModel();
      $where['requ_desc'] = array('like',"%$requ_desc%"); //查询条件放入数组
      $list_obj = $answerModel->where( $where)->where(['is_valid'=>'Y'])->order('id','asc')->paginate(4);
      $page = $list_obj->render();
      $list_data = $list_obj->toArray();
      $list = $list_data['data']; 
      $count = $list_data['total']; //数据总条数
      return $this->fetch('searchs',['list'=>$list,'page'=>$page,'count'=>$count,'requ_desc'=>$requ_desc]);

    }else if($request->isAjax()) {
      //接收并过滤数据
      $data['requ_desc'] = addslashes(strip_tags(trim(input('post.tiaojian'))));
      $page_data = input('post.page');
      //验证数据
      $rules = [
        'requ_desc|问题内容' =>['require','max'=>200,'min'=>2],
      ];
      $message = $this->validate($data,$rules);
      if($message!==true) {
        $this->error($message);die;
      }

      $tiaojian = $data['requ_desc'];
      $page = $page_data;
      //通过验证，搜索相关数据
      $answerModel = new AnswerModel();
      $where['requ_desc'] = array('like',"%$tiaojian%");
      $list_obj = $answerModel->where( $where)->where(['is_valid'=>'Y'])->order('id','asc')->paginate(4);
      $pages = $list_obj->render();
      $list = $list_obj->toArray();
      $count = $list['total'];

      //拼接字符串
      $html = '';
      $html .= '<div class="wenda-nav"></div><div class="wenda-content-box"><div class="wenda-content-list" style="display: block">';
      $i =1; //给a标签添加id 第一到第四
      foreach($list['data'] as $vo) {
        $html .= '<div class="item"><div class="item-pic"><img src="/home/images/wenda-item-01.jpg"></div><div class="item-desc"><p>';
        $html .= '<a target="view_frame"  href="/id/'.$vo['id'].'.html" id="answerd'.$i.'">'.$vo['requ_desc'].'</a>';
        $html .= '<span>'.$vo['requ_time'].'</span>';
        $html .= '</p><p>答：</p>';
        $html .= subtext(htmlspecialchars_decode($vo['ans_desc']),80);
        $html .= '<p>1个问答 </p></div></div>'; 
        $i++;    
      }        
      $html .= '收到'.$count.'条信息！<div class="pag">'.$pages.'</div></div>';
      $data = array();
      $data['tiaojian'] = $tiaojian;
      $data['html']     = $html;
      echo json_encode($data);exit;
    }
    return $this->fetch();
  }


}