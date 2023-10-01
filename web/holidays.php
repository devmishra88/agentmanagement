<?php
include_once "dbconfig.php";
$IsHomePage	= 0;

$PageTitle	= "Holidays";
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Holiday - <?php echo $clientname;?></title>
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
				<div style="display:flex; justify-content:space-between; align-items:center;" class="mb-4">
					<div>
						<button type="button" class="btn btn-primary" onclick="location.href='<?php echo $siteprefix;?>add-holiday.php'">Add More Holiday</button>
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

		var dataStr	= "Mode=GetHolidayList";

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
								  parseddata	+= '<th scope="col">Start Date</th>';
								  parseddata	+= '<th scope="col">End Date</th>';
								  parseddata	+= '<th scope="col">Reason</th>';
								  parseddata	+= '<th scope="col">Option</th>';
								parseddata	+= '</tr>';
							  parseddata	+= '</thead>';
							  parseddata	+= '<tbody>';

							for(datakey in res.recordlist)
							{
								var datakeyrows = res.recordlist[datakey];

								var startdate		= datakeyrows.startdate;
								var enddate			= datakeyrows.enddate;
								var reason			= datakeyrows.reason;
								var recordid		= datakeyrows.id;
								var canuserdelete	= datakeyrows.canuserdelete;

								parseddata	+= '<tr>';
								  parseddata	+= '<th scope="row">'+startdate+'</th>';
								  parseddata	+= '<td>'+enddate+'</td>';
								  parseddata	+= '<td>'+reason+'</td>';
								  parseddata	+= '<td><button type="button" class="btn btn-danger" onclick="deleteHoliday(\''+recordid+'\')">Delete</button></td>';
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

	function deleteHoliday(recordid){

		if(window.confirm("Are you sure? you want to delete holiday."))
		{
			var dataStr	= "Mode=DeleteHoliday&HolidayID="+recordid;

			$.ajax({
				headers: {"Authorization": localStorage.getItem('<?php echo $subdomain;?>_customer_token')},
				dataType: 'json',
				type	:"POST",
				cache	:false,
				data	:dataStr,
				url		:"<?php echo $ServerAPIURL;?>holiday.php",
				success:function(res)
				{
					if(res.success)
					{
						initData();
					}
					else
					{
						alert(res.msg);
					}
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