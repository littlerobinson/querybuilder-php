Vue.component('selectItem', {
    props: {
        'id': {type: String},
        'rowValue': {
            type: Object, default: function () {
                return {}
            }
        },
        'childRowValue': {
            type: Object, default: function () {
                return {}
            }
        },
        'childRowKey': {type: String},
        'childRowIndex': {type: String},
        'parentKey': {type: String},
        'rowKey': {type: String}
    },
    methods: {
        changeChildRowStatus: function ($key, $childRowKey, $childRowValue, $rowKey) {
            //this.$set($childRowValue, 'rowValue');
            this.$parent.changeChildRowStatus($key, $childRowKey, $childRowValue, $rowKey);
            this.$set($childRowValue, 'rowValue');
        }
    },
    template: '\
       <div>\
       <input\
       type="checkbox"\
       :id="id"\
       :value="childRowKey"\
       @click="changeChildRowStatus(parentKey, childRowKey, childRowValue, rowKey)">\
       <label :for="id">{{ childRowValue.name }}</label>\
       </div>\
   '
});