<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');
class firstPage extends CW_Controller
{
	function __construct()
	{
		parent::__construct();
		//判断当前登录用户
		$userrole = $this->session->userdata("userrole");
		if($userrole == 'user')
		{
			redirect(base_url().'index.php/login/toIndex');
		}
		$this->load->library('grocery_CRUD');
	}
	
	/*
	public function department()
	{
		$crud = new grocery_CRUD();
		$crud->set_theme('datatables');
		$crud->required_fields('name');
		$crud->display_as('name', '名称');
		$output = $crud->render();
		foreach ($output as $key=>$value)
		{
			$this->smarty->assign($key, $value);
		}
		$this->smarty->assign('title', '部门');
		$this->smarty->display('firstPage.tpl');
	}
	*/
	
	public function producttype()
	{
		$crud = new grocery_CRUD();
		$crud->set_theme('datatables');
		$crud->required_fields('status');
		$crud->display_as('name', '产品型号')->display_as('status', '状态');
		$crud->set_relation('status','status','statusname');
		$crud->unset_delete();
		
		//新增，编辑时对产品型号的判断
		$postUrl = $this->uri->uri_string();
		if(strpos($postUrl, "insert_validation") != FALSE)
		{
			$crud->set_rules('name','producttype','callback_add_producttype');
		}
		else if(strpos($postUrl, "update_validation") != FALSE)
		{
			$crud->set_rules('name','producttype','callback_edit_producttype');
		}
		else
		{
			//
		}
		$output = $crud->render();
		foreach ($output as $key=>$value)
		{
			$this->smarty->assign($key, $value);
		}
		$this->smarty->assign('item', '产品型号');
		$this->smarty->assign('title', '产品管理');
		$this->smarty->display('firstPage.tpl');
	}
	//产品型号新增时的判断
	public function add_producttype($str)
	{
		if(strlen(str_replace(" ", "", $str)) == 0)
		{
			$this->form_validation->set_message('add_producttype', '产品型号不能为空！');
			return FALSE;
		}
		else
		{
			$producttypeObj1 = $this->db->query("SELECT pe.name FROM producttype pe WHERE pe.status = '1' AND pe.name = '$str'");
			$producttypeObj2 = $this->db->query("SELECT pe.name FROM producttype pe WHERE pe.status = '2' AND pe.name = '$str'");
			if ($producttypeObj1->num_rows() != 0)
			{
				$this->form_validation->set_message('add_producttype', '该产品型号已存在，请重新输入！');
				return FALSE;
			}
			else if($producttypeObj2->num_rows() != 0)
			{
				$this->form_validation->set_message('add_producttype', '该产品型号已被停用，存在历史记录，请重新选择产品型号名称！');
				return FALSE;
			}
			else
			{
				return TRUE;
			}
		}
	}
	//产品型号编辑时的判断
	public function edit_producttype($var)
	{
		if(strlen(str_replace(" ", "", $var)) == 0)
		{
			$this->form_validation->set_message('edit_producttype', '产品型号不能为空！');
			return FALSE;
		}
		else
		{
			//取得当前id
			$postUrl = $this->uri->uri_string();
			$id = substr($postUrl, strripos($postUrl, "/")+1);
			//查询id不等于当前，且产品型号等于当前输入的产品型号的记录数
			$numObj = $this->db->query("SELECT COUNT(*) AS num FROM producttype pe WHERE pe.name = '$var' AND pe.id != '$id'");
			$num = $numObj->first_row()->num;
			//记录为空时允许修改，不为空时，不允许修改
			if($num != 0)
			{
				$this->form_validation->set_message('edit_producttype', '产品型号已存在！');
				return FALSE;
			}
			else
			{
				return TRUE;
			}
		}
	}
	
	public function skilllevel()
	{
		$crud = new grocery_CRUD();
		$crud->set_theme('datatables');
		$crud->required_fields('name');
		$crud->display_as('name', '名称');
		$output = $crud->render();
		foreach ($output as $key=>$value)
		{
			$this->smarty->assign($key, $value);
		}
		$this->smarty->assign('title', '技能水平');
		$this->smarty->display('firstPage.tpl');
	}

