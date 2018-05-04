<?php
    require_once __DIR__ . "/get_google_data.php";
    @require_once __DIR__ . "/functions/get_database_data.php";
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
        <h1>Bảng theo dõi danh sách các plan</h1>
        <p class="lead">Quản lý users, các plans và thông tin</p>
      </div>
      <div class="row">
  		<div class="database_tables">
	      	<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
	      	  <?php
	      	  	$arrayTables 	=	showTables();
	      	  	foreach($arrayTables as $table):
	      	  ?>
			  <div class="panel panel-default">
			    <div class="panel-heading" role="tab" id="heading_<?php echo $table['Tables_in_teamta_bot']; ?>">
			      <h4 class="panel-title">
			        <a class="btn btn-info" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse_<?php echo $table['Tables_in_teamta_bot']; ?>" aria-expanded="true" aria-controls="collapse_<?php echo $table['Tables_in_teamta_bot']; ?>">
			          Bảng <?php echo strtoupper($table['Tables_in_teamta_bot']); ?>
			        </a>
			      </h4>
			    </div>
			    <div id="collapse_<?php echo $table['Tables_in_teamta_bot']; ?>" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="heading_<?php echo $table['Tables_in_teamta_bot']; ?>">
			      <div class="panel-body">
			        <?php 
			        	switch ($table['Tables_in_teamta_bot']) {
			        		case 'chialai':
			        			@require_once __DIR__ . "/db_tables/chialai.php";
			        			break;
			        		case 'users':
			        			@require_once __DIR__ . "/db_tables/users.php";
			        			break;
			        		case 'plans':
			        			@require_once __DIR__ . "/db_tables/plans.php";
			        			break;
			        		case 'chitietplan':
			        			@require_once __DIR__ . "/db_tables/chitietplan.php";
			        			break;
			        	}
			        ?>
			      </div><!-- panel-body -->
			    </div>
			  </div><!-- panel-default -->
			<?php endforeach; ?>
			</div>
	      </div><!-- database_tables -->
	  </div><!-- row -->
      <div class="row">
      	<div class="col-md-6">
     		<p class="text-center"><a href="<?php echo siteUrl(); ?>/admin/index.php?import_data=yes" class="btn btn-primary">Lấy data từ Google Doc</a></p>
        </div>
  		<div class="col-md-6">
			<p class="text-center"><a href="#" class="btn btn-success">Cập nhật data từ Database -> Google Doc</a></p>
  		</div>
           
      </div>
    </div>

    <?php
      if(isset($_GET['import_data'])) {
        //echo '<p>'.updateTableUser().'</p>';
        /*getGoogleDocPlans();
        getGoogleDocChiTiet('buzz');*/
       	//('buzz');
       	//getProfitDetail('liza');

       	//getPlanTotalCoin('opc');

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
    <script type="text/javascript">
    	jQuery(document).ready(function($) {
    		$('#accordion').collapse('hide');
    	});
    </script>
  </body>
</html>