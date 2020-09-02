var VisioTones = {
    context: null,
    o: null,
    g: null,
    init: function() {
        this.context = new AudioContext();
        this.o = this.context.createOscillator();
        this.g = this.context.createGain();
        this.o.connect(this.g);
        this.g.connect(this.context.destination);
    },


    ringingTone: function() {
        this.o.frequency.value = 1440.0;
        this.o.start(0);
    },
}

VisioTones.init();