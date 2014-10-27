<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');
class Sckb extends CW_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->_init();
	}

	private function _init()
	{
		//取得测试站点
		$tmpRes = $this->db->query("SELECT * FROM testStation ORDER BY name");
		$testStationList = array(''=>'所有');
		if ($tmpRes->num_rows() > 0)
		{
			foreach ($tmpRes->result() as $row)
			{
				$testStationList[$row->id] = $row->name;
			}
		}
		$this->smarty->assign('testStationList', $testStationList);
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

	function index()
	{
		$date = $this->input->post('Date_Year').'-'.$this->input->post('Date_Month').'-'.sprintf("%02d", $this->input->post('Date_Day'));
		if (!($this->_checkDateFormat($date)))
		{
			$date = date("Y-m-d");
		}
		$this->smarty->assign('theDate', $date);
		$sqlTestStation = '';
		if ($this->input->post('testStation') != '')
		{
			$sqlTestStation = "AND testStation = '".$this->input->post('testStation')."'";
		}
		$tmpRes = $this->db->query("SELECT count(*) num, HOUR(testTime) hour FROM `productTestInfo` WHERE testTime >= '".$date." 00:00:00' AND testTime <= '".$date." 23:59:59'".$sqlTestStation." GROUP BY hour");
		$totalNumArray = $tmpRes->result_array();
		//添加时间标签
		$totalNumList = array();
		for ($i = 0; $i < 24; $i++)
		{
			$totalNumList[$i] = 0;
		}
		foreach ($totalNumArray as $item)
		{
			$totalNumList[$item['hour']] = $item['num'];
		}
		$tmpRes = $this->db->query("SELECT count(*) num, HOUR(testTime) hour FROM `productTestInfo` WHERE result = 1 AND testTime >= '".$date." 00:00:00' AND testTime <= '".$date." 23:59:59'".$sqlTestStation." GROUP BY hour");
		$okNumArray = $tmpRes->result_array();
		//添加时间标签
		$passRateList = array();
		for ($i = 0; $i < 24; $i++)
		{
			$passRateList[$i] = 0;
		}
		foreach ($okNumArray as $item)
		{
			$passRateList[$item['hour']] = number_format(($item['num'] * 100) / $totalNumList[$item['hour']], 1, '.', '');;
		}
		//计算通过率
		$this->smarty->assign('totalNumList', $totalNumList);
		$this->smarty->assign('passRateList', $passRateList);
		$this->smarty->assign('title', "生产看板");
		$this->smarty->display('sckb.tpl');
	}

}
