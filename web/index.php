<?php
require "../bootstrap.php";

setcookie("school_id", 2);

require_once "query.php";
?>

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
                <select v-model="newRuleRow" class="form-control">
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
                <input type="text" v-model="newValue" class="form-control" placeholder="Condition">
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
                <td>{{ conditionValue.value }}</td>
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

<div class="container">
    <div class="row">
        <div id="app-request">
<!--
            <div v-show="loading" id="spinner-loading">
                <div class="cssload-container">
                    <div class="cssload-loading"><i></i><i></i></div>
                </div>
                <div id="spinner-text" class="cssload-text-loader">Chargement...</div>
            </div>

            <div v-show="!loading">
-->
            <div>
                <h1>Requêteur</h1>
                <hr>
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
                                        {{ value.name }}
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
                                <form method="post" action="query.php">
                                    <input type="hidden" name="action" value="spreadsheet">
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
                                <hr>
                                <button type="button" aria-expanded="false" class="btn btn-info pull-left">
                                    Charger
                                </button>
                                <button type="button" aria-expanded="false" class="btn btn-success pull-right">
                                    Sauvegarder
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

<script src="assets/vendor/jquery/dist/jquery.min.js"></script>
<script src="assets/vendor/vue/dist/vue.js"></script>
<script src="assets/vendor/vue-resource/dist/vue-resource.min.js"></script>
<script src="assets/js/vue/component/spreadsheet.js"></script>
<script src="assets/js/vue/component/select.js"></script>
<script src="assets/js/vue/component/condition.js"></script>
<script src="assets/js/vue/filter.js"></script>
<script src="assets/js/vue/appRequest.js"></script>

</body>
</html>
