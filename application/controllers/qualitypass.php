<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');
class Qualitypass extends CW_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->_init();
	}
	
	private function _init()
	{
		//取得测试站
		$teststation = array(""=>"(ALL)");
		$teststationObj = $this->db->query("SELECT tn.id,tn.name 
											FROM 
											teststation tn 
											JOIN status ss ON tn.status = ss.id
											AND ss.statusname = 'active'
											ORDER BY tn.name");
		if($teststationObj->num_rows() != 0)
		{
			foreach ($teststationObj->result() as $value) 
			{
				$teststation[$value->id] = $value->name;
			}
		}
		$this->smarty->assign("teststation",$teststation);
		//取得产品型号
		$producttype = array(""=>"(ALL)");
		$producttypeObj = $this->db->query("SELECT pe.id,pe.name 
											FROM producttype pe 
											JOIN status ss ON pe.status = ss.id
											AND ss.statusname = 'active'
											ORDER BY pe.name");
		if($producttypeObj->num_rows() !=0)
		{
			foreach ($producttypeObj->result() as $value) 
			{
				$producttype[$value->id] = $value->name;
			}
		}
		$this->smarty->assign("producttype",$producttype);
	}
	
	public function index($offset = 0, $limit = 30, $testtime = "", $teststation = "", $producttype = "",$passstatus = "")
	{
		//获取当前查询的是未放行记录还是放行记录
		if($passstatus != "")
		{
			$passStatus = $passstatus;
		}
		else
		{
			$passStatus = $this->input->post("passstatus");
		}
		if($passStatus == "")
		{
			$passStatus = "unpass";
		}
		if($passStatus == "unpass")
		{
			$unpassOffset = $offset;
			$passedOffset = 0;
		}
		else
		{
			$unpassOffset = 0;
			$passedOffset = $offset;
		}
		//取得用户选择日期，默认当前日期
		if($testtime != "")
		{
			$date = $testtime;
		}
		else
		{
			$date = $this->input->post("date");
		}
		if(!$this->_checkDateFormat($date))
		{
			$date = date("Y-m-d");
		}
		if($teststation != "")
		{
			$teststation = $teststation;
		}
		else
		{
			$teststation = $this->input->post("teststation");
		}
		if($producttype != "")
		{
			$producttype = $producttype;
		}
		else
		{
			$producttype = $this->input->post("producttype");
		}
		$dateSql = " AND po.testTime >= '".$date." 00:00:00' AND po.testTime <= '".$date." 23:59:59' ";
		$teststationSql = "";
		$producttypeSql = "";
		if($teststation != "")
		{
			$teststationSql = " AND po.testStation = '".$teststation."' ";
		}
		if($producttype != "")
		{
			$producttypeSql = " AND po.productType = '".$producttype."' ";
		}
		$qualitypassSql = "SELECT tt.*,qd.producttestinfo,qd.responsible_person,qd.remark,qd.modify_time
						   FROM (
						   SELECT po.id,po.testTime,tn.name AS teststaion,pe.name AS producttype,po.sn,po.result
						   FROM producttestinfo po
						   JOIN teststation tn ON po.testStation = tn.id
						   JOIN producttype pe ON po.productType = pe.id
						   AND po.tag1 = '1'
						   AND po.result = '0'
						   ".$dateSql.$teststationSql.$producttypeSql." ORDER BY po.testTime DESC ) tt
						   LEFT JOIN qualitypass_record qd ON qd.producttestinfo = tt.id ";
		$qualitypassObj = $this->db->query($qualitypassSql);
		$qualitypassArr = $qualitypassObj->result_array();
		$totalcount = count($qualitypassArr);
		//分页
		$this->load->library('pagination');
		$config1['full_tag_open'] = '<div class="locPage1">';
		$config1['full_tag_close'] = '</div>';
		$config1['base_url'] = '';
		if($passStatus == "unpass")
		{
			$config1['uri_segment'] = 3;
		}
		else
		{
			$config1['uri_segment'] = 0;
		}
		$config1['total_rows'] = count($qualitypassArr);
		$config1['per_page'] = $limit;
		$pagination_1 = new CI_Pagination();
		$pagination_1->initialize($config1);
		$pagenation1 = $pagination_1->create_links();
		$this->smarty->assign("pagenation1",$pagenation1);
		$qualitypassLimitSql = $qualitypassSql." LIMIT ".$unpassOffset.",".$limit;
		$qualitypassObj = $this->db->query($qualitypassLimitSql);
		$qualitypassArr = $qualitypassObj->result_array();
		//记录序号开始值
		$this->smarty->assign('totalcount', $totalcount-$unpassOffset);
		
		//获取已放行记录
		$passedSql = "SELECT po.id,po.testTime,tn.name AS teststaion,pe.name AS producttype,po.sn,po.result,qd.responsible_person,qd.remark,qd.modify_time
					  FROM producttestinfo po
					  JOIN teststation tn ON po.testStation = tn.id
					  JOIN producttype pe ON po.productType = pe.id
					  JOIN qualitypass_record qd ON qd.producttestinfo = po.id
					  AND po.tag1 = '3'
					  AND po.result = '1'
					  ".$dateSql.$teststationSql.$producttypeSql." ORDER BY po.testTime DESC
					  ";
		$passedObj = $this->db->query($passedSql);
		$passedArr = $passedObj->result_array();
		$passedTotalcount = count($passedArr);
		//分页
		$config2['full_tag_open'] = '<div class="locPage2">';
		$config2['full_tag_close'] = '</div>';
		$config2['base_url'] = '';
		if($passStatus != "unpass")
		{
			$config2['uri_segment'] = 3;
		}
		else
		{
			$config2['uri_segment'] = 0;
		}
		$config2['total_rows'] = $passedTotalcount;
		$config2['per_page'] = $limit;
		$pagination_2 = new CI_Pagination();
		$pagination_2->initialize($config2);
		$pagenation2 = $pagination_2->create_links();
		$this->smarty->assign("pagenation2",$pagenation2);
		$passedLimitSql = $passedSql." LIMIT ".$passedOffset.",".$limit;
		$passedObj = $this->db->query($passedLimitSql);
		$passedArr = $passedObj->result_array();
		//记录序号开始值
		$this->smarty->assign('passedTotalcount', $passedTotalcount-$passedOffset);

		//assign页面筛选条件
		$this->smarty->assign('conditionTime', $testtime);
		$this->smarty->assign('conditionTestStation', $teststation);
		$this->smarty->assign('conditionProducttype', $producttype);
		//assign当前页面
		$this->smarty->assign('passStatus', $passStatus);
		
		$this->smarty->assign('qualitypassArr', $qualitypassArr);
		$this->smarty->assign('passedArr', $passedArr);
		$this->smarty->assign('item', '质量放行');
		$this->smarty->assign('title', '质量放行');
		$this->smarty->display("qualitypass.tpl");
	}
	
	//保存放行记录
	public function savequalitypass()
	{
		//放行时间
		$currDate = date("Y-m-d H:i:s");
		//放行记录
		$record = $_POST;
		//总放行记录数
		$totalNum = $record["totalrecord"];
		//当前登录用户
		$user = $this->session->userdata["username"];
		//查询页面的条件
		$testtime = $record["testtime"];
		$teststation = $record["teststation"];
		$producttype = $record["producttype"];
		//判断总记录条数
		if($totalNum != 0)
		{
			for($i = 1; $i <= $totalNum; $i++)
			{
				$id = $record["id".$i];
				$remark = $record["remark".$i];
				$oldRecordRes = $this->db->query("SELECT * FROM qualitypass_record WHERE producttestinfo = '".$id."'");
				if(isset($record["change".$i]))
				{
					if($oldRecordRes->num_rows() == 0)
					{
						$this->db->query("INSERT INTO qualitypass_record (producttestinfo,responsible_person,remark,modify_time)
										VALUES ('".$id."','".$user."','".$remark."','".$currDate."')");
					}
					else
					{
						$oldRecordArr = $oldRecordRes->result_array();
						$oldRecord = $oldRecordArr[0];
						$this->db->query("UPDATE qualitypass_record SET 
										  responsible_person = '".$user."', remark='".$remark."', modify_time='".$currDate."'
										  WHERE producttestinfo = '".$id."'
										  ");
					}
					$this->db->query("UPDATE producttestinfo po SET po.result = '1',po.tag1 = '3'
							  		  WHERE po.id = '".$id."'");
				}
				else
				{
					if($oldRecordRes->num_rows() == 0)
					{
						if($remark == "")
						{
							//do nothing
						}
						else
						{
							$this->db->query("INSERT INTO qualitypass_record (producttestinfo,responsible_person,remark,modify_time)
										VALUES ('".$id."','".$user."','".$remark."','".$currDate."')");
						}
					}
					else
					{
						$oldRecordArr = $oldRecordRes->result_array();
						$oldRecord = $oldRecordArr[0];
						if($remark != $oldRecord['remark'])
						{
							$this->db->query("UPDATE qualitypass_record SET 
										  responsible_person = '".$user."', remark='".$remark."', modify_time='".$currDate."'
										  WHERE producttestinfo = '".$id."'
										  ");
						}
						else
						{
							//do noting
						}
					}
				}
			}
		}
		else
		{
			//do nothing
		}
		//跳转到待放行页面
		$passstatus = "unpass";
		$this->index(0,30,$testtime,$teststation,$producttype,$passstatus);
	}
	
	//保存不放行记录
	public function savequalityUnpass()
	{
		//不放行时间
		$currDate = date("Y-m-d H:i:s");
		//不放行记录
		$record = $_POST;
		//不总放行记录数
		$totalNum = $record["totalrecord"];
		//当前登录用户
		$user = $this->session->userdata["username"];
		//查询页面的条件
		$testtime = $record["testtime"];
		$teststation = $record["teststation"];
		$producttype = $record["producttype"];
		if($totalNum != 0)
		{
			for($i = 1;$i <= $totalNum;$i++)
			{
				$id = $record["id".$i];
				$remark = $record["remark".$i];
				$oldRecordObj = $this->db->query("SELECT * FROM qualitypass_record WHERE producttestinfo = '".$id."'");
				$oldRecordArr = $oldRecordObj->result_array();
				if(count($oldRecordArr) != 0)
				{
					$oldRecord = $oldRecordArr[0];
					if(isset($record["change".$i]))
					{
						$this->db->query("UPDATE producttestinfo po SET po.result = '0',po.tag1 = '1'
								  		  WHERE po.id = '".$id."'");
						$this->db->query("UPDATE qualitypass_record SET 
										  responsible_person = '".$user."', remark='".$remark."', modify_time='".$currDate."'
										  WHERE producttestinfo = '".$id."'
										  ");
					}
					else
					{
						if($remark != $oldRecord['remark'])
						{
							$this->db->query("UPDATE qualitypass_record SET 
										  responsible_person = '".$user."', remark='".$remark."', modify_time='".$currDate."'
										  WHERE producttestinfo = '".$id."'
										  ");
						}
						else
						{
							//do noting
						}
					}
				}
				else
				{
					//do nothing
				}
			}
		}
		else
		{
			//do nothing
		}
		//跳转到已放行页面
		$passstatus = "passed";
		$this->index(0,30,$testtime,$teststation,$producttype,$passstatus);
	}
	
	
	//验证日期格式
	private function _checkDateFormat($date)
	{
		if(preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $date, $parts))
		{
			if(checkdate($parts[2], $parts[3], $parts[1]))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}
	
}