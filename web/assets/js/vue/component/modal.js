Vue.component('modal', {
    template: '#modal-template',
    props: {
        show: {
            type: Boolean,
            default: function () {
                return false
            }
        },
    },
    methods: {
        close: function () {
            this.$emit('update:show', false);
        },
    },
    ready: function () {
        document.addEventListener("keydown", (e) => {
            if (this.show && e.keyCode == 27) {
                this.close();
            }
        });
    }
});