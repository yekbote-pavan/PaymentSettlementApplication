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
    use ZuluCrypto\StellarSdk\Model\StellarAmount;
    if ( isset($_POST['btn-register']) )
    {
        $server = Server::testNet();
        $username = $_POST['username'];
        $pass = $_POST['password'];
        $password = hash('sha256', $pass);
        $accounttype = $_POST['accounttype'];
        $keypair = Keypair::newFromRandom();
        $publickey = $keypair->getPublicKey().PHP_EOL;
        $publickey = trim(preg_replace('/\s+/', ' ', $publickey));
        $seed = $keypair->getSecret().PHP_EOL;
        $seed = trim(preg_replace('/\s+/', ' ', $seed));
        $query = "INSERT INTO Users( Username , Password , AccountType , Publickey , Seed) VALUES('$username','$password','$accounttype','$publickey','$seed')";
        $result = mysqli_query($conn,$query);
        if($accounttype == 'Customer')
        {
            $limit = 10000;
        }
        else if($accounttype == 'Merchant')
        {
            $limit = 10000000;
        }
        else if($accounttype == 'Admin')
        {
            $limit = StellarAmount::newMaximum();
        }
        $getIssuer = "SELECT Publickey FROM Users where Username = 'Issuer'";
        $getIssuerResult = mysqli_query($conn,$getIssuer);
        if($result)
        {
            $response = file_get_contents('https://horizon-testnet.stellar.org/friendbot?addr=' . $publickey);
            if($response == false)
            {
                echo 'Error : Failed to initialise account';
            }
           else
            {
                if($getIssuerResult)
                {
                    $row = mysqli_fetch_array($getIssuerResult);
                    $issuerPublickey = $row['Publickey'];
                    $receivingKeypair = Keypair::newFromSeed($seed);
                    $asset = new Asset(Asset::TYPE_ALPHANUM_12);
                    $asset->setAssetCode("DOECredits");
                    $issuer = new AccountId($issuerPublickey);
                    $asset->setIssuer($issuer);
                    $server->buildTransaction($receivingKeypair)
                            ->addChangeTrustOp($asset,$limit)
                            ->submit($receivingKeypair);
                    header("Location: index.php");
                }
                else
                {
                   echo 'Error : Failed to retrieve Issuer keys';
                }
            } 
        }
        else
        {
            echo "Error : Query failed to execute";
        }
    }
?>
<!DOCTYPE html>
<html>
<head>
<title>Register</title>
<link rel="stylesheet" href="CSS/Form.css" type="text/css"/>
<!--<script src = "https://cdnjs.cloudflare.com/ajax/libs/stellar-sdk/0.8.2/stellar-sdk.min.js"></script>-->
</head>
<body background="CSS/thumb-1920-840086.jpg">
<div class="login-page">
  <div class="form">
    <form class="login-form" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" autocomplete="off">
    <a class="message">REGISTER</a>
    <br><br>
                <input type="text" class="form-control" name="username" placeholder="Enter Username"/>
                <br>
                <input type="password" class="form-control" name="password" placeholder="Enter Password"/>
                <br>
                <div id="mainselection">
                <select name="accounttype">
                    <option value="Customer">Customer</option>
                    <option value="Merchant">Merchant</option>
                    <option value="Admin">Admin</option>
                </select>
                </div>
                <!--<input type ="hidden" name="seed" id = "seed"/>
                <input type ="hidden" name="publickey" id = "publickey"/>-->
                <br>
                <button type="submit" class="btn btn-block btn-primary" name="btn-register">Register</button>
                <br>
                <br>
            	<a href="index.php">Already a User? Log In</a>
        </form>
    </div>
</div>
       <!-- <script>
            var pair = StellarSdk.Keypair.random();
            var secret = pair.secret();
            var publickey = pair.publicKey();
            document.getElementById("seed").value = secret;
            document.getElementById("publickey").value = publickey;
        </script> -->
</body>
</html>
<?php ob_end_flush(); ?>