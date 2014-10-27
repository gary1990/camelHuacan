<!--{extends file='defaultPage.tpl'}-->
<!--{block name=title}-->
<title>{$title}</title>
<!--{/block}-->
<!--{block name=style}-->
<link rel="stylesheet" type="text/css" href="{base_url()}resource/css/chosen.css" />
<style>
	.separate_line{
		height:3px;
	}
	.short_input{
		width:45px;
	}
	.long_input{
		width:150px;
	}
	.addbtn{
		cursor:pointer;
	}
	.delbtn{
		cursor:pointer;
	}
	.per_record_hidden{
		display:none;
	}
	.chzn-container-single{
		vertical-align: middle;
	}
	.producttype{
		width:150px;
	}
	.testitem{
		width:150px;
	}
	.producttypeCondition{
		width:150px;
	}
</style>
<!--{/block}-->
<!--{block name=script}-->
<script type="text/javascript" src="{base_url()}resource/js/chosen.jquery.js"></script>
<script type="text/javascript" src="{base_url()}resource/js/jquery.form.js"></script>
<script type="text/javascript">
	//在当前行下面添加一行
	function add_record(thisid){
		var num = $(".addcount").val();
		var add_td = $(".per_record").html();
		var add_tr = '<tr class="per_record" id="'+num+'">'+add_td+'</tr>';
		add_tr = add_tr.replace(/per_record_hidden_/g,"").replace(/addproducttype/g,'producttype').replace(/addtestitem/g,'testitem');
		add_tr = add_tr.replace(/producttype_/g,'producttype'+num).replace(/testitem_/g,'testitem'+num);
		add_tr = add_tr.replace(/statusfile_/g,'statusfile'+num).replace(/ports_/g,'ports'+num);
		add_tr = add_tr.replace(/channel_/g,'channel'+num).replace(/trace_/g,'trace'+num);
		add_tr = add_tr.replace(/start_/g,'start'+num).replace(/stop_/g,'stop'+num);
		add_tr = add_tr.replace(/mark_/g,'mark'+num).replace(/min_/g,'min'+num);
		add_tr = add_tr.replace(/max_/g,'max'+num).replace(/selfid/g,num);
		$("#"+thisid).after(add_tr);
		$("#"+num+" select").eq(0).chosen();
		$("#"+num+" select").eq(1).chosen();
		$(".addcount").attr("value",parseInt(num)+1);
	}
	//删除当前行
	function del_record(thisid){
		$("#"+thisid).remove();
	}
	
	$(document).ready(function(){
		//可选择、输入、搜索的下拉列表
		$(".producttypeCondition").chosen();
		$(".producttype").chosen();
		$(".testitem").chosen();
		//产品型号下拉列表的判空
		$("body").delegate(".producttype", "change", function(){
			var producttype = $(this).val();
      		if(producttype == "")
      		{
      			alert("产品型号必填");
      		}
      		else
      		{
      			//do noting
      		}
    	});
    	//测试项下拉列表change事件
    	$("body").delegate(".testitem", "change", function(){
			var testitem = $(this).val();
      		if(testitem == "")
      		{
      			alert("测试项必填");
      		}
      		else
      		{
      			//do noting
      		}
    	});
    	//端口数输入框的为整数判断
		$("body").delegate(".ports", "blur", function(){
			var ports = $(this).val();
			//取整后和原来数比较
      		if(parseInt(ports) != ports)
      		{
      			alert("端口数为整数");
      		}
    	});
		//分页事件
		$(".locPage > a").click(function(e) {
			e.preventDefault();
			var url = $("#searchForm").attr('action') + $(this).attr('href');
			$("#searchForm").attr('action', url);
			$("#searchForm").submit();
		});
		//查看按钮点击时，判断页面上是否做了修改
		$(".searchbtn").click(function(e){
			//取得当前记录条数
			var tatolcount = $(".addcount").val();
			//页面内容改变确认框的结果
			var conf;
			for(var i=tatolcount;i >= 1;i--)
			{
				var producttype = $('[name="producttype'+i+'"]').val();
				if(producttype == undefined)
				{
					continue;
				}
				else
				{
					var case1 = $('[name="producttype'+i+'"]').val() == $('[name="producttype'+i+'"]').next().next().val();
					var case2 = $('[name="testitem'+i+'"]').val() == $('[name="testitem'+i+'"]').next().next().val();
					var case3 = $('[name="statusfile'+i+'"]').val() == $('[name="statusfile'+i+'"]').next().val();
					var case4 = $('[name="ports'+i+'"]').val() == $('[name="ports'+i+'"]').next().val();
					var changed = case1 && case2 && case3 && case4;
					if(changed)
					{
						//do noting
					}
					else
					{
						var msg = "有修改尚未保存，确定不保存当前修改？";
						conf = confirm(msg);
						break;
					}
				}
			}
			if(conf == undefined)
			{
				//do noting,start search
			}
			else
			{
				if(conf == true)
				{
					//do noting,start search
				}
				else
				{
					e.preventDefault();
				}
			}
		});
		//导出按钮点击时间
		$(".exportbtn").click(function(){
			var oldurl = $("#searchForm").attr('action');
			var url = oldurl+"/0/30/export";
			$("#searchForm").attr('action', url);
			$("#searchForm").submit();
			$("#searchForm").attr('action', oldurl);
		});
		
		
		//保存按钮点击时判断产品型号，测试项，端口数
		$(".savebtn").click(function(e){
			e.preventDefault();
			//取得当前记录条数
			var tatolcount = $(".addcount").val();
			//产品型号，测试项，端口数判空判空结果。默认为true，防止页面记录全部删除后无法比较结果
			var nullResult = true;
			//产品型号，测试项，端口数判空
			for(var i=tatolcount;i >= 1;i--)
			{
				var producttype = $('[name="producttype'+i+'"]').val();
				if(producttype == undefined)
				{
					continue;
				}
				else
				{
					var case1 = $('[name="producttype'+i+'"]').val() != "";
					var case2 = $('[name="testitem'+i+'"]').val() != "";
					var case3 = $('[name="ports'+i+'"]').val() != "";
					var case4 = $('[name="statusfile'+i+'"]').val() != "";
					var empty = case1 && case2 && case3 && case4;
					if(empty)
					{
						nullResult = empty;
					}
					else
					{
						nullResult = empty;
						alert("产品型号，测试项，端口数，状态文件不为空！");
						break;
					}
				}
			}
			if(nullResult)
			{
				for(var i=tatolcount;i >= 1;i--)
				{
					var producttype = $('[name="producttype'+i+'"]').val();
					if(producttype == undefined)
					{
						continue;
					}
					else
					{
						var producttype = $('[name="producttype'+i+'"]').val();
						$('[name="producttype'+i+'"]').next().next().attr("value",producttype);
						var testitem = $('[name="testitem'+i+'"]').val();
						$('[name="testitem'+i+'"]').next().next().attr("value",testitem);
						var statusfile = $('[name="statusfile'+i+'"]').val();
						$('[name="statusfile'+i+'"]').next().attr("value",statusfile);
						var ports = $('[name="ports'+i+'"]').val();
						$('[name="ports'+i+'"]').next().attr("value",ports);
					}
				}
				var options = { 
			        success:function (res){ 
			        		$(".ids").attr("value",res);
			        		alert("保存成功！"); 
			        	}
			    }; 
				$('#locForm').ajaxSubmit(options);
			}
		});
		
		//导入按钮点击事件，触发“浏览”文件输入框点击事件
		$(".importbtn").click(function(e){
			
			e.preventDefault();
			var options = { 
			        success:function (res){ 
			        		alert(res); 
			        	}
			    }; 
			$("#importForm").ajaxSubmit(options);
			
			//$("#importForm").submit();
		});
	});
