<?php
var_dump(memory_get_usage());
        memprof_enable();

memprof_dump_callgrind(fopen("/tmp/callgrind_test.out", "w"));
