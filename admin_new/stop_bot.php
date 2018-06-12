<?php
include __DIR__.'/vendor/autoload.php';
require_once __DIR__ . "/functions/get_google_data.php";
use Retrinko\Ini\IniFile;
use Retrinko\Ini\IniSection;
    // Load ini file
    $iniFile = IniFile::load((__DIR__ . '/config.ini'));
    
    // Get ini file contents as array
    $arrayIni = $iniFile->toArray();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>On/Off Telegram Bot - Quản lý dự án Pos TeamTa</title>
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
        <li class="breadcrumb-item active">On-Off Telegram Bot</li>
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
              <i class="fa fa-table"></i> On-Off Telegram Bot</div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                  <thead>
                    <tr>
                      <th class="text-center">STT</th>
                      <th class="text-center">Bật Telegram Bot</th>
                      <th class="text-center">Tắt Telegram Bot</th>
                      <th class="text-center">Trạng Thái</th>
                    </tr>
                  </thead>
                  <tfoot>
                    <tr>
                      <th class="text-center">STT</th>
                      <th class="text-center">Bật Telegram Bot</th>
                      <th class="text-center">Tắt Telegram Bot</th>
                      <th class="text-center">Trạng Thái</th>
                    </tr>
                  </tfoot>
                  <tbody>
                    <tr>
                      <td class="text-center">1</td>
                      <td class="text-center"><a class="btn btn-success" href="<?php echo siteUrl(); ?>/admin_new/stop_bot.php?bot_status=1">Bật Telegram Bot</a></td>
                      <td class="text-center"><a class="btn btn-danger" href="<?php echo siteUrl(); ?>/admin_new/stop_bot.php?bot_status=0">Tắt Telegram Bot</a></td>
                      <td class="text-center">
                        <?php
                          
                          if(isset($_GET['bot_status'])) {
                            $requestActive  =   $_GET['bot_status'];
                            // Create new IniFile instance
                            $iniFile = new IniFile();
                            $section = new IniSection('telegram_bot');
                            //$section->set(['open_bot'=>$requestActive]);
                            $section->set('open_bot', $requestActive);
                            $iniFile->addSection($section);
                            $result   =   $iniFile->save(__DIR__.'/config.ini');
                            echo '<div class="alert alert-info" role="alert"><p>Cập nhật thành công</div>';
                          }
                        ?>
                      </td>
                    </tr>
                    <tr>
                      <td class="text-center" colspan="4">
                        <?php
                          $statusBot    =   $arrayIni['telegram_bot']['open_bot'];
                          if($statusBot   ==  1) {
                            $classAlert   =   'info';
                          } else if($statusBot   ==  0) {
                            $classAlert   =   'danger';
                          }
                        ?>
                        <div class="alert alert-<?php echo $classAlert; ?>" role="alert">
                          <?php
                            if($statusBot   ==  1) {
                              echo 'Đang <b>bật</b> telegram Bot';
                            } else if($statusBot   ==  0) {
                              echo 'Đang <b>tắt</b> telegram Bot';
                            }
                          ?>
                        </div>
                      </td>
                    </tr>
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