	public function team()
	{
		$crud = new grocery_CRUD();
		$crud->set_theme('datatables');
		$crud->required_fields('name');
		$crud->display_as('name', '名称');
		$crud->unset_edit();
		$crud->unset_delete();
		$crud->unset_add();
		$output = $crud->render();
		foreach ($output as $key=>$value)
		{
			$this->smarty->assign($key, $value);
		}
		$this->smarty->assign('item', '用户组');
		$this->smarty->assign('title', '用户组');
		$this->smarty->display('firstPage.tpl');
	}

	public function tester()
	{
		$crud = new grocery_CRUD();
		$crud->set_theme('datatables');
		$crud->display_as('fullname', '姓名')->display_as('password', '密码')->display_as('employeeid', '工号')->display_as('tester_section', '工段')->display_as('status', '状态');
		$crud->set_relation('tester_section', 'tester_section', 'name');
		$crud->set_relation('status', 'status', 'statusname');
		$crud->unset_delete();
		$crud->required_fields("fullname","tester_section","status");
		$crud->edit_fields("fullname","password","tester_section","status");
		$crud->set_rules('password','password','callback_tester_password');
		//新增，编辑时对测试员的判断
		$postUrl = $this->uri->uri_string();
		if(strpos($postUrl, "insert_validation") != FALSE)
		{
			$crud->set_rules('employeeid','tester','callback_add_tester');
		}
		else if(strpos($postUrl, "update_validation") != FALSE)
		{
			$crud->set_rules('employeeid','tester','callback_edit_tester');
		}
		else
		{
			//
		}
		$output = $crud->render();
		foreach ($output as $key=>$value)
		{
			$this->smarty->assign($key, $value);
		}
		$this->smarty->assign('item', '测试员');
		$this->smarty->assign('title', '测试员');
		//$this->smarty->assign('diagram', TRUE);
		$this->smarty->display('firstPage.tpl');
	}
	//测试员密码格式校验
	public function tester_password($str)
	{
		if(preg_match("/^[a-zA-Z0-9]+$/", $str))
		{
			return TRUE;
		}
		else
		{
			$this->form_validation->set_message('tester_password', '密码不为空，只能包含英文字母或数字！');
			return FALSE;
		}
	}
	//新增时测试员员工号格式校验
	public function add_tester($str)
	{
		if(preg_match("/^[a-zA-Z0-9]+$/", $str))
		{
			$nameRecordObj = $this->db->query("SELECT tr.employeeid FROM tester tr WHERE tr.employeeid = '$str'");
			if($nameRecordObj->num_rows() != 0)
			{
				$this->form_validation->set_message('add_tester', '该工号测试员已存在！');
				return FALSE;
			}
			else
			{
				return TRUE;
			}
		}
		else
		{
			$this->form_validation->set_message('add_tester', '工号不为空且只能包含英文字母或数字！');
			return FALSE;
		}
	}
	//编辑时测试员员工号格式校验
	public function edit_tester($str)
	{
		if(preg_match("/^[a-zA-Z0-9]+$/", $str))
		{
			//取得当前id
			$postUrl = $this->uri->uri_string();
			$id = substr($postUrl, strripos($postUrl, "/")+1);
			//查询id不等于当前，且测试项等于当前输入的测试项的记录数
			$numObj = $this->db->query("SELECT COUNT(*) AS num FROM tester tr WHERE tr.employeeid = '$str' AND tr.id != '$id'");
			$num = $numObj->first_row()->num;
			//记录为空时允许修改，不为空时，不允许修改
			if($num != 0)
			{
				$this->form_validation->set_message('edit_tester', "该工号的测试员已存在！");
				return FALSE;
			}
			else
			{
				return 	TRUE;
			}
		}
		else
		{
			$this->form_validation->set_message('edit_tester', '测试员工号不为空且只能包含英文字母或数字！');
			return FALSE;
		}
	}
	
