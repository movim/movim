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

    /**
     * Removes the last element of the Hash and returns its value.
     */
    this.pop = function()
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
    this.set = function(key, val)
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
    this.push = function(val)
    {
        this.container.push(this.numerical_id);
        this.container.push(val);
        this.numerical_id++;
        this.length++;
    }

    /**
     * Gets the value at key.
     */
    this.haskey = function(key)
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
    this.hasval = function(key)
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
    this.getval = function(val)
    {
        for(var eln = 1; eln < this.container.length; eln += 2)
        {
            if(this.container[eln] == val) {
                return true;
            }
        }
        return false;
    }

    /* Iteration */
    /**
     * Iterates through the hash.
     */
    this.iterate = function()
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
    this.reset = function()
    {
        this.cursor = 0;
        this.iterated = false;
    }

    /**
     * Gets the key at the current cursor position.
     */
    this.key = function()
    {
        return this.container[this.cursor * 2];
    }

    /**
     * Gets the value at the current cursor position.
     */
    this.val = function()
    {
        return this.container[this.cursor * 2 + 1];
    }

    /* Conversions */
    /**
     * Return an array containing the keys.
     */
    this.keys = function()
    {
        var keys = new Array();
        for(var eln = 0; eln < this.container.length; eln += 2)
        {
            keys.push(this.container[eln]);
        }

        return keys;
    }

    /**
     * Converts the hash into an array of values.
     */
    this.values = function()
    {
        var vals = new Array();
        for(var eln = 1; eln < this.container.length; eln += 2)
        {
            vals.push(this.container[eln]);
        }

        return vals;
    }

    /**
     * Converts the hash to a string.
     */
    this.to_string = function(want_keys)
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
}
