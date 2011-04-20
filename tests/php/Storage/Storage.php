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
}

$test = new TestStorage();
echo $test->create(true);

?></pre>