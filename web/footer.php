<?php
include_once "dbconfig.php";
?>
		<footer class="footer text-center">
            <div class="container">
                <div class="row">
                    <!-- Footer Location-->
                    <div class="col-lg-4 mb-5 mb-lg-0">
                        <h4 class="text-uppercase mb-4">Our Address</h4>
                        <p class="lead mb-0">
                            <?php echo $websiteaddress;?>
                        </p>
                    </div>
					<?php
					$whatsappnumber	= "";
					if($phone1 != "" && $iswhatsapp1 > 0)
					{
						$whatsappnumber	= $phone1;
					}
					if(($phone2 != "" && $iswhatsapp2 > 0) && $whatsappnumber == "")
					{
						$whatsappnumber	= $phone2;
					}
					?>
                    <div class="col-lg-4 mb-5 mb-lg-0">
                        <h4 class="text-uppercase mb-4">Contact Us</h4>
						<?php
						if(trim($whatsappnumber) != "")
						{
						?>
                        <a class="btn btn-outline-light btn-social mx-1" href="//wa.me/91<?php echo $whatsappnumber;?>"><i class="fab fa-whatsapp"></i></a>
						<br/>
						Phone: <a href="tel:<?php echo $whatsappnumber;?>" class='foooter-links'><?php echo $whatsappnumber;?></a>
						<br/>
						<?php
						}
						if(trim($websiteemail) != "")
						{
						?>
						<a href="mailto:<?php echo $websiteemail;?>" class='foooter-links'><?php echo $websiteemail;?></a>
						<br/>
						<?php
						}
						?>
					</div>
                    <div class="col-lg-4">
                        <h4 class="text-uppercase mb-4">TERMS & POLICIES</h4>
                        <p class="lead mb-0">
                            <a href="terms.php" class='foooter-links'>Terms Of Use</a><br/>
                            <a href="privacy-policy.php" class='foooter-links'>Privacy Policy</a><br/>
                            <a href="shipping-policy.php" class='foooter-links'>Shipping Policy</a><br/>
                            <a href="refund-return-policy.php" class='foooter-links'>Refund & Return Policy</a>
                        </p>
                    </div>
                </div>
            </div>
        </footer>
        <div class="copyright py-4 text-center text-white">
            <div class="container"><small>&copy; <?php echo date("Y");?> orlopay.com</small></div>
        </div>
<?php
if(!empty($GallaryArr))
{
	foreach($GallaryArr as $key=>$gallaryrows)
	{
		$imagefile	= $gallaryrows['imagefile'];
		?>
        <div class="portfolio-modal modal fade" id="gallerymodal<?php echo $gallaryrows['id'];?>" tabindex="-1" aria-labelledby="gallerymodal<?php echo $gallaryrows['id'];?>" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header border-0"><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button></div>
                    <div class="modal-body text-center pb-5">
                        <div class="container">
                            <div class="row justify-content-center">
                                <div class="col-lg-8">
                                    <!-- Portfolio Modal - Title-->
                                    <h2 class="portfolio-modal-title text-secondary text-uppercase mb-0"><?php echo $gallaryrows['name'];?></h2>
                                    <!-- Icon Divider-->
                                    <div class="divider-custom">
                                        <div class="divider-custom-line"></div>
                                        <div class="divider-custom-icon"><i class="fas fa-star"></i></div>
                                        <div class="divider-custom-line"></div>
                                    </div>
									<?php
									if($imagefile != "" && file_exists($Uploadimage.$imagefile))
									{
									?>
									<img class="img-fluid rounded mb-5" src="<?php echo $ServerURL.$Uploadimage.$imagefile;?>" alt="<?php echo $gallaryrows['name'];?> - Preview" />
									<?php
									}
									/*
									?>
                                    <p class="mb-4">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Mollitia neque assumenda ipsam nihil, molestias magnam, recusandae quos quis inventore quisquam velit asperiores, vitae? Reprehenderit soluta, eos quod consequuntur itaque. Nam.</p>
									<?php
									*/
									?>
                                    <button class="btn btn-primary" data-bs-dismiss="modal">
                                        <i class="fas fa-xmark fa-fw"></i>
                                        Close Window
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<?php
	}
}
?>
<script type="text/javascript">
$(document).ready(function(){
	const accesstoken	= localStorage.getItem('<?php echo $subdomain;?>_customer_token');

	if(accesstoken != null && accesstoken != undefined && accesstoken != "")
	{
		$(".usernavbox").html('<a class="nav-link py-3 px-0 px-lg-3 rounded" href="<?php echo $siteprefix;?>dashboard.php">Dashboard</a>');
	}
	else
	{
		$(".usernavbox").html('<a class="nav-link py-3 px-0 px-lg-3 rounded" href="<?php echo $siteprefix;?>bill-payment.php">Bill Payment</a>');
	}
})
</script>