README 

/*
	ACKNOWLEDGEMENTS:
	
	Thanks to Sybre Waaijer for the incredible code base to start with!
	
	INTRODUCTION:
	
	The plugin is a simple one to replace manual purchase for prosite plugin.
	
	FEATURES/PREREQ:
	1. Batch json file for 3 forms
	2. You need this domain checker plugin: https://wordpress.org/plugins/ajax-domain-checker/
	3. You need my trial plugin or sybre orginal signup plugin.
	
	Its split into 3 parts:
	
	1. SHORTCODE SECTION
	2. PARSE INFO SECTION
	3. BONUS ADDED CURRENCY
	
	INTRUCTIONS:
	
	FOR prositemanualpayment.php
	
	1. You need to define:
		a. Purchase page eg. /purchase	
		b. Payment confirmation page eg. /confirmapayment
		c. Domain Requestpage eg. /domain
	2. Import the batch json into gravity forms. You should have 3 Form ID's
	3. Make sure you write down your form ID here:
		a. purchase [PID=?]
		b. confirm payment [CID=?]
		c. request [RID=?]
	4. Short code time!
		a. On /purchase/ include [temp_purchase]
		b. On /pro-sites/ include [redirect_purchase]
		c. On /confirmpayment/ include [redirect_confirm]
		d. On /domain include [redirect_auto]
	5. Let's fill in some fields!
		Line 35: replace '/purchase/' with your nominated page
		Line 43: replace '14' with your PID
		Line 102: replace '15' with your CID
		Line 161: replace '16' with your RID
		Line 222: replace '14' with your PID
		Line 223: replace '15' with your CID
		Line 224: replace '16' with your RID
	
	FOR Gravity Forms

	Note: please do not touch the first 5 fields on any other form as they parse the information to your notifications.
*/
