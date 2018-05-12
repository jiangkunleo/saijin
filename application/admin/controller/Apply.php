<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use think\Request;
use app\admin\model\Apply as ApplyModel;
/**
 *  贷款申请控制器
 */
class Apply extends Common{
	//展示列表页
	public function index() {
		$applyModel     = new ApplyModel();
		//分页
		$list  = $applyModel->order('id','asc')->paginate(12); //分页，每页12条数据
		$count = $applyModel->count(); //总记录数
		$page  = $list->render();      //页码数据
		$currentPage = $list->currentPage(); //当前的页码
		return $this->fetch('index',['list'=>$list ,'page'=>$page,'count'=>$count,'currentPage'=>$currentPage]);
	}

	//添加备注
	public function comment(Request $request) {
	    if($request->isGet()) {
	        $apply_id       = input('id');
	        $applyModel     = new ApplyModel();
	        $apply_data_obj = $applyModel->find($apply_id);
	        $apply_data     = $apply_data_obj->toArray();

	        $this->assign('apply_data',$apply_data);
	        return $this->fetch();

	    }elseif($request->isPost()) {

	        $apply_id      = input('post.id');
	        $apply_comment = htmlspecialchars(trim(input('post.comment')));
	        $applyModel    = new ApplyModel();
	        $status = $applyModel->where('id',$apply_id)->update(['comment'=>$apply_comment]);
	        if( $status !== false ) {
	            $this->success('添加备注完成！',url('admin/apply/index'));die;
	        }else{
	            $this->error('添加备注失败！');die;
	        }
	    }
	}


	//删除申请信息
	public function del(Request $request) {
		//接收get传递过来的id
		if($request->isGet()) {
			$current_apply_id   = input('id');
			$status = ApplyModel::destroy($current_apply_id); 
			if($status!==false) {
			    $this->success('删除成功！','admin/apply/index',1);die;
			}else{
				$this->error('删除失败!');die;
			}
		}
	}


	//导出所有预约信息到Excel表格中
	public function outexcel() {
		//手动引入类文件
		import('outexcel.PHPExcel',EXTEND_PATH);
		import('outexcel.PHPExcel.Writer.Excel5',EXTEND_PATH);
		//实例化对象
		$excel_obj = new \PHPExcel();
		$write_obj = new \PHPExcel_Writer_Excel5($excel_obj);

        //设置sheet名称
        $sheets=$excel_obj->getActiveSheet()->setTitle('客户贷款申请信息列表');
        //设置sheet列头信息
        $excel_obj->setActiveSheetIndex()
        			->setCellValue('A1','客户ID')
        			->setCellValue('B1', '姓名')
        			->setCellValue('C1', '地区')
        			->setCellValue('D1', '手机号码')
        			->setCellValue('E1', '申请时间')
        			->setCellValue('F1', '备注');

        //查询表中的所有信息
        $applyModel   = new ApplyModel();
        $apply_data_obj = $applyModel->select();
        $apply_data = $apply_data_obj->toArray();
        $i = 2;
        foreach($apply_data as $v) {
        	$sheets=$excel_obj->getActiveSheet()->setCellValue('A'.$i,$v['id']);
        	$sheets=$excel_obj->getActiveSheet()->setCellValue('B'.$i,$v['name']);
        	$sheets=$excel_obj->getActiveSheet()->setCellValue('C'.$i,$v['zone']);
        	$sheets=$excel_obj->getActiveSheet()->setCellValue('D'.$i,$v['phone']);
        	$sheets=$excel_obj->getActiveSheet()->setCellValue('E'.$i,$v['add_time']);
        	$sheets=$excel_obj->getActiveSheet()->setCellValue('F'.$i,$v['comment']);
        	$i++;
        }

        //整体设置字体和字体大小
        $excel_obj->getDefaultStyle()->getFont()->setName( 'Arial');
        $excel_obj->getDefaultStyle()->getFont()->setSize(10);
        //单元格宽度自适应
        $excel_obj->getActiveSheet()->getColumnDimension('A')->setAutoSize(true); 
        $excel_obj->getActiveSheet()->getColumnDimension('B')->setAutoSize(true); 
        $excel_obj->getActiveSheet()->getColumnDimension('C')->setAutoSize(true); 
        $excel_obj->getActiveSheet()->getColumnDimension('D')->setAutoSize(true); 
        $excel_obj->getActiveSheet()->getColumnDimension('E')->setAutoSize(true); 
        $excel_obj->getActiveSheet()->getColumnDimension('F')->setAutoSize(true); 
        // 输出Excel表格到浏览器下载
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="客户贷款信息列表.xls"'); //excel表格名称
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
        $write_obj->save('php://output');        
	}




}