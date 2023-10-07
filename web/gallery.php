<?php
include_once "dbconfig.php";

$condition		= " AND id=:id";

$Sql	= "SELECT * FROM ".$Prefix."gallery WHERE clientid=:clientid AND status=:status AND deletedon < :deletedon ORDER BY orderby ASC";
$Esql	= array("clientid"=>(int)$ClientID,"status"=>1,"deletedon"=>1);

$Query	= pdo_query($Sql,$Esql);
$Num	= pdo_num_rows($Query);

if($Num > 0)
{
	?>
	<section class="page-section portfolio" id="gallery">
		<div class="container">
			<h2 class="page-section-heading text-center text-uppercase text-secondary mb-0">GALLERY</h2>
			<!-- Icon Divider-->
			<div class="divider-custom">
				<div class="divider-custom-line"></div>
				<div class="divider-custom-icon"><i class="fas fa-star"></i></div>
				<div class="divider-custom-line"></div>
			</div>
			<div class="row justify-content-center">
			<?php
			while($rows = pdo_fetch_assoc($Query))
			{
				$id			= $rows['id'];
				$name		= $rows['name'];
				$imagefile	= $rows['imagefile'];

				$GallaryArr[]	= $rows;
				?>
				<div class="col-md-6 col-lg-4 mb-5">
					<div class="portfolio-item mx-auto" data-bs-toggle="modal" data-bs-target="#gallerymodal<?php echo $id;?>">
						<?php
						if($imagefile != "" && file_exists($Uploadimage.$imagefile))
						{
							?>
							<div class="imagewrapper">
								<img class="img-fluid" src="<?php echo $ServerURL.$Uploadimage.$imagefile;?>" alt="<?php echo $name;?>" title="<?php echo $name;?>" />
							</div>
							<?php
						}
						else
						{
							?>
							<div class="textwrapper">
								<span class="py-3"><?php echo $name;?></span>
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