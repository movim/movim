<pre><?php

require("../../../loader.php");

class Account extends StorageBase
{
    // Storable fields.
    protected $balance;
    protected $interest;
    protected $owners;

    protected function type_init()
    {
        $this->balance = StorageType::float();
        $this->interest = StorageType::float();
    }

/*    public function tostring()
    {
        echo "TestStorage: ".$this->toto->getval()." - ".$this->prout->getval()." \n";
        }*/
}

class Owner extends StorageBase
{
    protected $name;
    protected $dob;
    protected $account;
    
    protected function type_init()
    {
        $this->name = StorageType::varchar(256);
        $this->dob = StorageType::date();
        $this->foreignkey('account', 'Account');
    }
}

$test = new Account();
echo $test->create(true) . "\n";

$test->balance = 1000;
$test->interest = 0.02;

echo $test->save(true);

$test2 = new Account(1); // Loads account id 1

echo "\n" . $test2->tostring();

unset($test2);

echo $test->delete(true) . "\n";

echo $test->drop(true) . "\n";

?></pre>