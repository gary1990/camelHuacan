<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');
class Pagefenye
{
	public function getFenye($nowpage, $totalrecord, $pageSize, $sidepage)
	{
		$fenye = "";
		$totalPage = ceil($totalrecord / $pageSize);
		$fenyepageArray = array(
			"firstpage"=>"",
			"lastpage"=>"",
			"leftpage"=> array(),
			"nowpage"=>$nowpage,
			"rightpage"=> array(),
			"nextpage"=>"",
			"endpage"=>""
		);
		if ($totalPage == 0)
		{
			$fenyepageArray = array(
				"firstpage"=>"",
				"lastpage"=>"",
				"leftpage"=> array(),
				"nowpage"=>"",
				"rightpage"=> array(),
				"nextpage"=>"",
				"endpage"=>""
			);
		}
		else
		{
			if ($nowpage == 1)
			{
				$fenyepageArray["firstpage"] = "";
				$fenyepageArray["lastpage"] = "";
				$fenyepageArray["leftpage"] = array();
				$fenyepageArray["nowpage"] = 1;
				if ($totalPage == 1)
				{
					$fenyepageArray["rightpage"] = array();
					$fenyepageArray["nextpage"] = "";
					$fenyepageArray["endpage"] = "";
				}
				else if ($totalPage <= $nowpage + $sidepage)
				{
					for ($i = 2; $i <= $totalPage; $i++)
					{
						array_push($fenyepageArray["rightpage"], $i);
					}
					$fenyepageArray["nextpage"] = "下一页";
					$fenyepageArray["endpage"] = "";
				}
				else
				{
					for ($i = 2; $i <= $nowpage + $sidepage; $i++)
					{
						array_push($fenyepageArray["rightpage"], $i);
					}
					$fenyepageArray["nextpage"] = "下一页";
					$fenyepageArray["endpage"] = "尾页";
				}
			}
			else
			{
				$fenyepageArray["nowpage"] = $nowpage;
				$fenyepageArray["lastpage"] = "上一页";
				if ($nowpage == $totalPage)
				{
					$fenyepageArray["endpage"] = "";
					$fenyepageArray["nextpage"] = "";
					$fenyepageArray["rightpage"] = array();
					if ($nowpage - $sidepage < 1)
					{
						$fenyepageArray["firstpage"] = "";
						for ($i = 1; $i < $nowpage; $i++)
						{
							array_push($fenyepageArray["leftpage"], $i);
						}
					}
					else
					{
						$fenyepageArray["firstpage"] = "首页";
						for ($i = $nowpage - $sidepage; $i < $nowpage; $i++)
						{
							array_push($fenyepageArray["leftpage"], $i);
						}
					}
				}
				else
				{
					$fenyepageArray["nowpage"] = $nowpage;
					$fenyepageArray["lastpage"] = "上一页";
					$fenyepageArray["nextpage"] = "下一页";
					if ($nowpage - $sidepage < 1)
					{
						$fenyepageArray["firstpage"] = "";
						for ($i = 1; $i < $nowpage; $i++)
						{
							array_push($fenyepageArray["leftpage"], $i);
						}
					}
					else
					{
						$fenyepageArray["firstpage"] = "首页";
						for ($i = $nowpage - $sidepage; $i < $nowpage; $i++)
						{
							array_push($fenyepageArray["leftpage"], $i);
						}
					}
					if ($nowpage + $sidepage >= $totalPage)
					{
						$fenyepageArray["endpage"] = "";
						for ($i = $nowpage + 1; $i <= $totalPage; $i++)
						{
							array_push($fenyepageArray["rightpage"], $i);
						}
					}
					else
					{
						$fenyepageArray["endpage"] = "尾页";
						for ($i = $nowpage + 1; $i <= $nowpage + $sidepage; $i++)
						{
							array_push($fenyepageArray["rightpage"], $i);
						}
					}
				}
			}
		}
		$fenye = "<a class='page' href='1'>".$fenyepageArray['firstpage']."</a>&nbsp<a class='page' href='".($nowpage - 1)."'>".$fenyepageArray['lastpage']."</a>&nbsp";
		//$lastUrl = $_SERVER["HTTP_REFERER"];
		foreach ($fenyepageArray['leftpage'] as $leftpage)
		{
			$fenye .= "<a class='page' href='".$leftpage."'>".$leftpage."</a>&nbsp";
		}
		if ($totalPage == 0)
		{
			$fenye .= "<b style='color:red'>数据库中未查到数据！</b>";
		}
		else
		{
			$fenye .= "<a class='page' href='".$nowpage."'>[".$nowpage."]</a>&nbsp";
		}
		foreach ($fenyepageArray['rightpage'] as $rightpage)
		{
			$fenye .= "<a class='page' href='".$rightpage."'>".$rightpage."</a>&nbsp";
		}
		$fenye .= "<a class='page' href='".($nowpage + 1)."'>".$fenyepageArray['nextpage']."</a>&nbsp<a class='page' href='".$totalPage."'>".$fenyepageArray['endpage']."</a>&nbsp";
		return $fenye;
	}

}
