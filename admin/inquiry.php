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

                  $insertArr = array("name"                        =>  putData( $_POST['name'] ),
				                     "user_id"                     =>   putData($_SESSION['admin_user']['id']),
                                     "mobile"                   	=>  putData( $_POST['mobile'] ),
									 "email"                  		=>  putData( $_POST['email'] ),
                                     "details"                  	=>  putData( $_POST['details'] )								
                                     );
        
         $whereClause = " email='".putData($_POST['email'])."' ";
         if(matchExists(inquiry, $whereClause) ) {
          echo $errorMsg = "<div class='warning-message'>".removeSlashesFrom( $_POST['email'] )." already exists. Please re-enter details!</div>";
        } else {
          $last_insert_id =  insertData($insertArr, inquiry);
          echo $errorMsg = "<div align=center class=notice-message>Enquiry has been added successfully!</div>";
			  //print_r($insertArr);
           // redirect("admin_setting.php?module=page&msg=$errorMsg");
  ###########generate message
        }
        }
		
		

   if( $_POST['update'] == "Update"  ) {

                  $updateArr = array("name"                        =>  putData( $_POST['name'] ),
				                     "user_id"                     =>   putData($_SESSION['admin_user']['id']),
                                     "mobile"                   	=>  putData( $_POST['mobile'] ),
									 "email"                  		=>  putData( $_POST['email'] ),
                                     "details"                  	=>  putData( $_POST['details'] )
                             );

                $whereClause = "id = '".$_POST['id']."' ";
                updateData($updateArr, 'inquiry', $whereClause);
               echo $errorMsg = "<div class='notice-message'>Details updated successfully!</div> ";
                ###########generate message
}

?>

      <div class="container-fluid">
     
      <div class="card">
            <div class="card-header">
              <h3 class="card-title float-left">All Inquiry List</h3>
              
              <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
     <form name="frm" id="js-enquiry" action="inquiry.php" method="post" />
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
                        <label>Descriptions*</label>
                        <textarea class="form-control required" rows="3" name="details" id="comment"></textarea>
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
              <table id="enquiry-list" class="table table-bordered table-striped display nowrap" style="width:100%">
                <thead>
                <tr>
                  <th>S. No.</th>
                  <th>Date</th>
                  <th>Name</th>
                  <th>Mobile</th>
                  <th>Email Id</th>
                  
                  <th>City</th>
                  <th>State</th>
                  <th>Investment Amount</th>
                  <th>Message</th>  
                  
                </tr>
                </thead>
                <tbody>
                 <?php
                   $qryStr = array(); ######## USED TO PASS AS QUERY STRING

                  $qry = "select * from inquiry  order by id desc";
				  $db->query( $qry );
                  if( $db->numRows() > 0 ) {
                         $i=1;
							 while($data = $db->fetchArray()) {
						 
                  ?>
                <tr>
                  <td width="10%"><?php echo $i++;?></td>
                  <td><?php if($data['created_date']!=''){echo $data['created_date'];}else{echo '--';}?></td>
                  <td><?php if($data['name']!=''){echo $data['name'];}else{echo '--';}?></td>
                  <td><?php if($data['mobile']!=''){echo $data['mobile'];}else{echo '--';}?></td>
                  <td><?php if($data['email']!=''){echo $data['email'];}else{echo '--';}?></td>
                   
                  <td><?php if($data['city']!=''){echo $data['city'];}else{echo '--';}?></td>
                  <td><?php if($data['state']!=''){echo $data['state'];}else{echo '--';}?></td>               
                  <td><?php if($data['investment_range']!=''){echo $data['investment_range'];}else{echo '--';}?></td>
                  <td><?php if($data['message']!=''){echo $data['message'];}else{echo '--';}?></td>
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
<!-- Tempusdominus Bootstrap 4 -->
<script src="https://cdn.datatables.net/buttons/2.0.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.html5.min.js"></script>


<!-- page script -->
<script>
  $(function () {
  //  $("#example1").DataTable();
    var table =$('#enquiry-list').DataTable({
      "paging": true,
       "scrollY": 400,
       dom: 'Bfrtip',
        buttons: ['csv'],
       "aLengthMenu": [[15,25, 50, 100, 500, -1], [15, 25, 50, 100, 500, "All"]],
      "scrollX": true,
      "lengthChange": true,
      "searching": true,
      "ordering": true,
      "info": true
      
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
        data:{'id':id,'module':'inquiry'},
        success: function(data) {
		  table.ajax.reload();
          alert('Record has been delete.');
		 
          // console.log(data);
        },
        error: function(data) {
            alert('error');
			 table.ajax.reload();
            //console.log(url);
        }
    });
	
 }
</script>