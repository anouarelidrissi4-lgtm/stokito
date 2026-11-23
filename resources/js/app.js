//

document.addEventListener('alpine:init', () => {
    Alpine.data('barcodeScanner', (onScan) => ({
        show: false,
        error: null,
        reader: null,

        open() {
            this.show = true;
            this.error = null;
            this.$nextTick(() => this.start());
        },

        close() {
            this.show = false;
            this.stop();
        },

        start() {
            if (typeof ZXing === 'undefined') {
                this.error = "Scanner indisponible (bibliotheque non chargee).";
                return;
            }

            this.reader = new ZXing.BrowserMultiFormatReader();
            this.reader
                .decodeFromVideoDevice(undefined, this.$refs.video, (result) => {
                    if (result) {
                        const code = result.getText();
                        this.stop();
                        this.show = false;
                        this.$wire.call(onScan, code);
                    }
                })
                .catch(() => {
                    this.error = "Impossible d'acceder a la camera.";
                });
        },

        stop() {
            if (this.reader) {
                try {
                    this.reader.reset();
                } catch (e) {
                    // ignore
                }
                this.reader = null;
            }
        },
    }));
});
