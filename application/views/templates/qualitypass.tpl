<!--{extends file='defaultPage.tpl'}-->
<!--{block name=title}-->
<title>{$title}</title>
<!--{/block}-->
<!--{block name=style}-->
<link rel="stylesheet" type="text/css" href="{base_url()}resource/css/ui.datepicker.css" />
<link rel="stylesheet" type="text/css" href="{base_url()}resource/css/chosen.css" />
<style>
	.top_title
	{
		width:900px;
		font-size:20px;
		font-weight:bold;
		text-align:center;
	}
	.teststation,.producttype
	{
		width:150px;
	}
	.parts
	{
		border: 1px solid #DDDDDD;
		width: 75px;
		height: 20px;
		margin-top: 10px;
		text-align: center;
		color:#000000;
		font-weight:900;
		cursor:pointer;
	}
	.default_part
	{
		background-color:#0066CC;
	}
	.default_result
	{
		display:none;
	}
	.table
	{
		border-collapse: collapse;
	}
	th,td
	{
		border-top:1px solid #DDDDDD;
	}
</style>
<!--{/block}-->
<!--{block name=script}-->
<script type="text/javascript" src="{base_url()}resource/js/calendar/ui.datepicker.js"></script>
<script type="text/javascript" src="{base_url()}resource/js/calendar/ui.datepicker-zh-CN.js"></script>
<script type="text/javascript" src="{base_url()}resource/js/chosen.jquery.js"></script>
<script type="text/javascript"> 
	$(document).ready(function(){
		$(".teststation").chosen();
		$(".producttype").chosen();
		$(".locPage1 > a").click(function(e){
			e.preventDefault();
			var url = $("#locForm").attr('action') + $(this).attr('href');
			$("#locForm").attr('action', url);
			$("#locForm").submit();
		});
		$(".locPage2 > a").click(function(e){
			e.preventDefault();
			var url = $("#locForm").attr('action') + $(this).attr('href');
			$("#locForm").attr('action', url);
			$("#locForm").submit();
		});
		$(".saveBtn1").click(function(e){
			e.preventDefault();
			$(".testtime").attr("value",$("#date").val());
			$(".tn").attr("value",$(".teststation").val());
			$(".pe").attr("value",$(".producttype").val());
			var url = $(".saveForm1").attr('action');
			$(".saveForm1").submit();
		});
		$(".saveBtn2").click(function(e){
			e.preventDefault();
			$(".testtime").attr("value",$("#date").val());
			$(".tn").attr("value",$(".teststation").val());
			$(".pe").attr("value",$(".producttype").val());
			var url = $(".saveForm2").attr('action');
			$(".saveForm2").submit();
		});
		//两部分的切换
		$(".parts").click(function(){
			$(".parts").css("background-color","white");
			$(this).css("background-color","#0066CC");
			$(".passStatus").attr("value",$(this).attr("id"));
			$(".result_div").hide();
			$("."+$(this).attr("id")).show();
			if($(this).attr("id") == "unpass")
			{
				$(".top_title").html("待放行产品记录");
			}
			else
			{
				$(".top_title").html("已放行产品记录");
			}
		});
	});
	
	jQuery(function($)
	{
		$('#date').datepicker({
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
<div class="span-64 last subitems">
	<div class="prepend-1 top_title">
		{if $passStatus eq 'unpass'}
			待放行产品记录
		{else}
			已放行产品记录
		{/if}
	</div>
	<div>
		<div>筛选条件</div>
		<div>
			<form id="locForm" method="post" action="{site_url('qualitypass/index')}">
				<div style="margin-bottom:5px;">
					<div style="margin-top:3px;margin-right: 50px;float:left;">
						日期：<input id="date" name="date" value="{$smarty.post.date|default:$conditionTime}" type="text"/>
					</div>
					<div style="margin-right: 50px;float:left;">
						测试站：
						{html_options class=teststation name=teststation options=$teststation selected=$smarty.post.teststation|default:''}
					</div>
					<div style="margin-right: 50px;">
						产品型号：
						{html_options class=producttype name=producttype options=$producttype selected=$smarty.post.producttype|default:''}
					</div>
				</div>
				<input class="passStatus" type="hidden" name="passstatus" value="{$passStatus|default:'unpass'}" />
				<div style="text-align:right;margin-right:23%;">
					<input type="submit" value="查询"/>
				</div>
			</form>
		</div>
	</div>
	<div>
		<div style="height:32px;">
			<div id="unpass" class="parts {if $passStatus eq 'unpass'}default_part{/if}" style="float:left;">待放行产品</div>
			<div id="passed" class="parts {if $passStatus neq 'unpass'}default_part{/if}" style="float:left;">已放行产品</div>
		</div>
		<div class="result_div unpass {if $passStatus neq 'unpass'}default_result{/if}">
			<form method="post" class="saveForm1" action="{site_url('qualitypass/savequalitypass')}">
				<table>
					<tr>
						<th>序号</th>
						<th>时间</th>
						<th>测试站</th>
						<th>产品型号</th>
						<th>序列号</th>
						<th>测试结果</th>
						<th>&nbsp;</th>
						<th style="border-left:1px solid #DDDDDD;">转为合格</th>
						<th>责任人</th>
						<th>备注</th>
					</tr>
					{counter start=$totalcount+1 skip=-1 name=count print=false}
					{foreach from=$qualitypassArr key=k item=value}
						<tr style="background:white">
							<td>{counter name=count}</td>
							<td>{$value['testTime']|default:''}</td>
							<td>{$value['teststaion']|default:''}</td>
							<td>{$value['producttype']|default:''}</td>
							<td>{$value['sn']|default:''}</td>
							<td>
								{if $value['result'] eq 1}
									<span style="color:green;">合格</span>
								{else}
									<span style="color:red;">不合格</span>
								{/if}
							</td>
							<td>
								<a href="{site_url('/packing/detail_vna')}/{$value['id']}" target="_blank">详情</a>
							</td>
							<td style="border-left: 1px solid #DDDDDD;">
								<input name="change{$k+1}" type="checkbox" value="{$value['id']}"/>
							</td>
							<td>
								{if $value['responsible_person'] eq NULL}
									{$CI->session->userdata('username')}
								{else}
									{$value['responsible_person']}
								{/if}
							</td>
							<td>
								<input name="remark{$k+1}" type="text" value="{$value['remark']}"/>
								<input name="id{$k+1}" type="hidden" value="{$value['id']}"/>
							</td>
						</tr>
					{/foreach}
				</table>
				<input type="hidden" name="totalrecord" value="{count($qualitypassArr)}"/>
				<input type="hidden" class="testtime" name="testtime" value=""/>
				<input type="hidden" class="tn" name="teststation" value=""/>
				<input type="hidden" class="pe" name="producttype" value=""/>
				{$pagenation1}
				<div style="text-align:right;">
					<input class="saveBtn1" type="submit" value="保存"/>
				</div>
			</form>
		</div>
		<div class="result_div passed {if $passStatus eq 'unpass'}default_result{/if}">
			<form method="post" class="saveForm2" action="{site_url('qualitypass/savequalityUnpass')}">
				<table>
					<tr>
						<th>序号</th>
						<th>时间</th>
						<th>测试站</th>
						<th>产品型号</th>
						<th>序列号</th>
						<th>测试结果</th>
						<th>&nbsp;</th>
						<th style="border-left:1px solid #DDDDDD;">不合格</th>
						<th>责任人</th>
						<th>备注</th>
					</tr>
					{counter start=$passedTotalcount+1 skip=-1 name=passedcount print=false}
					{foreach from=$passedArr key=k item=val}
						<tr style="background:white">
							<td>{counter name=passedcount}</td>
							<td>{$val['testTime']|default:''}</td>
							<td>{$val['teststaion']|default:''}</td>
							<td>{$val['producttype']|default:''}</td>
							<td>{$val['sn']|default:''}</td>
							<td>
								{if $val['result'] eq 1}
									<span style="color:green;">合格</span>
								{else}
									<span style="color:red;">不合格</span>
								{/if}
							</td>
							<td>
								<a href="{site_url('/packing/detail_vna')}/{$val['id']}" target="_blank">详情</a>
							</td>
							<td style="border-left: 1px solid #DDDDDD;">
								<input name="change{$k+1}" type="checkbox" value="{$val['id']}"/>
							</td>
							<td>
								{if $val['responsible_person'] eq NULL}
									{$CI->session->userdata('username')}
								{else}
									{$val['responsible_person']}
								{/if}
							</td>
							<td>
								<input name="remark{$k+1}" type="text" value="{$val['remark']}"/>
								<input name="id{$k+1}" type="hidden" value="{$val['id']}"/>
							</td>
						</tr>
					{/foreach}
				</table>
				<input type="hidden" name="totalrecord" value="{count($passedArr)}"/>
				<input type="hidden" class="testtime" name="testtime" value=""/>
				<input type="hidden" class="tn" name="teststation" value=""/>
				<input type="hidden" class="pe" name="producttype" value=""/>
				{$pagenation2}
				<div style="text-align:right;">
					<input class="saveBtn2" type="submit" value="保存"/>
				</div>
			</form>
		</div>
	</div>
</div>
<!--{/block}-->