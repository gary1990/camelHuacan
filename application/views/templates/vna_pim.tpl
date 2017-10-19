<!--{extends file='defaultPage.tpl'}-->
<!--{block name=title}-->
<title>{$title}</title>
<!--{/block}-->
<!--{block name=style}-->
<link rel="stylesheet" type="text/css" href="{base_url()}resource/css/ui.datepicker.css" />
<link rel="stylesheet" type="text/css" href="{base_url()}resource/css/chosen.css" />
<style type="text/css">
	.locLong {
		width: 100px;
	}
	.condition {
		vertical-align: text-bottom;
		text-align: left;
	}
	.spanStyle {
		margin-top: 5px;
	}
	.subCondition {
		width: 260px;
		height: 30px;
		line-height: 30px;
		float: left;
	}
	.snCondition {
		width: 520px;
	}
	.snInput{
		width: 330px;
	}
	.search{
		width: 800px
	}
	.bt {
		width: 70px
	}
	.gqts_item {
		border: 1px solid #DDDDDD;
		width: 75px;
		height: 20px;
		margin-top: 10px;
		text-align: center;
		cursor:pointer;
		float: left;
	}
	table{
		border-collapse: collapse;
		border-collapse: 0px;
	}
	th,td{
		border-bottom:1px solid #DDDDDD;
	}
	.option_codition{
		width:154px;
	}
	.seprateline
	{
		height:5px;
		margin:1em 0 1em 0;
	}
	.datepicker_header{
		width:224px;
	}
</style>
<!--{/block}-->
<!--{block name=script}-->
<script type="text/javascript" src="{base_url()}resource/js/calendar/ui.datepicker.js"></script>
<script type="text/javascript" src="{base_url()}resource/js/calendar/ui.datepicker-zh-CN.js"></script>
<script type="text/javascript" src="{base_url()}resource/js/jquery.form.js"></script>
<script type="text/javascript" src="{base_url()}resource/js/chosen.jquery.js"></script>
<script type="text/javascript">
	$(function(){
		//具有搜索功能的下拉列表
		$(".teststation").chosen();
		$(".equipment").chosen();
		$(".vnatester").chosen();
		$(".producttype").chosen();
		var current_item = $(".current_item").attr("value");
		if(current_item == "PIM")
		{
			$(".teststationCn").hide(); 
			$(".equipmentCn").hide();
			$(".testerCn").hide();
			$(".producttypeCn").hide();
			$(".platenumCn").hide();
		}
		$(".gqts_item").css("background-color","white");
		$(".gqts_item").css("color","#DDDDDD");
		$(".gqts_item").css("font-weight","normal");
		$("#"+current_item).css("background-color","#E5ECF9");
		$("#"+current_item).css("color","black");
		$("#"+current_item).css("font-weight","bold");
		$(".testResult").hide();
		$("."+current_item).show();
		
		$(".gqts_item").click(function(){
			$(".gqts_item").css("background-color","white");
			$(".gqts_item").css("color","#DDDDDD");
			$(".gqts_item").css("font-weight","normal");
			$(this).css("background-color","#E5ECF9");
			$(this).css("color","black");
			$(this).css("font-weight","bold");
			var current_item = $(this).attr("id");
			$(".current_item").attr("value",current_item);
			$(".testResult").hide();
			$("."+current_item).show();
			if(current_item == "PIM")
			{
				$(".teststationCn").hide(); 
				$(".equipmentCn").hide();
				$(".testerCn").hide();
				$(".producttypeCn").hide();
				$(".platenumCn").hide();
			}
			else
			{
				$(".teststationCn").show(); 
				$(".equipmentCn").show();
				$(".testerCn").show();
				$(".producttypeCn").show();
				$(".platenumCn").show();
			}
		});
		$(".bt").click(function(e){
			//var current_action = $(this).attr("id");
			//$(".current_action").attr("value",current_action);
			var current_item = $(".current_item").attr("value");
			var baseurl = $("#base_url").val();
			if(current_item == "VNA")
			{
				var url = baseurl+"index.php/vna_pim/vna";
			}
			else
			{
				var url = baseurl+"index.php/vna_pim/pim";
			}
			$("#locForm").attr('action', url);
		});
		$(".page").click(function(e){
			e.preventDefault();
			var current_item = $(".current_item").attr("value");
			var baseurl = $("#base_url").val();
			if(current_item == "VNA")
			{
				var url = baseurl+"index.php/vna_pim/vna/"+ $(this).attr('href');
			}
			else
			{
				var url = baseurl+"index.php/vna_pim/pim/"+ $(this).attr('href');
			}
			$("#locForm").attr('action', url);
			$("#locForm").submit();
		});
		$(".export_vna").click(function(){
			$("#locForm").attr('action', "{site_url()}/vna_pim/export_vna");
			$("#locForm").submit();
		});
		$(".export_vna_excel").click(function(){
            $("#locForm").attr('action', "{site_url()}/vna_pim/export_vna_excel");
            $("#locForm").submit();
        });
	});
	jQuery(function($)
	{
		$('#date_from').datepicker({
			yearRange: '1900:2999',
			showOn: 'both',
			buttonImage: '{base_url()}resource/img/calendar.gif',
			buttonImageOnly: true,
			showButtonPanel: true
		});
	});
	jQuery(function($)
	{
		$('#date_to').datepicker({
			yearRange: '1900:2999',
			showOn: 'both',
			buttonImage: '{base_url()}resource/img/calendar.gif',
			buttonImageOnly: true,
			showButtonPanel: true
		});
	});
