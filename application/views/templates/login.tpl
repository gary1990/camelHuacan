<!--{extends file='defaultPage.tpl'}-->
<!--{block name=title}-->
<title>登录</title>
<!--{/block}-->
<!--{block name=style}-->
<style type="text/css" media="screen">
	div.locUserNameDefaultStr {
		left: 4px;
	}
	div.locGeneralErrorInfo {
		padding-top: 1px;
		padding-bottom: 1px;
	}
	div.locUserType {
		height: 19px;
	}
	span.locH2 {
		font-size: 20px;
		font-style: bold;
	}
	div.locSecond {
		background-color: rgb(242,242,242);
	}
	div.locBlue {
		background-color: rgb(4,53,100);
	}
	.locWhite {
		color: white;
	}
	.locBig {
		font-size: 20px;
	}
	.locMid {
		font_size: 14px;
	}
	.locInputYellow {
		background-color: rgb(255, 253, 206);
	}
</style>
<!--{/block}-->
<!--{block name=script}-->
<script>
	$(document).ready(function()
	{
		$(".locDefaultStr").click(function()
		{
			$(this).prev(".locDefaultStrContainer").focus();
		});
		$(".locDefaultStrContainer").focus(function()
		{
			$(this).next(".locDefaultStr").hide();
		});
		$(".locDefaultStrContainer").blur(function()
		{
			if ($(this).val() == "")
			{
				$(this).next(".locDefaultStr").show();
			}
		});
		$(".locDefaultStrContainer").blur();
		$("#locLoginForm").validationEngine('attach',
		{
			promptPosition : "centerRight",
			autoPositionUpdate : "true"
		});
	});
	function checkUserName(field, rules, i, options)
	{
		var err = new Array();
		var reg1 = /^[_\.].*/;
		var reg2 = /.*[_\.]$/;
		var str = field.val();
		if (reg1.test(str) || reg2.test(str))
		{
			err.push('* 不能以下划线或点开始或结束！');
		}
		if ((countOccurrences(str, '.') + countOccurrences(str, '_')) > 1)
		{
			err.push('* 一个用户名仅允许包含一个下划线或一个点！');
		}
		if (err.length > 0)
		{
			return err.join("<br>");
		}
	}

	function countOccurrences(str, character)
	{
		var i = 0;
		var count = 0;
		for ( i = 0; i < str.length; i++)
		{
			if (str.charAt(i) == character)
			{
				count++;
			}
		}
		return count;
	}
</script>
<!--{/block}-->
<!--{block name=body}-->
<div class="prepend-2 span-60 append-2 last">
	<div class="locSecond prepend-2 span-56 append-2 last">
		<span class="locH2">欢迎回来</span>
		<hr>
		<div class="span-27 append-1">
			{$noticeBody|default:'暂无内容'}
		</div>
		<div class="locBlue span-28 last">
			<div class="prepend-1 prepend-top append-bottom">
				<span class="locWhite locBig">登录</span>
			</div>
			<form id="locLoginForm" action="{site_url('login/validateLogin')}" method="post">
				<div class="clear prepend-1">
					<div class="locWhite locMid label1">
						用户名
					</div>
				</div>
				<div class="clear prepend-1 span-11 inline append-bottom10">
					<div class="relative">
						<input id="userName" name="userName" class="locInputYellow locDefaultStrContainer input1 validate[required, custom[onlyLetterNumberUnderLineDot], minSize[6], maxSize[15], funcCall[checkUserName]]" value="{$smarty.post.userName|default:''}" type="text" />
						<div class="locDefaultStr defaultStr1 locUserNameDefaultStr">
							请输入用户名
						</div>
					</div>
				</div>
				<div class="clear prepend-1">
					<div class="locWhite locMid label1">
						密码
					</div>
				</div>
				<div class="clear prepend-1 span-11 inline append-bottom20">
					<div class="relative">
						<input id="password" name="password" class="locInputYellow locDefaultStrContainer input1 validate[required, custom[onlyLetterNumber], minSize[6], maxSize[20]]" type="password" />
						<div class="locDefaultStr defaultStr1 locUserNameDefaultStr">
							请输入密码
						</div>
					</div>
				</div>
				<div class="clear prepend-1">
					<div class="inline span-5">
						<button id="loginButton" class="button1" type="submit">
							登录
						</button>
					</div>
					<div class="span-4">
						<a href="#">忘记密码?</a>
					</div>
					<div class="span-10 locGeneralErrorInfo">
						<span class="error1">{$loginErrorInfo|default:''}</span>
					</div>
				</div>
				<div class="clear span-1">
					&nbsp;
				</div>
			</form>
		</div>
		<div class="clear">
			&nbsp;
		</div>
		<div class="clear">
			&nbsp;
		</div>
		<div class="clear">
			&nbsp;
		</div>
	</div>
	<div class="clear prepend-20 span-21">
		<span>Copyright(C) All Rights Reserved</span>
	</div>
</div>
<!--{/block}-->