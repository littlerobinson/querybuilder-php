// bootstrap the select
var select = new Vue({
    el: '#app-select',
    data: {
        checkedTables: [],
        checkedList: null
    },
    methods: {
        getTableRows: function () {
            this.checkedList = this.checkedTables;
        }
    }
});

// bootstrap the condition
var condition = new Vue({
    el: '#app-condition',
    data: {
        select: null
    }
});