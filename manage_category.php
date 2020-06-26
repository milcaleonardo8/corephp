<?php
include("connect.php");
$db->checkAdminLogin();

$ctable 	= "category";
$ctable1 	= "Category";
$main_page 	= "product page"; //for sidebar active menu
$page_title = "Manage ".$ctable1;
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo $page_title?> | <?php echo ADMINTITLE; ?></title>
		<!-- DataTables -->
		<link href="<?php echo ADMINURL;?>plugins/datatables/jquery.dataTables.min.css" rel="stylesheet" type="text/css"/>
		<link href="<?php echo ADMINURL;?>plugins/datatables/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css"/>
		<?php include('include_css.php'); ?>
	</head>
    <body class="fixed-left">
       
        <!-- Begin page -->
        <div id="wrapper">
            <!-- Top Bar Start -->
			<?php include("header.php"); ?>
            <div class="content-page">
                <!-- Start content -->
                <div class="content">
                    <div class="container">
						<div class="row">
							<div class="col-xs-12">
								<div class="page-title-box">
                                    <h4 class="page-title"><?php echo $page_title?> </h4>
                                    <div class="clearfix"></div>
                                </div>
							</div>
						</div>
					
						<div class="row">
                            <div class="col-sm-12">
                                <div class="card-box table-responsive">
									<form action="#" onSubmit="return searchByName();">
										<table cellpadding="10">
											<tr>
												<td>Search : </td>
												<td>
													<input type="text" name="searchName" id="searchName" value="" />&nbsp;
												</td>
												<td>
													<input class="btn btn-danger btn-sm m-l-10" type="submit" value="search">
													<input class="btn btn-success btn-sm" type="button" value="clear" onClick="clearSearchByName();">
												</td>
											</tr>
										</table>
									</form>
									<div class="loading-div" style="display:none;">
										<div><img style="width:10%" src="<?php echo SITEURL?>images/loader.svg"></div>
									</div>
									<div id="results"></div>
                                </div>
                            </div>
                        </div>
                    </div> 
                </div>
				<?php include("footer.php"); ?>
            </div>
        </div>
		<?php include('include_js.php'); ?>
		<script src="<?php echo ADMINURL?>plugins/datatables/jquery.dataTables.min.js"></script>
		<script src="<?php echo ADMINURL?>plugins/datatables/dataTables.bootstrap.js"></script>
		<script type="text/javascript">
			var searchName="";
			function searchByName(){
				searchName = $("#searchName").val();
				displayRecords(10,1);
				return false;
			}
			function clearSearchByName(){
				searchName = "";
				$("#searchName").val("");
				displayRecords(10,1);
			}
			$("#searchName").keyup(function(event){
				if(event.keyCode == 13){
					$("#searchByName").click();
				}
			});
			function loadDataTable(data_url,page=""){
				setTimeout(function(){
					$("#results" ).load( data_url,{"page":page},function(){
						$('#example').DataTable({
							"bPaginate": false,
							"bFilter": false,
							"bInfo": false,
							"bAutoWidth": false, 
							"aoColumns": [
								{ "sWidth": "20%" },
								{ "sWidth": "60%" },
								{ "sWidth": "20%","bSortable": false }
							]
						});
						$(".loading-div").fadeOut(500);
						$("#results").fadeIn();
					}); //load initial records
				},1500);
			}
			function displayRecords(numRecords) {

				var searchName 	= $("#searchName").val();
				searchName 		= encodeURIComponent(searchName.trim());
				var data_url 	= "<?php echo ADMINURL?>ajax_get_<?php echo $ctable; ?>.php?show=" + numRecords + "&searchName=" + searchName ;

				$("#results" ).html("");
				$(".loading-div").show();
				loadDataTable(data_url);
				
				//executes code below when user click on pagination links
				$("#results").on( "click", ".paging_simple_numbers a", function (e){
					e.preventDefault();

					var numRecords  = $("#numRecords").val();
					$(".loading-div").show(); //show loading element
					var page 		= $(this).attr("data-page"); //get page number from link

					loadDataTable(data_url,page);
				});
				$("#results").on( "change", "#numRecords", function (e){
					e.preventDefault();

					var numRecords  	= $("#numRecords").val();
					$(".loading-div").show(); //show loading element
					var page 			= $(this).attr("data-page"); //get page number from link

					loadDataTable(data_url,page);
				});
			}

			// used when user change row limit
			function changeDisplayRowCount(numRecords) {
				displayRecords(numRecords, 1);
			}

			$(document).ready(function() {
				displayRecords(10,1);
			});
			
			function del_conf(id){
				var r = confirm("Are you sure you want to delete?");
				if(r){
					window.location.href='<?php echo ADMINURL?>add_<?php echo $ctable; ?>/delete/'+id+'/';
				}
			}
		</script>
	</body>
</html>