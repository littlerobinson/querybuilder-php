<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <title>Query Builder</title>
    <link rel="stylesheet" href="assets/vendor/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/vendor/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/vendor/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/spinner-image.css">
    <link rel="stylesheet" href="assets/css/spinner-text.css">
</head>
<body>
<!-- component for select template -->
<script type="text/x-template" id="select-item">
    <div class="checkbox" v-if="model.display">
        <input
                type="checkbox"
                :class="{bold: object}"
                @click="changeStatus">
        <label v-if="model.translation">{{ model.translation }}</label>
        <label v-else>{{ model.name }}</label>
        <div v-show="selected" v-if="object">
            <select-item
                    class="item"
                    v-for="row in model.rows"
                    :key="row.name"
                    :db-obj="dbObj"
                    :from="from"
                    :select-tables="selectTables"
                    :model="row"
                    :items="items">
            </select-item>
        </div>
    </div>
</script>

<!-- component for condition template -->
<script type="text/x-template" id="condition-item">
    <div class="container">
        <div class="row">
            <div class="col-md-2">
                <select v-model="newLogicalOperator" class="form-control">
                    <option value="">-- Opérateur --</option>
                    <option v-for="(operator, key) in logicalOperators" :value="operator.value">
                        {{ operator.name }}
                    </option>
                </select>
            </div>
            <div class="col-md-2">
                <select v-model="newRuleTable" @change="addRuleRows" class="form-control">
                    <option value="">-- Sélectionner une table --</option>
                    <option v-for="(objTable, field) in selectTables"
                            :value="{ parentTable: objTable.parentName, table: objTable.table, field: objTable.name }">
                        <span v-if="dbObj[objTable.table]._table_translation">{{ dbObj[objTable.table]._table_translation }} ( {{ field }} )</span>
                        <span v-else>{{ objTable.parentName}} {{ objTable.table }} ( {{ field }} )</span>
                    </option>
                </select>
            </div>
            <div class="col-md-3">
                <select v-model="newRuleRow" @change="addRuleOperator" class="form-control">
                    <option value="">-- Sélectionner un champs --</option>
                    <option v-if="newRuleTable != ''" v-for="(row, index) in rows" :value="index">
                        {{ row }}
                    </option>
                </select>
            </div>
            <div class="col-md-2">
                <select v-model="newRuleOperator" class="form-control">
                    <option value="">-- Sélectionner une condition --</option>
                    <option v-for="(operator, key) in ruleOperators" :value="operator.value">
                        {{ operator.name }}
                    </option>
                </select>
            </div>
            <div class="col-md-2">
                <input v-if="type == 'text'" type="text" v-model="newValue" class="form-control" placeholder="Condition">
                <input v-if="type == 'number'" type="number" v-model="newValue" class="form-control" placeholder="Condition">
                <input v-if="type == 'date'" type="date" v-model="newValue" class="form-control" placeholder="Condition">
                <input v-if="newRuleOperator == 'BETWEEN'" type="date" v-model="newValue2" class="form-control" placeholder="Condition 2">
            </div>
            <div class="form-group col-md-1">
                <div v-if="adding">
                    <input type="button" value="Ajouter" class="form-control" @click="addCondition">
                </div>
                <div v-else>
                    <input type="button" value="Ajouter" disabled="disabled" class="form-control">
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-2">
                <label for="selectedLimit">Limite</label>
                <select v-model="selectedLimit" class="form-control" id="selectedLimit">
                    <option v-for="limit in limits" :value="limit.value">
                        {{ limit.name }}
                    </option>
                </select>
            </div>
        </div>
        <hr>
        <table class="table table-responsive" v-if="conditions.length > 0">
            <thead>
            <tr>
                <th>Opérateur logique</th>
                <th>Donnée</th>
                <th>Règle</th>
                <th>Valeur</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <tr
                    v-for="(conditionValue, conditionKey, conditionIndex) in conditions"
                    :key="conditionKey"
            >
                <td>{{ conditionValue.logicalOperator }}</td>
                <td>{{ conditionValue.field }}</td>
                <td>{{ conditionValue.ruleOperator }}</td>
                <td>{{ conditionValue.value }} {{ conditionValue.value2 }}</td>
                <td>
                    <a class="btn btn-danger delete-condition" href="#" @click="deleteCondition(conditionKey)"><i
                                class="icon-trash icon-bar"></i> X</a>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</script>

