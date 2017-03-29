Vue.component('selectItem', {
    template: '#select-item',
    props: {
        dbObj: Object,
        model: Object,
        items: Object,
        from: Object,
        checkedTables: Array,
        checkedRows: Array,
        foreignKeys: Array,
        foreignTables: Array
    },
    data: function () {
        return {
            selected: false,
            depth: ''
        }
    },
    computed: {
        object: function () {
            if (this.model && this.model.isFK === false) {
                return false;
            } else {
                return true;
            }
        }
    },
    methods: {
        changeStatus: function () {
            this.model.status = !this.model.status;
            /// Case adding table
            if (this.model.status && this.dbObj[this.model.table] && this.object) {
                if (this.checkedTables.indexOf(this.model.table) === -1) {
                    this.checkedTables.push(this.model.table);
                }
                /// Create From variable with select data
                this._addFrom();
                this._addRow(this.model.table);
            } else if (!this.model.status) {
                if (this.checkedTables.indexOf(this.model.table) > -1 && this.object) {
                    this.checkedTables.splice(this.checkedTables.indexOf(this.model.table), 1);
                }
                delete this.model.rows;
                this._updateDisplaySelect();
            }
            this.selected = !this.selected;
            /// Push/Delete in checkedRows array
            if (this.selected && !this.object) {
                this._addFrom();
                this.checkedRows.push(this.model.table + '.' + this.model.name);
            } else {
                this.checkedRows.splice(this.checkedRows.indexOf(this.model.table + '.' + this.model.name), 1);
            }
        },
        _addRow: function ($tableName) {
            var $fields = this.dbObj[$tableName];

            for (var $field in $fields) {
                /// add table visibility condition
                if ($field[0] === '_' || $fields[$field]._field_visibility === false) {
                    continue;
                }
                var $table = this.model.table;
                var $parentName = this.model.name;
                var $isFK = false;

                if (!this.model.rows) {
                    Vue.set(this.model, 'rows', []);
                }

                if ($fields._FK && $fields._FK[$fields[$field].name]) {
                    $table = $fields._FK[$fields[$field].name].tableName;
                    $isFK = true;
                }

                this.model.rows.push({
                    'name': $fields[$field].name,
                    'table': $table,
                    'isFK': $isFK,
                    'translation': $fields[$field]._field_translation,
                    'status': false,
                    'display': true,
                    'firstParent': false,
                    'parentName': $parentName
                });
            }
            this._updateDisplaySelect();
        },
        _updateDisplaySelect: function () {
            if (this.model.firstParent === false) {
                return
            }
            $display = (!(event.target.checked === true));

            for (var $index in this.items.rows) {
                if (this.items.rows[$index].firstParent === true && this.items.rows[$index].status === false) {
                    this.items.rows[$index].display = $display;
                }
            }
        },
        _addFrom: function () {
            this.depth = this.$parent.depth !== '' ? this.$parent.depth + '.' + this.model.name : this.model.name; /// Add depth info
            var $listDepth = this.depth.split('.');
            var $tmpDepth = {};
            if ($listDepth.length === 1) {
                this.from[$listDepth[0]] = {};
            } else {
                var $i = 0;
                for (var $index in $listDepth) {
                    if ($index === '0') { /// First loop
                        $tmpDepth = this.from[$listDepth[$index]];
                    } else if ($index != ($listDepth.length - 1)) { /// Middle loops
                        $tmpDepth = $tmpDepth[$listDepth[$index]];
                    } else { /// Last loop
                        if(this.object) {
                            $tmpDepth[$listDepth[$index]] = {};
                        }else {
                            $tmpDepth[$listDepth[$index]] = $listDepth[$index];
                        }
                    }
                }
            }
        }
    }
});