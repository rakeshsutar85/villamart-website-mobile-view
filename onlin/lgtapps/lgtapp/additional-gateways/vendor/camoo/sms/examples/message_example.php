<?php

declare(strict_types=1);

use Camoo\Sms\Message;

require_once dirname(__DIR__) . '/vendor/autoload.php';
/**
 * Send a sms
 */

/** @var Message|\Camoo\Sms\Objects\Message $oMessage */
$oMessage = Message::create('YOUR_API_KEY', 'YOUR_API_SECRET');
$oMessage->from = 'YourCompany';
$oMessage->to = '+237612345678';
$oMessage->message = 'Hello Kmer World! Déjà vu!';
var_dump($oMessage->send());

return;
# Send encrypted message
$oMessage = Message::create('YOUR_API_KEY', 'YOUR_API_SECRET');
$oMessage->from = 'YourCompany';
$oMessage->to = '+237612345678';
$oMessage->message = 'Hello Kmer World! Déjà vu!';
$oMessage->encrypt = true;
var_dump($oMessage->send());

##### Example for sending classic SMS 10FCFA/SMS ########
# When sending classic SMS you can't customize the sender. This type is only available for cameroonian phone numbers
$oMessage = Message::create('YOUR_API_KEY', 'YOUR_API_SECRET');
$oMessage->from = 'WhatEver'; // will be overridden
$oMessage->to = '+237612345678';
$oMessage->route = 'classic';  // This parameter tells our system to use the classic route to send your message.
$oMessage->message = 'Hello Kmer World! Déjà vu!';
var_dump($oMessage->send());

################ Classic SMS END #####################
# Change response format to XML
$oMessage = Message::create('YOUR_API_KEY', 'YOUR_API_SECRET');
$oMessage->setResponseFormat('xml');
$oMessage->from = 'YourCompany';
$oMessage->to = '+237612345678';
$oMessage->message = 'Hello Kmer World! Déjà vu!';
var_dump($oMessage->send());

##### Set notification URL for a single message ########
$oMessage = Message::create('YOUR_API_KEY', 'YOUR_API_SECRET');
$oMessage->from = 'YourCompany';
$oMessage->to = '+237612345678';
$oMessage->message = 'Hello Kmer World! Déjà vu!';
$oMessage->notify_url = 'http://your-own.url/script';
var_dump($oMessage->send());

// Done!
