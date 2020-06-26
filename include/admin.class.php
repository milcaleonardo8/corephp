<?php

class Admin extends Functions
{
	/*
		*** Cart Function List :) <<<
			-> getAddButton()
				- get Add Button for manage page
			-> getUpdateButton()
				- get Update Button for manage page
	*/

	public function getAddHtaccess($ctable,$ctable1,$url=null)
    {
		if($ctable!="" && $ctable1!=""){
			if($url!=null){
				?>
				<a class="btn btn-primary sidebar" href="<?php echo $url; ?>">Add <?php echo $ctable1; ?></a>
				<?php
			}else{
				?>
				<a class="btn btn-primary sidebar" href="<?php echo ADMINURL.$ctable; ?>">Add <?php echo $ctable1; ?></a>
				<?php
			}
		}	
    }

	public function getAddButton($ctable,$ctable1,$url=null)
    {
		if($ctable!="" && $ctable1!=""){
			if($url!=null){
				?>
				<a class="btn btn-info sidebar" href="<?php echo $url; ?>">Add <?php echo $ctable1; ?></a>
				<?php
			}else{
				?>
				<a class="btn btn-info sidebar m-t-10" href="<?php echo ADMINURL?>add_<?php echo $ctable; ?>/add/">Add <?php echo $ctable1; ?></a>
				<?php
			}
		}	
    }

	public function getUpdateButton($frmId=null)
    {
		if($frmId!=null){
			?>
			<button class="btn btn-info btn-flat sidebar" onClick="document.<?php echo $frmId; ?>.submit();">Update</button>
			<?php
		}else{
			?>
			<button class="btn btn-info btn-flat sidebar" onClick="document.frm.submit();">Update</button>
			<?php

		}

    }
	public function getTablePaginationBlock($pagiArr){
	?>
		<div class="tablePagination">
			<div class="row">
				<div class="col-lg-2 col-md-3">
					<div class="dataTables_info dataTables_length"> Rows Limit:
						<select id="numRecords" class="form-control input-sm" onChange="changeDisplayRowCount(this.value);">
							<option value="10" <?php if ($_REQUEST["show"] == 10 || $_REQUEST["show"] == "" ) { echo ' selected="selected"'; }  ?> >10</option>
							<option value="20" <?php if ($_REQUEST["show"] == 20) { echo ' selected="selected"'; }  ?> >20</option>
							<option value="30" <?php if ($_REQUEST["show"] == 30) { echo ' selected="selected"'; }  ?> >30</option>
						</select>
					</div>
				</div>
				<div class="col-lg-10 col-md-9">
					<div class="dataTables_paginate paging_simple_numbers text-right" id="example_paginate">
						<ul class="pagination" style="justify-content: flex-end;margin-top: 20px;">
						<?php 
						echo $this->paginate_function($pagiArr[0],$pagiArr[1],$pagiArr[2],$pagiArr[3]); 
						?>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	public function paginate_function($item_per_page, $current_page, $total_records, $total_pages)
	{
		$pagination = '';
		if($total_pages > 0 && $total_pages != 1 && $current_page <= $total_pages){ //verify total pages and current page number
			$right_links    = $current_page + 3; 
			$previous       = $current_page - 3; //previous link 
			$next           = $current_page + 1; //next link
			$first_link     = true; //boolean var to decide our first link

			if($current_page > 1){
				$previous_link = ($previous<=0)?1:$previous;
				$pagination .= '<li class="paginate_button page-item "><a href="#" aria-controls="datatable1" data-page="1" title="First" class="page-link">&laquo;</a></li>'; //first link
				$pagination .= '<li class="paginate_button page-item previous " id="example_previous"><a href="#" aria-controls="datatable1" data-page="'.$previous_link.'" title="Previous" class="page-link">&lt;</a></li>'; //previous link
					for($i = ($current_page-2); $i < $current_page; $i++){ //Create left-hand side links
						if($i > 0){
							$pagination .= '<li class="paginate_button page-item "><a class="page-link" href="#"  data-page="'.$i.'" aria-controls="datatable1" title="Page'.$i.'">'.$i.'</a></li>';
						}
					}   
				$first_link = false; //set first link to false
			}
			
			if($first_link){ //if current active page is first link
				$pagination .= '<li class="paginate_button page-item active"><a aria-controls="datatable1" class="page-link">'.$current_page.'</a></li>';
			}elseif($current_page == $total_pages){ //if it's the last active link
				$pagination .= '<li class="paginate_button page-item active"><a aria-controls="datatable1" class="page-link">'.$current_page.'</a></li>';
			}else{ //regular current link
				$pagination .= '<li class="paginate_button page-item active"><a aria-controls="datatable1" class="page-link">'.$current_page.'</a></li>';
			}
			
			for($i = $current_page+1; $i < $right_links ; $i++){ //create right-hand side links
				if($i<=$total_pages){
					$pagination .= '<li class="paginate_button page-item "><a href="#" aria-controls="datatable1" class="page-link" data-page="'.$i.'" title="Page '.$i.'">'.$i.'</a></li>';
				}
			}

			if($current_page < $total_pages){ 
				$next_link = ($i > $total_pages)? $total_pages : $i;
				$pagination .= '<li class="paginate_button page-item next " id="example_next"><a href="#" aria-controls="datatable1" class="page-link" data-page="'.$next_link.'" title="Next">&gt;</a></li>'; //next link
				$pagination .= '<li class="paginate_button page-item "><a href="#" aria-controls="datatable1" class="page-link" data-page="'.$total_pages.'" title="Last">&raquo;</a></li>'; //last link
			}
		}
		return $pagination; //return pagination links
	}
}
?>