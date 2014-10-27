<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');
class Cssj extends CW_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->_init();
		$this->load->library('zip');
	}

	private function _init()
	{
		$testResultList = array(
			''=>'所有',
			'0'=>'FAIL',
			'1'=>'PASS'
		);
		$this->smarty->assign('testResultList', $testResultList);
	}

	private function _checkDateFormat($date)
	{
		//match the format of the date
		if (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date, $parts))
		{
			//check weather the date is valid of not
			if (checkdate($parts[2], $parts[3], $parts[1]))
				return true;
			else
				return false;
		}
		else
			return false;
	}

	private function _checkTime($time)
	{
		$pattern = '/[\d]{4}-[\d]{1,2}-[\d]{1,2}\s[\d]{1,2}:[\d]{1,2}:[\d]{1,2}/';
		return preg_match($pattern, $time);
	}

	function index($offset = 0, $limit = 30)
	{
		$numArray = func_get_args();
		$num = 0;
		if (count($numArray) != 0)
		{
			$num = $numArray[0];
		}
		$timeFrom1 = emptyToNull($this->input->post('timeFrom1'));
		if ($timeFrom1 == null)
		{
			$timeFrom1 = 1900;
		}
		$timeFrom2 = emptyToNull($this->input->post('timeFrom2'));
		if ($timeFrom2 == null)
		{
			$timeFrom2 = 1;
		}
		$timeFrom3 = emptyToNull($this->input->post('timeFrom3'));
		if ($timeFrom3 == null)
		{
			$timeFrom3 = 1;
		}
		$timeFrom4 = emptyToNull($this->input->post('timeFrom4'));
		if ($timeFrom4 == null)
		{
			$timeFrom4 = 0;
		}
		$timeFrom5 = emptyToNull($this->input->post('timeFrom5'));
		if ($timeFrom5 == null)
		{
			$timeFrom5 = 0;
		}
		$timeFrom = $timeFrom1."-".$timeFrom2."-".$timeFrom3." ".$timeFrom4.":".$timeFrom5;
		$timeTo1 = emptyToNull($this->input->post('timeTo1'));
		if ($timeTo1 == null)
		{
			$timeTo1 = 2050;
		}
		$timeTo2 = emptyToNull($this->input->post('timeTo2'));
		if ($timeTo2 == null)
		{
			$timeTo2 = 1;
		}
		$timeTo3 = emptyToNull($this->input->post('timeTo3'));
		if ($timeTo3 == null)
		{
			$timeTo3 = 1;
		}
		$timeTo4 = emptyToNull($this->input->post('timeTo4'));
		if ($timeTo4 == null)
		{
			$timeTo4 = 23;
		}
		$timeTo5 = emptyToNull($this->input->post('timeTo5'));
		if ($timeTo5 == null)
		{
			$timeTo5 = 59;
		}
		$timeTo = $timeTo1."-".$timeTo2."-".$timeTo3." ".$timeTo4.":".$timeTo5;
		$testResult = emptyToNull($this->input->post('testResult'));
		$testStationName = emptyToNull($this->input->post('testStationName'));
		$productTypeName = emptyToNull($this->input->post('productTypeName'));
		$employeeId = emptyToNull($this->input->post('employeeId'));
		$sn = emptyToNull($this->input->post('sn'));
		//处理where条件
		$sqlTimeFrom = "";
		$sqlTimeTo = "";
		$sqlTestResult = "";
		$sqlTestStationName = "";
		$sqlProductTypeName = "";
		$sqlEmployeeId = "";
		$sqlSn = "";
		if ($timeFrom != null)
		{
			if ($this->_checkTime($timeFrom.":00"))
			{
				$sqlTimeFrom = " AND testTime >= '$timeFrom".":00"."'";
			}
			else
			{
				$sqlTimeFrom = " AND 0";
			}
		}
		if ($timeTo != null)
		{
			if ($this->_checkTime($timeTo.":59"))
			{
				$sqlTimeTo = " AND testTime <= '$timeTo".":59'";
			}
			else
			{
				$sqlTimeTo = " AND 0";
			}
		}
		if ($testResult != null)
		{
			if ($testResult == 0 || $testResult == 1)
			{
				$sqlTestResult = " AND result = '$testResult'";
			}
			else
			{
				$sqlTestResult = " AND 0";
			}
		}
		if ($testStationName != null)
		{
			$sqlTestStationName = " AND b.name like '%$testStationName%'";
		}
		if ($productTypeName != null)
		{
			$sqlProductTypeName = " AND d.name like '%$productTypeName%'";
		}
		if ($employeeId != null)
		{
			$sqlEmployeeId = " AND employeeId like '%$employeeId%'";
		}
		if ($sn != null)
		{
			$sqlSn = " AND sn like '%$sn%'";
		}
		//处理分页
		$this->load->library('pagination');
		$config['full_tag_open'] = '<div class="locPage">';
		$config['full_tag_close'] = '</div>';
		$config['base_url'] = '';
		$config['uri_segment'] = 3;
		//取得符合条件信息条数
		$tmpRes = $this->db->query("SELECT COUNT(*) num FROM productTestInfo a JOIN testStation b ON a.testStation = b.id JOIN tester c ON a.tester = c.id JOIN productType d ON a.productType = d.id WHERE 1".$sqlTimeFrom.$sqlTimeTo.$sqlTestResult.$sqlTestStationName.$sqlProductTypeName.$sqlEmployeeId.$sqlSn);
		$config['total_rows'] = $tmpRes->first_row()->num;
		$config['per_page'] = $limit;
		$this->pagination->initialize($config);
		$tmpRes = $this->db->query("SELECT a.id, a.testTime, a.testStation, a.tester, a.productType, a.sn, a.result, b.name testStationName, c.employeeId, d.name productTypeName FROM productTestInfo a JOIN testStation b ON a.testStation = b.id JOIN tester c ON a.tester = c.id JOIN productType d ON a.productType = d.id WHERE 1".$sqlTimeFrom.$sqlTimeTo.$sqlTestResult.$sqlTestStationName.$sqlProductTypeName.$sqlEmployeeId.$sqlSn." ORDER BY a.testTime DESC LIMIT ?, ?", array(
			(int)$offset,
			(int)$limit
		));
		$tmpArray = $tmpRes->result_array();
		if ($num == 1)
		{
			$this->getResultHtml($tmpArray);
		}
		$this->smarty->assign('productTestList', $tmpArray);
		$this->smarty->assign('title', '测试数据查询');
		$this->smarty->display('cssj.tpl');
	}

	public function getTestItemResult($productTestInfo)
	{
		$tmpRes = $this->db->query("SELECT a.id, a.img, b.name testItemName FROM testItemResult a JOIN testItem b ON a.testItem = b.id WHERE productTestInfo = ?", $productTestInfo);
		$testItemResultArray = $tmpRes->result_array();
		foreach ($testItemResultArray as &$item)
		{
			$tmpRes = $this->db->query("SELECT * FROM testItemMarkValue WHERE testItemResult = ?", $item['id']);
			$testItemMarkValueArray = $tmpRes->result_array();
			$item['testItemMarkValueArray'] = $testItemMarkValueArray;
		}
		$this->smarty->assign('productTestInfo', $productTestInfo);
		$this->smarty->assign('testItemResultList', $testItemResultArray);
		$this->smarty->display('cssj_testItem.tpl');
	}

	/*blow function is add by gary*/
	public function getResultHtml($tmpArray)
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
			$id = $value["id"];
			$sn = $value["sn"];
			$producttype = $value["productType"];
			$producttypeObject = $this->db->query("SELECT name FROM producttype WHERE id = ".$producttype);
			$producttypeArray = $producttypeObject->result_array();
			$producttype = $producttypeArray[0]['name'];
			$testtime = $value["testTime"];
			$preg_testtime = preg_replace("/[\s-:]/", "", $testtime);
			$testStationName = $value["testStationName"];
			$testStationSnList = $this->db->query("SELECT equipmentSn FROM teststation WHERE name = '".$testStationName."'");
			$testStationArray = $testStationSnList->result_array();
			$testStationSn = $testStationArray[0]["equipmentSn"];
			$employeeId = $value["employeeId"];
			$testResult = $value["result"];
			$downloadDir = $downloadRoot.$slash.$currentTime.$slash.$sn."_".$preg_testtime."_".$id;
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
												WHERE pm.model = 'Model Nr. ".$producttype."' AND pm.ser_num = '".$sn."') tt");
			$pimTesttimeArray = $pimTesttimeObject->result_array();
			$pimTesttime = $pimTesttimeArray[0]['time'];
			$pimWorkerId = $pimTesttimeArray[0]['work_num'];
			$pimResultListObject = $this->db->query("SELECT pl.name,pp.test_time,pm.work_num,pp.upload_date,pp.id FROM pim_label pl 
												JOIN pim_ser_num pm ON pm.pim_label=pl.id 
												JOIN pim_ser_num_group pp ON pp.pim_ser_num = pm.id 
												WHERE pm.model = 'Model Nr. ".$producttype."' AND pm.ser_num = '".$sn."'");
			$pimResultListArray = $pimResultListObject->result_array();
			$fileDir = $sn."_".$preg_testtime."_".$id."/".$sn."_".$preg_testtime."_".$id;
			fwrite($indexHandle, "<li><a href='./".$fileDir.".html'>".$sn."_".$preg_testtime."_".$id."</a></li>");
			$handle = fopen($downloadDir.$slash.$sn."_".$preg_testtime."_".$id.".html", "a");
			fwrite($handle, "<html><head><meta http-equiv='content-type' content='text/html;charset=utf-8' /></head><body>");
			fwrite($handle, "<div style='margin-bottom:35px'><span style='font-size:25px;'>质量追溯报告</span></div>");
			fwrite($handle,"<div style='margin-bottom:5px;margin-top:5px;'><span style='width:100px;'>产品序列号：</span>".$sn."</div>");
			fwrite($handle,"<div style='margin-bottom:5px;margin-top:5px;'><span style='width:100px'>型号：</span>".$producttype."</div>");
			fwrite($handle,"<div style='margin-bottom:5px;margin-top:5px;'><span style='width:100px'>订单号：</span></div>");
			fwrite($handle,"<hr/>");
			fwrite($handle, "<div style='margin-bottom:5px;margin-top:5px;'><span style='color:blue;font-weight:bold;'>生产信息</span></div>");
			fwrite($handle, "<div style='margin-bottom:10px;margin-top:10px;'><table border=1 cellspacing=0 width='570px'>");
			fwrite($handle, "<tr><th>测试项目</th><th>测试时间</th><th>测试设备型号</th><th>测试设备序列号</th><th>测试员</th><th>测试结果</th></tr>");
			fwrite($handle, "<tr><td>VNA</td><td>".$testtime."</td><td>".$testStationName."</td><td>".$testStationSn."</td><td>".$employeeId."</td><td>".$testResult."</td></tr>");
			fwrite($handle, "<tr><td>PIM</td><td>".$pimTesttime."</td><td>&nbsp</td><td>&nbsp</td><td>".$pimWorkerId."</td><td>&nbsp</td></tr>");
			fwrite($handle, "</table></div>");
			fwrite($handle, "<hr/>");
			fwrite($handle, "<div style='margin-bottom:10px;margin-top:5px;'><span style='color:blue;font-weight:bold;'>VNA测试数据</span></div>");

			
			$testDataList = $this->db->query("SELECT tm.name,tt.img,te.mark,te.value,te.channel,te.trace,tt.testResult FROM testitemresult tt JOIN testitemmarkvalue te ON te.testItemResult = tt.id JOIN testitem tm ON tt.testItem=tm.id AND tt.productTestinfo = ".$id);
			$testDataArray = $testDataList->result_array();
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
					if($key == count($testDataArray)-1){
						fwrite($handle, "</table></div>");
					}
				}	 
			}
			fwrite($handle, "</table></div>");
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
		$zip = new ZipArchive;
		$path = str_replace('\\', '/', $path)."/";
		$this->zip->read_dir($path,FALSE);
		$this->zip->download($currentTime.".zip");
	}
}
