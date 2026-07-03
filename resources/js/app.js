document.addEventListener('alpine:init', () => {
    Alpine.store('confirm', {
        show: false,
        message: '',
        danger: true,
        action: null,

        open(message, action, danger = true) {
            this.message = message;
            this.action = action;
            this.danger = danger;
            this.show = true;
        },

        accept() {
            const action = this.action;
            this.show = false;
            this.action = null;
            if (action) action();
        },

        cancel() {
            this.show = false;
            this.action = null;
        },
    });
});
