<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');
class Reportform extends CW_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->_init();
	}
	public function _init()
	{
		//取得工厂
		$factoryArr = array(""=>"(ALL)");
		$factoryObj=$this->db->query("SELECT DISTINCT fy.id,fy.name 
									  FROM factory fy
									  JOIN status ss ON fy.status = ss.id
									  AND ss.statusname = 'active'");
		if($factoryObj->num_rows() != 0)
		{
			foreach ($factoryObj->result_array() as $value) 
			{
				$factoryArr[$value["id"]] = $value["name"];
			}
		}
		$this->smarty->assign("factoryArr",$factoryArr);
		//取得车间
		$departmentArr = array(""=>"(ALL)");
		$departmentObj = $this->db->query("SELECT DISTINCT dt.id,dt.name 
										   FROM department dt
										   JOIN factory fy ON dt.factory = fy.id
										   JOIN status ss ON dt.status = ss.id 
										   AND fy.status = ss.id
										   AND ss.statusname = 'active'");
		if($departmentObj->num_rows() != 0)
		{
			foreach($departmentObj->result() as $value)
			{
				$departmentArr[$value->id] = $value->name;
			}
		}
		$this->smarty->assign("departmentArr",$departmentArr);
		//取得机台
		$latheArr = array(""=>"(ALL)");
		$latheObj = $this->db->query("SELECT DISTINCT lathe FROM producttestinfo WHERE lathe <> ''");
		if($latheObj->num_rows() != 0)
		{
			foreach ($latheObj->result() as  $value) 
			{
				$latheArr[$value->lathe] = $value->lathe;
			}
		}
		$this->smarty->assign("latheArr",$latheArr);
		//取得产品型号
		$producttypeArr = array(""=>"(ALL)");
		$producttypeObj = $this->db->query("SELECT id,name FROM producttype");
		if($producttypeObj->num_rows() != 0)
		{
			foreach ($producttypeObj->result() as $value) 
			{
				$producttypeArr[$value->id] = $value->name;
			}
		}
		$this->smarty->assign("producttypeArr",$producttypeArr);
	}
	
	public function index()
	{
		//整体合格率，天
		$date1 = $this->input->post("date1");
		if(!$this->_checkDateFormat($date1))
		{
			$date1 = date("Y-m-d");
		}
		//取得工厂，车间
		$factory1 = $this->input->post("factory1");
		$department1 = $this->input->post("department1");
		$testStaionSql1 = $this->_getTestStationSql($factory1, $department1);
		//计算查询开始日期（当前日期往前30天）
		$fromDate1 = date("Y-m-d",strtotime($date1)-30*24*3600);
		//总记录的SQL语句
		$totalListSql1 = "SELECT DATE(po.testTime) day,count(po.sn) num
						 FROM producttestinfo po 
						 WHERE po.testTime >= '".$fromDate1."' 
						 AND po.testTime < '".$date1."'
						 $testStaionSql1
						 GROUP BY day
						 ORDER BY po.testTime"
						 ;
		$totalNumObj1 = $this->db->query($totalListSql1);
		//总记录数组
		$totalList1 = array();
		for($i=30;$i>=1;$i--)
		{
			$day = date("Y-m-d",strtotime($date1)-$i*24*3600);
			$totalList1[$day] = 0;
		}
		foreach ($totalNumObj1->result_array() as $value) 
		{
			$totalList1[$value["day"]] = $value["num"];
		}
		//通过记录SQL语句
		$okNumSql1 = "SELECT DATE(po.testTime) day,count(po.sn) num
						 FROM producttestinfo po 
						 WHERE po.testTime >= '".$fromDate1."' 
						 AND po.testTime < '".$date1."'
						 $testStaionSql1
						 AND po.result = '1'
						 GROUP BY day
						 ORDER BY po.testTime"
						 ;
		$okNumObj1 = $this->db->query($okNumSql1);
		//通过率数组
		$passRateList1 = array();
		for($i=30;$i>=1;$i--)
		{
			$day = date("Y-m-d",strtotime($date1)-$i*24*3600);
			$passRateList1[$day] = 0;
		}
		foreach ($okNumObj1->result_array() as $value) 
		{
			$passRateList1[$value["day"]] = number_format($value['num']*100/$totalList1[$value["day"]],1,'.','');
		}
		$this->smarty->assign('passRateList1', $passRateList1);
		
		
		//整体合格率，月
		//当前日期
		$nowDate = date("Y-m-01");
		//查询开始日期（前12月的1号）
		$fromDate2 = date("Y-m-01", strtotime( $nowDate." -12 months"));
		//取得工厂，车间
		$factory2 = $this->input->post("factory2");
		$department2 = $this->input->post("department2");
		$testStaionSql2 = $this->_getTestStationSql($factory2, $department2);
		//总记录SQL语句
		$totalListSql2 = "SELECT MONTH(po.testTime) month,count(po.sn) num
						 FROM producttestinfo po 
						 WHERE po.testTime >= '".$fromDate2."'
						 AND po.testTime < '".$nowDate."'
						 $testStaionSql2
						 GROUP BY month
						 ORDER BY po.testTime"
						 ;
		$totalListObj2 = $this->db->query($totalListSql2);
		//前12个月各月份总记录数组，置空
		$totalList2 = array();
    	for ($i = 12; $i >= 1; $i--)
    	{
    		$months = date("n", strtotime( $nowDate." -$i months"));
			$totalList2[$months] = 0;
		}
		//前12个月各月份总记录数组，根据查询结果写入内容
		foreach ($totalListObj2->result_array() as $value) 
		{
			$totalList2[$value['month']] = $value["num"];
		}
		//前12个月，各月份通过记录数SQL语句
		$okNumSql2 = "SELECT MONTH(po.testTime) month,count(po.sn) num
						 FROM producttestinfo po 
						 WHERE po.testTime >= '".$fromDate2."'
						 AND po.testTime < '".$nowDate."'
						 $testStaionSql2
						 AND po.result = '1'
						 GROUP BY month
						 ORDER BY po.testTime"
						 ;
		$okNumObj2 = $this->db->query($okNumSql2);
		//各月份通过率数组，先置空
		$passRateList2 = array();
		for ($i = 12; $i >= 1; $i--)
    	{
    		$months = date("n", strtotime( $nowDate." -$i months"));
			$passRateList2[$months] = 0;
		}
		//根据查询出的PASS记录计算通过率
		foreach ($okNumObj2->result_array() as $value) 
		{
			$passRateList2[$value['month']] = number_format($value['num']*100/$totalList2[$value['month']],1,'.','');
		}
		$this->smarty->assign('passRateList2', $passRateList2);
		
		
		//机台合格率，月
		//当前日期
		$nowDate1 = $nowDate;
		//查询开始日期（前12月的1号）
		$fromDate3 = $fromDate2;
		//取得车台条件
		$lathe = $this->input->post("lathe");
		$latheSql = "";
		if($lathe != "")
		{
			$latheSql = " AND po.lathe = '".$lathe."' ";
		}
		//总记录SQL语句
		$totalListSql2 = "SELECT MONTH(po.testTime) month,count(po.sn) num
						 FROM producttestinfo po 
						 WHERE po.testTime >= '".$fromDate3."'
						 AND po.testTime < '".$nowDate1."'
						 $latheSql
						 GROUP BY month
						 ORDER BY po.testTime"
						 ;
		$totalListObj3 = $this->db->query($totalListSql2);
		//前12个月各月份总记录数组，置空
		$totalList3 = array();
    	for ($i = 12; $i >= 1; $i--)
    	{
    		$months = date("n", strtotime( $nowDate1." -$i months"));
			$totalList3[$months] = 0;
		}
		//前12个月各月份总记录数组，根据查询结果写入内容
		foreach ($totalListObj3->result_array() as $value) 
		{
			$totalList3[$value['month']] = $value["num"];
		}
		//前12个月，各月份通过记录数SQL语句
		$okNumSql3 = "SELECT MONTH(po.testTime) month,count(po.sn) num
						 FROM producttestinfo po 
						 WHERE po.testTime >= '".$fromDate3."'
						 AND po.testTime < '".$nowDate1."'
						 $latheSql
						 AND po.result = '1'
						 GROUP BY month
						 ORDER BY po.testTime"
						 ;
		$okNumObj3 = $this->db->query($okNumSql3);
		//各月份通过率数组，先置空
		$passRateList3 = array();
		for ($i = 12; $i >= 1; $i--)
    	{
    		$months = date("n", strtotime( $nowDate1." -$i months"));
			$passRateList3[$months] = 0;
		}
		//根据查询出的PASS记录计算通过率
		foreach ($okNumObj3->result_array() as $value) 
		{
			$passRateList3[$value['month']] = number_format($value['num']*100/$totalList3[$value['month']],1,'.','');
		}
		$this->smarty->assign('passRateList3', $passRateList3);
		
		
		
		//产品合格率，天
		$date2 = $this->input->post("date2");
		if(!$this->_checkDateFormat($date2))
		{
			$date2 = date("Y-m-d");
		}
		//取得产品型号条件
		$producttype = $this->input->post("producttype");
		$producttypeSql = "";
		if($producttype != "")
		{
			$producttypeSql = " AND po.productType = '".$producttype."' ";
		}
		//计算查询开始日期（当前日期往前30天）
		$fromDate4 = date("Y-m-d",strtotime($date2)-30*24*3600);
		//总记录的SQL语句
		$totalListSql4 = "SELECT DATE(po.testTime) day,count(po.sn) num
						 FROM producttestinfo po 
						 WHERE po.testTime >= '".$fromDate4."' 
						 AND po.testTime < '".$date2."'
						 $producttypeSql
						 GROUP BY day
						 ORDER BY po.testTime"
						 ;
		$totalNumObj4 = $this->db->query($totalListSql4);
		//总记录数组
		$totalList4 = array();
		for($i=30;$i>=1;$i--)
		{
			$day = date("Y-m-d",strtotime($date2)-$i*24*3600);
			$totalList4[$day] = 0;
		}
		foreach ($totalNumObj4->result_array() as $value)
		{
			$totalList4[$value["day"]] = $value["num"];
		}
		//通过记录SQL语句
		$okNumSql4 = "SELECT DATE(po.testTime) day,count(po.sn) num
						 FROM producttestinfo po 
						 WHERE po.testTime >= '".$fromDate4."' 
						 AND po.testTime < '".$date2."'
						 $producttypeSql
						 AND po.result = '1'
						 GROUP BY day
						 ORDER BY po.testTime"
						 ;
		$okNumObj4 = $this->db->query($okNumSql4);
		//通过率数组
		$passRateList4 = array();
		for($i=30;$i>=1;$i--)
		{
			$day = date("Y-m-d",strtotime($date2)-$i*24*3600);
			$passRateList4[$day] = 0;
		}
		foreach ($okNumObj4->result_array() as $value)
		{
			$passRateList4[$value["day"]] = number_format($value['num']*100/$totalList4[$value["day"]],1,'.','');
		}
		$this->smarty->assign('passRateList4', $passRateList4);
		
		
		$this->smarty->assign('item','报表');
		$this->smarty->assign('title', '报表');
		$this->smarty->display("reportform.tpl");
	}
	
	
	//根据工厂，车间取得测试站
	private function _getTestStationSql($factory,$department)
	{
		$testStationSql = "";
		if($factory == "" && $department == "")
		{
			//do nothing
		}
		else
		{
			if ($department == "")
			{
				$departmentObj = $this->db->query("SELECT id FROM department WHERE factory = '".$factory."'");
				$departmentSql = "";
				if($departmentObj->num_rows() != 0)
				{
					foreach ($departmentObj->result() as $value) 
					{
						$departmentSql .= $value->id.",";
					}
					$departmentSql = substr($departmentSql, 0 , -1);
					$testStationObj = $this->db->query("SELECT id FROM teststation WHERE department IN (".$departmentSql.")");
					if($testStationObj->num_rows() != 0)
					{
						$testStationSql = " AND po.testStation in (";
						foreach ($testStationObj->result_array() as $value)
						{
							$testStationSql .= $value['id'].",";
						}
						$testStationSql = substr($testStationSql, 0 ,-1);
						$testStationSql .= ") ";
					}
					else
					{
						$testStationSql = " AND po.testStation in (0)";
					}
				}
				else
				{
					$testStationSql = " AND po.testStation in (0)";
				}
			}
			else
			{
				$testStationObj = $this->db->query("SELECT id FROM teststation WHERE department = '".$department."'");
				if($testStationObj->num_rows() != 0)
				{
					$testStationSql = " AND po.testStation in (";
					foreach ($testStationObj->result_array() as $value)
					{
						$testStationSql .= $value['id'].",";
					}
					$testStationSql = substr($testStationSql, 0 ,-1);
					$testStationSql .= ") ";
				}
				else
				{
					$testStationSql = " AND po.testStation in (0)";
				}
			}
		}
		return $testStationSql;
	}
	//验证日期格式
	private function _checkDateFormat($data)
	{
		if(preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $data, $parts))
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
	//AJAX请求取得车间
	public function ajax_getDepartment()
	{
		$factory = json_decode($_POST['id']);
		$factoryObj = $this->db->query("SELECT dt.id,dt.name
										FROM department dt
										JOIN status ss ON dt.status = ss.id
										AND ss.statusname = 'active'
										AND dt.factory='".$factory."'
									    ");
		$factoryArr = $factoryObj->result_array();
		header('Content-Type: application/json');
		echo json_encode($factoryArr);
	}
}