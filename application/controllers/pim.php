<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');
class Pim extends CW_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library("Pagefenye");
	}

	public function showPimdata($nowpage)
	{
		$pageSize = 10;
		$sidepage = 3;
		$totalRowSql = "SELECT COUNT(id) as totalrow FROM pim_ser_num";
		$totalRowList = $this->db->query($totalRowSql);
		$totalRowArray = $totalRowList->result_array();
		$totalRecord = $totalRowArray[0]["totalrow"];
		$fenye = $this->pagefenye->getFenye($nowpage, $totalRecord, $pageSize, $sidepage);
		$selectFrom = ($nowpage - 1) * $pageSize;
		$pimrecordsSql = "SELECT tt.id,max(tt.test_time) AS test_time,tt.model,tt.ser_num,tt.work_num,tt.name 
				FROM (SELECT pm.id,pp.test_time,pm.model,pm.ser_num,pm.work_num,pl.name FROM pim_ser_num pm JOIN pim_label pl ON pm.pim_label = pl.id JOIN pim_ser_num_group pp ON pp.pim_ser_num = pm.id) tt 
				GROUP BY tt.id LIMIT ".$selectFrom.",".$pageSize."";
		$result = $this->db->query($pimrecordsSql);
		$pimListArray = $result->result_array();
		foreach ($pimListArray as &$item)
		{
			$item['test_time'] = substr($item['test_time'], 0, 10).'<br/>'.substr($item['test_time'], 11);
			$item['model'] = substr($item['model'], 9);
		}
		$this->smarty->assign('fenye', $fenye);
		$this->smarty->assign('pimListArray', $pimListArray);
		$this->smarty->assign('title', "PIM测试数据查询");
		$this->smarty->display('pim.tpl');
	}

	public function getPimData($pimsernum)
	{
		$selectlabelnameSql = "SELECT pl.name FROM pim_label pl JOIN pim_ser_num pm ON pm.pim_label = pl.id WHERE pm.id = ".$pimsernum;
		$labenameList = $this->db->query($selectlabelnameSql);
		$labelnameArray = $labenameList->result_array();
		$selectLimitlineSql = "SELECT pm.col12 FROM pim_ser_num pm WHERE pm.id=".$pimsernum;
		$limitLineResult = $this->db->query($selectLimitlineSql);
		$limtLineArray = $limitLineResult->result_array();
		$limtline = substr($limtLineArray[0]['col12'], 12);
		$selectInfolistSql = "SELECT pm.col1,pm.col2,pm.col3,pm.col4,pm.col5,pm.col6,pm.col7,pm.col8,pm.col9,pm.col10,pm.col11,pm.model,pp.test_time,pp.upload_date,pm.col12,pm.ser_num,pm.col13 
								FROM pim_ser_num pm JOIN pim_ser_num_group pp ON pp.pim_ser_num = pm.id WHERE pm.id= ".$pimsernum." ORDER BY pp.test_time";
		$infoList = $this->db->query($selectInfolistSql);
		$infolistArray = $infoList->result_array();
		$selectTesttimeSql = "SELECT pp.id,pp.test_time FROM pim_ser_num_group pp WHERE pp.pim_ser_num = ".$pimsernum." ORDER BY pp.test_time";
		$testtimeList = $this->db->query($selectTesttimeSql);
		$testtimeArray = $testtimeList->result_array();
		foreach ($testtimeArray as &$item)
		{
			$testdataSql = "SELECT pa.frequency,pa.value FROM pim_ser_num_group_data pa WHERE pa.pim_ser_num_group = ".$item["id"]." ORDER BY pa.frequency";
			$testdataList = $this->db->query($testdataSql);
			$testdataArray = $testdataList->result_array();
			$item['testdata'] = $testdataArray;
		}
		$this->smarty->assign('labelnamearray', $labelnameArray);
		$this->smarty->assign('infolistArray', $infolistArray);
		$this->smarty->assign('testdata', $testtimeArray);
		$this->smarty->assign('pimsernum', $pimsernum);
		$this->smarty->assign('limtline', $limtline);
		$this->smarty->display('pimtestdata.tpl');
	}
}
