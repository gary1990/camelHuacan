<!--{extends file='defaultPage.tpl'}-->
<!--{block name=title}-->
<title>{$title}</title>
<!--{/block}-->
<!--{block name=style}-->
<link rel="stylesheet" type="text/css" href="{base_url()}resource/css/ui.datepicker.css" />
<link rel="stylesheet" type="text/css" href="{base_url()}resource/css/chosen.css" />
<style>
	.span-block
	{
		display:inline-block;
		width:75px;
	}
	.subCondition{
		float:left;
		margin-right:30px;
		height:32px;
		line-height: 32px;
	}
	.option_codition{
		width:154px;
	}
	.snInput{
		width: 330px;
	}
	.seprateline
	{
		height:5px;
		margin:1em 0 1em 0;
	}
	.seprateline_short
	{
		height:5px;
		margin:1em 0 1em 0;
		width:94%; 
		position: relative;
		top:25px;
	}
	.advanceserch_span{
		cursor: pointer;
		color: blue;
		text-decoration: underline;
	}
	.item_short{
		width:50px;
	}
	.testitem_condition{
		width:600px;
	}
	tr,td,th{
		border-top:1px solid #DDDDDD;
	}
	td{
		word-break:break-all;
	}
	.testitems{
		display:none;
	}
	.datepicker_oneMonth{
		width:231px;
	}
	.chzn-container-single{
		vertical-align: middle;
	}
