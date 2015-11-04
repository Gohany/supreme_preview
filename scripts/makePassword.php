<?php

$sharedSecret = hash('sha256', 'Shared Secret Here 1249*)*@8712389&*(@&8497298ALKJlajskld');

if (PHP_SAPI !== 'cli')
{
        die('This script must be ran from command-line.' . PHP_EOL);
}

echo PHP_EOL . '##' . PHP_EOL;
echo '# SUPREME Password Creation Tool' . PHP_EOL;
echo '##' . PHP_EOL . PHP_EOL;

do
{
        if (isset($domain))
        {
                print 'Invalid Domain.  Try again.' . PHP_EOL;
        }

        print 'Domain (No http:// or www.): ';
        fscanf(STDIN, "%s\n", $domain);
        $ip = gethostbyname($domain);
}
while (filter_var($ip, FILTER_VALIDATE_IP) === false);

do
{
        if (isset($type))
        {
                print 'Invalid Type.  Try again.' . PHP_EOL;
        }

        print 'Type (database, ftp, or admin): ';
        fscanf(STDIN, "%s\n", $type);
}
while (in_array($type, array('database', 'ftp', 'admin')) === false);

$hash = hash('sha1', $domain . $ip . $type . $sharedSecret);

$search = [
        'a' => '@',
        'i' => '!',
        't' => '&',
        '0' => '#',
        'o' => '*',
        'g' => '^',
        's' => '$',
        'h' => '|',
        'z' => '%',
];

foreach ($search as $char => $replace)
{
        $hash = str_replace($char, $replace, $hash);
}

print "Password: " . PHP_EOL . substr($hash,0,12) . '!' . PHP_EOL;