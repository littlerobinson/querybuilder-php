Vue.component('conditionItem', {
    template: '#condition-item',
    props: {
        checkedTables: {
            type: Array,
            default: function () {
                return []
            }
        },
        items: {
            type: Object,
            default: function () {
                return {}
            }
        }
    },
    data: function () {
        return {
            conditions: [],
            rows: [],
            newRuleTable: '',
            newRuleRow: '',
            newOperator: '',
            newValue: '',
            newCondition: [],
            operatorList: [
                {value: 'EQUAL', name: 'Est égal à'},
                {value: 'LIKE', name: 'Contient'}
            ]
        }
    },
    methods: {
        addCondition: function () {
            var $newCondition = {
                rule: this.newRuleRow + ' (' + this.newRuleTable + ')',
                operator: this.newOperator,
                value: this.newValue
            };
            this.conditions.push($newCondition);
        },
        addRuleRows: function () {
            this._clearNewCondition();
            for (var $rowName in this.items[this.newRuleTable].rows) {
                this.rows.push($rowName);
            }
        },
        _clearNewCondition: function () {
            this.newRuleRow = '';
            this.newOperator = '';
            this.newValue = '';
            this.rows = [];
        }
    }
});