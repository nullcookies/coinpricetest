<?php
#!/usr/local/bin/php
    require_once __DIR__ . "/get_google_data.php";
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Dự án PoS TeamTA</title>

    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
    <div class="container">
      <div class="page-header">
        <h1>Lấy thông tin từ Google Doc -> Database</h1>
        <p class="lead"></p>
      </div>
      <div class="row">
      	<div class="col-md-6">
     		<p class="text-center"><a href="<?php echo siteUrl(); ?>/admin/get.php?import_data=yes" class="btn btn-primary">Lấy data từ Google Doc</a></p>
        </div>
    		<div class="col-md-6">
  			
    		</div>
      </div>
    </div>

    <?php
      if(isset($_GET['import_data'])) {
        echo '<p>'.updateTableUser().'</p>';
        echo '<p>'.updateTablePlans().'</p>';
        echo '<p>'.updateTableChiTiet().'</p>';
        echo '<p>'.updateTableChiaLai().'</p>';
      }
    ?>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>