<?php
/**
 * customized Controller to check login status
 * @author: penn
 */
class CW_Controller extends CI_Controller
{
	private $commonHead = '';
	private $jqueryHead = '';
	private $validationEngineHead = '';
	private $flowplayerHead = '';
	public function __construct()
	{
		parent::__construct();
		//define common head for each page
		$this->commonHead .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>'."\n";
		$this->commonHead .= '<!-- Framework CSS -->'."\n";
		$this->commonHead .= '<link rel="stylesheet" href="'.base_url().'resource/css/screen.css" type="text/css" media="screen, projection"/>'."\n";
		$this->commonHead .= '<link rel="stylesheet" href="'.base_url().'resource/css/print.css" type="text/css" media="print"/>'."\n";
		$this->commonHead .= '<!--[if lt IE 8]><link rel="stylesheet" href="'.base_url().'resource/css/ie.css" type="text/css" media="screen, projection"/><![endif]-->'."\n";
		$this->commonHead .= '<link rel="stylesheet" href="'.base_url().'resource/css/user.css" type="text/css" media="screen, projection"/>'."\n";
		$this->smarty->assign('commonHead', $this->commonHead);
		$this->jqueryHead .= '<!-- jquery -->'."\n";
		$this->jqueryHead .= '<script src="'.base_url().'resource/js/jquery.js" type="text/javascript"></script>'."\n";
		$this->smarty->assign('jqueryHead', $this->jqueryHead);
		$this->validationEngineHead .= '<!-- validationEngine -->'."\n";
		$this->validationEngineHead .= '<link rel="stylesheet" href="'.base_url().'resource/css/template.css" type="text/css" media="screen, projection"/>'."\n";
		$this->validationEngineHead .= '<link rel="stylesheet" href="'.base_url().'resource/css/validationEngine.jquery.css" type="text/css" media="screen, projection"/>'."\n";
		$this->validationEngineHead .= '<script src="'.base_url().'resource/js/jquery.validationEngine.js" type="text/javascript"></script>'."\n";
		$this->validationEngineHead .= '<script src="'.base_url().'resource/js/jquery.validationEngine-zh_CN.js" type="text/javascript"></script>'."\n";
		$this->smarty->assign('validationEngineHead', $this->validationEngineHead);
		$this->flowplayerHead .= '<!-- flowplayer -->'."\n";
		$this->flowplayerHead .= '<script src="'.base_url().'resource/flowplayer/flowplayer-3.2.6.min.js" type="text/javascript"></script>'."\n";
		$this->smarty->assign('flowplayerHead', $this->flowplayerHead);
		
		$itemArr = array(""=>"",
						 "VNA测试记录"=>"VNA测试记录",
						 "PIM测试记录"=>"PIM测试记录",
						 "包装记录"=>"包装记录",
						 "测试方案"=>"测试方案",
						 "产品型号"=>"产品型号",
						 "测试项"=>"测试项",
						 "测试站点"=>"测试站点",
						 "测试设备"=>"测试设备",
						 "测试员"=>"测试员",
						 "用户"=>"用户",
						 "用户组"=>"用户组"
						 );
		$this->smarty->assign('items', $itemArr);

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

		if (!CW_Controller::_checkLogin())
		{

			redirect(base_url()."index.php/login");

		}
	}

	public function _checkLogin()
	{
		if ($this->uri->segment(1) == 'login' || strpos($this->uri->segment(2), 'noLogin') === 0)
		{
			return TRUE;
		}
		else if ($this->session->userdata('username'))
		{
			$tmpRes = $this->db->query("SELECT * FROM user WHERE username='{$this->session->userdata('username')}';");
			if ($tmpRes && $tmpRes->num_rows() > 0)
			{
				return TRUE;
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

}

/*end file CW_Controller.php*/
