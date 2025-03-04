<?php

include __DIR__ . '/Mailchimp/Mailchimp.php';
include __DIR__ . '/Mailjet/Mailjet.php';
include __DIR__ . '/SendGrid/SendGrid.php';
include __DIR__ . '/SendinBlue/SendinBlue.php';
include __DIR__ . '/Hubspot/Hubspot.php';
include __DIR__ . '/ActiveCampaign/ActiveCampaign.php';
include __DIR__ . '/ConvertKit/ConvertKit.php';
include __DIR__ . '/GetResponse/GetResponse.php';
include __DIR__ . '/ConstantContact/ConstantContact.php';
include __DIR__ . '/Webhook/Webhook.php';
include __DIR__ . '/AWeber/AWeber.php';
include __DIR__ . '/Zoho/Zoho.php';
include __DIR__ . '/Zoho/ZohoCRM.php';
include __DIR__ . '/MailerLite/MailerLite.php';
include __DIR__ . '/Moosend/Moosend.php';
include __DIR__ . '/CampaignMonitor/CampaignMonitor.php';
include __DIR__ . '/MailPoet/MailPoet.php';
include __DIR__ . '/TNP/TNP.php';
include __DIR__ . '/Klaviyo/Klaviyo.php';
include __DIR__ . '/Pabbly/Pabbly.php';
include __DIR__ . '/Ontraport/Ontraport.php';
include __DIR__ . '/SendPulse/SendPulse.php';
include __DIR__ . '/LearnDash/LearnDash.php';
include __DIR__ . '/EDD/EDD.php';
include __DIR__ . '/Mailster/Mailster.php';
include __DIR__ . '/FluentCRM/FluentCRM.php';
include __DIR__ . '/Sendy/Sendy.php';
include __DIR__ . '/Omnisend/Omnisend.php';
include __DIR__ . '/Sender/Sender.php';

if(file_exists(__DIR__ . '/Validators/emailvalidator.php')){
   include __DIR__ . '/Validators/emailvalidator.php';
}
if(file_exists(__DIR__ . '/_Advanced/init.php')){
   include __DIR__ . '/_Advanced/init.php';
}
if(file_exists(__DIR__ . '/_Social/init.php')){
   include __DIR__ . '/_Social/init.php';
}