</script>
<!--{/block}-->
<!--{block name=body}-->
<div class="prepend-1 span-64">
	<div class="span-60">
		<form name="locForm" id="locForm" method="post" action="{site_url('vna_pim/vna')}">
			<div class="condition">
				<span class="span-5 spanStyle"> 时间: </span>
				<input id="date_from" class="locLong" type="text" name="timeFrom1" value="{$timeFrom1|default:''}"/>
				{html_options name=timeFrom2 options=$hourList selected=$timeFrom2|default:''}
				:
				{html_options name=timeFrom3 options=$minuteList selected=$timeFrom3|default:''}
				至:
				<input id="date_to" class="locLong" type="text" name="timeTo1" value="{$timeTo1|default:''}"/>
				{html_options name=timeTo2 options=$hourList selected=$timeTo2|default:''}
				:
				{html_options name=timeTo3 options=$minuteList selected=$timeTo3|default:''}
			</div>
			<div class="condition">
				<div class="subCondition teststationCn">
					<div class="span-5 spanStyle">
						测试站:
					</div>
					{html_options class="option_codition teststation" name=teststation options=$teststation selected=$smarty.post.teststation|default:''}
				</div>
				<div class="subCondition equipmentCn">
					<div class="span-5 spanStyle">
						测试设备:
					</div>
					{html_options class="option_codition equipment" name=equipment options=$equipment selected=$smarty.post.equipment|default:''}
				</div>
				<div class="subCondition testerCn">
					<div class="span-5 spanStyle">
						测试员:
					</div>
					{html_options class="option_codition vnatester" name=tester options=$vnatester selected=$smarty.post.tester|default:''}
				</div>
			</div>
			<div class="condition">
				<div class="subCondition producttypeCn">
					<div class="span-5 spanStyle">
						产品型号:
					</div>
					{html_options class="option_codition producttype" name=producttype options=$producttype selected=$smarty.post.producttype|default:''}
				</div>
				<div class="subCondition">
					<div class="span-5 spanStyle">
						测试结果:
					</div>
					{html_options class="option_codition result" name=testResult options=$testResultList selected=$smarty.post.testResult|default:''}
				</div>
				<div class="subCondition platenumCn">
					<div class="span-5 spanStyle">
						盘号:
					</div>
					<input class="platenum" name="platenum" value="{$smarty.post.platenum|default:''}" type="text"/>
				</div>
			</div>
			<div class="condition">
				<div class="subCondition">
					<div class="span-5 spanStyle">
						工单号:
					</div>
					<input name="labelnum" value="{$smarty.post.labelnum|default:''}" type="text"/>
				</div>
			</div>
			<div class="condition">
				<div class="subCondition snCondition">
					<div class="span-5 spanStyle">
						序列号:
					</div>
					<input name="sn" class="snInput" value="{$smarty.post.sn|default:''}" type="text"/>
				</div>
				<div class="subCondition search">
					<input class="bt" id="search" type="submit" value="查询"/>
					<input class="export_vna" type="button" value="导出报告"/>
					<input class="export_vna_excel" type="button" value="导出Excel"/>
					<input name="current_item" class="current_item" value="{if $title=='VNA测试记录'}VNA{else}PIM{/if}" type="hidden"/>
					<input name="current_page" class="current_page" value="{$smarty.post.current_page|default:'1'}" type="hidden"/>
				</div>
			</div>
		</form>
	</div>
