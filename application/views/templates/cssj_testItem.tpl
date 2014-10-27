<script>
	$(".imgPath").click(function(e)
	{
		e.preventDefault();
		$("#imgDiv").html('<img class="testImg" src=' + $(this).attr("path") + ' />');
	}); 
</script>
<div class="span-31 last">
	{$productTestInfo}
</div>
{foreach $testItemResultList as $item}
<div class="span-31 last">
	<div class="span-31">
		<a href="#" class="imgPath" path="{base_url()}/assets/uploadedSource/{$item['img']}">{$item['testItemName']}</a>
	</div>
	{foreach $item['testItemMarkValueArray'] as $mark}
	<div class="prepend-1 span-30 last">
		({$mark['value']}|{$mark['mark']}|{$mark['channel']}|{$mark['trace']})
	</div>
	{/foreach}
</div>
{/foreach} 