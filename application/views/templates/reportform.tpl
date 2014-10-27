<!--{extends file='defaultPage.tpl'}-->
<!--{block name=title}-->
<title>{$title}</title>
<!--{/block}-->
<!--{block name=style}-->
<link rel="stylesheet" type="text/css" href="{base_url()}resource/css/ui.datepicker.css" />
<link rel="stylesheet" type="text/css" href="{base_url()}resource/css/chosen.css" />
<style>
	.seprateline
	{
		height:5px;
		margin:1em 0 1em 0;
	}
	.defaulttitle
	{
		margin-bottom:0px;
	}
	.dataDiv {
		height: 450px;
		margin-bottom:30px;
	}
	.reportform{
		border:1px solid #DDDDDD;
	}
	.factory{
		width:150px;
	}
	.department1,.department2,.lathe,.producttype
	{
		width:150px;
	}
	.chzn-container-single{
		vertical-align:middle;
	}
</style>
<!--{/block}-->
<!--{block name=script}-->
<script src="{base_url()}resource/js/highCharts/highcharts.js"></script>
<script src="{base_url()}resource/js/highCharts/modules/exporting.js"></script>
<script type="text/javascript" src="{base_url()}resource/js/calendar/ui.datepicker.js"></script>
<script type="text/javascript" src="{base_url()}resource/js/calendar/ui.datepicker-zh-CN.js"></script>
<script type="text/javascript" src="{base_url()}resource/js/chosen.jquery.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		$(".producttype").chosen();
		$(".lathe").chosen();
		$(".factory").change(function(){
			var department = $(this).attr('id');
			var siteurl = $(".site_url").val();
			var factory = $(this).val();
			var selectPosition = $("."+department);
			if(factory != "")
			{
        		$('option', selectPosition).remove();
				var factoryId = {
					id:factory
				};
				var ajaxUrl = siteurl+"/reportform/ajax_getDepartment";
				$.ajax({
					url:ajaxUrl,
					type:"POST",
					data:factoryId,
					dataType: "json",
					success:function(data){
						if(data.length > 0){
							selectPosition.append("<option value=''></option>");
					    	$.each(data, function(index, array) {
					        	selectPosition.append("<option value='"+array["id"]+"'>"+array["name"]+"</option>");
        					});	        					
        				}
					},
					error:function(data){
						alert("获取车间ajax请求异常");
					}
				});
			}
			else
			{
				$('option', selectPosition).remove();
				/*{foreach from = $departmentArr key=k item=value}*/
					selectPosition.append('<option value="{$k}">{$value}</option>');
				/*{/foreach}*/
			}
		});
	});
	function setTotaldaypassRate()
	{
		var options =
		{
			chart :
			{
				renderTo : 'totaldaypassRate',
				type : 'spline'
			},
			title :
			{
				text : ''
			},
			xAxis :
			{
				categories : [],
				labels :
				{
					rotation : -45,
					align : 'right',
					style :
					{
						fontSize : '13px',
						fontFamily : 'Verdana, sans-serif'
					}
				}
			},
			yAxis :
			{
				min : 0,
				max : 100,
				title :
				{
					text : ''
				}
			},
			legend :
			{
				enabled : true
			},
			tooltip :
			{
				formatter : function()
				{
					return '<b>通过率</b>:' + this.y;
				}
			},
			series : []
		};
		var series =
		{
			name : 'xxx',
			data : [],
			pointWidth : 14,
			dataLabels :
			{
				enabled : true,
				rotation : -90,
				color : '#FFFFFF',
				align : 'right',
				x : -3,
				y : 10,
				formatter : function()
				{
					return this.y;
				},
				style :
				{
					fontSize : '11px',
					fontFamily : 'Verdana, sans-serif'
				}
			}
		};
		/*{foreach from=$passRateList1 key=k item=value}*/
		options.xAxis.categories.push("");
		series.data.push(/*{$value}*/);
		/*{/foreach}*/
		options.series.push(series);
		chart = new Highcharts.Chart(options);
	}

	function setTotalmonthpassRate()
	{
		var options =
		{
			chart :
			{
				renderTo : 'totalmonthpassRate',
				type : 'line'
			},
			title :
			{
				text : ''
			},
			xAxis :
			{
				categories : [],
				labels :
				{
					rotation : -45,
					align : 'right',
					style :
					{
						fontSize : '13px',
						fontFamily : 'Verdana, sans-serif'
					}
				}
			},
			yAxis :
			{
				min : 0,
				max : 100,
				title :
				{
					text : ''
				}
			},
			legend :
			{
				enabled : true
			},
			tooltip :
			{
				formatter : function()
				{
					return '<b>通过率</b>:' + this.y;
				}
			},
			series : []
		};
		var series =
		{
			name : 'xxx',
			data : [],
			pointWidth : 14,
			dataLabels :
			{
				enabled : true,
				rotation : -90,
				color : '#FFFFFF',
				align : 'right',
				x : -3,
				y : 10,
				formatter : function()
				{
					return this.y;
				},
				style :
				{
					fontSize : '11px',
					fontFamily : 'Verdana, sans-serif'
				}
			}
		};
		/*{foreach from=$passRateList2 item=value}*/
		options.xAxis.categories.push("");
		series.data.push(/*{$value}*/);
		/*{/foreach}*/
		options.series.push(series);
		chart = new Highcharts.Chart(options);
	}
	
	function setPlatepassRate()
	{
		var options =
		{
			chart :
			{
				renderTo : 'platepassRate',
				type : 'line'
			},
			title :
			{
				text : ''
			},
			xAxis :
			{
				categories : [],
				labels :
				{
					rotation : -45,
					align : 'right',
					style :
					{
						fontSize : '13px',
						fontFamily : 'Verdana, sans-serif'
					}
				}
			},
			yAxis :
			{
				min : 0,
				max : 100,
				title :
				{
					text : ''
				}
			},
			legend :
			{
				enabled : true
			},
			tooltip :
			{
				formatter : function()
				{
					return '<b>通过率</b>:' + this.y;
				}
			},
			series : []
		};
		var series =
		{
			name : 'xxx',
			data : [],
			pointWidth : 14,
			dataLabels :
			{
				enabled : true,
				rotation : -90,
				color : '#FFFFFF',
				align : 'right',
				x : -3,
				y : 10,
				formatter : function()
				{
					return this.y;
				},
				style :
				{
					fontSize : '11px',
					fontFamily : 'Verdana, sans-serif'
				}
			}
		};
		/*{foreach from=$passRateList3 item=value}*/
		options.xAxis.categories.push("");
		series.data.push(/*{$value}*/);
		/*{/foreach}*/
		options.series.push(series);
		chart = new Highcharts.Chart(options);
	}
	
	function setProductpassRate()
	{
		var options =
		{
			chart :
			{
				renderTo : 'productpassRate',
				type : 'line'
			},
			title :
			{
				text : ''
			},
			xAxis :
			{
				categories : [],
				labels :
				{
					rotation : -45,
					align : 'right',
					style :
					{
						fontSize : '13px',
						fontFamily : 'Verdana, sans-serif'
					}
				}
			},
			yAxis :
			{
				min : 0,
				max : 100,
				title :
				{
					text : ''
				}
			},
			legend :
			{
				enabled : true
			},
			tooltip :
			{
				formatter : function()
				{
					return '<b>通过率</b>:' + this.y;
				}
			},
			series : []
		};
		var series =
		{
			name : 'xxx',
			data : [],
			pointWidth : 14,
			dataLabels :
			{
				enabled : true,
				rotation : -90,
				color : '#FFFFFF',
				align : 'right',
				x : -3,
				y : 10,
				formatter : function()
				{
					return this.y;
				},
				style :
				{
					fontSize : '11px',
					fontFamily : 'Verdana, sans-serif'
				}
			}
		};
		/*{foreach from=$passRateList4 item=value}*/
		options.xAxis.categories.push("");
		series.data.push(/*{$value}*/);
		/*{/foreach}*/
		options.series.push(series);
		chart = new Highcharts.Chart(options);
	}
	
	$(document).ready(function()
	{
		setTotaldaypassRate();
		setTotalmonthpassRate();
		setPlatepassRate();
		setProductpassRate();
	});
	
	jQuery(function($)
	{
		$('#date1').datepicker({
			yearRange: '1900:2999',
			showOn: 'both',
			buttonImage: '{base_url()}resource/img/calendar.gif',
			buttonImageOnly: true,
			showButtonPanel: true
		});
		
		$('#date2').datepicker({
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
	<hr class="seprateline">
	<form method="post" action="{site_url('reportform')}">
		<div class="prepend-1 span-30 dataDiv" style="margin-bottom:50px;">
			<div style="margin-bottom:5px;">
				<span style="display:inline-block;width:220px;">整体合格率，天</span>
				日期：<input id="date1" name="date1" value="{$smarty.post.date1|default:''}" type="text"/>
			</div>
			<div id="totaldaypassRate" class="reportform">
			</div>
			<div style="margin-top:10px;">
				工厂
				{html_options class="factory" id=department1 name=factory1 options=$factoryArr selected=$smarty.post.factory1|default:''}
				车间
				{html_options class="department1" name=department1 options=$departmentArr selected=$smarty.post.department1|default:''}
			</div>
		</div>
		<div class="prepend-1 span-30 dataDiv" style="margin-bottom:50px;">
			<div style="margin-bottom:5px;">
				<span style="display:inline-block;width:220px;">整体合格率，月</span>
			</div>
			<div id="totalmonthpassRate" class="reportform">
			</div>
			<div style="margin-top:10px;">
				工厂
				{html_options class="factory" id=department2 name=factory2 options=$factoryArr selected=$smarty.post.factory2|default:''}
				车间
				{html_options class="department2" name=department2 options=$departmentArr selected=$smarty.post.department2|default:''}
			</div>
		</div>
		<div class="prepend-1 span-30 dataDiv">
			<div style="margin-bottom:5px;">
				<span style="display:inline-block;width:230px;">机台合格率，月</span>
			</div>
			<div id="platepassRate" class="reportform">
			</div>
			<div style="margin-top:10px;">
				机台
				{html_options class="lathe" name=lathe options=$latheArr selected=$smarty.post.lathe|default:''}
			</div>
		</div>
		<div class="prepend-1 span-30 dataDiv">
			<div style="margin-bottom:5px;">
				<span style="display:inline-block;width:220px;">产品合格率，天</span>
				日期：<input id="date2" name="date2" value="{$smarty.post.date2|default:''}" type="text"/>
			</div>
			<div id="productpassRate" class="reportform">
			</div>
			<div style="margin-top:10px;">
				产品
				{html_options class="producttype" name=producttype options=$producttypeArr selected=$smarty.post.producttype|default:''}
			</div>
		</div>
		<div style="text-align:right;">
			<input type="submit" value="查看"/>
		</div>
	</form>
	<input class="site_url" type="hidden" value="{site_url()}"/>
</div>
<!--{/block}-->