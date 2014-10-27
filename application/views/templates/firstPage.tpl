<!--{extends file='defaultPage.tpl'}-->
<!--{block name=title}-->
<title>{$title}</title>
<!--{/block}-->
<!--{block name=style}-->
<style type="text/css" media="screen">
	#diagram1 {
		height: 400px;
	}
</style>
<!--{foreach $css_files as $file}-->
<link type="text/css" rel="stylesheet" href="{$file}" />
<!--{/foreach}-->
<!--{/block}-->

<!--{block name=script}-->
<!--{foreach $js_files as $file}-->
<script src="{$file}"></script>
<!--{/foreach}-->
{if $diagram|default:false == true}
<script src="{base_url()}resource/js/highCharts/highstock.js"></script>
<script src="{base_url()}resource/js/highCharts/modules/exporting.js"></script>
{/if}
<script>
	//用户模块
	/*{if $title == '系统用户'}*/
	$(document).ready(function(){
		//新增用户时，修改生成密码输入框
		var url = window.location.toString();
		if(url.indexOf('edit') == -1)
		{
			$('#field-password').remove();
			var pass = '<input id="field-password" type="password" maxlength="255" value="" name="password">';
			$("#password_input_box").after(pass);
			$("#field-confirmpassword").remove();
			var confpass = '<input id="field-confirmpassword" type="password" maxlength="255" value="" name="confirmpassword">';
			$("#confirmpassword_input_box").after(confpass);
		}
		//判断确认密码与密码是否一致
		$("#field-confirmpassword").blur(function()
		{
			var confpsw = $(this).val();
			var psw = $("#field-password").val();
			if(confpsw != psw)
			{
				$(this).after('<span id="errorMsg" style="color:red;display:inline-block;margin-left:5px;">输入密码不一致，请重新输入！</span>');
				$(this).attr("value","");
			}
		});
		
		$("#field-confirmpassword").focus(function(){
			$("#errorMsg").remove();
		});
	});
	/*{/if}*/
	
	/*{if $title == '测试员'}*/
	$(document).ready(function()
	{
		$(".performance").parent().click(function(e)
		{
			e.preventDefault();
			var tmpStr = $(this).attr('href');
			var tmpIndex = tmpStr.substring((tmpStr.lastIndexOf('/') + 1));
			$.getJSON("{site_url('csrygl/testerPerformance')}" + "/" + tmpIndex, function(jsonData)
			{
				if (jsonData['length'] == 0)
				{
					return;
				}
				// Create the chart
				window.chart = new Highcharts.StockChart(
				{
					chart :
					{
						renderTo : 'diagram1'
					},
					rangeSelector :
					{
						selected : 1
					},
					title :
					{
						text : jsonData['testerName'] + '的绩效'
					},
					series : [
					{
						name : '测试绩效',
						data : jsonData['data']
					}]
				});
			});
		});
		$(".workload").parent().click(function(e)
		{
			e.preventDefault();
			var tmpStr = $(this).attr('href');
			var tmpIndex = tmpStr.substring((tmpStr.lastIndexOf('/') + 1));
			$.getJSON("{site_url('csrygl/testerWorkLoad')}" + "/" + tmpIndex, function(data)
			{
				// split the data set into ohlc and volume
				var ohlc = [], volume = [], dataLength = data['length'];
				for ( i = 0; i < dataLength; i++)
				{
					ohlc.push([data['data'][i][0], // the date
					data['data'][i][1], // open
					data['data'][i][2], // high
					data['data'][i][3], // low
					data['data'][i][4] // close
					]);
					volume.push([data['data'][i][0], // the date
					data['data'][i][5] // the volume
					])
				}
				// set the allowed units for data grouping
				var groupingUnits = [['week', // unit name
				[1] // allowed multiples
				], ['month', [1, 2, 3, 4, 6]]];
				window.chart = new Highcharts.StockChart(
				{
					chart :
					{
						renderTo : 'diagram1',
						alignTicks : false
					},
					rangeSelector :
					{
						selected : 1
					},
					title :
					{
						text : data['testerName'] + '的工作时间'
					},
					yAxis : [
					{
						title :
						{
							text : 'OHLC'
						},
						height : 200,
						lineWidth : 2
					},
					{
						title :
						{
							text : 'Volume'
						},
						top : 300,
						height : 100,
						offset : 0,
						lineWidth : 2
					}],
					series : [
					{
						type : 'candlestick',
						name : '工作时间',
						data : ohlc,
						dataGrouping :
						{
							units : groupingUnits
						}
					},
					{
						type : 'column',
						name : 'Volume',
						data : volume,
						yAxis : 1,
						dataGrouping :
						{
							units : groupingUnits
						}
					}]
				});
			});
		});
	});
	/*{/if}*/
</script>
<!--{/block}-->
<!--{block name=body}-->
<!--{$output}-->
<div id="diagram1" class="span-64 last">
</div>
<!--{/block}-->
