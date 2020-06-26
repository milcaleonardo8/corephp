<?php
include("connect.php");
$db->checkAdminLogin();

$ctable 	= "product";
$ctable2 	= "product_details";
$ctable1 	= "Product";

$main_page 	= "product page"; //for sidebar active menu
$page 		= "add_".$ctable;
$page_title = ucwords($_REQUEST['mode'])." ".$ctable1;

$IMAGEPATH_T 	= PRODUCT_MAIN_T;
$IMAGEPATH_A 	= PRODUCT_MAIN_A;
$IMAGEPATH 		= PRODUCT_MAIN;


$name				= "";
$descr				= "";
$pro_status			= "";
$is_best_selling	= "";
$is_fresh_picks		= "";
$is_new				= "";

$pro_cate_id		= "";
$cate_id			= "";
$sub_cate_id		= "";
$price				= "";
$sell_price			= "";
$unit				= "";

$size				= "";

if(isset($_REQUEST['submit']))
{
	// echo "<pre>";
	// print_r($_REQUEST);
	// die();
	if(isset($_SESSION['image_path']) && $_SESSION['image_path']!="")
	{
		copy($IMAGEPATH_T.$_SESSION['image_path'], $IMAGEPATH_A.$_SESSION['image_path']);
		$image_path = $_SESSION['image_path'];
		unlink($IMAGEPATH_T.$_SESSION['image_path']);
		unset($_SESSION['image_path']);
	}

	$name 				= $db->clean($_REQUEST['name']);
	$slug 				= $db->createSlug($_REQUEST['name']);
	
	$descr 				= $db->clean($_REQUEST['descr']);
	$pro_status 		= $db->clean($_REQUEST['pro_status']);
	$is_best_selling 	= $db->clean($_REQUEST['is_best_selling']);
	$is_fresh_picks 	= $db->clean($_REQUEST['is_fresh_picks']);
	$is_new 			= $db->clean($_REQUEST['is_new']);
	
	//for multiple categories
	$pro_cate_id 			= $_REQUEST['pro_cate_id'];
	$cate_id 				= $_REQUEST['cate_id'];
	$sub_cate_id 			= $_REQUEST['sub_cate_id'];
	$price 					= $_REQUEST['price'];
	$sell_price 			= $_REQUEST['sell_price'];
	$unit 					= $_REQUEST['unit'];
	$size 					= $_REQUEST['size'];
	
	if(isset($_REQUEST['mode']) && $_REQUEST['mode']=="add")
	{
		$rows 	= array(
					"name",
					"slug",
					"descr",
					"image_path",
					"pro_status",
					"is_best_selling",
					"is_fresh_picks",
					"is_new",
				);
			
		$values = array(
					$name,
					$slug,
					$descr,
					$image_path,
					$pro_status,
					$is_best_selling,
					$is_fresh_picks,
					$is_new,
				);

		$product_id = $db->insertData($ctable,$values,$rows);

		if($product_id!=0)
		{
			for($i=0;$i < count($pro_cate_id);$i++)
			{
				$rows3	=	array(
							"product_id",
							"pro_cate_id",
							"cate_id",
							"sub_cate_id",
							"price",
							"sell_price",
							"unit",
							"size",
						);
						
				$values3	=	array(
							$product_id,
							$pro_cate_id[$i],
							$cate_id[$i],
							$sub_cate_id[$i],
							$price[$i],
							$sell_price[$i],
							$unit[$i],
							$size[$i],
						);	

				$pro_details = $db->insertData("product_details",$values3,$rows3);
			}
		}

		$_SESSION['MSG'] = "Inserted";
		$db->location(ADMINURL."manage_product/");
		exit;
	}
	else if(isset($_REQUEST['mode']) && $_REQUEST['mode']=="edit")
	{
		if($_REQUEST['old_image_path']!="" && $image_path!=""){
			if(file_exists($IMAGEPATH_A.$_REQUEST['old_image_path'])){
				unlink($IMAGEPATH_A.$_REQUEST['old_image_path']);
			}
		}else{
			if($image_path==""){
				$image_path = $_REQUEST['old_image_path'];
				if($image_path == ""){
					$image_path = "";	
				}
			}
		}

		$product_id = $_REQUEST['id'];

		$rows 	= array(
					"name"				=> $name,
					"slug"				=> $slug,
					"descr"				=> $descr,
					"image_path"		=> $image_path,
					"pro_status"		=> $pro_status,
					"is_best_selling"	=> $is_best_selling,
					"is_fresh_picks"	=> $is_fresh_picks,
					"is_new"			=> $is_new,
				);
		
		$where	= "id=".$_REQUEST['id'];
		$db->updateData($ctable,$rows,$where);

		// $delete_pro = $db->delete("product_details","product_id='".$product_id."'");

		/**/
		$delete_ids = $_REQUEST['delete_product_details_id'];
		$delete_ids = explode(',', $delete_ids);
		foreach ($delete_ids as $key => $value) {
			$del_where	= "id=".$value;
			$del_rows = array("isDelete"=>1);
			$db->updateData("product_details",$del_rows,$del_where);
		}

		$update_ids = $_REQUEST['product_details_ids'];
		$update_cnt = count($update_ids);
		
		for($i=0;$i < count($pro_cate_id);$i++)
		{
			if($i < $update_cnt) {
				foreach ($update_ids as $key => $value) {
					$update_rows =	array(
						"product_id"=>$product_id,
						"pro_cate_id"=>$pro_cate_id[$key],
						"cate_id"=>$cate_id[$key],
						"sub_cate_id"=>$sub_cate_id[$key],
						"price"=>$price[$key],
						"sell_price"=>$sell_price[$key],
						"unit"=>$unit[$key],
						"size"=>$size[$key]
					);
					$update_where = "id=".$value;
					$db->updateData("product_details",$update_rows,$update_where);
				}
			}
			else {	
				$rows3	=	array(
							"product_id",
							"pro_cate_id",
							"cate_id",
							"sub_cate_id",
							"price",
							"sell_price",
							"unit",
							"size",
						);
						
				$values3	=	array(
							$product_id,
							$pro_cate_id[$i],
							$cate_id[$i],
							$sub_cate_id[$i],
							$price[$i],
							$sell_price[$i],
							$unit[$i],
							$size[$i],
						);	
				$pro_details = $db->insert("product_details",$values3,$rows3);
			}

		}
		
		$_SESSION['MSG'] = "Updated";
		$db->location(ADMINURL."manage_product/");
		exit;
	}
}

