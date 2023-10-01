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
        <title>Terms - <?php echo $clientname;?></title>
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
                <h2 class="page-section-heading text-center text-uppercase text-secondary mb-0">Introduction and Terms of Use</h2>
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
								<p>The terms and conditions contained hereinafter (&quot;<strong>Terms of Use</strong>&quot;)
								shall apply to the use of the website <a href="http://<?php echo $subdomain;?>.orlopay.com"><?php echo $clientname;?></a> and any other
								linked pages, products, software(s), API keys, features, content or
								application services (including but without limitation to any mobile
								application services) in connection therewith, offered from time to
								time by (&quot;<strong><?php echo $clientname;?></strong>&quot; or &quot;<strong>we</strong>&quot;
								or &quot;<strong>our</strong>&quot; or &quot;<strong>us</strong>&quot;) (collectively, &quot;<strong>Website</strong>&quot;).</p>
								<p>Any person logging on to or using the Website (even when such person
								does not avail of any services provided in the Website
								(&quot;<strong>Services</strong>&quot;)) (hereinafter referred to as a &quot;<strong>User</strong>&quot;,
								&quot;<strong>you</strong>&quot; or &quot;<strong>Client</strong>&quot;) shall be presumed to have
								read these Terms of Use (which includes the Privacy Policy, separately
								put up on the Website) and unconditionally and irrevocably accepted
								the terms and conditions set out herein (these Terms of Use). These
								Terms of Use, together with the rest of the Policies (defined below),
								constitute a binding and enforceable agreement between the User and
								<?php echo $clientname;?>. These Terms of Use do not alter in any way the terms or
								conditions of any other written agreement you may have with <?php echo $clientname;?>
								for other services.</p>
								<p>THE USER UNDERSTANDS AND UNCONDITIONALLY ACKNOWLEDGES THAT EVEN THOUGH
								THE USER MAY BE ALLOWED TO EXECUTE TRANSACTIONS ON THE PLATFORM OF
								<?php echo $clientname;?>, THE FUNDS SHALL NOT BE SETTLED TO THE ACCOUNT OF SUCH USER
								PENDING COMPLETION OF KYC OBLIGATIONS ON THE PART OF THE USER IN
								ACCORDANCE WITH THE RBI'S KYC GUIDELINES (DEFINED BELOW). FURTHER,
								UPON NON-COMPLETION OF KYC OBLIGATIONS ON THE PART OF THE USER, AS
								MENTIONED ABOVE, TO THE SATISFACTION OF <?php echo $clientname;?>, WE RESERVE THE RIGHT
								TO NOT RELEASE THE SETTLEMENT AMOUNTS TO THE USER AND MAY EVENTUALLY
								REVERSE THE FUNDS TO THE ACCOUNT FROM WHERE SUCH PAYMENT ORIGINATED.</p>
								<p>Please read the terms set out hereunder carefully before agreeing to
								the same. If you do not agree to these Terms of Use (including any
								referenced policies or guidelines), please immediately terminate your
								use of the Website. You can accept the Terms of Use by:</p>
								<ul>
								<li>
								<p>Clicking to accept or agree to the Terms of Use, where this option
								is made available to you by <?php echo $clientname;?> in the User interface for any
								particular Service; or</p>
								</li>
								<li>
								<p>Accessing, testing or actually using the Services. In this case, you
								understand and agree that <?php echo $clientname;?> will treat your use of the Services
								as acceptance of the Terms of Use from that point onwards.</p>
								</li>
								</ul>
								<p>For the purpose of these Terms of Use, wherever the context so
								requires, the term &quot;User&quot; shall mean and include any natural or legal
								person who has agreed to these Terms of Use on behalf of itself or any
								other legal entity.</p>
								<p>It is clarified that the Privacy Policy (that is provided separately),
								form an integral part of these Terms of Use and should be read
								contemporaneously with the Terms of Use. Illegality or
								unenforceability of one or more provisions of these Terms of Use shall
								not affect the legality and enforceability of the other terms of the
								Terms of Use. For avoidance of doubt, if any of the provisions becomes
								void or unenforceable, the rest of the provisions of these Terms of
								Use shall be binding upon the User.</p>
								<p>The Terms of Use may be revised or altered by us at our sole
								discretion at any time without any prior intimation to the User. The
								latest Terms of Use will be posted here. Any such changes by <?php echo $clientname;?>
								will be effective immediately. By continuing to use this Website or to
								access the Services / usage of our Services after changes are made,
								you agree to be bound by the revised/ amended Terms of Use and such
								amendments shall supersede all other terms of use previously accepted
								by the User. You are solely responsible for understanding and
								complying with all applicable laws of your specific jurisdiction,
								including but not limited to the provisions of the RBI Guidelines on
								Regulation of Payment Aggregators and Payment Gateways, Payment and
								Settlement Systems Act, 2007, Prevention of Money Laundering Act,
								2002, Know Your Customer (KYC) / Anti-Money Laundering (AML) /
								Combating Financing of Terrorism (CFT) guidelines issued by the
								Department of Regulation, RBI (the &quot;<strong>KYC Guidelines</strong>&quot;) etc., that
								may be applicable to you in connection with your business and use of
								our Services.</p>
								<h2>Use of the Services by User</h2>
								<p>In order to access certain Services, you may be required to open a
								User account with <?php echo $clientname;?> by providing information about yourself
								(such as identification or contact details) as part of the
								registration process (&quot;<strong>Registration Data</strong>&quot;) for the Services, or as
								part of your continued use of the Services. You agree that any
								Registration Data you give to <?php echo $clientname;?> will always be accurate,
								correct, complete and up to date. If you provide any information that
								is untrue, inaccurate, incomplete, or not current or if we have
								reasonable grounds to suspect that such information is in violation of
								applicable law or not in accordance with the Terms of Use (whether
								wholly or in part), we reserve the right to reject your registration
								and/ or indefinitely suspend or terminate your User account and refuse
								to provide you access to the Website. Further, you agree to indemnify
								and keep us indemnified from and against all claims resulting from the
								use of any detail/ information/ Registration Data that you post and/
								or supply to us. We shall be entitled to remove any such detail/
								information/ Registration Data posted by you without any prior
								intimation to you.</p>
								<p>Notwithstanding anything else contained in any other agreement
								involving you and <?php echo $clientname;?> and/ or any other third party, in order to
								ensure that we are not violating any right that you might have in your
								Registration Data, you hereby grant to us a non-exclusive, worldwide,
								perpetual, irrevocable, royalty-free, sub-licensable right to exercise
								the copyright, publicity, and database rights (but no other rights)
								that you have in the Registration Data, in any media now or in future
								known, with respect to your Registration Data solely to enable us to
								use such Registration Data that you have supplied to us.</p>
								<p>Any amendment or rectification of your Registration Data in the User
								account can be carried out by accessing the &quot;User account&quot; section on
								the Website. You may choose to delete any or all of your User content/
								information or even the User account at any time. Processing such
								deletion may take some time, but the same shall be done by <?php echo $clientname;?>.
								We may maintain backup of all User content for such time as may be
								required under applicable laws and for operational purposes of
								<?php echo $clientname;?>. You are solely responsible for maintaining the
								confidentiality of your account and password and for any activity that
								occurs in or through your account. We will not be liable to any person
								for any loss or damage which may arise as a result of any failure on
								your part to protect your login ID or password or any other credential
								pertaining to your account. You should take all necessary steps to
								ensure that the password is kept confidential and secure. In case you
								have any reason to believe that your password has become known to
								anyone else, or if the password is being, or is likely to be, used in
								an unauthorised manner, you should inform us immediately at
								<a href="mailto:<?php echo $websiteemail;?>"><?php echo $websiteemail;?></a>. In the event of any dispute between two or
								more parties as to ownership of any particular account with <?php echo $clientname;?>,
								you agree that <?php echo $clientname;?> shall be the sole arbitrator for such dispute
								and that <?php echo $clientname;?>'s decision in this regard will be final and binding
								on you.</p>
								<p>You understand and undertake that you shall be solely responsible for
								your Registration Data and User content and undertake to, neither by
								yourself nor by permitting any third party to host, display, upload,
								modify, publish, transmit, update or share any information that:</p>
								<ul>
								<li>
								<p>Belongs to another person and to which you do not have any right to;</p>
								</li>
								<li>
								<p>Is grossly harmful, harassing, blasphemous, defamatory, obscene,
								pornographic, pedophilic, seditious, libelous, invasive of
								another's privacy, hateful, or racially, ethnically
								objectionable, disparaging, relating or encouraging money
								laundering or gambling, or otherwise unlawful in any manner
								whatsoever;</p>
								</li>
								<li>
								<p>Harms minors in any way;</p>
								</li>
								<li>
								<p>Infringes any patent, trademark, copyright or other proprietary
								rights of any person or entity anywhere in the world;</p>
								</li>
								<li>
								<p>Violates any law for the time being in force;</p>
								</li>
								<li>
								<p>Deceives or misleads the addressee about the origin of such messages
								or communicates any information which is grossly offensive or
								menacing in nature;</p>
								</li>
								<li>
								<p>Impersonates another person;</p>
								</li>
								<li>
								<p>Contains software viruses or any other computer code, files or
								programs designed to interrupt, destroy or limit the functionality
								of any computer resource;</p>
								</li>
								<li>
								<p>Threatens the unity, integrity, defense, security or sovereignty of
								India, friendly relations with foreign states, or public order or
								causes incitement to the commission of any cognizable offence or
								prevents investigation of any offence or is insulting to any other
								nation; or</p>
								</li>
								<li>
								<p>Is illegal in any other way.</p>
								</li>
								</ul>
								<p>You agree and understand that <?php echo $clientname;?> reserves the right to remove
								and/or edit such detail/ information. If you come across any
								information as mentioned above on the Website, you are requested to
								immediately contact our Grievance officer.</p>
								<p>You agree to use the Services only for purposes that are permitted by
								(a) these Terms of Use and (b) any applicable law, regulation or
								generally accepted practices or guidelines in the relevant
								jurisdictions.</p>
								<p>You agree to use the data owned by <?php echo $clientname;?> (as available on the
								Website or through any other means like API(s) etc.) only for personal
								purposes and not for any commercial use unless agreed to by <?php echo $clientname;?>
								in writing.</p>
								<p>You agree not to access (or attempt to access) any of the Services by
								any means other than through the interface that is provided by
								<?php echo $clientname;?>, unless you have been specifically allowed to do so in a
								separate agreement with <?php echo $clientname;?>. You specifically agree not to access
								(or attempt to access) any of the Services through any automated means
								(including use of scripts or web crawlers) and shall ensure that you
								comply with the instructions set out in any robots.txt file present on
								the Services.</p>
								<p>You agree that you will not engage in any activity that interferes
								with or disrupts the Services (or the servers and networks which are
								connected to the Services) on this Website.</p>
								<p>Unless you have been specifically permitted to do so in a separate
								agreement with <?php echo $clientname;?>, you agree that you will not reproduce,
								duplicate, copy, sell, trade or resell the Services for any purpose.</p>
								<p>You agree that you are solely responsible for (and that <?php echo $clientname;?> has
								no responsibility to you or to any third party for) any breach of your
								obligations under the Terms of Use and for the consequences (including
								any loss or damage which <?php echo $clientname;?> may suffer) of any such breach. You
								further agree to the use of your data by us in accordance with the
								Privacy Policy.</p>
								<p><?php echo $clientname;?> may share any Content (<em>defined hereinafter</em>) generated by
								the User or their Registration Data with governmental and regulatory
								agencies who are lawfully authorised for investigative, protective and
								cyber security activities. Such information may be transferred for the
								purposes of verification of identity, or for prevention, detection,
								investigation, prosecution pertaining to cyber security incidents and
								punishment of offences under any law for the time being in force.</p>
								<p>If you have opted for use of <?php echo $clientname;?>'s 'subscriptions' product by
								virtue of which your customers have set up a standing instruction
								(&quot;<strong>Recurring Payment Instruction</strong>&quot;) to charge his/ her chosen
								payment method (such as credit card, debit card or bank account) as
								per the billing cycle communicated by you to <?php echo $clientname;?>, then you
								consent that the relevant amount will be charged to such payment
								method as per the billing cycle communicated to <?php echo $clientname;?>. You agree
								that <?php echo $clientname;?> shall continue to charge the relevant amount to the
								relevant customer's chosen payment method as per such billing cycle
								until you or the customer terminates the Recurring Payment
								Instruction.</p>
								<h2>Eligibility</h2>
								<p>Any person who is above eighteen (18) years of age and competent to
								contract under the applicable laws is eligible to access or visit the
								Website or avail the Services displayed therein. Your use or access of
								the Website shall be treated as your representation that you are
								competent to contract and if you are registering as a business entity,
								then you represent and warrant that you have the authority to bind such
								business entity to the Terms of Use. Without generality of the
								foregoing, use of the Website is available only to persons who can form
								a legally binding contract under the Indian Contract Act, 1872 and any
								amendments thereto. [ ]{.underline}</p>
								<p>The User represents and warrants that it will be financially responsible
								for all of User's usage (including the purchase of any Service) and
								access of the Website. The User shall also be responsible for use of
								User's account by others. The Terms of Use shall be void where
								prohibited by applicable laws, and the right to access the Website shall
								automatically stand revoked in such cases.</p>
								<h2>Content in the Services</h2>
								<p>For the purposes of these Terms of Use, the term &quot;<strong>Content</strong>&quot;
								includes, without limitation, information, data, text, logos,
								photographs, videos, audio clips, animations, written posts, articles,
								comments, software, scripts, graphics, themes and interactive features
								generated, provided or otherwise made accessible on or through the
								Services.</p>
								<p>You should be aware that Content presented to you as part of the
								Services, including but not limited to advertisements in the Services
								and sponsored Content within the Services may be protected by
								intellectual property rights which are owned by the sponsors or
								advertisers who provide that Content to <?php echo $clientname;?> (or by other persons
								or companies on their behalf). You may not modify, rent, lease, loan,
								sell, distribute or create derivative works based on this Content
								(either in whole or in part) unless you have been specifically told
								that you may do so by <?php echo $clientname;?> or by the owners of that Content, in
								writing and in a separate agreement.</p>
								<p><?php echo $clientname;?> reserves the right (but shall have no obligation) to
								pre-screen, review, flag, filter, modify, refuse or remove any or all
								Content from any Service.</p>
								<p><?php echo $clientname;?> reserves the right to moderate, publish, re-publish, and use
								all User generated contributions and comments (including but not
								limited to reviews, profile pictures, comments, likes, favorites,
								votes) posted on the Website as it deems appropriate (whether in whole
								or in part) for its product(s), whether owned or affiliated. <?php echo $clientname;?>
								is not liable to pay royalty to any User for re-publishing any content
								across any of its platforms.</p>
								<p>If you submit any material on the Website, you agree thereby to grant
								<?php echo $clientname;?> the right to use, moderate, publish any such work worldwide
								for any of its product(s), whether owned or affiliated.</p>
								<p>You understand that by using the Services you may be exposed to
								Content that you may find offensive, indecent or objectionable and
								that, in this respect, your use of the Services will be at your own
								risk.</p>
								<p>You agree that you are solely responsible for (and that <?php echo $clientname;?> has
								no responsibility to you or to any third party for) any Content that
								you create, transmit or display while using the Services and for the
								consequences of your actions (including any loss or damage which
								<?php echo $clientname;?> may suffer) by doing so.</p>
								<h2>Proprietary Rights</h2>
								<p>You acknowledge and agree that <?php echo $clientname;?> (or <?php echo $clientname;?>'s licensors)
								owns all legal and proprietary right, title and interest in and to the
								Services, including any intellectual property rights which subsist in
								the Services (whether those rights happen to be registered or not, and
								wherever in the world those rights may exist). You further acknowledge
								that the Services may contain information which is designated
								confidential by <?php echo $clientname;?> and that you shall not disclose such
								information without <?php echo $clientname;?>'s prior written consent.</p>
								<p>Unless you have agreed otherwise in writing with <?php echo $clientname;?>, nothing in
								the Terms of Use gives you a right to use any of <?php echo $clientname;?>'s trade
								names, trademarks, service marks, logos, domain names, and other
								distinctive brand features.</p>
								<p>Unless you have been expressly authorized to do so in writing by
								<?php echo $clientname;?>, you agree that in using the Services, you will not use any
								trade mark, service mark, trade name, logo of any company or
								organization in a way that is likely or intended to cause confusion
								about the owner or authorized User of such marks, names or logos.</p>
								<h2>Representations and Warranties of User/ seller</h2>
								<p>The User/ seller hereby represents and warrants:</p>
								<ul>
								<li>
								<p>That User/ seller, in case of a natural person, is at least 18 years
								old with a conscious mind fit and proper to enter into this
								agreement (the &quot;Policies&quot;), is a resident of India with valid
								credentials and is an entity who is legally eligible to carry out or
								operate a business in India;</p>
								</li>
								<li>
								<p>That the all the information and documents pertaining to his/ her
								identity and address proof, as submitted for the purpose of know
								your client (KYC) verification with <?php echo $clientname;?> are true and genuine
								and are not fabricated or doctored in any way whatsoever;That the
								User shall hold and keep <?php echo $clientname;?>, its promoters, directors,
								employees, officials, agents, subsidiaries, affiliates and
								representatives harmless from any liabilities arising in connection
								with any incidental or intentional discrepancy that is found to be
								there in the documents submitted by such User for the purpose of KYC
								formalities;</p>
								</li>
								<li>
								<p>That any incidental or upfront liability arising in connection with
								User's/ seller's KYC formalities for the purpose of availing the
								services of <?php echo $clientname;?> shall be the absolute responsibility and
								repercussion of the User and neither <?php echo $clientname;?> nor any of its
								affiliates or office bearers shall be responsible in any way for any
								reason including for ascertaining the veracity of the KYC documents
								submitted by such User with <?php echo $clientname;?>;</p>
								</li>
								<li>
								<p>That User/ seller shall be solely responsible for understanding and
								complying with any and all applicable laws relevant to the User and
								their business, and any liability, whether pecuniary or otherwise,
								arising from any non-compliance of such applicable laws shall be at
								the sole cost and risk of such User;</p>
								</li>
								<li>
								<p>That the User shall ensure that its IT systems and infrastructure
								are compliant with the mandates of PCI-DSS and Payment
								Application-Data Security Standard, as applicable to it;</p>
								</li>
								<li>
								<p>That the User/ seller shall operate and conduct his/ her business as
								per declaration provided by such User to <?php echo $clientname;?> at the time of
								onboarding of such User by <?php echo $clientname;?> and shall promptly report any
								change/ deviation/ addition/ deletion in the scope of business
								activities of such User to <?php echo $clientname;?>;</p>
								</li>
								<li>
								<p>That all data, information, inventions, intellectual properties
								(including patents, trademarks, copyrights, design and trade
								secrets), know-how, new uses and processes, and any other
								intellectual property right, asset or form, including, but not
								limited to, analytical methods, procedures and techniques, research,
								procedure manuals, financial information, computer technical
								expertise, software for the purpose of availing of services of
								<?php echo $clientname;?> and any updates or amendments thereto is and shall be the
								sole intellectual property of <?php echo $clientname;?> and should in no way be
								construed to grant any rights and/ or title to the User in such
								intellectual property of <?php echo $clientname;?>;</p>
								</li>
								<li>
								<p>That User/ seller shall not store any customer card and such related
								date in any form or manner whatsoever on their websites/ servers;</p>
								</li>
								<li>
								<p>That it shall be the exclusive responsibility of the User to ensure
								that the correct line of business of the User is declared under
								merchant category code (MCC) pertaining to the User and that
								<?php echo $clientname;?> reserves the right to withhold settlements and/ or suspend
								transactions of the User in case of any mismatch or violation in its
								MCC declaration; and</p>
								</li>
								<li>
								<p>That the User/ seller shall accord adequate and timely co-operation
								in allowing <?php echo $clientname;?> or</p>
								</li>
								</ul>
								<p>it's appointed agencies or regulators to conduct audits, including for
								compliance with the Policies and provisions of applicable laws.</p>
								<h2>Indemnity</h2>
								<p>The User shall indemnify and hold <?php echo $clientname;?>, its subsidiaries,
								affiliates, promoters, directors, employees, contractors,
								licensors and agents and any other related or third parties
								involved with <?php echo $clientname;?> in any manner whatsoever, harmless from
								and against all losses arising from claims, demands, actions or
								other proceedings as a result of:</p>
								<ul>
								<li>
								<p>Fraud, negligence and willful misconduct by the User in the use of
								the Services;</p>
								</li>
								<li>
								<p>Violation of applicable laws in the use of the Services and/ or in
								the conduct of the business of the User, including but not limited
								to the legal provisions mentioned under paragraphs 6 and 7
								hereinabove;</p>
								</li>
								<li>
								<p>Breach of the User's confidentiality obligations under these Terms
								of Use;</p>
								</li>
								<li>
								<p>Disputes raised by a User's customer in relation to a transaction
								where such dispute is not attributable to the Services;</p>
								</li>
								<li>
								<p>Penalties, fines, charges, or any other actions as a result of
								breach or violation of any the User's representations and
								warranties; and</p>
								</li>
								<li>
								<p>Fines, penalties and charges imposed by the Acquiring Bank, Card
								Payment Networks or any Governmental Authority on account of
								transactions on the User's website or platform that are in
								violation of applicable law.</p>
								</li>
								</ul>
								<h2>Limitation of Liability</h2>
								<p>Subject to the overall provisions stated above, you expressly
								understand and agree that <?php echo $clientname;?>, its subsidiaries, affiliates,
								promoters, directors, employees, agents and licensors shall not be
								liable to you for:</p>
								<ul>
								<li>
								<p>Any direct, indirect, incidental, special, consequential, punitive
								or exemplary damages which may be incurred by you, however caused
								and under any theory of liability. This shall include, but not be
								limited to, any loss of profit (whether incurred directly or
								indirectly), any loss of goodwill or business reputation, any loss
								of data suffered, cost of procurement of substitute goods or
								Services, or other intangible loss;</p>
								</li>
								<li>
								<p>Any loss or damage which may be incurred by you, including but not
								limited to loss or damage as a result of any reliance placed by
								you on the completeness, accuracy or existence of any advertising,
								or as a result of any relationship or transaction between you and
								any advertiser or sponsor whose advertisement appears on the
								Services;</p>
								</li>
								<li>
								<p>The deletion of, corruption of, or failure to store, any content and
								other communications data maintained or transmitted by or through
								your use of the Services;</p>
								</li>
								<li>
								<p>Your failure to provide <?php echo $clientname;?> with accurate Registration Data; or</p>
								</li>
								<li>
								<p>Your failure to keep your password or account details secure and
								confidential.</p>
								</li>
								</ul>
								<p>The limitations on <?php echo $clientname;?>'s liability to you shall apply whether or
								not <?php echo $clientname;?> has/ had been advised of or should have been aware of the
								possibility of any losses to you.</p>
								<h2>Force Majeure</h2>
								<p><?php echo $clientname;?> shall not be in breach of its obligation hereunder if
								it is delayed in the performance of, or is unable to perform
								(whether partially or fully) its obligations (provide the
								Services) as a result of the occurrence of a Force Majeure Event
								(defined below).</p>
								<p>Force Majeure Event means any event, whatever be the origin, not
								within the reasonable control of <?php echo $clientname;?>, which <?php echo $clientname;?> is
								unable to prevent, avoid or remove or circumvent by the use of
								reasonable diligence. Force Majeure event shall include, but
								shall not be limited to, acts of god, acts, orders, directions
								of governmental/ regulatory/ judicial/ quasi-judicial/ law
								enforcement authorities/ agencies which hinders <?php echo $clientname;?> from
								performing its obligations under any agreement, including these
								Terms of Use, with you, war, hostilities, invasion, armed
								conflict, act of foreign enemy, embargoes, riot, insurrection,
								labour stoppages, outages and downtimes systems failures
								experienced by a facility provider, revolution or usurped power,
								acts of terrorism, sabotage, nuclear explosion, earthquake,
								pandemic, epidemic, hacking or man in the middle attack or
								similar attacks/ intrusions, fires, typhoons, storms and other
								natural catastrophes.</p>
								<p>Any payment obligations of <?php echo $clientname;?>, in case of a Force Majeure
								event, shall be limited by and be subject to the fulfillment of
								the payment obligations of the partners banks/ financial
								institutions, counterparties and any other parties involved in
								or intrinsically linked to the provision of the Services of
								<?php echo $clientname;?>.</p>
								<h2>Confidentiality</h2>
								<p>The User may receive or have access to certain confidential and
								proprietary information, including without limitation,
								information belonging and/or relating to <?php echo $clientname;?> and its
								affiliates, marketing prospects, contractors, officers,
								directors or shareholders, personal data of customers of the
								User, financial and operational information, billing records,
								business model and reports, computer systems and modules, secure
								websites, reporting systems, marketing strategies, operational
								plans, proprietary systems and procedures, trade secrets and
								other similar proprietary information, including technical
								&quot;know-how&quot;, methods of operation, business methodologies,
								software, software and technology architecture, networks, any
								other information not generally available to the public, and any
								items in any form in writing or oral, clearly identified as
								confidential (&quot;<strong>Confidential Information</strong>&quot;).</p>
								<p>The User shall keep Confidential Information in confidence. The
								User shall use commercial, reasonable and necessary safety
								measures and steps to maintain the confidentiality and secrecy
								of the Confidential Information from public disclosure, and the
								User shall at all times maintain appropriate measures to protect
								the security and integrity of the Confidential Information. The
								User shall not, without <?php echo $clientname;?>'s prior written consent,
								divulge any of the Confidential Information to any third party
								other than the User's officers, employees, agents or
								representatives who have a need to know for the purposes of
								these Terms of Use. The User shall take all reasonable steps to
								ensure that all of its directors, managers, officers, employees,
								agents, independent contractors or other representatives comply
								with this paragraph 12 whenever they are in possession of
								Confidential Information as part of this Agreement. The User
								shall use the Confidential Information solely in furtherance of
								and in connection with the Services contemplated under these
								Terms of Use. The User further agrees that the Confidential
								Information will not be used by him/ her and his/ her
								representatives, in any way detrimental to the interests of
								<?php echo $clientname;?>.</p>
								<p>The User hereby unconditionally and irrevocably agrees and
								undertakes to take all necessary measures to ensure that the
								User's website or any other computer system that is being used
								to effect Transactions by the usage of Services, do not store/
								save any customer card or any other such related data. You
								further undertake that you shall be responsible for ensuring
								complete and absolutely privacy, anonymity and security of all
								customer data flowing through your systems during the usage of
								the Services by you.</p>
								<p>Exceptions: The aforesaid confidentiality obligations shall
								impose no obligation on the User with respect to any portion of
								Confidential Information which:</p>
								<ul>
								<li>
								<p>Was at the time received or which thereafter becomes, through no act
								or failure on the part of the User, generally known or available
								to the public;</p>
								</li>
								<li>
								<p>Was at the time of receipt, known to the User as evidenced by
								written documentation then rightfully in the possession of the
								User or <?php echo $clientname;?>;</p>
								</li>
								<li>
								<p>Was already acquired by the User from a third party who does not
								thereby breach an obligation of confidentiality to <?php echo $clientname;?> and
								who discloses it to the User in good faith;</p>
								</li>
								<li>
								<p>Was developed by the User without use of the <?php echo $clientname;?>'s Confidential
								Information in such development; or</p>
								</li>
								<li>
								<p>Has been disclosed pursuant to the requirements of applicable law,
								any governmental/ regulatory authority, judicial/ quasi-judicial
								authority provided however, that <?php echo $clientname;?> shall have been given a
								reasonable opportunity to resist disclosure and/or to obtain a
								suitable protective order.</p>
								</li>
								</ul>
								<p>The User shall notify <?php echo $clientname;?> immediately upon discovery of any
								unauthorized use or disclosure of Confidential Information or
								any other breach of this paragraph 12. The User will cooperate
								with <?php echo $clientname;?> in every reasonable way to help <?php echo $clientname;?> regain
								possession of such Confidential Information and prevent its
								further unauthorized use.</p>
								<p>Remedies: Parties acknowledge that irreparable damage may occur
								on breach of the terms and provisions set out in this
								paragraph 12. Accordingly, if the User breaches or threatens to
								breach any of the provisions set out in this paragraph 12, then
								<?php echo $clientname;?> shall be entitled, without prejudice, to seek all the
								rights and remedies available to it under applicable law,
								including a temporary restraining order and an injunction
								restraining any breach of the provisions set out in this
								paragraph 12. Such remedies shall not be deemed to be exclusive
								but shall be in addition to all other remedies available under
								applicable law or in equity.</p>
								<h2>Prohibited Services</h2>
								<p>You agree that you will not accept payments in connection with
								businesses, business activities or business practices, including but
								limited to the following:</p>
								<ul>
								<li>
								<p>Adult goods and services which include pornography and other
								sexually suggestive materials (including literature, imagery and
								other media), escort or prostitution services, website access
								and/or website memberships of pornography or illegal sites;</p>
								</li>
								<li>
								<p>Alcohol which includes alcohol or alcoholic beverages such as beer,
								liquor, wine, or champagne etc.;</p>
								</li>
								<li>
								<p>Body parts which include organs or other body parts;</p>
								</li>
								<li>
								<p>Bulk marketing tools which include email lists, software, or other
								products enabling unsolicited email messages (spam);</p>
								</li>
								<li>
								<p>Cable descramblers and black boxes which include devices intended to
								obtain cable and satellite signals for free;</p>
								</li>
								<li>
								<p>Child pornography which includes pornographic materials involving
								minors;</p>
								</li>
								<li>
								<p>Copyright unlocking devices which include mod chips or other devices
								designed to circumvent copyright protection;</p>
								</li>
								<li>
								<p>Copyrighted media which includes unauthorized copies of books,
								music, movies, and other licensed or protected materials;</p>
								</li>
								<li>
								<p>Copyrighted software which includes unauthorized copies of software,
								video games and other licensed or protected materials, including
								OEM or bundled software;</p>
								<ul>
								<li>
								<p>Counterfeit and unauthorized goods which include replicas or
								imitations of designer goods, items without a celebrity
								endorsement that would normally require such an association, fake
								autographs, counterfeit stamps, and other potentially unauthorized
								goods;</p>
								</li>
								<li>
								<p>Drugs and drug paraphernalia which include illegal drugs and drug
								accessories, including herbal drugs like marijuana, salvia and
								magic mushrooms etc.;</p>
								</li>
								<li>
								<p>Drug test circumvention aids which include drug cleansing shakes,
								urine test additives, and related items;</p>
								</li>
								<li>
								<p>Endangered species which include plants, animals or other organisms
								(including product derivatives) in danger of extinction;</p>
								</li>
								<li>
								<p>Gaming/ gambling which include lottery tickets, sports bets,
								memberships/ enrolment in online gambling sites, and related
								content;</p>
								</li>
								<li>
								<p>Government IDs or documents which include fake IDs, passports,
								diplomas, and noble titles;</p>
								</li>
								<li>
								<p>Hacking and cracking materials which include manuals, how-to guides,
								information, or equipment enabling illegal access to software,
								servers, website, or other protected property;</p>
								</li>
								<li>
								<p>Illegal goods which include materials, products, or information
								promoting illegal goods or enabling illegal acts;</p>
								</li>
								<li>
								<p>Miracle cures which include unsubstantiated cures, remedies or other
								items marketed as quick health fixes;</p>
								</li>
								<li>
								<p>Offensive goods which include literature, products or other
								materials that inter alia :</p>
								<ul>
								<li>
								<p>Defame or slander any person or groups of people based on race,
								ethnicity, national origin, religion, sex, or other factors;</p>
								</li>
								<li>
								<p>Encourage or incite violent acts; or</p>
								</li>
								<li>
								<p>Promote intolerance or hatred.</p>
								</li>
								<li>
								<p>Offensive goods which include crime scene photos or items, such
								as personal belongings, associated with criminals;</p>
								</li>
								<li>
								<p>Pyrotechnic devices, combustibles, corrosives and hazardous
								materials which include explosives and related goods, toxic,
								flammable, and radioactive materials and substances;</p>
								</li>
								<li>
								<p>Regulated goods which include air bags, batteries containing
								mercury, freon or similar substances/ refrigerants, chemical/
								industrial solvents, government uniforms, car titles, license
								plates, police badges and law enforcement equipment,
								lock-picking devices, pesticides, postage meters, recalled
								items, slot machines, surveillance equipment, goods regulated
								by government or other agency specifications;</p>
								</li>
								<li>
								<p>Securities which include government and/ or public sector unit
								bonds, stocks, debentures or related financial products;</p>
								</li>
								<li>
								<p>Tobacco and cigarettes which include cigarettes, cigars, chewing
								tobacco, and related products;</p>
								</li>
								<li>
								<p>Traffic devices which include radar detectors/ jammers, license
								plate covers, traffic signal changers, and related products;</p>
								</li>
								<li>
								<p>Weapons which include firearms, ammunition, knives, brass
								knuckles, gun parts, gun powder or explosive mixtures and
								other armaments;</p>
								</li>
								<li>
								<p>Wholesale currency which includes discounted currencies or
								currency exchanges;</p>
								</li>
								<li>
								<p>Live animals or hides/ skins/ teeth, nails and other parts etc.
								of animals;</p>
								</li>
								<li>
								<p>Multi-level marketing collection fees;</p>
								</li>
								<li>
								<p>Matrix sites or sites using a matrix scheme approach;</p>
								</li>
								<li>
								<p>Work-at-home approach and/ or work-at-home information;</p>
								</li>
								<li>
								<p>Drop-shipped merchandise;</p>
								</li>
								<li>
								<p>Any product or service which is not in compliance with all
								applicable laws and regulations whether federal, state, local
								or international, including the laws of India;</p>
								</li>
								<li>
								<p>The User providing services that have the potential of casting
								the payment gateway facilitators in a poor light and/ or that
								may be prone to buy and deny attitude of the cardholders when
								billed (e.g. adult material/ mature content/ escort services/
								friend finders) and thus leading to chargeback and fraud
								losses;</p>
								</li>
								<li>
								<p>Businesses or website that operate within the scope of laws
								which are not absolutely clear or are ambiguous in nature
								(e.g. web-based telephony, website supplying medicines or
								controlled substances, website that promise online
								match-making);</p>
								</li>
								<li>
								<p>Businesses out rightly banned by law (e.g. betting &amp; gambling/
								publications or content that is likely to be interpreted by
								the authorities as leading to moral turpitude or decadence or
								incite caste/ communal tensions, lotteries/ sweepstakes &amp;
								games of chance;</p>
								</li>
								<li>
								<p>Businesses dealing in intangible goods/ services (e.g. software
								download/ health/ beauty Products), and involved in pyramid
								marketing schemes or get-rich-quick schemes;</p>
								</li>
								<li>
								<p>Any other product or service, which in the sole opinion of
								either the Acquiring Bank, is detrimental to the image and
								interests of either of them/ both of them, as communicated by
								either of them/ both of them to the User from time to time.
								This shall be without prejudice to any other terms &amp;
								conditions mentioned in these Terms of Use;</p>
								</li>
								<li>
								<p>Mailing lists;</p>
								</li>
								<li>
								<p>Virtual currency, cryptocurrency, prohibited investments for
								commercial gain or credits that can be monetized, re-sold or
								converted to physical or digital goods or services or
								otherwise exit the virtual world;</p>
								</li>
								<li>
								<p>Money laundering services;</p>
								</li>
								<li>
								<p>Database providers (for tele-callers);</p>
								</li>
								<li>
								<p>Bidding/ auction houses;</p>
								</li>
								<li>
								<p>Activities prohibited by the Telecom Regulatory Authority of
								India; and</p>
								</li>
								<li>
								<p>Any other activities prohibited by applicable law.</p>
								</li>
								</ul>
								</li>
								</ul>
								</li>
								</ul>
								<p>The above list is subject to additions/ amendments (basis changes/
								amendments to applicable laws) by <?php echo $clientname;?> without prior intimation to
								you.</p>
								<h2>Transaction Disputes</h2>
								<p>Transactions may be disputed anytime within up to 120 (one hundred
								twenty) days, from the date of transaction by a buyer, as per the Card
								Payment Network Rules. Disputes resolved in favour of a buyer may
								result in reversal of payment to such buyer (&quot;<strong>Chargeback</strong>&quot;). In the
								event of rejection/ suspension of payments to the seller, chargebacks,
								refunds and/or any other dispute relating to the transactions
								contemplated under these Terms of Use (&quot;<strong>Disputed Transaction</strong>&quot;),
								on any grounds whatsoever, we will forthwith notify the seller of the
								same.</p>
								<blockquote></blockquote>
								<p>On such notification the seller will conduct an internal review of
								such matter and will, within 5 (five) working days from receipt of
								notification, respond to us in writing either:</p>
								<ul>
								<li>
								<p>Requesting us to refund Refund Request the payment
								received by the seller in respect of such Disputed Transaction
								Refund Monies; or</p>
								</li>
								<li>
								<p>Providing us with a statement explaining how the Disputed
								Transaction is not warranted, together with all documentary
								evidence in support of contesting such Disputed Transaction.</p>
								</li>
								</ul>
								<p>All refunds shall be made to the original method of payment. In the
								event that the seller provides a Refund Request to us or fails to
								contest such Disputed Transaction within the aforesaid 5 (five)
								working days or contests Disputed Transaction without providing
								supporting documentation to us, payment service providers, Card
								Payment Network and/ or issuing institution's satisfaction, we will
								be entitled to recover the Refund Monies from credits subsequently
								made to the escrow account with respect to payments made by the
								seller's buyers.</p>
								<p>In the event that we are unable to recover the Refund Monies as
								aforesaid, due to the amounts credited to the escrow account being
								lower than the Refund Monies, <?php echo $clientname;?> shall be entitled to recover
								such Refund Monies (or any part thereof) from the User by (i) raising
								a debit note in respect of such monies; and/ or (ii) setting-off the
								remaining Refund Monies against the future payables to the seller and
								refund the same to the respective buyers. The seller will be liable to
								make payment of the Refund Monies or part thereof which has not been
								recovered by us forthwith. It is hereby agreed and acknowledged by the
								parties that the Fees charged by us in respect of the Disputed
								Transaction will not be refunded or repaid by us to the seller, buyer
								or any other person. Further, the Chargeback will be provided within 1
								(one) week of the transaction and maximum amount of the Chargeback
								payable by <?php echo $clientname;?> to the buyer will be the value of the transaction
								only.</p>
								<h2>Technical Issues &amp; Delivery Policy</h2>
								<p>In case of any technical issues, please raise a support ticket from
								your service dashboard or by emailing us at &lt;<b><?php echo $websiteemail;?></b>&gt; to
								let us know of the same. We endeavour to deliver Service to you within
								15 (fifteen) working days of bank approval, failing which you can
								terminate a transaction related to Service at any time and get a full
								refund.</p>
								<h2>Governing Law, Settlement of Disputes and Jurisdiction</h2>
								<p>These Terms of Use and any dispute or claim arising under it will be
								governed by and construed in accordance with the laws of India. The
								Users agree that any legal action or proceedings arising out of these
								Terms of Use or in connection with these Terms of Use may be brought
								<strong>exclusively</strong> in the competent courts/ tribunals having jurisdiction
								in Raipur, India and the Users irrevocably submit themselves to the
								jurisdiction of such courts/ tribunals.</p>
								<h2>Privacy</h2>
								<p><strong>Your privacy is extremely important to us. Upon acceptance of these
								Terms of Use you confirm that you have read, understood and
								unequivocally accepted our Policies, including the provisions of our
								Privacy Policy.</strong></p>
								<p><strong>I hereby confirm that I have read these Terms of Use and accept
								them.</strong></p>
								 

                    </div>
                </div>
            </div>
        </section>
		<?php include_once "footer.php";?>
        <script src="<?php echo $ServerURL;?>js/bootstrap.bundle.min.js"></script>
        <script src="<?php echo $ServerURL;?>js/scripts.js"></script>
    </body>
</html>