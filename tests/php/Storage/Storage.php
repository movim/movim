<pre><?php

require("../../../loader.php");

class TestStorage extends StorageBase
{
    // Storable fields.
    public $toto;
    public $prout;

    protected function type_init()
    {
        $this->toto = StorageType::int();
        $this->prout = StorageType::varchar(10);
    }

/*    public function tostring()
    {
        echo "TestStorage: ".$this->toto->getval()." - ".$this->prout->getval()." \n";
        }*/
}

$test = new TestStorage();
echo $test->create(true) . "\n";

$test->load(array('id' => 1));

echo $test->tostring();

echo $test->save(true);

?></pre>