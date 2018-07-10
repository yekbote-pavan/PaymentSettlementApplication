<?php
    require __DIR__ . '/vendor/autoload.php';
    use ZuluCrypto\StellarSdk\Keypair;
    use ZuluCrypto\StellarSdk\Server;
    use ZuluCrypto\StellarSdk\XdrModel\Asset;
    use ZuluCrypto\StellarSdk\XdrModel\AccountId;
    use ZuluCrypto\StellarSdk\XdrModel\Operation\SetOptionsOp;
$server = Server::testNet();
$receivingKeypair = Keypair::newFromSeed('SCLRLMYRXA42KQSH34A7GMQHCKVSRGXCNZRL2RNJ277M62Q3IDLZCLHG');
$asset = new Asset(Asset::TYPE_ALPHANUM_12);
$asset->setAssetCode("DOECredits");
$issuer = new AccountId('GBLJGGNZB7GTTC2D2AZ3HPAAIO4CQMES442C4OEI6PDZFRG4MBQL6ZOS');
$asset->setIssuer($issuer);
$limit = 10000000;
$server->buildTransaction($receivingKeypair)
    ->addChangeTrustOp($asset,$limit) 
    ->submit($receivingKeypair);

