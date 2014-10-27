<!--{extends file='userPage.tpl'}-->
<!--{block name=title}-->
<title>包装记录</title>
<!--{/block}-->
<!--{block name=style}-->
<link rel="stylesheet" type="text/css" href="{base_url()}resource/css/ui.datepicker.css" />
<link rel="stylesheet" type="text/css" href="{base_url()}resource/css/chosen.css" />
<style type="text/css">
	.span-block
	{
		display:inline-block;
		width:75px;
	}
	.span-withmargin
	{
		margin-left:30px;
	}
	.packcondition
	{
		vertical-align:middle;
	}
	table
	{
		border-collapse: collapse;
	}
	th,td
	{
		border-bottom:1px solid #DDDDDD;
	}
	.packingConditionhtml_options
	{
		width:155px;
	}
	.packingtime
	{
		width:80px;
	}
	.seprateline
	{
		height:5px;
		margin:1em 0 1em 0;
	}
	.packingrecord
	{
		display:inline-block;
		background:blue;
		font-weight: bold;
	}
	
	.testitem
	{
		border:1px solid black;
		width:300px;
		padding-left:10px;
		position:absolute;
		top: 50%;
    	left: 40%;
    	background-color:white;
    	display:none;
    	z-index: 10000;
	}
	
 	.mask {
        background-color:#C7EDCC;
        position:absolute;
        top:0px;
        left:0px;
        filter: Alpha(Opacity=20);
    }
    
    .testresult{
    	width:100px;
    }
    
    .datepicker_header{
		width:224px;
	}
	
	.chzn-container-single{
		vertical-align: middle;
	}
