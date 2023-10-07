<?php
include_once "dbconfig.php";
$IsHomePage	= 0;

$PageTitle	= "Payment History";
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title><?php echo $clientname;?> - orlopay.com</title>
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
		<section class="page-section portfolio">
			<div class="container">
				<h2 class="page-section-heading text-center text-uppercase text-secondary mb-0">
				<?php echo $PageTitle;?>
				</h2>
				<!-- Icon Divider-->
				<div class="divider-custom">
					<div class="divider-custom-line"></div>
					<div class="divider-custom-icon"><i class="fas fa-star"></i></div>
					<div class="divider-custom-line"></div>
				</div>
				<div class="datalistcontainer table-responsive"></div>
			</div>
		</section>
		<?php include_once "footer.php";?>
        <script src="<?php echo $ServerURL;?>js/bootstrap.bundle.min.js"></script>
        <script src="<?php echo $ServerURL;?>js/scripts.js"></script>
    </body>
	<script type="text/javascript">
	$(document).ready(function(){
		const accesstoken	= localStorage.getItem('<?php echo $subdomain;?>_customer_token');

		if(accesstoken == null || accesstoken == undefined || accesstoken == "")
		{
			window.location.href = '<?php echo $loginpaymentlink;?>';
		}
		else
		{
			$(".datalistcontainer").html('<div class="text-center text-danger mb-3">Loading...</div>');

			var dataStr	= "Mode=GetAllCustomerPayment";

			setTimeout(function(){

				$.ajax({
					headers: {"Authorization": localStorage.getItem('<?php echo $subdomain;?>_customer_token')},
					dataType: 'json',
					type	:"POST",
					cache	:false,
					data	:dataStr,
					url		:"<?php echo $ServerAPIURL;?>payments.php",
					success:function(res)
					{
						if(res.success)
						{
							var datasetnum	= Object.keys(res.recordset.paymentlist).length;
							if(datasetnum > 0)
							{
								var datakey;

								var parseddata	= "";

								parseddata	+= '<table class="table">';
								  parseddata	+= '<thead class="thead-dark">';
									parseddata	+= '<tr>';
									  parseddata	+= '<th scope="col">#</th>';
									  parseddata	+= '<th scope="col">Date</th>';
									  parseddata	+= '<th scope="col">Pay. ID</th>';
									  parseddata	+= '<th scope="col">Payment</th>';
									parseddata	+= '</tr>';
								  parseddata	+= '</thead>';
								  parseddata	+= '<tbody>';

								for(datakey in res.recordset.paymentlist)
								{
									var datakeyrows = res.recordset.paymentlist[datakey];

									var amount				= datakeyrows.amount;
									var customerpaymentid	= datakeyrows.customerpaymentid;
									var date				= datakeyrows.date;
									var serialno			= datakeyrows.serialno;

									parseddata	+= '<tr>';
									  parseddata	+= '<th scope="row">'+serialno+'</th>';
									  parseddata	+= '<td>'+date+'</td>';
									  parseddata	+= '<td>'+customerpaymentid+'</td>';
									  parseddata	+= '<td class="text-end">'+amount+'</td>';
									parseddata	+= '</tr>';
								}
								parseddata	+= '</tbody>';
								parseddata	+= '</table>';

								$('.datalistcontainer').html(parseddata);
							}
						}
						else
						{
							$(".datalistcontainer").html('<div class="text-center text-danger mb-3">'+res.msg+'</div>');
						}
					}
				});

			},500);
		}
	})
	</script>
</html>