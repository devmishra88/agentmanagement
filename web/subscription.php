<?php
include_once "dbconfig.php";
$IsHomePage	= 0;

$PageTitle	= "Subscription";
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Subscription - <?php echo $clientname;?></title>
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
				<div style="display:flex; justify-content:space-between; align-items:center;" class="mb-4">
					<div>
						<button type="button" class="btn btn-primary" onclick="location.href='<?php echo $siteprefix;?>add-subscription.php'">Add More Subscription</button>
					</div>
				</div>
				<div class="datalistcontainer table-responsive"></div>
			</div>
		</section>
		<?php include_once "footer.php";?>
        <script src="<?php echo $ServerURL;?>js/bootstrap.bundle.min.js"></script>
        <script src="<?php echo $ServerURL;?>js/scripts.js"></script>
    </body>
	<script type="text/javascript">

	function initData(){

		$(".datalistcontainer").html('<div class="text-center text-danger mb-3">Loading...</div>');

		var dataStr	= "Mode=GetCustomerInventoryLog";

		setTimeout(function(){

			$.ajax({
				headers: {"Authorization": localStorage.getItem('<?php echo $subdomain;?>_customer_token')},
				dataType: 'json',
				type	:"POST",
				cache	:false,
				data	:dataStr,
				url		:"<?php echo $ServerAPIURL;?>subscription.php",
				success:function(res)
				{
					if(res.success)
					{
						var recordlistnum	= Object.keys(res.recordlist).length;
						if(recordlistnum > 0)
						{
							var datakey;

							var parseddata	= "";

							parseddata	+= '<table class="table">';
							  parseddata	+= '<thead class="thead-dark">';
								parseddata	+= '<tr>';
								  parseddata	+= '<th scope="col">Item</th>';
								  parseddata	+= '<th scope="col">Start Date</th>';
								  parseddata	+= '<th scope="col">End Date</th>';
								  parseddata	+= '<th scope="col">Qty.</th>';
								  parseddata	+= '<th scope="col">Option</th>';
								parseddata	+= '</tr>';
							  parseddata	+= '</thead>';
							  parseddata	+= '<tbody>';

							for(datakey in res.recordlist)
							{
								var datakeyrows = res.recordlist[datakey];

								var inventoryid	= datakeyrows.inventoryid;
								var date		= datakeyrows.activitydate;
								var unsubdate	= datakeyrows.unsubscribedate;
								var name		= datakeyrows.name;
								var quantity	= datakeyrows.quantity;
								var isclosed	= Number(datakeyrows.isclosed);

								var statustxt	= "Inactive";

								if(!isclosed)
								{
									statustxt	= "Active";
								}

								parseddata	+= '<tr>';
								  parseddata	+= '<th scope="row">'+name+'</th>';
								  parseddata	+= '<td>'+date+'</td>';
								  parseddata	+= '<td>'+unsubdate+'</td>';
								  parseddata	+= '<td>'+quantity+'</td>';
								if(!isclosed)
								{
								  parseddata	+= '<td><button type="button" class="btn btn-danger" onclick="closeSubscription(\''+inventoryid+'\')">Close</button></td>';
								}
								else
								{
								  parseddata	+= '<td>--</td>';
								}
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

	function closeSubscription(inventoryid){

		if(window.confirm("Are you sure? you want to close subscription."))
		{
			/*var dataStr	= "Mode=CloseSubscription&inventoryid="+inventoryid;*/
			var dataStr	= "Mode=AddContactRequest&inventoryid="+inventoryid+"&requesttype=subscriptionclosure";

			$.ajax({
				headers: {"Authorization": localStorage.getItem('<?php echo $subdomain;?>_customer_token')},
				dataType: 'json',
				type	:"POST",
				cache	:false,
				data	:dataStr,
				url		:"<?php echo $ServerAPIURL;?>contact.php",
				success:function(res)
				{
					alert(res.msg);

					/*if(res.success)
					{
						initData();
					}
					else
					{
						alert(res.msg);
					}*/
				}
			});
		}
	}

	$(document).ready(function(){
		const accesstoken	= localStorage.getItem('<?php echo $subdomain;?>_customer_token');

		if(accesstoken == null || accesstoken == undefined || accesstoken == "")
		{
			window.location.href = '<?php echo $loginpaymentlink;?>';
		}
		else
		{
			initData();
		}
	});
	</script>
</html>