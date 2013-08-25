<?php 

/*
    Just a simple chain of hashes of some bytes from /dev/urandom.

    If you can't read/write files to where you're trying to (including
    giving PHP permissions to read from /dev/urandom) your seed data
    might just be an empty string.

    My target users are myself and people who understand the implications 
    of that, and they should be able to figure that out on their own.  I 
    don't personally want to turn this into some project.
*/

$seedFilePath = './.randomSeed';
$sequenceFilePath = './.sequenceFile';
$identityLifetime = 1001; //Number of sha1 iterations you start with,  (the chain is a finite size... thus, the lifetime)

/*
    Bootstrap the data file if you don't have one yet.
*/
if (! file_exists($seedFilePath)) {

    $fileHandler = fopen('/dev/urandom', "r");
    $fileBuffer="";
    while (strlen($fileBuffer) < 1024) { //Feel free to increase the entropy, but also learn about /dev/urandom first.
        $fileBuffer .= (string) fgets($fileHandler, 32); 
    }
    fclose($fileHandler);

    file_put_contents($seedFilePath, $fileBuffer);
}

/*
    Ensure there is a sequence file.
*/
if (! file_exists($sequenceFilePath)) {
    file_put_contents($sequenceFilePath, $identityLifetime);
}

/*
    Generate a text file with the highest unused sha1 chain value as the name.
*/
$sequence = (int) file_get_contents($sequenceFilePath) - 1;
$seedData = (string) file_get_contents($seedFilePath);
if (empty($seedData)) {
    die('Read the funny manual.' . PHP_EOL);
}

$chain = array($seedData);

for ($i =0; $i < $sequence; $i+=1) {
    $chain[] = sha1(end($chain));
}


file_put_contents($sequenceFilePath, $sequence);
file_put_contents("./{$sequence}-" . end($chain) . ".md", $sequence . " " .end($chain));
echo "Created {$sequence} " . end($chain) . PHP_EOL;
