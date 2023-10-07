<?php
include_once "dbconfig.php";
$IsHomePage	= 0;

$PageTitle	= "Ledger";
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Ledger - <?php echo $clientname;?></title>
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

			var dataStr	= "Mode=GetLedger";

			setTimeout(function(){

				$.ajax({
					headers: {"Authorization": localStorage.getItem('<?php echo $subdomain;?>_customer_token')},
					dataType: 'json',
					type	:"POST",
					cache	:false,
					data	:dataStr,
					url		:"<?php echo $ServerAPIURL;?>ledger.php",
					success:function(res)
					{
						if(res.success)
						{
							var ledgerlistnum	= Object.keys(res.ledgerlist).length;
							if(ledgerlistnum > 0)
							{
								var datakey;

								var parseddata	= "";

								parseddata	+= '<table class="table">';
								  parseddata	+= '<thead class="thead-dark">';
									parseddata	+= '<tr>';
									  parseddata	+= '<th scope="col">Date</th>';
									  parseddata	+= '<th scope="col">Item</th>';
									  parseddata	+= '<th scope="col">Pay. ID</th>';
									  parseddata	+= '<th scope="col">Due</th>';
									  parseddata	+= '<th scope="col">Paid</th>';
									  parseddata	+= '<th scope="col">Bal.</th>';
									parseddata	+= '</tr>';
								  parseddata	+= '</thead>';
								  parseddata	+= '<tbody>';

								for(datakey in res.ledgerlist)
								{
									var datakeyrows = res.ledgerlist[datakey];

									var balance		= datakeyrows.balance;
									var date		= datakeyrows.date;
									var due			= datakeyrows.due;
									var item		= datakeyrows.item;
									var paid		= datakeyrows.paid;
									var paymentid	= datakeyrows.paymentid;

									if(paymentid == null)
									{
										paymentid	= "--";
									}

									if(item == 'Opening Balance')
									{
										due		= '--';
										paid	='--';
									}

									var balclass ='ledgerbalancepositive';

									if(balance < 1)
									{
										balclass='ledgerbalancenegative';
									}

									parseddata	+= '<tr>';
									  parseddata	+= '<th scope="row">'+date+'</th>';
									  parseddata	+= '<td>'+item+'</td>';
									  parseddata	+= '<td>'+paymentid+'</td>';
									  parseddata	+= '<td>'+due+'</td>';
									  parseddata	+= '<td>'+paid+'</td>';
									  parseddata	+= '<td class="'+balclass+' text-end">'+balance+'</td>';
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