<?php
//require_once('admin/includes/CommonIncludes.php');
require_once('admin/includes/AdminTemplates.php');
require_once('admin/config/config.php');
require_once('admin/includes/CommonFunctions.php');
/*<a href="admin/includes/CommonIncludes.php"></a> -->*/

commonHead();
?>
<body class="skin-blue" onload="fieldfocus('password');">
	 <header class="header">
			<nav class="navbar navbar-static-top no-margin" role="navigation" >
				<a  title="Tuplit" href="login" class="logo" style="white-space:nowrap;width :auto">
                <!-- Add the class icon to your logo image or logo icon to add the margining -->
                Tuplit
            </a>
			</nav>
	  </header>
	
		

                <!-- Main content -->
                <section class="content">
                 
                    <div class="error-page">
						<br><br>
                        <h1 class="headline text-info" style="color:#01A99A"> <strong>404</strong></h1>
                        <div class="error-content"><br>
                            <h3><i class="fa fa-warning text-yellow"></i> Oops! Page not found.</h3>
                           <?php if(isset($_GET['UID']) && !empty($_GET['UID'])) { 
									if(isset($_GET['Type']) && $_GET['Type'] == 1){
							?>
							<p>You can't access this link directly. Please proceed Forget Password process with your App.</p>        
							<?php } else{ ?>
							<p>You can't access this link directly. Click &nbsp;&nbsp; <a href="<?php echo SITE_PATH; ?>/merchant/ForgotPassword" title="Forgot password"><i class="fa fa-lock"></i>&nbsp;&nbsp;Forgot password</a> &nbsp; to proceed further.</div></p>   
							<?php	}
								}
							else { ?>
						    <p>
                                We could not find the page you were looking for. 
                                Meanwhile, you may <a href='#'>return to dashboard</a> or try using the search form.
                            </p>
							<?php } ?>
                            <!-- <form class='search-form'>
                                <div class='input-group'>
                                    <input type="text" name="search" class='form-control' placeholder="Search"/>
                                    <div class="input-group-btn">
                                        <button type="submit" name="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                                    </div>
                                </div>
                            </form> -->
                        </div><!-- /.error-content -->
                    </div><!-- /.error-page -->

                </section><!-- /.content -->
            </aside><!-- /.right-side -->
	</div>
	</div>
	
	<footer>&copy; <?php echo date('Y');?> Tuplit Inc. </footer>
	
	
</html>