<!--{extends file='userPage.tpl'}-->
<!--{block name=title}-->
<title>PIM测试数据查询</title>
<!--{/block}-->
<!--{block name=style}-->
<style type="text/css">
	.pimList {
		width: 615px;
		float: left;
		font-size:11px;
	}
	.view {
		cursor: pointer;
		color: blue;
	}
	.pimTestDetail 
	{
		float:left;
	}
</style>
<!--{/block}-->
<!--{block name=subBody}-->
<!--{block name=subScript}-->
<script type="text/javascript">
	$(document).ready(function()
	{
		$.ajaxSetup(
		{
			cache : false
		});
		$(".view").click(function()
		{
			var pimsernum = $(this).attr("id");
			var url = "{site_url('pim/getPimData')}" + "/" + pimsernum;
			$("#pimTestDetailDiv").load(url);
		});
	}); 
</script>
<!--{/block}-->
<hr/>
<div>
	<div class="pimList">
		<table cellspacing="0" cellpadding="0" border="0">
			<tr>
				<th>序号</th><th>测试时间</th><th>型号</th><th>序列号</th><th>工号</th><th>工单号</th><th>测试值</th>
			</tr>
			{foreach from=$pimListArray item=pimList}
			<tr>
				<td>{$pimList['id']}</td><td>{$pimList['test_time']}</td><td>{$pimList['model']}</td><td>{$pimList['ser_num']}</td><td>{$pimList['work_num']}</td><td>{$pimList['name']}</td><td>
				<div class="view" id="{$pimList['id']}">
					查看
				</div></td>
			</tr>
			{/foreach}
		</table>
		{$fenye}
	</div>
	<div id="pimTestDetailDiv" class="pimTestDetail">
	</div>
</div>
<!--{/block}-->
