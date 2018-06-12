<?php
#!/usr/local/bin/php
    require_once __DIR__ . "/functions/request_add_coin.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>Yêu Cầu Thêm Coin - Quản lý dự án Pos TeamTa</title>
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
        <li class="breadcrumb-item active">Yêu Cầu Thêm Coin</li>
      </ol>
      <div class="row">
        <div class="col-12">
          <h1>Trang Quản Lý Dự Án PoS TeamTa</h1>
        </div>
      </div>
      <div class="row">
        <?php
          if(isset($_GET['id_dang_ky'])) {
            updateAdminApprove($_GET['id_dang_ky']);
            $arrayRegister  =   getInfoRegister($_GET['id_dang_ky']);
            sendEmailToUser($arrayRegister['username'], $arrayRegister['email'],$arrayRegister['ten_plan'], $arrayRegister['so_coin_them'], $arrayRegister['txtid'], $arrayRegister['ngay_yeu_cau']);
          }
        ?>
        <div class="col-12">
          <!-- Example DataTables Card-->
          <div class="card mb-3">
            <div class="card-header">
              <i class="fa fa-table"></i> Yêu Cầu Thêm Coin</div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                  <thead>
                    <tr>
                      <th class="text-center">STT</th>
                      <th class="text-center">Ngày Yêu Cầu</th>
                      <th class="text-center">Username</th>
                      <th class="text-center">Tên Plan</th>
                      <th class="text-center">Số Coin Thêm</th>
                      <th class="text-center">Txtid</th>
                      <th class="text-center">Email</th>
                      <th class="text-center" width="30%">Action</th>
                    </tr>
                  </thead>
                  <tfoot>
                    <tr>
                      <th class="text-center">STT</th>
                      <th class="text-center">Ngày Yêu Cầu</th>
                      <th class="text-center">Username</th>
                      <th class="text-center">Tên Plan</th>
                      <th class="text-center">Số Coin Thêm</th>
                      <th class="text-center">Txtid</th>
                      <th class="text-center">Email</th>
                      <th class="text-center" width="30%">Action</th>
                    </tr>
                  </tfoot>
                  <tbody>
                    <?php 
                      $arrayData  =   getDataRegister();
                      foreach($arrayData as $key => $value):
                    ?>
                      <tr>
                        <td class="text-center"><?php echo $key+1; ?></td>
                        <td class="text-center"><?php echo $value['ngay_yeu_cau']; ?></td>
                        <td class="text-center"><?php echo $value['username']; ?></td>
                        <td class="text-center"><?php echo strtoupper($value['ten_plan']); ?></td>
                        <td class="text-center"><?php echo $value['so_coin_them']; ?></td>
                        <td class="text-center"><?php echo $value['txtid']; ?></td>
                        <td class="text-center"><?php echo $value['email']; ?></td>
                        <td class="text-center">
                          <?php
                            if($value['admin_approve'] == 0) {
                              echo '<a href="'.siteUrl().'/admin_new/request_add_coin.php?id_dang_ky='.$value['id'].'" class="btn btn-primary" role="button" aria-pressed="true">Xác Nhận</a>';
                            } else if($value['admin_approve'] == 1){
                              echo '<a href="#" class="btn btn-primary disabled" role="button" aria-pressed="true">Đã Xác Nhận</a>';
                            }
                          ?>
                        </td>
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