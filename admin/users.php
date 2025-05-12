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

  <!-- /.navbar -->

<?php  include_once("includes/header.php"); ?>

  <!-- Main Sidebar Container -->

<?php  include_once("includes/menu.php"); ?>



<!-- Content Wrapper. Contains page content -->

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

     <?php



if($_POST['add']=='new') {



                  $insertArr = array("username"                     =>   putData($_POST['name']),

                                     "contact_number"                   	=>  putData( $_POST['mobile'] ),

									 "email"                  		=>  putData( $_POST['email'] ),

									 "password"                  	=>  putData( $_POST['password'] ),

                                     "address"                  	=>  putData( $_POST['address'] )							

                                     );

        

         $whereClause = " email='".putData($_POST['email'])."' ";

		  

         if(matchExists(admin, $whereClause) ) {

          echo $errorMsg = "<div class='warning-message'>".removeSlashesFrom( $_POST['email'] )." already exists. Please re-enter details!</div>";

        } else {

          $last_insert_id =  insertData($insertArr, admin);

		  // print_r($insertArr); die();

		  if($last_insert_id>0){

          echo $errorMsg = "<div align=center class=notice-message>User has been added successfully!</div>";

			

			  }else{

			 echo $errorMsg = "<div align=center class=notice-message>Error:User has been not been added!</div>";

			  }

           // redirect("admin_setting.php?module=page&msg=$errorMsg");

  ###########generate message

        }

        }

		

		



   if( $_POST['update'] == "Update"  ) {



                  $updateArr = array("name"                        =>  putData( $_POST['name'] ),

				                    "username"                     =>   putData($_POST['name']),

				                    "contact_number"                   	=>  putData( $_POST['mobile'] ),

									 "email"                  		=>  putData( $_POST['email'] ),

									 "password"                  	=>  putData( $_POST['password'] ),

                                     "address"                  	=>  putData( $_POST['address'] )

                             );



                $whereClause = "id = '".$_POST['id']."' ";

                updateData($updateArr, 'admin', $whereClause);

               echo $errorMsg = "<div class='notice-message'>Details updated successfully!</div> ";

                ###########generate message

}



?>



      <div class="container-fluid">

     

      <div class="card">

            <div class="card-header">

              <h3 class="card-title float-left">Users</h3>

              


              <div class="modal fade" id="myModal" role="dialog">

    <div class="modal-dialog">

     <form name="frm" id="js-enquiry" action="users.php" method="post" />

     <input type="hidden" name="add" value="new">

      <!-- Modal content-->

      <div class="modal-content">

        

        <div class="modal-body">

              

          		<div class="form-group">

                        <label>Name*</label>

                        <input type="text" class="form-control required" name="name"  placeholder="Enter Name">

                  </div>

                  <div class="form-group">

                        <label>Mobile*</label>

                        <input type="text" class="form-control required" name="mobile" placeholder="Enter Mobile Number">

                  </div>

                  <div class="form-group">

                        <label>Email ID*</label>

                        <input type="text" class="form-control required" name="email" placeholder="Enter Email ID">

                  </div>

                  <div class="form-group">

                        <label>Password*</label>

                        <input type="password" class="form-control required" name="password" placeholder="Enter password">

                  </div>

                  <div class="form-group">

                        <label>Address*</label>

                        <textarea class="form-control required" rows="3" name="address" id="address"></textarea>

                  </div>

        </div>

        <div class="modal-footer">

        	<button type="submit" name="submit" class="btn btn-success" >Add</button>

          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>

        </div>

      </div>

      </form>

      

    </div>

  </div>

  

  

  

            </div>

            <!-- /.card-header -->

            <div class="card-body">

              <table id="user-list" class="table table-bordered table-striped">

                <thead>

                <tr>

                  <th>S. No.</th>

                  <?php

				  if($_SESSION['admin_user']['type']==0){?>

                  <th>User</th>

                  <?php  } ?>

                  <th>Name</th>

                  <th>Mobile</th>

                  <th>Email Id</th>

                  <th>Account Status</th>

                  <th>Action</th>

                </tr>

                </thead>

                <tbody>

                 <?php

                   $qryStr = array(); ######## USED TO PASS AS QUERY STRING

				   if($_SESSION['admin_user']['type']==0){

                  $qry = "select * from admin  order by id desc";

				  }else{

				 $qry = "select * from admin where id='".$_SESSION['admin_user']['id']."' order by id desc";



				  }

				  $db->query( $qry );

                  if( $db->numRows() > 0 ) {

                         $i=1;

							 while($data = $db->fetchArray()) {

						 

                  ?>

                <tr>

                  <td width="10%"><?php echo $i++;?></td>

                 <?php if($_SESSION['admin_user']['type']==0){?>

                  <td width="10%"><?php echo userName($data['id']);?></td>

                  <?php } ?>

                  <td><?=$data['username'];?></td>

                  <td><?=$data['contact_number'];?></td>

                  <td><?=$data['email'];?></td>

                  <td width="35%"><?php if($data['status']==1){ echo 'Active';}else{ echo 'Inactive';}?></td>

                  <td width="25%">

                  <!--  ********************************** edit Modal *************************** -->

                  <div class="modal fade" id="editModal<?=$data['id'];?>" role="dialog">

    <div class="modal-dialog">

     <form name="frmedit" class="jseditenquiry" action="inquiry.php" method="post" />

      <input type="hidden" name="id" value="<?php echo $data['id'];?>">

      <input type="hidden" name="update" value="Update">

      <!-- Modal content-->

      <div class="modal-content">

        

        <div class="modal-body">

              

          		<div class="form-group">

                        <label>Name*</label>

                        <input type="text" class="form-control required" name="name" value="<?=$data['name'];?>" placeholder="Enter Name">

                  </div>

                  <div class="form-group">

                        <label>Mobile*</label>

                        <input type="text" class="form-control required" name="mobile" value="<?=$data['mobile'];?>" placeholder="Enter Mobile Number">

                  </div>

                  <div class="form-group">

                        <label>Email ID*</label>

                        <input type="text" class="form-control required" name="email" value="<?=$data['email'];?>" placeholder="Enter Email ID">

                  </div>

                  <div class="form-group">

                        <label>Address*</label>

                        <textarea class="form-control required" rows="3" name="details" id="comment"><?=$data['details'];?></textarea>

                  </div>

                  

                  

        </div>

        <div class="modal-footer">

        	<button type="submit" name="edit" class="btn btn-success" value="Update" >Update</button>

          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>

        </div>

      </div>

      </form>

      

    </div>

  </div>

                  <!-- ************************************* END ***********************************-->

                  <a href="carddetails.php?uid=<?=md5($data['id']);?>" class="btn btn-success" > <i class="fas fa-edit"></i> Edit</a></td>

                </tr>

              <?php } ?>

               <?PHP

                  } else {

                          echo "<tr bgcolor='White'><td colspan='8' class='error-message' align='center'>NO RECORD FOUND!!!</td></tr>";

                  }

                  ?>

                </tbody>

                

              </table>

            </div>

            <!-- /.card-body -->

          </div>

        

      </div><!--/. container-fluid -->

    </section>

    

    

    

    <!-- /.content -->

  </div>

  <!-- /.content-wrapper -->



  <!-- Control Sidebar -->

  <aside class="control-sidebar control-sidebar-dark">

    <!-- Control sidebar content goes here -->

  </aside>

  <!-- /.control-sidebar -->



  <!-- Main Footer -->