</style>
<!--{/block}-->
<!--{block name=script}-->
<script type="text/javascript" src="{base_url()}resource/js/calendar/ui.datepicker.js"></script>
<script type="text/javascript" src="{base_url()}resource/js/calendar/ui.datepicker-zh-CN.js"></script>
<script type="text/javascript" src="{base_url()}resource/js/chosen.jquery.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		//高级搜索点击时
		$('.advanceserch_span').click(function(){
			$(".testitems").toggle("slow");
		});
		//分页事件
		$(".locPage > a").click(function(e) {
			e.preventDefault();
			var url = $("#locForm").attr('action') + $(this).attr('href');
			$("#locForm").attr('action', url);
			$("#locForm").submit();
		});
		$(".ui-autocomplete-input").attr("value","");
		//具有搜索功能的下拉列表
		$(".teststation").chosen();
		$(".equipment").chosen();
		$(".tester").chosen();
		$(".producttype").chosen();
		$(".testitem").chosen();
	});
	//日历插件
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
	//日历插件
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
<!--{block name=subScript}-->
<!--{/block}-->
<!--{/block}-->
<!--{block name=body}-->
<div class="prepend-1 span-60 last subitems">
	<div class="span-60">
		<form name="locForm" id="locForm" method="post" action="{site_url('advancedsearch/index')}">
			<div class="packcondition">
				<span class="span-block"> 时间: </span>
				<input id="date_from" class="locLong locLong1" type="text" name="timeFrom1" value="{$smarty.post.timeFrom1|default:''}"/>
				{html_options name=timeFrom2 class=timeFrom2 options=$hourList selected=$smarty.post.timeFrom2|default:''}
				:
				{html_options name=timeFrom3 class=timeFrom3 options=$minuteList selected=$smarty.post.timeFrom3|default:''}
				至:
				<input id="date_to" class="locLong locLong2" type="text" name="timeTo1" value="{$smarty.post.timeTo1|default:''}"/>
				{html_options name=timeTo2 class=timeTo2 options=$hourList selected=$smarty.post.timeTo2|default:''}
				:
				{html_options name=timeTo3 class=timeTo3 options=$minuteList selected=$smarty.post.timeTo3|default:''}
			</div>
			<div class="condition">
				<div class="subCondition">
					<span class="span-block"> 测试站: </span>
					{html_options class="option_codition teststation" name=teststation options=$teststation selected=$smarty.post.teststation|default:''}
				</div>
				<div class="subCondition">
					<span class="span-block"> 测试设备: </span>
					{html_options class="option_codition equipment" name=equipment options=$equipment selected=$smarty.post.equipment|default:''}
				</div>
				<div class="subCondition">
					<span class="span-block"> 测试员: </span>
					{html_options class="option_codition tester" name=tester options=$tester selected=$smarty.post.tester|default:''}
				</div>
			</div>
			<div class="condition">
				<div class="subCondition">
					<span class="span-block"> 产品型号: </span>
					{html_options class="option_codition producttype" name=producttype options=$producttype selected=$smarty.post.producttype|default:''}
				</div>
				<div class="subCondition">
					<span class="span-block"> 测试结果: </span>
					{html_options class="option_codition result" name=testResult options=$testResultList selected=$smarty.post.testResult|default:''}
				</div>
				<div class="subCondition">
					<span class="span-block"> 盘号: </span>
					<input class="platenum" name="platenum" value="{$smarty.post.platenum|default:''}" type="text"/>
				</div>
			</div>
			<div class="condition">
				<div class="subCondition">
					<span class="span-block"> 工单号: </span>
					<input name="labelnum" value="{$smarty.post.labelnum|default:''}" type="text"/>
				</div>
				<div class="subCondition">
					<span class="span-block"> 序列号: </span>
					<input name="sn" class="snInput" value="{$smarty.post.sn|default:''}" type="text"/>
				</div>
			</div>
			<hr align="left" class="seprateline_short"/>
			<div style="text-align:right;">
				<span class="advanceserch_span">高级搜索</span>
			</div>
			<div class="testitems">
			{for $itemcount=1 to count($testitem)-1}
				<div class="testitem_condition">
					<div class="subCondition">
						<span class="span-block"> 测试项{$itemcount}: </span> 
						{assign var="tmp" value="testitem{$itemcount}"}
						{html_options class="option_codition testitem" name="testitem{$itemcount}" options=$testitem selected=$smarty.post.$tmp|default:''}
					</div>
					<div class="subCondition">
						<span class="span-block"> 范围: </span>
						{assign var="tmp1" value="testitemfrom{$itemcount}"}
						{assign var="tmp2" value="testitemto{$itemcount}"}
						<input class="item_short" type="text" name="testitemfrom{$itemcount}" value="{$smarty.post.$tmp1|default:''}"/>&nbsp;~&nbsp;
						<input class="item_short" type="text" name="testitemto{$itemcount}" value="{$smarty.post.$tmp2|default:''}"/>
					</div>
				</div>
			{/for}
			<input type="hidden" name="testitemcount" value="{count($testitem)-1}"/>
			</div>
			<hr class="seprateline"/>
			<div style="text-align:right;">
				<input type="submit" value="查询"/>
			</div>
		</form>
	</div>
	<div>
		&nbsp;
	</div>
	<div>
		<table>
			<tr>
				<th>序号</th><th>时间</th><th>序列号</th>
				{foreach from=$testitemLimitArr key=k item=value}
				<th>{$k|default:""}</th>
				{/foreach}
			</tr>
			{counter name=advancesearch start=$count+1 skip=-1 print=FALSE}
			{foreach from=$advanceSearchArr item=value}
				<tr style="background:white">
					<td>{counter name="advancesearch"}</td>
					<td>{$value['testTime']}</td>
					<td>
						{if $value['tag1'] eq 2}
							<span style="color:red;">{$value['sn']}</span>
						{else}
							{$value['sn']}
						{/if}
					</td>
					{foreach from=$testitemLimitArr key=k item=val}
					<td>
						{if $val[1] eq "" or $val[2] eq ""}
							{$value[$k]|default:'&nbsp;'}
						{else}
							{if $value[$k] eq ""}
								{$value[$k]|default:'&nbsp;'}
							{else}
								{if $value[$k] >= $val[1] and $val[2] >= $value[$k]}
									{$value[$k]|default:'&nbsp;'}
								{else}
									<span style="color: red;">超出范围</span>
								{/if}
							{/if}
						{/if}
					</td>
					{/foreach}
				</tr>
			{/foreach}
		</table>
		{$CI->pagination->create_links()}
	</div>
</div>
<!--{/block}-->
