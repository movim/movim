<pre><?php

require("../../../loader.php");

class TestStorage extends StorageBase
{
    // Storable fields.
    protected $toto;
    protected $prout;
    protected $foreign;

    protected function type_init()
    {
        $this->toto = StorageType::int();
        $this->prout = StorageType::varchar(10);
        $this->id = 2;
    }

/*    public function tostring()
    {
        echo "TestStorage: ".$this->toto->getval()." - ".$this->prout->getval()." \n";
        }*/
}

$test = new TestStorage();
echo $test->create(true) . "\n";

$test->toto = 10;
$test->prout = 'tagada';

echo $test->save(true);

$test2 = new TestStorage();

$test2->load(array('id' => 1));

echo "\n" . $test2->tostring();

unset($test2);

echo $test->delete(true) . "\n";

echo $test->drop(true) . "\n";

?></pre>