</style>
<!--{/block}-->
<!--{block name=script}-->
<script type="text/javascript" src="{base_url()}resource/js/calendar/ui.datepicker.js"></script>
<script type="text/javascript" src="{base_url()}resource/js/calendar/ui.datepicker-zh-CN.js"></script>
<script type="text/javascript" src="{base_url()}resource/js/jquery.mulitselector.js"></script>
<script type="text/javascript" src="{base_url()}resource/js/chosen.jquery.js"></script>
<script src="http://malsup.github.com/jquery.form.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		//可输入选择的下拉列表
		$(".packer").chosen();
		//分页事件
		$(".locPage > a").click(function(e) {
			e.preventDefault();
			var url = $("#locForm").attr('action') + $(this).attr('href');
			$("#locForm").attr('action', url);
			$("#locForm").submit();
		});
		//点击导出按钮时事件
		$(".export").click(function(){
			var timefrom1 = $(".locLong1").val();
			if(timefrom1 == "")
			{
				timefrom1 = "1900-01-01";
			}
			var timefrom2 = $(".timeFrom2").val();
			if(timefrom2 == "")
			{
				timefrom2 = "00";
			}
			var timefrom3 = $(".timeFrom3").val();
			if(timefrom3 == "")
			{
				timefrom3 = "00";
			}
			var timeto1 = $(".locLong2").val();
			if(timeto1 == "")
			{
				timeto1 = "2999-01-01";
			}
			var timeto2 = $(".timeTo2").val();
			if(timeto2 == "")
			{
				timeto2 = "00";
			}
			var timeto3 = $(".timeTo3").val();
			if(timeto3 == "")
			{
				timeto3 = "00";
			}
			var timefrom = timefrom1+" "+timefrom2+":"+timefrom3;
			var timeto = timeto1+" "+timeto2+":"+timeto3;
			var box = $(".packbox").val();
			if(box == "")
			{
				box = "未设置";
			}
			var sn = $(".productsn").val();
			if(sn == "")
			{
				sn = "未设置";
			}
			var type = $(".producttype").val();
			if(type == "")
			{
				type = "未设置";
			}
			var ordernum = $(".ordernum").val();
			if(ordernum == "")
			{
				ordernum = "未设置";
			}
			var packer = $(".packer").val();
			if(packer == "")
			{
				packer = "未设置";
			}
			var testresult = $(".testresult").val();
			if(testresult == "")
			{
				testresult = "未设置";
			}
			var totalcount = $(".totalcount").val();
			var msg = "即将按如下条件导出报告：\r\r";
			msg += "\t1.时间段："+timefrom+"至"+timeto;
			msg += "\t\r\t2.包装箱号："+box;
			msg += "\t\r\t3.包装员："+packer;
			msg += "\t\r\t4.产品型号："+type;
			msg += "\t\r\t5.产品序列号："+sn;
			msg += "\t\r\t6.订单号："+ordernum;
			msg += "\t\r\t7.复检结果："+testresult;
			msg += "\r\r"+"共计"+totalcount+"条记录，确认导出？";
			var conf = confirm(msg);
			if(conf == true)
			{
				/*
				//调用mulitselector插件
				var data = 
				[
					{ id: "10",name: "{$count}"}
				];
				
				$(".locLong2").mulitselector({
					title:"请选择要导出的测试项",
					data:data
				});
				*/
				
				var div_obj = $("#"+"div_id");
		        var windowWidth = document.body.clientWidth;       
		        var windowHeight = document.body.clientHeight;  
		        var popupHeight = div_obj.height();       
		        var popupWidth = div_obj.width();    
		        //添加并显示遮罩层   
		        $("<div id='mask'></div>").addClass("mask")
		                                  .width(windowWidth)   
		                                  .height(windowHeight)
		                                  .appendTo("body");
		        $(".testitem").show();
			}
			else
			{
				//do nothing
			}
		});	
		//导出时测试项选择框的确定按钮
		$(".expBtn").click(function(){
			$(this).parent().parent().hide();
			$("#mask").remove();
			
			var u = $(".baseurl").val()+"index.php/packing/export";
			var options = {
				url:u,
				type:'POST'
			};
			//$("#locForm").ajaxSubmit(options);
			$("#locForm").attr("action",u);
			$("#locForm").submit();
			
		});
		//导出时测试项选择框的取消按钮
		$(".cancelBtn").click(function(){
			$(this).parent().parent().hide();
			$("#mask").remove();
		});
		$(".search").click(function(){
			var u = $(".baseurl").val()+"index.php/packing/index";
			$("#locForm").attr("action",u);
		});
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
<!--{/block}-->
<!--{block name=body}-->
<div class="prepend-1 span-63">
	<form name="locForm" id="locForm" method="post" action="{site_url('packing/index')}">
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
		<div class="packcondition">
			<span class="span-block"> 包装箱: </span>
			<input name="packbox" class="packbox" type="text" value="{$smarty.post.packbox|default:''}" />
			<span class="span-block span-withmargin"> 产品序列号: </span>
			<input name="productsn" class="productsn" type="text" value="{$smarty.post.productsn|default:''}" />
			<span class="span-block span-withmargin"> 产品型号: </span>
			<input name="producttype" class="producttype" type="text" value="{$smarty.post.producttype|default:''}" />
		</div>
		<div class="packcondition">
			<span class="span-block"> 订单号: </span>
			<input name="ordernum" class="ordernum" type="text" value="{$smarty.post.ordernum|default:''}" />
			<span class="span-block span-withmargin"> 包装员: </span>
			{html_options name=packer options=$packer class="packingConditionhtml_options packer" selected=$smarty.post.packer|default:''}
			<span class="span-block span-withmargin"> 复检结果: </span>
			{html_options name=testresult options=$testresult class="packingConditionhtml_options testresult" selected=$smarty.post.testresult|default:''}
			<input class="search" type="submit"  value="查询"/>
			<input class="export" type="button" value="导出报告"/>
		</div>
		<div class="testitem">
			<div style="text-align:left">请选择要导出的测试项：</div>
			<br/>
			<div style="padding-left:70px;">
				{counter start=0 skip=1 print=false}
				{if count($testItemArray) != 0}
					{foreach from=$testItemArray item=value}
						<input name="testitem{counter}" type="checkbox" value="{$value['id']}"/>
						<label>{$value['name']}</label>
						<br>
					{/foreach}
					<input name="testitempim" type="checkbox" value="pim"/>
					<label>PIM</label>
				{else}
				{/if}
			</div>
			<div style="text-align: right;">
				<input class="expBtn" type="button" value="确认"/>
				<input class="cancelBtn" type="button" value="取消"/>
			</div>
		</div>
		<input type="hidden" name=testitemcount class="testitemcount" value="{$testitemcount}"/>
	</form>
	<hr class="seprateline">
	<span class="packingrecord">包装记录</span>
	<hr>
	<table>
		<tr>
			<th>序号</th>
			<th class="packingtime">时间</th>
			<th>包装箱</th>
			<th>产品序列号</th>
			<th>产品型号</th>
			<th>订单号</th>
			<th>包装员</th>
			<th>复检结果</th>
		</tr>
		{counter start=$count+1 skip=-1 print=false}
		{foreach from=$packingResultArray key=k item=value name=packforeach}
			{if $smarty.foreach.packforeach.index is odd}
				<tr style="background:white">
			{else}
				<tr style="background:#E5ECF9">
			{/if}
				<td>{counter}</td>
				<td>{$value['packingtime']}</td>
				<td>{$value['boxsn']}</td>
				<td><a href="{site_url('/packing/detail')}/{$value['id']}" target="_blank">{$value['productsn']}</a></td>
				<td>{$value['name']}</td>
				<td>{$value['ordernum']}</td>
				<td>{$value['packername']}</td>
				<td>
					{if $value['result'] eq 'PASS'}
						<span style="background-color: #66FF66">合格</span>
					{elseif $value['result'] eq 'FAIL'}
						<span style="background-color: #FF3300">不合格</span>
					{else}
						<span style="background-color: yellow">未测试</span>
					{/if}
				</td>
			</tr>	
		{/foreach}
	</table>
	{$CI->pagination->create_links()}
	<input type="hidden" class="baseurl" value="{base_url()}"/>
	<input type="hidden" class="totalcount" value="{$totalcount}"/>
</div>
<!--{/block}-->