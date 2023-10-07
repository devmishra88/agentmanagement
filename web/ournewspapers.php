<?php
include_once "dbconfig.php";

$condition		= " AND id=:id";
$CategoryEsql	= array("id"=>605,"status"=>1);

$CategorySql	= "SELECT * FROM ".$Prefix."category WHERE status=:status ".$condition." ORDER BY orderby ASC";

$CategoryQuery	= pdo_query($CategorySql,$CategoryEsql);
$CategoryNum	= pdo_num_rows($CategoryQuery);

if($CategoryNum > 0)
{
	$ClientInventoryData	= GetClientInventory($ClientID,$stateid,$cityid);

	$InventoryListArr	= array();
		
	$index		= 0;
	$idarray			= array();
	$categoryidarray	= array();
	$namearray			= array();
	$pricearray			= array();
	$frequencyarray		= array();
	$isassignedarray	= array();
	$imagearray			= array();

	while($catrows = pdo_fetch_assoc($CategoryQuery))
	{
		$catid		= $catrows['id'];
		$cattitle	= $catrows['title'];
		$type		= $catrows['type'];

		$InventorySql	= "SELECT inv.* FROM ".$Prefix."inventory inv,".$Prefix."inventory_state_city rel WHERE 1 AND inv.categoryid=:categoryid AND inv.id=rel.inventoryid AND rel.stateid=:stateid AND rel.cityid=:cityid GROUP BY rel.inventoryid ORDER BY inv.name ASC";
		$InventoryEsql	= array("categoryid"=>(int)$catid,"stateid"=>(int)$stateid,"cityid"=>(int)$cityid);

		$InventoryQuery	= pdo_query($InventorySql,$InventoryEsql);
		$InventoryNum	= pdo_num_rows($InventoryQuery);

		if($InventoryNum > 0)
		{
			while($rows = pdo_fetch_assoc($InventoryQuery))
			{
				$imagetobeshow	= "";
				$id				= $rows['id'];
				$categoryid		= $rows['categoryid'];
				$name			= $rows['name'];
				$price			= $rows['price'];
				$image			= $rows['productimage'];

				if($image != "" && file_exists($LargeThumb.$image))
				{
					$imagetobeshow	= $LargeThumb.$image;
				}
				else
				{
					/*$imagetobeshow	= $Uploadimage."noimage.jpg";*/
					$imagetobeshow		= "noimage";
				}

				if(!empty($ClientInventoryData[$id]))
				{
					if($ClientInventoryData[$id]['status'] > 0)
					{
						$inventorystatus	= $ClientInventoryData[$id]['status'];
						$inventoryprice		= $ClientInventoryData[$id]['price'];
						
						$idarray[]			= (int)$id;
						$categoryidarray[]	= (int)$categoryid;
						$isassignedarray[]	= $inventorystatus;
						$namearray[]		= $name;
						$pricearray[]		= (float)$inventoryprice;
						$imagearray[]		= $imagetobeshow;
					}
				}
			}
		}
		$InventorySql2	= "SELECT inv.* FROM ".$Prefix."inventory inv WHERE inv.clientid=:clientid AND deletedon<:deletedon AND status>:status AND categoryid=:categoryid ORDER BY inv.name ASC";
		$InventoryEsql2	= array("categoryid"=>(int)$catid,"clientid"=>(int)$ClientID,"deletedon"=>1,'status'=>0);

		$InventoryQuery2	= pdo_query($InventorySql2,$InventoryEsql2);
		$InventoryNum2	= pdo_num_rows($InventoryQuery2);
		if($InventoryNum2 > 0)
		{
			while($rows2 = pdo_fetch_assoc($InventoryQuery2))
			{
				$imagetobeshow	= "";
				$id			= $rows2['id'];
				$categoryid	= $rows2['categoryid'];
				$name		= $rows2['name'];
				$price		= $rows2['price'];
				$frequency	= $rows2['frequency'];
				$image		= $rows2['productimage'];

				if($image != "" && file_exists($LargeThumb.$image))
				{
					$imagetobeshow	= $LargeThumb.$image;
				}
				else
				{
					/*$imagetobeshow	= $Uploadimage."noimage.jpg";*/
					$imagetobeshow		= "noimage";
				}

				if(!empty($ClientInventoryData[$id]))
				{
					if($ClientInventoryData[$id]['status'] > 0)
					{
						$inventorystatus	= $ClientInventoryData[$id]['status'];
						$inventoryprice		= $ClientInventoryData[$id]['price'];
						
						$idarray[]			= (int)$id;
						$categoryidarray[]	= (int)$categoryid;
						$isassignedarray[]	= $inventorystatus;
						$namearray[]		= $name;
						$pricearray[]		= (float)$inventoryprice;
						$imagearray[]		= $imagetobeshow;
					}
				}
			}
		}
		
		if(!empty($namearray))
		{
			$sortnamearray = array_map("strtolower",$namearray);

			array_multisort($sortnamearray,SORT_ASC,$namearray,$idarray,$categoryidarray,$pricearray,$frequencyarray,$imagearray);
			foreach($namearray as $key => $value)
			{
				$id				= $idarray[$key];
				$categoryid		= $categoryidarray[$key];
				$name			= $value;
				$inventoryprice	= $pricearray[$key];
				$isassigned		= $isassignedarray[$key];
				$imagepreview	= $imagearray[$key];

				$noofpices			= "";
				$purchaserate		= "";
				$haspurchaserate	= false;
				$hasnoofpices		= false;

				$InventoryListArr[$index]['id']					= (int)$id;
				$InventoryListArr[$index]['categoryid']			= (int)$categoryid;
				$InventoryListArr[$index]['name']				= $name;
				$InventoryListArr[$index]['isassigned']			= $inventorystatus;
				$InventoryListArr[$index]['imagepreview']		= $imagepreview;
				/*$InventoryListArr[$index]['price']			= (float)$inventoryprice;
				$InventoryListArr[$index]['type']				= (int)$type;
				$InventoryListArr[$index]['hasnoofpices']		= $hasnoofpices;
				$InventoryListArr[$index]['numberofpieces']		= $noofpices;
				$InventoryListArr[$index]['purchaserate']		= $purchaserate;
				$InventoryListArr[$index]['haspurchaserate']	= $haspurchaserate;*/

				$index++;
			}
		}
	}
}
if(!empty($InventoryListArr))
{
	?>
	<section class="page-section portfolio" id="ournewspapers">
		<div class="container">
			<h2 class="page-section-heading text-center text-uppercase text-secondary mb-0">Our Newspapers</h2>
			<!-- Icon Divider-->
			<div class="divider-custom">
				<div class="divider-custom-line"></div>
				<div class="divider-custom-icon"><i class="fas fa-star"></i></div>
				<div class="divider-custom-line"></div>
			</div>
			<div class="row justify-content-center">
			<?php
			foreach($InventoryListArr as $inventorykey=>$inventoryrows)
			{
				$inventoryid	= $inventoryrows['id'];
				$categoryid		= $inventoryrows['categoryid'];
				$inventoryname	= $inventoryrows['name'];
				$imagepreview	= $inventoryrows['imagepreview'];
				?>
				<!-- Portfolio Item <?php echo $inventoryid;?>-->
				<div class="col-md-6 col-lg-4 mb-5">
					<div class="portfolio-item mx-auto">
						<?/*?><div class="portfolio-item-caption d-flex align-items-center justify-content-center h-100 w-100">
							<div class="portfolio-item-caption-content text-center text-white"><i class="fas fa-plus fa-3x"></i></div>
						</div><?*/?>
						<?php
						if($imagepreview == "noimage")
						{
							?>
							<div class="textwrapper">
								<span class="py-3"><?php echo $inventoryname;?></span>
							</div>
							<?php
						}
						else
						{
							?>
							<div class="imagewrapper">
								<img class="img-fluid" src="<?php echo $ServerURL.$imagepreview;?>" alt="<?php echo $inventoryname;?>" title="<?php echo $inventoryname;?>" />
							</div>
							<?php
						}
						?>
					</div>
				</div>
				<?php
			}
			?>
			</div>
		</div>
	</section>
	<?php
}