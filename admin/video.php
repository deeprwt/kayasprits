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
      <div class="container-fluid">
           <?php

if($_POST['add']=='new') {
  if(!empty($_FILES['video_image']['name'])) {
		   $vidio_image=$_FILES['video_image']['name'];
           $video_image =$_SERVER['DOCUMENT_ROOT'] . "/ppd/upload/" . $video_image;        
           move_uploaded_file($_FILES['video_image']['tmp_name'], $target_path);
           }else{
		   $video_image='no-image.png';
		   }

                  $insertArr = array("video_url"                        =>  putData( $_POST['video_url'] ),
				                    
									// "video_image"                        =>  $vidio_image,
				                     "user_id"                     =>   putData($_SESSION['admin_user']['id']),
                                     "status"                  	=>  1								
                                     );
        // print_r($insertArr); die();
         $whereClause = " video_url='".putData($_POST['video_url'])."' ";
         if(matchExists(video, $whereClause) ) {
          echo $errorMsg = "<div class='warning-message'>".removeSlashesFrom( $_POST['video_url'] )." already exists. Please re-enter details!</div>";
        } else {
          $last_insert_id =  insertData($insertArr, video);
		  if($last_insert_id>0){
          echo $errorMsg = "<div align=center class=notice-message>Video has been added successfully!</div>";
		  }else{
		  echo $errorMsg = "<div align=center class=notice-message>Video has not been added successfully!</div>";
		  }
			
           // redirect("admin_setting.php?module=page&msg=$errorMsg");
  ###########generate message
        }
        }
	

   if( $_POST['update'] == "Update"  ) {
   
         if(!empty($_FILES['video_image']['name'])) {
		   $video_image=$_FILES['video_image']['name'];
           $target_path =$_SERVER['DOCUMENT_ROOT'] . "/ppd/upload/" . $video_image;        
           move_uploaded_file($_FILES['video_image']['tmp_name'], $target_path);
           }else{
		   $video_image=$_POST['video_image_hidden'];
		   }

                  $updateArr = array("video_url"                        =>  putData( $_POST['video_url'] ),
				                     
									// "video_image"                        =>  $video_image,
				                     "user_id"                     =>   putData($_SESSION['admin_user']['id']),
                                    
                                     "status"                  	=>  1      );
                
                $whereClause = "id = '".$_POST['id']."' ";
                updateData($updateArr, 'video', $whereClause);
               echo $errorMsg = "<div class='notice-message'>Details updated successfully!</div> ";
			 // print_r($updateArr); die();
                ###########generate message
}

?>
      <div class="card">
            <div class="card-header">
              <h3 class="card-title float-left">Video</h3>
              
              <button type="button" class="btn btn-success float-right" data-toggle="modal" data-target="#myModal"><i class="fas fa-plus"></i> Add Video</button>
  
  <!-- Modal -->
  <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <form name="frm" id="js-video" action="video.php" method="post" enctype="multipart/form-data" />
     <input type="hidden" name="add" value="new">
      <div class="modal-content">
        
        <div class="modal-body">
          
                 
                  <!-- <div class="form-group col-md-12 float-left">
                        <label>Video Preview Image(Size:295X195)</label>
                        <input type="file" name="video_image" id="video_image" />
                  </div>-->
                  
          <div class="form-group col-md-12 float-left">
                        <label>Enter Video Url</label>
                        <input type="text" name="video_url" id="video_url" class="form-control required" placeholder="Enter Video Url ...">
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
              <table id="video-list" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>S. No.</th>
                   <?php
				  if($_SESSION['admin_user']['type']==0){?>
                  <th>User</th>
                  <?php  } ?>
                  
                  <th>Video Url</th>
                  <th>Action</th>
                </tr>
                </thead>
                <tbody>
                  <?php
                   $qryStr = array(); ######## USED TO PASS AS QUERY STRING
				    if($_SESSION['admin_user']['type']==0){
					$qry = "select * from video where user_id='".$_SESSION['admin_user']['id']."' order by id desc";
					}else{
					$qry = "select * from video where user_id='".$_SESSION['admin_user']['id']."' order by id desc";
					}
					
				  $db->query( $qry );
                  if( $db->numRows() > 0 ) {
                         $i=1;
							 while($data = $db->fetchArray()) {
						 
                  ?>
                <tr>
                  <td><?php echo $i++;?></td>
                   <?php if($_SESSION['admin_user']['type']==0){?>
                  <td><?php echo userName($data['user_id']);?></td>
                  <?php } ?>
                  <td><?=$data['video_url'];?></td>
                  
                  <td> <!--  ********************************** edit Modal *************************** -->
                  <div class="modal fade" id="editModal<?=$data['id'];?>" role="dialog">
    <div class="modal-dialog">
     <form name="frmedit" class="jseditvideo" action="video.php" method="post" enctype="multipart/form-data" />
      <input type="hidden" name="id" value="<?php echo $data['id'];?>">
      <input type="hidden" name="update" value="Update">
      <!-- Modal content-->
      <div class="modal-content">
        
        <div class="modal-body">
                  
                   <!--<div class="form-group col-md-12 float-left">
                        <label>Video Preview Image</label>      
                        <input type="hidden" name="video_image_hidden"  value="<?=$data['video_image'];?>"/>           
                       <img  src="<?php echo '../upload/'.$data['video_image'];?>" width="50" height="50">
                        <input type="file" name="video_image" id="video_image" />(Size:295X195)
                  </div>-->
    
                  <div class="form-group col-md-12 float-left">
                        <label>Enter Video Url</label>
                        <input type="text" name="video_url" id="video_url" class="form-control required" value="<?=$data['video_url'];?>" placeholder="Enter Video Url ...">
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
                  <!-- ************************************* END ***********************************--> <button type="button" class="btn btn-success"  data-toggle="modal" data-target="#editModal<?=$data['id'];?>"><i class="fas fa-edit"></i> Edit</button> <button type="button" class="btn btn-danger" onclick="return confirmDelete(<?php echo $data['id'];?>);"><i class="fas fa-times"></i> Delete</button></td>
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
<script>
  $(function () {
  //  $("#example1").DataTable();
    var table =$('#video-list').DataTable({
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
	$("#js-video").validate({ 
	           errorClass: 'customErrorClass',	});
			 			   
	$(".jseditvideo").validate({ 
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
        data:{'id':id,'module':'video'},
        success: function(data) {		  
          alert('Record has been delete.');
		  $("#video-list").load(window.location + " #video-list");
          // console.log(data);
        },
        error: function(data) {
            alert('error');
			$("#video-list").load(window.location + " #video-list");
            //console.log(url);
        }
    });
	
 }
</script>