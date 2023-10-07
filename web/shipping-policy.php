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
        <title>Shipping Policy - <?php echo $clientname;?></title>
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
        <section class="page-section" id="login">
			<br>
			<br>
			<br>
            <div class="container">
                <!-- Contact Section Heading-->
                <h2 class="page-section-heading text-center text-uppercase text-secondary mb-0">Shipping Policy</h2>
                <!-- Icon Divider-->
                <div class="divider-custom">
                    <div class="divider-custom-line"></div>
                    <div class="divider-custom-icon"><i class="fas fa-star"></i></div>
                    <div class="divider-custom-line"></div>
                </div>
                <!-- Contact Section Form-->
                <div class="row justify-content-center">
                    <div class="col-lg-8 col-xl-12">
                        <p>Updated at 2022-01-01</p>
							<p>http://<?php echo $subdomain;?>.orlopay.com (&ldquo;we&rdquo; and &ldquo;us&rdquo;) is the operator of (http://<?php echo $subdomain;?>.orlopay.com). By placing an order through this Website you will be agreeing to the terms below. These are provided to ensure both parties are aware of and agree upon this arrangement to mutually protect and set expectations on our service.</p>
							<ul>
							<li>General</li>
							</ul>
							<p>Subject to stock availability. We try to maintain accurate stock counts on our website but from time-to-time there may be a stock discrepancy and we will not be able to fulfill all your items at time of purchase. In this instance, we will fulfill the available products to you, and contact you about whether you would prefer to await restocking of the backordered item or if you would prefer for us to process a refund.</p>
							<ol start="2">
							<li>Shipping Costs</li>
							</ul>
							<p>Free shipping</p>
							<ol start="3">
							<li>Returns</li>
							</ul>
							<p>3.1 Return Due To Change Of Mind</p>
							<p>http://<?php echo $subdomain;?>.orlopay.com will happily accept returns due to change of mind as long as a request to return is received by us within 9 days of receipt of item and are returned to us in original packaging, unused and in resellable condition.Return shipping will be paid at the customers expense and will be required to arrange their own shipping.Once returns are received and accepted, refunds will be processed to store credit for a future purchase. We will notify you once this has been completed through email.</p>
							<p>will refund the value of the goods returned but will NOT refund the value of any shipping paid.</p>
							<p>3.2 Warranty Returns</p>
							<p>http://<?php echo $subdomain;?>.orlopay.com will happily honor any valid warranty claims, provided a claim is submitted within 90 days of receipt of items.</p>
							<p>Customers will be required to pre-pay the return shipping, however we will reimburse you upon successful warranty claim.</p>
							<p>Upon return receipt of items for warranty claim, you can expect http://<?php echo $subdomain;?>.orlopay.com to process your warranty claim within 7 days.</p>
							<p>Once warranty claim is confirmed, you will receive the choice of:</p>
							<p>(a) refund to your payment method</p>
							<p>(b) a refund in store credit</p>
							<p>(c) a replacement item sent to you (if stock is available)</p>
							<ol start="4">
							<li>Delivery Terms</li>
							</ul>
							<p>4.1 Transit Time Domestically</p>
							<p>In general, domestic shipments are in transit for 2 &ndash; 7 days</p>
							<p>4.2 Transit time Internationally</p>
							<p>Generally, orders shipped internationally are in transit for 4 &ndash; 22 days. This varies greatly depending on the courier you have selected. We are able to offer a more specific estimate when you are choosing your courier at checkout.</p>
							<p>4.3 Dispatch Time</p>
							<p>Orders are usually dispatched within 2 business days of payment of order</p>
							<p>Our warehouse operates on Monday &ndash; Friday during standard business hours, except on national holidays at which time the warehouse will be closed. In these instances, we take steps to ensure shipment delays will be kept to a minimum.</p>
							<p>4.4 Change Of Delivery Address</p>
							<p>For change of delivery address requests, we are able to change the address at any time before the order has been dispatched.</p>
							<p>4.5 P.O. Box Shipping</p>
							<p>http://<?php echo $subdomain;?>.orlopay.com will ship to P.O. box addresses using postal services only. We are unable to offer couriers services to these locations.</p>
							<p>4.6 Military Address Shipping</p>
							<p>We are able to ship to military addresses using USPS. We are unable to offer this service using courier services.</p>
							<p>4.7 Items Out Of Stock</p>
							<p>If an item is out of stock, we will wait for the item to be available before dispatching your order. Existing items in the order will be reserved while we await this item.</p>
							<p>4.8 Delivery Time Exceeded</p>
							<p>If delivery time has exceeded the forecasted time, please contact us so that we can conduct an investigation.</p>
							<ol start="5">
							<li>Tracking Notifications</li>
							</ul>
							<p>Upon dispatch, customers will receive a tracking link from which they will be able to follow the progress of their shipment based on the latest updates made available by the shipping provider.</p>
							<ol start="6">
							<li>Parcels Damaged In Transit</li>
							</ul>
							<p>If you find a parcel is damaged in-transit, if possible, please reject the parcel from the courier and get in touch with our customer service. If the parcel has been delivered without you being present, please contact customer service with next steps.</p>
							<ol start="7">
							<li>Duties &amp; Taxes</li>
							</ul>
							<p>7.1 Sales Tax</p>
							<p>Sales tax has already been applied to the price of the goods as displayed on the website</p>
							<p>7.2 Import Duties &amp; Taxes</p>
							<p>Import duties and taxes for international shipments may be liable to be paid upon arrival in destination country. This varies by country, and http://<?php echo $subdomain;?>.orlopay.com encourage you to be aware of these potential costs before placing an order with us.</p>
							<p>If you refuse to to pay duties and taxes upon arrival at your destination country, the goods will be returned to http://<?php echo $subdomain;?>.orlopay.com at the customers expense, and the customer will receive a refund for the value of goods paid, minus the cost of the return shipping. The cost of the initial shipping will not be refunded.</p>
							<ol start="8">
							<li>Cancellations</li>
							</ul>
							<p>If you change your mind before you have received your order, we are able to accept cancellations at any time before the order has been dispatched. If an orderhas already been dispatched, please refer to our refund policy.</p>
							<ol start="9">
							<li>Insurance</li>
							</ul>
							<p>Parcels are insured for loss and damage up to the value as stated by the courier.</p>
							<p>9.1 Process for parcel damaged in-transit</p>
							<p>We will process a refund or replacement as soon as the courier has completed their investigation into the claim.</p>
							<p>9.2 Process for parcel lost in-transit</p>
							<p>We will process a refund or replacement as soon as the courier has conducted an investigation and deemed the parcel lost.</p>
							<p>Shipping in India</p>
							<p>&ndash; Free shipping on all products for COD and prepaid orders.</p>
							<p>&ndash; Once the order is placed, it takes 5 to 7 business days to dispatch after placing the order. Then 4 to 5 business days for it to get delivered.</p>
							<p>&ndash; So, the estimated time would be 10 to 15 working days. You will receive the tracking details once your order has been shipped.</p>
							<p>&ndash; These timelines may be affected due to current situations.</p>
							<p>International Shipping</p>
							<p>We have a customer friendly return policy.</p>
							<p>&ndash; Once the order is placed, it takes 15 to 20 working days to dispatch after placing the order.</p>
							<p>&ndash; If customization is added it will take more 4 to 5 working days to dispatch.</p>
							<p>&ndash; So, the estimated time would be 20 to 25 working days for the order to get delivered. You will receive the tracking details once your order has been shipped.</p>
							<p>&ndash; These timelines may be affected due to current situations.</p>

                    </div>
                </div>
            </div>
        </section>
		<?php include_once "footer.php";?>
        <script src="<?php echo $ServerURL;?>js/bootstrap.bundle.min.js"></script>
        <script src="<?php echo $ServerURL;?>js/scripts.js"></script>
    </body>
</html>
