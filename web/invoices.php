<?php
include_once "dbconfig.php";
$IsHomePage	= 0;

$PageTitle	= "Invoices";
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Invoices - <?php echo $clientname;?></title>
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

			var dataStr	= "Mode=GetCustomerInvoices";

			setTimeout(function(){

				$.ajax({
					headers: {"Authorization": localStorage.getItem('<?php echo $subdomain;?>_customer_token')},
					dataType: 'json',
					type	:"POST",
					cache	:false,
					data	:dataStr,
					url		:"<?php echo $ServerAPIURL;?>invoice.php",
					success:function(res)
					{
						if(res.success)
						{
							var invoicelistnum	= Object.keys(res.invoicelist).length;
							if(invoicelistnum > 0)
							{
								var invoice;

								var invoicedata	= "";

								invoicedata	+= '<table class="table">';
								  invoicedata	+= '<thead class="thead-dark">';
									invoicedata	+= '<tr >';
									  invoicedata	+= '<th scope="col">#</th>';
									  invoicedata	+= '<th scope="col">Date</th>';
									  invoicedata	+= '<th scope="col">Amount</th>';
									invoicedata	+= '</tr>';
								  invoicedata	+= '</thead>';
								  invoicedata	+= '<tbody>';

								for(invoice in res.invoicelist)
								{
									var invoicerows = res.invoicelist[invoice];

									var id			= Number(invoicerows.id);
									var createdon	= invoicerows.createdon;
									var clientid	= invoicerows.clientid;
									var customerid	= invoicerows.customerid;
									var amount		= invoicerows.amount;

									invoicedata	+= '<tr onclick="GenerateInvoicePDF('+clientid+','+customerid+','+id+')" style="cursor:pointer">';
									  invoicedata	+= '<th scope="row">'+id+'</th>';
									  invoicedata	+= '<td>'+createdon+'</td>';
									  invoicedata	+= '<td class="text-end">'+amount+'</td>';
									invoicedata	+= '</tr>';
								}
								invoicedata	+= '</tbody>';
								invoicedata	+= '</table>';

								$('.datalistcontainer').html(invoicedata);
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
	function GenerateInvoicePDF(clientid,customerid,invoiceid)
		{
			var dataStr = "Mode=GetInvoicePDF&clientid="+clientid+"&customerid="+customerid+"&invoiceid="+invoiceid;

			$.ajax({
				dataType: 'json',
				type	:'POST',
				cache	:false,
				data	:dataStr,
				url		:'<?php echo $ServerAPIURL;?>invoice.php',
				success	:function(res)
				{
					if(res.success)
					{
						window.open(res.pdffilepath);
						return false;
					}
					else
					{
						alert(res.msg);
						return false;
					}
				}
			});
		}
	</script>
</html>
