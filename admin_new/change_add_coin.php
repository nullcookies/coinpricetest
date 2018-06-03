<?php
#!/usr/local/bin/php
    require_once __DIR__ . "/functions/get_google_data.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>Change Add Coin - Quản lý dự án Pos TeamTa</title>
  <!-- Bootstrap core CSS-->
  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom fonts for this template-->
  <link href="vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
  <!-- Page level plugin CSS-->
  <link href="vendor/datatables/dataTables.bootstrap4.css" rel="stylesheet">
  <!-- Custom styles for this template-->
  <link href="css/sb-admin.css" rel="stylesheet">
</head>

<body class="fixed-nav sticky-footer bg-dark" id="page-top">
  <!-- Navigation-->
  <?php include __DIR__ . '/include/navigation.php'; ?>
  <div class="content-wrapper">
    <div class="container-fluid">
      <!-- Breadcrumbs-->
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <a href="#">Dashboard</a>
        </li>
        <li class="breadcrumb-item active">Change Add Coin</li>
      </ol>
      <div class="row">
        <div class="col-12">
          <h1>Trang Quản Lý Dự Án PoS TeamTa</h1>
        </div>
      </div>
      <div class="row">
        <div class="col-12">
          <!-- Example DataTables Card-->
          <div class="card mb-3">
            <div class="card-header">
              <i class="fa fa-table"></i> Active/Deactive Change Add Coin Request</div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                  <thead>
                    <tr>
                      <th class="text-center">STT</th>
                      <th class="text-center">Tên Plan</th>
                      <th class="text-center">Cập nhật</th>
                      <th class="text-center">Trạng Thái</th>
                    </tr>
                  </thead>
                  <tfoot>
                    <tr>
                      <th class="text-center">STT</th>
                      <th class="text-center">Tên Plan</th>
                      <th class="text-center">Cập nhật</th>
                      <th class="text-center">Trạng Thái</th>
                    </tr>
                  </tfoot>
                  <tbody>
                    <?php
                      if(isset($_GET['ten_plan']) && isset($_GET['active_request'])) {
                        $resultRequest        =     requestActivePlan($_GET['ten_plan'], $_GET['active_request']);
                      }
                      $currentActivePlans     =     getDbPlans();
                      foreach($currentActivePlans as $key => $value) :
                        $statusPlan           =     getStatusPlans($value['ten_plan']);
                        if($statusPlan == true) {
                          $activeClass        =     '<div class="alert alert-success" role="alert"><b>Cho</b> phép thêm Coin</div>';
                        } else {
                          $activeClass        =     '<div class="alert alert-danger" role="alert"><b>Không</b> cho phép thêm Coin</div>';
                        }
                    ?>
                    <tr>
                      <td class="text-center"><?php echo $key+1; ?></td>
                      <td class="text-center"><?php echo strtoupper($value['ten_plan']); ?></td>
                      <td class="text-center">
                        <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                          <div class="btn-group" role="group">
                            <button id="btnGroupDrop1" type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              Gửi Yêu Cầu
                            </button>
                            <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                              <a class="dropdown-item" href="<?php echo siteUrl(); ?>/admin_new/change_add_coin.php?ten_plan=<?php echo $value['ten_plan']?>&active_request=1">Cho phép</a>
                              <a class="dropdown-item" href="<?php echo siteUrl(); ?>/admin_new/change_add_coin.php?ten_plan=<?php echo $value['ten_plan']?>&active_request=0">Không cho phép</a>
                            </div>
                          </div>
                        </div>
                      </td>
                      <td class="text-center"><?php echo $activeClass; ?></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
            <!-- <div class="card-footer small text-muted">Updated yesterday at 11:59 PM</div> -->
          </div>
        </div><!-- col-12 -->
      </div><!-- row -->
    <!-- /.container-fluid-->
    <!-- /.content-wrapper-->
    </div>
  </div>
</body>
<?php include __DIR__ . "/include/footer.php"; ?>
</html>