</script>
<!--{block name=subScript}-->
<!--{/block}-->
<!--{/block}-->
<!--{block name=body}-->
<div class="span-64 last subitems">
	<div class="prepend-1 span-63">
		<form method="post" id="searchForm" action="{site_url()}/producttestcase/index">
			产品型号：
			{html_options name=producttypesearch class="producttypeCondition" options=$producttypeSearch selected=$smarty.post.producttypesearch|default:''}
			&nbsp;&nbsp;&nbsp;
			<input class="searchbtn" type="submit" value="查看" />
			&nbsp;&nbsp;&nbsp;
		</form>
		<div style="text-align: right;">
			<form id="importForm" action="{site_url()}/producttestcase/importCsvFile" method="post" enctype="multipart/form-data">
				<input type="file" name="file" id="file"/>
				<input class="importbtn" type="submit" value="导入"/>
				<input class="exportbtn" type="button" value="导出" />
			</form>
		</div>
		<hr class="separate_line">
		<div>
			<div>
				<form name="locForm" id="locForm" method="post" action="{site_url('producttestcase/del_ins/')}">
					<table>
						<tr>
							<th>产品型号</th>
							<th>测试项</th>
							<th>状态文件</th>
							<th width="45px">端口数</th>
							<th>&nbsp;</th>
							<th>&nbsp;</th>
						</tr>
						<tr class="per_record per_record_hidden">
							<td>{html_options class="addproducttype" name=producttype_ options=$producttype}<input type="hidden" class="short_input" value=""/></td>
							<td>{html_options class="addtestitem" name=testitem_ options=$testitem}<input type="hidden" class="short_input" value=""/></td>
							<td><input class="long_input statusfile" name="statusfile_" type="text"/><input type="hidden" class="short_input" value=""/></td>
							<td><input class="short_input ports" name="ports_" maxlength="4" type="text" /><input type="hidden" class="short_input" value=""/></td>
							<td><span class="addbtn" onclick="add_record(selfid)">+</span></td>
							<td><span class="delbtn" onclick="del_record(selfid)">-</span></td>
						</tr>
						{if count($testcaseArr) eq 0}
							<tr class="per_record" id="1">
								<td>{html_options name=producttype1 class=producttype options=$producttype}<input type="hidden" class="short_input" value=""/></td>
								<td>{html_options name=testitem1 class=testitem options=$testitem}<input type="hidden" class="short_input" value=""/></td>
								<td><input class="long_input statusfile" name="statusfile1" type="text" /><input type="hidden" class="short_input" value=""/></td>
								<td><input class="short_input ports" name="ports1" maxlength="4" type="text" /><input type="hidden" class="short_input" value=""/></td>
								<td><span class="addbtn" onclick="add_record(1)">+</span></td>
								<td><span class="delbtn">-</span></td>
							</tr>
						{else}
							{foreach from=$testcaseArr key=k item=value}
								<tr class="per_record" id="{$k+1}">
									<td>{html_options name="producttype{$k+1}" class=producttype options=$producttype selected=$value["producttype"]|default:""}<input type="hidden" class="short_input" value="{$value["producttype"]|default:""}"/></td>
									<td>{html_options name="testitem{$k+1}" class=testitem options=$testitem selected=$value["testitem"]|default:""}<input type="hidden" class="short_input" value="{$value["testitem"]|default:""}"/></td>
									<td><input class="long_input statusfile" name="statusfile{$k+1}" type="text" value="{$value["statefile"]|default:""}" /><input type="hidden" class="short_input" value="{$value["statefile"]|default:""}"/></td>
									<td><input class="short_input ports" name="ports{$k+1}" maxlength="4" type="text" value="{$value["ports"]|default:""}" /><input type="hidden" class="short_input" value="{$value["ports"]|default:""}"/></td>
									<td><span class="addbtn" onclick="add_record({$k+1})">+</span></td>
									<td><span class="delbtn" onclick="del_record({$k+1})">-</span></td>
								</tr>
							{/foreach}
						{/if}
					</table>
					{$CI->pagination->create_links()}
					<div style="text-align: right;">
						<input class="savebtn" type="submit" value="保存"/>
					</div>
					<input name="tatolcount" class="tatolcount" type="hidden" value="{$count}" />
					<input name="addcount" class="addcount" type="hidden" value="{$count+2}" />
					<input name="ids" class="ids" type="hidden" value="{$idStr}" />
				</form>
			</div>
		</div>
	</div>
</div>
<!--{/block}-->