	public function testitem()
	{
		$crud = new grocery_CRUD();
		$crud->set_theme('datatables');
		$crud->required_fields('status');
		$crud->display_as('name', '名称')->display_as('status','状态');
		$crud->set_relation("status","status","statusname");
		$crud->unset_delete();
		//新增，编辑时对测试项的判断
		$postUrl = $this->uri->uri_string();
		if(strpos($postUrl, "insert_validation") != FALSE)
		{
			$crud->set_rules('name','testitem','callback_add_testitem');
		}
		else if(strpos($postUrl, "update_validation") != FALSE)
		{
			$crud->set_rules('name','testitem','callback_edit_testitem');
		}
		else
		{
			//
		}
		$output = $crud->render();
		foreach ($output as $key=>$value)
		{
			$this->smarty->assign($key, $value);
		}
		$this->smarty->assign('item', '测试项');
		$this->smarty->assign('title', '测试项');
		$this->smarty->display('firstPage.tpl');
	}
	//测试项新增时的校验
	public function add_testitem($str)
	{
		if(strlen(str_replace(" ", "", $str)) == 0)
		{
			$this->form_validation->set_message('add_testitem', '测试项不能为空！');
			return FALSE;
		}
		else
		{
			$testitemObj1 = $this->db->query("SELECT tm.name FROM testitem tm WHERE tm.status = '1' AND tm.name = '$str'");
			$testitemObj2 = $this->db->query("SELECT tm.name FROM testitem tm WHERE tm.status = '2' AND tm.name = '$str'");
			if ($testitemObj1->num_rows() != 0)
			{
				$this->form_validation->set_message('add_testitem', '该测试项已存在，请重新输入！');
				return FALSE;
			}
			else if($testitemObj2->num_rows() != 0)
			{
				$this->form_validation->set_message('add_testitem', '该测试项已被停用，存在历史记录，请重新测试项名称！');
				return FALSE;
			}
			else
			{
				return TRUE;
			}
		}
	}
	//测试项编辑时的校验
	public function edit_testitem($str)
	{
		if(strlen(str_replace(" ", "", $str)) == 0)
		{
			$this->form_validation->set_message('edit_testitem', '测试项不能为空！');
			return FALSE;
		}
		else
		{
			//取得当前id
			$postUrl = $this->uri->uri_string();
			$id = substr($postUrl, strripos($postUrl, "/")+1);
			//查询id不等于当前，且测试项等于当前输入的测试项的记录数
			$numObj = $this->db->query("SELECT COUNT(*) AS num FROM testitem tm WHERE tm.name = '$str' AND tm.id != '$id'");
			$num = $numObj->first_row()->num;
			//记录为空时允许修改，不为空时，不允许修改
			if($num != 0)
			{
				$this->form_validation->set_message('edit_testitem', '测试项已存在！');
				return FALSE;
			}
			else
			{
				return TRUE;
			}
		}
	}
	
	public function testright()
	{
		$crud = new grocery_CRUD();
		$crud->set_theme('datatables');
		$crud->required_fields('name');
		$crud->display_as('name', '名称');
		$output = $crud->render();
		foreach ($output as $key=>$value)
		{
			$this->smarty->assign($key, $value);
		}
		$this->smarty->assign('title', '测试员权限');
		$this->smarty->display('firstPage.tpl');
	}
	
	public function factory()
	{
		$crud = new grocery_CRUD();
		$crud->set_theme('datatables');
		$crud->required_fields('status');
		$crud->display_as('name', '工厂')->display_as('status','状态');
		$crud->set_relation("status",'status','statusname');
		$crud->unset_delete();
		//新增，编辑时对工厂名称的判断
		$postUrl = $this->uri->uri_string();
		if(strpos($postUrl, "insert_validation") != FALSE)
		{
			$crud->set_rules('name','factoryname','callback_add_factoryname');
		}
		else if(strpos($postUrl, "update_validation") != FALSE)
		{
			$crud->set_rules('name','factoryname','callback_edit_factoryname');
		}
		else
		{
			//
		}
		$output = $crud->render();
		foreach ($output as $key=>$value)
		{
			$this->smarty->assign($key, $value);
		}
		$this->smarty->assign('item', '工厂');
		$this->smarty->assign('title', '工厂');
		$this->smarty->display('firstPage.tpl');
	}
	public function add_factoryname($str)
	{
		if(preg_match("/^[a-zA-Z0-9]+$/", $str))
		{
			$nameRecordObj = $this->db->query("SELECT fy.name FROM factory fy WHERE fy.name = '$str'");
			if($nameRecordObj->num_rows() != 0)
			{
				$this->form_validation->set_message('add_factoryname', '工厂已存在！');
				return FALSE;
			}
			else
			{
				return TRUE;
			}
		}
		else
		{
			$this->form_validation->set_message('add_factoryname', '名称不为空且只能包含英文字母或数字！');
			return FALSE;
		}
	}
	public function edit_factoryname($str)
	{
		if(preg_match("/^[a-zA-Z0-9]+$/", $str))
		{
			$postUrl = $this->uri->uri_string();
			$id = substr($postUrl, strripos($postUrl, "/")+1);
			//查询id不等于当前，且工厂名称等于当前输入的设备序列号的记录数
			$numObj = $this->db->query("SELECT COUNT(*) AS num FROM factory fy WHERE fy.name = '$str' AND fy.id != '$id'");
			$num = $numObj->first_row()->num;
			//记录为空时允许修改，不为空时，不允许修改
			if($num != 0)
			{
				$this->form_validation->set_message('edit_factoryname', '该工厂已存在！');
				return FALSE;
			}
			else
			{
				return TRUE;
			}
		}
		else
		{
			$this->form_validation->set_message('edit_factoryname', '名称不为空且只能包含英文字母或数字！');
			return FALSE;
		}
	}
	
