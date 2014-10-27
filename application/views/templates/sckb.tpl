<!--{extends file='userPage.tpl'}-->
<!--{block name=title}-->
<title>生产看板</title>
<!--{/block}-->
<!--{block name=style}-->
<style type="text/css" media="screen">
	div.dataDiv {
		height: 400px;
	}
</style>
<!--{/block}-->
<!--{block name=subScript}-->
<script src="{base_url()}resource/js/highCharts/highcharts.js"></script>
<script src="{base_url()}resource/js/hightCharts/modules/exporting.js"></script>
<script>
	function setTotalNum()
	{
		var options =
		{
			chart :
			{
				renderTo : 'dateNum',
				type : 'column'
			},
			title :
			{
				text : '日产量'
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
				title :
				{
					text : '产量'
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
					return '<b>产量</b>:' + this.y + '<br/>' + '<b>时间点</b>: ' + (this.x) + '点';
				}
			},
			series : []
		};
		var series =
		{
			name : '时间(24小时)',
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
		/*{foreach $totalNumList as $item}*/
		options.xAxis.categories.push(/*{counter name=totalNum}*/);
		series.data.push(/*{$item}*/);
		/*{/foreach}*/
		options.series.push(series);
		chart = new Highcharts.Chart(options);
	}

	function setPassRate()
	{
		var options =
		{
			chart :
			{
				renderTo : 'passRate',
				type : 'column'
			},
			title :
			{
				text : '通过率'
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
					text : '通过率'
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
					return '<b>通过率</b>:' + this.y + '%<br/>' + '<b>时间点</b>: ' + (this.x) + '点';
				}
			},
			series : []
		};
		var series =
		{
			name : '时间(24小时)',
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
		/*{foreach $passRateList as $item}*/
		options.xAxis.categories.push(/*{counter name=passRateNum}*/);
		series.data.push(/*{$item}*/);
		/*{/foreach}*/
		options.series.push(series);
		chart = new Highcharts.Chart(options);
	}


	$(document).ready(function()
	{
		setTotalNum();
		setPassRate();
	}); 
</script>
<!--{/block}-->
<!--{block name=subBody}-->
<div class="prepend-1 span-63 last">
	<form method="post" action="{site_url('sckb')}">
		日期:
		{html_select_date start_year="-10" month_format="%m" field_order=YMD time=$theDate|default:'' }
		测试站点:{html_options name=testStation options=$testStationList selected=$smarty.post.testStation|default:''}
		<input type="submit" value="查找">
	</form>
</div>
<div class="span-64 last">
	<div id="dateNum" class="prepend-1 span-30 dataDiv">
	</div>
	<div id="passRate" class="prepend-1 span-30 dataDiv">
	</div>
</div>
<!--{/block}-->
