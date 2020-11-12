<?php
$themename["asmut"] = "Assemblage Untidy";
$themedesc["asmut"] = "Theme by Kawa";
$themecode["asmut"] = "
@import url('/css/common-dark.css');
@import url('/css/borders.css');

body { background: #040420 url('/img/back2c.png') fixed; }

table.post:not(.safe) { background: url('/img/back2t1.png') fixed; }
table.post td.post:not(.safe) { background: url('/img/back2g.png') fixed; }

.cell0:not(.safe) { background: url('/img/back2t2.png') fixed; }
.cell1:not(.safe) { background: url('/img/back2t3.png') fixed; }
.cell2:not(.safe) { background: url('/img/back2t1.png') fixed; }

.header0 th:not(.safe) {
	background: url('/img/backc2.png') repeat-x;
	background-size:100% 100%;
}

.header1 th:not(.safe), .errort {
	background: url('/img/backh.png') repeat-x;
	background-size:100% 100%;
}

.codeblock {
	background: url('/img/back2t2.png');
}

button, input[type='submit'] {
	border: 1px solid #669;
	background: #04082C;
	color: #FFF;
}

input[type='text'], input[type='password'], input[type='file'], input[type='email'], select, textarea {
	background: #04082C;
	border: 1px solid #669;
	color: white;
}
";
$themeprev["asmut"] = "
.safe.asmut {
	background: #040420 url('/img/back2c.png') fixed;
	color: #fff; border-collapse: collapse;
}
.safe.asmut th {
	background: url('/img/backh.png') repeat-x;
	background-size:100% 100%;
	border: 1px solid #000;
}
.safe.asmut td { background: url('/img/back2t2.png') fixed; border: 1px solid #000; }
.safe.asmut .cell2 { background: url('/img/back2t1.png') fixed; }
";

?>