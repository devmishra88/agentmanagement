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
        <title><?php echo $clientname;?> - orlopay.com</title>
        <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
        <script src="<?php echo $ServerURL;?>js/all.js" crossorigin="anonymous"></script>
        <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css" />
        <link href="https://fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic" rel="stylesheet" type="text/css" />
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
                <h2 class="page-section-heading text-center text-uppercase text-secondary mb-0">Login</h2>
                <!-- Icon Divider-->
                <div class="divider-custom">
                    <div class="divider-custom-line"></div>
                    <div class="divider-custom-icon"><i class="fas fa-star"></i></div>
                    <div class="divider-custom-line"></div>
                </div>
                <!-- Contact Section Form-->
                <div class="row justify-content-center">
                    <div class="col-lg-8 col-xl-7">
                        <form name="contactForm" id="contactForm" onsubmit="return(submitLogin())">
							<input type="hidden" name="isotpavailable" id="isotpavailable" value="0">
							<input type="hidden" name="orgphone" id="orgphone" value="">
                            <!-- Phone number input-->
                            <div class="form-floating mb-3">
                                <input class="form-control" id="phone" type="tel" placeholder="(123) 456-7890" data-sb-validations="required" maxlength="10" />
                                <label for="phone">Phone number</label>
                                <div class="invalid-feedback phone" data-sb-feedback="phone:required">A phone number is required.</div>
                            </div>
                            <div class="otpwrapper form-floating mb-3 d-none">
                                <input class="form-control" id="otp" type="number" placeholder="XXXXXX" data-sb-validations="required"  maxlength="6"/>
                                <label for="otp">Login OTP</label>
                                <div class="invalid-feedback otp" data-sb-feedback="otp:required">A login otp is required.</div>
                            </div>
                            <div class="d-none" id="submitSuccessMessage">
                                <div class="text-center mb-3">
                                    <div class="fw-bolder processingmsg">Form submission successful!</div>
                                </div>
                            </div>
                            <div class="d-none" id="submitErrorMessage"><div class="text-center text-danger mb-3 errormessagewrapper">Error sending message!</div></div>
							<div style="display:flex; justify-content:space-between; align-items:center;">
								<div>
									<button class="btn btn-primary btn-xl" id="submitButton" type="submit">Login</button>
								</div>
								<div class="resendwrapper"></div>
							</div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
		<?php include_once "footer.php";?>
        <script src="<?php echo $ServerURL;?>js/bootstrap.bundle.min.js"></script>
        <script src="<?php echo $ServerURL;?>js/scripts.js"></script>
        <script type="text/javascript">
		function submitLogin()
		{
			var errmsg	= "";

			var phone			= $.trim($("#phone").val());
			var orgphone		= $.trim($("#orgphone").val());
			var isotpavailable	= $.trim($("#isotpavailable").val());
			var otp				= $.trim($("#otp").val());

			var phonetoverify	= phone;

			if(isotpavailable < 1)
			{
				if(phone == "")
				{
					errmsg	+= "Please enter phone";
					$(".phone").show();
				}
				else
				{
					$(".phone").hide();
				}
			}
			else
			{
				if(otp == "")
				{
					errmsg	+= "Please enter otp";
					$(".otp").show();
				}
				else
				{
					$(".otp").hide();
				}
				phonetoverify	= orgphone;
			}

			if($.trim(errmsg) != "")
			{
				return false;
			}
			else
			{
				if(isotpavailable < 1)
				{
					var dataStr	= "Mode=SendLoginOtp&clientid=<?php echo $ClientID;?>&phone="+phonetoverify;
				}
				else
				{
					var dataStr	= "Mode=VerifyCustomerLogin&clientid=<?php echo $ClientID;?>&phone="+phonetoverify+"&isotpavailable="+isotpavailable+"&otp="+otp;
				}

				$(".invalid-feedback").hide();

				$(".processingmsg").html("Processing...");

				$("#submitSuccessMessage").removeClass('d-none');

				$("#submitButton").addClass('disabled');

				$("#submitButton").attr("disabled",true);

				setTimeout(function(){

					$.ajax({
						dataType: 'json',
						type	:"POST",
						cache	:false,
						data	:dataStr,
						url		:"<?php echo $ServerAPIURL;?>customer.php",
						success:function(res)
						{
							$("#submitButton").removeClass('disabled');
							$("#submitButton").attr("disabled",false);

							$(".processingmsg").html('');
							$("#submitSuccessMessage").addClass('d-none');

							if(res.success)
							{
								$("#orgphone").val($("#phone").val());
								$("#isotpavailable").val(1);

								setOtpPhone();

								if(res.isotpverified)
								{
									localStorage.setItem("pay_customer_token",res.accesstoken);
									localStorage.setItem("pay_customer_name",res.customername);
									window.location.href = '/dashboard.php';
								}

								$(".otpwrapper").removeClass('d-none');
							}
							else
							{
								$(".errormessagewrapper").html(res.msg);
								$("#submitErrorMessage").removeClass('d-none');

								setTimeout(function(){

									$(".errormessagewrapper").html('');
									$("#submitErrorMessage").addClass('d-none');

								},2000);

							}
						}
					});

				},500);
			}

			return false;
		}

		function setOtpPhone(){
		  var menuredirectiontime = 60;

		  $(".resendwrapper").html('Resend OTP in <b>'+menuredirectiontime+' s</b>');

		  var menuTicker = setInterval(function(){
			if(menuredirectiontime < 2)
			{
				clearInterval(menuTicker);
				$(".resendwrapper").html('<button type="button" class="btn btn-primary btn-xl" onclick="initResendOTP()">Resend OTP</button>');
			}
			else
			{
				var resendtimer	= menuredirectiontime - 1;

				$(".resendwrapper").html('Resend OTP in <b>'+(menuredirectiontime - 1)+' s</b>');
			}
			menuredirectiontime -= 1;
		  }, 1000);
		}

		function initResendOTP(){
			$("#submitButton").removeClass('disabled');
			$("#submitButton").attr("disabled",false);

			$(".processingmsg").html('');
			$("#submitSuccessMessage").addClass('d-none');

			$("#orgphone").val('');
			$("#isotpavailable").val('');

			$(".otpwrapper").addClass('d-none');

			$(".resendwrapper").html('');

			submitLogin();
		}
		</script>
    </body>
</html>
