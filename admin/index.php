<?php include("../web_services/admin_operations.php");?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Stem | Admin</title>
  <!-- Bootstrap core CSS -->
  <link href="css/bootstrap.min.css" rel="stylesheet" />
  <link href="fontawesome/css/all.css" rel="stylesheet" />
  <link href="css/styles.css" rel="stylesheet" />
  <script type="text/javascript" src="js/jquery-3.3.1.min.js"></script>
  <link rel="stylesheet" href="./morris/morris.css"/>
  <script src="./morris/raphael-min.js"></script>
  <script src="./morris/morris.min.js"></script>
</head>

<body>
  <nav class="navbar navbar-default">
    <div class="container">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar"
          aria-expanded="false" aria-controls="navbar">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span> <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="#">Stem</a>
      </div>
      <div id="navbar" class="collapse navbar-collapse">
        <ul class="nav navbar-nav">
          <li class="active"><a href="index.php">Dashboard</a></li>
          <li><a href="users.php">Users</a></li>
          <li><a href="loans.php">Loans</a></li>
          <li><a href="crb.php">CRB</a></li>
        </ul>

        <ul class="nav navbar-nav navbar-right">
          <li><a href="#">Welcome, Admin</a></li>
          <li><a href="login.php"><i class="fas fa-power-off"></i> Logout</a></li>
        </ul>
      </div>
      <!-- /.nav-collapse -->
    </div>
  </nav>
  <header id="header">
    <div class="container">
      <div class="row">
        <div class="col-md-10">
          <h1>
            <i class="fas fa-cog"></i> Dashboard
            <small>Manage your site</small>
          </h1>
        </div>
        <div class="col-md-2">
          <div class="dropdown create">
            <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown"
              aria-haspopup="true" aria-expanded="true">
              Create content <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
              <li><a type="button" data-toggle="modal" data-target="#addUser">Add User</a></li>
              <li><a href="#">View Loans</a></li>
              <li><a href="#">CRB</a></li>
              <li role="separator" class="divider"></li>
              <li><a href="#">Close</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </header>

  <section id="breadcrumb">
    <div class="container">
      <ol class="breadcrumb">
        <li class="active">Dashboard</li>
      </ol>
    </div>
  </section>

  <section id="main">
    <div class="container">
      <div class="row">
        <div class="col-md-3">
          <div class="list-group">
            <a href="index.php" class="list-group-item active main-color-bg">
              <i class="fas fa-cog"></i> Dashboard
            </a>
            <a href="users.php" class="list-group-item">
              <i class="fas fa-users"></i> Users
              <span class="badge"><?php echo userCount();?></span>
            </a>
            <a href="loans.php" class="list-group-item">
              <i class="fas fa-money-check-alt"></i> Loan Applications
              <span class="badge"><?php echo loanApplicationsCount();?></span>
            </a>
            <a href="crb.php" class="list-group-item">
              <i class="fas fa-exclamation-circle"></i> Stem CRB
              <span class="badge"><?php echo crbMembers();?></span>
            </a>
          </div>

          <div class="well">
            <h4>Profits</h4>
            <div class="progress">
              <div class="progress-bar" role="progressbar" aria-valuenow="73" aria-valuemin="0" aria-valuemax="100"
                style="width: 73%;">
                73%
              </div>
            </div>

            <h4>Returns</h4>
            <div class="progress">
              <div class="progress-bar" role="progressbar" aria-valuenow="64" aria-valuemin="0" aria-valuemax="100"
                style="width: 64%;">
                64%
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-9">
          <div class="panel panel-default">
            <div class="panel-heading main-color-bg">
              <h3 class="panel-title">Overview</h3>
            </div>
            <div class="panel-body">
              <div class="col-md-3">
                <div class="well dash-box">
                  <h2><i class="fas fa-users"></i> <?php echo userCount();?> </h2>
                  <h4> Users </h4>
                </div>
              </div>
              <div class="col-md-3">
                <div class="well dash-box">
                  <h2><i class="fas fa-money-check-alt"></i> <?php echo loanApplicationsCount(); ?> </h2>
                  <h4> Applications </h4>
                </div>
              </div>
              <div class="col-md-3">
                <div class="well dash-box">
                  <h2><i class="fas fa-exclamation-circle"></i> <?php echo crbMembers();?> </h2>
                  <h4> Debtors </h4>
                </div>
              </div>
              <div class="col-md-3">
                <div class="well dash-box">
                  <h2><i class="fas fa-chart-bar"></i> 64% </h2>
                  <h4> Returns </h4>
                </div>
              </div>
            </div>
          </div>

          <div class="panel panel-default">
            <div class="panel-heading main-color-bg">
              <h3 class="panel-title">Charts</h3>
            </div>
            <div class="panel-body">
              <div id="chart" style="height: 250px;"></div>
            </div>
          </div>

          <!-- Latest users -->
          <div class="panel panel-default">
            <div class="panel-heading main-color-bg">
              <h3 class="panel-title">Latest Users</h3>
            </div>
            <div class="panel-body">
            <?php
                fetchLatestUsers();
            ?>
            </div>
          </div>

        </div>
      </div>
    </div>
  </section>


<?php echo getChartData();?>
  <footer class="footer" id="footer">
    <div class="container">
      <p>Copyright Stem, &copy; 2018</p>
    </div>
  </footer>



  <!-- Modals -->

  <!-- Add User Modal -->
  <div class="modal fade" id="addUser" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form action="">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="addUserModalLabel">Add User</h4>
          </div>
          <div class="modal-body">
            <div class="col-sm-12">
              <div class="row">
                <div class="col-sm-6 form-group">
                  <label>First Name</label>
                  <input type="text" placeholder="Enter First Name Here.." class="form-control">
                </div>
                <div class="col-sm-6 form-group">
                  <label>Last Name</label>
                  <input type="text" placeholder="Enter Last Name Here.." class="form-control">
                </div>
              </div>
              <div class="form-group">
                <label>National ID</label>
                <input type="text" placeholder="Enter National ID Here.." class="form-control">
              </div>
              <div class="form-group">
                <label>Phone Number</label>
                <input type="text" placeholder="Enter Phone Number Here.." class="form-control">
              </div>
              <div class="form-group">
                <label>Email Address</label>
                <input type="text" placeholder="Enter Email Address Here.." class="form-control">
              </div>
              <div class="form-group">
                <label>Website</label>
                <input type="text" placeholder="Enter Website Name Here.." class="form-control">
              </div>

            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              <button type="button" class="btn btn-primary main-color-bg">Save</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>


  <!--
      Bootstrap core JavaScript
      ==================================================
    -->
  <!-- Placed at the end of the document so the pages load faster -->
  
  <script>
    Morris.Bar({
      element: 'chart',
      data: [<?php echo getChartData();?>],
      xkey: 'LendDate',
      ykeys: ['Loan', "Paid_Amount", "Balance"],
      labels: ['Loan', "Paid_Amount", "Balance"],
      stacked: true
    });
  </script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
</body>

</html>