if(isset($_REQUEST['id']) && $_REQUEST['id']>0 && $_REQUEST['mode']=="edit")
{
	$where 		= "id='".$_REQUEST['id']."' AND isDelete=0";
	$ctable_r 	= $db->getData($ctable,"*",$where);
	$ctable_d 	= @mysqli_fetch_array($ctable_r);

	$name				= stripslashes($ctable_d['name']);
	$descr				= htmlspecialchars_decode($ctable_d['descr']);
	$pro_status			= stripslashes($ctable_d['pro_status']);
	$is_best_selling	= stripslashes($ctable_d['is_best_selling']);
	$is_fresh_picks		= stripslashes($ctable_d['is_fresh_picks']);
	$is_new				= stripslashes($ctable_d['is_new']);
	$image_path			= stripslashes($ctable_d['image_path']);
}
else
{
	$pro_status			= 1;
}

if(isset($_REQUEST['id']) && $_REQUEST['id']>0 && $_REQUEST['mode']=="delete")
{
	$id 	= $_REQUEST['id'];
	$rows 	= array("isDelete" => "1");
	$db->updateData($ctable,$rows,"id='".$id."'");
	
	$rows 	= array("isDelete" => "1");
	$db->updateData("product_details",$rows,"product_id='".$id."'");
	
	$_SESSION['MSG'] = "Deleted";
	$db->location(ADMINURL."manage_product/");
	exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo $page_title; ?> | <?php echo ADMINTITLE; ?></title>
<?php include("include_css.php"); ?>
<link href="<?php echo ADMINURL?>assets/crop/css/demo.html5imageupload.css?v1.3" rel="stylesheet">
</head>
<body class="fixed-left">
<div id="wrapper">
	<?php include("header.php"); ?>
	<?php include("left.php"); ?>
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
							<form role="form" name="frm" id="frm" action="." method="post" enctype="multipart/form-data" onsubmit="return form_submit();">
								<input type="hidden" name="delete_product_details_id" id="delete_product_details_id" value="">
								<div class="row">
									<div class="col-md-6">
										<input type="hidden" name="mode" id="mode" value="<?php echo $_REQUEST['mode']; ?>">
										<input type="hidden" name="id" id="id" value="<?php echo $_REQUEST['id']; ?>">
											
										<div class="form-group">
											<label>Name <code>*</code></label>
											<input type="text" class="form-control" value="<?php echo $name; ?>" id="name" name="name">
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6">
										<div class="form-group">
											<label>Status <code>*</code></label>
											<select name="pro_status" id="pro_status" class="form-control">
												<option value="">Please Select Option</option>
												<option <?php if($pro_status=="1"){ echo "selected";}?> value="1">In Stock</option>
												<option <?php if($pro_status=="2"){ echo "selected";}?> value="2">Out Of Stock</option>
											</select>
										</div>
									</div>
								</div>
								
								<div class="row">
									<div class="col-md-12">		
										<div class="form-group">
											<label>Is Product ?</label>
											<div class="button-list">
												
												<div class="btn-switch btn-switch-success">
													<input type="checkbox" id="input-btn-switch-success" name="is_best_selling" id="is_best_selling" value="1" <?php if($is_best_selling=="1"){ echo "checked";}?>/>
													<label for="input-btn-switch-success"
														   class="btn btn-rounded btn-success waves-effect waves-light">
														<em class="glyphicon glyphicon-ok"></em>
														<strong> Best Selling</strong>
													</label>
												</div>

												<div class="btn-switch btn-switch-info">
													<input type="checkbox" id="input-btn-switch-info" name="is_fresh_picks" id="is_fresh_picks" value="1" <?php if($is_fresh_picks=="1"){ echo "checked";}?>/>
													<label for="input-btn-switch-info"
														   class="btn btn-rounded btn-info waves-effect waves-light">
														<em class="glyphicon glyphicon-ok"></em>
														<strong> Fresh Picks</strong>
													</label>
												</div>

												<div class="btn-switch btn-switch-warning">
													<input type="checkbox" id="input-btn-switch-warning" name="is_new" id="is_new" value="1" <?php if($is_new=="1"){ echo "checked";}?>/>
													<label for="input-btn-switch-warning"
														   class="btn btn-rounded btn-warning waves-effect waves-light">
														<em class="glyphicon glyphicon-ok"></em>
														<strong> New</strong>
													</label>
												</div>
											</div>
										</div>
									</div>
								</div>
								
								
								<div class="row">
									<div class="col-md-6">	
										<div class="form-group">
											<label>Description <code>*</code></label>
											<textarea class="form-control" id="descr" name="descr" rows="3"><?php echo $descr; ?></textarea>
											<div class="desc_error"></div>
										</div>
									</div>
								</div>

								

								<div class="row">
									<div class="col-md-6">
										<div class="form-group">
											<label for="image_path">Image
											<input type="hidden" name="filename" id="filename" class="form-control" />
											</label>
											<small>minimum image size 458 x 304 <code>*</code></small> 

											
													<div id="dropzone" class="dropzone" data-width="458" data-height="304" data-ghost="false" data-originalsize="false" data-url="<?php echo ADMINURL?>crop_product_main.php" style="width: 458px;height:304px;">
													<input type="file" id="image_path" name="image_path">
													<input type="hidden" name="old_image_path" value="<?php echo $image_path; ?>" />
													</div>
												
											
										</div>
									</div>
								</div>

								<div class="row">
									<div class="col-md-6">
										<div class="form-group">
											<input type="hidden" name="filename" id="filename" class="form-control" />
											</label>											
											<?php
											if($image_path!="" && file_exists($IMAGEPATH_A.$image_path)){
											?>
												<img src="<?php echo SITEURL.$IMAGEPATH.$image_path;?>" width="458">
											<?php
											}
											?>	
										</div>
									</div>
								</div>
								
								<br><br>
								<div class="row">
									<div class="col-md-12">
										<div class="filed">
											<?php
											$ctable_c2 = 0;
											$auto_count=1;
											if(isset($_REQUEST['id']) && $_REQUEST['id']>0 && $_REQUEST['mode']=="edit")
											{
												$where2 	= "product_id='".$_REQUEST['id']."' AND isDelete=0";
												$ctable_r2 	= $db->getData("product_details","*",$where2);
												$ctable_c2 	= @mysqli_num_rows($ctable_r2);
											}
											
											if($ctable_c2 > 0)
											{
												while($ctable_d2 = @mysqli_fetch_array($ctable_r2)) 
												{

												$addons_id			= stripslashes($ctable_d2['id']);
												$pro_cate_id		= stripslashes($ctable_d2['pro_cate_id']);
												$cate_id			= stripslashes($ctable_d2['cate_id']);
												$sub_cate_id		= stripslashes($ctable_d2['sub_cate_id']);
												$price				= stripslashes($ctable_d2['price']);
												$sell_price			= stripslashes($ctable_d2['sell_price']);
												$unit				= stripslashes($ctable_d2['unit']);
												$size				= stripslashes($ctable_d2['size']);
												?>
												<input type="hidden" name="product_details_ids[]" data-id="<?php echo $addons_id ?>" value="<?php echo $addons_id ?>">
												<div class="col-md-12 filed_class">
													<div class="col-md-2">
														<div class="form-group">
															<label>Product Category <span class="text-danger">*</span></label>
															<select class="form-control" name="pro_cate_id[]" data-id="f<?php echo $auto_count; ?>" id="pro_cate_id<?php echo $auto_count; ?>" onChange="getCat(this.value,this);">
																<option value="">Select Product Category</option>
																<?php
																$pro_category_r = $db->getData("product_category","*","isDelete=0");
																if(@mysqli_num_rows($pro_category_r)>0)
																{
																while($pro_category_d = @mysqli_fetch_array($pro_category_r))
																{
																?>
																	<option value="<?php echo $pro_category_d['id']; ?>" <?php if($pro_category_d['id']== $pro_cate_id){?> selected <?php } ?>><?php echo $pro_category_d['name']; ?></option>
																<?php
																}
																}
																?>
															</select>
														</div>
													</div>
													<div class="col-md-2">
														<div class="form-group">
															<label>Category</label>
															<select class="form-control" name="cate_id[]" id="cate_id<?php echo $auto_count; ?>" onChange="getSubCat(this.value,this);" data-id="subc<?php echo $auto_count; ?>">
																<option value="">Select Category</option>
																<?php
																$category_r = $db->getData("category","*","isDelete=0 AND pro_cate_id='".$pro_cate_id."'");
																if(@mysqli_num_rows($category_r)>0)
																{
																while($category_d = @mysqli_fetch_array($category_r))
																{
																?>
																	<option value="<?php echo $category_d['id']; ?>" <?php if($category_d['id']== $cate_id){?> selected <?php } ?>><?php echo $category_d['name']; ?></option>
																<?php
																}
																}
																?>
															</select>
														</div>
													</div>
													<div class="col-md-2">
														<div class="form-group">
															<label>Sub Category</label>
															<select class="form-control" name="sub_cate_id[]" id="sub_cate_id<?php echo $auto_count; ?>">
																<option value="">Select Sub Category</option>
																<?php
																$sub_category_r = $db->getData("sub_category","*","isDelete=0 AND cate_id='".$cate_id."'");
																if(@mysqli_num_rows($sub_category_r)>0)
																{
																while($sub_category_d = @mysqli_fetch_array($sub_category_r))
																{
																?>
																	<option value="<?php echo $sub_category_d['id']; ?>" <?php if($sub_category_d['id']== $sub_cate_id){?> selected <?php } ?>><?php echo $sub_category_d['name']; ?></option>
																<?php
																}
																}
																?>
															</select>
														</div>
													</div>
													
													<div class="col-md-1">
														<div class="form-group">
															<label>Price <code>*</code></label>
															<input type="text" class="form-control" value="<?php echo $price; ?>" name="price[]" id="price<?php echo $auto_count; ?>">
														</div>
													</div>

													<div class="col-md-1">
														<div class="form-group">
															<label>Sell Price</label>
															<input type="text" class="form-control" value="<?php echo $sell_price; ?>" name="sell_price[]">
														</div>
													</div>

													<div class="col-md-1">
														<div class="form-group">
															<label>Size</label>
															<input type="text" class="form-control" value="<?php echo $size; ?>" name="size[]" id="size<?php echo $auto_count; ?>">
														</div>
													</div>

													<div class="col-md-2">
														<div class="form-group">
															<label>Unit <code>*</code></label>
															<select name="unit[]" class="form-control" id="unit<?php echo $auto_count; ?>">
																<option value="">Please Select Option</option>
																<option <?php if($unit=="Meter"){ echo "selected";}?> value="Meter">Meter(s)</option>
															</select>
														</div>
													</div>

													<?php 
													if($auto_count==1)
													{?>
													<div class="col-md-1">
														<label>&nbsp;</label>
														<div class="form-group">
															<a type="button" class="btn btn-icon waves-effect waves-light btn-success add_button"><i class="fa fa-plus"></i></a>
														</div>
													</div>
													<?php 
													} 
													else 
													{?>
													<div class="col-md-1">
														<label>&nbsp;</label>
														<div class="form-group">
															<a type="button" data-delete-id="<?php echo $addons_id ?>" class="btn btn-icon waves-effect waves-light btn-danger remove_button"><i class="fa fa-minus"></i></a>
														</div>
													</div>
													<?php 
													}?>
												</div>
												<?php
												$auto_count++;
												}
											}
											else
											{
											?>
											<div class="col-md-12 filed_class">
												<div class="col-md-2">
													<div class="form-group">
														<label>Product Category <span class="text-danger">*</span></label>
														<select class="form-control" name="pro_cate_id[]" data-id="f1" id="pro_cate_id1" onChange="getCat(this.value,this);">
															<option value="">Select Product Category</option>
															<?php
															$pro_category_r = $db->getData("product_category","*","isDelete=0");
															if(@mysqli_num_rows($pro_category_r)>0)
															{
																while($pro_category_d = @mysqli_fetch_array($pro_category_r))
																{
																?>
																<option value="<?php echo $pro_category_d['id']; ?>"><?php echo $pro_category_d['name']; ?></option>
																<?php
																}
															}
															?>
														</select>
													</div>
												</div>
											
												<div class="col-md-2">
													<div class="form-group">
														<label>Category</label>
														<select class="form-control" name="cate_id[]" onChange="getSubCat(this.value,this);" id="cate_id1" data-id="subc1">
															
														</select>
													</div>
												</div>

												<div class="col-md-2">
													<div class="form-group">
														<label>Sub Category</label>
														<select class="form-control" name="sub_cate_id[]" id="sub_cate_id1">
														<option value="">Please select sub category</option>
														</select>
													</div>
												</div>
												
												<div class="col-md-1">
													<div class="form-group">
														<label>Price <code>*</code></label>
														<input type="text" class="form-control" id="price1" name="price[]">
													</div>
												</div>

												<div class="col-md-1">
													<div class="form-group">
														<label>Sell Price</label>
														<input type="text" class="form-control" id="sell_price1" name="sell_price[]">
													</div>
												</div>

												<div class="col-md-1">
													<div class="form-group">
														<label>Size</label>
														<input type="text" class="form-control" id="size1" name="size[]">
													</div>
												</div>

												<div class="col-md-2">
													<div class="form-group">
														<label>Unit <code>*</code></label>
														<select name="unit[]" id="unit1" class="form-control">
															<option value="">Please Select Option</option>
															<option value="Meter">Meter(s)</option>
														</select>
													</div>
												</div>

												<div class="col-md-1">
													<label>&nbsp;</label>
													<div class="form-group">
														<a type="button" class="btn btn-icon waves-effect waves-light btn-success add_button"><i class="fa fa-plus"></i></a>
													</div>
												</div>
											</div>
											<?php
											}
											?>
										</div>
									</div>
								</div>
								<br><br>
								<div class="row">	
									<div class="col-md-12">
										<button type="submit" name="submit" id="submit" class="btn btn-primary waves-effect w-md waves-light">Submit</button>
												
										<button type="button" class="btn btn-inverse waves-effect w-md waves-light" onClick="window.location.href='<?php echo ADMINURL ?>manage_<?php echo $ctable; ?>/'">Back</button>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php include("footer.php"); ?>
	</div>
</div>
<?php 
		$demo = '<div class="col-md-12 filed_class"><div class="col-md-2">';
		$demo .= '<div class="form-group">';
		$demo .= '<label>Product Category <span class="text-danger">*</span></label>';
			$demo .= '<select class="form-control" name="pro_cate_id[]" id="pro_cate_id1" data-id="f1" onChange="getCat(this.value,this);">';
			$demo .= '<option value="">Select Product Category</option>';
			$pro_category_r = $db->getData("product_category","*","isDelete=0");
			if(@mysqli_num_rows($pro_category_r)>0)
			{
				while($pro_category_d = @mysqli_fetch_array($pro_category_r))
				{
					$demo .= '<option value="'.$pro_category_d['id'].'">'.$pro_category_d['name'].'</option>';
				}
			}
			$demo .='</select>';
		$demo .= '</div>';
	$demo .= '</div>';
	$demo .= '<div class="col-md-2">';
		$demo .= '<div class="form-group">';
			$demo .= '<label>Category</label>';
			$demo .= '<select class="form-control" name="cate_id[]" onChange="getSubCat(this.value,this);" id="cate_id1" data-id="subc1">';
			$demo .= '<option value="">Select Category</option>';
			$demo.='</select>';
		$demo .= '</div>';
	$demo .= '</div>';
	$demo .= '<div class="col-md-2">';
		$demo .= '<div class="form-group">';
			$demo .= '<label>Sub Category</label>';
			$demo .= '<select class="form-control" name="sub_cate_id[]" id="sub_cate_id1">';
			$demo .= '<option value="">Please select sub category</option>';
			$demo.='</select>';
		$demo .= '</div>';
	$demo .= '</div>';
	$demo .= '<div class="col-md-1">';
		$demo .= '<div class="form-group">';
			$demo .= '<label>Price <span class="text-danger">*</span></label>';
			$demo .= '<input type="text" class="form-control" id="price1" name="price[]">';
		$demo .= '</div>';
	$demo .= '</div>';
	$demo .= '<div class="col-md-1">';
		$demo .= '<div class="form-group">';
			$demo .= '<label>Sell Price </label>';
			$demo .= '<input type="text" class="form-control" id="sell_price1" name="sell_price[]">';
		$demo .= '</div>';
	$demo .= '</div>';
	$demo .= '<div class="col-md-1">';
		$demo .= '<div class="form-group">';
			$demo .= '<label>Size </label>';
			$demo .= '<input type="text" class="form-control" id="size1" name="size[]">';
		$demo .= '</div>';
	$demo .= '</div>';
	$demo .= '<div class="col-md-2">';
		$demo .= '<div class="form-group">';
			$demo .= '<label>Unit <span class="text-danger">*</span></label>';
			$demo .= '<select name="unit[]" id="unit1" class="form-control">';
				$demo .= '<option value="">Please select Option Value</option>';
				$demo .= '<option value="Meter">Meter(s)</option>';
			$demo.='</select>';
		$demo .= '</div>';
	$demo .= '</div>';
	$demo .='<div class="col-md-1"><label>&nbsp;</label><div class="form-group"><a type="button" class="btn btn-icon waves-effect waves-light btn-danger remove_button"> <i class="fa fa-minus"></i></a></div></div></div>';
?>
<script>
	var resizefunc = [];
</script>     
<?php include('include_js.php'); ?>
<script src="<?php echo ADMINURL?>assets/js/ckeditor/ckeditor.js" type="text/javascript"></script>
<script src="<?php echo ADMINURL?>plugins/select2/js/select2.min.js" type="text/javascript"></script>
<script src="<?php echo ADMINURL?>assets/crop/js/commonfile_html5imageupload.js?v1.3.4"></script>
<script>
	CKEDITOR.replace('descr');

	var custom_img_width = '458';
	
	$('#dropzone').html5imageupload({
		onAfterProcessImage: function() {
			var imgName = $('#filename').val($(this.element).data('imageFileName'));
		},
		onAfterCancel: function() {
			$('#filename').val('');
		}
	});
	
	$(function(){
		$("#frm").validate({
			ignore: "",
			rules: {
				name:{required : true},
				descr:{required : true},
				pro_status:{required : true},
				//"pro_cate_id[]":{required : true},
				//"price[]":{required : true,number: true,min: 0},
				//"unit[]":{required : true},
				//"size[]":{required : true},
				image_path:{required : $("#mode").val()=="add" && $("#filename").val()=="" },
				filename:{ required: $("#mode").val()=="add" && $("#filename").val()=="" },
			},
			messages: {
				name:{required:"Please enter product name."},
				descr:{required:"Please enter description."},
				pro_status:{required:"Please select status."},
				//"pro_cate_id[]":{required:"Please select product category."},
				//"price[]":{required:"Please enter product price."},
				//"unit[]":{required:"Please select product unit."},
				//"size[]":{required:"Please select product size."},
				image_path:{required:"Please upload image."},
				filename:{required:"Please click on right tick mark after upload image."},
			},
			errorPlacement: function(error, element) {
				if (element.attr("name") == "image_path") {
					error.insertAfter("#dropzone");
				}else if (element.attr("name") == "filename") {
					error.insertAfter("#dropzone");
				} else if (element.attr("name") == "descr") 
				{
					error.insertAfter(".desc_error");
				}
				else
				{
					error.insertAfter(element);
				}
			}
		});
	});

	$(document).ready(function(){ 
		var addButton = $('.add_button'); 
		var wrapper = $('.filed'); 
		<?php 
		if(isset($_REQUEST['id']) && $_REQUEST['id']>0 && $_REQUEST['mode']=="edit")
		{ 
		?>
			var cal = '<?php echo ($auto_count - 1)?>'; 
		<?php }else{?>
			var cal = '<?php echo ($auto_count)?>'; 
		<?php }?>
		$(addButton).click(function()
		{ 
			var data = '<?php echo $demo; ?>';
			var auto_newval = parseInt(cal) + 1;
			var tmp1  = data.replace(/f1/gi,"f"+(auto_newval));
			var tmp1  = tmp1.replace(/pro_cate_id1/gi,"pro_cate_id"+(auto_newval));
			var tmp1  = tmp1.replace(/cate_id1/gi,"cate_id"+(auto_newval));
			var tmp1  = tmp1.replace(/sub_cate_id1/gi,"sub_cate_id"+(auto_newval));
			var tmp1  = tmp1.replace(/price1/gi,"price"+(auto_newval));
			var tmp1  = tmp1.replace(/size1/gi,"size"+(auto_newval));
			var tmp1  = tmp1.replace(/unit1/gi,"unit"+(auto_newval));
			var tmp1  = tmp1.replace(/subc1/gi,"subc"+(auto_newval));
			$(wrapper).append(tmp1); 
			cal = parseInt(cal) + 1;
		});
		$(wrapper).on('click', '.remove_button', function(e){ 
			e.preventDefault();
			$(this).parents('.filed_class').remove();
			/**/
			var id = $(this).attr('data-delete-id');
			$('input[name="product_details_ids[]"][data-id="'+id+'"]').remove();
			var delete_ids = $('#delete_product_details_id').val();
			if(delete_ids != '') {
				$('#delete_product_details_id').val(delete_ids+','+id);
			}
			else {
				$('#delete_product_details_id').val(id);
			}
		});
	});
	
	function getCat(id,this_id)
	{
		var tmp = $(this_id).data("id");
		var lastChar = tmp[tmp.length -1];
		$.ajax({
			type: "POST",
			url: "<?php echo ADMINURL?>ajax_get_category_list.php",
			data: 'pro_cate_id='+id,
			success: function(result)
			{
				$("#cate_id"+lastChar).html(result);
			}
		});
	}

	function getSubCat(id,this_id)
	{
		var stmp = $(this_id).data("id");
		var slastChar = stmp[stmp.length -1];
		$.ajax({
			type: "POST",
			url: "<?php echo ADMINURL?>ajax_get_sub_category_list.php",
			data: 'cate_id='+id,
			success: function(result)
			{
				$("#sub_cate_id"+slastChar).html(result);
			}
		});
	}
	
	function form_submit() 
	{
		$("#submit").attr("readonly", true);
		if($("#frm").validate())
		{
			$("#submit").attr("readonly", true);
			
			var multi_val = true;
			$( ".filed_class" ).filter(function( index ) 
			{
				var ec = index + 1;
				var pro_cate_field 	= $( "#pro_cate_id"+ec).val();
				var cate_id_field 	= $( "#cate_id"+ec).val();
				var sub_cate_field 	= $( "#sub_cate_id"+ec).val();
				var price_field 	= $( "#price"+ec).val();
				var size_field 		= $( "#size"+ec).val();
				var unit_field 		= $( "#unit"+ec).val();
				
				$("#pro_cate_id"+ec).css('border-bottom','1px solid rgba(152, 152, 152, 0.8)');
				$("#cate_id"+ec).css('border-bottom','1px solid rgba(152, 152, 152, 0.8)');
				$("#sub_cate_id"+ec).css('border-bottom','1px solid rgba(152, 152, 152, 0.8)');
				$("#price"+ec).css('border-bottom','1px solid rgba(152, 152, 152, 0.8)');
				$("#size"+ec).css('border-bottom','1px solid rgba(152, 152, 152, 0.8)');
				$("#unit"+ec).css('border-bottom','1px solid rgba(152, 152, 152, 0.8)');
				
				if(pro_cate_field=="")
				{
					$("#pro_cate_id"+ec ).css('border-bottom','1px #ff3111 solid');
					multi_val = false;
				}
				
				if(cate_id_field=="")
				{
					$("#cate_id"+ec ).css('border-bottom','1px #ff3111 solid');
					multi_val = false;
				}
				
				if(sub_cate_field=="")
				{
					$("#sub_cate_id"+ec ).css('border-bottom','1px #ff3111 solid');
					multi_val = false;
				}
				
				if(price_field=="")
				{
					$("#price"+ec ).css('border-bottom','1px #ff3111 solid');
					multi_val = false;
				}
				
				if(size_field=="")
				{
					$("#size"+ec ).css('border-bottom','1px #ff3111 solid');
					multi_val = false;
				}
				
				if(unit_field=="")
				{
					$("#unit"+ec ).css('border-bottom','1px #ff3111 solid');
					multi_val = false;
				}
			});
			if(multi_val==false)
			{
				$("#submit").attr("readonly", false);
			}
			return multi_val;
		}
		else
		{
			$("#submit").attr("readonly", false);
			return false;
		}
		return true;
	}
</script>
</body>
</html>