/*
Email update routines.

Tasks after changes on GoDaddy:
1) Download: Full Customer and Suppressions List from GoDaddy
2) Import the customer tables:  web.root/nutshell/src/test/scripts/ImportcustomersTest.php
3) Copy updated table to production database
4) Run this script to update contacts
3) Find new contacts from customer list  (to be implemented)
5) Build update table from suppressions list (to be implemented)
*/
SET @postDate = (SELECT DATE(MAX(lastUpdate)) FROM qcall_gdcustomers);
SELECT @postDate;

-- update posted date
UPDATE qcall_contacts SET `postedDate` = @postDate, emailRefused = 0
WHERE subscribed = 1 AND email IN (SELECT email FROM `qcall_gdcustomers`);

-- update unsubscribes
UPDATE `qcall_contacts` SET subscribed = 0, emailRefused = 0 
WHERE email IN (
	SELECT email FROM qcall_gdcustomers_suppressed
	WHERE `suppressedReason` 
		IN ('email_marketing_unsubscribed','email_list_unsubscribed','suppressed_manually') );

-- Bounced
UPDATE `qcall_contacts` SET bounced = 1, emailRefused = 0 
WHERE email IN (
	SELECT email FROM qcall_gdcustomers_suppressed
	WHERE `suppressedReason` = 'email_bounced_hard');
	
-- Blocked
UPDATE `qcall_contacts` SET emailRefused = 1 
WHERE email IN (
	SELECT email FROM qcall_gdcustomers_suppressed
	WHERE `suppressedReason` 
		IN ('email_blocked','email_spammed') );

-- Subscription issue, have to fix this manually
-- In GoDaddy customers, search on the email, 
-- update as Confirmed (Double Opted-in) and not suppressed
SELECT firstName,lastName,email, SOURCE FROM `qcall_contacts`
WHERE subscribed = 1 AND `source` IN ('registrations','endorsements','endorsement')      
AND email IN (
	SELECT email FROM qcall_gdcustomers_suppressed
	WHERE `suppressedReason` 
		IN ('subscribed_to_list','subscription_requested') );
	
