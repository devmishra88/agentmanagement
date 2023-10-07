<?php
include_once "dbconfig.php";

$IsHomePage	= 1;

$GallaryArr	= array();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Home - <?php echo $clientname;?></title>
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
		include_once "ournewspapers.php";
		include_once "ourmagazines.php";
		include_once "gallery.php";
		?>
        <!-- About Section-->
        <section class="page-section bg-primary text-white mb-0" id="about">
            <div class="container">
                <!-- About Section Heading-->
                <h2 class="page-section-heading text-center text-uppercase text-white">About</h2>
                <!-- Icon Divider-->
                <div class="divider-custom divider-light">
                    <div class="divider-custom-line"></div>
                    <div class="divider-custom-icon"><i class="fas fa-star"></i></div>
                    <div class="divider-custom-line"></div>
                </div>
                <!-- About Section Content-->
                <div class="row">
                    <div class="col-lg-8 ms-auto"><p class="lead"><?php echo $aboutusdesc;?></p></div>
                </div>
				<?/*?>
                <div class="text-center mt-4">
                    <a class="btn btn-xl btn-outline-light" href="https://startbootstrap.com/theme/freelancer/">
                        <i class="fas fa-download me-2"></i>
                        Free Download!
                    </a>
                </div>
				<?*/?>
            </div>
        </section>
		<?php include_once "contactus.php";?>
		<?php include_once "footer.php";?>
        <script src="<?php echo $ServerURL;?>js/bootstrap.bundle.min.js"></script>
        <script src="<?php echo $ServerURL;?>js/scripts.js"></script>
        <script type="text/javascript">
		function validateEmail(elementValue)
		{
			var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
			return emailPattern.test(elementValue);
		}
		function submitContact()
		{
			var errmsg	= "";

			var name	= $.trim($("#name").val());
			var email	= $.trim($("#email").val());
			var phone	= $.trim($("#phone").val());
			var message	= $.trim($("#message").val());

			if(name == "")
			{
				errmsg	+= "Please enter name";
				$(".name").show();
			}
			else
			{
				$(".name").hide();
			}

			if(email == "")
			{
				errmsg	+= "Please enter email";
				$(".email").html('An email is required.');
				$(".email").show();
			}
			else if(!validateEmail(email))
			{
				errmsg	+= "Please enter valid email";
				$(".email").html('Please enter a valid email.');
				$(".email").show();
			}
			else
			{
				$(".email").hide();
			}

			if(phone == "")
			{
				errmsg	+= "Please enter phone";
				$(".phone").show();
			}
			else
			{
				$(".phone").hide();
			}

			if(message == "")
			{
				errmsg	+= "Please enter message";
				$(".message").show();
			}
			else
			{
				$(".message").hide();
			}

			if($.trim(errmsg) != "")
			{
				return false;
			}
			else
			{
				var dataStr	= "Mode=AddContactRequest&requesttype=contactrequest&clientid=<?php echo $ClientID;?>&name="+name+"&email="+email+"&phone="+phone+"&message="+message;

				$(".invalid-feedback").hide();

				$(".processingmsg").html("Processing...");

				/*$("#submitSuccessMessage").show();*/
				$("#submitSuccessMessage").removeClass('d-none');

				$("#submitButton").addClass('disabled');

				$("#submitButton").attr("disabled",true);

				setTimeout(function(){

					$.ajax({
						dataType: 'json',
						type	:"POST",
						cache	:false,
						data	:dataStr,
						url		:"<?php echo $ServerAPIURL;?>contact.php",
						success:function(res)
						{
							$("#submitButton").removeClass('disabled');
							$("#submitButton").attr("disabled",false);

							if(res.success)
							{
								$("#name").val('');
								$("#email").val('');
								$("#phone").val('');
								$("#message").val('');

								$(".processingmsg").html("Form submission successful!");

								setTimeout(function(){

									$("#submitSuccessMessage").addClass('d-none');

								},2000);
							}
						}
					});

				},500);
			}

			return false;
		}
		</script>
    </body>
</html>
