Vue.component('selectItem', {
    template: '#select-item',
    props: {
        dbObj: Object,
        model: Object,
        items: Object
    },
    data: function () {
        return {
            selected: false,
            foreignKeys: []
        }
    },
    computed: {
        object: function () {
            if (this.model && this.model.table === null) {
                return false;
            } else {
                return true;
            }
        }
    },
    methods: {
        changeStatus: function () {
            this.model.status = !this.model.status;
            if (this.model.status && this.dbObj[this.model.table]) {
                this._addRow(this.model.table);
            } else if (!this.model.status) {
                delete this.model.rows;
                this._updateDisplaySelect();
            }
            this.selected = !this.selected;
        },
        _addRow: function ($tableName) {
            /**
             * Add Foreign Keys and Disable tables with no relation
             * Add FK if checked
             */
            var $fields = this.dbObj[$tableName];

            for (var $field in $fields) {
                if ($field[0] === '_') {
                    continue;
                }
                var $fkTableName = null;

                if (!this.model.rows) {
                    Vue.set(this.model, 'rows', []);
                }

                if ($fields._FK && $fields._FK[$fields[$field].name]) {
                    $fkTableName = $fields._FK[$fields[$field].name].tableName;
                }
                this.model.rows.push({
                    'name': $fields[$field].name,
                    'table': $fkTableName,
                    'translation': $fields[$field]._field_translation,
                    'status': false,
                    'display': true,
                    'parent': false
                });
            }
            this._updateDisplaySelect();
        },
        _updateDisplaySelect: function () {
            if(this.model.parent === false) {return;}
            $display = (!(event.target.checked === true));

            for (var $index in this.items.rows) {
                if(this.items.rows[$index].parent === true && this.items.rows[$index].status === false) {
                    this.items.rows[$index].display = $display;
                }
            }
        }
    }
});