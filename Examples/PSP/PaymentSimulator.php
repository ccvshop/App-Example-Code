<?php
	require_once('../../Config.php');
	require_once('../../Data/Data_Core.php');
	require_once('../../Data/Data_Credential.php');
	require_once('../../Data/Data_WebHook.php');
	require_once('../../Data/Data_Transaction.php');
	require_once('../../Entities/Credential.php');
	require_once('../../Entities/WebHook.php');
	require_once('../../Entities/Transaction.php');
	require_once('../../Entities/Transaction/Address.php');
	require_once('../../Exceptions/InvalidApiResponse.php');
	require_once('../../Exceptions/InvalidCredentialException.php');
	require_once('../../Exceptions/InvalidHashException.php');
	require_once('../../Exceptions/InvalidTransactionId.php');
	require_once('../../Exceptions/InvalidJsonException.php');
	require_once('../../Json/JsonSerializer.php');
	require_once('../../Http/WebRequest.php');
	require_once('../../Http/Hash.php');
	require_once('../../Log/Log.php');

	#Transaction id ophalen uit url en deze ophalen uit de storage.
	$oTransaction = \AppConnector\Data\Data_Transaction::GetOneByTransactionId($_GET['transaction_id']);

	if(!empty($_POST)) {
		if(isset($_POST['status'])) {
			$oTransaction->SetStatus($_POST['status']);
		}
		\AppConnector\Data\Data_Transaction::Update($oTransaction);

		header('location: ' . $oTransaction->GetReturnUrl());
		die();
	}
?>
<html>
<head>
	<title></title>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css"
		  integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css"
		  integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
</head>
<body>

<div class="container">
	<h1>App PSP Simulator</h1>
	<p>

	</p>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">
				Test Transaction
			</h3>
		</div>
		<div class="panel-body">
			<p>
				Order <?= $oTransaction->GetOrderNumber(); ?> of  <?= $oTransaction->GetCurrency(); ?> <?= $oTransaction->GetAmount(); ?>
				to be paid with <?= $oTransaction->GetMethod(); ?>.
			</p>
			<form action="PaymentSimulator.php?transaction_id=<?= $oTransaction->GetTransactionId(); ?>" method="post">
				<button name="status" value="SUCCESS" class="btn btn-success">Success</button>
				<button name="status" value="CANCELLED" class="btn btn-danger">Cancel</button>
				<button name="status" value="FAILED" class="btn btn-danger">Failed</button>
				<button name="status" value="EXPIRED" class="btn btn-danger">Expired</button>
				<button name="status" value="OPEN" class="btn btn-warning">Open</button>
			</form>
		</div>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">
				Debug info
			</h3>
		</div>
		<div class="panel-body">
			<p>method: <?= $oTransaction->GetMethod(); ?> </p>
			<p>issuer: <?= $oTransaction->GetIssuer(); ?> </p>
			<p>return_url: <?= $oTransaction->GetReturnUrl(); ?> </p>
			<p>order_id: <?= $oTransaction->GetOrderId(); ?></p>
			<p>order_number: <?= $oTransaction->GetOrderNumber(); ?></p>
			<p>amount: <?= $oTransaction->GetAmount(); ?></p>
			<p>currency: <?= $oTransaction->GetCurrency(); ?></p>
			<p>transaction_id: <?= $oTransaction->GetTransactionId(); ?></p>
			<p>create_date: <?= $oTransaction->GetCreated(); ?></p>
			<p>language: <?= $oTransaction->GetLanguage(); ?></p>
			<p>status: <?= $oTransaction->GetStatus(); ?></p>
		</div>
	</div>
</div>
</body>
</html>