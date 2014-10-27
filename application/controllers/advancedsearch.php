<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');
class Advancedsearch extends CW_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->_init();
	}
	
	private function _init()
	{
		$hourList = array(''=>'');
		for ($i = 0; $i <= 23; $i++)
		{
			$arr = array($i=>$i);
			$hourList = array_merge_recursive($hourList, $arr);
		}
		$this->smarty->assign("hourList", $hourList);
		$minuteList = array(''=>'');
		for ($i = 0; $i <= 59; $i++)
		{
			$arr = array($i=>$i);
			$minuteList = array_merge_recursive($minuteList, $arr);
		}
		$this->smarty->assign("minuteList", $minuteList);
		$testResultList = array(
			''=>'(ALL)',
			'0'=>'FAIL',
			'1'=>'PASS'
		);
		$this->smarty->assign('testResultList', $testResultList);
		//取得测试站
		$teststationObject = $this->db->query("SELECT tn.id,tn.name FROM teststation tn 
											   JOIN status ss ON tn.status = ss.id
											   AND ss.statusname = 'active'
											   ORDER BY tn.name");
		$teststationArray = $teststationObject->result_array();
		$teststation = $this->array_switch($teststationArray, "name", "(ALL)");

		$this->smarty->assign("teststation",$teststation);
		//取得测试设备
		$equipmentObject = $this->db->query("SELECT et.id,et.sn FROM equipment et 
											   JOIN status ss ON et.status = ss.id
											   AND ss.statusname = 'active'
											   ORDER BY et.sn");
		$equipmentArray = $equipmentObject->result_array();
		$equipment = $this->array_switch($equipmentArray, "sn", "(ALL)");
		$this->smarty->assign("equipment",$equipment);
		//取得测试者
		$testerObject = $this->db->query("SELECT tr.id,tr.employeeid FROM tester tr 
											   JOIN status ss ON tr.status = ss.id
											   ORDER BY tr.employeeid
											   ");
		$testerArray = $testerObject->result_array();
		$tester = $this->array_switch($testerArray, "employeeid", "(ALL)");
		$this->smarty->assign("tester",$tester);
		//取得产品型号
		$producttypeObject = $this->db->query("SELECT pe.id,pe.name FROM producttype pe
											   JOIN status ss ON pe.status = ss.id
											   AND ss.statusname = 'active'
											   ORDER BY pe.name");
		$producttypeArray = $producttypeObject->result_array();
		$producttype = $this->array_switch($producttypeArray, "name", "(ALL)");
		$this->smarty->assign("producttype",$producttype);
		//取得测试项
		$testitemObject = $this->db->query("SELECT tm.id,tm.name FROM testitem tm
											   JOIN status ss ON tm.status = ss.id
											   AND ss.statusname = 'active'
											   ORDER BY tm.name");
		$testitemArray = $testitemObject->result_array();
		$testitem = $this->array_switch($testitemArray, "name", "(NULL)");
		$this->smarty->assign("testitem",$testitem);
	}
	
	public function index($offset = 0,$limit = 30)
	{
		$timeFrom1 = $this->input->post("timeFrom1");
		$timeFrom2 = $this->input->post("timeFrom2");
		$timeFrom3 = $this->input->post("timeFrom3");
		$timeTo1 = $this->input->post("timeTo1");
		$timeTo2 = $this->input->post("timeTo2");
		$timeTo3 = $this->input->post("timeTo3");
		$teststation = emptyToNull($this->input->post("teststation"));
		$equipment = emptyToNull($this->input->post("equipment"));
		$tester = emptyToNull($this->input->post("tester"));
		$producttype = emptyToNull($this->input->post("producttype"));
		$testResult = emptyToNull($this->input->post("testResult"));
		$platenum = emptyToNull($this->input->post("platenum"));
		$labelnum = emptyToNull($this->input->post("labelnum"));
		$sn = emptyToNull($this->input->post('sn'));
		
		$timeFrom1 = emptyToNull($this->input->post("timeFrom1"));
		if ($timeFrom1 == null)
		{
			$timeFrom1 = "1900-01-01";
		}
		$timeFrom2 = emptyToNull($this->input->post("timeFrom2"));
		if ($timeFrom2 == null)
		{
			$timeFrom2 = 0;
		}
		$timeFrom3 = emptyToNull($this->input->post("timeFrom3"));
		if ($timeFrom3 == null)
		{
			$timeFrom3 = 0;
		}
		$timeFrom = $timeFrom1." ".$timeFrom2.":".$timeFrom3;
		$timeTo1 = emptyToNull($this->input->post("timeTo1"));
		if ($timeTo1 == null)
		{
			$timeTo1 = "2999-01-01";
		}
		$timeTo2 = emptyToNull($this->input->post("timeTo2"));
		if ($timeTo2 == null)
		{
			$timeTo2 = 0;
		}
		$timeTo3 = emptyToNull($this->input->post("timeTo3"));
		if ($timeTo3 == null)
		{
			$timeTo3 = 0;
		}
		$timeTo = $timeTo1." ".$timeTo2.":".$timeTo3;
		$timeFromSql=" AND po.testTime >= '".$timeFrom."'";
		$timeToSql = " AND po.testTime <= '".$timeTo."'";
		$teststationSql = "";
		$equipmentSql = "";
		$testerSql = "";
		$producttypeSql = "";
		$testResultSql = "";
		$platenumSql = "";
		$labelnumSql = "";
		$snSql = "";
		if($testResult != null)
		{
			if($testResult == 0 || $testResult == 1)
			{
				$testResultSql = " AND po.result = ".$testResult;
			}
			else
			{
				$testResultSql = " AND 0 ";
			}
		}
		if($sn != null)
		{
			$start = strpos($sn, "+");
			$end = strripos($sn, "+");
			
			if(strlen($sn) == 1)
			{
				$snSql = " AND po.sn LIKE '%".$sn."%' ";
			}
			else
			{
				if($start == 0 && $end == (strlen($sn)-1))
				{
					$sn = substr($sn, 1,strlen($sn)-2);
					$snSql = " AND po.sn = '".$sn."' ";
				}
				else
				{
					$snSql = " AND po.sn LIKE '%".$sn."%' ";
				}
			}
		}
		if($equipment != null)
		{
			$equipmentSnObj = $this->db->query("SELECT sn FROM equipment WHERE id = '".$equipment."'");
			$equipmentSn = $equipmentSnObj->first_row()->sn;
			$equipmentSql = " AND po.equipmentSn = '".$equipmentSn."' ";
		}
		if($teststation != null)
		{
			$teststationSql = " AND po.testStation = '".$teststation."' ";
		}
		if($producttype != null)
		{
			$producttypeSql = " AND po.productType = '".$producttype."' ";
		}
		if($tester != null)
		{
			$testerSql = " AND po.tester = '".$tester."' ";
		}
		if($platenum != null)
		{
			$platenumSql = " AND po.platenum LIKE '%".$platenum."%' ";
		}
		if($labelnum != null)
		{
			$labelnumSql = " AND po.workorder LIKE '%".$labelnum."%' ";
		}
		//处理行列转换SQL语句中的CASE条件及MAX语句，生成SQL语句
		$advanceSearcSql = "";
		$testItemsSql = " AND tt.testItem IN (";
		$maxSql = "";
		$caseSql = "";
		//保存用户所选测试项及范围的数组
		$testitemLimitArr = array();
		$itemcount = $this->input->post("testitemcount");
		//取得用户所选测试项，及范围
		if($itemcount != "")
		{
			for($i=1;$i<=$itemcount;$i++)
			{
				$testitem = $this->input->post("testitem".$i);
				if($testitem != "")
				{
					$testitemNameObj = $this->db->query("SELECT name FROM testitem WHERE id = $testitem");
					$testitemName = $testitemNameObj->first_row()->name;
					$testitemfrom = $this->input->post("testitemfrom".$i);
					$testitemto = $this->input->post("testitemto".$i);
					$arr = array($testitem,$testitemfrom,$testitemto);
					$testitemLimitArr[$testitemName] = $arr;
				}
			}
		}
		$advanceSearchArr = array();
		$count = "";
		if(count($testitemLimitArr) == 0)
		{
			//总记录数，供pack.tpl中序号用
			$count = count($advanceSearchArr);
			//分页
			$this->load->library('pagination');
			$config['full_tag_open'] = '<div class="locPage">';
			$config['full_tag_close'] = '</div>';
			$config['base_url'] = '';
			$config['uri_segment'] = 3;
			$config['total_rows'] = count($advanceSearchArr);
			$config['per_page'] = $limit;
			$this->pagination->initialize($config);
		}
		else
		{
			foreach ($testitemLimitArr as $key => $value) 
			{
				$testItemsSql .= $value['0'].",";
				$maxSql .= " MAX(aaa.$key) AS '$key' ,";
				$caseSql .= " CASE aa.testItem WHEN '".$value[0]."' THEN aa.value END AS '$key',";
			}
			//截去最后一个","号
			$testItemsSql = substr($testItemsSql, 0 ,-1);
			$testItemsSql .= ") ";
			$maxSql = substr($maxSql, 0 ,-1);
			$caseSql = substr($caseSql, 0 ,-1);
			$advanceSearchSql = "SELECT
    							aaa.tag1,aaa.testTime,aaa.sn,
								$maxSql
								FROM
    							(
    								SELECT
        							aa.tag1,aa.testTime,aa.sn,
        							$caseSql
    								FROM
    								(
    									SELECT a.tag1,a.testTime, a.sn, a.testItem,MAX(cast(a.value as DECIMAL(10,4))) as value
          							 	FROM 
          								(
            							SELECT po.tag1,po.id, po.sn, po.equipmentSn, po.testTime, po.testStation, po.tester, po.productType, po.result, po.platenum,
            								 po.workorder, tt.testItem, tt.testResult, tt.img, te.value, te.mark
						            	FROM producttestinfo po
						            	JOIN testitemresult tt ON tt.productTestInfo = po.id
						            	JOIN testitemmarkvalue te ON te.testItemResult = tt.id
						            	".$snSql.$timeFromSql.$timeToSql.$teststationSql.$equipmentSql.$testerSql.$producttypeSql.$testResultSql.$platenumSql.$labelnumSql."
            							)a
            							GROUP BY a.sn, a.testItem, a.testTime
            							ORDER BY a.testTime DESC
              						) aa
    							) AS aaa
								GROUP BY aaa.testTime,aaa.sn";
			$advanceSearchObj = $this->db->query($advanceSearchSql);
			$advanceSearchArr = $advanceSearchObj->result_array();
			//总记录数，供advancesearch.tpl中序号用
			$count = count($advanceSearchArr);
			//分页
			$this->load->library('pagination');
			$config['full_tag_open'] = '<div class="locPage">';
			$config['full_tag_close'] = '</div>';
			$config['base_url'] = '';
			$config['uri_segment'] = 3;
			$config['total_rows'] = count($advanceSearchArr);
			$config['per_page'] = $limit;
			$this->pagination->initialize($config);
			//当前查找的记录范围
			$advanceSearchLimitSql = $advanceSearchSql." LIMIT ".$offset.",".$limit;
			$advanceSearchLimitObj = $this->db->query($advanceSearchLimitSql);
			$advanceSearchArr = $advanceSearchLimitObj->result_array();
		}
		$this->smarty->assign('count', $count-$offset);
		$this->smarty->assign('testitemLimitArr', $testitemLimitArr);
		$this->smarty->assign('advanceSearchArr', $advanceSearchArr);
		$this->smarty->assign('item', '高级查询');
		$this->smarty->assign('title', '高级查询');
		$this->smarty->display("advancedsearch.tpl");
	}
	
	//转换从数据库根据id,另一项项取出的数组，赋给页面下拉列表
	protected function array_switch($var1,$var2,$var3)
	{
		$arr = array(""=>$var3);
		foreach($var1 as $value)
		{
			$arr = $arr+array($value['id']=>$value[$var2]);
		}
		return $arr;
	}
}