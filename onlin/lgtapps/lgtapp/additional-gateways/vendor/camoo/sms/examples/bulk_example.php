<?php

declare(strict_types=1);

use Camoo\Sms\Message;

require_once dirname(__DIR__) . '/vendor/autoload.php';
/**
 * Send BULK sms
 */
$oMessage = Message::create('YOUR_API_KEY', 'YOUR_API_SECRET');

// OR (since version 3.3.1)
$oMessage = Message::create()->setCredential(new \Camoo\Sms\Entity\Credential('YOUR_API_KEY', 'YOUR_API_SECRET'));

$oMessage->from = 'YourCompany';
$oMessage->to = ['+237612345678', '+237612345679', '+237612345610', '+33689764530', '+4917612345671'];
$oMessage->message = 'Hello Kmer World! Déjà vu!';
var_dump($oMessage->sendBulk());

# Send Bulk sms and set callback to save result into the Database
# TO do only if you store the messages in your own database
$hCallback = [
    'path_to_php' => '/usr/bin/php', // match your running php version. should be >= 7.1.0
    'driver' => [\Camoo\Sms\Database\MySQL::class, 'getInstance'],
    'bulk_chunk' => 1,
    'db_config' => [
        [
            'db_name' => 'test',
            'db_user' => 'test',
            'db_password' => 'secret',
            'db_host' => 'localhost',
            'table_sms' => 'my_table',
        ],
    ],
    'variables' => [
        //Your DB keys => Map camoo keys
        'message' => 'message',
        'recipient' => 'to',
        'message_id' => 'message_id',
        'sender' => 'from',
    ],
];
$oMessage = Message::create('YOUR_API_KEY', 'YOUR_API_SECRET');
$oMessage->from = 'YourCompany';
$oMessage->to = ['+237612345678', '+237612345679', '+237612345610', '+33689764530', '+4917612345671', '...'];
$oMessage->message = 'Hello Kmer World! Déjà vu!';
var_dump($oMessage->sendBulk($hCallback));

# Send personalized Bulk SMS
#
$oMessage = Message::create('YOUR_API_KEY', 'YOUR_API_SECRET');
$oMessage->from = 'YourCompany';
$oMessage->to = [['name' => 'John Doe', 'mobile' => '+237612345678'], ['name' => 'Jeanne Doe', 'mobile' => '+237612345679'], ['...']];
$oMessage->message = 'Hello %NAME% Kmer World! Déjà vu!';
var_dump($oMessage->sendBulk($hCallback));
// Done!