</div>
<div class="prepend-1 span-64">
	<hr class="seprateline">
	<div class="gqts_item {if $title=='产品管理'} currItem {else} normal {/if}" id="VNA">
		VNA
	</div>
	<div class="gqts_item" id="PIM">
		PIM
	</div>
	<hr/>
	<div class="testResult VNA">
		<table border="0">
			<tr><th>序号</th><th>时间</th><th>测试站</th><th>测试设备</th><th>测试员</th><th>产品型号</th><th>序列号</th><th>工单号</th><th>测试结果</th></tr>
			{counter name="vancounter" start=$vnaCount+1 skip=-1 print=FALSE}
			{foreach from=$vnaResultArray key=k item=value name=vnaforeach}
			{if $smarty.foreach.vnaforeach.index is odd}
				<tr style="background:white">
				{else}
				<tr style="background:#E5ECF9">
			{/if}
				<td>{counter name="vancounter"}</td>
				<td>{$value['testTime']}</td>
				<td>{$value['testStation']}</td>
				<td>{$value['equipmentSn']}</td>
				<td>{$value['tester']}</td>
				<td>{$value['productType']}</td>
				<td>
					{if $value['tag1'] eq 1}
					<a href="{site_url('/packing/detail_vna')}/{$value['id']}" target="_blank">{$value['sn']}</a>
					{else}
					<a style="color:red;" href="{site_url('/packing/detail_vna')}/{$value['id']}" target="_blank">{$value['sn']}</a>
					{/if}
				</td>
				<td>{$value['workorder']}</td>
				<td>
					{if $value['result'] eq 1}
						<span style="color:green;">合格</span>
					{else}
						<span style="color:red;">不合格</span>
					{/if}
				</td>
			</tr>
			{/foreach}
		</table>
		{$vnaFenye}
	</div>
	<div class="testResult PIM">
		<table border="0">
			<tr>
				<th>序号</th><th>时间</th><th>测试站</th><th>测试设备</th><th>测试员</th><th>产品型号</th><th>序列号</th><th>工单号</th><th>测试结果</th>
			</tr>
			{counter name="pimcounter" start=$pimCount+1 skip=-1 print=FALSE}
			{foreach from=$pimResultArray key=k item=value name=pimforeach}
				{if $smarty.foreach.pimforeach.index is odd}
				<tr style="background:white">
				{else}
				<tr style="background:#E5ECF9">
				{/if}
					<td>{counter name="pimcounter"}</td>
					<td>{$value['test_time']}</td>
					<td>&nbsp;</td>
					<td>{$value['model']}</td>
					<td>{$value['work_num']}</td>
					<td>{$vnaResultArray[0]["productType"]|default:''}</td>
					<td>
						<a href="{site_url('/packing/detail_pim')}/{$value['id']}" target="_blank">{$value['ser_num']}</a>
					</td>
					<td>{$value['name']}</td>
					<td>
						{if $value['result'] eq 1}
							<span style="color:green;">合格</span>
						{else}
							<span style="color:red;">不合格</span>
						{/if}
					</td>
				</tr>
			{/foreach}
		</table>
		{$pimFenye}
	</div>
</div>
<!--{/block}-->