<!--{extends file='defaultPage.tpl'}-->
<!--{block name=style}-->
<!--{/block}-->
<!--{block name=script}-->
<script type="text/javascript">
/*add by gary 新增包装员时数据校验 */
	$(document).ready(function(){
		$("input[value='保存']").click(function(e){
			var subUrl = window.location.href.substring(window.location.href.substring(0,window.location.href.lastIndexOf("/")).lastIndexOf("/"));
			if(subUrl == "/packingemployees/add")
			{
				var packerId = $("#field-employeeId").val();
				var packerPassword = $("#field-password").val();
				
				var packerIdReg = /[\W_]/g;
			
				if(packerIdReg.test(packerId))
				{
					alert("工号只允许数字、字母！");
					e.preventDefault();
				}
				else
				{
					//submmit
				}
				if(packerPassword.length < 6 || packerIdReg.test(packerPassword))
				{
					alert("密码只允许数字、字母，最少6位！");
					e.preventDefault();
				}else
				{
					//submmit
				}	
			}
		});
	});
</script>
<!--{block name=subScript}-->
<!--{/block}-->
<!--{/block}-->
<!--{block name=body}-->
<div class="span-64 last">
	<div class="span-64 last">
		<div class="span-12">
			<span class="cldnH1">产品</span>
		</div>
		<div class="prepend-1 span-6">
			<span><a style="line-height: 39px" class="{if $title=='产品管理'} currentMenu {else} normal {/if}" href="{site_url('firstPage/producttype')}">产品管理</a></span>
		</div>
	</div>
	<hr>
	<div class="span-64 last">
		<div class="span-12">
			<span class="cldnH1">测试</span>
		</div>
		<div class="prepend-1 span-6">
			<span><a style="line-height: 39px" class="{if $title=='测试项'} currentMenu {else} normal {/if}" href="{site_url('firstPage/testitem')}">测试项</a></span>
		</div>
		<div class="prepend-1 span-6">
			<span><a style="line-height: 39px" class="{if $title=='测试站点'} currentMenu {else} normal {/if}" href="{site_url('firstPage/teststation')}">测试站点</a></span>
		</div>
		<div class="prepend-1 span-6">
			<span><a style="line-height: 39px" class="{if $title=='产品测试方案'} currentMenu {else} normal {/if}" href="{site_url('firstPage/producttypetestcase')}">产品测试方案</a></span>
		</div>
	</div>
	<hr>
	<div class="span-64 last">
		<div class="span-12">
			<span class="cldnH1">组织与人员</span>
		</div>
		<div class="prepend-1 span-6">
			<span><a style="line-height: 39px" class="{if $title=='部门'} currentMenu {else} normal {/if}" href="{site_url('firstPage/department')}">部门管理</a></span>
		</div>
		<div class="prepend-1 span-6">
			<span><a style="line-height: 39px" class="{if $title=='技能水平'} currentMenu {else} normal {/if}" href="{site_url('firstPage/skilllevel')}">技能水平</a></span>
		</div>
		<div class="prepend-1 span-6">
			<span><a style="line-height: 39px" class="{if $title=='测试员'} currentMenu {else} normal {/if}" href="{site_url('firstPage/tester')}">测试员</a></span>
		</div>
		<div class="prepend-1 span-6">
			<span><a style="line-height: 39px" class="{if $title=='测试员权限'} currentMenu {else} normal {/if}" href="{site_url('firstPage/testright')}">测试员权限</a></span>
		</div>
	</div>
	<hr>
	<div class="span-64 last">
		<div class="span-12">
			<span class="cldnH1">系统用户管理</span>
		</div>
		<div class="prepend-1 span-6">
			<span><a style="line-height: 39px" class="{if $title=='系统用户'} currentMenu {else} normal {/if}" href="{site_url('firstPage/user')}">系统用户管理</a></span>
		</div>
		<div class="prepend-1 span-6">
			<span><a style="line-height: 39px" class="{if $title=='用户组'} currentMenu {else} normal {/if}" href="{site_url('firstPage/team')}">用户组</a></span>
		</div>
		<div class="prepend-1 span-6">
			<span><a style="line-height: 39px" class="{if $title=='首页通知'} currentMenu {else} normal {/if}" href="{site_url('firstPage/firstpagenotice')}">首页通知</a></span>
		</div>
	</div>
	<hr>
	<div class="span-64 last">
		<div class="span-12">
			<span class="cldnH1">功能模块</span>
		</div>
		<div class="prepend-1 span-6">
			<span><a style="line-height: 39px" class="{if $title=='生产看板'} currentMenu {else} normal {/if}" href="{site_url('sckb')}">生产看板</a></span>
		</div>
		<div class="prepend-1 span-9">
			<span><a style="line-height: 39px" class="{if $title=='质量追溯'} currentMenu {else} normal {/if}" href="{site_url('gqts')}">质量追溯</a></span>
		</div>
	</div>
	<hr>
	<div class="span-64 last">
		<div class="span-12">
			<span class="cldnH1">包装</span>
		</div>
		<div class="prepend-1 span-6">
			<span><a style="line-height: 39px" class="{if $title=='包装用户'} currentMenu {else} normal {/if}" href="{site_url('firstPage/packingemployees')}">用户</a></span>
		</div>
		<div class="prepend-1 span-9">
			<span><a style="line-height: 39px" class="{if $title=='包装记录'} currentMenu {else} normal {/if}" href="{site_url('packing')}">包装记录</a></span>
		</div>
	</div>
</div>
<div class="prepend-top span-64 last">
	<!--{block name=subBody}-->
	<!--{/block}-->
</div>
<!--{/block}-->
