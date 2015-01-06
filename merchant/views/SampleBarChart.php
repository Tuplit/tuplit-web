<?php 
require_once('includes/CommonIncludes.php');
$array_val  			= array('10','30','20','40','30');
$_SESSION['arrayVal'] 	= $array_val;
$_SESSION['data']		= 10;
$_SESSION["usedData"]	= 10;
echo "<pre>";print_r($_SESSION['arrayVal']);echo "</pre>";

?>
<input type="hidden" id="bar_values" value="<?php print_r ($array_val);?>" >

<script src="<?php echo SITE_PATH;?>/webresources/js/jquery-latest.js" type="text/javascript"></script>

<script type="text/javascript">
$(document).ready(function(){
var arrayVal	= '<%=Session["data"]%>';
alert(arrayVal);
var Val	= <?php  echo $_SESSION['data'];?>;
alert(Val);
var sessionValue = '<%=Session["usedData"]%>';
alert(sessionValue);
/*Session.set(dataValue, 'test data' );
alert(Session.get(dataValue));*/
//sessionStorage.SessionName = "100" ;
//sessionStorage.getItem("SessionName");
sessionStorage.setItem("sessName","100");
alert(sessionStorage.getItem("sessName"));
});
</script>
