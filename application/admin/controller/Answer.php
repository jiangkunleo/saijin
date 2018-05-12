<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use think\Request;
use app\admin\model\Answer as AnswerModel;
/**
 *  客户提问 问答资料控制器
 */
class Answer extends Common{
	//提问列表
	public function index() {

		$AnswerModel   = new AnswerModel();
		//分页
		$list  = $AnswerModel->order('id','asc')->paginate(10); //分页，每页10条数据
		$count = $AnswerModel->count(); //总记录数
		$page  = $list->render();      //页码数据
		$currentPage = $list->currentPage(); //当前的页码
		return $this->fetch('index',['list'=>$list ,'page'=>$page,'count'=>$count,'currentPage'=>$currentPage]);
	}

	//回答提问
	public function add() {
		

		return $this->fetch();
	}

	//查看问答
	public function edit(Request $request) {
		if($request->isGet()) {
			$id = input('id');
			$answerModel = new AnswerModel();
			$ans_data_obj = AnswerModel::get($id);
			$ans_data = $ans_data_obj->toArray();
			//halt($ans_data);
			$this->assign('ans_data',$ans_data);
			return $this->fetch();

		}elseif($request->isPost()) {
			//接收并过滤数据
			$data['id'] = input('id');
			$data['requ_name'] = input('requ_name');
			$data['requ_desc'] = input('requ_desc');
			$data['requ_time'] = input('requ_time');
			$data['ans_name'] = addslashes(strip_tags(trim(input('post.ans_name'))));
			$data['ans_time'] = date('Y-m-d H:i:s',time());
			$data['ans_desc'] = htmlspecialchars(input('post.ans_desc'));
			$data['is_ans']    =  input('post.is_ans');
			$data['is_valid']  =  input('post.is_valid');
			$data['is_hot']    =  input('post.is_hot');
			//halt($data);
			//**验证数据
			$rules = [
				'ans_name|回复者民称' =>['require','max'=>30,'min'=>2],
				'ans_desc|回复内容'   =>['require','min'=>2],
			];
			$message = $this->validate($data,$rules);
			if($message!==true) {
				$this->error($message);die;
			}

			$answerModel = new AnswerModel();
			$status = $answerModel->update($data);
			if($status!==false) {
				$this->success('回复提交成功',url('admin/answer/index'));die;
			}else{
				$this->error('提交回复失败！');die;
			}
		}
	}

	//删除问题
	public function del(Request $request) {
		if($request->isGet()) {
			$current_ans_id   = input('id');
			$status = AnswerModel::destroy($current_ans_id); //删除此文章的数据库信息
			if($status!==false) {
			    $this->success('删除成功！','admin/answer/index',1);die;
			}else{
				$this->error('删除失败!');die;
			}
		}
	}


	//是否有效，切换
	public function valid(Request $request) {
		$answerModel    = new AnswerModel();
		$current_ans_id = input('id');
		$ans_data_obj   = $answerModel->where('id',$current_ans_id)->field('is_valid')->select();
		$ans_data = $ans_data_obj->toArray();
		if($ans_data[0]['is_valid']=='Y') {
			$answerModel->where('id',$current_ans_id)->update(['is_valid'=>'N']);
		}else{
			$answerModel->where('id',$current_ans_id)->update(['is_valid'=>'Y']);	
		}
		$this->redirect('/admin/answer/index');die;
	}

	//是否设置热门，切换
	public function hot(Request $request) {
		$answerModel    = new AnswerModel();
		$current_ans_id = input('id');
		$ans_data_obj   = $answerModel->where('id',$current_ans_id)->field('is_hot')->select();
		$ans_data = $ans_data_obj->toArray();
		if($ans_data[0]['is_hot']=='Y') {
			$answerModel->where('id',$current_ans_id)->update(['is_hot'=>'N']);
		}else{
			$answerModel->where('id',$current_ans_id)->update(['is_hot'=>'Y']);	
		}
		$this->redirect('/admin/answer/index');die;
	}


}