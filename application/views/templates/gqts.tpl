<!--{extends file='defaultPage.tpl'}-->
<!--{block name=title}-->
<title>质量追溯</title>
<!--{/block}-->
<!--{block name=style}-->
<link rel="stylesheet" type="text/css" href="{base_url()}resource/css/ui.datepicker.css" />
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
		width: 300px;
		height: 30px;
		float: left;
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
</style>
<!--{/block}-->
<!--{block name=script}-->
<script type="text/javascript" src="{base_url()}resource/js/calendar/ui.datepicker.js"></script>
<script type="text/javascript" src="{base_url()}resource/js/calendar/ui.datepicker-zh-CN.js"></script>
<script src="http://malsup.github.com/jquery.form.js"></script> 
<script type="text/javascript">
	$(function(){
		var current_item = $(".current_item").attr("value");
		$(".gqts_item").css("background-color","white");
		$("#"+current_item).css("background-color","#DDDDDD");
		$(".testResult").hide();
		$("."+current_item).show();
		
		$(".gqts_item").click(function(){
			$(".gqts_item").css("background-color","white");
			$(this).css("background-color","#DDDDDD");
			var current_item = $(this).attr("id");
			$(".current_item").attr("value",current_item);
			$(".testResult").hide();
			$("."+current_item).show();
		});
		$(".bt").click(function(e){
			var current_action = $(this).attr("id");
			$(".current_action").attr("value",current_action);
		});
		$(".page").click(function(e){
			e.preventDefault();
			var current_page = $(this).attr("href");
			$(".current_page").attr("value",current_page);
			$(".current_action").attr("value","search");
			$("#locForm").submit();
		});
	});
	jQuery(function($)
	{
		$('#date_from').datepicker({
			yearRange: '2012:2112',
			showOn: 'both',
			buttonImage: '{base_url()}resource/img/calendar.gif',
			buttonImageOnly: true,
			showButtonPanel: true
		});
	});
	jQuery(function($)
	{
		$('#date_to').datepicker({
			yearRange: '2012:2112',
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
	<div class="span-50">
		<form name="locForm" id="locForm" method="post" action="{site_url('gqts')}">
			<div class="condition">
				<span class="span-5 spanStyle"> 时间: </span>
				<input id="date_from" class="locLong" type="text" name="timeFrom1" value="{$smarty.post.timeFrom1|default:''}"/>
				{html_options name=timeFrom2 options=$hourList selected=$smarty.post.timeFrom2|default:''}
				:
				{html_options name=timeFrom3 options=$minuteList selected=$smarty.post.timeFrom3|default:''}
				至:
				<input id="date_to" class="locLong" type="text" name="timeTo1" value="{$smarty.post.timeTo1|default:''}"/>
				{html_options name=timeTo2 options=$hourList selected=$smarty.post.timeTo2|default:''}
				:
				{html_options name=timeTo3 options=$minuteList selected=$smarty.post.timeTo3|default:''}
			</div>
			<div class="condition">
				<div class="subCondition">
					<div class="span-5 spanStyle">
						测试结果:
					</div>
					{html_options name=testResult options=$testResultList selected=$smarty.post.testResult|default:''}
				</div>
				<div class="subCondition">
					<div class="span-5 spanStyle">
						序列号:
					</div>
					<input name="sn" value="{$smarty.post.sn|default:''}" type="text"/>
				</div>
			</div>
			<div class="condition">
				<div class="subCondition">
					<div class="span-5 spanStyle">
						测试站:
					</div>
					<input name="teststation" value="{$smarty.post.teststation|default:''}" type="text"/>
				</div>
				<div class="subCondition">
					<div class="span-5 spanStyle">
						工单号:
					</div>
					<input name="labelnum" value="{$smarty.post.labelnum|default:''}" type="text"/>
				</div>
			</div>
			<div class="condition">
				<div class="subCondition">
					<div class="span-5 spanStyle">
						型号:
					</div>
					<input name="producttype" value="{$smarty.post.producttype|default:''}" type="text"/>
				</div>
				<div class="subCondition">
					<div class="span-5 spanStyle">
						订单号:
					</div>
					<input name="ordernum" value="{$smarty.post.ordernum|default:''}" type="text"/>
				</div>
			</div>
			<div class="condition">
				<div class="subCondition">
					<div class="span-5 spanStyle">
						测试员:
					</div>
					<input name="tester" value="{$smarty.post.tester|default:''}" type="text"/>
				</div>
				<div class="subCondition">
					<input class="bt" id="search" type="submit" value="查询"/>
					<input name="current_item" class="current_item" value="{$smarty.post.current_item|default:'VNA'}" type="text"/>
					<input name="current_page" class="current_page" value="{$smarty.post.current_page|default:'1'}" type="text"/>
				</div>
			</div>
		</form>
	</div>
</div>
<div class="gqts_item gqts_VNA" id="VNA">
	VNA
</div>
<div class="gqts_item" id="PIM">
	PIM
</div>
<hr/>
<div class="testResult VNA">
	<table border="0">
		<tr><th>序号</th><th>时间</th><th>测试站</th><th>测试员</th><th>型号</th><th>序列号</th><th>工单号</th><th>订单号</th></tr>
		{foreach from=$vnaResultArray item=value}
		<tr>
			<td>{$value['id']}</td>
			<td>{$value['testTime']}</td>
			<td>{$value['testStation']}</td>
			<td>{$value['tester']}</td>
			<td>{$value['productType']}</td>
			<td><a href="{site_url('/packing/detail')}/{$value['sn']}" target="_blank">{$value['sn']}</a></td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		{/foreach}
	</table>
	{$vnaFenye}
</div>
<div class="testResult PIM">
	<table border="0">
		<tr>
			<th>序号</th><th>时间</th><th>测试站</th><th>测试员</th><th>型号</th><th>序列号</th><th>工单号</th><th>订单号</th>
		</tr>
		{foreach from=$pimResultArray item=value}
			<tr>
				<td>{$value['id']}</td>
				<td>{$value['test_time']}</td>
				<td>&nbsp;</td>
				<td>{$value['work_num']}</td>
				<td>{$value['model']}</td>
				<td><a href="{site_url('/packing/detail')}/{$value['ser_num']}" target="_blank">{$value['ser_num']}</a></td>
				<td>{$value['name']}</td>
				<td>&nbsp;</td>
			</tr>
		{/foreach}
	</table>
	{$pimFenye}
</div>
<!--{/block}-->