Vue.component('conditionItem', {
    template: '#condition-item',
    props: {
        value: {
            type: Array,
            default: function () {
                return []
            }
        },
        checkedTables: {
            type: Array,
            default: function () {
                return []
            }
        }
    },
    data: function () {
        return {
            conditions: this.value,
            newCondition: '',
            tables: this.checkedTables
        }
    },
    methods: {
        addCondition: function () {
            console.log(this.newCondition);
            this.value.push(this.newCondition);
            this.newCondition = '';
        }
    }
});