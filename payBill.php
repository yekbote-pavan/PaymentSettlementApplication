<?php
    ob_start();
    session_start();
    include_once 'doeconnect.php';
    require __DIR__ . '/vendor/autoload.php';
    use ZuluCrypto\StellarSdk\Keypair;
    use ZuluCrypto\StellarSdk\Server;
    use ZuluCrypto\StellarSdk\XdrModel\Asset;
    use ZuluCrypto\StellarSdk\XdrModel\AccountId;
    use ZuluCrypto\StellarSdk\XdrModel\Operation\SetOptionsOp;
    
    if(isset($_POST['btn-pay']))
    {
        //retrieve source,destination,amount
        $destname = $_SESSION['username'];
        $sourcename = $_POST['destination'];
        $amount = $_POST['amount'];
        
        //interaction with db
        $query1 = "SELECT Publickey,Seed FROM Users where Username like '$sourcename'";
        $query2 = "SELECT Publickey,Seed FROM Users where Username like '$destname'";
        $result1 = mysqli_query($conn,$query1);
        $result2 = mysqli_query($conn,$query2);
        $row1 = mysqli_fetch_array($result1);
        $row2 = mysqli_fetch_array($result2);
        $sourcePublickey = $row1['Publickey'];
        $sourceSeed = $row1['Seed'];
        $destPublickey = $row2['Publickey'];
        $destSeed = $row2['Seed'];
        
        //speak to stellar and get keypair
        $sourceKeypair = Keypair::newFromSeed($sourceSeed);
        $receivingKeypair = Keypair::newFromSeed($destSeed);
        
        //Asset initialisation
        $asset = new Asset(Asset::TYPE_ALPHANUM_12);
        $asset->setAssetCode("DOECredits");
        $issuer = new AccountId('GBLJGGNZB7GTTC2D2AZ3HPAAIO4CQMES442C4OEI6PDZFRG4MBQL6ZOS');
        $asset->setIssuer($issuer);
        
        //server start
        $server = Server::testNet();
        
        //build transaction
        $txEnvelope = $server->buildTransaction($sourceKeypair)
                            ->addCustomAssetPaymentOp($asset, $amount, $receivingKeypair->getPublicKey())
                            ->getTransactionEnvelope();
        
        //source signs transaction
        $txEnvelope->sign($sourceKeypair);
        
        //encode transaction
        $b64Tx = base64_encode($txEnvelope->toXdr());
        
        //submit transaction
        $transaction = $server->submitB64Transaction($b64Tx);
        $transactionID = $transaction->getTransactionHash();

        //insert into database  transactions
        $query3 = "Select ID from Users where Username like '$sourcename'";
        $query4 = "Select ID from Users where Username like '$destname'";
        $result3 = mysqli_query($conn,$query3);
        $result4 = mysqli_query($conn,$query4);
        $row3 = mysqli_fetch_array($result3);
        $row4 = mysqli_fetch_array($result4);
        $sourceID = $row3['ID'];
        $destID = $row4['ID'];
        $query5 = "INSERT INTO Transactions(TransactionID,SourceID,DestinationID,Amount) 
                   VALUES('$transactionID','$sourceID','$destID','$amount')";
        $result5 = mysqli_query($conn,$query5);
        if(!result5)
        {
            echo "Error : Failed to execute query";
        }

        //get DOE publickey,seed
        $query6 = "SELECT Publickey,Seed from Users where Username like 'DOE'";
        $result6 = mysqli_query($conn,$query6);
        $row6 = mysqli_fetch_array($result6);
        $doePublickey = $row6['Publickey'];
        $doeSeed = $row6['Seed'];
        
        //get DOE keypair
        $doeKeypair = Keypair::newFromSeed($doeSeed);
        
        //calculate compensation
        $compensation = 0.01 * $amount;

        //new transactions where source = merchant, destination = DOE
        $txEnvelope = $server->buildTransaction($receivingKeypair)
                            ->addCustomAssetPaymentOp($asset, $compensation, $doeKeypair->getPublicKey())
                            ->getTransactionEnvelope();
        
        //source signs transaction
        $txEnvelope->sign($receivingKeypair);
        
        //encode transaction
        $b64Tx = base64_encode($txEnvelope->toXdr());
        
        //submit transaction
        $transaction = $server->submitB64Transaction($b64Tx);
        $transactionID = $transaction->getTransactionHash();
        
        //interact with db, insert into transactions table
        $query3 = "Select ID from Users where Username like '$destname'";
        $query4 = "Select ID from Users where Username like 'DOE'";
        $result3 = mysqli_query($conn,$query3);
        $result4 = mysqli_query($conn,$query4);
        $row3 = mysqli_fetch_array($result3);
        $row4 = mysqli_fetch_array($result4);
        $sourceID = $row3['ID'];
        $destID = $row4['ID'];
        $query5 = "INSERT INTO Transactions(TransactionID,SourceID,DestinationID,Amount) 
                   VALUES('$transactionID','$sourceID','$destID','$compensation')";
        $result5 = mysqli_query($conn,$query5);
    }
    
    if(isset($_POST['btn-back']))
    {
        header("Location: merchantHome.php");
    }

?>

<!DOCTYPE HTML>
<html>
    <head>
        <title>Home</title>
        <link rel="stylesheet" href="CSS/Form.css" type="text/css"/>
    </head>
    <body background="CSS/thumb-1920-840086.jpg">
    <div class="login-page">
    <div class="form">
    <form method="post" class="login-form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" autocomplete="off">
    <a class="message">BILL</a>
        <br>
        <br> 
        <input type="text" class="form-control" name="amount" placeholder="Enter Amount">
        <br>
        <input type="text" class="form-control" name="destination" placeholder="Enter Customer Name">
        <br><br>
        <button type="submit" class="btn btn-block btn-primary" name="btn-pay">Pay</button>
        <br><br>
        <button type="submit" class="btn btn-block btn-primary" name="btn-back">Back</button>
    </form>
    </div>
    </div>
    </body>
</html>