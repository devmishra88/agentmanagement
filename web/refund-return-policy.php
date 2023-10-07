<?php
include_once "dbconfig.php";

$IsHomePage	= 0;

$GallaryArr	= array();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Return & Refund Policy - <?php echo $clientname;?></title>
        <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
        <script src="<?php echo $ServerURL;?>js/all.js" crossorigin="anonymous"></script>
        <link href="//fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css" />
        <link href="//fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic" rel="stylesheet" type="text/css" />
        <link href="<?php echo $ServerURL;?>css/styles.css?t=<?php echo time();?>" rel="stylesheet" />
        <script src="<?php echo $ServerURL;?>js/jquery.min.js"></script>
    </head>
    <body id="page-top">
		<?php
		include_once "header.php";
		?>
        <section class="page-section" id="login">
			<br>
			<br>
			<br>
            <div class="container">
                <!-- Contact Section Heading-->
                <h2 class="page-section-heading text-center text-uppercase text-secondary mb-0">Return & Refund</h2>
                <!-- Icon Divider-->
                <div class="divider-custom">
                    <div class="divider-custom-line"></div>
                    <div class="divider-custom-icon"><i class="fas fa-star"></i></div>
                    <div class="divider-custom-line"></div>
                </div>
                <!-- Contact Section Form-->
                <div class="row justify-content-center">
                    <div class="col-lg-8 col-xl-12">
                        <p>Updated at 2022-01-01</p>
							<p><strong>Definitions and key terms</strong></p>
							<p>To help explain things as clearly as possible in this Return &amp; Refund Policy, every time any of these terms are referenced, are strictly defined as:</p>
							<ul>
							<li>Company: when this policy mentions &ldquo;Company,&rdquo; &ldquo;we,&rdquo; &ldquo;us,&rdquo; or &ldquo;our,&rdquo; it refers to <?php echo $clientname;?>, that is responsible for your information under this Return &amp; Refund Policy.</li>
							<li>Customer: refers to the company, organization or person that signs up to use the <?php echo $clientname;?> Service to manage the relationships with your consumers or service users.</li>
							<li>Device: any internet-connected device such as a phone, tablet, computer or any other device that can be used to visit <?php echo $clientname;?> and use the services.</li>
							<li>Service: refers to the service provided by <?php echo $clientname;?> as described in the relative terms (if available) and on this platform.</li>
							<li>Website: <?php echo $clientname;?>."&rsquo;s" site, which can be accessed via this URL: <a href="http://<?php echo $subdomain;?>.orlopay.com"><?php echo $subdomain;?>.orlopay.com</a></li>
							<li>You: a person or entity that is registered with <?php echo $clientname;?> to use the Services.</li>
							</ul>
							<p><br /><strong><span class="bold">Return &amp; Refund Policy</span></strong></p>
							<p>Thanks for shopping at <?php echo $clientname;?>. We appreciate the fact that you like to buy the stuff we build. We also want to make sure you have a rewarding experience while you&rsquo;re exploring, evaluating, and purchasing our products.</p>
							<p>As with any shopping experience, there are terms and conditions that apply to transactions at <?php echo $clientname;?>. We&rsquo;ll be as brief as our attorneys will allow. The main thing to remember is that by placing an order or making a purchase at <?php echo $clientname;?>, you agree to the terms set forth below along with Policy.</p>
							<p>If there&rsquo;s something wrong with the product/service you bought, or if you are not happy with it, you have 7 days to issue a refund and return your product/service.</p>
							<p>If you would like to return a product, the only way would be if you follow the next guidelines:</p>
							<p>&nbsp; &nbsp;-The product has to be in the packaging we sent in the first place.<br />&nbsp; &nbsp;-The product has to be damage-free, if we find any damage on the product we will cancel your refund immediately.</p>
							<p><br /><strong><span class="bold">Refunds</span></strong></p>
							<p>We at &nbsp;<?php echo $clientname;?> take pride in serving our customers with the best products. Every single product that you choose is thoroughly inspected, checked for defects, and packaged with utmost care. We do this to ensure that you fall in love with our products.</p>
							<p>Sadly, there are times when we may not have the product(s) that you choose in-stock or may face some issues with our inventory and quality check. In such cases, we may have to cancel your order. You will be intimated about it in advance so that you don't have to worry unnecessarily about your order. If you have purchased via Online payment (not Cash on Delivery), then you will be refunded once our team confirms your request.</p>
							<p>We carry out thorough quality checks before processing the ordered item. We take utmost care while packing the product. At the same time, we ensure that the packing is good such that the items won&rsquo;t get damaged during transit. Please note that <?php echo $clientname;?> is not liable for damages that are caused to the items during transit or transportation.</p>
							<p>We will revise your returned product as soon as we receive it and if it follows the guidelines addressed above, we will proceed to issue a refund of your purchase. Your refund may take a couple of days to process but you will be notified when you receive your money.</p>
							<p><br /><strong><span class="bold">Shipping</span></strong></p>
							<p><?php echo $clientname;?> is not responsible for return shipping costs. Every shipping has to be paid by the customer, even if the item had free shipping in the first place, the customer has to pay for the shipping in return.</p>
							<p><br /><strong><span class="bold">Your Consent</span></strong></p>
							<p>By using our website, registering an account, or making a purchase, you hereby consent to our Return &amp; Refund Policy and agree to its terms.</p>
							<p><br /><strong><span class="bold">Changes To Our Return &amp; Refund Policy</span></strong></p>
							<p>Should we update, amend or make any changes to this document so that they accurately reflect our Service and policies. Unless otherwise required by law, those changes will be prominently posted here. Then, if you continue to use the Service, you will be bound by the updated Return &amp; Refund Policy. If you do not want to agree to this or any updated Return &amp; Refund Policy, you can delete your account.</p>
							<p><br /><strong><span class="bold">Contact Us</span></strong></p>
							<p>If, for any reason, You are not completely satisfied with any good or service that we provide, don't hesitate to contact us and we will discuss any of the issues you are going through with our product.</p>
							<p>&nbsp;-Via Email: &nbsp;<?php echo $websiteemail;?><br /></p>
                    </div>
                </div>
            </div>
        </section>
		<?php include_once "footer.php";?>
        <script src="<?php echo $ServerURL;?>js/bootstrap.bundle.min.js"></script>
        <script src="<?php echo $ServerURL;?>js/scripts.js"></script>
    </body>
</html>
