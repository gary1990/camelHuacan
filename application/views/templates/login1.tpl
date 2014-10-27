<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<link rel="stylesheet" href="{base_url()}resource/css/screen.css" type="text/css" media="screen, projection"/>
		<link rel="stylesheet" href="{base_url()}resource/css/print.css" type="text/css" media="print"/>
		<!--[if lt IE 8]><link rel="stylesheet" href="{base_url()}/resource/css/ie.css" type="text/css" media="screen, projection"/><![endif]-->
		<link rel="stylesheet" href="{base_url()}resource/css/user.css" type="text/css" media="screen, projection"/>
		<script src="{base_url()}resource/js/jquery.js" type="text/javascript"></script>
		
		<!-- validationEngine -->
		<link rel="stylesheet" href="{base_url()}resource/css/template.css" type="text/css" media="screen, projection"/>
		<link rel="stylesheet" href="{base_url()}resource/css/validationEngine.jquery.css" type="text/css" media="screen, projection"/>
		<script src="{base_url()}resource/js/jquery.validationEngine.js" type="text/javascript"></script>
		<script src="{base_url()}resource/js/jquery.validationEngine-zh_CN.js" type="text/javascript"></script>

		<title>登录</title>
		<style>
			.logo_appName{
				text-align:center;
				margin-top:50px;
				margin-bottom:30px;
			}
			.appName{
				font-size:38px;
			    font-family:Arial;
			}
			img{
				width:25px;
				height:20px;
				vertical-align: -8px;
			}
			.locBlue{
				margin-left:50%;
				border-left:1px solid #666666;
				padding-left:30px;
			}
			.label1{
				font-size:16px;
			}
			.input1{
				height:20px;
				width:149px;
			}
			.button1{
				cursor:pointer;
				color:gray;
				background-color:#001429;
				border: 0;
				border-radius:5px;
				width:75px;
				height:25px;
				font-size:16px;
			}
			.inline{
				margin-right:20px;
			}
			.span-21{
				margin-top:100px;
				text-align:center;
			}
			.error1{
				font-size:13px;
			}
		</style>
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
	</head>
	<body>
		<div class="container">
			<div class="prepend-2 span-60 append-2 last">
				<div class="logo_appName">
					<span class="appName">{$producter}</span>
				</div>
				<div class="locSecond prepend-2 span-56 append-2 last">
					<div class="locBlue span-28 last">
						<form id="locLoginForm" action="{site_url('login/validateLogin')}" method="post">
							<div class="clear prepend-1">
								<div class="locWhite locMid label1">
									用户名
								</div>
							</div>
							<div class="clear prepend-1 span-11 inline append-bottom10">
								<div class="relative">
									<input id="userName" name="userName" class="locInputYellow locDefaultStrContainer input1 validate[required, custom[onlyLetterNumber], minSize[6]]" value="{$smarty.post.userName|default:''}" type="text" />
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
					<div class="clear">
						&nbsp;
					</div>
				</div>
				<div class="clear prepend-20 span-21">
					<img src="{base_url()}resource/img/gemcycle.png"/>
					<span>Camel Production System 5.1,Powered by Gemcycle</span>
				</div>
			</div>
		</div>
	</body>
</html>