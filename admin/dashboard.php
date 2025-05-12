<?php

##########################################################

#####     CREATED BY   : rajeshkumar.maurya@gmail.com    ######

#####          CREATION DATE: PUT DATE              ######

#####     CODE BRIEFING: PUT THE PAGE FUNCTIONALITY ######

#####                                              ######

#####     COMPANY           : baratotravels    ######

#####                                               ######

##########################################################

include_once("session.php");

include_once("../config.inc.php");

include_once("../functions/functions.php");

include_once("../functions/functions.inc.php");

include_once("../includes/db.inc.php");

?>

<?php  include_once("includes/header.php"); ?>

<?php  include_once("includes/menu.php"); ?>

  <div class="content-wrapper">

    <!-- Content Header (Page header) -->

    <div class="content-header">

      <div class="container-fluid">

        <div class="row mb-2">

          <!-- /.col -->

          <!-- /.col -->

        </div><!-- /.row -->

      </div><!-- /.container-fluid -->

    </div>

    <!-- /.content-header -->



    <!-- Main content -->

    <section class="content">
       
      <div class="container-fluid">

        <!-- Info boxes -->

        <div class="row">

          

          <!-- /.col -->

          <div class="col-12 col-sm-6 col-md-6">

            <div class="info-box mb-3">

              <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-address-book"></i></span>



              

              <!-- /.info-box-content -->

            </div>

            <!-- /.info-box -->

          </div>

          <!-- /.col -->



          <!-- fix for small devices only -->

          <div class="clearfix hidden-md-up"></div>



          <div class="col-12 col-sm-6 col-md-6">

            <div class="info-box mb-3">

              <span class="info-box-icon bg-success elevation-1"><i class="fas fa-address-card"></i></span>



              <div class="info-box-content">

                <span class="info-box-text">Inquiry</span>

                <span class="info-box-number"><?php $inquiry_qry = "select count(*) as cnt from inquiry where transaction_type= 'inquiry'";

				  $db->query( $inquiry_qry );

                  if( $db->numRows() > 0 ) { $data_inquiry_qry = $db->fetchArray(); echo $data_inquiry_qry['cnt'];}else{ echo '0';} ?></span>

              </div>

              <!-- /.info-box-content -->

            </div>

            <!-- /.info-box -->

          </div>

          <!-- /.col -->

          

            <!-- /.info-box -->

          </div>

          <!-- /.col -->

        </div>

        <!-- /.row -->

       

        <!-- Main row -->

        <div class="row">

          <!-- Left col -->

          <?php if($_SESSION['admin_user']['type']==0){?>

          <div class="col-md-8">

            <!-- MAP & BOX PANE -->

            

            <!-- /.card -->

            <div class="row">              

              <!-- /.col -->



              

              <!-- /.col -->

            </div>

            <!-- /.row -->



            <!-- TABLE: LATEST ORDERS -->

            

            <!-- /.card -->

          </div>

          <!-- /.col -->



          

          <?php } ?>

          <!-- /.col -->

          

          <div class="col-md-12">

          	<div class="card">

              <div class="card-header border-transparent">

                <h3 class="card-title">Latest Transactions</h3>



                <div class="card-tools">

                  <button type="button" class="btn btn-tool" data-widget="collapse">

                    <i class="fas fa-minus"></i>

                  </button>

                  <button type="button" class="btn btn-tool" data-widget="remove">

                    <i class="fas fa-times"></i>

                  </button>

                </div>

              </div>

              <!-- /.card-header -->

              <div class="card-body p-0">

                <div class="table-responsive">

                  <table class="table m-0">

                    <thead>

                      <th>Name</th>

                      <th>Mobile</th>

                      <th>Email ID</th>

                      <th>City</td>

                      <th>Country</th>

                      <th>Register As</th>
                      <th>Amount(NPR)</th>
                      <th>Trans. Refid</th>
                      <th>Status</th>

                    </tr>

                    </thead>

                    <tbody>

                      <?php

                   $qryStr = array(); ######## USED TO PASS AS QUERY STRING

                  $qry = "select * from inquiry  order by id Desc limit 5";

				  $db->query( $qry );

                  if( $db->numRows() > 0 ) {

                         $i=1;

							 while($data = $db->fetchArray()) {

						 

                  ?>

                    <tr>

                  <td><?php if($data['name']!=''){echo $data['name'];}else{echo '--';}?></td>
                  <td><?php if($data['mobile']!=''){echo $data['mobile'];}else{echo '--';}?></td>
                  <td><?php if($data['email']!=''){echo $data['email'];}else{echo '--';}?></td>
                  <td><?php if($data['city']!=''){echo $data['city'];}else{echo '--';}?></td>
                  <td><?php if($data['country']!=''){echo $data['country'];}else{echo '--';}?></td>
                  <td><?php if($data['customer']!=''){echo ucwords($data['customer']);}else{echo '--';}?></td>
                  <td><?php if($data['customer']=='visitor'){ echo $data['amount'];}
                      else if($data['customer']=='exhibitor'){echo $data['amount'];}
                      else if($data['customer']=='awardnominee'){echo $data['amount'];}else{echo '--';}?></td>
                      <td><?php if($data['transaction_refId']!=''){echo $data['transaction_refId'];}else{echo '--';}?></td>
                  <td><?php if($data['q']=='su'){echo 'success';}else if($data['q']=='fu'){echo 'Failed';}else{echo'--';}?></td>
                    </tr>

                       <?php } ?>

               <?php

                  } else {

                          echo "<tr bgcolor='White'><td colspan='8' class='error-message' align='center'>NO RECORD FOUND!!!</td></tr>";

                  }

                  ?>

                    </tbody>

                  </table>

                </div>

                <!-- /.table-responsive -->

              </div>

              <!-- /.card-body -->

              <div class="card-footer clearfix">

                

                <a href="inquiry.php" class="btn btn-sm btn-secondary float-right">View All Transaction</a>

              </div>

              <!-- /.card-footer -->

            </div>

          </div>

          

          

        </div>

        <!-- /.row -->

      </div><!--/. container-fluid -->

    </section>

    

    

    

    <!-- /.content -->

  </div>

<?php  include_once("includes/footer.php"); ?>

