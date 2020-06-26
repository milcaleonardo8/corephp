<?php
include("connect.php");
$db->checkAdminLogin();

$ctable 		= "category";
$ctable1 		= "Category";
$main_page 		= "product page"; //for sidebar active menu
$page_title 	= ucwords($_REQUEST['mode'])." ".$ctable1;

$cate_id = $name = $slug = "";

if(isset($_REQUEST['submit'])){

	$name 					= $db->clean($_REQUEST['name']);
	$slug					= $db->createSlug($_REQUEST['name']);
	
	if(isset($_REQUEST['mode']) && $_REQUEST['mode']=="add")
	{
		$dup_where 	= "slug = '".$slug."' AND isDelete=0";
		$r 			= $db->dupCheck($ctable,$dup_where);
		if($r){
			$_SESSION['MSG'] 	= "Duplicate";
			$db->location(ADMINURL."add_category/add/");
			exit;
		}else{
			$rows 	= array(
						"name",
						"slug",
					);
			$values = array(
						$name,
						$slug,
					);
			$last_id =  $db->insertData($ctable,$values,$rows);

			$_SESSION['MSG'] 	= "Inserted";
			$db->location(ADMINURL."manage_category/");
			exit;
		}
		
	}
	else if(isset($_REQUEST['mode']) && $_REQUEST['mode']=="edit")
	{    
		$dup_where 	= "slug = '".$slug."' AND id!='".$_REQUEST['id']."' AND isDelete=0";
		$r 			= $db->dupCheck($ctable,$dup_where);

		if($r)
		{
			$_SESSION['MSG'] 	= "Duplicate";
			$db->location(ADMINURL."add_category/add/");
			exit;
		}
		else
		{
			$rows 	= array(
					"name" 		  => $name,
					"slug" 		  => $slug,
				);

			$where	= "id='".$_REQUEST['id']."' AND isDelete=0";
			$db->updateData($ctable,$rows,$where);
			
			$_SESSION['MSG'] = "Updated";
			$db->location(ADMINURL."manage_category/");
			exit;
		}
	}
}

if(isset($_REQUEST['id']) && $_REQUEST['id']>0 && $_REQUEST['mode']=="edit")
{
	$where 		= " id='".$_REQUEST['id']."' AND isDelete=0";
	$ctable_r 	= $db->getData($ctable,"*",$where);
	$ctable_d 	= @mysqli_fetch_array($ctable_r);
	
	$name		= $ctable_d['name'];
}

if(isset($_REQUEST['id']) && $_REQUEST['id']>0 && $_REQUEST['mode']=="delete")
{
	$id 	= $_REQUEST['id'];
	$rows 	= array("isDelete" => "1");
	
	$db->updateData($ctable,$rows,"id='".$id."'");
	
	$_SESSION['MSG'] 	= "Deleted";
	$db->location(ADMINURL."manage_category/");
	exit;
}
?>
<!DOCTYPE html>
<html>
	<head>
	<title><?php echo $page_title; ?> | <?php echo ADMINTITLE; ?></title>
	<?php include("include_css.php"); ?>
	</head>
	<body class="fixed-left">
		<div id="wrapper">	
			<?php include("header.php"); ?>
			<div class="content-page">
				<div class="content">
					<div class="container">
						<div class="row">
							<div class="col-xs-12">
								<div class="page-title-box">
									<h4 class="page-title"> <?php echo $page_title; ?> </h4>
									<div class="clearfix"></div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-12">
								<div class="card-box">
									<div class="row">
										<div class="col-md-6">
											<form role="form" name="frm" id="frm" action="." method="post">
												<input type="hidden" name="mode" id="mode" value="<?php echo $_REQUEST['mode']; ?>">
												<input type="hidden" name="id" id="id" value="<?php echo $_REQUEST['id']; ?>">

												<div class="form-group">
													<label for="name">Name <code>*</code></label>
													<input type="text" class="form-control" value="<?php echo $name; ?>" id="name" name="name">
												</div>
												
												<button type="submit" name="submit" id="submit" class="btn btn-primary waves-effect w-md waves-light">Submit</button>
												
												<button type="button" class="btn btn-inverse waves-effect w-md waves-light" onClick="window.location.href='<?= ADMINURL ?>manage_category/'">Back</button>
											</form>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php include("footer.php"); ?>
			</div>
		</div>
		<?php include('include_js.php'); ?>
		<script>
			$(function(){
				$("#frm").validate({
					rules: {
						name:{required : true}
					},
					messages: {
						name:{required:"Please enter category name."}
					}
				});
			});
		</script>
	</body>
</html>