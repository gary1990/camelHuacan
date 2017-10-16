<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title>质量报告</title>
		<script src="{base_url()}resource/js/jquery.js"></script>
		<style type="text/css">
			body{
				margin:0px;
				border:0px;
			}
			.container{
				width:765px;
				margin:0 auto;
				border:1px solid black;
				padding:10px;
			}
			.title{
				font-size: 28px;
				text-align:center;
			}
			.producter{
				text-align:right;
				margin-bottom:20px;
				margin-right:20px;
				margin-top:10px;
			}
			.productsn{
				margin-top:10px;
			}
			.producttype{
				margin-top:20px;
				margin-bottom:10px;
			}
			.hr_line{
				height:3px;
				margin-bottom:30px;
				color:black;
			}
			.separate_line{
				margin-top:20px;
				margin-bottom:20px;
				height:3px;
			}
			table{
				border-collapse: collapse;
				margin-top:10px;
			}
			td,th{
				border: 1px solid black;
				text-align:center;
			}
			.testitem{
				color:blue;
				font-weight:bold;
			}
			.subitem{
				margin-top:30px;
			}
			.vnaImg{
				width:500px;
			}
			.vnaimg{
				margin-top:10px;
			}
			.pimImg{
				width:350px;
			}
			.vnaresulttable{
				width:350px;
			}
			.basictable{
				width:700px;
			}
		</style>
	</head>
	<body>
		<div class="container">
			<div class="title">
				质量报告
			</div>
			<div class="producter">
				生产厂家：{$producter}
			</div>
			<div class="productsn">
				产品序列号：{$productsn}
			</div>
			<div class="producttype">
				型号：{$basicInfoArray['name']|default:''}
			</div>
			<hr class="hr_line">
			<span class="testitem">VNA测试</span>
			<div>
				<table class="basictable">
					<tr>
						<th>测试时间</th>
						<th>测试设备型号</th>
						<th>测试设备序列号</th>
						<th>测试员</th>
						<th>测试结果</th>
					</tr>
					<tr>
						<td>{$basicInfoArray['testTime']|default:''}</td>
						<td>{$basicInfoArray['teststationname']|default:''}</td>
						<td>{$basicInfoArray['equipmentSn']|default:''}</td>
						<td>{$basicInfoArray['tester']|default:''}</td>
						<td>
							{if $basicInfoArray['result']|default:'' eq ""}
								&nbsp;
							{elseif $basicInfoArray['result'] eq "1"}
								合格
							{else}
								不合格
							{/if}
						</td>
					</tr>
				</table>
			</div>
			{counter start=0 skip=1 print=false}
			{foreach from=$result key=k item=value}
				<div class="subitem">测试项{counter}:{$k|default:''}</div>
				<table class="vnaresulttable">
					<tr><th>Freq</th><th>Value</th><th>Result</th></tr>
					{foreach from=$value[1] item=val}
						<tr>
							<td>{$val[0]}</td>
							<td>{$val[1]}</td>
							<td>
								{if $val[2] eq "1"}
									合格
								{else}
									不合格
								{/if}
							</td>
						</tr>
					{/foreach}
				</table>
				<div class="vnaimg"><img src="{base_url()}uploadedSource/{$value['0']|regex_replace:"/[\\\\]/":"/"}" class="vnaImg"/></div>
			{/foreach}
			<hr class="hr_line">
			<span class="testitem">耐压测试</span>
			<div>{$hiPotResult}</div>
			<hr class="separate_line">
			<span class="testitem">PIM测试</span>
			<div>
				<table class="basictable">
					<tr>
						<th>测试时间</th>
						<th>测试设备型号</th>
						<th>测试设备序列号</th>
						<th>测试员</th>
						<th>测试结果</th>
					</tr>
					<tr>
						<td>{$pimbasicInfo['testtime']|default:''}</td>
						<td>{$pimbasicInfo['teststaionName']|default:''}</td>
						<td>{$pimbasicInfo['teststationSn']|default:''}</td>
						<td>{$pimbasicInfo['col13']|default:''}</td>
						<td>{$pimtestResult|default:'&nbsp;'}</td>
					</tr>
				</table>
			</div>
			<div>
				<table class="vnaresulttable">
					<tr>
						{foreach from=$pimmaxdataArray key=k item=value}
							<th>组{$k+1}</th>
						{/foreach}
					</tr>
					<tr>
						{foreach from=$pimmaxdataArray item=value}
							<td>{$value['value']}</td>
						{/foreach}
					</tr>
				</table>
			</div>
			{foreach from=$pimmaxdataArray key=k item=value}
				<div style="display: inline-block;margin-top:10px;margin-right:25px;">
					<div>组{$k+1}</div>
					<img class="pimImg" src="{base_url()}uploadedSource/pim/{$value['upload_date']|regex_replace:"/[-]/":"_"}/{$pimbasicInfo['name']|default:''}_{$productsn|regex_replace:"/[\s]/":""}/{$productsn}_{$value['test_time']|regex_replace:'/[-:\s]/':''}.jpg"/>
				</div>
			{/foreach}
		</div>
	</body>
</html>