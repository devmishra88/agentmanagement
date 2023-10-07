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
        <title>Privacy Policy - <?php echo $clientname;?></title>
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
                <h2 class="page-section-heading text-center text-uppercase text-secondary mb-0">Privacy Policy</h2>
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
							<p>
								We understand your concern about the personal information you submit on our web-site. We also go by the notion that has to be a fine equilibrium between the need to give better, tailored and customized service to you and to protect your personal details, simultaneously <a href="http://<?php echo $subdomain;?>.orlopay.com/" target="_blank"><?php echo $subdomain;?>.orlopay.com</a><wbr /> value your trust in us that we shall not misuse your personal information in any way.</p>
							<p>
								You can visit the Site and explore our offerings without submitting any personal details. During your visit to the Site you are anonymous and we could not discover you unless you have an account on the Site and log on with your user name and password.</p>
							<p>
								<span style="color:#ff00cc;">Data that we collect</span></p>
							<p>
								We may collect little information if you use our payment services. We gather your data only for processing your invoices on <a href="http://<?php echo $subdomain;?>.orlopay.com/" target="_blank"><?php echo $subdomain;?>.orlopay.com</a> & any possible future claims & offer you our services.It might be possible that we collect your name, gender, email address, postal address, delivery address (if different), contact number, fax number, payment details, payment card details or bank account details.</p>
							<p>
								Basically, we need the information to process the payment for the invoices.</p>
							<p>
								We reserve the right to communicate your personal information to any third party that makes a legally-compliant request for its disclosure.</p>
							<p>
								<span style="color:#ff00cc;">Cookies</span></p>
							<p>
								Like many online services, <a href="http://<?php echo $subdomain;?>.orlopay.com/" target="_blank"><?php echo $subdomain;?>.orlopay.com</a> uses "cookies" on our portal to gather information. A cookie is a small text file that is transferred to your computer or mobile device's hard disk when you visit a website. We use cookies for two things. Firstly, we use "persistent" cookies to save your login information for future logins to the Service. Second, we employ "session ID" cookies to enable certain features of the Service, to better understand how you interact with the Service and to monitor the web traffic routing on the Service. Unlike persistent cookies, session cookies are deleted from your computer when you log off from the Service and then close your browser.</p>
							<p>
								<span style="color:#ff00cc;">Consent</span></p>
							<p>
								By submitting data to us, you allow us to use your data in the manner set out in this Privacy Policy.</p>
							<p>
								<span style="color:#ff00cc;">Links with other sites</span></p>
							<p>
								Our site may contain some links which may take you to some other websites which may have their own privacy policy, different from ours. It means that once you leave our site, our privacy policy will no longer apply.</p>

                    </div>
                </div>
            </div>
        </section>
		<?php include_once "footer.php";?>
        <script src="<?php echo $ServerURL;?>js/bootstrap.bundle.min.js"></script>
        <script src="<?php echo $ServerURL;?>js/scripts.js"></script>
    </body>
</html>
