<?php
include_once "dbconfig.php";
$IsHomePage	= 0;

$PageTitle	= "Add Holiday";
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Add Holiday - <?php echo $clientname;?></title>
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
				<div>
					<b>Instructions:</b><br><br>
					1. Holiday must be added 2 days in advance.<br>
					2. Holiday should be minimum for 7 days.<br>
					3. Holiday can be deleted before 36 hours only.<br><br>
				</div>
                <div class="row justify-content-center">
                    <div class="col-lg-8 col-xl-7">
						<form name="frm" id="frm" onsubmit="return(submitForm())">
						  <div class="form-row">
							<div class="form-group">
							  <label for="startdate">Start Date</label>
							  <div style="display:flex">
								<input type="text" class="form-control formdatepicker" id="startdate" placeholder="MM/DD/YYYY">
							  </div>
							</div>
							<div class="form-group">
							  <label for="enddate">End Date</label>
							  <div style="display:flex">
								<input type="text" class="form-control formdatepicker" id="enddate" placeholder="MM/DD/YYYY">
							  </div>
							</div>
							<div class="form-group">
							  <label for="reason">Reason</label>
							  <textarea class="form-control" name="reason" id="reason" rows="3"></textarea>
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

		$(document).ready(function(){
			const accesstoken	= localStorage.getItem('<?php echo $subdomain;?>_customer_token');

			if(accesstoken == null || accesstoken == undefined || accesstoken == "")
			{
				window.location.href = '<?php echo $loginpaymentlink;?>';
			}
		});

		$(function(){

			var mindate	= new Date();

			var nextday	= new Date(mindate.getFullYear(),mindate.getMonth(),mindate.getDate()+2);

			$("#startdate").datepicker({
				changeMonth: true,
				changeYear: true,
				showOn: "button",
				buttonImage: "<?php echo $ServerURL;?>images/calendar.png",
				buttonImageOnly: true,
				minDate:nextday,
				/*onClose: function(selectedDate){*/
				onSelect: function(selectedDate){

					var checkdate	= new Date(selectedDate);

					var checkmonth	= checkdate.getMonth()+1;

					if(checkmonth < 10)
					{
					  checkmonth = "0"+checkmonth;
					}

					/*var checkday = checkdate.getDate()+7;*/
					var checkday = checkdate.getDate();

					if(checkday < 10)
					{
					  checkday = "0"+checkday;
					}

					var enddate	= checkmonth+'/'+checkday+'/'+checkdate.getFullYear();

					var tempdate = new Date(enddate);
					tempdate.setDate(tempdate.getDate() + 7);

					var tempEndMonth	= tempdate.getMonth()+1;

					if(tempEndMonth < 10)
					{
						tempEndMonth = "0"+tempEndMonth;
					}

					var tempEndDay = tempdate.getDate();

					if(tempEndDay < 10)
					{
						tempEndDay = "0"+tempEndDay;
					}

					var tempEndDate	= tempEndMonth+'/'+tempEndDay+'/'+tempdate.getFullYear();

					$("#enddate").datepicker("option", "minDate", tempEndDate);
				}
			 });

			$("#enddate").datepicker({
				changeMonth: true,
				changeYear: true,
				showOn: "button",
				buttonImage: "<?php echo $ServerURL;?>images/calendar.png",
				buttonImageOnly: true,
				onClose: function(selectedDate) {
					$("#startdate").datepicker("option", "maxDate", selectedDate);
				}
			 });
		});

		function submitForm()
		{
			var errmsg	= "";

			var startdate	= $.trim($("#startdate").val());
			var enddate		= $.trim($("#enddate").val());
			var reason		= $.trim($("#reason").val());

			if(startdate == "")
			{
				errmsg	+= "Please select start date. \n";
			}

			if(enddate == "")
			{
				errmsg	+= "Please select end date. \n";
			}

			/*console.log(Date.getTime(startdate));
			console.log(Date.getTime(enddate));

			return false;*/

			if($.trim(errmsg) != "")
			{
				alert(errmsg);
				return false;
			}
			else
			{
				var dataStr	= "Mode=AddHoliday&ClientID=<?php echo $ClientID;?>&CustomerID=&CustomerType=1&EndDate="+enddate+"&InventoryID=&InventoryType0&Reason="+reason+"&StartDate="+startdate;

				$("#submitButton").addClass('disabled');

				$("#submitButton").attr("disabled",true);

				setTimeout(function(){

					$.ajax({
						headers: {"Authorization": localStorage.getItem('<?php echo $subdomain;?>_customer_token')},
						dataType: 'json',
						type	:"POST",
						cache	:false,
						data	:dataStr,
						url		:"<?php echo $ServerAPIURL;?>holiday.php",
						success:function(res)
						{
							$("#submitButton").removeClass('disabled');
							$("#submitButton").attr("disabled",false);

							alert(res.msg);

							if(res.success)
							{
								$("#reason").val('');
							}
						}
					});

				},500);
			}

			return false;
		}
	</script>
</html>