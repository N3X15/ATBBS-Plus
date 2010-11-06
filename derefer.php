<html>
<head>
<?php
	if(isset($_GET['url'])) {
		$url = htmlspecialchars($_GET['url']);
		echo "<META HTTP-EQUIV=\"refresh\" CONTENT=\"0; URL=" . $url . "\">";
	}
?>
</head>
<body>
<?php
	if(isset($_GET['url'])) {
		$url = htmlspecialchars($_GET['url']);
		echo "<p>Redirecting to <a href=\"" . $url . "\">" . $url . "</a></p>";
	} else {
		echo "<p>No URL specified.</p>";
	}
?>
</body>
</html>
