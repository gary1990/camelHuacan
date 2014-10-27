<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');
class Login extends CW_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->helper('form');
		$this->load->helper('cookie');
	}

	public function index()
	{
		$this->session->sess_destroy();
		//取得首页通知内容
		$tmpRes = $this->db->query('SELECT content FROM firstpagenotice');
		if ($tmpRes)
		{
			$this->smarty->assign('noticeBody', $tmpRes->first_row()->content);
		}
		$this->smarty->display('login.tpl');
	}

	public function logout()
	{
		$this->session->sess_destroy();
		redirect(base_url()."index.php/login");
	}

	public function login2($userName = null, $password = null)
	{
		$this->session->sess_destroy();
		$_POST['userName'] = $userName;
		$_POST['password'] = $password;
		$this->validateLogin();
	}

	public function validateLogin()
	{
		$var = '';
		if ($this->_authenticate($var))
		{
			//登录成功
			$this->input->set_cookie('type', $this->input->post('type'), 3600 * 24 * 30);
			redirect(base_url().'index.php/sckb');
		}
		else
		{
			//登录失败
			$this->smarty->assign('loginErrorInfo', $var);
			$this->index();
		}
	}

	private function _checkDataFormat(&$result)
	{
		$this->load->library('form_validation');
		$config = array(
			array(
				'field'=>'userName',
				'label'=>'用户名',
				'rules'=>'required|callback_checkUsername1|callback_checkUsername2|callback_checkUsername3'
			),
			array(
				'field'=>'password',
				'label'=>'密码',
				'rules'=>'required|alpha_numeric|min_length[6]|max_length[20]'
			)
		);
		$this->form_validation->set_rules($config);
		$this->form_validation->set_error_delimiters('*', '<br>');
		if ($this->form_validation->run() == FALSE)
		{
			$result = validation_errors();
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}

	public function checkUsername1($str)
	{
		$r1 = preg_match("/^[\w\.]{6,15}$/", $str);
		if ($r1 == 0)
		{
			$this->form_validation->set_message('checkUsername1', '%s 只能包含英文字母，数字，下划线和点,长度为6-15.');
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}

	public function checkUsername2($str)
	{
		$docNum = substr_count($str, '.');
		$lineNum = substr_count($str, '_');
		if ($docNum + $lineNum > 1)
		{
			$this->form_validation->set_message('checkUsername2', '%s 只能包含一个下划线或点.');
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}

	public function checkUsername3($str)
	{
		$r1 = preg_match("/^\..*/", $str);
		$r2 = preg_match("/^_.*/", $str);
		$r3 = preg_match("/.*\.$/", $str);
		$r4 = preg_match("/.*_$/", $str);
		if ($r1 || $r2 || $r3 || $r4)
		{
			$this->form_validation->set_message('checkUsername3', '%s 不能以下划线或点开始或结束.');
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}

	private function _authenticate(&$var)
	{
		$this->lang->load('form_validation', 'chinese');
		//check data format
		if (!($this->_checkDataFormat($result)))
		{
			$var = $result;
			return FALSE;
		}
		else
		{
			$tmpRes = $this->db->query('SELECT * FROM user WHERE userName = ?', strtolower($this->input->post('userName')));
			if ($tmpRes)
			{
				if ($tmpRes->num_rows() > 0)
				{
					$tmpArr = $tmpRes->first_row('array');
					if ($tmpArr['password'] == strtolower($this->input->post('password')))
					{
						$this->session->set_userdata('username', strtolower($this->input->post('userName')));
						$this->session->set_userdata('userId', $tmpArr['id']);
						return TRUE;
					}
					else
					{
						//密码错误
						$var = "*密码错误，请仔细检查";
						return FALSE;
					}
				}
				else
				{
					//用户名不存在
					$var = "*无此用户,请重新输入";
					return FALSE;
				}
			}
			else
			{
				//查询失败
				$var = "*系统繁忙，请稍后尝试进入";
				return FALSE;
			}
		}
	}

	public function clientLogin($username = null, $password = null, $equipmentSn = null)
	{
		$this->load->helper('xml');
		$root = xml_dom();
		$dom = xml_add_child($root, 'info');
		//检查用户名密码
		if ($tmpArray = $this->_checkTestUser($username, $password))
		{
			//取得测试员姓名,员工号,权限
			$username = xml_add_child($dom, 'username');
			xml_add_child($username, 'result', 'true');
			xml_add_child($username, 'name', $tmpArray['testerName']);
			xml_add_child($username, 'id', $tmpArray['testerId']);
			xml_add_child($username, 'test_right_id', $tmpArray['testRightId']);
			xml_add_child($username, 'test_right_name', $tmpArray['testRightName']);
			//根据$equipmentSn取得站点id,站点名称
			$tmpRes = $this->db->query('SELECT * FROM testStation WHERE equipmentSn = ?', array($equipmentSn));
			if ($tmpRes->num_rows() > 0)
			{
				$testStation = xml_add_child($dom, 'test_station');
				xml_add_child($testStation, 'result', 'true');
				xml_add_child($testStation, 'id', $tmpRes->first_row()->id);
				xml_add_child($testStation, 'name', $tmpRes->first_row()->name);
			}
			else
			{
				//没有查到站点
				$testStation = xml_add_child($dom, 'test_station');
				xml_add_child($testStation, 'result', 'false');
			}
			//取得产品类型列表
			$tmpRes = $this->db->query('SELECT * FROM productType a JOIN (SELECT DISTINCT productType FROM producttypetestcase) b ON a.id = b.productType ORDER BY id');
			if ($tmpRes->num_rows() > 0)
			{
				$productTestCase = xml_add_child($dom, 'product_test_case');
				xml_add_child($productTestCase, 'result', 'true');
				$tmpProductTypeArray = $tmpRes->result_array();
				foreach ($tmpProductTypeArray as $productTypeItem)
				{
					$productType = xml_add_child($productTestCase, 'product_type');
					xml_add_child($productType, 'id', $productTypeItem['id']);
					xml_add_child($productType, 'name', $productTypeItem['name']);
					//取得产品测试案例内容
					$tmpRes = $this->db->query('SELECT a.testItem, b.name testItemName, a.stateFile, a.portNum FROM productTypeTestCase a JOIN testItem b ON a.testItem = b.id WHERE a.productType = ? ORDER BY a.testItem', array($productTypeItem['id']));
					if ($tmpRes->num_rows() > 0)
					{
						$tmpTestItemArray = $tmpRes->result_array();
						$testItem = xml_add_child($productType, 'test_item');
						xml_add_child($testItem, 'result', 'true');
						foreach ($tmpTestItemArray as $testItemItem)
						{
							xml_add_child($testItem, 'id', $testItemItem['testItem']);
							xml_add_child($testItem, 'name', $testItemItem['testItemName']);
							xml_add_child($testItem, 'state_file', $testItemItem['stateFile']);
							xml_add_child($testItem, 'port_num', $testItemItem['portNum']);
						}
					}
					else
					{
						$testItem = xml_add_child($productType, 'test_item');
						xml_add_child($testItem, 'result', 'false');
					}
				}
			}
			else
			{
				$productTestCase = xml_add_child($dom, 'product_test_case');
				xml_add_child($productTestCase, 'result', 'false');
			}
		}
		else
		{
			$username = xml_add_child($dom, 'username');
			xml_add_child($username, 'result', 'false');
		}
		xml_print($root);
	}

	private function _checkTestUser($username, $password)
	{
		//检查用户名密码
		$tmpRes = $this->db->query('SELECT a.id testerId, a.name testerName, a.employeeId, b.id testRightId, b.name testRightName FROM tester a JOIN testRight b ON a.testRight = b.id WHERE a.employeeId = ? AND password = ?', array(
			$username,
			$password
		));
		if ($tmpRes->num_rows() > 0)
		{
			return $tmpRes->first_row('array');
		}
		else
		{
			return FALSE;
		}
	}

	public function uploadFile($username = null, $password = null)
	{
		if (PHP_OS == 'WINNT')
		{
			$uploadRoot = "D:\\camel\\camel\\assets\\uploadedSource";
			$slash = "\\";
		}
		else if (PHP_OS == 'Darwin')
		{
			$uploadRoot = "/Library/WebServer/Documents/aptana/xiong/assets/uploadedSource";
			$slash = "/";
		}
		else
		{
			$this->_returnUploadFailed("错误的服务器操作系统");
			return;
		}
		if ($this->_checkTestUser($username, $password) === FALSE)
		{
			$this->_returnUploadFailed("错误的用户名密码");
			return;
		}
		else
		{
			//保存上传文件
			$file_temp = $_FILES['file']['tmp_name'];
			date_default_timezone_set('Asia/Shanghai');
			$dateStamp = date("Y_m_d");
			$dateStampFolder = $uploadRoot.$slash.$dateStamp;
			if (file_exists($dateStampFolder) && is_dir($dateStampFolder))
			{
				//do nothing
			}
			else
			{
				if (mkdir($dateStampFolder))
				{
				}
				else
				{
					$this->_returnUploadFailed("日期目录创建失败");
					return;
				}
			}
			$file_name = $dateStamp.$slash.$_FILES['file']['name'];
			//complete upload
			$filestatus = move_uploaded_file($file_temp, $uploadRoot.$slash.$file_name);
			if (!$filestatus)
			{
				$this->_returnUploadFailed("文件:".$_FILES['file']['name']."上传失败");
				return;
			}
			//解压缩文件
			if (PHP_OS == 'WINNT')
			{
				$file = $uploadRoot.$slash.$file_name;
				$file1 = str_replace(' ', '', $file);
				rename($file,$file1);
				exec('C:\Progra~1\7-Zip\7z.exe x '.$file1.' -o'.$uploadRoot.$slash.$dateStamp.' -y', $info);
			}
			else if (PHP_OS == 'Darwin')
			{
				$zip = new ZipArchive;
				if ($zip->open($uploadRoot.$slash.$file_name) === TRUE)
				{
					$zip->extractTo($uploadRoot.$slash.$dateStamp.$slash);
					$zip->close();
					//关闭处理的zip文件
				}
				else
				{
					$this->_returnUploadFailed("文件:".$_FILES['file']['name']."打开失败");
					return;
				}
			}
			//解析文件并插入数据库
			$this->db->trans_start();
			//解析General.csv
			if ($handle = fopen($uploadRoot.$slash.$dateStamp.$slash.substr($_FILES['file']['name'], 0, -4).$slash.'General.csv', "r"))
			{
				$i = 0;
				while (($buffer = fgets($handle)) !== false)
				{
					$i = $i + 1;
					if ($i == 1)
					{
						$tmpArray = explode(",", $buffer);
						continue;
					}
					$tmpArray = explode(",", $buffer);
					//取得测试时间
					$testTime = $tmpArray[0];
					//取得测试站号
					$tmpRes = $this->db->query("SELECT id FROM testStation WHERE name = ?", array($tmpArray[1]));
					if ($tmpRes->num_rows() == 0)
					{
						$this->db->trans_rollback();
						$this->_returnUploadFailed("文件:".$_FILES['file']['name']."中General.csv中(".$buffer.")对应测试站点没有找到");
						return;
					}
					else
					{
						$testStation = $tmpRes->first_row()->id;
					}
					//取得设备序列号
					$equipmentSn = $tmpArray[2];
					//取得测试者id
					$tmpRes = $this->db->query("SELECT id FROM tester WHERE employeeId = ?", array($tmpArray[3]));
					if ($tmpRes->num_rows() == 0)
					{
						$this->db->trans_rollback();
						$this->_returnUploadFailed("文件:".$_FILES['file']['name']."中General.csv中(".$buffer.")对应测试者没有找到");
						return;
					}
					else
					{
						$tester = $tmpRes->first_row()->id;
					}
					//取得产品类型
					$tmpRes = $this->db->query("SELECT id FROM producttype WHERE name = ?", array($tmpArray[4]));
					if ($tmpRes->num_rows() == 0)
					{
						$this->db->trans_rollback();
						$this->_returnUploadFailed("文件:".$_FILES['file']['name']."中General.csv中(".$buffer.")对应产品类型没有找到");
						return;
					}
					else
					{
						$productType = $tmpRes->first_row()->id;
					}
					//取得产品SN
					$sn = $tmpArray[5];
					//处理测试结果
					if ($tmpArray[6] == 'PASS')
					{
						$testResult = 1;
					}
					else
					{
						$testResult = 0;
					}
					//处理客户化数据
					$cus1 = $tmpArray[7];
					$cus2 = $tmpArray[8];
					$cus3 = $tmpArray[9];
					$cus4 = $tmpArray[10];
					$cus5 = $tmpArray[11];
					//插入producttestinfo
					$tmpSql = "INSERT INTO `producttestinfo`(`sn`, `equipmentSn`, `testTime`, `testStation`, `tester`, `productType`, `result`, `column1`, `column2`, `column3`, `column4`, `column5`, `column6`, `column7`, `column8`, `column9`, `column10`) ";
					$tmpSql .= "VALUES ('$sn','$equipmentSn','$testTime'+ INTERVAL 0 SECOND,$testStation,$tester,$productType,$testResult,'$cus1','$cus2','$cus3','$cus4','$cus5',null,null,null,null,null)";
					$tmpRes = $this->db->query($tmpSql);
					if ($tmpRes === TRUE)
					{
						//取得producttestinfo id
						$productTestInfo = $this->db->insert_id();
						//取得测试项名称
						$testItemList = $this->_getDirFiles($uploadRoot.$slash.$dateStamp.$slash.substr($_FILES['file']['name'], 0, -4).$slash, 'csv', 'General.csv');
						foreach ($testItemList as $testItemItem)
						{
							//插入testitemresult
							//转换csv文件名
							if (PHP_OS == 'WINNT')
							{
								$fileName = $testItemItem;
							}
							else if (PHP_OS == 'Darwin')
							{
								$fileName = urldecode($testItemItem);
							}
							//取得测试项目名称
							$tmpArray = preg_split("[-|\.]", $fileName);
							$testItemName = $tmpArray[0];
							//取得测试项目id
							$tmpRes = $this->db->query("SELECT id FROM testitem WHERE name = ?", array(iconv('GB2312', 'UTF-8', $testItemName)));
							if ($tmpRes->num_rows() > 0)
							{
								$testItem = $tmpRes->first_row()->id;
							}
							else
							{
								$this->db->trans_rollback();
								$this->_returnUploadFailed("文件:".$_FILES['file']['name']."中没有找到对应测试项目名称:".iconv('GB2312', 'UTF-8', $testItemName));
								return;
							}
							$testResult = $tmpArray[1] == 'PASS' ? 1 : 0;
							//取得图片文件名称
							if (PHP_OS == 'WINNT')
							{
								$imgFile = iconv('GB2312', 'UTF-8', substr($testItemItem, 0, -9)."-img.png");
							}
							else if (PHP_OS == 'Darwin')
								$imgFile = substr($testItemItem, 0, -9)."-img.png";
							{
							}
							$testItemImg = $dateStamp.$slash.substr($_FILES['file']['name'], 0, -4).$slash.$imgFile;
							//插入testitemresult
							$tmpRes = $this->db->query("INSERT INTO `testitemresult`(`productTestInfo`, `testItem`, `testResult`, `img`) VALUES ($productTestInfo, $testItem, $testResult, ?)", array($testItemImg));
							if ($tmpRes === TRUE)
							{
								//取得testitemresult id
								$testItemResult = $this->db->insert_id();
								//处理testItem文件
								if ($handle2 = fopen($uploadRoot.$slash.$dateStamp.$slash.substr($_FILES['file']['name'], 0, -4).$slash.$testItemItem, "r"))
								{
									$i2 = 0;
									while (($buffer2 = fgets($handle2)) !== false)
									{
										$i2 = $i2 + 1;
										if ($i2 == 1)
										{
											$tmpArray2 = explode(",", $buffer2);
											continue;
										}
										$tmpArray2 = explode(",", $buffer2);
										//取得testResult
										$singleTestResult = $tmpArray2[1];
										//取得mark
										$singleTextMark = $tmpArray2[0];
										//取得channel
										$singleTextChannel = $tmpArray2[2];
										//取得trace
										$singleTextTrace = $tmpArray2[3];
										$tmpRes = $this->db->query("INSERT INTO `testitemmarkvalue`(`testItemResult`, `value`, `mark`, `channel`, `trace`) VALUES (?, ?, ?, ?, ?)", array(
											$testItemResult,
											$singleTestResult,
											$singleTextMark,
											$singleTextChannel,
											$singleTextTrace
										));
										if ($tmpRes === TRUE)
										{
											//do nothing
										}
										else
										{
											$this->db->trans_rollback();
											$this->_returnUploadFailed("文件:".$_FILES['file']['name']."中".iconv('GB2312', 'UTF-8', $testItemName).":$buffer2 插入失败");
											return;
										}
									}
									fclose($handle2);
								}
								else
								{
									$this->_returnUploadFailed("文件:$fileName 打开失败");
									return;
								}
							}
							else
							{
								$this->db->trans_rollback();
								$this->_returnUploadFailed("文件:".$_FILES['file']['name']."中$testItemItem 插入testitemresult失败");
								return;
							}
						}
					}
					else
					{
						$this->db->trans_rollback();
						$this->_returnUploadFailed("文件:".$_FILES['file']['name']."中General.csv中(".$buffer.")插入producttestinfo失败");
						return;
					}
				}
				fclose($handle);
			}
			else
			{
				$this->_returnUploadFailed("文件:General.csv 打开失败");
				return;
			}
		}
		$this->_returnUploadOk();
		return;
	}

	private function _returnUploadOK()
	{
		$this->db->trans_commit();
		$this->load->helper('xml');
		$dom = xml_dom();
		$uploadResult = xml_add_child($dom, 'uploadResult');
		xml_add_child($uploadResult, 'result', 'true');
		xml_add_child($uploadResult, 'info', 'success');
		xml_print($dom);
	}

	private function _returnUploadOK2($str)
	{
		//test
		echo $str;
		//end test
		$this->db->trans_commit();
		$this->load->helper('xml');
		$dom = xml_dom();
		$uploadResult = xml_add_child($dom, 'uploadResult');
		xml_add_child($uploadResult, 'result', 'true');
		xml_add_child($uploadResult, 'info', 'success');
		xml_print($dom);
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

	private function _getDirFiles($dir, $extension, $except)
	{
		if ($handle = opendir($dir))
		{
			$files = array();
			/* Because the return type could be false or other equivalent type(like 0),
			 this is the correct way to loop over the directory. */
			while (false !== ($file = readdir($handle)))
			{
				if (($file != 'General.csv') && substr($file, strrpos($file, '.') + 1) == $extension)
				{
					$files[] = $file;
				}
			}
			closedir($handle);
			return $files;
		}
		else
		{
			return FALSE;
		}
	}

	public function uploadPimFile($username = null, $password = null, $ordernum = null)
	{
		//error_reporting(0);
		if (PHP_OS == 'WINNT')
		{
			$uploadRoot = "E:\\wwwRoot\\camel\\assets\\uploadedSource\\pim";
			$slash = "\\";
		}
		else if (PHP_OS == 'Darwin')
		{
			$uploadRoot = "/Library/WebServer/Documents/aptana/xiong/assets/uploadedSource/pim";
			$slash = "/";
		}
		else
		{
			$this->_returnUploadFailed("错误的服务器操作系统");
			return;
		}
		/*		if ($this->_checkTestUser($username, $password) === FALSE)
		 {
		 $this->_returnUploadFailed("错误的用户名密码");
		 return;
		 }
		 else
		 {*/
		//保存上传文件
		//$file_temp = $_FILES['Filedata']['tmp_name'];
		$file_temp = $_FILES['file']['tmp_name'];
		date_default_timezone_set('Asia/Shanghai');
		$dateStamp = date("Y_m_d");
		$dateStampFolder = $uploadRoot.$slash.$dateStamp;
		if (file_exists($dateStampFolder) && is_dir($dateStampFolder))
		{
			//do nothing
		}
		else
		{
			if (mkdir($dateStampFolder))
			{
			}
			else
			{
				$this->_returnUploadFailed("日期目录创建失败");
				return;
			}
		}
		$file_name = $dateStamp.$slash.$_FILES['file']['name'];
		//complete upload
		//解压前先删除旧文件
		if (file_exists($uploadRoot.$slash.$file_name))
		{
			unlink($uploadRoot.$slash.$file_name);
		}
		$filestatus = move_uploaded_file($file_temp, $uploadRoot.$slash.$file_name);
		if (!$filestatus)
		{
			$this->_returnUploadFailed("文件:".$_FILES['file']['name']."上传失败");
			return;
		}
		//解压缩文件
		//解压前先删除旧文件夹
		$this->delDirAndFile($uploadRoot.$slash.substr($file_name, 0, -4));
		if (PHP_OS == 'WINNT')
		{
			exec('C:\Progra~1\7-Zip\7z.exe x '.$uploadRoot.$slash.$file_name.' -o'.$uploadRoot.$slash.substr($file_name, 0, -4).' -y', $info);
		}
		else if (PHP_OS == 'Darwin')
		{
			$zip = new ZipArchive;
			if ($zip->open($uploadRoot.$slash.$file_name) === TRUE)
			{
				$zip->extractTo($uploadRoot.$slash.substr($file_name, 0, -4).$slash);
				$zip->close();
				//关闭处理的zip文件
			}
			else
			{
				$this->_returnUploadFailed("文件:".$_FILES['file']['name']."打开失败");
				return;
			}
		}
		//解析文件并插入数据库
		$this->db->trans_start();
		//初始化pim_label(工单号)
		$pim_label = substr($file_name, strrpos($file_name, $slash) + 1, -4);
		//对pim_label插入数据
		$tmpSql = "INSERT INTO `pim_label`(`name`) ";
		$tmpSql .= "VALUES ('".$pim_label."')";
		$tmpRes = $this->db->query($tmpSql);
		if ($tmpRes === TRUE)
		{
			//取得pim_label id
			$pim_label = $this->db->insert_id();
			//取得所有csv文件列表
			//get all image files with a .cvs extension.
			$csvArray = glob($uploadRoot.$slash.substr($file_name, 0, -4).$slash."*.csv");
			//print each file name
			foreach ($csvArray as $csv)
			{
				//解析单个csv文件
				//从csv文件名取得序列号
				$ser_num = substr($csv, strrpos($csv, $slash) + 1, -4);
				if ($file_content = file_get_contents($csv))
				{
					//去除csv文件引号中的换行符号
					$pattern = '/"([0-9.;\-]+)\r\n"/';
					$replacement = '"${1}"';
					$file_content = preg_replace($pattern, $replacement, $file_content);
					//一个line表示一个组
					$lines = explode("\n", str_replace("\r", "", $file_content));
					//删除lines中由最后一个回车换行造成的空元素
					array_pop($lines);
					$firstGroup = true;
					$groupTestTime = 0;
					foreach ($lines as $line)
					{
						$lineContentArray = explode(",", $line);
						$lineContentArray = $this->_trimQuoterMark($lineContentArray);

						//如果是第一个组,使用此组值来初始化pim_ser_num中的值					
						if ($firstGroup)
						{
							$tmpSql = "INSERT INTO `pim_ser_num`(`work_num`, `test_time`, `model`, `ser_num`, `pim_label`, `col1`, `col2`, `col3`, `col4`, `col5`, `col6`, `col7`, `col8`, `col9`, `col10`, `col11`, `col12`, `col13`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
							$tmpRes = $this->db->query($tmpSql, array(
								' ',
								'0000-00-00 00:00:00',
								$lineContentArray[11],
								$ser_num,
								$pim_label,
								$lineContentArray[0],
								$lineContentArray[1],
								$lineContentArray[2],
								$lineContentArray[3],
								$lineContentArray[4],
								$lineContentArray[5],
								$lineContentArray[6],
								$lineContentArray[7],
								$lineContentArray[8],
								$lineContentArray[9],
								$lineContentArray[10],
								$lineContentArray[13],
								$lineContentArray[15]
							));
							if ($tmpRes === TRUE)
							{
								//取得pim_ser_num id
								$pim_ser_num = $this->db->insert_id();
								//插入pim_ser_num_data
							}
							else
							{
								$this->db->trans_rollback();
								$this->_returnUploadFailed("插入pim_ser_num失败!原始数据:$csv中$line");
								return;
							}
						}
						//取得当前组最近时间
						$tmpTestTime = date('Y-m-d H:i:s', strtotime($lineContentArray[12]));
						$groupTestTime = ($tmpTestTime > $groupTestTime) ? $tmpTestTime : $groupTestTime;
						//检查对应组图片是否存在
						$jpgFile = $uploadRoot.$slash.substr($file_name, 0, -4).$slash.$ser_num.'_'.(str_replace(' ', '', $lineContentArray[12])).".jpg";
						if (!file_exists($jpgFile))
						{
							$this->db->trans_rollback();
							$this->_returnUploadFailed("$jpgFile,插入pim_ser_num_group时对应图片没有找到!原始数据:{$csv}中{$line}中");
							return;
						}
						//插入pim_ser_num_group
						$tmpSql = "INSERT INTO `pim_ser_num_group`(`pim_ser_num`, `test_time`, `upload_date`) VALUES (?, ?, ?)";
						$tmpRes = $this->db->query($tmpSql, array(
							$pim_ser_num,
							$tmpTestTime,
							$dateStamp
							
						));
						if ($tmpRes === TRUE)
						{
							//取得pim_ser_num_group id
							$pim_ser_num_group = $this->db->insert_id();
							//插入pim_ser_num_group_data数据
							for ($i = 16; $i < count($lineContentArray); $i++)
							{
								$tmpSql = "INSERT INTO `pim_ser_num_group_data`(`pim_ser_num_group`, `frequency`, `value`) VALUES ($pim_ser_num_group,?,?)";
								$tmpRes = $this->db->query($tmpSql, explode(';', $lineContentArray[$i]));
								if ($tmpRes === TRUE)
								{
								}
								else
								{
									$this->db->trans_rollback();
									$this->_returnUploadFailed("插入pim_ser_num_group_data失败!原始数据:$csv中$line中".$lineContentArray[$i]);
									return;
								}
							}
						}
						else
						{
							$this->db->trans_rollback();
							$this->_returnUploadFailed("插入pim_ser_num_group失败!原始数据:$csv中$line");
							return;
						}
						//设置本组测试时间
						$tmpSql = "UPDATE `pim_ser_num_group` SET `test_time`=? WHERE id = ?";
						$tmpRes = $this->db->query($tmpSql, array(
							$groupTestTime,
							$pim_ser_num_group
						));
						if ($tmpRes)
						{
						}
						else
						{
							$this->db->trans_rollback();
							$this->_returnUploadFailed("更新pim_ser_num_group测试时间失败!原始数据:$csv中$line");
							return;
						}
						$firstGroup = false;					
					}
				}
				else
				{
					$this->db->trans_rollback();
					$this->_returnUploadFailed("打开文件$csv失败!");
					return;
				}
			}
			$this->_returnUploadOk();
			return;
		}
		else
		{
			$this->db->trans_rollback();
			$this->_returnUploadFailed("创建工单号$pim_label失败!");
			return;
		}
	}

	//去除包含数组元素的引号，处理测试结果为空的数据
	private function _trimQuoterMark($array)
	{
		foreach ($array as &$item)
		{
			if(strlen($item) == 0)
			{
				$item = "100;100";
			}
			if(substr($item,0,1) == "\"")
			{
				$item = substr($item, 1, -1);
			}
		}
		return $array;
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
							delDirAndFile($dirName.$slash.$item);
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

	public function packingConnectCheck()
	{
		$result = "<result>connected</result>";
		print($result);
	}
	
	public function packingUserCheck()
	{
		$userId = $_POST["packinguserid"];
		$userPassword = $_POST["packinguserpassword"];
		
		$passWord = $this->db->query("SELECT password,name FROM packingemployees WHERE employeeId = '".$userId."'");
		$num = $passWord->num_rows();
		if($num == 0)
		{
			print("<result><info>工号或密码填写错误</info></result>");
		}
		else
		{
			$password = $passWord->first_row()->password;
			$employeename = $passWord->first_row()->name;
			if($password == $userPassword)
			{
				print("<result><info>yes</info><employeename>".$employeename."</employeename></result>");
			}
			else
			{
				print("<result><info>工号或密码填写错误</info></result>");
			}
		}
	}
	
	public function packingProductSnCheck()
	{
		$sn = $_POST["productsn"];
		$pimstate = $_POST["pimstate"];
		$packer = $_POST["packer"];
		$ordernum = $_POST["ordernum"];
		$boxsn = $_POST["boxsn"];
		$packingTime = date("Y-m-d H:i:s");
		if($pimstate == "pimcheck")
		{
			$pimSn = $this->db->query("SELECT ser_num FROM pim_ser_num WHERE ser_num = '".$sn."'"); 
			if($pimSn->num_rows() == 0)
			{
				$this->db->query("INSERT INTO packingresult (packingtime,boxsn,productsn,ordernum,packer,result) 
							VALUES ('".$packingTime."','','".$sn."','".$ordernum."','".$packer."','UNTESTED')");
				print("<result><info>pimresultnull</info></result>");
			}
			else
			{
				$pimResultSql = "SELECT pm.model,pm.col12,pa.value FROM pim_ser_num pm 
									JOIN pim_ser_num_group pp 
									ON pp.pim_ser_num = pm.id
									JOIN pim_ser_num_group_data pa 
									ON pa.pim_ser_num_group = pp.id
									WHERE pm.ser_num = '".$sn."'";
				$pimResult = $this->db->query($pimResultSql);
				$pimResultArray = $pimResult->result_array();
				$limtLineResult = $pimResultArray[0]['col12'];
				$productType = 
				$limitLine = substr($limtLineResult, 11);
				$loopNum = 0;
				foreach($pimResultArray as $value)
				{
					if($value['value'] < $limitLine)
					{
						$loopNum++;
					}
					else
					{
						$this->db->query("INSERT INTO packingresult (packingtime,boxsn,productsn,ordernum,packer,result) 
										VALUES ('".$packingTime."','','".$sn."','".$ordernum."','".$packer."','FAIL')");
						print("<result><info>pimresultfail</info></result>");
						return;					
					}
				}
				if($loopNum == count($pimResultArray))
				{
					$vnaResultSql = "SELECT result FROM producttestinfo po WHERE po.sn = '".$sn."'";
					$vnaResultObject = $this->db->query($vnaResultSql);
					$vnaResultArray = $vnaResultObject->result_array();
					if(count($vnaResultArray) == 0)
					{
						$this->db->query("INSERT INTO packingresult (packingtime,boxsn,productsn,ordernum,packer,result) 
										VALUES ('".$packingTime."','','".$sn."','".$ordernum."','".$packer."','UNTESTED')");
						print("<result><info>vnaresultnull</info></result>");
					}
					else
					{
						$vnaResult = $vnaResultArray[0]['result'];
						if($vnaResult == 1)
						{
							$this->db->query("INSERT INTO packingresult (packingtime,boxsn,productsn,ordernum,packer,result) 
										VALUES ('".$packingTime."','".$boxsn."','".$sn."','".$ordernum."','".$packer."','PASS')");
							print("<result><info>pass</info></result>");
						}
						else
						{
							$this->db->query("INSERT INTO packingresult (packingtime,boxsn,productsn,ordernum,packer,result) 
										VALUES ('".$packingTime."','','".$sn."','".$ordernum."','".$packer."','FAIL')");
							print("<result><info>vnaresultfail</info></result>");
						}
					}
				}
			}
		}
		else if($pimstate == "pimuncheck")
		{
			$pimSn = $this->db->query("SELECT ser_num FROM pim_ser_num WHERE ser_num = '".$sn."'"); 
			if($pimSn->num_rows() != 0)
			{
				print("<result><info>pimexsit</info></result>");
			}
			else
			{
				$vnaResultSql = "SELECT result FROM producttestinfo po WHERE po.sn = '".$sn."'";
				$vnaResultObject = $this->db->query($vnaResultSql);
				$vnaResultArray = $vnaResultObject->result_array();
				if(count($vnaResultArray) == 0)
				{
					$this->db->query("INSERT INTO packingresult (packingtime,boxsn,productsn,ordernum,packer,result) 
										VALUES ('".$packingTime."','','".$sn."','".$ordernum."','".$packer."','UNTESTED')");
					print("<result><info>vnaresultnull</info></result>");
				}
				else
				{
					$vnaResult = $vnaResultArray[0]['result'];
					if($vnaResult == 1)
					{
						$this->db->query("INSERT INTO packingresult (packingtime,boxsn,productsn,ordernum,packer,result) 
										VALUES ('".$packingTime."','".$boxsn."','".$sn."','".$ordernum."','".$packer."','PASS')");
						print("<result><info>pass</info></result>");
					}
					else
					{
						$this->db->query("INSERT INTO packingresult (packingtime,boxsn,productsn,ordernum,packer,result) 
										VALUES ('".$packingTime."','','".$sn."','".$ordernum."','".$packer."','FAIL')");
						print("<result><info>vnaresultfail</info></result>");
					}
				}
			}
		}
		else
		{
			$vnaResultSql = "SELECT result FROM producttestinfo po WHERE po.sn = '".$sn."'";
			$vnaResultObject = $this->db->query($vnaResultSql);
			$vnaResultArray = $vnaResultObject->result_array();
			if(count($vnaResultArray) == 0)
			{
				$this->db->query("INSERT INTO packingresult (packingtime,boxsn,productsn,ordernum,packer,result) 
										VALUES ('".$packingTime."','','".$sn."','".$ordernum."','".$packer."','UNTESTED')");
				print("<result><info>vnaresultnull</info></result>");
			}
			else
			{
				$vnaResult = $vnaResultArray[0]['result'];
				if($vnaResult == 1)
				{
					$this->db->query("INSERT INTO packingresult (packingtime,boxsn,productsn,ordernum,packer,result) 
										VALUES ('".$packingTime."','".$boxsn."','".$sn."','".$ordernum."','".$packer."','PASS')");
					print("<result><info>pass</info></result>");
				}
				else
				{
					$this->db->query("INSERT INTO packingresult (packingtime,boxsn,productsn,ordernum,packer,result) 
										VALUES ('".$packingTime."','','".$sn."','".$ordernum."','".$packer."','FAIL')");
					print("<result><info>vnaresultfail</info></result>");
				}
			}	
		}
	}
	
}

/*end*/