<!-- component spreadsheet result template -->
<script type="text/x-template" id="spreadsheet-template">
    <table class="table table-bordered">
        <thead>
        <tr>
            <th v-for="column in columns"
                @click="sortBy(column.key)"
                :class="{ active: sortKey == column.key }">
                {{ column.label | capitalize }}
                <span class="arrow" :class="sortOrders[column.key] > 0 ? 'asc' : 'dsc'"></span>
            </th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="entry in filteredData">
            <td v-for="column in columns">
                {{ entry[column.key] }}
            </td>
        </tr>
        </tbody>
    </table>
</script>

<!-- template for the modal component -->
<script type="x/template" id="modal-template">
    <div class="modal-mask" @click="close" v-show="show" transition="modal">
        <div class="modal-container" @click.stop>
            <div class="modal-header">
                <slot name="header"></slot>
            </div>

            <div class="modal-body">
                <slot name="body"></slot>
            </div>

            <div class="modal-footer text-right">
                <slot name="footer"></slot>
            </div>
        </div>
    </div>
</script>

<div class="container">
    <div class="row">
        <div id="app-request">

            <div v-show="loading" id="spinner-loading">
                <div class="cssload-container">
                    <div class="cssload-loading"><i></i><i></i></div>
                </div>
                <div id="spinner-text" class="cssload-text-loader">Chargement...</div>
            </div>

            <div v-show="!loading">
                <div>
                    <h1>Requêteur</h1>
                    <hr>
                    <div>
                        <transition name="fade" appear hidden>
                            <div v-if="message" v-for="(item, key) in message">
                                <div v-if="item.length > 0" class="form-group">
                                    <div v-if="key === 'success'">
                                        <div class="alert alert-success alert-dismissible fade in" role="alert">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"
                                                    @click="deleteMessage(key)">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                            {{ item }}
                                        </div>
                                    </div>
                                    <div v-else-if="key === 'error'">
                                        <div class="alert alert-danger alert-dismissible fade in" role="alert">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"
                                                    @click="deleteMessage(key)">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                            {{ item }}
                                        </div>
                                    </div>
                                    <div v-else="key === 'info'">
                                        <div class="alert alert-info alert-dismissible fade in" role="alert">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"
                                                    @click="deleteMessage(key)">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                            {{ item }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </transition>

                        <div id="select" class="col-xs-2">
                            <transition name="fade" appear hidden>
                                <div class="panel panel-default">
                                    <div class="panel-heading"><strong>{{ 'Sélection' | capitalize }}</strong></div>
                                    <div class="panel-body">
                                        <select-item
                                                class="item"
                                                :db-obj="dbObj"
                                                :from="from"
                                                :select-tables="selectTables"
                                                :model="items"
                                                :items="items">
                                        </select-item>
                                    </div>
                                    <div class="panel-footer alert-danger">
                                        <strong>Liste des tables séléctionnées : </strong><br>
                                        <ul id="repeat-object">
                                            <li v-for="value in selectTables">
                                                <div v-if="dbObj[value.table]._table_translation">
                                                    {{ dbObj[value.table]._table_translation }}
                                                </div>
                                                <div v-else>
                                                    {{ value.table }}
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </transition>
                        </div>

                        <div id="condition" class="col-xs-8">
                            <transition name="fade" appear hidden>
                                <div class="panel panel-default">
                                    <div class="panel-heading"><strong>{{ 'Conditions' | capitalize }}</strong></div>
                                    <div class="panel-body">
                                        <condition-item
                                                :select-tables="selectTables"
                                                :items="items"
                                                :conditions="conditions"
                                                :db-obj="dbObj"
                                        >
                                        </condition-item>
                                        <button type="button" class="btn btn-success pull-right" aria-expanded="false"
                                                @click="search"
                                                :disabled="!searchable"
                                        >
                                            Recherche
                                        </button>
                                        <form method="post">
                                            <input type="hidden" name="action_query_builder" value="spreadsheet">
                                            <input type="hidden" name="columns" v-model="JSON.stringify(columns)">
                                            <input type="hidden" name="data" v-model="JSON.stringify(data)">
                                            <input type="submit" value="Extraire" class="btn btn-info pull-right"
                                                   :disabled="(data.length > 0) ? false : true">
                                        </form>
                                    </div>
                                </div>
                            </transition>
                        </div>

                        <div id="query" class="col-xs-2">
                            <transition name="fade" appear hidden>
                                <div class="panel panel-default">
                                    <div class="panel-heading"><strong>{{ 'Sauvegardes' | capitalize }}</strong></div>
                                    <div class="panel-body">
                                        <div>
                                            <ol id="save-queries" class="rectangle-list">
                                                <li v-for="(value, index) in saveQuery">
                                                    <a @click="loadSave(value.id)">
                                                        {{ value.title }}
                                                        <span v-if="value.user === null">(public)</span>
                                                        <span v-else>(privée)</span>
                                                    </a>
                                                    <i @click="deleteSave(value.id, index)"
                                                       class="fa fa-times fa-2x text-danger" aria-hidden="true"></i>
                                                </li>
                                            </ol>
                                        </div>
                                        <hr>
                                        <modal :show="showModal" @update:show="val => showModal = val">
                                            <h3 slot="header">
                                                SAUVEGARDER LA REQUÊTE
                                            </h3>

                                            <div slot="body">
                                                <div class="col-xs-8">
                                                    <input type="text" class="form-control"
                                                           placeholder="Titre de la requête"
                                                           v-model="saveTitle">
                                                </div>
                                                <div id="modal-save-btn" class="col-xs-4">
                                                    <button type="button" class="btn btn-success"
                                                            :disabled="!saveTitle.length > 0" @click="save(0)">
                                                        Sauvegarde public
                                                    </button>
                                                    <button type="button" class="btn btn-danger"
                                                            :disabled="!saveTitle.length > 0" @click="save(1)">
                                                        Sauvegarde privée
                                                    </button>
                                                </div>
                                            </div>

                                            <div slot="footer">
                                                <span v-for="value in selectTables">
                                                    <div v-if="dbObj[value.table]._table_translation">
                                                        {{ dbObj[value.table]._table_translation }}
                                                    </div>
                                                    <div v-else>
                                                        {{ value.table }}
                                                    </div>
                                                </span>
                                            </div>
                                        </modal>
                                        <button id="show-modal" class="btn btn-success pull-right"
                                                aria-expanded="false"
                                                @click="showModal = true"
                                                :disabled="!searchable"
                                        >
                                            Sauvegarde
                                        </button>
                                    </div>
                                </div>
                            </transition>
                        </div>

                        <!-- result -->
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading"><strong>Résultat</strong></div>
                                    <div class="panel-body">
                                        <table class="table">
                                            <thead></thead>
                                            <tbody id="tbody-response">
                                            </tbody>
                                        </table>

                                        <!-- research root element -->
                                        <div id="app-result-research">
                                            <form id="search">
                                                Recherche <input type="text" name="query" v-model="searchQuery">
                                            </form>
                                            <hr>
                                            <spreadsheet
                                                    :data="data"
                                                    :columns="columns"
                                                    :filter-key="searchQuery">
                                            </spreadsheet>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xs-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading"><strong>Requête SQL</strong></div>
                                    <div class="panel-body">
                                        <span>{{ sqlRequest }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="assets/vendor/jquery/dist/jquery.min.js"></script>
<script src="assets/vendor/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="assets/vendor/vue/dist/vue.js"></script>
<script src="assets/vendor/vue-resource/dist/vue-resource.min.js"></script>
<script src="assets/js/vue/component/spreadsheet.js"></script>
<script src="assets/js/vue/component/select.js"></script>
<script src="assets/js/vue/component/condition.js"></script>
<script src="assets/js/vue/component/modal.js"></script>
<script src="assets/js/vue/filter.js"></script>
<script src="assets/js/vue/appRequest.js"></script>

</body>
</html>
