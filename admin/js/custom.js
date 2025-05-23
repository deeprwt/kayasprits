$(document).ready(function(){
	$('.btnShowAssets').click(function(){
		$('#assetsList').html('');
		// =============== Modified by @gieart_dotcom ===========
        $.ajax({
                type : 'GET',
                url : SERVER + 'admin/assets/browse_assets',
                success : function (images){
                	$('#assetsList').html(images);
                }
             });
        // =======================================================
	})
});


function setFeaturedImage(path){
	var asset_path = path.replace(BASE_URI,"");
   	$('#featured_image').val(asset_path);
   	
   	$('.preview_featured_image').html('<img src="'+path+'" class="img-responsive thumbnail" onclick="removeFeaturedImage()" style="width:150px;height:150px;cursor:pointer"/>');
}

function removeFeaturedImage(){
	$('#featured_image').val('');
	$('.preview_featured_image').html('');
}

browseAsset = function(page){
	$('#assetsList').html('');
	// =============== Modified by @gieart_dotcom ===========
    $.ajax({
            type : 'GET',
            url : SERVER + 'admin/assets/browse_assets?page='+page,
            success : function (images){
            	$('#assetsList').html(images);
            }
    });
    // =======================================================
}

function ajax_call(data, loadDataToDiv) {
                $("#"+loadDataToDiv).html('<option selected="selected">-- -- -- Loding Data -- -- --</option>');
                if(loadDataToDiv=='ajax-state'){
                    $('#ajax-city').html('');
                    $('#ajax-state').html('');                    
                }
                if(loadDataToDiv=='ajax-city'){
                    $('#ajax-city').html('');
                }
                $.post(BASE_URI + 'getAjaxLocation', data, function(result) {
                    $('#' + loadDataToDiv).html(result);
                });
            }