	public function department()
	{
		$crud = new grocery_CRUD();
		$crud->set_theme('datatables');
		$crud->required_fields('status','factory');
		$crud->display_as('name', '车间')->display_as('factory','工厂')->display_as('status','状态');
		$crud->set_relation("status",'status','statusname')
			 ->set_relation("factory",'factory','name');
		$crud->unset_delete();
		//新增，编辑时对车间名称的判断
		$postUrl = $this->uri->uri_string();
		if(strpos($postUrl, "insert_validation") != FALSE)
		{
			$crud->set_rules('name','factoryname','callback_add_departmentname');
		}
		else if(strpos($postUrl, "update_validation") != FALSE)
		{
			$crud->set_rules('name','factoryname','callback_edit_departmentname');
		}
		else
		{
			//
		}
		$output = $crud->render();
		foreach ($output as $key=>$value)
		{
			$this->smarty->assign($key, $value);
		}
		$this->smarty->assign('item', '车间');
		$this->smarty->assign('title', '车间');
		$this->smarty->display('firstPage.tpl');
	}
	public function add_departmentname($str)
	{
		if(preg_match("/^[a-zA-Z0-9]+$/", $str))
		{
			$nameRecordObj = $this->db->query("SELECT dt.name FROM department dt WHERE dt.name = '$str'");
			if($nameRecordObj->num_rows() != 0)
			{
				$this->form_validation->set_message('add_departmentname', '该车间已存在！');
				return FALSE;
			}
			else
			{
				return TRUE;
			}
		}
		else
		{
			$this->form_validation->set_message('add_departmentname', '车间名称不能为空且只能包含英文字母或数字！');
			return FALSE;
		}
	}
	public function edit_departmentname($str)
	{
		if(preg_match("/^[a-zA-Z0-9]+$/", $str))
		{
			$postUrl = $this->uri->uri_string();
			$id = substr($postUrl, strripos($postUrl, "/")+1);
			//查询id不等于当前，且车间名称等于当前输入的设备序列号的记录数
			$numObj = $this->db->query("SELECT COUNT(*) AS num FROM department dt WHERE dt.name = '$str' AND dt.id != '$id'");
			$num = $numObj->first_row()->num;
			//记录为空时允许修改，不为空时，不允许修改
			if($num != 0)
			{
				$this->form_validation->set_message('edit_departmentname', '该车间已存在！');
				return FALSE;
			}
			else
			{
				return TRUE;
			}
		}
		else
		{
			$this->form_validation->set_message('edit_departmentname', '车间名称不能为空且只能包含英文字母或数字！');
			return FALSE;
		}
	}
	
