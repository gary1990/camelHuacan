<?php
if (!defined('BASEPATH'))
	exit('no direct script access allowed');
class Gqts extends CW_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->_init();
		$this->load->library("Pagefenye");
		$this->load->library("zip");
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
			''=>'所有',
			'0'=>'FAIL',
			'1'=>'PASS'
		);
		$this->smarty->assign('testResultList', $testResultList);
	}

	public function index()
	{
		$current_item = $this->input->post("current_item");
		$current_page = $this->input->post("current_page");
		$current_action = $this->input->post("current_action");
		$timeFrom1 = emptyToNull($this->input->post("timeFrom1"));
		if ($timeFrom1 == null)
		{
			$timeFrom1 = 1900;
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
			$timeTo1 = 2112;
		}
		$timeTo2 = emptyToNull($this->input->post("timeTo2"));
		if ($timeTo2 == null)
		{
			$timeTo2 = 23;
		}
		$timeTo3 = emptyToNull($this->input->post("timeTo3"));
		if ($timeTo3 == null)
		{
			$timeTo3 = 59;
		}
		$timeTo = $timeTo1." ".$timeTo2.":".$timeTo3;
		$testResult = emptyToNull($this->input->post('testResult'));
		$sn = emptyToNull($this->input->post('sn'));
		$teststation = emptyToNull($this->input->post('teststation'));
		$labelnum = emptyToNull($this->input->post('labelnum'));
		$producttype = emptyToNull($this->input->post('producttype'));
		$ordernum = emptyToNull($this->input->post('ordernum'));
		$tester = emptyToNull($this->input->post('tester'));
		if ($current_item == "")
		{
		 	$arr = $this->defaultSerachResult();
		 	$vnaResultArray = $arr[0];
			$vnaFenye = $arr[1];
			$pimResultArray = $arr[2];
			$pimFenye = $arr[3];
			$vanTotalRecord = $arr[4];
			$pimTotalRecord = $arr[5];
			$this->smarty->assign("vnaResultArray", $vnaResultArray);
			$this->smarty->assign("vnaFenye", $vnaFenye);
			$this->smarty->assign("pimResultArray", $pimResultArray);
			$this->smarty->assign("pimFenye", $pimFenye);
			$this->smarty->assign("vnaTotalRecord", $vanTotalRecord);
			$this->smarty->assign("pimTotalRecord", $pimTotalRecord);
		}
		else if ($current_item == "VNA")
		{
			$timeFromSql=" AND po.testTime >= '".$timeFrom."'";
			$timeToSql = " AND po.testTime <= '".$timeTo."'";
			$testResultSql = "";
			$snSql = "";
			$teststationSql = "";
			$labelnumSql = "";
			$producttypeSql = "";
			$ordernumSql = "";
			$testerSql = ""; 
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
				$snSql = " AND po.sn like '%".$sn."%' ";
			}
			if($teststation != null)
			{
				$teststationSql = " AND tn.name like '%".$teststation."%' ";
			}
			if($producttype != null)
			{
				$producttypeSql = " AND pe.name like '%".$producttype."%' ";
			}
			if($tester != null)
			{
				$testerSql = " AND tr.name like '%".$tester."%' ";
			}
			$sidepage = 3;
			$vnaPageSize = 30;
			$vnaTotalpageObject = $this->db->query("SELECT COUNT(t.id) AS num FROM (SELECT po.id,po.testTime,tn.name AS testStation,tr.name AS tester,pe.name AS productType,po.sn FROM producttestinfo po 
													JOIN teststation tn ON po.testStation = tn.id
													JOIN tester tr ON po.tester = tr.id
													JOIN producttype pe ON po.productType = pe.id
													".$timeFromSql.$timeToSql.$testResultSql.$snSql.$teststationSql.$producttypeSql.$testerSql.") t");
			$vnaTotalpageArray = $vnaTotalpageObject->result_array();
			$vnaTotalpage = $vnaTotalpageArray[0]['num'];
			if($current_page > ceil($vnaTotalpage/$vnaPageSize)){
				$current_page = 1;
			}
			$vnaSelectFrom = ($current_page-1)*$vnaPageSize;
			$vnaResultSql = "SELECT po.result,po.id,po.testTime,tn.name AS testStation,tr.name AS tester,pe.name AS productType,po.sn FROM producttestinfo po 
							JOIN teststation tn ON po.testStation = tn.id
							JOIN tester tr ON po.tester = tr.id
							JOIN producttype pe ON po.productType = pe.id
							".$timeFromSql.$timeToSql.$testResultSql.$snSql.$teststationSql.$producttypeSql.$testerSql."
							ORDER BY po.testTime DESC
							LIMIT ".$vnaSelectFrom.",".$vnaPageSize;
			$vnaResultObject = $this->db->query($vnaResultSql);
			$vnaResultArray = $vnaResultObject->result_array();
			/*
			if($current_action == "export")
			{
				$vnaTotalresultSql = "SELECT po.result,po.id,po.testTime,tn.name AS testStation,tr.name AS tester,pe.name AS productType,po.sn FROM producttestinfo po 
							JOIN teststation tn ON po.testStation = tn.id
							JOIN tester tr ON po.tester = tr.id
							JOIN producttype pe ON po.productType = pe.id
							".$timeFromSql.$timeToSql.$testResultSql.$snSql.$teststationSql.$producttypeSql.$testerSql."
							ORDER BY po.testTime DESC";
				$vnaTotalresultObject = $this->db->query($vnaTotalresultSql);
				$vnaTotalresultArray = $vnaTotalresultObject->result_array();
				$this->getVnaResultHtml($vnaTotalresultArray);
			}
			*/
			$vnaFenye = $this->pagefenye->getFenye($current_page, $vnaTotalpage, $vnaPageSize, $sidepage);
			$arr = $this->defaultSerachResult();
			$pimResultArray = $arr[2];
			$pimFenye = $arr[3];
			$pimTotalpage = $arr[5];
			$this->smarty->assign("vnaResultArray", $vnaResultArray);
			$this->smarty->assign("vnaFenye", $vnaFenye);
			$this->smarty->assign("pimResultArray", $pimResultArray);
			$this->smarty->assign("pimFenye", $pimFenye);
			$this->smarty->assign("vnaTotalRecord", $vnaTotalpage);
			$this->smarty->assign("pimTotalRecord", $pimTotalpage);
		}
		else
		{
			$timeFromSql=" AND pp.test_time >= '".$timeFrom."'";
			$timeToSql = " AND pp.test_time <= '".$timeTo."'";
			$testResultSql = "";
			$snSql = "";
			$teststationSql = "";
			$labelnumSql = "";
			$producttypeSql = "";
			$ordernumSql = "";
			$testerSql = ""; 
			if($sn != null)
			{
				$snSql = " AND pm.ser_num like '%".$sn."%' ";
			}
			if($labelnumSql != null)
			{
				$labelnumSql = " AND pl.name like '%".$sn."%' ";
			}
			if($producttype != null)
			{
				$producttypeSql = " AND pm.model like '%".$producttype."%' ";
			}
			$pimTotalpageObject = $this->db->query("SELECT COUNT('tt.id') AS num 
														FROM (SELECT t.id,MAX(t.test_time) AS test_time,t.upload_date,t.model,t.ser_num,t.work_num,t.name
								  								FROM (SELECT pm.id,pp.test_time,pp.upload_date,pm.model,pm.ser_num,pm.work_num,pl.name 
																		FROM pim_ser_num pm 
																		JOIN pim_label pl ON pm.pim_label = pl.id 
																		JOIN pim_ser_num_group pp ON pp.pim_ser_num = pm.id
																		".$timeFromSql.$timeToSql.$snSql.$labelnumSql.$producttypeSql."
																	  ) t
																GROUP BY t.id
																ORDER BY t.test_time DESC
															  ) tt ");
			$pimTotalpageArray = $pimTotalpageObject->result_array();
			$pimTotalpage = $pimTotalpageArray[0]['num'];
			$sidepage = 3;
			$pimPageSize = 10;
			if($current_page > ceil($pimTotalpage/$pimPageSize))
			{
				$current_page = 1;
			}
			$pimSelectFrom = ($current_page-1)*$pimPageSize;
			$pimResultSql = "SELECT t.id,MAX(t.test_time) AS test_time,t.upload_date,t.model,t.ser_num,t.work_num,t.name
							FROM (SELECT pm.id,pp.test_time,pp.upload_date,pm.model,pm.ser_num,pm.work_num,pl.name 
								  FROM pim_ser_num pm 
								  JOIN pim_label pl ON pm.pim_label = pl.id 
								  JOIN pim_ser_num_group pp ON pp.pim_ser_num = pm.id
								  ".$timeFromSql.$timeToSql.$snSql.$labelnumSql.$producttypeSql."
								  ) t
							GROUP BY t.id
							ORDER BY t.test_time DESC
							LIMIT ".$pimSelectFrom.",".$pimPageSize;
			$pimResultObject = $this->db->query($pimResultSql);
			$pimResultArray = $pimResultObject->result_array();
			/*
			if($current_action == "export")
			{
				$pimTotalresultSql = "SELECT t.id,MAX(t.test_time) AS test_time,t.upload_date,t.model,t.ser_num,t.work_num,t.name
										FROM (SELECT pm.id,pp.test_time,pp.upload_date,pm.model,pm.ser_num,pm.work_num,pl.name 
								  		FROM pim_ser_num pm 
								  		JOIN pim_label pl ON pm.pim_label = pl.id 
								  		JOIN pim_ser_num_group pp ON pp.pim_ser_num = pm.id
								  		".$timeFromSql.$timeToSql.$snSql.$labelnumSql.$producttypeSql."
								  		) t
										GROUP BY t.id
										ORDER BY t.test_time DESC";
				$pimTotalresultObject = $this->db->query($pimTotalresultSql);
				$pimTotalresultArray = $pimTotalresultObject->result_array();
				$this->getPimResultHtml($pimTotalresultArray);
			}
			 * 
			 */
			$pimFenye = $this->pagefenye->getFenye($current_page, $pimTotalpage, $pimPageSize, $sidepage);
			$arr = $this->defaultSerachResult();
			$vnaResultArray = $arr[0];
			$vnaFenye = $arr[1];
			$vnaTotalpage = $arr[4];
			$this->smarty->assign("vnaResultArray", $vnaResultArray);
			$this->smarty->assign("vnaFenye", $vnaFenye);
			$this->smarty->assign("pimResultArray", $pimResultArray);
			$this->smarty->assign("pimFenye", $pimFenye);
			$this->smarty->assign("vnaTotalRecord", $vnaTotalpage);
			$this->smarty->assign("pimTotalRecord", $pimTotalpage);
		}
		$this->smarty->assign("title", "质量追溯");
		$this->smarty->display("gqts.tpl");
	}

	private function defaultSerachResult()
	{
		$current_page = 1;
		$sidepage = 3;
		$vnaPageSize = 30;
		$vnaSelectFrom = ($current_page - 1) * $vnaPageSize;
		$vnaResultObject = $this->db->query("SELECT po.id,po.testTime,tn.name AS testStation,tr.name AS tester,pe.name AS productType,po.sn FROM producttestinfo po 
												JOIN teststation tn ON po.testStation = tn.id
												JOIN tester tr ON po.tester = tr.id
												JOIN producttype pe ON po.productType = pe.id
												ORDER BY po.testTime DESC
												LIMIT ".$vnaSelectFrom.",".$vnaPageSize);
		$vnaResultArray = $vnaResultObject->result_array();
		$vnaTotalpageObject = $this->db->query("SELECT COUNT(id) AS num 
												FROM 
												(SELECT po.id,po.testTime,tn.name AS testStation,tr.name AS tester,pe.name AS productType,po.sn 
													FROM producttestinfo po 
													JOIN teststation tn ON po.testStation = tn.id
													JOIN tester tr ON po.tester = tr.id
													JOIN producttype pe ON po.productType = pe.id) t");
		$vnaTotalpageArray = $vnaTotalpageObject->result_array();
		$vnaTotalpage = $vnaTotalpageArray[0]['num'];
		$vnaFenye = $this->pagefenye->getFenye($current_page, $vnaTotalpage, $vnaPageSize, $sidepage);
		$pimPageSize = 10;
		$pimTotalpageObject = $this->db->query("SELECT COUNT(id) AS num FROM pim_ser_num");
		$pimTotalpageArray = $pimTotalpageObject->result_array();
		$pimTotalpage = $pimTotalpageArray[0]["num"];
		$pimSelectFrom = ($current_page - 1) * $pimPageSize;
		$pimFenye = $this->pagefenye->getFenye($current_page, $pimTotalpage, $pimPageSize, $sidepage);
		$pimTotalResultObject = $this->db->query("SELECT pm.id,pp.test_time,pp.upload_date,pm.model,pm.ser_num,pm.work_num,pl.name 
													FROM pim_ser_num pm 
													JOIN pim_label pl ON pm.pim_label = pl.id 
													JOIN pim_ser_num_group pp ON pp.pim_ser_num = pm.id
													");
		$pimResultObject = $this->db->query("SELECT t.id,MAX(t.test_time) AS test_time,t.upload_date,t.model,t.ser_num,t.work_num,t.name
											FROM (SELECT pm.id,pp.test_time,pp.upload_date,pm.model,pm.ser_num,pm.work_num,pl.name 
													FROM pim_ser_num pm 
													JOIN pim_label pl ON pm.pim_label = pl.id 
													JOIN pim_ser_num_group pp ON pp.pim_ser_num = pm.id) t
											GROUP BY t.id
											ORDER BY t.test_time DESC
											LIMIT ".$pimSelectFrom.",".$pimPageSize);
		$pimResultArray = $pimResultObject->result_array();
		return array($vnaResultArray,$vnaFenye,$pimResultArray,$pimFenye,$vnaTotalpage,$pimTotalpage);
	}
	/*
	private function getVnaResultHtml($tmpArray)
	{
		if(count($tmpArray) != 0)
		{
			$downloadRoot = getcwd()."\\assets\\downloadedSource";
			$slash = "\\";
			if (file_exists($downloadRoot) && is_dir($downloadRoot))
			{
			}
			else
			{
				mkdir($downloadRoot,777);
			}
			$currentTime = date("Ymd_H_i_s", time());
			if (file_exists($downloadRoot.$slash.$currentTime) && is_dir($downloadRoot.$slash.$currentTime))
			{
			}
			else
			{
				mkdir($downloadRoot.$slash.$currentTime,777);
			}
			
			$indexHandle = fopen($downloadRoot.$slash.$currentTime.$slash."index.html", "a");
			fwrite($indexHandle, "<html><head><meta http-equiv='content-type' content='text/html;charset=utf-8'><style type='text/css'>a{text-decoration:none;}</style></head><body>");
			fwrite($indexHandle, "<h4>测试数据文件列表：产品型号_测试时间_数据库中记录序号</h4>");
			fwrite($indexHandle, "<ol>");		
			foreach ($tmpArray as $value)
			{
				set_time_limit(0);
				
				$id = $value["id"];
				$sn = $value["sn"];
				$producttype = $value["productType"];
				$testtime = $value["testTime"];
				$preg_testtime = preg_replace("/[\s-:]/", "", $testtime);
				$testStationName = $value["testStation"];
				$testStationSnList = $this->db->query("SELECT equipmentSn FROM teststation WHERE name = '".$testStationName."'");
				$testStationArray = $testStationSnList->result_array();
				$testStationSn = $testStationArray[0]["equipmentSn"];
				$employeeId = $value["tester"];
				$testResult = $value["result"];
				
				$pre_sn = preg_replace('/\W/', '', $sn);
				if(strlen($pre_sn) > 60)
				{
					$pre_sn = "---".substr($pre_sn,(strlen($pre_sn)-59));
				}
				
				$downloadDir = $downloadRoot.$slash.$currentTime.$slash.$pre_sn."_".$preg_testtime."_".$id;
				if (file_exists($downloadDir) && is_dir($downloadDir))
				{
				}
				else
				{
					mkdir($downloadDir,777);
				}
				$pimTesttimeObject = $this->db->query("SELECT work_num,MAX(tt.test_time) AS time FROM (SELECT pm.work_num,pl.name,pp.test_time,pp.upload_date,pp.id FROM pim_label pl 
													JOIN pim_ser_num pm ON pm.pim_label=pl.id 
													JOIN pim_ser_num_group pp ON pp.pim_ser_num = pm.id 
													WHERE pm.model = '".$producttype."' AND pm.ser_num = '".$sn."') tt");
				$pimTesttimeArray = $pimTesttimeObject->result_array();
				if(count($pimTesttimeArray) != 0)
				{
					$pimTesttime = $pimTesttimeArray[0]['time'];
					$pimWorkerId = $pimTesttimeArray[0]['work_num'];
				}else
				{
					$pimTesttime = "";
					$pimWorkerId = "";
				}
				$pimResultListObject = $this->db->query("SELECT pl.name,pp.test_time,pm.work_num,pp.upload_date,pp.id FROM pim_label pl 
													JOIN pim_ser_num pm ON pm.pim_label=pl.id 
													JOIN pim_ser_num_group pp ON pp.pim_ser_num = pm.id 
													WHERE pm.model = '".$producttype."' AND pm.ser_num = '".$sn."'");
				$pimResultListArray = $pimResultListObject->result_array();
				$fileDir = $pre_sn."_".$preg_testtime."_".$id."/".$pre_sn."_".$preg_testtime."_".$id;
				fwrite($indexHandle, "<li><a href='./".$fileDir.".html'>".$sn."_".$preg_testtime."_".$id."</a></li>");
				$handle = fopen($downloadDir.$slash.$pre_sn."_".$preg_testtime."_".$id.".html", "a");
				fwrite($handle, "<html><head><meta http-equiv='content-type' content='text/html;charset=utf-8' /></head><body>");
				fwrite($handle, "<div style='margin-bottom:35px'><span style='font-size:25px;'>质量追溯报告</span></div>");
				fwrite($handle,"<div style='margin-bottom:5px;margin-top:5px;'><span style='width:100px;'>产品序列号：</span>".$sn."</div>");
				fwrite($handle,"<div style='margin-bottom:5px;margin-top:5px;'><span style='width:100px'>型号：</span>".$producttype."</div>");
				fwrite($handle,"<div style='margin-bottom:5px;margin-top:5px;'><span style='width:100px'>订单号：</span></div>");
				fwrite($handle,"<hr/>");
				fwrite($handle, "<div style='margin-bottom:5px;margin-top:5px;'><span style='color:blue;font-weight:bold;'>生产信息</span></div>");
				fwrite($handle, "<div style='margin-bottom:10px;margin-top:10px;'><table border=1 cellspacing=0 width='570px'>");
				fwrite($handle, "<tr><th>测试项目</th><th>测试时</th><th>测试设备型号</th><th>测试设备序列号</th><th>测试员</th><th>测试结果</th></tr>");
				fwrite($handle, "<tr><td>VNA</td><td>".$testtime."</td><td>".$testStationName."</td><td>".$testStationSn."</td><td>".$employeeId."</td><td>".$testResult."</td></tr>");
				fwrite($handle, "<tr><td>PIM</td><td>".$pimTesttime."</td><td>&nbsp</td><td>&nbsp</td><td>".$pimWorkerId."</td><td>&nbsp</td></tr>");
				fwrite($handle, "</table></div>");
				fwrite($handle, "<hr/>");
				fwrite($handle, "<div style='margin-bottom:10px;margin-top:5px;'><span style='color:blue;font-weight:bold;'>VNA测试数据</span></div>");
				
				$testDataList = $this->db->query("SELECT tm.name,tt.img,te.mark,te.value,te.channel,te.trace,tt.testResult FROM testitemresult tt JOIN testitemmarkvalue te ON te.testItemResult = tt.id JOIN testitem tm ON tt.testItem=tm.id AND tt.productTestinfo = ".$id);
				$testDataArray = $testDataList->result_array();
				if(count($testDataArray) != 0)
				{
					$arr = array();
					foreach ($testDataArray as $key=>$value)
					{
						
						if ($value['testResult'] == 0)
						{
							$value['testResult'] = "FAIL";
						}
						else
						{
							$value['testResult'] = "PASS";
						}
						
						if (!in_array($value['name'], $arr))
						{
							$imgServerRoot = "assets/uploadedSource/";
							$imgRoot = $value['img'];
							$start = strrpos($imgRoot, "\\");
							$end  = strrpos($imgRoot,"-");
							$lenght = $end - $start;
							$imgRoot = substr($imgRoot,0,$start+1).iconv("utf-8","gbk",substr($imgRoot, $start+1,$lenght-1))."-img.png";
							$imgDir = str_replace("/", "\\", getcwd()."\\assets\\uploadedSource\\".$imgRoot);
							$imgName = substr($imgDir, strrpos($imgDir, "\\") + 1);
							if(file_exists($imgDir)){
								copy($imgDir,$downloadDir."\\".$imgName);
							}
							if (count($arr) == 0)
							{
								fwrite($handle, "<div style='font-size:15px;margin-top:5px;margin-bottom:5px;'><span style='width:70px;'>测试项:</span>".$value['name']."</div>");
								fwrite($handle, "<div><div style='float=left;margin-top:10px;margin-right:20px;'><img width='640px' height='480px' src='".iconv("gbk","utf-8",$imgName)."' /></div>");
							}
							else
							{
								fwrite($handle, "</table></div>");
								fwrite($handle, "<div style='font-size:15px;margin-top:5px;margin-bottom:5px;'><span style='width:70px;'>测试项:</span>".$value['name']."</div>");
								fwrite($handle, "<div style='float=left;margin-top:10px;margin-right:20px;'><img width='640px' height='480px' src='".iconv("gbk","utf-8",$imgName)."' /></div>");
							}
							array_push($arr, $value['name']);
							fwrite($handle, "<br/>");
							fwrite($handle, "<div style='height=480px'><table border=1 cellspacing=0 width='500px'><tr><th>Freq</th><th>value</th><th>channel</th><th>trace</th><th>结果</th></tr>");
							fwrite($handle, "<tr><td>".$value['mark']."</td><td>".$value['value']."</td><td>".$value['channel']."</td><td>".$value['trace']."</td><td>".$value['testResult']."</td></tr>");
						}
						else
						{
							fwrite($handle, "<tr><td>".$value['mark']."</td><td>".$value['value']."</td><td>".$value['channel']."</td><td>".$value['trace']."</td><td>".$value['testResult']."</td></tr>");
							if($key == count($testDataArray)-1)
							{
								fwrite($handle, "</table></div>");
							}
						}	 
					}
					fwrite($handle, "</div>");				
				}		
				//html文档中写入PIM测试数据查询数据
				if(count($pimResultListArray) != 0){
					$pimEmployID = $pimResultListArray[0]['work_num'];
					$pimName = $pimResultListArray[0]['name'];
					fwrite($handle, "<hr/>");
					fwrite($handle,"<div style='color:blue;margin-top:5px;margin-bottom:5px;font-weight:bold;'>PIM测试数据</div>");
					fwrite($handle, "<div style='margin-top:10px;margin-bottom:5px;'><table border=1 cellspacing=0 width='500px'><tr>");
					for($i=1;$i <= count($pimResultListArray);$i++){
						fwrite($handle, "<th>数据".$i."</th>");
					}
					fwrite($handle, "</tr><tr>");
					foreach($pimResultListArray as $value)
					{
						$pimDataObject = $this->db->query("SELECT MAX(pa.value) AS value FROM pim_ser_num_group_data pa WHERE pa.pim_ser_num_group = ".$value['id']);
						$pimDataArray = $pimDataObject->result_array();
						if(count($pimDataArray) != 0) 
						{
							fwrite($handle,"<td style='text-align:center'>".$pimDataArray[0]['value']."</td>");
						}
					}
					fwrite($handle, "</tr></table><div>");
					foreach($pimResultListArray as $key=>$value)
					{
						$pimtesttimes = preg_replace("/[\s-:]/","", $value['test_time']);
						$pimImgDir = getcwd()."\\assets\\uploadedSource\\pim\\".str_replace("-", "_", $value['upload_date'])."\\".$value['name']."\\".$sn."_".$pimtesttimes.".jpg";
						$pimImageName = $sn."_".$pimtesttimes.".jpg";
						if(file_exists($pimImgDir)){
							copy($pimImgDir, $downloadDir."\\".$pimImageName);
						}
						fwrite($handle, "<img alt='服务器上可能无此图片' style='margin-top:10px;margin-right:10px' width='640px' height='480px' src='".$pimImageName."'>");
					}
				}
			}
	
			fwrite($indexHandle, "</ol>");
			fwrite($indexHandle, "</body></html>");
			fwrite($handle, "</body></html>");
			fclose($indexHandle);
			fclose($handle);
			$path = $downloadRoot.$slash.$currentTime;
			//exec('C:\Progra~1\7-Zip\7z.exe a -tzip '.$downloadRoot.$slash.$currentTime.'.zip '.$path);
			$zip = new ZipArchive;
			$path = str_replace('\\', '/', $path)."/";
			$this->zip->read_dir($path,FALSE);
			$this->zip->download($currentTime.".zip");
		}
	}

	private function getPimResultHtml($arr)
	{
		$currentTime = date("Ymd_H_i_s",time());
		$slash="\\";
		$downloadDir = getcwd().$slash."assets".$slash."downloadedSource";
		if(file_exists($downloadDir) && is_dir($downloadDir))
		{	
		}
		else
		{
			mkdir($downloadDir,777);
		}
		$fileDir = $downloadDir.$slash.$currentTime;
		if(file_exists($fileDir) && is_dir($fileDir))
		{	
		}
		else
		{
			mkdir($fileDir,777);
		}
		$indexHandle = fopen($fileDir.$slash."index.html", "a");
		fwrite($indexHandle, "<html><head><meta http-equiv='content-type' content='text/html;charset=utf-8'><style type='text/css'>a{text-decoration:none;}</style></head><body>");
		//fwrite($indexHandle, "<h4>测试数据文件列表：产品型号_测试时间_数据库中记录序号</h4>");
		fwrite($indexHandle, "<ol>");
		$file = array();
		foreach($arr as $value)
		{
			$id = $value['id'];
			$testTime = $value['test_time'];
			$testTimeFormated = preg_replace("/[\s:-]/", "", $testTime);
			$upload_date = $value['upload_date'];
			$producttype = $value['model'];
			$producttypeFormated = preg_replace("/\W/", "", $producttype);
			$sn = $value['ser_num'];
			
			
			$worker = $value['work_num'];
			$labelnum = $value['name'];
			$filename = $producttypeFormated."_".$sn."_".$testTimeFormated;
			if(!in_array($filename, $file)){
				fwrite($indexHandle, "<li><a href='".$filename.$slash.$filename.".html'>".$filename."</a></li>");
				array_push($file,$filename);
			}
			if(file_exists($fileDir.$slash.$filename) && is_dir($fileDir.$slash.$filename))
			{
			}
			else
			{
				mkdir($fileDir.$slash.$filename,777);
				$handle = fopen($fileDir.$slash.$filename.$slash.$filename.".html","a");
				fwrite($handle, "<html><head><meta http-equiv='content-type' content='text/html;charset=utf-8' /></head><body>");
				fwrite($handle, "<div style='margin-bottom:35px'><span style='font-size:25px;'>质量追溯报告</span></div>");
				fwrite($handle,"<div style='margin-bottom:5px;margin-top:5px;'><span style='width:100px;'>产品序列号：</span>".$sn."</div>");
				fwrite($handle,"<div style='margin-bottom:5px;margin-top:5px;'><span style='width:100px'>型号：</span>".$producttype."</div>");
				fwrite($handle,"<div style='margin-bottom:5px;margin-top:5px;'><span style='width:100px'>订单号：</span></div>");
				fwrite($handle,"<hr/>");
				fwrite($handle, "<div style='margin-bottom:5px;margin-top:5px;'><span style='color:blue;font-weight:bold;'>生产信息</span></div>");
				$vnaResultObject = $this->db->query("SELECT po.sn,po.id,pe.name AS producttype,po.testTime,po.equipmentSn,tn.name AS teststationName,tn.equipmentSn AS teststationSN,tr.name AS tester,po.result
													 FROM producttestinfo po 
													 JOIN producttype pe ON po.productType = pe.id
													 JOIN teststation tn ON po.testStation = tn.id
													 JOIN tester tr ON po.tester = tr.id
													 AND po.sn = '".$sn."'
													 AND pe.name = '".$producttype."'");
				$vnaResultArray = $vnaResultObject->result_array();
				if(count($vnaResultArray) != 0)
				{
					$vnaId = $vnaResultArray[0]["id"];
					$vnatestTime = $vnaResultArray[0]["testTime"];
					$vnaequipmentName = $vnaResultArray[0]["teststationName"];
					$vnaequipmentSn = $vnaResultArray[0]["teststationSN"];
					$vnaTester = $vnaResultArray[0]["tester"];
					$vnaResult = $vnaResultArray[0]["result"];
					if($vnaResult == 0){
						$vnaResult = "FAIL";
					}
					else
					{
						$vnaResult = "PASS";
					}
				
					fwrite($handle, "<div style='margin-bottom:10px;margin-top:10px;'><table border=1 cellspacing=0 width='570px'>");
					fwrite($handle, "<tr><th>测试项目</th><th>测试时间</th><th>测试设备型号</th><th>测试设备序列号</th><th>测试员</th><th>测试结果</th></tr>");
					fwrite($handle, "<tr><td>VNA</td><td>".$vnatestTime."</td><td>".$vnaequipmentName."</td><td>".$vnaequipmentSn."</td><td>".$vnaTester."</td><td>".$vnaResult."</td></tr>");
					fwrite($handle, "<tr><td>PIM</td><td>".$testTime."</td><td>&nbsp</td><td>&nbsp</td><td>".$worker."</td><td>&nbsp</td></tr>");
					fwrite($handle, "</table></div>");
					fwrite($handle, "<hr/>");					
					fwrite($handle, "<div style='margin-bottom:10px;margin-top:5px;'><span style='color:blue;font-weight:bold;'>VNA测试数据</span></div>");
					$vnaTestDataObject = $this->db->query("SELECT tm.name,tt.img,te.mark,te.value,te.channel,te.trace,tt.testResult 
													FROM testitemresult tt 
												 	JOIN testitemmarkvalue te ON te.testItemResult = tt.id 
												 	JOIN testitem tm ON tt.testItem=tm.id AND tt.productTestinfo = ".$vnaId);
					$vnaTestDataArray = $vnaTestDataObject->result_array();
					$arr = array();
					foreach ($vnaTestDataArray as $key=>$value)
					{
						if ($value['testResult'] == 0)
						{
							$value['testResult'] = "FAIL";
						}
						else
						{
							$value['testResult'] = "PASS";
						}
						if (!in_array($value['name'], $arr))
						{
							$imgServerRoot = "assets/uploadedSource/";
							$imgRoot = $value['img'];
							$start = strrpos($imgRoot, "\\")."...";
							$end  = strrpos($imgRoot,"-")."<br/>";
							$lenght = $end - $start; 
							$imgRoot = substr($imgRoot,0,$start+1).iconv("utf-8","gbk",substr($imgRoot, $start+1,$lenght-1))."-img.png";
							$imgDir = str_replace("/", "\\", getcwd()."\\assets\\uploadedSource\\".$imgRoot);
							$imgName = substr($imgDir, strrpos($imgDir, "\\") + 1);
							if(file_exists($imgDir)){
								copy($imgDir,$downloadDir."\\".$imgName);	
							}
							if (count($arr) == 0)
							{
								fwrite($handle, "<div style='font-size:15px;margin-top:5px;margin-bottom:5px;'><span style='width:70px;'>测试项:</span>".$value['name']."</div>");
								fwrite($handle, "<div><div style='float=left;margin-top:10px;margin-right:20px;'><img width='640px' height='480px' src='".iconv("gbk","utf-8",$imgName)."' /></div>");
							}
							else
							{
								fwrite($handle, "</table></div>");
								fwrite($handle, "<div style='font-size:15px;margin-top:5px;margin-bottom:5px;'><span style='width:70px;'>测试项:</span>".$value['name']."</div>");
								fwrite($handle, "<div style='float=left;margin-top:10px;margin-right:20px;'><img width='640px' height='480px' src='".iconv("gbk","utf-8",$imgName)."' /></div>");
							}
							array_push($arr, $value['name']);
							fwrite($handle, "<br/>");
							fwrite($handle, "<div style='height=480px'><table border=1 cellspacing=0 width='500px'><tr><th>Freq</th><th>value</th><th>channel</th><th>trace</th><th>结果</th></tr>");
							fwrite($handle, "<tr><td>".$value['mark']."</td><td>".$value['value']."</td><td>".$value['channel']."</td><td>".$value['trace']."</td><td>".$value['testResult']."</td></tr>");
						}
						else
						{
							fwrite($handle, "<tr><td>".$value['mark']."</td><td>".$value['value']."</td><td>".$value['channel']."</td><td>".$value['trace']."</td><td>".$value['testResult']."</td></tr>");
							if($key == count($vnaTestDataArray)-1){
								fwrite($handle, "</table></div>");
							}
						}	 
					}
					fwrite($handle, "</table></div>");
					$pimResultListObject = $this->db->query("SELECT pl.name,pp.test_time,pm.work_num,pp.upload_date,pp.id FROM pim_label pl 
												JOIN pim_ser_num pm ON pm.pim_label=pl.id 
												JOIN pim_ser_num_group pp ON pp.pim_ser_num = pm.id 
												WHERE pm.model = '".$producttype."' AND pm.ser_num = '".$sn."'");
					$pimResultListArray = $pimResultListObject->result_array();
					if(count($pimResultListArray) != 0){
						$pimEmployID = $pimResultListArray[0]['work_num'];
						$pimName = $pimResultListArray[0]['name'];
						fwrite($handle, "<hr/>");
						fwrite($handle,"<div style='color:blue;margin-top:5px;margin-bottom:5px;font-weight:bold;'>PIM测试数据</div>");
						fwrite($handle, "<div style='margin-top:10px;margin-bottom:5px;'><table border=1 cellspacing=0 width='500px'><tr>");
						for($i=1;$i <= count($pimResultListArray);$i++){
							fwrite($handle, "<th>数据".$i."</th>");
						}
						fwrite($handle, "</tr><tr>");
						foreach($pimResultListArray as $value)
						{
							$pimDataObject = $this->db->query("SELECT MAX(pa.value) AS value FROM pim_ser_num_group_data pa WHERE pa.pim_ser_num_group = ".$value['id']);
							$pimDataArray = $pimDataObject->result_array();
							if(count($pimDataArray) != 0) 
							{
								fwrite($handle,"<td style='text-align:center'>".$pimDataArray[0]['value']."</td>");
							}
						}
						fwrite($handle, "</tr></table><div>");
						foreach($pimResultListArray as $key=>$value)
						{
							$pimtesttimes = preg_replace("/[\s-:]/","", $value['test_time']);
							$pimImgDir = getcwd()."\\assets\\uploadedSource\\pim\\".str_replace("-", "_", $value['upload_date'])."\\".$value['name']."\\".$sn."_".$pimtesttimes.".jpg";
							$pimImageName = $sn."_".$pimtesttimes.".jpg";
							if(file_exists($pimImgDir)){
								copy($pimImgDir, $downloadDir.$slash.$currentTime.$slash.$filename.$slash.$pimImageName);
							}
							fwrite($handle, "<img alt='服务器上可能无此图片' style='margin-top:10px;margin-right:10px' width='640px' height='480px' src='".$pimImageName."'>");
						}
					}
				}													
			}	
		}
		fwrite($indexHandle, "</ol></body></html>");
		fclose($indexHandle);
		fwrite($handle, "</body></html>");
		fclose($handle);	
		
		$path = $downloadDir.$slash.$currentTime;
		$zip = new ZipArchive;
		$path = str_replace('\\', '/', $path)."/";
		$this->zip->read_dir($path,FALSE);
		$this->zip->download($currentTime.".zip");
	}

	public function vnaDetail($arg)
	{
		$vnaDetailObject = $this->db->query("SELECT pe.name AS producttype,po.sn,po.testTime,po.result,tn.name AS teststationName,tn.equipmentSn,tr.name AS testerName,tm.name AS testitem,tt.img,tt.testResult,te.value,te.mark,te.channel,te.trace FROM producttestinfo po 
						 					 JOIN testitemresult tt ON tt.productTestInfo = po.id
						 					 JOIN testitemmarkvalue te ON te.testItemResult = tt.id
						 					 JOIN testitem tm ON tt.testItem = tm.id
						 					 JOIN teststation tn ON po.testStation=tn.id
						   					 JOIN tester tr ON po.tester = tr.id
						 					 JOIN producttype pe ON po.productType = pe.id
						 					 AND po.id = ".$arg
						 					);
		$vnaDetailArray = $vnaDetailObject->result_array();
		if(count($vnaDetailArray) !=0 ){
			$sn = $vnaDetailArray[0]["sn"];
			$testTime = $vnaDetailArray[0]["testTime"];
			$teststationName = $vnaDetailArray[0]["teststationName"];
			$equipmentSn = $vnaDetailArray[0]["equipmentSn"];
			$tester = $vnaDetailArray[0]["testerName"];
			$producttype = $vnaDetailArray[0]['producttype'];
			$testresult = $vnaDetailArray[0]['result'];
			if($testresult == 0){
				$testresult = "FAIL";
			}else{
				$testresult = "PASS";
			}			
		}
		$sn = "";
		$testTime = "";
		$teststationName = "";
		$equipmentSn = "";
		$tester = "";
		$producttype = "";
		$testresult = "";
		$result = array();
		$testitem = array();
		foreach($vnaDetailArray as $value)
		{
			if(!in_array($value['testitem'],$testitem))
			{
				$arr = array($value['testitem'],$value['img'],array(array($value["value"],$value["mark"],$value["channel"],$value["trace"],$value["testResult"])));
				$result[$value['testitem']] = $arr;
				array_push($testitem,$value['testitem']);
			}	
			else
			{
				$arr = array($value["value"],$value["mark"],$value["channel"],$value["trace"],$value["testResult"]);
				array_push($result[$value['testitem']][2],$arr);
			}
		}
		$this->smarty->assign("tester",$tester);
		$this->smarty->assign("equipmentSn",$equipmentSn);
		$this->smarty->assign("teststationName",$teststationName);
		$this->smarty->assign("testtime",$testTime);
		$this->smarty->assign("sn",$sn);
		$this->smarty->assign("producttype",$producttype);
		$this->smarty->assign("testresult",$testresult);
		$this->smarty->assign("result",$result);
		$this->smarty->display("gqts_vna.tpl");
	}

	public function pimDetail($arg)
	{
		$pimTesttimeObject = $this->db->query("SELECT MAX(pp.test_time) AS pimtesttime FROM pim_ser_num pm
										JOIN pim_ser_num_group pp ON pp.pim_ser_num = pm.id
										AND pm.id = ".$arg);
		$pimTesttimeArray = $pimTesttimeObject->result_array();
		$pimTesttime = $pimTesttimeArray[0]['pimtesttime'];								
		$pimDetailObject = $this->db->query("SELECT pm.id,pl.name,pm.model,pm.ser_num,pm.work_num,pp.test_time,pp.upload_date,MAX(pa.value) AS value FROM pim_ser_num pm
											JOIN pim_label pl ON pm.pim_label=pl.id
											JOIN pim_ser_num_group pp ON pp.pim_ser_num = pm.id
											JOIN pim_ser_num_group_data pa ON pa.pim_ser_num_group = pp.id
											AND pm.id = ".$arg."
											GROUP BY pp.test_time
											");
		$pimDetailArray = $pimDetailObject->result_array();
		$this->smarty->assign("pimTesttime",$pimTesttime);
		$this->smarty->assign("pimDetailArray",$pimDetailArray);
		$this->smarty->display("gqts_pim.tpl");
	}
	*/
}
