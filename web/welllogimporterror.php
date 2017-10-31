<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<html>
<head>
<link rel="stylesheet" type="text/css" href="gva_styles.css" />
</head>
<body>
<TABLE class='container'>
<tr>
<td>
	<H1>Error: These depths have already been imported!</H1>
	<h2><?echo "Last imported depth: $lastendmd";?></h2>
	<h2><?echo "LAS file depth: $lasstart - $lasend";?></h2>
	<A class='menu' href='<?echo $ret;?>?seldbname=<?echo $seldbname;?>'>Return</A>
</td>
</tr>
</table>
</body>
</html>
<?exit();?>
