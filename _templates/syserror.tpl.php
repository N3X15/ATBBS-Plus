<html>
	<head>
		<title>
			FATAL SYSTEM ERROR
		</title>
		<style type="text/css">
body,html
{
	background:maroon;
	color:white;
	font:small sans-serif;
	margin:0;
}

h1
{
	text-align:center;
	border-bottom:3px solid #770000;
	padding:0.5em;
}

div#ebody
{
	margin:2em;
	background:#a00000;
	-moz-border-radius:3px;
	padding:7px;
	border-bottom:3px solid #770000;
	border-right:2px solid #770000;
}

a
{
	color:red;
}
		</style>
	</head>
	<body>
		<h1>
			FATAL SYSTEM ERROR
		</h1>
		<div id="ebody">
			<p>
				<?=$this->err?>
			</p>
			<p style="text-align:center;font-size:smaller;font-weight:bold;">
				[ Click <a href="#" onClick="history.go(-1)">here</a> to return to the previous page ]
			</p>
		</div>
	</body>
</html>
