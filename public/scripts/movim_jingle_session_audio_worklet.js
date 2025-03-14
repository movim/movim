class MovimJingleSessionAudioWorklet extends AudioWorkletProcessor {
    static BUFFER_SIZE = 128 * 128;

    constructor() {
        super();
        this.isMuteStep = 0;
        this.remoteMaxLevel = 0;
        this.buffer = new Float32Array(MovimJingleSessionAudioWorklet.BUFFER_SIZE);
        this.offset = 0;
    }

    process(inputList, _outputList, _parameters) {
        const input = inputList[0][0];

        if (input != undefined) {
            for (let i = 0; i < input.length; i++) {
                this.buffer[i + this.offset] = input[i];
            }

            this.offset += input.length;

            if (this.offset >= this.buffer.length - 1) {
                this.flush();
            }

            return true;
        }
    }

    flush() {
        this.offset = 0;

        var instant = 0.0;
        var sum = 0.0;

        for (var i = 0; i < this.buffer.length; ++i) {
            sum += this.buffer[i] * this.buffer[i];
        }

        instant = Math.sqrt(sum / this.buffer.length);
        this.remoteMaxLevel = Math.max(this.remoteMaxLevel, instant);

        var base = (instant / this.remoteMaxLevel);
        var level = (base > 0.05) ? base ** .3 : 0;

        // Fallback in case we don't have the proper signalisation
        if (level == 0) {
            this.isMuteStep++;
        } else {
            this.isMuteStep = 0;
        }

        this.port.postMessage({
            "isMuteStep": this.isMuteStep,
            "level": level.toFixed(2),
            "published": Date.now()
        });
    }
}

registerProcessor('jinglesession-audioworklet', MovimJingleSessionAudioWorklet);