	public function teststation()
	{
		$crud = new grocery_CRUD();
		$crud->set_theme('datatables');
		$crud->required_fields('status','department');
		$crud->display_as('name', '名称')->display_as('department', '车间')
			 ->display_as('status','状态');
		$crud->set_relation('status','status','statusname')
			 ->set_relation('department','department','name');
		$crud->unset_delete();
		//新增，编辑时对测试站名称的判断
		$postUrl = $this->uri->uri_string();
		if(strpos($postUrl, "insert_validation") != FALSE)
		{
			$crud->set_rules('name','teststationname','callback_add_testsationname');
		}
		else if(strpos($postUrl, "update_validation") != FALSE)
		{
			$crud->set_rules('name','teststationname','callback_edit_testsationname');
		}
		else
		{
			//
		}
		
		$output = $crud->render();
		foreach ($output as $key=>$value)
		{
			$this->smarty->assign($key, $value);
		}
		$this->smarty->assign('item', '测试站点');
		$this->smarty->assign('title', '测试站点');
		$this->smarty->display('firstPage.tpl');
	}
	//新增时测试站名称的验证
	public function add_testsationname($str)
	{
		if(preg_match("/^[a-zA-Z0-9]+$/", $str))
		{
			$nameRecordObj = $this->db->query("SELECT tn.name FROM teststation tn WHERE tn.name = '$str'");
			if($nameRecordObj->num_rows() != 0)
			{
				$this->form_validation->set_message('add_testsationname', '测试站名称已存在！');
				return FALSE;
			}
			else
			{
				return TRUE;
			}
		}
		else
		{
			$this->form_validation->set_message('add_testsationname', '名称不为空且只能包含英文字母或数字！');
			return FALSE;
		}
	}
	//编辑时对测试站名称的验证
	public function edit_testsationname($str)
	{
		if(preg_match("/^[a-zA-Z0-9]+$/", $str))
		{
			//取得当前id
			$postUrl = $this->uri->uri_string();
			$id = substr($postUrl, strripos($postUrl, "/")+1);
			//查询id不等于当前，且测试站点名称等于当前输入的测试站点名称的记录数
			$numObj = $this->db->query("SELECT COUNT(*) AS num FROM teststation tn WHERE tn.name = '$str' AND tn.id != '$id'");
			$num = $numObj->first_row()->num;
			//记录为空时允许修改，不为空时，不允许修改
			if($num != 0)
			{
				$this->form_validation->set_message('edit_testsationname', '测试站已存在！');
				return FALSE;
			}
			else
			{
				return TRUE;
			}
		}
		else
		{
			$this->form_validation->set_message('edit_testsationname', '名称不为空且只能包含英文字母或数字！');
			return FALSE;
		}
	}
	//验证数字和字母,不可以为空
	public function num_character($str)
	{
		if(preg_match("/^[a-zA-Z0-9]+$/", $str))
		{
			return TRUE;
		}
		else
		{
			$this->form_validation->set_message('num_character', '部门、工厂不为空且只能包含英文字母或数字！');
			return FALSE;
		}
	}
	
	public function equipment()
	{
		$crud = new grocery_CRUD();
		$crud->set_theme('datatables');
		$crud->display_as('partnumber', '型号')->display_as('sn', '序列号')->display_as('status','状态');
		$crud->set_relation('status','status','statusname');
		$crud->unset_add();
		$crud->unset_delete();	
		$crud->required_fields('status','partnumber');
		$crud->set_rules('sn','sn','callback_equipment_sn');
		$output = $crud->render();
		foreach ($output as $key=>$value)
		{
			$this->smarty->assign($key, $value);
		}
		$this->smarty->assign('item', '测试设备');
		$this->smarty->assign('title', '测试设备');
		$this->smarty->display('firstPage.tpl');
	}
	//测试设备编辑时设备序列号的校验
	public function equipment_sn($str)
	{
		//取得当前id
		if(strlen(str_replace(" ", "", $str)) == 0)
		{
			$this->form_validation->set_message('equipment_sn', '序列号不能为空！');
			return FALSE;
		}
		else
		{
			$postUrl = $this->uri->uri_string();
			$id = substr($postUrl, strripos($postUrl, "/")+1);
			//查询id不等于当前，且设备序列号等于当前输入的设备序列号的记录数
			$numObj = $this->db->query("SELECT COUNT(*) AS num FROM equipment et WHERE et.sn = '$str' AND et.id != '$id'");
			$num = $numObj->first_row()->num;
			//记录为空时允许修改，不为空时，不允许修改
			if($num != 0)
			{
				$this->form_validation->set_message('equipment_sn', '该设备已授权！');
				return FALSE;
			}
			else
			{
				return TRUE;
			}
		}
	}
	
	
	public function producttestinfo()
	{
		$crud = new grocery_CRUD();
		$crud->set_theme('datatables');
		$crud->required_fields('sn', 'equipmentSn', 'testTime', 'testStation', 'tester', 'productType', 'result');
		$crud->display_as('sn', '序列号')->display_as('equipmentSn', '设备序列号')
			 ->display_as('testTime', '测试时间')->display_as('testStation', '测试站点')
			 ->display_as('tester', '测试员')->display_as('productType', '产品类型')
			 ->display_as('result', '测试结果');
		$crud->set_relation('testStation', 'testStation', 'name');
		$crud->set_relation('tester', 'tester', 'employeeId');
		$crud->set_relation('productType', 'productType', 'name');
		$output = $crud->render();
		foreach ($output as $key=>$value)
		{
			$this->smarty->assign($key, $value);
		}
		$this->smarty->assign('title', '产品测试信息');
		$this->smarty->display('firstPage.tpl');
	}

