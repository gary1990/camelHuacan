<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');
class Login extends CW_Controller
{
	//public $filePath = "C:\\projects\\PHP\\camelHuacan\\assets\\uploadedSource";
    //TODO publish replace
    //private $filePath = "/Users/garychen/Sites/camelHuacan/assets/uploadedSource";
    private $slash = "/";
    //private $filePath = "D:\\camel\\camel\\assets\\uploadedSource";
    //private $slash = "\\";
    //for kingsignal
    private $filePath = "E:\\camel\\projects\\camel\\uploadedSource";

    private $vnaClientName = "Kamel VNA Application for TS - HuaC";
    private $vnaClientVersion = "V2.2.0";

	public function __construct()
	{
		parent::__construct();
		$this->load->helper('form');
		$this->load->helper('cookie');
	}

	public function index()
	{
		$this->session->sess_destroy();
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
		$this->smarty->display('login1.tpl');
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
			$this->toIndex();
		}
		else
		{
			//登录失败
			$this->smarty->assign('loginErrorInfo', $var);
			$this->index();
		}
	}
	
	public function toIndex()
	{
		$today = date("Y年m月d日");
		$this->session->set_userdata("today",$today);
		//redirect(base_url().'index.php/sckb');
		$this->smarty->display("index.tpl");
	}
	
	private function _checkDataFormat(&$result)
	{
		$this->load->library('form_validation');
		$config = array(
			array(
				'field'=>'userName',
				'label'=>'用户名',
				'rules'=>'required|callback_checkUsername1'
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
		$r1 = preg_match("/^[a-zA-Z0-9]{6,}$/", $str);
		if ($r1 == 0)
		{
			$this->form_validation->set_message('checkUsername1', '%s 只能包含英文字母，数字，长度最少为6位。');
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
					$statusRes = $this->db->query("SELECT id FROM status WHERE statusname = ?","active");
					if($statusRes->num_rows() > 0)
					{
						$statusArr = $statusRes->first_row('array');
						if($tmpArr['status'] == $statusArr['id'])
						{
							if ($tmpArr['password'] == strtolower($this->input->post('password')))
							{
								$this->session->set_userdata('username', strtolower($this->input->post('userName')));
								$this->session->set_userdata('userId', $tmpArr['id']);
								$userRoleObj = $this->db->query("SELECT name FROM team WHERE id = '".$tmpArr['team']."'");
								$userRoleArr = $userRoleObj->result_array();
								$this->session->set_userdata('userrole', $userRoleArr[0]['name']);
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
							//状态不为active
							$var = "*该用户已停用";
							return FALSE;
						}
					}
					else
					{
						//status表中，登录状态active未添加
						$var = "*员工可登录状态未添加，请与管理员联系";
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
		if ($tmpArray = $this->_checkTestUser($username, $password ,'VNA'))
		{
			//取得测试员姓名,员工号,权限
			$username = xml_add_child($dom, 'username');
			xml_add_child($username, 'result', 'true');
			xml_add_child($username, 'name', $tmpArray['testerName']);
			xml_add_child($username, 'id', $tmpArray['testerId']);

			//根据$equipmentSn判断测试设备是否存在
			$tmpRes = $this->db->query("SELECT et.* FROM equipment et 
										JOIN status ss on et.status = ss.id
										AND ss.statusname = 'active'
										AND et.sn = ?", array($equipmentSn));
			if ($tmpRes->num_rows() > 0)
			{
				$tmpResEquipmentArry = $tmpRes->result_array();
				$testStation = xml_add_child($dom, 'test_station');
				xml_add_child($testStation, 'result', 'true');
				xml_add_child($testStation, 'id', $tmpResEquipmentArry[0]["id"]);
			}
			else
			{
				//没有查到测试设备
				$testStation = xml_add_child($dom, 'test_station');
				xml_add_child($testStation, 'result', 'false');
			}
			//取得产品类型列表
			$tmpRes = $this->db->query("SELECT a.* FROM productType a 
										JOIN (SELECT DISTINCT producttype FROM test_configuration) b ON a.id = b.productType 
										JOIN status c ON a.status = c.id
										AND c.statusname = 'active'
										ORDER BY a.id");
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
					$tmpRes = $this->db->query("SELECT DISTINCT a.producttype,a.testitem,a.statefile,a.ports,a.channel,a.trace,a.startf,a.stopf,a.mark,a.min,a.max,b.name testItemName 
												FROM test_configuration a 
												JOIN testItem b ON a.testItem = b.id 
												JOIN status c ON b.status = c.id
								   				AND c.statusname = 'active'
												AND a.productType = ? 
												ORDER BY a.testItem", array($productTypeItem['id']));
					if ($tmpRes->num_rows() > 0)
					{
						$tmpTestItemArray = $tmpRes->result_array();
						$testItem = xml_add_child($productType, 'test_item');
						xml_add_child($testItem, 'result', 'true');
						foreach ($tmpTestItemArray as $testItemItem)
						{
							xml_add_child($testItem, 'id', $testItemItem['testitem']);
							xml_add_child($testItem, 'name', $testItemItem['testItemName']);
							xml_add_child($testItem, 'state_file', $testItemItem['statefile']);
							xml_add_child($testItem, 'port_num', $testItemItem['ports']);
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
	//pim客户端登陆
	public function pimClientLogin($username = null, $password = null)
	{
		$this->load->helper("xml");
		$root = xml_dom();
		$dom = xml_add_child($root, 'info');
		if($this->_checkTestUser($username, $password, 'PIM'))
		{
			$username = xml_add_child($dom, 'username');
			xml_add_child($username, 'result', 'true');
		}
		else
		{
			$username = xml_add_child($dom, 'username');
			xml_add_child($username, 'result', 'false');
		}
		xml_print($root);
	}
	
	private function _checkTestUser($username, $password ,$section)
	{
		//检查用户名密码
		$sectionIdObj = $this->db->query("SELECT id FROM tester_section WHERE name = ?",$section);
		if($sectionIdObj->num_rows() > 0)
		{
			$sectionIdArr = $sectionIdObj->result_array();
			$tmpRes = $this->db->query("SELECT a.id testerId, a.fullname testerName, a.employeeid 
										FROM tester a JOIN status b ON a.status = b.id
										AND b.statusname = 'active'
										AND a.employeeId = ? 
										AND a.password = ?
										AND a.tester_section = ?", array(
										$username,
										$password,
										$sectionIdArr[0]['id']
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
		else
		{
			return FALSE;
		}
		
	}

	public function uploadFile($username = null, $password = null)
	{
		if (PHP_OS == 'WINNT')
		{
			//$uploadRoot = "D:\\camel\\camel\\assets\\uploadedSource";
			$uploadRoot = $this->filePath;
			$slash = "\\";
		}
		else if (PHP_OS == 'Darwin')
		{
            $uploadRoot = "/Users/garychen/Sites/camelHuacan/assets/uploadedSource";
			$slash = "/";
		}
		else
		{
			$this->_returnUploadFailed("错误的服务器操作系统");
			return;
		}
		if ($this->_checkTestUser($username, $password, 'VNA') === FALSE)
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
			//$this->_returnUploadFailed($dateStampFolder);
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
				//判断.zip文件是否有空格，并解压缩
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
					$tmpRes = $this->db->query("SELECT id FROM tester WHERE employeeid = ?", array($tmpArray[3]));
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
					$temp = "";
					$platenum = "";
					$lathe = "";
					$innermeter = "";
					$outmeter = "";
					$workorder = "";
					if(isset($tmpArray[7]))
					{
						$temp = $tmpArray[7];
					}
					if(isset($tmpArray[8]))
					{
						$platenum = $tmpArray[8];
					}
					if(isset($tmpArray[9]))
					{
						$lathe = $tmpArray[9];
					}
					if(isset($tmpArray[10]))
					{
						$innermeter = $tmpArray[10];
					}
					if(isset($tmpArray[11]))
					{
						$outmeter = $tmpArray[11];
					}
					if(isset($tmpArray[12]))
					{
						$workorder = $tmpArray[12];
					}
					//处理标志位
					$tag = "1";
					$tag1 = "1";
					$snOld = $this->db->query("SELECT id,tag FROM producttestinfo WHERE sn = ?", $sn);
					if($snOld->num_rows() !== 0)
					{
						$tag = $snOld->num_rows()+1;
						$snOldArr = $snOld->result_array();
						foreach ($snOldArr as $value)
						{
							$id = $value["id"];
							$this->db->query("UPDATE producttestinfo SET tag1 = '2' WHERE id = ?", $id);
						}
					}
					//插入producttestinfo
					$tmpSql = "INSERT INTO `producttestinfo`(`sn`, `equipmentSn`, `testTime`, `testStation`, `tester`, `productType`, `result`, `temp`, `platenum`, `lathe`, `innermeter`, `outmeter`, `workorder`, `tag`, `tag1`, `column9`, `column10`) ";
					$tmpSql .= "VALUES ('$sn','$equipmentSn','$testTime'+ INTERVAL 0 SECOND,$testStation,$tester,$productType,$testResult,'$temp','$platenum','$lathe','$innermeter','$outmeter','$workorder','$tag','$tag1',null,null)";
					$tmpRes = $this->db->query($tmpSql);
					
					if ($tmpRes === TRUE)
					{
						//取得producttestinfo id
						$productTestInfo = $this->db->insert_id();
						//取得测试项名称
						$testItemList = $this->_getDirFiles($uploadRoot.$slash.$dateStamp.$slash.substr($_FILES['file']['name'], 0, -4).$slash, 'csv', 'General.csv');
						foreach ($testItemList as $testItemItem)
						{
                            if(strrpos($testItemItem, "Data") <= 0) {
                                //插入testitemresult
                                //转换csv文件名
                                if (PHP_OS == 'WINNT') {
                                    $fileName = $testItemItem;
                                } else if (PHP_OS == 'Darwin') {
                                    $fileName = urldecode($testItemItem);
                                }
                                //取得测试项目名称
                                $tmpArray = preg_split("[-|\.]", $fileName);
                                $testItemName = $tmpArray[0];
                                //取得测试项目id
                                $tmpRes = $this->db->query("SELECT id FROM testitem WHERE name = ?", array(iconv('GB2312', 'UTF-8', $testItemName)));
                                if ($tmpRes->num_rows() > 0) {
                                    $testItem = $tmpRes->first_row()->id;
                                } else {
                                    $this->db->trans_rollback();
                                    $this->_returnUploadFailed("文件:" . $_FILES['file']['name'] . "中没有找到对应测试项目名称:" . iconv('GB2312', 'UTF-8', $testItemName));
                                    return;
                                }
                                $testResult = $tmpArray[1] == 'PASS' ? 1 : 0;
                                //取得图片文件名称
                                if (PHP_OS == 'WINNT') {
                                    $imgFile = iconv('GB2312', 'UTF-8', substr($testItemItem, 0, -9) . "-img.png");
                                } else if (PHP_OS == 'Darwin')
                                    $imgFile = substr($testItemItem, 0, -9) . "-img.png";
                                {
                                }
                                $testItemImg = $dateStamp . $slash . substr($_FILES['file']['name'], 0, -4) . $slash . $imgFile;
                                //插入testitemresult
                                $tmpRes = $this->db->query("INSERT INTO `testitemresult`(`productTestInfo`, `testItem`, `testResult`, `img`) VALUES ($productTestInfo, $testItem, $testResult, ?)", array($testItemImg));
                                if ($tmpRes === TRUE) {
                                    //取得testitemresult id
                                    $testItemResult = $this->db->insert_id();
                                    //处理testItem文件
                                    if ($handle2 = fopen($uploadRoot . $slash . $dateStamp . $slash . substr($_FILES['file']['name'], 0, -4) . $slash . $testItemItem, "r")) {
                                        $i2 = 0;
                                        while (($buffer2 = fgets($handle2)) !== false) {
                                            $i2 = $i2 + 1;
                                            if ($i2 == 1) {
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
                                            if ($tmpRes === TRUE) {
                                                //do nothing
                                            } else {
                                                $this->db->trans_rollback();
                                                $this->_returnUploadFailed("文件:" . $_FILES['file']['name'] . "中" . iconv('GB2312', 'UTF-8', $testItemName) . ":$buffer2 插入失败");
                                                return;
                                            }
                                        }
                                        fclose($handle2);
                                    } else {
                                        $this->_returnUploadFailed("文件:$fileName 打开失败");
                                        return;
                                    }
                                } else {
                                    $this->db->trans_rollback();
                                    $this->_returnUploadFailed("文件:" . $_FILES['file']['name'] . "中$testItemItem 插入testitemresult失败");
                                    return;
                                }
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
		if (PHP_OS == 'WINNT')
		{
			$uploadRoot = $this->filePath."\\pim";
			$slash = "\\";
		}
		else if (PHP_OS == 'Darwin')
		{
			$uploadRoot = "/Users/garychen/Sites/camelHuacan/assets/uploadedSource/pim";
			$slash = "/";
		}
		else
		{
			//false01->错误的服务器操作系统
			$this->_returnUploadFailed("false01");
			return;
		}

        $testerId = "";
        $checkUserRes = $this->_checkTestUser($username, $password, 'PIM');
		if ($checkUserRes === FALSE)
		{
			//false02->错误的用户名密码
		 	$this->_returnUploadFailed("false02");
		 	return;
		} else {
            $testerId = $checkUserRes["testerId"];
        }

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
				//false03->日期目录创建失败
				$this->_returnUploadFailed("false03");
				return;
			}
		}
		$file_name = $dateStamp.$slash.$_FILES['file']['name'];
		$file_nameWithoutBlank = str_replace(' ', '', $file_name);
		//complete upload
		//解压前先删除旧文件
		if (file_exists($uploadRoot.$slash.$file_name))
		{
			unlink($uploadRoot.$slash.$file_name);
		}
		$filestatus = move_uploaded_file($file_temp, $uploadRoot.$slash.$file_name);
		if (!$filestatus)
		{
			//false04->文件:".$_FILES['file']['name']."上传失败
			$this->_returnUploadFailed("false04");
			return;
		}
		//解压缩文件
		//解压前先删除旧文件夹
		$this->delDirAndFile($uploadRoot.$slash.substr($file_name, 0, -4));
		if (PHP_OS == 'WINNT')
		{
			//判断.zip文件是否有空格，并解压缩
			$file = $uploadRoot.$slash.$file_name;
			$file1 = str_replace(' ', '', $file);
			rename($file,$file1);
			exec('C:\Progra~1\7-Zip\7z.exe x '.$file1.' -o'.substr($file1, 0, -4).' -y', $info);
		}
		else if (PHP_OS == 'Darwin')
		{
			$zip = new ZipArchive;
			//判断.zip文件是否有空格，并解压缩
			$file = $uploadRoot.$slash.$file_name;
			$file1 = str_replace(' ', '', $file);
			rename($file,$file1);
			if ($zip->open($file1) === TRUE)
			{
				$zip->extractTo(substr($file1, 0, -4).$slash);
				$zip->close();
				//关闭处理的zip文件
			}
			else
			{
				//false05->文件:".$_FILES['file']['name']."打开失败
				$this->_returnUploadFailed("false05");
				return;
			}
		}
		//解析文件并插入数据库
		$this->db->trans_start();
		//初始化pim_label(工单号)
		$pim_label = substr($_FILES['file']['name'], 0, strrpos($_FILES['file']['name'], '_'));
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
			$csvArray = glob($uploadRoot.$slash.substr($file_nameWithoutBlank, 0, -4).$slash."*.csv");
			//判断是否拿到csv文件
			if($csvArray == null)
			{
				$this->db->trans_rollback();
				$this->_returnUploadFailed("can not find csv files.");
				return;
			}
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
					//一个line表示一个组, 包含一组内所有的信息。
					$lines = explode("\n", str_replace("\r", "", $file_content));

					//删除lines中由最后一个回车换行造成的空元素
					array_pop($lines);
					$firstGroup = true;
					$groupTestTime = 0;
					$pim_ser_num = 0;
					foreach ($lines as $line)
					{
						$lineContentArray = explode(",", $line);
						$lineContentArray = $this->_trimQuoterMark($lineContentArray);
						//如果是第一个组,使用此组值来初始化pim_ser_num中的值
                        //极限值
                        $limitStr = str_replace(" ", "", substr($lineContentArray[13], strpos($lineContentArray[13], ":") + 1));
						//默认合格, 有不合格数据更新为0
                        $testResult = 1;
                        if ($firstGroup)
						{
                            //查询更新老的记录, 更新islatest字段为0(不是最新);
                            $oldRecord = $this->db->query("SELECT `id` FROM `pim_ser_num` WHERE `ser_num` = '$ser_num'");
                            if($oldRecord->num_rows() !== 0)
                            {
                                $oldRecordArr = $oldRecord->result_array();
                                $oldIds = array();
                                if(count($oldRecordArr) > 0) {
                                    foreach ($oldRecordArr as $value)
                                    {
                                        array_push($oldIds, $value["id"]);
                                    }
                                    $ids = implode(',', $oldIds);
                                    $updateRes = $this->db->query("UPDATE `pim_ser_num` SET `islatest` = 0 
                                                                   WHERE `id`
                                                                   IN (".$ids.")");
                                    if($updateRes !== TRUE) {
                                        $this->db->trans_rollback();
                                        $this->_returnUploadFailed("更新老记录失败");
                                        return;
                                    }
                                }
                            }

							$tmpSql = "INSERT INTO `pim_ser_num`(`work_num`, `test_time`, `model`, `ser_num`, `pim_label`, `col1`, `col2`, `col3`, `col4`, `col5`, `col6`, `col7`, `col8`, `col9`, `col10`, `col11`, `col12`, `col13`, `islatest`, `result`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
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
                                $testerId,
                                1,
                                1
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
								//false06->插入pim_ser_num失败!原始数据:$csv中$line
								$this->_returnUploadFailed("false06");
								return;
							}
						}
						//取得当前组最近时间
						$tmpTestTime = date('Y-m-d H:i:s', strtotime($lineContentArray[12]));
						$groupTestTime = ($tmpTestTime > $groupTestTime) ? $tmpTestTime : $groupTestTime;
						//检查对应组图片是否存在
						$jpgFile = $uploadRoot.$slash.substr($file_nameWithoutBlank, 0, -4).$slash.$ser_num.'_'.(str_replace(' ', '', $lineContentArray[12])).".jpg";
						if (!file_exists($jpgFile))
						{
							$this->db->trans_rollback();
							//false07->$jpgFile,插入pim_ser_num_group时对应图片没有找到!原始数据:{$csv}中{$line}中
							$this->_returnUploadFailed("false07");
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
                            //第17个开始为测试信息数据
							for ($i = 16; $i < count($lineContentArray); $i++)
							{
							    $testValue = preg_split("/;/", $lineContentArray[$i])[1];
                                if($testResult === 1) {
                                    if($testValue >= $limitStr) {
                                        $testResult = 0;
                                    }
                                }

								$tmpSql = "INSERT INTO `pim_ser_num_group_data`(`pim_ser_num_group`, `frequency`, `value`) VALUES ($pim_ser_num_group,?,?)";
								$tmpRes = $this->db->query($tmpSql, explode(';', $lineContentArray[$i]));
								if ($tmpRes === TRUE)
								{
								}
								else
								{
									$this->db->trans_rollback();
									//false08->插入pim_ser_num_group_data失败!原始数据:$csv中$line中".$lineContentArray[$i]
									$this->_returnUploadFailed("false08");
									return;
								}
							}
						}
						else
						{
							$this->db->trans_rollback();
							//false09->插入pim_ser_num_group失败!原始数据:$csv中$line
							$this->_returnUploadFailed("false09");
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
							//false10->更新pim_ser_num_group测试时间失败!原始数据:$csv中$line
							$this->_returnUploadFailed("false10");
							return;
						}
						$firstGroup = false;					
					}

					//更新整条记录的测试时间和测试结果
					$updatePimSerNumTimeSql = "UPDATE `pim_ser_num` SET `test_time`=?, `result`= ? WHERE id = ?";
					$updatePimSerNumTimeRes = $this->db->query($updatePimSerNumTimeSql, array(
						$groupTestTime,
                        $testResult,
						$pim_ser_num
					));
					if($updatePimSerNumTimeRes) {
					} else {
						$this->db->trans_rollback();
						//false13->更新pim_ser_num测试时间失败!原始数据:$csv中$line
						$this->_returnUploadFailed("fasle13");
						return;
					}
				}
				else
				{
					$this->db->trans_rollback();
					//false11->打开文件$csv失败!
					$this->_returnUploadFailed("false11");
					return;
				}
			}
			$this->_returnUploadOk();
			return;
		}
		else
		{
			$this->db->trans_rollback();
			//false12->创建工单号$pim_label失败!
			$this->_returnUploadFailed("false12");
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
	
	//包装客户端验证服务器是否可连接的方法
	public function packingConnectCheck()
	{
		$result = "<result>connected</result>";
		print($result);
	}
	
	//包装客户端对用户名密码的验证方法
	public function packingUserCheck()
	{
		$userId = $_POST["packinguserid"];
		$userPassword = $_POST["packinguserpassword"];
		$passWord = $this->db->query("SELECT tr.password,tr.fullname FROM tester tr
									  JOIN tester_section tn ON tr.tester_section = tn.id
									  JOIN status ss ON tr.status = ss.id
									  AND tn.name = 'PACK'
									  AND ss.statusname = 'active'
									  AND tr.employeeid = '".$userId."'");
		$num = $passWord->num_rows();
		
		if($num == 0)
		{
			print("<result><info>工号或密码填写错误</info></result>");
		}
		else
		{
			$password = $passWord->first_row()->password;
			$employeename = $passWord->first_row()->fullname;
			if($password == $userPassword)
			{
				//验证成功，返回包装员name，供包装客户端，显示提示信息用
				print("<result><info>yes</info><employeename>".$employeename."</employeename></result>");
			}
			else
			{
				print("<result><info>工号或密码填写错误</info></result>");
			}
		}
	}
	//包装客户端对输入产品的验证
	public function packingProductSnCheck()
	{
		$sn = $_POST["productsn"];
		$producttype = $_POST['producttype'];
        //PIM是否检查
		$pimstate = $_POST["pimstate"];
        //耐压是否检查
        $hiPotstate = $_POST["hipotstate"];
		$packer = $_POST["packer"];
		$ordernum = $_POST["ordernum"];
		$boxsn = $_POST["boxsn"];
		$packingTime = date("Y-m-d H:i:s");
        //工单号
        $jobNum = $_POST["jobNum"];
        $factoryId = $_POST["factoryId"];
        $materialName = $_POST["materialName"];
        $materialCode = $_POST["materialCode"];
        //批次号
        $lotCode = $_POST["lotCode"];

		//验证用户所选产品型号，是否与当前sn产品的实际型号对应
		$productTypeObject = $this->db->query("SELECT pe.name
						     				  FROM producttestinfo po
						  					  JOIN producttype pe
						  					  ON po.productType = pe.id
						  					  WHERE po.sn = '".$sn."'");
		$productTypeArray = $productTypeObject->result_array();
		if(count($productTypeArray) != 0)
		{
			$productType = $productTypeArray[0]['name'];
			if($productType != $producttype)
			{
				print("<result><info>$productType</info></result>");
				return;
			}
		}

		//检查耐压测试是否合格
        if($hiPotstate == "hiPotcheck") {
            $hiPotInfoObj = $this->db->query("SELECT * 
                                          FROM hi_pot_result hpr
                                          WHERE hpr.sn = '".$sn."'
                                          AND hpr.finalresult = 1
                                          ORDER BY hpr.id DESC");
            $hiPotInfoArr = $hiPotInfoObj->result_array();
            if(count($hiPotInfoArr) == 0) {
                print("<result><info>hipotresultnull</info></result>");
                return;
            } else {
                if($hiPotInfoArr[0]["result"]) {
                } else {
                    print("<result><info>hipotresultfail</info></result>");
                    return;
                }
            }
        }

		if($pimstate == "pimcheck")
		{
			$pimSn = $this->db->query("SELECT ser_num,test_time,result FROM pim_ser_num WHERE ser_num = '".$sn."' AND islatest = 1");
			if($pimSn->num_rows() == 0)
			{
				//取得vna当前tag位，如果有，取得vna当前tag1为1的tag位。如果无，标志位取0
				$vnatagObj = $this->db->query("SELECT tag FROM producttestinfo po WHERE tag1 = '1' AND po.sn = '".$sn."'");
				if($vnatagObj->num_rows() == 0)
				{
					$packTag = '0';
				}
				else
				{
					$packTag = $vnatagObj->first_row()->tag;
				}
//				$this->db->query("INSERT INTO packingresult (packingtime,boxsn,productsn,ordernum,packer,result,tag)
//							VALUES ('".$packingTime."','','".$sn."','".$ordernum."','".$packer."','UNTESTED','".$packTag."')");
				print("<result><info>pimresultnull</info></result>");
                return;
			}
			else
			{
				$pim_result = $pimSn->first_row()->result;

				if($pim_result){//pim合格，检查vna测试是否存在
                    $vnaResultSql = "SELECT po.result,po.tag,po.testTime FROM producttestinfo po WHERE po.sn = '".$sn."' AND po.tag1 = '1'";
                    $vnaResultObject = $this->db->query($vnaResultSql);
                    $vnaResultArray = $vnaResultObject->result_array();
					//判断vna测试是否存在
					if(count($vnaResultArray) == 0)
					{
						//vna测试不存在
						$packTag = '0';
//						$this->db->query("INSERT INTO packingresult (packingtime,boxsn,productsn,ordernum,packer,result,tag)
//										VALUES ('".$packingTime."','','".$sn."','".$ordernum."','".$packer."','UNTESTED','".$packTag."')");
						print("<result><info>vnaresultnull</info></result>");
					}
					else
					{
                        //查询PIM和VNA数据,生成json
                        //TODO 取消生成json数据
                        //$jsonDataResult = $this->getVnaPimJsonData($sn, true, $factoryId, $jobNum, $materialName, $materialCode, $lotCode);
                        $jsonDataResult = json_encode(new stdClass());
                        //vna测试存在
						$packTag = $vnaResultArray[0]['tag'];
						$vnaResult = $vnaResultArray[0]['result'];
                        $vnaTestTime = preg_replace("/[\s\\-:]/", "", $vnaResultArray[0]['testTime']);
						if($vnaResult == 1)
						{
							$this->db->query("INSERT INTO packingresult (packingtime,boxsn,productsn,ordernum,packer,result,tag) 
										VALUES ('".$packingTime."','".$boxsn."','".$sn."','".$ordernum."','".$packer."','PASS','".$packTag."')");
							print("<result><info>pass</info><data>$jsonDataResult</data><testtime>$vnaTestTime</testtime></result>");
						}
						else
						{
//							$this->db->query("INSERT INTO packingresult (packingtime,boxsn,productsn,ordernum,packer,result,tag)
//										VALUES ('".$packingTime."','','".$sn."','".$ordernum."','".$packer."','FAIL','".$packTag."')");
							print("<result><info>vnaresultfail</info><data>$jsonDataResult</data><testtime>$vnaTestTime</testtime></result>");
						}
					}
				}else{//pim fail, get vna record tag
                    $timeToClient = "";
                    //查询PIM和VNA数据,生成json
                    //$jsonDataResult = $this->getVnaPimJsonData($sn, true, $factoryId, $jobNum, $materialName, $materialCode, $lotCode);
                    $jsonDataResult = json_encode(new stdClass());
                    //取得vna当前tag位，如果有，取得vna当前tag1为1的tag位。如果无，标志位取0
					$vnatagObj = $this->db->query("SELECT tag,testTime FROM producttestinfo po WHERE tag1 = '1' AND po.sn = '".$sn."'");
					if($vnatagObj->num_rows() == 0)
					{
                        $pimSnArray = $pimSn->result_array();
                        $timeToClient = preg_replace("/[\s\\-:]/", "", $pimSnArray[0]['test_time']);
						$packTag = '0';
					}
					else
					{
                        $timeToClient = preg_replace("/[\s\\-:]/", "", $vnatagObj->first_row()->testTime);
						$packTag = $vnatagObj->first_row()->tag;
					}
//					$this->db->query("INSERT INTO packingresult (packingtime,boxsn,productsn,ordernum,packer,result,tag)
//									VALUES ('".$packingTime."','','".$sn."','".$ordernum."','".$packer."','FAIL','".$packTag."')");
					print("<result><info>pimresultfail</info><data>$jsonDataResult</data><testtime>$timeToClient</testtime></result>");
				}
			}
		}
		else if($pimstate == "pimuncheck")
		{
			$pimSn = $this->db->query("SELECT ser_num,test_time FROM pim_ser_num WHERE ser_num = '".$sn."' AND islatest = 1");
			if($pimSn->num_rows() != 0)
			{
				print("<result><info>pimexsit</info></result>");
			}
			else
			{
				//pim测试数据不存在,直接检查vna
				$vnaResultSql = "SELECT po.result,po.tag,po.testTime FROM producttestinfo po WHERE po.sn = '".$sn."' AND po.tag1 = '1'";
				$vnaResultObject = $this->db->query($vnaResultSql);
				$vnaResultArray = $vnaResultObject->result_array();
				if(count($vnaResultArray) == 0)
				{
					//van测试结果为空
					$packTag = '0';
//					$this->db->query("INSERT INTO packingresult (packingtime,boxsn,productsn,ordernum,packer,result,tag)
//										VALUES ('".$packingTime."','','".$sn."','".$ordernum."','".$packer."','UNTESTED','".$packTag."')");
					print("<result><info>vnaresultnull</info></result>");
				}
				else
				{
                    //查询PIM和VNA数据,生成json
                    //$jsonDataResult = $this->getVnaPimJsonData($sn, false, $factoryId, $jobNum, $materialName, $materialCode, $lotCode);
                    $jsonDataResult = json_encode(new stdClass());
                    //van结果不为空
					$packTag = $vnaResultArray[0]['tag'];
					$vnaResult = $vnaResultArray[0]['result'];
                    $vnaTesttime = preg_replace("/[\s\\-:]/", "", $vnaResultArray[0]['testTime']);
					if($vnaResult == 1)
					{
						$this->db->query("INSERT INTO packingresult (packingtime,boxsn,productsn,ordernum,packer,result,tag)
										VALUES ('".$packingTime."','".$boxsn."','".$sn."','".$ordernum."','".$packer."','PASS','".$packTag."')");
						print("<result><info>pass</info><data>$jsonDataResult</data><testtime>$vnaTesttime</testtime></result>");
					}
					else
					{
//						$this->db->query("INSERT INTO packingresult (packingtime,boxsn,productsn,ordernum,packer,result,tag)
//										VALUES ('".$packingTime."','','".$sn."','".$ordernum."','".$packer."','FAIL','".$packTag."')");
						print("<result><info>vnaresultfail</info><data>$jsonDataResult</data><testtime>$vnaTesttime</testtime></result>");
					}
				}
			}
		}
		else
		{
			$vnaResultSql = "SELECT po.result,po.tag,po.testTime FROM producttestinfo po WHERE po.sn = '".$sn."' AND po.tag1 = '1'";
			$vnaResultObject = $this->db->query($vnaResultSql);
			$vnaResultArray = $vnaResultObject->result_array();
			if(count($vnaResultArray) == 0)
			{
				//van测试结果为空
				$packTag = '0';
//				$this->db->query("INSERT INTO packingresult (packingtime,boxsn,productsn,ordernum,packer,result,tag)
//										VALUES ('".$packingTime."','','".$sn."','".$ordernum."','".$packer."','UNTESTED','".$packTag."')");
				print("<result><info>vnaresultnull</info></result>");
			}
			else
			{
                //查询PIM和VNA数据,生成json
                //$jsonDataResult = $this->getVnaPimJsonData($sn, false, $factoryId, $jobNum, $materialName, $materialCode, $lotCode);
                $jsonDataResult = json_encode(new stdClass());
                //van结果不为空
				$packTag = $vnaResultArray[0]['tag'];
				$vnaResult = $vnaResultArray[0]['result'];
                $vnaTestTime = preg_replace("/[\s\\-:]/", "", $vnaResultArray[0]['testTime']);

				if($vnaResult == 1)
				{
//					$this->db->query("INSERT INTO packingresult (packingtime,boxsn,productsn,ordernum,packer,result,tag)
//										VALUES ('".$packingTime."','".$boxsn."','".$sn."','".$ordernum."','".$packer."','PASS','".$packTag."')");
					print("<result><info>pass</info><data>$jsonDataResult</data><testtime>$vnaTestTime</testtime></result>");
				}
				else
				{
//					$this->db->query("INSERT INTO packingresult (packingtime,boxsn,productsn,ordernum,packer,result,tag)
//										VALUES ('".$packingTime."','','".$sn."','".$ordernum."','".$packer."','FAIL','".$packTag."')");
					print("<result><info>vnaresultfail</info><data>$jsonDataResult</data><testtime>$vnaTestTime</testtime></result>");
				}
			}	
		}
	}

	//上传耐压测试数据
	public function voltagewithstandupload($employeeId = null, $testStationName = null, $instrName = null, $instrSN = null, $productSN = null, $testResult = null) {
	    //序列号
        $sn = $productSN;
        //工号
        $employeeid = $employeeId;
        $testerId = "";
        $testTime = date("Y-m-d H:i:s");
        $testData = null;
        //0不合格, 1合格
        $testResult = strtoupper($testResult) == "FAIL" ? 0 : 1;
        //是否为最终测试结果
        $finalresult = 1;
        //查询测试员
        $hiPottesterSql = $this->db->query("SELECT tr.id, tr.employeeid FROM tester tr
                            JOIN tester_section ts on tr.tester_section = ts.id
                            JOIN status ss on tr.status = ss.id
                            WHERE tr.employeeid = '".$employeeid."'
                            AND ts.name = 'HI_POT'                      
							AND ss.statusname = 'active'");
        $hiPottesterArr = $hiPottesterSql->result_array();
        if(count($hiPottesterArr) == 0) {
            $this->_returnUploadFailed("未找到指定测试员");
            return;
        } else {
            $testerId = $hiPottesterArr[0]["id"];
        }

        //记录插入数据库
        $this->db->trans_start();
        //更新老的记录
        $oldRecord = $this->db->query("SELECT id FROM hi_pot_result WHERE sn = ? AND finalresult = 1", $sn);
        if($oldRecord->num_rows() !== 0)
        {
            $oldRecordArr = $oldRecord->result_array();
            $oldIds = array();
            if(count($oldRecordArr) > 0) {
                foreach ($oldRecordArr as $value)
                {
                    array_push($oldIds, $value["id"]);
                }
                $ids = implode(',', $oldIds);
                $updateRes = $this->db->query("UPDATE hi_pot_result SET finalresult = 0 WHERE id in (".$ids.")");
                if($updateRes !== TRUE) {
                    $this->db->trans_rollback();
                    $this->_returnUploadFailed("更新记录失败");
                    return;
                }
            }
        }
        $tmpRes = $this->db->query("INSERT INTO `hi_pot_result`
                                    (`sn`, `result`, `finalresult`, `testerid`, `testtime`, `testdata`, `teststationname`, `instrName`, `instrSN`) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", array(
                                        $sn,
                                        $testResult,
                                        $finalresult,
                                        $testerId,
                                        $testTime,
                                        $testData,
                                        $testStationName,
                                        $instrName,
                                        $instrSN
                                    ));
        if ($tmpRes === TRUE)
        {
            $this->_returnUploadOk();
            return;
        }
        else
        {
            $this->db->trans_rollback();
            $this->_returnUploadFailed("插入数据失败");
            return;
        }
    }

	//包装客户端取得产品型号的方法
	public function getProducttype()
	{
		$producttype = $_POST['producttype'];
		if($producttype == "")
		{
			$producttypeObject = $this->db->query("SELECT DISTINCT name FROM producttype");
			$producttypeArray = $producttypeObject->result_array();
			$producttypeString = "";
			foreach ($producttypeArray as $value) 
			{
				$producttypeString = $value['name'].",".$producttypeString;
			}
			print($producttypeString);
		}
		else
		{
			print("<result><info>$producttype</info></result>");
		}
	}

    //包装客户端, 包装完成一箱, 调用方法
    public function insertPackResult() {
        $recordstring = $_POST['recordstring'];
        print("<result><info>$recordstring</info></result>");
    }
	
	public function vnaSnExistCheck($sn = null)
	{
		$sn = urldecode($sn);
		$snCountObject = $this->db->query("SELECT sn FROM producttestinfo a WHERE a.sn = '".$sn."'");
		if($snCountObject->num_rows() != 0)
		{
			$this->_returnExistResult("false");
		}
		else
		{
			$this->_returnExistResult("true");
		}
		
	}
	
	private function _returnExistResult($result)
	{
		$this->load->helper('xml');
		$dom = xml_dom();
		$uploadResult = xml_add_child($dom, 'existResult');
		xml_add_child($uploadResult, 'result', $result);
		xml_print($dom);
	}

	
	//包装客户端返回vna，pim测试数据
	public function getVnaPimJsonData($sn, $needPim, $fatory = "", $work_order = "", $uut_name = "", $uut_code = "", $lot_code = "") {
		$jsonResult = new stdClass();
		$vnaInfoResultSql = "select po.sn as serial_number, po.equipmentSn, po.testTime,
                                CASE po.result WHEN 1 THEN 'passed' ELSE 'failed' END as testresult,
                                ts.name as site_code, 
                                tr.fullname as operator, section.name as operation_sequence,
                                pe.name as producttype,
                                ti.name as testitemname,
                                tt.img, tt.testItem as testItemID,
                                CASE tt.testResult WHEN 1 THEN 'passed' ELSE 'failed' END as itemResult,
                                et.partnumber as ate_name
                              from producttestinfo po
                                join testitemresult tt on tt.productTestInfo = po.id
                                join teststation ts on ts.id = po.testStation
                                join tester tr on po.tester = tr.id
                                join producttype pe on po.productType = pe.id
                                join testitem ti on tt.testItem = ti.id
                                join tester_section section on tr.tester_section = section.id
                                join equipment et on po.equipmentSn = et.`sn`
							  where po.sn = '" .$sn ."'
							  and po.tag1 = 1";
		$vnaInfoResult = $this->db->query($vnaInfoResultSql);
		$vnaInfoResultArray = $vnaInfoResult->result_array();

        //只有VNA测试存在的时候, PIM才会有数据
		if(count($vnaInfoResultArray) > 0) {
			$vnaInfoResultObj = $vnaInfoResultArray[0];
			//厂区
			$jsonResult->factory = $fatory;
			//加工线体
			$jsonResult->line = "";
			//uut_info 被测对象基本信息
			$jsonResult->uut_info = new stdClass();
			$jsonResult->uut_info->work_order = $work_order;
			//uut_type固定值，供应商用0
			$jsonResult->uut_info->uut_type = "0";
			//uut_name 物料名称
			$jsonResult->uut_info->uut_name = $uut_name;
			//uut_code 物料编码
			$jsonResult->uut_info->uut_code = $uut_code;
			//serial_number 物料条码
			$jsonResult->uut_info->serial_number = trim($sn);
			//supplier
			$jsonResult->uut_info->supplier = "";
			//date_code
			$jsonResult->uut_info->date_code = "";
			//lot_code, 批次号
			$jsonResult->uut_info->lot_code = $lot_code;
			//mould
			$jsonResult->uut_info->mould = "";
			//cavity
			$jsonResult->uut_info->cavity = "";
			//colour
			$jsonResult->uut_info->colour = "";

			//ate_info 测试设备信息, 必填
			$jsonResult->ate_info = new stdClass();
			//ate_name, 测试装备名称
			$jsonResult->ate_info->ate_name = $vnaInfoResultObj['ate_name'];
			//computer_name
			$jsonResult->ate_info->computer_name = "";
			//fixuer_id
			$jsonResult->ate_info->fixuer_id = "";

			//program_info 测试程序信息，必填
			$jsonResult->program_info = new stdClass();
			//program_name， 测试程序名称
			$jsonResult->program_info->program_name = $this->vnaClientName;
			//program_ver， 测试程序版本
			$jsonResult->program_info->program_ver = $this->vnaClientVersion;

			//uut_result, 物料测试结果, 必填
			$jsonResult->uut_result = new stdClass();
			//operator 操作员
			$jsonResult->uut_result->operator = $vnaInfoResultObj['operator'];
			//operation_sequence 测试工序
			$jsonResult->uut_result->operation_sequence = $vnaInfoResultObj['operation_sequence'];
			//site_code 测试工站
			$jsonResult->uut_result->site_code = $vnaInfoResultObj['site_code'];
			//start_time	测试开始时间
			$jsonResult->uut_result->start_time = $vnaInfoResultObj['testTime'];
			//stop_time	测试结束时间
			$jsonResult->uut_result->stop_time = $vnaInfoResultObj['testTime'];
			//test_result	测试结果
			$jsonResult->uut_result->test_result = $vnaInfoResultObj['testresult'];

			//test_item_list 测试项/子项结果列表（数组）
			$jsonResult->test_item_list = array();

			foreach ($vnaInfoResultArray as $value) {
				$testItemname = $value["testitemname"];

				$testItemPath = $this->filePath.$this->slash.(substr($value["img"], 0, strrpos($value["img"], "\\"))).$this->slash;
				//TODO 暂时替换,发布去除
                //$testItemPath = str_replace("\\", $this->slash, $testItemPath);

                $itemObj = new \stdClass();
                $itemObj->id = $value["testItemID"];
                $itemObj->item_name = $testItemname;
                $itemObj->start_time = $value["testTime"];
                $itemObj->stop_time = $value["testTime"];
                $itemObj->test_result = $value["itemResult"];
                $itemObj->result_desc = "";
                //是否值类型, 固定值Y
                $itemObj->value_flag = "Y";
                $itemObj->lower_limit = "";
                $itemObj->upper_limit = "";
                $itemObj->test_value = "";

                $itemObj->sub_test_item_list = array();

                $itemFiles = glob($testItemPath."TraceData-*.csv");
                $itemFilesReal = array();

                foreach($itemFiles as $f) {
                    $nameUtf8 = iconv("gbk", "utf-8", $f);
                    if(strpos($nameUtf8, "TraceData-".$testItemname."-")) {
                        array_push($itemFilesReal, $f);
                    };
                };

                foreach ($itemFilesReal as $itemFilePath) {
                    $subitemID = 1;

                    if ($file_content = file_get_contents($itemFilePath)) {
                        $itemResultStr = substr($itemFilePath, strrpos($itemFilePath, "-") + 1);
                        $itemResultStr = substr($itemResultStr, 0, strrpos($itemResultStr, "."));
                        $itemResult = strtoupper($itemResultStr) == "PASS" ? "passed" : "failed";

                        //获取当前测试记录的极限线数组
                        $limitLinesArray = $this->getLimitsArrayByDataFile($testItemPath, $itemFilePath, $testItemname);

                        //去除csv文件引号中的换行符号
                        $pattern = '/"([0-9.;\-]+)\r\n"/';
                        $replacement = '"${1}"';
                        $file_content = preg_replace($pattern, $replacement, $file_content);
                        //一个line表示一个组
                        $lines = explode("\n", str_replace("\r", "", $file_content));
                        //删除lines中由最后一个回车换行造成的空元素
                        array_pop($lines);
                        foreach ($lines as $line) {
                            $subItemObj = new stdClass();
                            $subItemObj->id = "" . $subitemID;
                            //测试子项名称
                            $subItemObj->sub_item_name = $testItemname;
                            $subItemObj->start_time = $value["testTime"];
                            $subItemObj->stop_time = $value["testTime"];
                            $subItemObj->test_result = $itemResult;

                            $lineArr = preg_split("/,/", preg_replace("/\s/", "", $line));

                            if(!is_numeric($lineArr[0])) {
                                continue;
                            }

                            $freqVal = $lineArr[0] ? $this->convertVNAData($lineArr[0], 6) : '';
                            $dataVal = $lineArr[1] ? $this->convertVNAData($lineArr[1], 0) : '';

                            $currFreqLimitResult = $this->getFreqLimitAndFreqResult($lineArr[0],$lineArr[1],$limitLinesArray);

                            //测试频点值
                            $subItemObj->result_desc = "" . $freqVal;
                            $subItemObj->value_flag = 'Y';
                            $subItemObj->lower_limit = "";
                            $subItemObj->upper_limit = "";
                            $subItemObj->test_value = "" . $dataVal;
                            if(count($currFreqLimitResult) > 0) {
                                if($currFreqLimitResult[1] == "MAX") {
                                    $subItemObj->upper_limit = "".$currFreqLimitResult[0];
                                } else {
                                    $subItemObj->lower_limit = "".$currFreqLimitResult[0];
                                }
                                $subItemObj->test_result = $currFreqLimitResult[2];
                            }

                            array_push($itemObj->sub_test_item_list, $subItemObj);

                            $subitemID++;
                        }
                    }
                }

                array_push($jsonResult->test_item_list, $itemObj);
			}

			//PIM数据
            if($needPim) {
                $pimGroupInfoSql = "SELECT a.group_id, a.test_time, substring( a.col12, 12 ) as limit_line, max( a.value ) AS maxval, max( a.value ) > substring( a.col12, 12 ) AS result
								FROM (
									SELECT pp.id as group_id, pm.model, pm.col12, pp.test_time, pa.frequency, pa.value
									FROM pim_ser_num pm
									JOIN pim_ser_num_group pp ON pp.pim_ser_num = pm.id
									JOIN pim_ser_num_group_data pa ON pa.pim_ser_num_group = pp.id
									WHERE pm.ser_num = '".$sn."'
								)a
								GROUP BY a.test_time
								ORDER BY a.test_time DESC";
                $pimGroupInfoResult = $this->db->query($pimGroupInfoSql);
                $pimGroupInfoArray = $pimGroupInfoResult->result_array();
                if(count($pimGroupInfoArray) > 0) {
                    foreach ($pimGroupInfoArray as $pimGroup) {
                        $pimResultObj = new stdClass();
                        //PIM测试, 固定值10
                        $pimResultObj->id = "10";
                        $pimResultObj->item_name = "PIM";
                        $pimResultObj->start_time = $pimGroup['test_time'];
                        $pimResultObj->stop_time = $pimGroup['test_time'];
                        $pimResultObj->test_result = $pimGroup["result"] == 1 ? "failed" : "passed";
                        $pimResultObj->result_desc = "";
                        $pimResultObj->value_flag = "Y";
                        $pimResultObj->lower_limit = "";
                        $pimResultObj->upper_limit = trim($pimGroup['limit_line']);
                        $pimResultObj->test_value = "";

                        $pimResultObj->sub_test_item_list = array();

                        $pimGroupID = $pimGroup["group_id"];

                        $pimGroupDataSql = "select * from pim_ser_num_group_data where pim_ser_num_group = ".$pimGroupID;
                        $pimGroupDataResult = $this->db->query($pimGroupDataSql);
                        $pimGroupDataArray = $pimGroupDataResult->result_array();
                        foreach ($pimGroupDataArray as $pimGroupData) {
                            $pimSubItemID = 1;

                            $pimSubitemObj = new stdClass();
                            $pimSubitemObj->id = "".$pimSubItemID;
                            $pimSubitemObj->sub_item_name = "PIM";
                            $pimSubitemObj->start_time = $pimGroup['test_time'];
                            $pimSubitemObj->stop_time = $pimGroup['test_time'];
                            $pimSubitemObj->test_result = $pimGroupData["value"] > trim($pimGroup['limit_line']) ? "failed" : "passed";
                            $pimSubitemObj->result_desc = "".floatval($pimGroupData["frequency"]);
                            $pimSubitemObj->value_flag = "Y";
                            $pimSubitemObj->lower_limit = "";
                            $pimSubitemObj->upper_limit = trim($pimGroup['limit_line']);
                            $pimSubitemObj->test_value = $pimGroupData["value"];

                            array_push($pimResultObj->sub_test_item_list, $pimSubitemObj);

                            $pimSubItemID++;
                        }

                        array_push($jsonResult->test_item_list, $pimResultObj);
                    }
                }
            }
		}
		return json_encode($jsonResult);
	}

	//根据测试文件名称,测试项名。 获取该测试文件测试时对应的极限线
    /**
     * @param $folderPath 文件保存路径
     * @param $testDataFilePath 测试数据文件路径
     * @param $testItemName 测试项名称
     * @return array 返回极限线数组, index 0: (0: 关闭；1: 上限线；2: 下限线); index 1: 起始频点; index 2: 终止频点; index 3: 起始极限值; index 4: 终止极限值;
     */
	private function getLimitsArrayByDataFile($folderPath, $testDataFilePath, $testItemName) {
//	    print_r($testDataFilePath);
//        echo "<br>";
//        print_r(iconv("gbk", "utf-8", $testDataFilePath));
//        echo "<br>";
//        print_r($testItemName);
//        echo "<br>";
        //获取当前测试项的channel

        $subPath1 = substr($testDataFilePath, strpos($testDataFilePath, "-" ) + 1);
        $subPath2 = substr($subPath1, strpos($subPath1, "-" ) + 1);
        $channelStr = substr(substr($subPath2, 0, strpos($subPath2, "-" )), 0, 3);
        $limitFileName = $folderPath . "LimitData-". iconv("utf-8", "gbk", $testItemName)."-".$channelStr.".csv";
        $limitFiles = array();
        if(file_exists($limitFileName)) {
            array_push($limitFiles, $limitFileName);
        }

        $limitArr = array();
        $testDataFileName = substr($testDataFilePath, strripos($testDataFilePath, $this->slash) + 1);

        foreach($limitFiles as $limitFile) {
            $channelInfo = substr($limitFile, strripos($limitFile, "-") + 1, -4);
            if(strrpos($testDataFileName, $channelInfo)) {
                if($limitFileContent =  file_get_contents(($limitFile))) {
                    $allLimit = preg_split("/,/", $limitFileContent);
                    if(count($allLimit) > 0) {
                        //去除第一个元素,代表有几个极限线
                        $limitCount = array_shift($allLimit);
                        $limitCount = $this->convertVNAData($limitCount, 0);
                        if($limitCount * 5 == count($allLimit)) {
                            $groupNum = 1;
                            $currLimit = array();
                            for($i = 0; $i < count($allLimit); $i++) {
                                array_push($currLimit, $allLimit[$i]);
                                if($groupNum == 5) {
                                    array_push($limitArr, $currLimit);
                                    $groupNum = 1;
                                } else {
                                    $groupNum++;
                                }
                            }
                        }
                    }
                }

            }
        }

        return $limitArr;
    }

    /**
     * @param $frequence 频点值, 科学计数法
     * @param $testValue 频点下的测试值,
     * @param $channelLimitsArray 所在channel的极限线数组
     * @return $result 返回结果数组, index 0: 该频点的极限值, index 2: 上限(MAX), 下限(MIN), index 3: 频点测试结果
     */
    private function getFreqLimitAndFreqResult($frequence, $testValue, $channelLimitsArray) {
        $result = array();
        if(count($channelLimitsArray) > 0) {
            foreach ($channelLimitsArray as $channelLimit) {
                $limitType = $this->convertVNAData($channelLimit[0], 0);
                if($limitType != 0) {

                    //频点在当前极限线的起截止频率内
                    $convertFreq = $this->convertVNAData($frequence, 6);
                    if($frequence >= $this->convertVNAData($channelLimit[1], 6)  && $convertFreq <= $this->convertVNAData($channelLimit[2], 6)) {
                        $currFreqLimit = $this->getLimitData($channelLimit[1], 6, $channelLimit[2], 6, $frequence, 6, $channelLimit[3], $channelLimit[4]);
                        array_push($result, $currFreqLimit);
                        if($limitType == 1) {
                            array_push($result, "MAX");
                            if($testValue >= $currFreqLimit) {
                                array_push($result, "failed");
                            } else {
                                array_push($result, "passed");
                            }
                        } else {
                            array_push($result, "MIN");
                            if($testValue <= $currFreqLimit) {
                                array_push($result, "failed");
                            } else {
                                array_push($result, "passed");
                            }
                        }
                        break;
                    }
                }
            }
        }
        return $result;
    }

	public function testGetLimit() {
	    echo $this->convertVNAData("+0.00000000000E+000", 0);
	    //echo $this->getLimitData("+4.00000000000E+008", 0, "+2.20000000000E+009", 0, "+2.40000000000E+008", 0, "+1.10000000000E+000","+1.10000000000E+000");
    }

	private function getLimitData($startFreq, $startUnit, $stopFreq, $stopUnit, $currFreq, $currUnit, $startRes, $stopRes) {
	    $destinationRes = "";
        $startRes = floatval($startRes);
        $stopRes = floatval($stopRes);
        $startFreq = $this->convertVNAData($startFreq, $startUnit);
        $stopFreq = $this->convertVNAData($stopFreq, $stopUnit);
        $currFreq = $this->convertVNAData($currFreq, $currUnit);
        if($startRes == $stopRes) {
            $destinationRes = $startRes;
            return $destinationRes;
        }

        $destinationRes = (($currFreq - $startFreq) * $stopRes - ($currFreq - $stopFreq) * $startRes) / ($stopFreq - $startFreq);

        return $destinationRes;
    }

	//转换网分计数法, unit代表需要转换成的数量级, 5.44000000E+08
    private function convertVNAData($dataStr, $unit) {
        $dataArr =  preg_split("/E\\+/", $dataStr);
        $dataValue = floatval($dataArr[0]);
        return ($dataValue * pow(10, $dataArr[1])) / pow(10, $unit);
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

/*end*/
