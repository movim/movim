/**
 * Implements hashes.
 */

function Hash()
{
    this.container    = new Array();
    this.numerical_id = 0;
    this.length       = 0;
    this.cursor       = 0;
    this.iterated     = false;

    /* Common operations */
    this.pop          = Hash_pop;
    this.set          = Hash_set;
    this.push         = Hash_push;
    this.haskey       = Hash_haskey;
    this.hasval       = Hash_hasval;
    this.getval       = Hash_getval;

    /* Iteration */
    this.iterate      = Hash_iterate;
    this.reset        = Hash_reset;
    this.key          = Hash_key;
    this.val          = Hash_val;

    /* Conversions */
    this.keys         = Hash_keys;
    this.values       = Hash_values;
    this.to_string    = Hash_to_string;
}

/**
 * Removes the last element of the Hash and returns its value.
 */
function Hash_pop()
{
    var lastval = this.container.pop();
    // Removing the key
    var lastkey = this.container.pop();

    this.length--;
    if(lastkey == this.numerical_id)
        this.numerical_id--;
    
    return lastval;
}

/**
 * Adds an element to the Hash.
 */
function Hash_set(key, val)
{
    // Attempting to set the value on an existing key
    for(var eln = 0; eln < this.container.length; eln += 2)
    {
        if(this.container[eln] == key) {
            this.container[eln + 1] = val;
            return val;
        }
    }

    // Didn't work. Pushing instead.
    this.container.push(key);
    this.container.push(val);

    this.length++;
}

/**
 * Adds an unassociative element to the Hash.
 */
function Hash_push(val)
{
    this.container.push(this.numerical_id);
    this.container.push(val);
    this.numerical_id++;
    this.length++;
}

/**
 * Gets the value at key.
 */
function Hash_getval(key)
{
    for(var eln = 0; eln < this.container.length; eln += 2)
    {
        if(this.container[eln] == key) {
            return this.container[eln + 1];
        }
    }

    return false;
}

/**
 * Checks if Hash contains key.
 */
function Hash_haskey(key)
{
    for(var eln = 0; eln < this.container.length; eln += 2)
    {
        if(this.container[eln] == key) {
            return true;
        }
    }
    return false;
}

/**
 * Checks if Hash contains val.
 */
function Hash_hasval(val)
{
    for(var eln = 1; eln < this.container.length; eln += 2)
    {
        if(this.container[eln] == val) {
            return true;
        }
    }
    return false;
}

/**
 * Converts the hash into an array of values.
 */
function Hash_values()
{
    var vals = new Array();
    for(var eln = 1; eln < this.container.length; eln += 2)
    {
        vals.push(this.container[eln]);
    }

    return vals;
}

/**
 * Return an array containing the keys.
 */
function Hash_keys()
{
    var keys = new Array();
    for(var eln = 0; eln < this.container.length; eln += 2)
    {
        keys.push(this.container[eln]);
    }

    return keys;
}

/**
 * Converts the hash to a string.
 */
function Hash_to_string(want_keys)
{
    if(arguments.length == 0) {
        want_keys = true;
    }

    var buffer = "";
    var sep = "";
    if(want_keys) {
        for(var eln = 0; eln < this.container.length; eln += 2) {
            if(eln > 0) sep = ",";
            buffer+= sep + this.container[eln] + ":" + this.container[eln + 1];
        }
    } else {
        for(var eln = 1; eln < this.container.length; eln += 2) {
            if(eln > 1) sep = ",";
            buffer+= sep + this.container[eln];
        }
    }

    return buffer;
}

/**
 * Iterates through the hash.
 */
function Hash_iterate()
{
    if(this.cursor == 0 && !this.iterated) {
        this.iterated = true;
        return true;
    }

    if(this.cursor + 1 == this.length) {
        return false;
    } else {
        this.cursor++;
        return true;
    }
}

/**
 * Resets the cursor's position.
 */
function Hash_reset()
{
    this.cursor = 0;
    this.iterated = false;
}

/**
 * Gets the key at the current cursor position.
 */
function Hash_key()
{
    return this.container[this.cursor * 2];
}

/**
 * Gets the value at the current cursor position.
 */
function Hash_val()
{
    return this.container[this.cursor * 2 + 1];
}