	public function producttypetestcase()
	{
		$crud = new grocery_CRUD();
		$crud->set_theme('datatables');
		$crud->required_fields('productType', 'testItem', 'stateFile', 'portNum');
		$crud->display_as('productType', '产品型号')->display_as('testItem', '测试项')->display_as('stateFile', '状态文件')->display_as('portNum', '端口数');
		$crud->set_relation('productType', 'productType', 'name');
		$crud->set_relation('testItem', 'testItem', 'name');
		$output = $crud->render();
		foreach ($output as $key=>$value)
		{
			$this->smarty->assign($key, $value);
		}
		$this->smarty->assign('title', '产品测试方案');
		$this->smarty->display('firstPage.tpl');
	}

	public function testitemmarkvalue()
	{
		$crud = new grocery_CRUD();
		$crud->set_theme('datatables');
		$crud->required_fields('testItemResult', 'value');
		$crud->display_as('testItemResult', '测试项目')->display_as('value', '结果值')->display_as('markF', '频率标记')->display_as('markT', '时间标记');
		$crud->set_relation('testItemResult', 'testItemResult', 'id');
		$output = $crud->render();
		foreach ($output as $key=>$value)
		{
			$this->smarty->assign($key, $value);
		}
		$this->smarty->assign('title', '测试项目配置');
		$this->smarty->display('firstPage.tpl');
	}

	public function testitemresult()
	{
		$crud = new grocery_CRUD();
		$crud->set_theme('datatables');
		$crud->required_fields('productTestInfo', 'testItem', 'img');
		$crud->display_as('productTestInfo', '产品测试信息')->display_as('testItem', '测试项目')->display_as('img', '测试图路径');
		$crud->set_relation('productTestInfo', 'productTestInfo', 'id');
		$crud->set_relation('testItem', 'testItem', 'name');
		$output = $crud->render();
		foreach ($output as $key=>$value)
		{
			$this->smarty->assign($key, $value);
		}
		$this->smarty->assign('title', '测试项目结果');
		$this->smarty->display('firstPage.tpl');
	}

