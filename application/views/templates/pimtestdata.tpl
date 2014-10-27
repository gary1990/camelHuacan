<script>
	$(".aa").click(function()
	{
		alert("hello");
	});
	$(".tab").click(function(){
		var listnow = $(this).attr("id");
		if(listnow != 'data_list1'){
			$(".data_list").hide();
			$("."+listnow).show();
			$(".tab").css("background-color","white");
			$(this).css("background-color","gray");
		}else{
			$(".data_list").hide();
			$(".data_list1").show();
			$(".tab").css("background-color","white");
			$(".tab1").css("background-color","gray");
		}
	});
</script>
<style type="text/css">
	.headList_div{
		float:left;
		width:30px;
		text-align:center;
	}
	.tab{
		border:1px solid black;
		cursor:pointer;
	}
	.data_list{
		width:335px;
		font-size:11px;
	}
	img{
		width:335px;
		height:200px;
	}
	.data_list{
		margin-top:10px;
	}
	.pim_table{
		font-size:11px; 
	}
</style>
<div class="pimdata">
	<div class="headList">
		<div class="headList_div">{$pimsernum}</div>
		{foreach from=$testdata key=key item=data}
		{if $key !== 0 }
			<div class="headList_div tab tab{$key+1}" id="data_list{$key+1}">组{$key+1}</div>
		{else}
			<div class="headList_div tab tab{$key+1}" id="data_list{$key+1}" style="background-color: gray;">组{$key+1}</div>
		{/if}
		{/foreach}	
	</div>
	<br/>
	{foreach from=$infolistArray key=key item=infolist}
	{if $key == 0}
	<div class="data_list data_list{$key+1}">
	{else}
	<div class="data_list data_list{$key+1}" style="display: none">
	{/if}
		{$infolist['col1']},{$infolist['col2']},{$infolist['col3']},{$infolist['col4']}<br/>
		{$infolist['col5']},{$infolist['col6']},{$infolist['col7']},{$infolist['col8']}<br/>
		{$infolist['col9']},{$infolist['col10']},{$infolist['col11']},{$infolist['model']},{$infolist['test_time']|regex_replace:"/[-:]/":""}<br/>
		{$infolist['col12']},Ser No:{$infolist['ser_num']},{$infolist['col13']}<br/>
		<img src = "{base_url()}assets/uploadedSource/pim/{$infolist['upload_date']|regex_replace:"/[-]/":"_"}/{$labelnamearray[0]['name']}/{$infolist['ser_num']}_{$infolist['test_time']|regex_replace:'/[-:\s]/':''}.jpg" />
	</div>
	{/foreach}
	{foreach from=$testdata key=key item=testdateArray1}
	{if $key == 0}
	<div class="data_list data_list{$key+1}">
	{else}
	<div class="data_list data_list{$key+1}" style="display: none;">
	{/if}
		<table class="pim_table" border="1">
		<tr><th>频率</th><th>值</th></tr>
		{foreach from=$testdateArray1['testdata'] item=tesdateArray2}
			<tr>
				<td>{$tesdateArray2['frequency']}</td>
				{if $tesdateArray2['value'] > $limtline}
					<td><div style="background-color: red;">{$tesdateArray2['value']}</div></td>
				{else}
					<td><div>{$tesdateArray2['value']}</div></td>
				{/if}
			</tr>
		{/foreach}
		</table>
	</div>
	{/foreach}
</div>