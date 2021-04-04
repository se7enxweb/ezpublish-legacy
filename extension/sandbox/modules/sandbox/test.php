<?php

echo '<pre>';
$mail = new eZMail2();
$mail->setBody( 'Sending emails is fun again!' );
$mail->setReceiver( 'pkamps@mugo.ca', 'Philipp Kamps Receiver' );

$receiverElements =
	[
		[
			'name' => 'Philipp Kamps3',
			'email' => 'pkamps@mugo.co'
		],
		[
			'name' => 'Philipp Kamps4',
			'email' => '%pkamps@mugo.com'
		],
	];

$mail->setReceiverElements( $receiverElements );

print_r( $mail );
echo '</pre>';


//$mail->setSubject( 'Time for Symfony Mailer!' );
//$mail->setSender( 'pkamps@mugo.ca' );
//$mail->setReceiver( 'pkamps@mugo.ca' );

//var_dump( eZMailTransport::send( $mail ) );

/*
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

$transport = Transport::fromDsn('smtp://localhost');
$mailer = new Mailer($transport);

$email = (new Email())
	->from('pkamps@mugo.ca')
	->to('pkamps@mugo.ca')
	//->cc('cc@example.com')
	//->bcc('bcc@example.com')
	//->replyTo('fabien@example.com')
	//->priority(Email::PRIORITY_HIGH)
	->subject('Time for Symfony Mailer!')
	->text('Sending emails is fun again!')
	->html('<p>See Twig integration for better HTML integration!</p>');

var_dump( $mailer->send($email) );
*/