	public function user()
	{
		$crud = new grocery_CRUD();
		$crud->set_theme('datatables');
		$crud->columns('fullname','username','password','status','team');
		$crud->required_fields('fullname','password', 'team','confirmpassword','status');
		$crud->edit_fields('fullname','username','password','status','team');
		$crud->display_as('fullname', '姓名')
			 ->display_as('username', '用户名')
			 ->display_as('password', '密码')
			 ->display_as('confirmpassword', '确认密码')
			 ->display_as('status', '状态')
			 ->display_as('team', '组');
		$crud->set_relation('team', 'team', 'name')
			 ->set_relation('status','status','statusname');
		$crud->unset_delete();
		//新增，编辑时对用户名的判断
		$postUrl = $this->uri->uri_string();
		if(strpos($postUrl, "insert_validation") != FALSE)
		{
			$crud->set_rules('username','username','callback_add_username');
		}
		else if(strpos($postUrl, "update_validation") != FALSE)
		{
			$crud->set_rules('username','username','callback_edit_username');
		}
		else
		{
			//
		}
		$crud->set_rules('password','user_password','callback_check_user_password');
		
		$output = $crud->render();
		foreach ($output as $key=>$value)
		{
			$this->smarty->assign($key, $value);
		}
		$this->smarty->assign('item', '用户');
		$this->smarty->assign('title', '系统用户');
		$this->smarty->display('firstPage.tpl');
	}
	//添加用户时对用户名的校验
	public function add_username($str)
	{
		if(strlen($str) < 6)
		{
			$this->form_validation->set_message('add_username', '用户名不能少于六位且只能包含英文字母或数字！');
			return FALSE;
		}
		else
		{
			if(preg_match("/^[a-zA-Z0-9]+$/", $str))
			{
				$nameRecordObj = $this->db->query("SELECT ur.username FROM user ur WHERE ur.username = '$str'");
				if($nameRecordObj->num_rows() != 0)
				{
					$this->form_validation->set_message('add_username', '此用户名的用户已存在！');
					return FALSE;
				}
				else
				{
					return TRUE;
				}
			}
			else
			{
				$this->form_validation->set_message('add_username', '用户名不能少于六位且只能包含英文字母或数字！');
				return FALSE;
			}
		}
	}
	//编辑时对用户名的验证
	public function edit_username($str)
	{
		if(strlen($str) < 6)
		{
			$this->form_validation->set_message('edit_username', '用户名不能少于六位且只能包含英文字母或数字！');
			return FALSE;
		}
		else
		{
			if(preg_match("/^[a-zA-Z0-9]+$/", $str))
			{
				//取得当前id
				$postUrl = $this->uri->uri_string();
				$id = substr($postUrl, strripos($postUrl, "/")+1);
				//查询id不等于当前，且测试站点名称等于当前输入的测试站点名称的记录数
				$numObj = $this->db->query("SELECT COUNT(*) AS num FROM user ur WHERE ur.username = '$str' AND ur.id != '$id'");
				$num = $numObj->first_row()->num;
				//记录为空时允许修改，不为空时，不允许修改
				if($num != 0)
				{
					$this->form_validation->set_message('edit_username', '用户已存在！');
					return FALSE;
				}
				else
				{
					return TRUE;
				}
			}
			else
			{
				$this->form_validation->set_message('edit_username', '用户名不能少于六位且只能包含英文字母或数字！');
				return FALSE;
			}
		}
		
	}
	//对密码的格式校验
	public function check_user_password($str)
	{
		if(strlen($str) < 6)
		{
			$this->form_validation->set_message('check_user_password', '密码不能少于六位且只能包含英文字母或数字！');
			return FALSE;
		}
		else
		{
			if(preg_match("/^[a-zA-Z0-9]+$/", $str))
			{
				return TRUE;
			}
			else
			{
				$this->form_validation->set_message('check_user_password', '密码不能少于六位且只能包含英文字母或数字！');
				return FALSE;
			}
		}
	}
	
	public function firstpagenotice()
	{
		$crud = new grocery_CRUD();
		$crud->set_theme('datatables');
		$crud->required_fields('content');
		$crud->display_as('content', '通知内容');
		$crud->unset_add();
		$crud->unset_delete();
		$output = $crud->render();
		foreach ($output as $key=>$value)
		{
			$this->smarty->assign($key, $value);
		}
		$this->smarty->assign('title', '首页通知');
		$this->smarty->display('firstPage.tpl');
	}

	function index()
	{
		$this->smarty->assign('title', '首页');
		$this->smarty->assign('css_files', array());
		$this->smarty->assign('js_files', array());
		$this->smarty->assign('output', '');
		$this->smarty->display('firstPage.tpl');
	}
	
	/* add by gary */
	public function packingemployees()
	{
		$crud = new grocery_CRUD();
		$crud->set_theme('datatables');
		$crud->required_fields('employeeId', 'name', 'password');
		$crud->display_as('employeeId', '工号')->display_as('name', '姓名')->display_as('password', '密码');
		$output = $crud->render();
		foreach ($output as $key=>$value)
		{
			$this->smarty->assign($key, $value);
		}
		$this->smarty->assign('title', '包装用户');
		$this->smarty->display('firstPage.tpl');		
	}
	
}
