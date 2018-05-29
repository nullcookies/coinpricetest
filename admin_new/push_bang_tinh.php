<?php
#!/usr/local/bin/php
    require_once __DIR__ . "/functions/push_google_bang_tinh.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>Push Bảng Tính - Quản lý dự án Pos TeamTa</title>
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
        <li class="breadcrumb-item active">Push Bảng Tính</li>
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
              <i class="fa fa-table"></i> Push Bảng Tính</div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                  <thead>
                    <tr>
                      <th class="text-center">STT</th>
                      <th class="text-center">Tên Sheet</th>
                      <th class="text-center">Cập nhật</th>
                      <th class="text-center" width="30%">Status</th>
                    </tr>
                  </thead>
                  <tfoot>
                    <tr>
                      <th class="text-center">STT</th>
                      <th class="text-center">Tên Sheet</th>
                      <th class="text-center">Cập nhật</th>
                      <th class="text-center">Status</th>
                    </tr>
                  </tfoot>
                  <tbody>
                    <tr>
                      <td class="text-center">1</td>
                      <td class="text-center">Bảng Users</td>
                      <td class="text-center"><a class="btn btn-primary" href="<?php echo siteUrl(); ?>/admin_new/push_bang_tinh.php?import_users=yes">Cập Nhật</a></td>
                      <td class="text-center">
                        <?php
                          if(isset($_GET['import_users'])) {
                            $status   =   updateUserSheet();
                            if($status == true) {
                              echo '<div class="alert alert-info" role="alert">Cập nhật bảng User thành công</div>';
                            }
                          }
                        ?>
                      </td>
                    </tr>
                    <?php
                      $result   =   '';
                      if(isset($_GET['import_plan'])) {
                        if(isset($_GET['other_request'])) {
                          $trangThai  = callUpdatePlans($_GET['import_plan'], $_GET['other_request']);
                          $result     = '<div class="alert alert-info" role="alert">'.$trangThai.'</div>';
                        } else {
                          $trangThai  = callUpdatePlans($_GET['import_plan'], 'all');
                          $result     = '<div class="alert alert-info" role="alert">'.$trangThai.'</div>';
                        }
                      }
                      $arrayPlans   = getDbPlans();
                      foreach($arrayPlans as $key => $value) :
                    ?>
                    <tr>
                      <td class="text-center"><?php echo $key+2; ?></td>
                      <td class="text-center">Bảng <?php echo ucfirst($value['ten_plan']); ?></td>
                      <td class="text-center">
                        <a class="btn btn-primary" href="<?php echo siteUrl(); ?>/admin_new/push_bang_tinh.php?import_plan=<?php echo $value['ten_plan']; ?>">Cập Nhật Tất Cả</a>
                        <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                          <div class="btn-group" role="group">
                            <button id="btnGroupDrop1" type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              Cập Nhật Khác
                            </button>
                            <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                              <a class="dropdown-item" href="<?php echo siteUrl(); ?>/admin_new/push_bang_tinh.php?import_plan=<?php echo $value['ten_plan']; ?>&other_request=ho_ten">Họ Tên</a>
                              <a class="dropdown-item" href="<?php echo siteUrl(); ?>/admin_new/push_bang_tinh.php?import_plan=<?php echo $value['ten_plan']; ?>&other_request=so_dao_pos">Số Đào PoS</a>
                              <a class="dropdown-item" href="<?php echo siteUrl(); ?>/admin_new/push_bang_tinh.php?import_plan=<?php echo $value['ten_plan']; ?>&other_request=so_vi">Số Ví</a>
                              <a class="dropdown-item" href="<?php echo siteUrl(); ?>/admin_new/push_bang_tinh.php?import_plan=<?php echo $value['ten_plan']; ?>&other_request=tai_rut">Tái/Rút</a>
                            </div>
                          </div>
                        </div>
                      </td>
                      <td class="text-center"> 
                      <?php if(isset($_GET['import_plan']) && $_GET['import_plan'] == $value['ten_plan']) { echo $result; } ?>
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