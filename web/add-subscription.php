<?php
include_once "dbconfig.php";
$IsHomePage	= 0;

$PageTitle	= "Add Subscription";
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Add Subscription - <?php echo $clientname;?></title>
        <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
        <script src="<?php echo $ServerURL;?>js/all.js" crossorigin="anonymous"></script>
        <link href="//fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css" />
        <link href="//fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic" rel="stylesheet" type="text/css" />
        <link href="<?php echo $ServerURL;?>css/styles.css?t=<?php echo time();?>" rel="stylesheet" />
        <script src="<?php echo $ServerURL;?>js/jquery.min.js"></script>
		<script src="<?=$ServerURL?>js/jquery-ui.js"></script>
		<link rel="stylesheet" href="<?=$ServerURL?>js/jquery-ui.css">
    </head>
    <body id="page-top">
		<?php
		include_once "header.php";
		?>
		<section class="page-section portfolio" id="ournewspapers">
			<div class="container">
				<br><br>
				<h2 class="page-section-heading text-center text-uppercase text-secondary mb-0">
				<?php echo $PageTitle;?>
				</h2>
				<!-- Icon Divider-->
				<div class="divider-custom">
					<div class="divider-custom-line"></div>
					<div class="divider-custom-icon"><i class="fas fa-star"></i></div>
					<div class="divider-custom-line"></div>
				</div>
                <div class="row justify-content-center">
                    <div class="col-lg-8 col-xl-7">
						<form name="frm" id="frm" onsubmit="return(submitForm())">
						  <div class="form-row">
							<div class="form-group">
							  <label for="stock">Select Stock</label>
							  <select id="stock" class="form-control">
								<option value="">-Select-</option>
							  </select>
							</div>
							<div class="form-group">
							  <label for="subscriptiondate">Subscription Date</label>
							  <div style="display:flex">
								<input type="text" class="form-control formdatepicker" id="subscriptiondate" placeholder="MM/DD/YYYY">
							  </div>
							</div>
						  </div>
						  <br />
						  <button type="submit" id="submitButton" class="btn btn-primary">Save Now</button>
						</form>
                    </div>
                </div>
			</div>
		</section>
		<?php include_once "footer.php";?>
        <script src="<?php echo $ServerURL;?>js/bootstrap.bundle.min.js"></script>
        <script src="<?php echo $ServerURL;?>js/scripts.js"></script>
    </body>
	<script type="text/javascript">

		function initData(){

			var dataStr	= "Mode=GetCustomerInventory";

			$.ajax({
				headers: {"Authorization": localStorage.getItem('<?php echo $subdomain;?>_customer_token')},
				dataType: 'json',
				type	:"POST",
				cache	:false,
				data	:dataStr,
				url		:"<?php echo $ServerAPIURL;?>customer.php",
				success:function(res)
				{
					var availablestock	= "<option value=''>-Select-</option>";

					if(res.success)
					{
						var inventorylistnum	= Object.keys(res.inventorylist).length;

						if(inventorylistnum > 0)
						{
							var datakey;

							for(datakey in res.inventorylist)
							{
								var datakeyrows = res.inventorylist[datakey];

								var id			= datakeyrows.id;
								var title		= datakeyrows.title;
								var recordlist	= datakeyrows.recordlist;

								var recordlistnum	= Object.keys(recordlist).length;

								if(recordlistnum > 0)
								{
									var datakey2;

									for(datakey2 in recordlist)
									{
										var datakeyrows2 = recordlist[datakey2];

										var stockid		= datakeyrows2.id;
										var stockname	= datakeyrows2.name;
										var isassigned	= datakeyrows2.isassigned;

										if(!isassigned)
										{
											availablestock	+= "<option value='"+stockid+"'>"+stockname+"</option>";
										}
									}
								}
							}
						}
					}

					$("#stock").html(availablestock);
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
				initData();
			}
		});

		$(function(){
			$(".formdatepicker").datepicker({
				changeMonth: true,
				changeYear: true,
				showOn: "button",
				buttonImage: "<?php echo $ServerURL;?>images/calendar.png",
				buttonImageOnly: true,
				minDate:new Date()
			 });
		});

		function submitForm()
		{
			var errmsg	= "";

			var stock				= $.trim($("#stock").val());
			var subscriptiondate	= $.trim($("#subscriptiondate").val());

			if(stock == "")
			{
				errmsg	+= "Please select a stock. \n";
			}

			if(subscriptiondate == "")
			{
				errmsg	+= "Please select a subscription date. \n";
			}

			if($.trim(errmsg) != "")
			{
				alert(errmsg);
				return false;
			}
			else
			{
				var dataStr	= "Mode=AddSubscription&clientid=<?php echo $ClientID;?>&inventoryid="+stock+"&subscriptiondate="+subscriptiondate;

				$("#submitButton").addClass('disabled');

				$("#submitButton").attr("disabled",true);

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
							$("#submitButton").removeClass('disabled');
							$("#submitButton").attr("disabled",false);

							alert(res.msg);

							if(res.success)
							{
								$("#stock").val('');
								$("#subscriptiondate").val('');
								initData();
							}
						}
					});

				},500);
			}

			return false;
		}
	</script>
</html>