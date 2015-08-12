/**
 * Implements hashes.
 */

function H(object)
{
    return new Hash(object);
}

function isHash(object)
{
    var type = "";
    if(object != null) {
        type = object.type;
    }

    return type == "Hash";
}

/**
 * Allows iterating over a hash.
 */
function HashIterator(data, keys)
{
    this.hash = data;
    this.keys = keys;
    this.cursor = 0;
    this.iterated = false;
    this.type = "HashIterator";

    /**
     * Moves one item further.
     */
    this.next = function()
    {
        if(this.iterated && this.cursor < this.keys.length - 1) {
            this.cursor++;
            return true;
        }
        else if(!this.iterated) {
            this.iterated = true;
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Moves back one item.
     */
    this.prev = function()
    {
        if(this.iterated && this.cursor > 0) {
            this.cursor--;
            return true;
        }
        else if(!this.iterated) {
            this.iterated = true;
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * returns the key at the current position.
     */
    this.key = function()
    {
        return this.keys[this.cursor];
    }

    /**
     * Sets the cursor on the first key.
     */
    this.start = function()
    {
        this.cursor = 0;
        this.iterated = false;
        return this.cursor;
    }

    /**
     * Sets the cursor on the last key.
     */
    this.end = function()
    {
        this.cursor = this.keys.length - 1;
        this.iterated = false;
        return this.cursor;
    }

    /**
     * returns the value at the current position.
     */
    this.val = function()
    {
        return this.hash[this.keys[this.cursor]];
    }
}

function Hash(object)
{
    this.container = null;
    this.type      = "Hash";

    /**
     * Adds an element to the Hash.
     */
    this.set = function(key, val)
    {
        var ret = this.container[key] = val;

        return ret;
    }

    /**
     * gets the value for a key.
     */
    this.get = function(key)
    {
        return this.container[key];
    }

    this.del = function(key)
    {
        var value = this.container[key];
        delete this.container[key];
        return value;
    }

    this.haskey = function(key)
    {
        return key in this.container;
    }

    /**
     * Iterates through the hash.
     */
    this.iterate = function()
    {
        return new HashIterator(this.container, this.keys());
    }

    /* Conversions */
    /**
     * Return an array containing the keys.
     */
    this.keys = function()
    {
        var keys = new Array();
        for(var key in this.container) {
            keys.push(key);
        }

        return keys;
    }

    /**
     * Converts the hash into an array of values.
     */
    this.values = function()
    {
        var values = new Array();
        for(var key in this.container) {
            values.push(this.container[key]);
        }

        return values;
    }

    /**
     * Converts the hash to a string.
     */
    this.to_string = function(want_keys)
    {
        if(arguments.length == 0) {
            want_keys = true;
        }

        var buffer = ""; var i = 0;
        for(var key in this.container) {
            if(i > 0) buffer += ", ";
            if(want_keys) {
                buffer += key + ": ";
            }
            buffer += this.container[key];
            i++;
        }

        return buffer;
    }


    // Contructor
    if(arguments.length > 0 && object != null) {
        this.container = object;
    } else {
        this.container = {};
    }
}
