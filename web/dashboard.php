<?php
include_once "dbconfig.php";
$IsHomePage	= 0;

$DashboardListArr[0]['id']			= 1;
$DashboardListArr[0]['name']		= 'Invoices';
$DashboardListArr[0]['link']		= $siteprefix.'invoices.php';
$DashboardListArr[0]['isactive']	= 1;

$DashboardListArr[3]['id']			= 4;
$DashboardListArr[3]['name']		= 'Payment History';
$DashboardListArr[3]['link']		= $siteprefix.'payment-history.php';
$DashboardListArr[3]['isactive']	= 1;

$DashboardListArr[1]['id']			= 2;
$DashboardListArr[1]['name']		= 'Ledger';
$DashboardListArr[1]['link']		= $siteprefix.'ledger.php';
$DashboardListArr[1]['isactive']	= 1;

/*$DashboardListArr[2]['id']		= 3;
$DashboardListArr[2]['name']		= 'Make Payment';
$DashboardListArr[2]['link']		= '/make-payment.php';
$DashboardListArr[2]['isactive']	= 0;*/

$DashboardListArr[4]['id']			= 5;
$DashboardListArr[4]['name']		= 'Subscription';
$DashboardListArr[4]['link']		= $siteprefix.'subscription.php';
$DashboardListArr[4]['isactive']	= 1;

$DashboardListArr[5]['id']			= 6;
$DashboardListArr[5]['name']		= 'Holidays';
$DashboardListArr[5]['link']		= $siteprefix.'holidays.php';
$DashboardListArr[5]['isactive']	= 1;
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Dashboard - <?php echo $clientname;?></title>
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
		<section class="page-section portfolio" id="ournewspapers">
			<div class="container">
				<div class="loggedinnamewrapper"></div>
				<div class="divider-custom">
					<div class="divider-custom-line"></div>
					<div class="divider-custom-icon"><i class="fas fa-star"></i></div>
					<div class="divider-custom-line"></div>
				</div>
				<div style="display:flex; justify-content:space-between; align-items:center;" class="mb-4 outstandingwrapper"></div>
				<div class="row justify-content-center">
				<?php
				foreach($DashboardListArr as $listkey=>$listrows)
				{
					$listid		= $listrows['id'];
					$listname	= $listrows['name'];
					$listlink	= $listrows['link'];
					$isactive	= $listrows['isactive'];
					?>
					<div class="col-md-6 col-lg-4">
						<div class="portfolio-item dashboard mx-auto">
							<div class="textwrapper dashboard">
							<?php
							if($isactive > 0)
							{
							?>
							<a class="nav-link py-3 px-0 px-lg-3 rounded" href="<?php echo $listlink;?>">
								<span class="py-3">
									<?php echo $listname;?>
								</span>
							</a>
							<?php
							}
							else
							{
							?>
							<span class="py-3 inactiveitem">
								<?php echo $listname;?>
							</span>
							<?php
							}
							?>
							</div>
						</div>
					</div>
					<?php
				}
				?>
					<div class="col-md-6 col-lg-4">
						<div class="portfolio-item dashboard mx-auto">
							<div class="textwrapper dashboard">
								<span class="py-3 logoutfromapp">
									Logout
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<?php include_once "footer.php";?>
        <script src="<?php echo $ServerURL;?>js/bootstrap.bundle.min.js"></script>
        <script src="<?php echo $ServerURL;?>js/scripts.js"></script>
    </body>
	<script type="text/javascript">

	function initData()
	{
		var dataStr	= "Mode=GetCustomerDetail";

		$.ajax({
			headers: {"Authorization": localStorage.getItem('<?php echo $subdomain;?>_customer_token')},
			dataType: 'json',
			type	:"POST",
			cache	:false,
			data	:dataStr,
			url		:"<?php echo $ServerAPIURL;?>customer.php",
			success:function(res)
			{
				if(res.success)
				{
					if(res.customerdetail.hasoutstanding)
					{
						var paybtndata	= '';
						if(res.customerdetail.isonlinepaymentareabycustomerid)
						{
							paybtndata	= '<button type="button" class="btn btn-primary paynow" onclick="GeneratePaymentLink()">Pay Now</button><button type="button" class="btn btn-primary processing" style="display:none;">Processing</button>';
						}

						var htmldata	= '<div style="font-weight:bold;">Outstanding : '+res.customerdetail.outstanding+'</div><div>'+paybtndata+'</div>';

						$(".outstandingwrapper").html(htmldata);
					}
				}
			}
		});
	}

	function GeneratePaymentLink()
	{
		var dataStr	= "Mode=GetPaymentLink";

		$(".paynow").hide();
		$(".processing").show();

		$.ajax({
			headers: {"Authorization": localStorage.getItem('<?php echo $subdomain;?>_customer_token')},
			dataType: 'json',
			type	:"POST",
			cache	:false,
			data	:dataStr,
			url		:"<?php echo $ServerAPIURL;?>customer.php",
			success:function(res)
			{
				if(res.success)
				{
					window.location.href=res.paymentlink;
				}
				else
				{
					$(".paynow").show();
					$(".processing").hide();
					alert(res.msg);
				}
			}
		});
	}

	$(document).ready(function(){
		const accesstoken	= localStorage.getItem('<?php echo $subdomain;?>_customer_token');

		if(accesstoken == null || accesstoken == undefined || accesstoken == "")
		{
			window.location.href = '<?php echo $loginpaymentlink;?>';
		}
		else
		{
			$(".loggedinnamewrapper").html('<h3 class="page-section-name text-center text-uppercase text-secondary mb-0">Welcome, '+localStorage.getItem('<?php echo $subdomain;?>_customer_name')+'</h3>');

			initData();
		}

		$(document).on("click", ".inactiveitem", function(){
			alert('Coming soon...');
		})

		$(document).on("click", ".logoutfromapp", function(){
			
			if(window.confirm("Are you sure? you want to logout."))
			{
				localStorage.setItem("<?php echo $subdomain;?>_customer_token",'');
				localStorage.setItem("<?php echo $subdomain;?>_customer_name",'');
				window.location.href = '<?php echo $loginpaymentlink;?>';
			}

		})
	})
	</script>
</html>
