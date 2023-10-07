        <!-- Navigation-->
        <nav class="navbar navbar-expand-lg bg-secondary text-uppercase fixed-top" id="mainNav">
            <div class="container">
                <a class="navbar-brand" href="/#page-top"><?php echo $clientname;?></a>
                <button class="navbar-toggler text-uppercase font-weight-bold bg-primary text-white rounded" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                    Menu
                    <i class="fas fa-bars"></i>
                </button>
                <div class="collapse navbar-collapse" id="navbarResponsive">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item mx-0 mx-lg-1"><a class="nav-link py-3 px-0 px-lg-2 rounded" href="/">Home</a></li>
                        <li class="nav-item mx-0 mx-lg-1 usernavbox"><a class="nav-link py-3 px-0 px-lg-2 rounded" href="<?php echo $siteprefix;?>bill-payment.php">Bill Payment</a></li>
                        <li class="nav-item mx-0 mx-lg-1"><a class="nav-link py-3 px-0 px-lg-2 rounded" href="/#ournewspapers">OUR NEWSPAPERS</a></li>
                        <li class="nav-item mx-0 mx-lg-1"><a class="nav-link py-3 px-0 px-lg-2 rounded" href="/#ourmagazines">OUR MAGAZINES</a></li>
                        <li class="nav-item mx-0 mx-lg-1"><a class="nav-link py-3 px-0 px-lg-2 rounded" href="/#gallery">GALLERY</a></li>
                        <li class="nav-item mx-0 mx-lg-1"><a class="nav-link py-3 px-0 px-lg-2 rounded" href="/#about">About</a></li>
                        <li class="nav-item mx-0 mx-lg-1"><a class="nav-link py-3 px-0 px-lg-2 rounded" href="/#contact">Contact</a></li>
                    </ul>
                </div>
            </div>
        </nav>
		<?php
		if($IsHomePage > 0)
		{
		?>
        <!-- Masthead-->
        <header class="masthead bg-primary text-white text-center">
            <div class="container d-flex align-items-center flex-column">
                <!-- Masthead Avatar Image-->
                <?php
				/*
				?>
				<img class="masthead-avatar mb-5" src="assets/img/avataaars.svg" alt="..." />
                <?php
				*/
				if($logo != "" && file_exists($Uploadimage.$logo))
				{
					?>
					<img class="masthead-avatar mb-5" src="<?php echo $ServerURL.$Uploadimage.$logo;?>" alt="..." />
					<?php
				}
				?>
                <!-- Masthead Heading-->
                <h1 class="masthead-heading text-uppercase mb-0">Welcome to <?php echo $clientname;?></h1>
                <!-- Icon Divider-->
                <div class="divider-custom divider-light">
                    <div class="divider-custom-line"></div>
                    <div class="divider-custom-icon"><i class="fas fa-star"></i></div>
                    <div class="divider-custom-line"></div>
                </div>
                <!-- Masthead Subheading-->
                <p class="masthead-subheading font-weight-light mb-0"><?php echo $tagline;?></p>
            </div>
        </header>
		<?php
		}
		?>