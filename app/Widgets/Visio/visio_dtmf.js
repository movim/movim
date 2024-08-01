var VisioDTMF = {
    context: null,
    osc1: null,
    osc2: null,
    started: false,

    setup: function () {
        this.context = new window.AudioContext;
        this.osc1 = this.context.createOscillator();
        this.osc2 = this.context.createOscillator();

        this.gainNode = this.context.createGain();
        this.gainNode.gain.value = 0.25;

        this.filter = this.context.createBiquadFilter();
        this.filter.type = "lowpass";
        this.filter.frequency = 8000;

        this.osc1.connect(this.gainNode);
        this.osc2.connect(this.gainNode);

        this.gainNode.connect(this.filter);
        this.filter.connect(this.context.destination);
    },

    pressButton(number) {
        var dtmfFrequencies = {
            "1": { f1: 697, f2: 1209 },
            "2": { f1: 697, f2: 1336 },
            "3": { f1: 697, f2: 1477 },
            "4": { f1: 770, f2: 1209 },
            "5": { f1: 770, f2: 1336 },
            "6": { f1: 770, f2: 1477 },
            "7": { f1: 852, f2: 1209 },
            "8": { f1: 852, f2: 1336 },
            "9": { f1: 852, f2: 1477 },
            "*": { f1: 941, f2: 1209 },
            "0": { f1: 941, f2: 1336 },
            "#": { f1: 941, f2: 1477 }
        }

        let frequencies = dtmfFrequencies[number];

        this.tone(frequencies.f1, frequencies.f2);
    },

    tone: function (freq1, freq2) {
        if (this.started) this.stop();

        this.setup();

        this.osc1.frequency.value = freq1;
        this.osc2.frequency.value = freq2;

        this.osc1.start(0);
        this.osc2.start(0);

        this.started = true;
    },

    stop: function () {
        this.osc1.stop(0);
        this.osc2.stop(0);

        this.started = false;
    }
}

VisioDTMF.setup();
