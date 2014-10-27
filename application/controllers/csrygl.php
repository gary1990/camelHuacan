<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');
class Csrygl extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->_init();
	}

	private function _init()
	{
	}
//
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

	function index($tester, $timeFrom = null, $timeTo = null)
	{
		//处理where条件
		$sqlTimeFrom = "";
		$sqlTimeTo = "";
		if ($timeFrom != null)
		{
			if ($this->_checkTime($timeFrom))
			{
				$sqlTimeFrom = " AND testTime >= '$timeFrom'";
			}
			else
			{
				$sqlTimeFrom = " AND 0";
			}
		}
		if ($timeTo != null)
		{
			if ($this->_checkTime($timeTo))
			{
				$sqlTimeTo = " AND testTime <= '$timeTo'";
			}
			else
			{
				$sqlTimeTo = " AND 0";
			}
		}
		$tmpRes = $this->db->query("SELECT count(*) NUM,  DATE(testTime) dateValue FROM productTestInfo WHERE tester = ?".$sqlTimeFrom.$sqlTimeTo." GROUP BY dateValue ORDER BY dateValue", array($tester));
		$tmpArray = $tmpRes->result_array();
		print_r($tmpArray);
	}

	function testerWorkLoad($tester)
	{
		//取得测试员名字
		$tmpRes = $this->db->query("SELECT name FROM tester WHERE id = ?", array($tester));
		$testerName = $tmpRes->first_row()->name;
		$jsonData['testerName'] = $testerName;
		$tmpRes = $this->db->query("SELECT CONCAT(LPAD(HOUR(MAX(testTime)), 2, '0'),':',LPAD(MINUTE(MAX(testTime)), 2, '0')) maxT, CONCAT(LPAD(HOUR(MIN(testTime)), 2, '0'),':',LPAD(MINUTE(MIN(testTime)), 2, '0')) minT, CONCAT(UNIX_TIMESTAMP(CONCAT(DATE(testTime), ' 00:00:00')) + 28800, '000') testDate FROM productTestInfo WHERE tester = ? GROUP BY testDate ORDER BY testDate", array($tester));
		$tmpArray = $tmpRes->result_array();
		$jasonData['length'] = count($tmpArray);
		foreach ($tmpArray as $item)
		{
			$jsonData['data'][] = array(
				(float)$item['testDate'],
				(int)$item['maxT'],
				(int)$item['minT'],
				(int)$item['maxT'],
				(int)$item['minT'],
				(int)($item['maxT'] - $item['minT'])
			);
		}
		echo json_encode($jsonData);
	}

	function testerPerformance($tester)
	{
		//取得测试员名字
		$tmpRes = $this->db->query("SELECT name FROM tester WHERE id = ?", array($tester));
		$testerName = $tmpRes->first_row()->name;
		$jsonData['testerName'] = $testerName;
		$tmpRes = $this->db->query("SELECT CONCAT(UNIX_TIMESTAMP(CONCAT(DATE(testTime), ' 00:00:00')) + 28800, '000') testDate, count(*) num FROM `productTestInfo` WHERE tester = ? AND result = 1 GROUP BY testDate", array($tester));
		$tmpArray = $tmpRes->result_array();
		$jasonData['length'] = count($tmpArray);
		foreach ($tmpArray as $item)
		{
			$jsonData['data'][] = array(
				(float)$item['testDate'],
				(float)$item['num']
			);
		}
		echo json_encode($jsonData);
	}

}