<?php  include_once("includes/footer.php"); ?>

<!-- ./wrapper -->



<!-- REQUIRED SCRIPTS -->



<!-- jQuery -->

<script src="plugins/jquery/jquery.min.js"></script>

<!-- Bootstrap 4 -->

<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Select2 -->

<script src="plugins/select2/js/select2.full.min.js"></script>

<!-- InputMask -->

<script src="plugins/inputmask/jquery.inputmask.bundle.js"></script>

<script src="plugins/moment/moment.min.js"></script>

<!-- date-range-picker -->

<script src="plugins/daterangepicker/daterangepicker.js"></script>

<!-- bootstrap color picker -->

<script src="plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>

<!-- Tempusdominus Bootstrap 4 -->

<script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>

<!-- FastClick -->

<script src="plugins/fastclick/fastclick.js"></script>

<!-- AdminLTE App -->

<script src="dist/js/adminlte.min.js"></script>

<!-- AdminLTE for demo purposes -->

<script src="dist/js/demo.js"></script>

<!-- DataTables -->

<script src="plugins/datatables/jquery.dataTables.js"></script>

<script src="plugins/datatables/dataTables.bootstrap4.js"></script>

<!-- page script -->

<script>

  $(function () {

  //  $("#example1").DataTable();

    var table =$('#user-list').DataTable({

      "paging": true,

      "lengthChange": true,

      "searching": true,

      "ordering": true,

      "info": true,

      "autoWidth": false,

    });

  });

</script>

<script type="text/javascript" src="js/jquery.validate.min.js"></script>

<script>$().ready(function() {

	$("#js-enquiry").validate({ 

	           errorClass: 'customErrorClass',	});

			 			   

	$(".jseditenquiry").validate({ 

	           errorClass: 'customErrorClass',	});

			   

			   });

</script>



<script language="javascript">



function confirmDelete(id)

    {

		  if(!confirm("Are you sure to delete this Record?") ) { 

		  return false;

		  }

        $.ajax({

        type: "POST",

        url: '<?=$websiteurl?>admin/ajax.php',

        data:{'id':id,'module':'users'},

        success: function(data) {

		  alert('Record has been delete.');
		  $("#user-list").load(window.location + " #user-list");
          // console.log(data);

        },

        error: function(data) {

            alert('error');
 $("#user-list").load(window.location + " #user-list");

            //console.log(url);

        }

    });

	

 }

</script>