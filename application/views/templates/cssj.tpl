<!--{extends file='userPage.tpl'}-->
<!--{block name=title}-->
<title>测试数据查询</title>
<!--{/block}-->
<!--{block name=style}-->
<style type="text/css" media="screen">
	.testImg {
		width: 300px;
		height: 200px;
	}
	.locPass {
		color: black;
		background-color: green;
	}
	.locFail {
		color: black;
		background-color: red;
	}
	.locLong {
		width: 40px;
	}
	.locShort {
		width: 20px;
	}
</style>
<!--{/block}-->
<!--{block name=subScript}-->
<script>
	$(document).ready(function()
	{
		$(".detail").click(function(e)
		{
			e.preventDefault();
			$('#imgDiv').html('');
			var tmpStr = $(this).attr('href');
			var tmpIndex = tmpStr.substring((tmpStr.lastIndexOf('/') + 1));
			$("#testItemDiv").load("{site_url('cssj/getTestItemResult')}" + "/" + tmpIndex, function(responseText, textStatus, XMLHttpRequest)
			{
				if (textStatus == 'success')
				{
				}
			});
		});
		//处理分页连接点击事件
		$(".locPage > a").click(function(e) {
			e.preventDefault();
			var url = $("#locForm").attr('action') + $(this).attr('href');
			$("#locForm").attr('action', url);
			$("#locForm").submit();
		});
	}); 
</script>
<script type="text/javascript">
	function search_or_export(e)
	{
		if(e==0)
		{
			var url= "{site_url('/cssj/index/0')}";
			document.locForm.action = url;
		}
		else
		{
			var url= "{site_url('/cssj/index/1')}";
			document.locForm.action = url;
		}
	}
</script>
<!--{/block}-->
<!--{block name=subBody}-->
<div class="prepend-1 span-31">
	<form name="locForm" id="locForm" method="post" action="{site_url('cssj/index')}">
		<div class="span-5">
			时间:
		</div>
		<input class="locLong" type="text" name="timeFrom1" value="{$smarty.post.timeFrom1|default:''}"/>
		年
		<input class="locShort" type="text" name="timeFrom2" value="{$smarty.post.timeFrom2|default:''}"/>
		月
		<input class="locShort" type="text" name="timeFrom3" value="{$smarty.post.timeFrom3|default:''}"/>
		日
		<input class="locShort" type="text" name="timeFrom4" value="{$smarty.post.timeFrom4|default:''}"/>
		时
		<input class="locShort" type="text" name="timeFrom5" value="{$smarty.post.timeFrom5|default:''}"/>
		分
		<br>
		<div class="span-5">
			至:
		</div>
		<input class="locLong" type="text" name="timeTo1" value="{$smarty.post.timeTo1|default:''}"/>
		年
		<input class="locShort" type="text" name="timeTo2" value="{$smarty.post.timeTo2|default:''}"/>
		月
		<input class="locShort" type="text" name="timeTo3" value="{$smarty.post.timeTo3|default:''}"/>
		日
		<input class="locShort" type="text" name="timeTo4" value="{$smarty.post.timeTo4|default:''}"/>
		时
		<input class="locShort" type="text" name="timeTo5" value="{$smarty.post.timeTo5|default:''}"/>
		分
		<br>
		<div class="span-5">
			测试结果:
		</div>
		{html_options name=testResult options=$testResultList selected=$smarty.post.testResult|default:''}
		<br>
		<div class="span-5">
			测试站:
		</div>
		<input type="text" name="testStationName" value="{$smarty.post.testStationName|default:''}"/>
		<br>
		<div class="span-5">
			型号:
		</div>
		<input type="text" name="productTypeName" value="{$smarty.post.productTypeName|default:''}"/>
		<br>
		<div class="span-5">
			工号:
		</div>
		<input type="text" name="employeeId" value="{$smarty.post.employeeId|default:''}"/>
		<br>
		<div class="span-5">
			序列号:
		</div>
		<input type="text" name="sn" value="{$smarty.post.sn|default:''}"/>
		<br>
		<input type="submit" value="查找" onclick="search_or_export(0)" />
		<input type="submit" value="导出" onclick="search_or_export(1)" />
	</form>
</div>
<div id="imgDiv" class="prepend-1 span-31 last">
</div>
<hr>
<div class="prepend-1 span-63 last">
	测试数据查询
</div>
<div class="prepend-1 span-46">
	<div class="span-46">
		<div class="span-2">
			序号
		</div>
		<div class="span-10">
			时间
		</div>
		<div class="span-5">
			测试站
		</div>
		<div class="span-2">
			工号
		</div>
		<div class="span-10">
			型号
		</div>
		<div class="span-8">
			序列号
		</div>
		<div class="span-4">
			测试结果
		</div>
		<div class="span-2">
			详情
		</div>
	</div>
	{foreach $productTestList as $productTest}
	<div class="span-46">
		<div class="span-2">
			{$productTest['id']}
		</div>
		<div class="span-10">
			{$productTest['testTime']}
		</div>
		<div class="span-5">
			{$productTest['testStationName']}
		</div>
		<div class="span-2">
			{$productTest['employeeId']}
		</div>
		<div class="span-10">
			{$productTest['productTypeName']}
		</div>
		<div class="span-8">
			{$productTest['sn']}
		</div>
		<div class="span-4">
			{if $productTest['result'] == 0} <span class="locFail">Fail</span>
			{elseif $productTest['result'] == 1} <span class="locPass">Pass</span>
			{/if}
		</div>
		<div class="span-2">
			<a class="detail" href="{$productTest['id']}">详情</a>
		</div>
	</div>
	{/foreach}
	{$CI->pagination->create_links()}
</div>
<div id="testItemDiv" class="prepend-1 span-16 last">
</div>
<!--{/block}-->