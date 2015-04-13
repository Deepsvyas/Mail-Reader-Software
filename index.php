<?php error_reporting(0);
//include email reader class file
include_once('emailReaderClass.php');
//create email reader class object
$obj= New emailReaderClass();

if(isset($_POST['submit'])){	
	$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
	$username = $_POST['email']; # e.g somebody@gmail.com
	$password = $_POST['password'];
	$subject = $_POST['subject'];
	$mailbody = $obj->read_body($hostname,$username,$password,$subject);
	if($mailbody == '0'){
		header('location:readmail.php?error');
		exit();
	}else{
		$insertion = $obj->insert_data($mailbody);
		header('location:orders.php?success');
		exit();
	}
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link href="favicon.ico" rel="icon" type="image/x-icon">
        <title>.:IRUNGUNS Exporter/Importer of firearms between the US & Canada:.</title>
        <link href="css/style.css" rel="stylesheet" type="text/css">
        <link href="css/responsive.css" rel="stylesheet" type="text/css">
        <link href="css/bootstrap.css" rel="stylesheet" type="text/css">
        <link href="css/bootstrap-responsive.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="css/layout.css">
        <script type="text/javascript" src="js/jquery.min.js"></script>
        <script type="text/javascript" src="js/jquery-ui.js"></script>
        <script type="text/javascript" src="js/validation.js"></script>
      
        <style>
            #webmaildiv , #gmaildiv{
                display: block;
            }
            #load_img{
                width: 50%;
                position: relative;
                left: 67%;
                margin-left: -43%;
            }
            #loading{
                display: none;;
                /*                text-align: center;
                                margin-left: 240px;
                                margin-right: auto;
                                margin-top: 100px;
                                margin-bottom: auto;
                                border: #eeeeee;
                                width: 50%;
                                height: 50%;*/
            }
            .main-contentarea{
                /*                margin-top:0; 
                                margin-right:auto;
                                margin-bottom:0;
                                margin-left:auto; */
                width: auto;
                height: auto;
                line-height: 30px;
                left: 36%;
                position: relative;
            }
        </style>
        
    </head>
    <body>
        <div class="wrapper">
            <header>
                <div class="inner_head "><div class="logo"><a href="#"><img src="logo.png" height="90" width="340" alt="Logo"> </a></div>
                    <div class="headerRight">
                        <div class="topMenu"> <span class="leftimg"></span>
                            <div class="innertopMenu" id="shopping_welcome_box">
                                <ul class="navBar">
                                    <li class="first"><a href="#" style="font-family:cursive;font-size: smaller;">Welcome : admin</a></a></li>
                                    <li><span class="centerline"></span></li>
                                    <li><a href="my_account.php"></a></li>
                                </ul>
                            </div>
                            <span class="rightimg"></span> 
                        </div>
                        <div class="mainMenu">
                            <div class="row-fluid">
                                <div class="navbar navbar-inverse">
                                    <div class="navbar-inner">
                                        <div class="container-fluid"> 
                                            <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse"> <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span> </a>
                                            <div class="nav-collapse collapse">
                                                <ul class="nav">
                                                    <li class="active"><a href="readmail.php">Read Mail</a></li>
                                                    <li ><a href="orders.php">All Order</a></li>
                                                    <li><a href="#">Go To Admin</a></li>
                                                </ul>
                                            </div>
                                            <!-- /.nav-collapse --> 
                                        </div>
                                        <!-- /.container --> 
                                    </div>
                                    <!-- /.navbar-inner --> 
                                </div>
                                <!-- /.navbar --> 
                            </div>
                        </div>
                    </div>
				</div>
            </header>
            <div class="content-area">
                <div class="inner-content">  
                    <div class="main-contentarea" id="mytab">
                        <div  class="head_table">
                            <h4>Enter Gmail Accont Credentials</h4>
                        </div>
                        <div id="loading" >
                            <img src="spin.gif" alt="loading..." id="load_img"/>
                        </div>
                        <hr style="margin: 5px ! important;">
                        <div id="gmaildiv"  style="width: auto;" >
                            <form action="#" method="post">
                                <div class="fieldbox" style="text-align: center;">
                                    <label style="color: rgb(154, 55, 49) ! important;font-size: large;">Email Address :</label>
                                    <input type="text" name="email" id="email" size="10" value="">
                                </div><br>
                                <div class="fieldbox" style="text-align: center;">
                                    <label  style="color: rgb(154, 55, 49) ! important;font-size: large;">User Password :</label>
                                    <input type="password" name="password" id="password" size="35" value="">
                                </div>
                                <div class="fieldbox" style="text-align: center;"><br>
                                    <label  style="color: rgb(154, 55, 49) ! important;font-size: large;">Subject :</label>
                                    <textarea name="subject" id="subject" rows="5" value="I Run Guns LLC Order Confirmation"></textarea>
                                </div>
                                <div style="float: left;"><label>&nbsp;</label>
                                    <input class="button" type="submit" value="Submit" name="submit"  onclick="$('#loading').show();">
                                </div>  
                            </form>
                        </div>
                        <!--</div>-->
                    </div>
				</div> 
            </div>
        </div>
	</div>
</div>     
</body>
</html>
