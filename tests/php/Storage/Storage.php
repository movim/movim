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
$test->create();

$test->balance = 1000;
$test->interest = 0.02;

$test->save();

$test2 = new Account(1); // Loads account id 1

echo $test2->tostring() . "\n";

unset($test2);

// Adding owner.
$o = $test->children->add(new Owner());
$o->create();

$o->name = "Foo Bar";
$o->dob = "1990-10-01";

//var_dump($test);

$test->save();

echo $o->tostring() . "\n";

$test->delete();

//echo $test->drop(true) . "\n";

?></pre>