<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');
class Packing extends CW_Controller
{
	function __consruct()
	{
		parent::construct();
		$this->load->library("zip");
	}
	
	public function index($offset = 0, $limit = 30)
	{
		$hourList = array(''=>'');
		for($i=0;$i<=23;$i++)
		{
			$arr = array($i=>$i);
			$hourList = array_merge_recursive($hourList,$arr);
		}
		$this->smarty->assign("hourList", $hourList);
		
		$minuteList = array(''=>'');
		for($i=0;$i<=59;$i++)
		{
			$arr = array($i=>$i);
			$minuteList = array_merge_recursive($minuteList,$arr);
		}
		$this->smarty->assign("minuteList", $minuteList);
		
		$testResult = array(
			""=>"(ALL)",
			"PASS"=>"PASS",
			"FAIL"=>"FAIL",
			"UNTESTED"=>"UNTESTED"
		);
		$this->smarty->assign("testresult",$testResult);
		$packerObject = $this->db->query("SELECT tr.employeeid,tr.fullname FROM tester tr 
										  JOIN status ss ON tr.status = ss.id
										  JOIN tester_section tn ON tr.tester_section = tn.id
										  AND ss.statusname = 'active'
										  AND tn.name = 'PACK'");
		$packerArray  = $packerObject->result_array();
		$packer = array(""=>"(ALL)");
		foreach ($packerArray as $value)
		{
			$arr = array($value["employeeid"]=>$value["fullname"]);
			$packer = $packer+$arr;
		}
		$this->smarty->assign("packer",$packer);
		$producttypeObject = $this->db->query("SELECT id,name FROM producttype");
		$producttypeArray = $producttypeObject->result_array();
		$producttype = array(""=>"");
		foreach($producttypeArray as $value)
		{
			$arr = array($value['id'] => $value['name']);
			$producttype = $producttype + $arr;
		}
		$this->smarty->assign("producttype",$producttype);
		
		$timeFrom1 = $this->input->post("timeFrom1");
		if($timeFrom1 == "")
		{
			$timeFrom1 = date("Y-m-d", strtotime("-1 months"));
		}
		$timeFrom2 = $this->input->post("timeFrom2");
		if($timeFrom2 == "")
		{
			$timeFrom2 = "0";
		}
		$timeFrom3 = $this->input->post("timeFrom3");
		if($timeFrom3 == "")
		{
			$timeFrom3 = "0";
		}
		$timeTo1 = $this->input->post("timeTo1");
		if($timeTo1 == "")
		{
			$timeTo1 = date("Y-m-d");
		}
		$timeTo2 = $this->input->post("timeTo2");
		if($timeTo2 == "")
		{
			$timeTo2 = "0";
		}
		$timeTo3 = $this->input->post("timeTo3");
		if($timeTo3 == "")
		{
			$timeTo3 = "0";
		}
		$timeFrom = $timeFrom1." ".$timeFrom2.":".$timeFrom3;
		$timeTo = $timeTo1." ".$timeTo2.":".$timeTo3;
		$packBox = $this->input->post("packbox");
		$productSn = $this->input->post("productsn");
		$producttype = $this->input->post("producttype");
		$orderNum = $this->input->post("ordernum");
		$packer = $this->input->post("packer");
		$testResult = $this->input->post("testresult");
		$timeConditionSql = " WHERE (pt.packingtime >= '".$timeFrom."' AND pt.packingtime <= '".$timeTo."')";
		if($timeFrom != "1900-01-01 00:00" || $timeTo != "2999-01-01 00:00")
		{
			$timeConditionSql = " WHERE pt.packingtime >= '".$timeFrom."' AND pt.packingtime <= '".$timeTo."'";
		}
		$packBoxSql = "";
		$producttypeSql = "";
		$productSnSql = "";
		$orderNumSql = "";
		$packerSql = "";
		$testResultSql = "";
		if($packBox != null)
		{
			$packBoxSql = " AND pt.boxsn LIKE '%".$packBox."%'";
		}
		if($producttype != null)
		{
			$producttypeSql = " AND pe.name LIKE '%".$producttype."%'";
		}
		if($productSn !=null)
		{
			$productSnSql = " AND pt.productsn LIKE '%".$productSn."%'";
		}
		if($orderNum != null)
		{
			$orderNumSql = " AND pt.ordernum LIKE '%".$orderNum."%'";
		}
		if($packer != null)
		{
			$packerSql = " AND tr.employeeid = '".$packer."'";
		}
		if($testResult != null)
		{
			$testResultSql = " AND pt.result = '".$testResult."'";
		}
		$packingTotalResultSql = "SELECT DISTINCT pt.id,pt.packingtime,pt.boxsn,pt.productsn,pe.name,pt.ordernum,tr.fullname AS packername,pt.result 
		                          FROM packingresult pt
		                          JOIN tester tr ON pt.packer=tr.employeeid 
								  LEFT JOIN producttestinfo po ON pt.productsn = po.sn
								  LEFT JOIN producttype pe ON po.productType = pe.id
							 	  ".$timeConditionSql.$packBoxSql.$producttypeSql.$productSnSql.$orderNumSql.$packerSql.$testResultSql." 
							      ORDER BY pt.packingtime DESC";
		$packingTotalResultObject = $this->db->query($packingTotalResultSql);
		$packingTotalResultArray = $packingTotalResultObject->result_array();
		$totalcount = count($packingTotalResultArray);
		$this->load->library('pagination');
		$config['full_tag_open'] = '<div class="locPage">';
		$config['full_tag_close'] = '</div>';
		$config['base_url'] = '';
		$config['uri_segment'] = 3;
		$config['total_rows'] = count($packingTotalResultArray);
		$config['per_page'] = $limit;
		$this->pagination->initialize($config);
		$packingResultSql = $packingTotalResultSql." LIMIT ".$offset.",".$limit;
		$packingResultObject = $this->db->query($packingResultSql);
		$packingResultArray = $packingResultObject->result_array();
		$count = count($packingResultArray);
		//取得所有测试项
		$testItemObject = $this->db->query("SELECT id,name FROM testitem");
		$testItemArray = $testItemObject->result_array();
		$this->smarty->assign("testItemArray",$testItemArray);
		$testitemcount = count($testItemArray);
		$this->smarty->assign("testitemcount",$testitemcount);

		$this->smarty->assign("timeFrom1",$timeFrom1);
		$this->smarty->assign("timeFrom2", $timeFrom2);
		$this->smarty->assign("timeFrom3", $timeFrom3);
		$this->smarty->assign("timeTo1",$timeTo1);
		$this->smarty->assign("timeTo2", $timeTo2);
		$this->smarty->assign("timeTo3", $timeTo3);
		
		$this->smarty->assign("totalcount",$totalcount);
		$this->smarty->assign("count",$totalcount-$offset);
		$this->smarty->assign("packingResultArray",$packingResultArray);
		$this->smarty->assign("item","包装记录");
		$this->smarty->assign("title","包装记录");
		$this->smarty->display("packing.tpl");
	}
	
	//服务器包装部分点击产品序列号，查看详情
	public function detail($var)
	{
		//取得生产厂家名称
		$producterUrl = base_url()."/resource/producter.txt";
		$producter = file_get_contents($producterUrl);
		$producter = iconv("gbk", "utf-8", $producter);
		$this->smarty->assign("producter",$producter);
		
		//取得产品序列号,包装标志位
		$snObj = $this->db->query("SELECT pt.productsn,pt.tag FROM packingresult pt WHERE pt.id = '".$var."'");
		$packTag = $snObj->first_row()->tag;
		$productsn = $snObj->first_row()->productsn;
		
		$this->smarty->assign("productsn",$productsn);
		
		//获取vna基本信息
		$basicInfoObject = $this->db->query("SELECT DISTINCT po.tag1,po.testTime,tn.name as teststationname,po.equipmentSn,pe.name,tr.fullname AS tester,po.result
											FROM producttestinfo po 
											JOIN testitemresult tt ON tt.productTestInfo = po.id 
											JOIN testitemmarkvalue te ON te.testItemResult = tt.id
											JOIN producttype pe ON po.productType = pe.id
											JOIN tester tr ON po.tester = tr.id
											JOIN teststation tn ON po.testStation = tn.id
											WHERE po.sn = '".$productsn."'
											AND po.tag1 = 1
											AND po.tag = '".$packTag."'");
		$basicInfoArray = $basicInfoObject->result_array();
		if(count($basicInfoArray) != 0)
		{
			$basicInfoArray = $basicInfoArray[0];
		}
		else
		{
			$basicInfoArray = array();
		}
		$this->smarty->assign("basicInfoArray",$basicInfoArray);
		
		//获取vna测试详情
		$testDetailObject = $this->db->query("SELECT tm.name,tt.testResult,tt.img,te.value,te.mark
										FROM producttestinfo po 
										JOIN testitemresult tt ON tt.productTestInfo = po.id
										JOIN testitemmarkvalue te ON te.testItemResult = tt.id
										JOIN testitem tm ON tt.testItem = tm.id
										WHERE po.sn = '".$productsn."'
										AND po.tag1 = 1
										AND po.tag = '".$packTag."'");
		$testDetailArray = $testDetailObject->result_array();
		//结果数组
		$result = array();
		//测试项数组
		$testitem = array();
		if(count($testDetailArray) != 0)
		{
			foreach($testDetailArray as $value)
			{
				if(!in_array($value['name'], $testitem))
				{
					$arr = array($value['img'],array(array($value['mark'],$value['value'],$value['testResult'])));
					$result[$value['name']] = $arr;
					array_push($testitem,$value['name']);
				}
				else
				{
					$arr = array($value['mark'],$value['value'],$value['testResult']);
					array_push($result[$value['name']][1],$arr);
				}
			}
		}
		$this->smarty->assign("result",$result);
		
		//获取PIM基本信息
		$pimbasicInfoObject = $this->db->query("SELECT pl.name,pm.col12,tr.fullname as employeeid,MAX(pp.test_time) AS testtime,pp.upload_date, pm.model, pm.ser_num, pm.result
												FROM pim_label pl
												JOIN pim_ser_num pm ON pm.pim_label = pl.id
												JOIN pim_ser_num_group pp ON pp.pim_ser_num = pm.id
												JOIN pim_ser_num_group_data pa ON pa.pim_ser_num_group = pp.id
												LEFT JOIN tester tr on pm.col13 = tr.id
												WHERE pm.ser_num = '".$productsn."'
												AND pm.islatest = 1");

		$pimbasicInfoArray = $pimbasicInfoObject->result_array();
		
		$pimbasicInfo = array();
		$pimtestResult = "";
		$pimmaxdataArray = array();
		
		if(count($pimbasicInfoArray) != 0 && $pimbasicInfoArray[0]["testtime"] != "")
		{
			$pimbasicInfo = $pimbasicInfoArray[0];
            $pimtestResult = $pimbasicInfo['result'];
            if($pimtestResult) {
                $pimtestResult = "合格";
            } else {
                $pimtestResult = "不合格";
            }
            //取得所有值
			$pimdataObject = $this->db->query("SELECT pp.test_time,pa.value
									  FROM pim_label pl
									  JOIN pim_ser_num pm ON pm.pim_label = pl.id
									  JOIN pim_ser_num_group pp ON pp.pim_ser_num = pm.id
									  JOIN pim_ser_num_group_data pa ON pa.pim_ser_num_group = pp.id
									  WHERE pm.ser_num = '".$productsn."'");
			$pimdataArray = $pimdataObject->result_array();
			//对数据处理，将同一测试时间的数据放到一组
			$pim_testtime = array();
			$pimdataFormart = array();
			foreach($pimdataArray as $value)
			{
				if(!in_array($value["test_time"], $pim_testtime))
				{
					$arr = array($value["value"]);
					$pimdataFormart[$value["test_time"]] = $arr;
					array_push($pim_testtime,$value["test_time"]);
				}
				else
				{
					array_push($pimdataFormart[$value["test_time"]],$value["value"]);
				}
			}

			//取得各组的最大值
			$pimmaxdataObject = $this->db->query("SELECT pp.test_time,pp.upload_date,MAX(pa.value) AS value FROM pim_ser_num pm
											JOIN pim_label pl ON pm.pim_label=pl.id
											JOIN pim_ser_num_group pp ON pp.pim_ser_num = pm.id
											JOIN pim_ser_num_group_data pa ON pa.pim_ser_num_group = pp.id
											AND pm.ser_num = '".$productsn."'
											GROUP BY pp.test_time");
			$pimmaxdataArray = $pimmaxdataObject->result_array();
		}
		$this->smarty->assign("pimbasicInfo",$pimbasicInfo);
		$this->smarty->assign("pimtestResult",$pimtestResult);
		$this->smarty->assign("pimmaxdataArray",$pimmaxdataArray);

		$this->smarty->display("vna_pim_detail.tpl");
	}
	
	//vna测试数据点击产品序列号，查看详情
	public function detail_vna($var)
	{
		//取得生产厂家名称
		$producterUrl = base_url()."resource/producter.txt";
		$producter = @file_get_contents($producterUrl);
		if($producter == FALSE)
		{
			$producter = "未找到配置文件producter.txt";
		}
		else
		{
			$producter = iconv("gbk", "utf-8", $producter);
		}
		$this->smarty->assign("producter",$producter);
		$productsnObj = $this->db->query("SELECT sn ,tag1 FROM producttestinfo WHERE id = $var");
		$productsn = $productsnObj->first_row()->sn;
		$tag1 = $productsnObj->first_row()->tag1;
		
		$this->smarty->assign("productsn",$productsn);
		
		//获取vna基本信息
		$basicInfoObject = $this->db->query("SELECT DISTINCT po.testTime,tn.name as teststationname,po.equipmentSn,pe.name,tr.fullname AS tester,po.result,po.tag1
											FROM producttestinfo po 
											JOIN testitemresult tt ON tt.productTestInfo = po.id 
											JOIN testitemmarkvalue te ON te.testItemResult = tt.id
											JOIN producttype pe ON po.productType = pe.id
											JOIN tester tr ON po.tester = tr.id
											JOIN teststation tn ON po.testStation = tn.id
											WHERE po.id = '".$var."'");
		$basicInfoArray = $basicInfoObject->result_array();
		if(count($basicInfoArray) != 0)
		{
			$basicInfoArray = $basicInfoArray[0];
		}
		else
		{
			$basicInfoArray = array();
		}
		$this->smarty->assign("basicInfoArray",$basicInfoArray);
		
		
		//获取vna测试详情
		$testDetailObject = $this->db->query("SELECT tm.name,tt.testResult,tt.img,te.value,te.mark
										FROM producttestinfo po 
										JOIN testitemresult tt ON tt.productTestInfo = po.id
										JOIN testitemmarkvalue te ON te.testItemResult = tt.id
										JOIN testitem tm ON tt.testItem = tm.id
										WHERE po.id = '".$var."'");
		$testDetailArray = $testDetailObject->result_array();
		//结果数组
		$result = array();
		//测试项数组
		$testitem = array();
		if(count($testDetailArray) != 0)
		{
			foreach($testDetailArray as $value)
			{
				if(!in_array($value['name'], $testitem))
				{
					$arr = array($value['img'],array(array($value['mark'],$value['value'],$value['testResult'])));
					$result[$value['name']] = $arr;
					array_push($testitem,$value['name']);
				}
				else
				{
					$arr = array($value['mark'],$value['value'],$value['testResult']);
					array_push($result[$value['name']][1],$arr);
				}
			}
		}
		$this->smarty->assign("result",$result);

        //获取耐压测试基本信息
        $hiPotInfoObj = $this->db->query("SELECT * 
                                          FROM hi_pot_result hpr
                                          WHERE hpr.sn = '".$productsn."'
                                          AND hpr.finalresult = 1
                                          ORDER BY hpr.id DESC");
        $hiPotInfoArr = $hiPotInfoObj->result_array();
        $hiPotResult = array();
        if(count($hiPotInfoArr) == 0) {
            $hiPotResult["result"] = "";
        } else {
            $hiPotResult = $hiPotInfoArr[0];
            if($hiPotResult["result"] == 1) {
                $hiPotResult["result"] = "合格";
            } else {
                $hiPotResult["result"] = "不合格";
            }
        }
        $this->smarty->assign("hiPotResult", $hiPotResult);

		//获取PIM基本信息
		$pimbasicInfoObject = $this->db->query("SELECT pl.name,pm.col12,tr.fullname as employeeid,MAX(pp.test_time) AS testtime,pp.upload_date, pm.model, pm.ser_num, pm.result
												FROM pim_label pl
												JOIN pim_ser_num pm ON pm.pim_label = pl.id
												JOIN pim_ser_num_group pp ON pp.pim_ser_num = pm.id
												JOIN pim_ser_num_group_data pa ON pa.pim_ser_num_group = pp.id
												LEFT JOIN tester tr ON pm.col13 = tr.id
												WHERE pm.ser_num = '".$productsn."'
												AND pm.islatest = 1");

		$pimbasicInfoArray = $pimbasicInfoObject->result_array();

		$pimbasicInfo = array();
		$pimtestResult = "";
		$pimmaxdataArray = array();
		
		//加$pimbasicInfoArray[0]["testtime"] != ""条件，因为上面的sql语句执行结果总不为空
		if(count($pimbasicInfoArray) != 0 && $pimbasicInfoArray[0]["testtime"] != "")
		{
			$pimbasicInfo = $pimbasicInfoArray[0];
			$pim_result = $pimbasicInfo["result"];
			//判断是否合格，0代表不合格，1代表合格
			if($pim_result)
			{
				$pimtestResult = "合格";
			}
			else
			{
				$pimtestResult = "不合格";
			}
			//取得各组的最大值
			$pimmaxdataObject = $this->db->query("SELECT pp.test_time,pp.upload_date,MAX(pa.value) AS value FROM pim_ser_num pm
											JOIN pim_label pl ON pm.pim_label=pl.id
											JOIN pim_ser_num_group pp ON pp.pim_ser_num = pm.id
											JOIN pim_ser_num_group_data pa ON pa.pim_ser_num_group = pp.id
											AND pm.ser_num = '".$productsn."'
											GROUP BY pp.test_time");
			$pimmaxdataArray = $pimmaxdataObject->result_array();
		}
		$this->smarty->assign("pimbasicInfo",$pimbasicInfo);
		$this->smarty->assign("pimtestResult",$pimtestResult);
		$this->smarty->assign("pimmaxdataArray",$pimmaxdataArray);

		$this->smarty->display("vna_pim_detail.tpl");
	}	
	
	//pim测试数据点击产品序列号，查看详情
	public function detail_pim($var)
	{
		//取得生产厂家名称
		$producterUrl = base_url()."resource/producter.txt";
		$producter = @file_get_contents($producterUrl);
		if($producter == FALSE)
		{
			$producter = "未找到配置文件producter.txt";
		}
		else
		{
			$producter = iconv("gbk", "utf-8", $producter);
		}
		$this->smarty->assign("producter",$producter);
		
		//取得序列号
		$productSnObj = $this->db->query("SELECT pm.ser_num FROM pim_ser_num pm WHERE pm.id = '".$var."'");
		$productSnArr = $productSnObj->result_array();
		$productsn = $productSnArr[0]['ser_num'];
		$this->smarty->assign("productsn",$productsn);
		
		//获取vna基本信息
		$basicInfoObject = $this->db->query("SELECT DISTINCT po.tag1,po.testTime,tn.name as teststationname,po.equipmentSn,pe.name,tr.fullname AS tester,po.result
											FROM producttestinfo po 
											JOIN testitemresult tt ON tt.productTestInfo = po.id 
											JOIN testitemmarkvalue te ON te.testItemResult = tt.id
											JOIN producttype pe ON po.productType = pe.id
											JOIN tester tr ON po.tester = tr.id
											JOIN teststation tn ON po.testStation = tn.id
											AND po.sn = '".$productsn."'
											AND po.tag1 in (1,3)");
		$basicInfoArray = $basicInfoObject->result_array();
		if(count($basicInfoArray) != 0)
		{
			$basicInfoArray = $basicInfoArray[0];
		}
		else
		{
			$basicInfoArray = array();
		}
		$this->smarty->assign("basicInfoArray",$basicInfoArray);
		
		//获取vna测试详情
		$testDetailObject = $this->db->query("SELECT tm.name,tt.testResult,tt.img,te.value,te.mark
										FROM producttestinfo po 
										JOIN testitemresult tt ON tt.productTestInfo = po.id
										JOIN testitemmarkvalue te ON te.testItemResult = tt.id
										JOIN testitem tm ON tt.testItem = tm.id
										WHERE po.sn = '".$productsn."'
										AND po.tag1 = '1'");
		$testDetailArray = $testDetailObject->result_array();
		//结果数组
		$result = array();
		//测试项数组
		$testitem = array();
		if(count($testDetailArray) != 0)
		{
			foreach($testDetailArray as $value)
			{
				if(!in_array($value['name'], $testitem))
				{
					$arr = array($value['img'],array(array($value['mark'],$value['value'],$value['testResult'])));
					$result[$value['name']] = $arr;
					array_push($testitem,$value['name']);
				}
				else
				{
					$arr = array($value['mark'],$value['value'],$value['testResult']);
					array_push($result[$value['name']][1],$arr);
				}
			}
		}
		$this->smarty->assign("result",$result);

        //获取耐压测试基本信息
        $hiPotInfoObj = $this->db->query("SELECT * 
                                          FROM hi_pot_result hpr
                                          WHERE hpr.sn = '".$productsn."'
                                          AND hpr.finalresult = 1
                                          ORDER BY hpr.id DESC");
        $hiPotInfoArr = $hiPotInfoObj->result_array();
        $hiPotResult = array();
        if(count($hiPotInfoArr) == 0) {
            $hiPotResult["result"] = "";
        } else {
            $hiPotResult = $hiPotInfoArr[0];
            if($hiPotResult["result"] == 1) {
                $hiPotResult["result"] = "合格";
            } else {
                $hiPotResult["result"] = "不合格";
            }
        }
        $this->smarty->assign("hiPotResult", $hiPotResult);

        //获取PIM基本信息
		$pimbasicInfoObject = $this->db->query("SELECT pl.name,pm.col12,tr.fullname as employeeid,MAX(pp.test_time) AS testtime,pp.upload_date, pm.model, pm.ser_num, pm.result
												FROM pim_label pl
												JOIN pim_ser_num pm ON pm.pim_label = pl.id
												JOIN pim_ser_num_group pp ON pp.pim_ser_num = pm.id
												JOIN pim_ser_num_group_data pa ON pa.pim_ser_num_group = pp.id
												LEFT JOIN tester tr ON pm.col13 = tr.id
												WHERE pm.id = '".$var."'");
		$pimbasicInfoArray = $pimbasicInfoObject->result_array();
		
		$pimbasicInfo = array();
		$pimtestResult = "";
		$pimmaxdataArray = array();
		if(count($pimbasicInfoArray) != 0)
		{
			$pimbasicInfo = $pimbasicInfoArray[0];
			$pim_result = $pimbasicInfo["result"];
			//判断是否合格，0代表不合格，1代表合格
			if($pim_result)
			{
				$pimtestResult = "合格";
			}
			else
			{
				$pimtestResult = "不合格";
			}
			//取得各组的最大值
			$pimmaxdataObject = $this->db->query("SELECT pp.test_time,pp.upload_date,MAX(pa.value) AS value FROM pim_ser_num pm
											JOIN pim_label pl ON pm.pim_label=pl.id
											JOIN pim_ser_num_group pp ON pp.pim_ser_num = pm.id
											JOIN pim_ser_num_group_data pa ON pa.pim_ser_num_group = pp.id
											AND pm.ser_num = '".$productsn."'
											GROUP BY pp.test_time");
			$pimmaxdataArray = $pimmaxdataObject->result_array();
		}
		$this->smarty->assign("pimbasicInfo",$pimbasicInfo);
		$this->smarty->assign("pimtestResult",$pimtestResult);
		$this->smarty->assign("pimmaxdataArray",$pimmaxdataArray);

		$this->smarty->display("vna_pim_detail.tpl");
	}

	//查看详情
	public function viewDetail($id){
	    $idType = isset($_GET['type']) ? $_GET['type'] : "";
        //取得生产厂家名称
        $producterUrl = base_url()."resource/producter.txt";
        $producter = @file_get_contents($producterUrl);
        if($producter == FALSE)
        {
            $producter = "未找到配置文件producter.txt";
        }
        else
        {
            $producter = iconv("gbk", "utf-8", $producter);
        }
        $this->smarty->assign("producter",$producter);

        //取得序列号
        $productsn = "";
        if($idType == "VNA") {

        } else if ($idType == "PIM") {
            $productSnObj = $this->db->query("SELECT pm.ser_num FROM pim_ser_num pm WHERE pm.id = '".$id."'");
            $productSnArr = $productSnObj->result_array();
            $productsn = $productSnArr[0]['ser_num'];
        } else {
            $productSnObj = $this->db->query("SELECT * 
                                          FROM hi_pot_result hpr
                                          WHERE hpr.id = '".$id."'");
            $productSnArr = $productSnObj->result_array();
            $productsn = $productSnArr[0]['sn'];
        }

        $this->smarty->assign("productsn",$productsn);

        //获取vna基本信息
        $basicInfoObject = $this->db->query("SELECT DISTINCT po.tag1,po.testTime,tn.name as teststationname,po.equipmentSn,pe.name,tr.fullname AS tester,po.result
											FROM producttestinfo po 
											JOIN testitemresult tt ON tt.productTestInfo = po.id 
											JOIN testitemmarkvalue te ON te.testItemResult = tt.id
											JOIN producttype pe ON po.productType = pe.id
											JOIN tester tr ON po.tester = tr.id
											JOIN teststation tn ON po.testStation = tn.id
											AND po.sn = '".$productsn."'
											AND po.tag1 in (1,3)");
        $basicInfoArray = $basicInfoObject->result_array();
        if(count($basicInfoArray) != 0)
        {
            $basicInfoArray = $basicInfoArray[0];
        }
        else
        {
            $basicInfoArray = array();
        }
        $this->smarty->assign("basicInfoArray",$basicInfoArray);

        //获取vna测试详情
        $testDetailObject = $this->db->query("SELECT tm.name,tt.testResult,tt.img,te.value,te.mark
										FROM producttestinfo po 
										JOIN testitemresult tt ON tt.productTestInfo = po.id
										JOIN testitemmarkvalue te ON te.testItemResult = tt.id
										JOIN testitem tm ON tt.testItem = tm.id
										WHERE po.sn = '".$productsn."'
										AND po.tag1 = '1'");
        $testDetailArray = $testDetailObject->result_array();
        //结果数组
        $result = array();
        //测试项数组
        $testitem = array();
        if(count($testDetailArray) != 0)
        {
            foreach($testDetailArray as $value)
            {
                if(!in_array($value['name'], $testitem))
                {
                    $arr = array($value['img'],array(array($value['mark'],$value['value'],$value['testResult'])));
                    $result[$value['name']] = $arr;
                    array_push($testitem,$value['name']);
                }
                else
                {
                    $arr = array($value['mark'],$value['value'],$value['testResult']);
                    array_push($result[$value['name']][1],$arr);
                }
            }
        }
        $this->smarty->assign("result",$result);

        //获取耐压测试基本信息
        $hiPotInfoObj = $this->db->query("SELECT * 
                                          FROM hi_pot_result hpr
                                          WHERE hpr.sn = '".$productsn."'
                                          AND hpr.finalresult = 1
                                          ORDER BY hpr.id DESC");
        $hiPotInfoArr = $hiPotInfoObj->result_array();
        $hiPotResult = array();
        if(count($hiPotInfoArr) == 0) {
            $hiPotResult["result"] = "";
        } else {
            $hiPotResult = $hiPotInfoArr[0];
            if($hiPotResult["result"] == 1) {
                $hiPotResult["result"] = "合格";
            } else {
                $hiPotResult["result"] = "不合格";
            }
        }
        $this->smarty->assign("hiPotResult", $hiPotResult);

        //获取PIM基本信息
        $pimbasicInfoObject = $this->db->query("SELECT pl.name,pm.col12,tr.fullname as employeeid,pp.test_time AS testtime,pp.upload_date, pm.model, pm.ser_num, pm.result
												FROM pim_label pl
												JOIN pim_ser_num pm ON pm.pim_label = pl.id
												JOIN pim_ser_num_group pp ON pp.pim_ser_num = pm.id
												JOIN pim_ser_num_group_data pa ON pa.pim_ser_num_group = pp.id
												LEFT JOIN tester tr ON pm.col13 = tr.id
												WHERE pm.ser_num = '".$productsn."'
												AND pm.islatest = 1");
        $pimbasicInfoArray = $pimbasicInfoObject->result_array();

        $pimbasicInfo = array();
        $pimtestResult = "";
        $pimmaxdataArray = array();
        if(count($pimbasicInfoArray) != 0)
        {
            $pimbasicInfo = $pimbasicInfoArray[0];
            $pim_result = $pimbasicInfo["result"];
            //判断是否合格，0代表不合格，1代表合格
            if($pim_result)
            {
                $pimtestResult = "合格";
            }
            else
            {
                $pimtestResult = "不合格";
            }
            //取得各组的最大值
            $pimmaxdataObject = $this->db->query("SELECT pp.test_time,pp.upload_date,MAX(pa.value) AS value FROM pim_ser_num pm
											JOIN pim_label pl ON pm.pim_label=pl.id
											JOIN pim_ser_num_group pp ON pp.pim_ser_num = pm.id
											JOIN pim_ser_num_group_data pa ON pa.pim_ser_num_group = pp.id
											AND pm.ser_num = '".$productsn."'
											GROUP BY pp.test_time");
            $pimmaxdataArray = $pimmaxdataObject->result_array();
        }
        $this->smarty->assign("pimbasicInfo",$pimbasicInfo);
        $this->smarty->assign("pimtestResult",$pimtestResult);
        $this->smarty->assign("pimmaxdataArray",$pimmaxdataArray);

        $this->smarty->display("vna_pim_detail.tpl");
    }

	//导出报告
	public function export()
	{
		set_time_limit(0);
		//取得所有测试项总数（页面隐藏控件传过来）
		$testitemcount = $this->input->post("testitemcount");
		//遍历所有页面上vna测试项的状态，检查用户是否选中，并放入数组。
		$selectTestItemArray = array();
		for($i=1;$i <= $testitemcount;$i++)
		{
			if($this->input->post("testitem".$i) != "")
			{
				array_push($selectTestItemArray,$this->input->post("testitem".$i));
			}
		}

		//根据用户所选测试项--获得选中产品测试项的id,name
		$testItemSql1 = $this->sqlColumnInArray($selectTestItemArray, "id");
		$testItemSql = "SELECT id,name FROM testitem WHERE ".$testItemSql1;
		$testitemObject = $this->db->query($testItemSql);
		$testitemArray = $testitemObject->result_array();
		
		//取得PIM测试的选中状态
		$testitempim = $this->input->post("testitempim");
		
		//根据当前用户填选状况查到满足情况的SN
		$timeFrom1 = $this->input->post("timeFrom1");
		if($timeFrom1 == "")
		{
			$timeFrom1 = "1900-01-01";
		}
		$timeFrom2 = $this->input->post("timeFrom2");
		if($timeFrom2 == "")
		{
			$timeFrom2 = "00";
		}
		$timeFrom3 = $this->input->post("timeFrom3");
		if($timeFrom3 == "")
		{
			$timeFrom3 = "00";
		}
		$timeTo1 = $this->input->post("timeTo1");
		if($timeTo1 == "")
		{
			$timeTo1 = "2999-01-01";
		}
		$timeTo2 = $this->input->post("timeTo2");
		if($timeTo2 == "")
		{
			$timeTo2 = "23";
		}
		$timeTo3 = $this->input->post("timeTo3");
		if($timeTo3 == "")
		{
			$timeTo3 = "59";
		}
		$timeFrom = $timeFrom1." ".$timeFrom2.":".$timeFrom3;
		$timeTo = $timeTo1." ".$timeTo2.":".$timeTo3;
		$packBox = $this->input->post("packbox");
		$productSn = $this->input->post("productsn");
		$producttype = $this->input->post("producttype");
		$orderNum = $this->input->post("ordernum");
		$packer = $this->input->post("packer");
		$testResult = $this->input->post("testresult");
		$timeConditionSql = " WHERE (pt.packingtime >= '".$timeFrom."' AND pt.packingtime <= '".$timeTo."')";
		if($timeFrom != "1900-01-01 00:00" || $timeTo != "2999-01-01 00:00")
		{
			$timeConditionSql = " WHERE pt.packingtime >= '".$timeFrom."' AND pt.packingtime <= '".$timeTo."'";
		}
		$packBoxSql = "";
		$producttypeSql = "";
		$productSnSql = "";
		$orderNumSql = "";
		$packerSql = "";
		$testResultSql = "";
		if($packBox != null)
		{
			$packBoxSql = " AND pt.boxsn LIKE '%".$packBox."%'";
		}
		if($producttype != null)
		{
			$producttypeSql = " AND pe.id = '".$producttype."'";
		}
		if($productSn !=null)
		{
			$productSnSql = " AND pt.productsn LIKE '%".$productSn."%'";
		}
		if($orderNum != null)
		{
			$orderNumSql = " AND pt.ordernum LIKE '%".$orderNum."%'";
		}
		if($packer != null)
		{
			$packerSql = " AND ps.employeeId = '".$packer."'";
		}
		if($testResult != null)
		{
			$testResultSql = " AND pt.result = '".$testResult."'";
		}
		$packingTotalSnSql = "SELECT DISTINCT pt.id,pt.productsn,pt.boxsn,pt.result,pt.tag
		                          FROM packingresult pt 
		                          JOIN tester tr ON pt.packer=tr.employeeid 
								  LEFT JOIN producttestinfo po ON pt.productsn = po.sn
								  LEFT JOIN producttype pe ON po.productType = pe.id
							 	  ".$timeConditionSql.$packBoxSql.$producttypeSql.$productSnSql.$orderNumSql.$packerSql.$testResultSql." 
							      ORDER BY pt.packingtime DESC";
		$packingTotalSnObject = $this->db->query($packingTotalSnSql);
		$packingTotalSnArray= $packingTotalSnObject->result_array();
		
		//遍历得到的序列号数组
		if(count($packingTotalSnArray) == 0)
		{
			$this->_returnUploadFailed("查询数据为空");
			return;
		}
		else
		{
			date_default_timezone_set('Asia/Shanghai');
			$dateStamp = date("YmdHis");
			$dateInReport = date("Y年m月d日");
			
			if(PHP_OS == 'WINNT')
			{
				$slash = "\\";
				$downloadRoot = getcwd().$slash."assets".$slash."downloadedSource";
			}
			else
			{
				$this->_returnUploadFailed("错误的服务器操作系统");
				return;
			}
			
			//创建文件下载的根目录downloadedSource
			if(file_exists($downloadRoot) && is_dir($downloadRoot))
			{
				//do nothing
			}
			else
			{
				if(mkdir($downloadRoot))
				{
				}
				else
				{
					$this->_returnUploadFailed("文件下载目录创建失败");
					return;
				}
			}
			//创建当前下载的文件夹
			$currdownloadRoot = $downloadRoot.$slash.$dateStamp;
			if(file_exists($currdownloadRoot) && is_dir($currdownloadRoot))
			{
				//do noting
			}
			else
			{
				if(mkdir($currdownloadRoot))
				{
					//拷贝公司logo
					$logoRoot = getcwd().$slash."resource".$slash."img".$slash."logo.png";
					if(file_exists($logoRoot))
					{
						copy($logoRoot,$currdownloadRoot.$slash."logo.png");
					}
					else
					{
						$this->_returnUploadFailed($logoRoot."公司logo不存在");
						return;
					}
				}
				else
				{
					$this->_returnUploadFailed("创建下载根目录时出错");
					return;
				}
			}
			//获取生产厂家名称
			$producterRoot = getcwd().$slash."resource".$slash."producter.txt";
			
			if(file_exists($producterRoot))
			{
				$producterName = file_get_contents($producterRoot);
			}
			else
			{
				$this->_returnUploadFailed($producterRoot."未找到");
				return;
			}
			
			//创建html文件，先写index.html
			$indexHandle = fopen($currdownloadRoot.$slash."index.html", "a");
			fwrite($indexHandle, '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
								  <html xmlns="http://www.w3.org/1999/xhtml">
									<head>
										<meta http-equiv="content-type" content="text/html;charset=utf-8">
										<style type="text/css">
											body{border:0px;margin:0px}
											a{text-decoration:none;}
											.container{width:1024px;margin:0px auto;border:1px solid black;padding:15px;}
											img{width:60px;height:30px;}
											table{border-collapse:collapse;}
											table, td, th{border:1px solid black;}
										</style>
									</head>
										<body>
											<div class="container">
												<div class="top">
													<div style="float:left;width:45%;"><img src="./logo.png"/></div>
													<div style="font-weight:bold;font-size:28px;text-align:left">质量报告</div>
												</div>
												<div style="margin-top:30px;margin-bottom:10px;">
													<div style="text-align:left;padding-left:70%;">
														<span>生产厂家：'.iconv("gbk", "utf-8", $producterName).'</span>
													</div>
													<div style="text-align:left;padding-left:70%;">
														<span>报告日期：'.$dateInReport.'</span>
													</div>
												</div>'
												);
			fwrite($indexHandle,'<div class="content" style="padding-left:10px;padding-right:10px;font-size:13px;">
								');
			fwrite($indexHandle, '<table style="width:100%;"><tr><th>序号</th><th>产品型号</th><th>包装箱</th><th>产品序列号</th><th>检测结果</th>');
			//index.html中写入表头<th>部分的测试项--用户所选
			if(count($testitemArray) == 0 && $testitempim == "")
			{
				//do noting
			}
			else
			{
				//循环写入vna测试项--用户所选
				foreach($testitemArray as $value)
				{
					fwrite($indexHandle, '<th>'.$value['name'].'</th>');
				}
				//写入pim
				if($testitempim == "")
				{
					
				}
				else
				{
					fwrite($indexHandle, "<th>PIM</th>");
				}
			}
			fwrite($indexHandle, "</tr>");
			//循环得到的序列号数组sn数组
			foreach($packingTotalSnArray as $key=>$value)
			{
				fwrite($indexHandle, '<tr><td>'.($key+1).'</td>');
				//取得产品序列号
				$sn = $value['productsn'];
				//取得包装箱号
				$boxSn = $value['boxsn'];
				//取得测试结果
				$result = $value['result'];
				//取得标志位
				$packTag = $value['tag'];

				//取得产品类型
				$producttypeObject = $this->db->query("SELECT pe.name FROM producttestinfo po 
								  					   JOIN producttype pe ON po.productType = pe.id
								                       AND po.sn = '".$sn."'
								                       AND po.tag = '".$packTag."'");

				$producttypeArray = $producttypeObject->result_array();
				if(count($producttypeArray) == 0)
				{
					$producttype = "";
				}
				else
				{
					$producttype = $producttypeArray[0]["name"];
				}
				
				//index.html中写入产品类型，装箱号，序列号
				fwrite($indexHandle, '<td>'.$producttype.'</td><td>'.$boxSn.'</td><td>'.$sn.'</td>');
				//index.html中写入检测结果
				if($result == "PASS")
				{
					fwrite($indexHandle, '<td><span style="color:green;"><b>合格</b></span></td>');
				}
				else if($result == "FAIL")
				{
					fwrite($indexHandle, '<td style="color:red"><b>不合格</b></td>');
				}
				else
				{
					fwrite($indexHandle, '<td style="color:yellow"><b>未测试</b></td>');
				}
				
				//写入各vna测试项最大值--用户所选
				if(count($testitemArray) == 0)
				{
					//do noting
				}
				else
				{
					//从产品测试方案表中取得当前产品--实际测试项
					$actualTestItemObject = $this->db->query("SELECT pn.testitem FROM test_configuration pn 
									  						  JOIN producttestinfo po ON pn.producttype = po.productType
									  						  AND po.sn = '".$sn."'
									  						  AND po.tag = '".$packTag."'");
					$actualTestItemArray = $actualTestItemObject->result_array();
					$actualTestItem = array();
					
					if(count($actualTestItemArray) != 0)
					{
						foreach($actualTestItemArray as $value)
						{
							array_push($actualTestItem,$value['testitem']);
						}
					}
					else
					{
					}
					
					//循环	用户所选的测试项
					foreach($testitemArray as $value)
					{
						$testitemId = $value['id'];
						
						//判断当前测试项，是否包含在当前产品实际测试项中
						if(in_array($testitemId,$actualTestItem))
						{
							$maxvalueObject = $this->db->query("SELECT MAX(te.value) AS value FROM testitemmarkvalue te
							 				  					JOIN testitemresult tt ON te.testItemResult = tt.id
							 				  					JOIN producttestinfo po ON tt.productTestInfo = po.id
							 				  					AND po.sn = '".$sn."'
							 				  					AND tt.testItem = '".$testitemId."'
							 				  					AND po.tag = '".$packTag."'
							 				 					");
							$maxvalueArray = $maxvalueObject->result_array();
							
							if(count($maxvalueArray) == 0)
							{
								fwrite($indexHandle, '<td>&nbsp;</td>');
							}
							else
							{
								$maxvalue = $maxvalueArray[0]['value'];
								fwrite($indexHandle, '<td>'.$maxvalue.'</td>');
							}
						}
						else
						{
							fwrite($indexHandle, '<td>&nbsp;</td>');
						}
					}
				}
				//判断并取得pim最大值
				if($testitempim != "")
				{
					//取得当前序列号产品的各组的最大值
					$pimMaxValueObject = $this->db->query("SELECT MAX(pa.value) as value,pp.test_time 
								  						   FROM pim_ser_num_group_data pa
								  						   JOIN pim_ser_num_group pp ON pa.pim_ser_num_group = pp.id
								  						   JOIN pim_ser_num pm ON pp.pim_ser_num = pm.id
								  						   AND pm.ser_num = '".$sn."'
					  			  						   GROUP BY pp.test_time
					              						  ");
					$pimMaxValueArray = $pimMaxValueObject->result_array();

					if(count($pimMaxValueArray) == 0)
					{
						fwrite($indexHandle, '<td>&nbsp;</td>');
					}
					else if(count($pimMaxValueArray) == 1)
					{
						fwrite($indexHandle, '<td>'.$pimMaxValueArray[0]["value"].'</td>');
					}
					else
					{
						//去除第一组值
						array_shift($pimMaxValueArray);
						//取得剩下的最大值，返回的是数组
						$maxArr = max($pimMaxValueArray);
						fwrite($indexHandle, '<td>'.$maxArr["value"].'</td>');
					}
				}
				else
				{
					//do nothing
				}
				fwrite($indexHandle, "</tr>");
				/*
				//创建保存当前序列号产品的子文件夹
				$subSnRoot = $currdownloadRoot.$slash.$sn;
				if(file_exists($subSnRoot) && is_dir($subSnRoot))
				{
					//do noting
				}
				else
				{
					if(mkdir($subSnRoot))
					{
						
					}
					else
					{
						$this->_returnUploadFailed("新建文件夹失败".$subSnRoot);
						return;
					}
				}
				//创建sn.html文件
				$subSnName = $subSnRoot.$slash.$sn.".html";
				$subHandle = fopen($subSnName,"a");
				fwrite($subHandle, '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
								  <html xmlns="http://www.w3.org/1999/xhtml">
									<head>
										<meta http-equiv="content-type" content="text/html;charset=utf-8">
										<style type="text/css">
											body{border:0px;margin:0px}
											.container{width:765px;margin:0px auto;border:1px solid black;padding:10px;}
											table{border-collapse:collapse;margin-top:10px;}
											table, td, th{border:1px solid black;}
											.hr_line{height:3px;margin-bottom:30px;color:black;}
											.separate_line{margin-top:20px;margin-bottom:20px;height:3px;}
											.testitem{color:blue;font-weight:bold;}
											.subitem{margin-top:30px;}
											.detailtable{width:350px;}
											.basictable{width:700px;}
											.vnaImg{width:500px;}
											.vnaimg{margin-top:10px;}
											.pimImg{width:350px;}
										</style>
									</head>
									<body>
										<div class="container">
											<div style="font-weight:bold;font-size:28px;text-align:center;">质量报告</div>
											<div style="text-align:right;margin-top:15px;margin-bottom:30px;">生产厂家：'.$producterName.'</div>
											<div style="margin-bottom:15px;">产品序列号：'.$sn.'</div>
											<div style="margin-bottom:15px;">型号：'.$producttype.'</div>
											<hr class="hr_line"/>
											');
				//获取vna测试详情
				if(count($selectTestItemArray) == 0 )
				{
					//do nothing
				}
				else
				{
					fwrite($subHandle, '<span class="testitem">VNA测试</span>
											<div>
												<table class="basictable">
												<tr>
													<th>测试时间</th>
													<th>测试设备型号</th>
													<th>测试设备序列号</th>
													<th>测试员</th>
													<th>测试结果</th>
												</tr>');
					//获取vna基本信息
					$vnaBasicInfoObject = $this->db->query("SELECT DISTINCT po.testTime,tn.name as teststationname,tn.equipmentSn,pe.name,tr.name AS tester,po.result
											FROM producttestinfo po 
											JOIN testitemresult tt ON tt.productTestInfo = po.id 
											JOIN testitemmarkvalue te ON te.testItemResult = tt.id
											JOIN producttype pe ON po.productType = pe.id
											JOIN tester tr ON po.tester = tr.id
											JOIN teststation tn ON po.testStation = tn.id
											WHERE po.sn = '".$sn."'");
					$vnaBasicInfoArray = $vnaBasicInfoObject->result_array();
					//写入vna基本信息
					if(count($vnaBasicInfoArray) == 0)
					{
						fwrite($subHandle, '<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>');
					}
					else
					{
						fwrite($subHandle,'<tr>
											<td>'.$vnaBasicInfoArray[0]['testTime'].'</td>
											<td>'.$vnaBasicInfoArray[0]['teststationname'].'</td>
											<td>'.$vnaBasicInfoArray[0]['equipmentSn'].'</td>
											<td>'.$vnaBasicInfoArray[0]['tester'].'</td>
											');
						if($vnaBasicInfoArray[0]['result'] == 1)
						{
							fwrite($subHandle, '<td>合格</td>');
						}
						else
						{
							fwrite($subHandle, '<td>不合格</td>');
						}
						fwrite($subHandle, '</tr>');
					}
					fwrite($subHandle, '</table></div>');
					
					//转换sql语句
					$itemCondition = $this->sqlColumnInArray($selectTestItemArray, "tt.testItem");
					//查询所有vna测试详情
					$vnatestDetailObject = $this->db->query("SELECT tm.name,tt.testResult,tt.img,te.value,te.mark
													  FROM producttestinfo po 
													  JOIN testitemresult tt ON tt.productTestInfo = po.id
												      JOIN testitemmarkvalue te ON te.testItemResult = tt.id
													  JOIN testitem tm ON tt.testItem = tm.id
													  AND po.sn = '".$sn."'
					 								  AND ".$itemCondition."
					 								  ");
					$vnatestDetailArray = $vnatestDetailObject->result_array();
					//结果数组
					$result = array();
					//转换测试项数组
					$testitem = array();
					if(count($vnatestDetailArray) != 0)
					{
						foreach($vnatestDetailArray as $value)
						{
							if(!in_array($value['name'], $testitem))
							{
								$arr = array($value['img'],array(array($value['mark'],$value['value'],$value['testResult'])));
								$result[$value['name']] = $arr;
								array_push($testitem,$value['name']);
							}
							else
							{
								$arr = array($value['mark'],$value['value'],$value['testResult']);
								array_push($result[$value['name']][1],$arr);
							}
						}
					}
					//遍历转换后的数组$result，写入html
					$i = 1;
					foreach($result as $key=>$value)
					{
						//拷贝图片
						//服务器端图片路径
						$vnaImgRoot =  getcwd().$slash."assets".$slash."uploadedSource".iconv("utf-8","gbk",$slash.$value[0]);
						//图片名称
						$vnaImgName = iconv("utf-8","gbk",substr($value[0], strripos($value[0],'\\')+1));
						if(file_exists($vnaImgRoot))
						{
							copy($vnaImgRoot,$subSnRoot.$slash.$vnaImgName);
						}
						else
						{
						}
						//写入测试项名称
						fwrite($subHandle, '<div class="subitem">测试项'.$i.':'.$key.'</div>');
						//写入测试项，详细数据--一组或多组
						fwrite($subHandle, '<table class="detailtable"><tr><th>Freq</th><th>Value</th><th>Result</th></tr>');
						foreach ($value[1] as $key => $value) 
						{
							fwrite($subHandle, '<tr><td>'.$value[0].'</td><td>'.$value[1].'</td>');
							if($value[2] == 1)
							{
								fwrite($subHandle, '<td>合格</td>');
							}
							else
							{
								fwrite($subHandle, '<td>不合格</td>');
							}
							fwrite($subHandle, '</tr>');
						}
						fwrite($subHandle, '</table>');
						$i++;
						fwrite($subHandle, '<div class="vnaimg"><img src="./'.iconv("gbk","utf-8",$vnaImgName).'" class="vnaImg"/></div>');
					}
				}
				//写入分割线,PIM基本表头
				if($testitempim == "")
				{
					
				}
				else
				{
					//获取PIM基本信息
					$pimbasicInfoObject = $this->db->query("SELECT pl.name,pm.col12,pm.col13,MAX(pp.test_time) AS testtime,pp.upload_date
															FROM pim_label pl
															JOIN pim_ser_num pm ON pm.pim_label = pl.id
															JOIN pim_ser_num_group pp ON pp.pim_ser_num = pm.id
															JOIN pim_ser_num_group_data pa ON pa.pim_ser_num_group = pp.id
															WHERE pm.ser_num = '".$sn."'");
					$pimbasicInfoArray = $pimbasicInfoObject->result_array();
					
					$pimbasicInfo = array();
					$pimtestResult = "";
					$pimmaxdataArray = array();
					if(count($pimbasicInfoArray) != 0)
					{
						$pimbasicInfo = $pimbasicInfoArray[0];
						//取得极限值
						$limitLine = substr($pimbasicInfo["col12"], strrpos($pimbasicInfo["col12"], ":")+1);
						//取得所有值
						$pimdataObject = $this->db->query("SELECT pp.test_time,pa.value
												  FROM pim_label pl
												  JOIN pim_ser_num pm ON pm.pim_label = pl.id
												  JOIN pim_ser_num_group pp ON pp.pim_ser_num = pm.id
												  JOIN pim_ser_num_group_data pa ON pa.pim_ser_num_group = pp.id
												  WHERE pm.ser_num = '".$sn."'");
						$pimdataArray = $pimdataObject->result_array();
						//对数据处理，将同一测试时间的数据放到一组
						$pim_testtime = array();
						$pimdataFormart = array();
						foreach($pimdataArray as $value)
						{
							if(!in_array($value["test_time"], $pim_testtime))
							{
								$arr = array($value["value"]);
								$pimdataFormart[$value["test_time"]] = $arr;
								array_push($pim_testtime,$value["test_time"]);
							}
							else
							{
								array_push($pimdataFormart[$value["test_time"]],$value["value"]);
							}
						}
						//判断有几组数据大于极限值
						$i = 0;
						foreach($pimdataFormart as $value)
						{
							foreach($value as $val)
							{
								if($val >= $limitLine)
								{
									$i++;
									break;
								}
							}
						}
						if($i >= 2)
						{
							$pimtestResult = "不合格";
						}
						else
						{
							if(count($pimdataFormart) != 0)
							{
								$pimtestResult = "合格";
							}
						}
						//取得各组的最大值
						$pimmaxdataObject = $this->db->query("SELECT pp.test_time,pp.upload_date,MAX(pa.value) AS value FROM pim_ser_num pm
														JOIN pim_label pl ON pm.pim_label=pl.id
														JOIN pim_ser_num_group pp ON pp.pim_ser_num = pm.id
														JOIN pim_ser_num_group_data pa ON pa.pim_ser_num_group = pp.id
														AND pm.ser_num = '".$sn."'
														GROUP BY pp.test_time");
						$pimmaxdataArray = $pimmaxdataObject->result_array();
						//html写入基本信息
						fwrite($subHandle,'<hr class="separate_line"><span class="testitem">PIM测试</span><div>
								   	   <table class="basictable">
									   		<tr>
												<th>测试时间</th>
												<th>测试设备型号</th>
												<th>测试设备序列号</th>
												<th>测试员</th>
												<th>测试结果</th>
											</tr>
											<tr>
												<td>'.$pimbasicInfoArray[0]['testtime'].'</td>
												<td>&nbsp;</td>
												<td>&nbsp;</td>
												<td>'.$pimbasicInfoArray[0]['col13'].'</td>
												<td>'.$pimtestResult.'</td>
											</tr>
										</table></div>
										');
						//每组测试的最大值写入html
						fwrite($subHandle, '<div><table class="detailtable"><tr>');
						foreach ($pimmaxdataArray as $key => $value) 
						{
							fwrite($subHandle, '<th>组'.($key+1).'</th>');
						}
						fwrite($subHandle, '</tr><tr>');
						foreach ($pimmaxdataArray as $key => $value) 
						{
							fwrite($subHandle, '<td>'.$value["value"].'</td>');
						}
						fwrite($subHandle, '</tr>');
						fwrite($subHandle, '</table></div>');
						//拷贝pim图片，写入html文件
						foreach ($pimmaxdataArray as $key => $value) 
						{
							fwrite($subHandle, '<div style="display: inline-block;margin-top:10px;margin-right:25px;">');
							$uploadDate = str_replace("-", "_", $value['upload_date']);
							$testTime = preg_replace("/[\s-:]/","", $value['test_time']);
							$pimImgRoot = getcwd().$slash."assets".$slash."uploadedSource".$slash."pim".$slash.$uploadDate.$slash.$pimbasicInfoArray[0]['name'].$slash.$sn."_".$testTime.".jpg";
							$pimImgName = $sn."_".$testTime.".jpg";
							if(file_exists($pimImgRoot))
							{
								copy($pimImgRoot, $subSnRoot.$slash.$pimImgName);
							}
							fwrite($subHandle, '<div>组'.($key+1).'</div>');
							fwrite($subHandle, '<img class="pimImg" src="./'.$pimImgName.'"/>');
							fwrite($subHandle, '</div>');
						}
					}
				}
				fwrite($subHandle, '</div></body>');
				fclose($subHandle);
				*/
			}

			fwrite($indexHandle, "</table>");
			fwrite($indexHandle, '</div></div></body></html>');
			fclose($indexHandle);
			
			exec('C:\Progra~1\7-Zip\7z.exe a -tzip '.$currdownloadRoot.'.zip '.$currdownloadRoot);
			$this->delDirAndFile($currdownloadRoot);
			
			$fileRoot = $currdownloadRoot.".zip";
			$fileName = $dateStamp.".zip";

			if(!file_exists($fileRoot))
			{
				die("Error:File not found.");
			}
			else
			{
				header("Pragma: public");
       			header("Expires: 0");
        		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        		header("Cache-Control: public");
        		header("Content-Description: File Transfer");
        		header("Content-type: application/octet-stream");
        		header("Content-Disposition: attachment; filename=\"" . $fileName . "\"");
        		header("Content-Transfer-Encoding: binary");
        		header("Content-Length: " . filesize($fileRoot));
        		ob_end_flush();
				@readfile($fileRoot);
			}
			
		}
	}
	
	
	//从数组中遍历元素，组成SQL的IN语句
	protected function sqlColumnInArray($arr,$columName)
	{
		if(count($arr) == 0)
		{
			$sql=$columName." = null";
		}
		else if(count($arr) == 1)
		{
			$sql = $columName." = '".$arr[0]."'";
		}
		else
		{
			$sql = $columName." in ('".$arr[0]."','";
			for($i=1;$i<count($arr)-1;$i++)
			{
				$sql .= $arr[$i]."','";
			}
			$sql .= $arr[count($arr)-1]."')";
		}
		return $sql;
	}
	
	private function _returnUploadFailed($err)
	{
		$this->load->helper('xml');
		$dom = xml_dom();
		$uploadResult = xml_add_child($dom, 'uploadResult');
		xml_add_child($uploadResult, 'result', 'false');
		xml_add_child($uploadResult, 'info', $err);
		xml_print($dom);
	}
	
	//循环删除目录和文件函数
	private function delDirAndFile($dirName)
	{
		if (PHP_OS == 'WINNT')
		{
			$slash = "\\";
		}
		else if (PHP_OS == 'Darwin')
		{
			$slash = "/";
		}
		if (file_exists($dirName))
		{
			if ($handle = opendir($dirName))
			{
				while (false !== ($item = readdir($handle)))
				{
					if ($item != "." && $item != "..")
					{
						if (is_dir($dirName.$slash.$item))
						{
							$this->delDirAndFile($dirName.$slash.$item);
						}
						else
						{
							unlink($dirName.$slash.$item);
						}
					}
				}
				closedir($handle);
				rmdir($dirName);
			}
		}
	}
	
	//cheack pim result
	protected function checkPimResult($pim_ser_num)
	{
		$perPimResultSql = "SELECT a.test_time, max( a.value ) AS maxval, max( a.value ) > substring( a.col12, 12 ) AS result
								FROM (
									SELECT pm.model, pm.col12, pp.test_time, pa.value
									FROM pim_ser_num pm
									JOIN pim_ser_num_group pp ON pp.pim_ser_num = pm.id
									JOIN pim_ser_num_group_data pa ON pa.pim_ser_num_group = pp.id
									WHERE pm.ser_num = '".$pim_ser_num."'
								)a
								GROUP BY a.test_time
								ORDER BY a.test_time DESC";
		$perPimResult = $this->db->query($perPimResultSql);
		$perPimResultArray = $perPimResult->result_array();
		//pim结果, 默认不合格
		$pim_result = false;
		//pim count == 1,只有一组
		if(count($perPimResultArray) == 1){
			//result == 0, Pass
			if($perPimResultArray[0]['result'] == 0){
				$pim_result = true;
			}
		}
		else//pim count > 1
		{
			//check if first test result is pass, 0 is pass, 1 is fail
			if($perPimResultArray[count($perPimResultArray)-1]['result'] != 0)
			{
				$pimPassCount = 0;
				$pimPrevResult;
				foreach ($perPimResultArray as $k1 => $v1) {
					$result = $v1['result'];// 0 is pass, 1 is fail
					if($k1 == 0){//$key == 0, is the first group
						if($result == 0){
							$pimPassCount = $pimPassCount + 1;
						}
						$pimPrevResult = $result;
					}
					else//$k1 != 0, not first group
					{
						if($result == 0){//current group is pass
							if($pimPrevResult == 0){
								$pimPassCount = $pimPassCount + 1;
							}else{
								$pimPassCount = 1;
								$pimPrevResult = $result;
							}
						}else{//current group is fail, clean pass count and set current result to prev result 
							$pimPassCount = 0;
							$pimPrevResult = $result;
						}
					}
					if($pimPassCount == 3){//3 continuous pass groups,set pim result true and stop foreach
						$pim_result = true;
						break;
					}
				}
			}
			else//first time test result is pass
			{
				$pim_result = true;
			}
		}
		return $pim_result;
	}
}
