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
            var $newRuleTable = this.newRuleTable;

            /// Get table fields
            var $selectItem = this.items.rows.filter(function (item, index) {
                if (item.name === $newRuleTable) {
                    return String(index);
                }
            })[0];
            console.log($selectItem);
            for (var $rowName in $selectItem.rows) {
                console.log($selectItem.rows[$rowName].name);
                this.rows.push($selectItem.rows[$rowName].name);
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