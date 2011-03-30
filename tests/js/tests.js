/* Testing framework's tools. */
var failures = 0;
var success = 0;
var tests = H();

/************* Common stuff ***************/

function print(text)
{
    document.getElementById("page").innerHTML += text;
}

function printtest(result)
{
    if(!result) {
        failures++;
        print('x');
    } else {
        success++;
        print('.');
    }
}

function print_summary()
{
    var total = failures + success;

    print('<h2>Test summary:</h2>');
    print('<p>Total tests: ' + total + '<br />');
    print('Success: ' + success + '<br />');
    print('Failures: ' + failures + '<br />');
    print('Success rate: ' + parseInt(success / Math.max(1, total) * 100) + '%</p>');
}

// Runs the test suites
function runtests()
{
    if(isHash(tests)) {
        var iter = tests.iterate();
        while(iter.next()) {
            if(isHash(iter.val())) { // This is a test suite.
                print("<h2>Running test " + iter.key() + "</h2>");
                var suite_iter = iter.val().iterate();
                while(suite_iter.next()) {
                    print('<b>' + suite_iter.key() + '</b> ');
                    var test = suite_iter.val();
                    try {
                        test();
                    }
                    catch(err)
                    {
                        printtest(false);
                        print('<br />' + err + '<br />');
                    }
                    print('<br />');
                }
            }
        }
    }

    print_summary();
}

/*********** Testing funtions *************/
// Asserts that thing returns true.
function assert(thing)
{
    printtest(thing);
}

// Ensures that thing returns false.
function nassert(thing)
{
    printtest(!thing);
}

// Ensures that thing is equal to exp_thing
function equals(thing, exp_thing)
{
    printtest(thing == exp_thing);
}

// Ensures that thing is not equal to exp_thing
function different(thing, exp_thing)
{
    printtest(thing != exp_thing);
}
