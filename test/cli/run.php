<?php

if (PHP_SAPI !== 'cli')
{
        die('This script must be ran from command-line.' . PHP_EOL);
}

if (!isset($_SERVER['_HTDOCS_']))
{
        $_SERVER['_HTDOCS_'] = realpath(dirname(__FILE__) . '/../..');
}

require_once $_SERVER['_HTDOCS_'] . '/test/cli/assets.php';
require_once $_SERVER['_HTDOCS_'] . '/base_include.php';

$methods = array();

readline_completion_function('readline_callback');

function readline_callback($input, $index)
{
        $cliTest = new cliTest;
        foreach ($cliTest->tests as $class => $info)
        {
                foreach ($info as $name => $filename)
                {
                        $return[] = $class . "." . $name;
                }
        }
        return $return;
}

class cliTest
{

        public $tests;
        public $class;
        public $method;

        public function __construct()
        {

                foreach (glob($_SERVER['_HTDOCS_'] . "/test/cli/tests/*.php") as $filename)
                {

                        require_once $filename;

                        foreach ($this->getClasses($filename) as $className)
                        {
                                $reflection = new ReflectionClass($className);
                                foreach ($reflection->getMethods() as $k => $object)
                                {
                                        $this->tests[$object->class][$object->name] = $filename;
                                }
                        }
                }
        }
        
        public function printIntro()
        {
                print "\x1B[01;91m";
                print file_get_contents($_SERVER['_HTDOCS_'] . "/test/cli/art.txt");
                print "\x1B[0m";
                print PHP_EOL;
        }
        
        public function printSuccess()
        {
                print file_get_contents($_SERVER['_HTDOCS_'] . "/test/cli/thumbsup.txt");
                print PHP_EOL;
        }
        
        public function printFailure()
        {
                print file_get_contents($_SERVER['_HTDOCS_'] . "/test/cli/fail.txt");
                print PHP_EOL;
        }
        
        public function printExit()
        {
                print "\x1B[01;93m";
                print file_get_contents($_SERVER['_HTDOCS_'] . "/test/cli/exit.txt");
                print "\x1B[0m";
                print PHP_EOL;
        }

        public function expandInput($input)
        {
                $explode = explode('.', $input);
                if (count($explode) == 2)
                {
                        return $explode;
                }
                throw new Exception('Input in unexpected format');
        }

        public function userInput($input)
        {
                if (count($input) > 1)
                {
                        list($this->class, $this->method) = $this->expandInput($input[1]);
                        return true;
                }
                return false;
        }

        public function testExists()
        {
                if (isset($this->tests[$this->class][$this->method]))
                {
                        return true;
                }
                return false;
        }

        public function getClasses($filepath)
        {
                return $this->getClassDeclarations(file_get_contents($filepath));
        }

        public function getClassDeclarations($php_code)
        {
                $classes = array();
                $tokens = token_get_all($php_code);
                $count = count($tokens);
                for ($i = 2; $i < $count; $i++)
                {
                        if ($tokens[$i - 2][0] == T_CLASS && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING)
                        {
                                $class_name = $tokens[$i][1];
                                $classes[] = $class_name;
                        }
                }
                return $classes;
        }

        public function getMethodArgs($php_code, $class, $method)
        {
                $arguments = array();
                $tokens = token_get_all($php_code);
                $count = count($tokens);

                $inCorrectMethod = false;
                $inArray = false;
                $inCorrectClass = false;

                for ($i = 2; $i < $count; $i++)
                {

                        if ($tokens[$i - 2][0] == T_CLASS && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING)
                        {
                                $inCorrectClass = $tokens[$i][1] == $class ? true : false;
                        }

                        if ($inCorrectClass && $tokens[$i - 2][0] == T_FUNCTION && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING)
                        {
                                $inCorrectMethod = $tokens[$i][1] == $method ? true : false;
                        }

                        if ($inCorrectClass && $inCorrectMethod)
                        {

                                if ($tokens[$i - 2][0] == T_VARIABLE && $tokens[$i - 1][0] == T_OBJECT_OPERATOR && $tokens[$i][0] == T_STRING)
                                {
                                        $inArray = $tokens[$i][1] == 'orderedArgs' ? true : false;
                                }
                                elseif ($inArray && $tokens[$i][0] == T_CONSTANT_ENCAPSED_STRING)
                                {
                                        $arguments[] = trim($tokens[$i][1], "'");
                                }
                        }
                }

                return $arguments;
        }

        public function getUserTest()
        {
                do
                {
                        $test = readline("Test: ");
                        
                        if (in_array($test, array('exit', 'bye', 'quit', 'fuckoff')))
                        {
                                $this->printExit();
                                exit;
                        }
                        
                        if (!empty($test))
                        {
                                readline_add_history($test);
                        }
                        list($this->class, $this->method) = $this->expandInput($test);
                }
                while ($this->testExists() === false);
                return true;
        }

        public function getTestArguments()
        {
                foreach ($this->getMethodArgs(file_get_contents($this->tests[$this->class][$this->method]), $this->class, $this->method) as $argument)
                {
                        $input = readline($argument.': ');
                        if (!empty($input))
                        {
                                readline_add_history($input);
                        }
                        $arguments[] = $input;
                }
                return $arguments;
        }

}

try
{
        $cliTest = new cliTest;
        $cliTest->printIntro();
        do
        {
                
                if ($cliTest->userInput($argv) && $cliTest->testExists())
                {
                        $testArguments = empty($argv) ? null : array_slice($argv, 2);
                        $repeat = false;
                }
                elseif ($cliTest->getUserTest())
                {
                        $testArguments = $cliTest->getTestArguments();
                        $repeat = true;
                }

                $supremeTest = new $cliTest->class;
                $output = $supremeTest->{$cliTest->method}($testArguments);

                if ($output === true)
                {
                        print "\x1B[01;92m";
                        $cliTest->printSuccess();
                        print "Test of " . $cliTest->class . "::" . $cliTest->method . " has succeeded" . "\x1B[0m" . PHP_EOL;
                }
                else
                {
                        print "\x1B[01;91m";
                        $cliTest->printFailure();
                        print "Test of " . $cliTest->class . "::" . $cliTest->method . " has failed" . "\x1B[0m" . PHP_EOL;
                        print "DETAILS: " . PHP_EOL;
                        var_dump($output);
                        print PHP_EOL;
                }
        }
        while ($repeat);
}
catch (Exception $e)
{
        print $e->getMessage() . PHP_EOL;
}